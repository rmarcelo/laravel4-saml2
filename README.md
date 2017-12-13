## Laravel 4 - Saml2
A Laravel package for Saml2 integration as a SP (service provider) based on OneLogin toolkit, which is much lighter and easier to install than simplesamlphp SP. It doesn't need separate routes or session storage to work!

The aim of this library is to be as simple as possible. We won't mess with Laravel users, auth, session...  We prefer to limit ourselves to a concrete task. Ask the user to authenticate at the IDP and process the response. Same case for SLO requests.


## Installation - Composer

To install Saml2 as a Composer package to be used with Laravel 4, simply add this to your composer.json:

```json
"pitbulk/laravel4-saml2": "^0.0.5"
```

..and run `composer update`.  Once it's installed, you can register the service provider in `app/config/app.php` in the `providers` array:

```php
'providers' => array(
    		'Pitbulk\Saml2\Saml2ServiceProvider',
)
```

Then publish the config file with `php artisan config:publish pitbulk/laravel4-saml2`. This will add the file `app/config/packages/pitbulk/laravel4-saml2/saml_settings.php`. This config is handled almost directly by [onelogin](https://github.com/onelogin/php-saml) so you may get further references there, but will cover here what's really necessary.


### Configuration

Once you publish your saml_settings.php to your own files, you need to configure your SP and IDP (remote server). The only real difference between this config and the one that OneLogin uses, is that the SP entityId, assertionConsumerService url and singleLogoutService URL are injected by the library. They are taken from routes 'saml_metadata', 'saml_acs' and 'saml_sls' respectively.

You can, as usual, create environment specific configurations in subdirectories, i.e `app/config/packages/pitbulk/laravel4-saml2/dev/saml_settings.php`

You can get relevant metadata for setting up the IDP at 'http://laravel_url/saml2/metadata'. For instance, if you're using simplesamlphp, your configuration in `/metadata/sp-remote.php` should look something like this:

```php
$metadata['http://laravel_url/saml2/metadata'] = array(
    'AssertionConsumerService' => 'http://laravel_url/saml2/acs',
    'SingleLogoutService' => 'http://laravel_url/saml2/sls',
    //the following two affect what the $Saml2user->getUserId() will return
    'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
    'simplesaml.nameidattribute' => 'uid' 
);
```


### Routes

The package will automatically inject routes for usage with the IDP. By default the url's are prefixed by saml2, but this can be changed with the setting named `routesPrefix` in your saml_settings.php. The following urls are created
* saml2/metadata - metadata for IDP setup
* saml2/logout - end the session at the IDP
* saml2/acs - [Assertion Consumer Service](https://wiki.shibboleth.net/confluence/display/CONCEPT/AssertionConsumerService)
* saml2/sls - [SingleLogoutService](https://www.portalguard.com/blog/2016/06/20/saml-single-logout-need-to-know/)


### Usage

When you want your user to login, just call `Saml2Auth::login()`. Just remember that it does not use any session storage, so you have to check for a session yourself before calling it. 
A good place to start would be in the auth filter.

```php
Route::filter('auth', function()
{
	if (Auth::guest()) { 
		return Saml2Auth::login(URL::full()); // The users intended URL is saved in RelayState
	}
});
```

This will redirect the user to the IDP, and after authentication the user will be returned to the Assertion Consumer Service endpoint (by default at 'http://laravel_url/saml2/acs'). After processing the response the library will fire an event, `saml2.loginRequestReceived`.
You need to write code to listen for this event and handle the rest of the login process for Laravel. This code can be places in either `app/start/global.php` or, if you've created it, in `app/events.php`. Read more about handling events in the [Laravel documentation](https://laravel.com/docs/4.2/events).

```php
Event::listen('saml2.loginRequestReceived', function(Saml2User $saml2User)
{
    // Useful data in $saml2User:
    // $saml2User->getAttributes();
    // $saml2User->getUserId();
    // base64_decode($saml2User->getRawSamlAssertion());
    // $saml2User->getIntendedUrl()

    // Find user by ID or attribute
    $user = User::find($samlUser->getUserId);
    $user = User::where('email', $samlUser->getNameId());
    if(!$user) {
        // If the user does not exist, create a new one just in time, or show an error message
    }
    // Create the login session for the user
    Auth::login($user);
});
```

You will also need to look over your login page (if you've created one)


### Log out

There are two ways the user can log out.

## The user logs out in your app
In this case you 'should' notify the IDP first so the global session is closed. You should 'not' end the users session yet, this is done when the IDP sends a confirmation of the logout.
This is initiated by calling `Saml2Auth::logout()` or by redirecting the user to the route named 'saml_logout' (by default at 'http://laravel_url/saml2/logout').
A logout request will be sent to the IDP that will send a response back to the Sinlge Logout Service url (by default at 'http://laravel_url/saml2/sls').
When this happens and event will fired, `saml2.logoutRequestReceived`, you should handle this event and end the users session in Laravel.

```php
Event::listen('saml2.logoutRequestReceived', function()
{
    Auth::logout();
    //echo "bye, we logged out.";
});
```

## User logout from the IPP
The user either logs out at the IDP service directly or, from another SP who sends a logout request to the IDP (as described in the first logout case). In this case the IDP will send a request to the same Single Logout Service endpoint as the first case, so your code should already take care of everything.


That's it. Feel free to ask any questions, make PR or suggestions, or open Issues.

### Forked from
Lavarel 4 - Saml2 Reference: https://github.com/Kn4ppster/laravel4-saml2
