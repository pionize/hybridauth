<?php
/*!
* Hybridauth
* https://hybridauth.github.io | https://github.com/hybridauth/hybridauth
*  (c) 2015 Hybridauth authors | https://hybridauth.github.io/license.html
*/

namespace Hybridauth\Provider;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\UnexpectedValueException;
use Hybridauth\Data;
use Hybridauth\User;

/**
 *
 */
class LinkedIn extends OAuth2
{
    /**
    * {@inheritdoc}
    */
    public $scope = 'r_basicprofile r_emailaddress r_contactinfo';

    /**
    * {@inheritdoc}
    */
    protected $apiBaseUrl = 'https://api.linkedin.com/v1/';

    /**
    * {@inheritdoc}
    */
    protected $authorizeUrl = 'https://www.linkedin.com/uas/oauth2/authorization';

    /**
    * {@inheritdoc}
    */
    protected $accessTokenUrl = 'https://www.linkedin.com/uas/oauth2/accessToken';

    /**
    * {@inheritdoc}
    */
    protected function initialize()
    {
        parent::initialize();

        $this->apiRequestHeaders = [
            'Authorization' => 'Bearer ' . $this->token('access_token')
        ];
  
        $this->tokenExchangeHeaders = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
    }

    /**
    * {@inheritdoc}
    */
  public function getUserProfile()
  {
      $this->fields = [
        'id', 'email-address', 'first-name', 'last-name', 'headline','location', 'industry',
        'picture-url', 'public-profile-url',
      ];
      
      $response = $this->apiRequest('people/~:(' . implode(',', $this->fields) . ')?format=json');
      
      $data = new Data\Collection($response);
      
      if (! $data->exists('id')) {
        throw new UnexpectedValueException('Provider API returned an unexpected response.');
      }
      
      $tmp = $data->get('location');
      $location = explode(',', $tmp->name);
      $city = $location[1];
      $userProfile = new User\Profile();
      
      $userProfile->identifier  = $data->get('id');
      $userProfile->firstName   = $data->get('firstName');
      $userProfile->lastName    = $data->get('lastName');
      $userProfile->photoURL    = $data->get('pictureUrl');
      $userProfile->profileURL  = $data->get('publicProfileUrl');
      $userProfile->description       = $data->get('headline');
      //$userProfile->bio         = $data->get('language');
      $userProfile->city        = $city;
      $userProfile->email       = $data->get('emailAddress');
      $userProfile->emailVerified       = $data->get('emailAddress');
      
      $userProfile->displayName = trim($userProfile->firstName . ' ' . $userProfile->lastName);
      
      return $userProfile;
  }
}
