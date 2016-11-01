<?php

$samlSettings =  Config::get('laravel4-saml2::saml_settings');
if (isset($samlSettings['lavarel']) && isset($samlSettings['lavarel']['routesPrefix'])) {
       $prefix = $samlSettings['lavarel']['routesPrefix'];
} else {
        $prefix = 'saml2';
}

Route::group([
    'prefix' => $prefix
],
    function () {

    Route::get('/metadata', array(
        'as' => 'saml_metadata',
        'uses' => 'Pitbulk\Saml2\Controllers\Saml2Controller@metadata',
    ));

#    Route::get('/login', array(
#        'as' => 'saml_login',
#        'uses' => 'Pitbulk\Saml2\Controllers\Saml2Controller@login',
#    ));

    Route::get('/logout', array(
        'as' => 'saml_logout',
        'uses' => 'Pitbulk\Saml2\Controllers\Saml2Controller@logout',
    ));

    Route::post('/acs', array(
        'as' => 'saml_acs',
        'uses' => 'Pitbulk\Saml2\Controllers\Saml2Controller@acs',
    ));

    Route::get('/sls', array(
        'as' => 'saml_sls',
        'uses' => 'Pitbulk\Saml2\Controllers\Saml2Controller@sls',
    ));
});
