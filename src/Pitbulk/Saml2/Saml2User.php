<?php

namespace Pitbulk\Saml2;

use Config;
use Input;
use OneLogin_Saml2_Auth;
use URL;

/**
 * A simple class that represents the user that 'came' inside the saml2 assertion
 * Class Saml2User
 * @package Pitbulk\Saml2
 */
class Saml2User
{

    protected $auth;

    public function __construct(OneLogin_Saml2_Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @return string User Id retrieved from assertion processed this request
     */
    public function getUserId()
    {
        $userId = null;

        $attrs = $this->getAttributes();
        if (!empty($attrs)) {
            $samlSettings = Config::get('laravel4-saml2::saml_settings');
            if (isset($samlSettings['attrMapping'])) {
                $attrMapping = $samlSettings['attrMapping'];
                if (isset($attrMapping['userId']) && !empty($attrMapping['userId'])) {
                    if (isset($attrs[$attrMapping['userId']])) {
                        $userId = $attrs[$attrMapping['userId']][0];
                    }
                }
            }
        }

        if (empty($userId)) {
            $userId = $this->getNameId();
        }
        return $userId;
    }

    /**
     * @return array attributes retrieved from assertion processed this request
     */
    public function getAttributes()
    {
        return $this->auth->getAttributes();
    }

    /**
     * @return string the saml assertion processed this request
     */
    public function getRawSamlAssertion()
    {
        return Input::get('SAMLResponse'); //just this request
    }

    public function getIntendedUrl()
    {
        $relayState = Input::get('RelayState'); //just this request

        if ($relayState && URL::full() != $relayState) {
            return $relayState;
        }
    }

    public function getSessionIndex()
    {
        return $this->auth->getSessionIndex();
    }

    public function getNameId()
    {
        return $this->auth->getNameId();
    }
}
