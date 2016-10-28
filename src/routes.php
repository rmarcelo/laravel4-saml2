<?php


Route::group(array('prefix' => Config::get('laravel4-saml2::settings.routesPrefix')), function () {

    Route::get('/logout', array(
        'as' => 'saml_logout',
        'uses' => 'Pitbulk\Saml2\Controllers\Saml2Controller@logout',
    ));

    Route::get('/metadata', array(
        'as' => 'saml_metadata',
        'uses' => 'Pitbulk\Saml2\Controllers\Saml2Controller@metadata',
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
