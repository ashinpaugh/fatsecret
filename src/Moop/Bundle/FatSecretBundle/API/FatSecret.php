<?php

namespace Moop\Bundle\FatSecretBundle\API;

use Moop\Bundle\FatSecretBundle\Utility\OAuth;
use stdClass;

/**
 * FatSecret API Library.
 * 
 * @see  http://platform.fatsecret.com/api/Default.aspx?screen=res
 * @date 2/9/2015
 */
class FatSecret
{
    /**
     * @var OAuth
     */
    protected $oauth_client;
    
    /**
     * @var String
     */
    protected $base_url;
    
    /**
     * @Construct.
     * 
     * @param String $base_url
     */
    public function __construct(OAuth $client, $base_url)
    {
        $this->oauth_client = $client;
        $this->base_url     = $base_url;
    }
    
    /**
     * Creates a new user profile on FS.
     * On success - a auth_token and auth_secret are received.
     * 
     * @param String $user_id
     *
     * @return stdClass
     */
    public function createProfile($user_id)
    {
        return $this->makeRequest('POST',  [
            'method'  => 'profile.create',
            'user_id' => $user_id,
        ]);
    }
    
    /**
     * Fetches the auth_token and auth_secret for a user.
     * 
     * @param String $user_id
     *
     * @return stdClass
     */
    public function getAuthTokenInfo($user_id)
    {
        return $this->makeRequest('GET',  [
            'method'  => 'profile.get_auth',
            'user_id' => $user_id,
        ]);
    }
    
    /**
     * Make the OAuth request to FS.
     * 
     * @param String $method
     * @param array  $params
     *
     * @return stdClass
     */
    protected function makeRequest($method, array $params)
    {
        $params   = array_merge($params, ['format' => 'json']);
        $response = $this->getOAuthClient()->send(
            $this->base_url,
            $params,
            $method
        );
        
        return json_decode($response->getContent());
    }
    
    /**
     * @return OAuth
     */
    protected function getOAuthClient()
    {
        return $this->oauth_client;
    }
}