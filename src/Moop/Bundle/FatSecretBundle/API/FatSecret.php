<?php

namespace Moop\Bundle\FatSecretBundle\API;

use Doctrine\Common\Cache\CacheProvider;
use Moop\Bundle\FatSecretBundle\Exception\FatException;
use Moop\Bundle\FatSecretBundle\Utility\OAuth;
use stdClass;

/**
 * FatSecret API Library.
 * 
 * @author Austin Shinpaugh
 * @date 2/9/2015
 */
class FatSecret
{
    /**
     * @var CacheProvider
     */
    protected $cache;
    
    /**
     * @var OAuth
     */
    protected $oauth_client;
    
    /**
     * @var String
     */
    protected $base_url;
    
    /**
     * @var String
     */
    protected $format;
    
    /**
     * @Construct.
     * 
     * @param String $base_url
     */
    public function __construct(CacheProvider $cache, OAuth $client, $base_url)
    {
        $this->format       = 'json';
        $this->cache        = $cache;
        $this->oauth_client = $client;
        $this->base_url     = $base_url;
        
        // FS can timeout occasionally - give it time to breathe.
        $this->getOAuthClient()->getHttpClient()
            ->getClient()
            ->setTimeout(15)
        ;
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
     * POST call to prevent caching for security reasons.
     * 
     * @param String $user_id
     *
     * @return stdClass
     */
    public function getAuthTokenInfo($user_id)
    {
        return $this->makeRequest('POST',  [
            'method'  => 'profile.get_auth',
            'user_id' => $user_id,
        ]);
    }
    
    /**
     * Get some basic profile information about a user.
     * 
     * @param String $auth_token
     * @param String $auth_secret
     *
     * @return Array
     */
    public function getProfile($auth_token = null, $auth_secret = null)
    {
        $this->checkAndSyncTokens($auth_token, $auth_secret);
        
        return $this->makeRequest('GET',  [
            'method'             => 'profile.get',
            'oauth_token'        => $auth_token,
            'oauth_token_secret' => $auth_secret,
        ]);
    }
    
    /**
     * Search for a food.
     * 
     * @param String  $search
     * @param Integer $max_results
     * @param Integer $page_number
     *
     * @return Array
     */
    public function searchFood($search, $max_results = 15, $page_number = 0)
    {
        return $this->makeRequest('GET',  [
            'method'            => 'foods.search',
            'max_results'       => $max_results,
            'page_number'       => $page_number,
            'search_expression' => $search,
        ]);
    }
    
    /**
     * Get details about a specific food entry.
     * 
     * @param Integer $food_id
     *
     * @return Array
     */
    public function getFood($food_id)
    {
        return $this->makeRequest('GET',  [
            'method'  => 'food.get',
            'food_id' => $food_id,
        ]);
    }
    
    /**
     * Search recipes.
     * 
     * @param String  $search
     * @param Integer $max_results
     * @param Integer $page_number
     *
     * @return Array
     */
    public function searchRecipes($search, $max_results = 15, $page_number = 0)
    {
        return $this->makeRequest('GET',  [
            'method'            => 'recipes.search',
            'max_results'       => $max_results,
            'page_number'       => $page_number,
            'search_expression' => $search,
        ]);
    }
    
    /**
     * Fetches the Exercise types supported by FatSecret.
     * Should be cached for a long period of time.
     * 
     * @return Array
     */
    public function getExercisesTypes()
    {
        return $this->makeRequest('GET', [
            'method' => 'exercises.get',
        ]);
    }
    
    public function weighIn($weight, $goal_weight_kg, $height_cm, $weight_type = 'lb', $height_type = 'inch', $auth_token = null, $auth_secret = null)
    {
        $this->checkAndSyncTokens($auth_token, $auth_secret);
        
        return $this->makeRequest('POST', [
            'method'             => 'weight.update',
            'oauth_token'        => $auth_token,
            'oauth_token_secret' => $auth_secret,
            'current_weight_kg'  => $weight,
            'current_height_cm'  => $height_cm,
            'weight_type'        => $weight_type,
            'height_type'        => $height_type,
            'goal_weight_kg'     => $goal_weight_kg
        ]);
    }
    
    /**
     * Sets the OAuth tokens for a user so we don't have to pass in the
     * tokens to the actual requests.
     * 
     * @param FatUserInterface $user
     *
     * @return $this
     */
    public function setUserOAuthTokens(FatUserInterface $user)
    {
        $this->getOAuthClient()
            ->setOAuthToken($user->getOAuthToken())
            ->setOAuthTokenSecret($user->getOAuthTokenSecret())
        ;
        
        return $this;
    }
    
    /**
     * Get FatSecret's response format.
     * 
     * @return $this
     */
    public function getFormat()
    {
        return $this->format;
    }
    
    /**
     * Set FatSecret's response format.
     *
     * @param String $format
     *
     * @return $this
     * @throws FatException
     */
    public function setFormat($format)
    {
        if (!in_array($format, ['json', 'xml'])) {
            throw new FatException('Invalid format set: ' . $format);
        }
        
        return $this;
    }
    
    /**
     * Make the OAuth request to FS.
     * 
     * @param String $method
     * @param array  $params
     *
     * @return Array
     */
    protected function makeRequest($method, array $params)
    {
        $cache_key = implode('-', array_merge([$method], $params));
        $can_cache = 'GET' === $method;
        
        if ($can_cache && ($content = $this->cache->fetch($cache_key))) {
            return $content;
        }
        
        $params   = array_merge($params, ['format' => $this->getFormat()]);
        $response = $this->getOAuthClient()->send($this->base_url, $params, $method);
        $result   = json_decode($response->getContent(), true);
        
        // Sometimes the API responses are wrapped in a useless wrapper array.
        $result = 1 === count($result) ? current($result) : $result;
        
        if ($can_cache) {
            $this->cache->save($cache_key, $result);
        }
        
        return $result;
    }
    
    /**
     * @return OAuth
     */
    protected function getOAuthClient()
    {
        return $this->oauth_client;
    }
    
    /**
     * Ensures that valid OAuth tokens are passed to the API.
     * 
     * @param String &$auth_token
     * @param String &$auth_secret
     *
     * @throws FatException
     */
    protected function checkAndSyncTokens(&$auth_token, &$auth_secret)
    {
        if ($auth_token && $auth_secret) {
            return;
        }
        
        $this->checkOAuthTokenPresence();
        
        $auth_token  = $this->getOAuthClient()->getOAuthToken();
        $auth_secret = $this->getOAuthClient()->getOAuthTokenSecret();
    }
    
    /**
     * See if the OAuth tokens were set when required by the API.
     * 
     * @return bool
     * @throws FatException
     */
    private function checkOAuthTokenPresence()
    {
        $client = $this->getOAuthClient();
        
        if (!$client->getOAuthToken() || !$client->getOAuthTokenSecret()) {
            throw new FatException('One or more OAuth tokens were not found.');
        }
        
        return true;
    }
}