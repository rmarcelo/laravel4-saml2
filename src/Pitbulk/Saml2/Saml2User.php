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

    function __construct(OneLogin_Saml2_Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @return string User Id retrieved from assertion processed this request
     */
    function getUserId()
    {
        $userId = null;

        $userId = $this->getMappedAttribute('userId');

        if (empty($userId)) {
            $userId = $this->getNameId();
        }
        return $userId;
    }

    /**
     * @return array attributes retrieved from assertion processed this request
     */
    function getAttributes()
    {
        return $this->auth->getAttributes();
    }

    /**
     * @return array attributes retrieved from assertion, mapped by settings
     */
    public function getMappedAttributes()
    {
        $unmappedAttributes = $this->getAttributes();
        if (empty($unmappedAttributes)) {
            return $unmappedAttributes;
        }
        $samlSettings = Config::get('laravel4-saml2::saml_settings');
        $mappedAttributes = [];
        if (isset($samlSettings['attrMapping'])) {
            foreach ($samlSettings['attrMapping'] as $mapped => $unmapped) {
                $mappedAttributes[$mapped] = $unmappedAttributes[$unmapped];
            }
        }
        return $mappedAttributes;
    }

    /**
     * @return string the mapped attribute from the assertions
     */
    public function getMappedAttribute($attribute)
    {
        $attributes = $this->getMappedAttributes();
        if (array_key_exists($attribute, $attributes)) {
            return $attributes[$attribute];
        }
        return null;
    }


    /**
     * @return string the saml assertion processed this request
     */
    function getRawSamlAssertion()
    {
        return Input::get('SAMLResponse'); //just this request
    }

    function getIntendedUrl()
    {
        $relayState = Input::get('RelayState'); //just this request

        if ($relayState && URL::full() != $relayState) {
            return $relayState;
        }
    }

    function getSessionIndex()
    {
        return $this->auth->getSessionIndex();
    }

    function getNameId()
    {
        return $this->auth->getNameId();
    }
} 