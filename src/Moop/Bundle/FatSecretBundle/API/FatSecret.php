<?php

namespace Moop\Bundle\FatSecretBundle\API;

use Buzz\Client\AbstractClient;
use Buzz\Client\ClientInterface;
use Buzz\Exception\RequestException;
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
        $this->setTimeout(15);
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
        return $this->makeRequest('POST', false, [
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
        return $this->makeRequest('POST', false, [
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
        
        return $this->makeRequest('GET', false, [
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
        return $this->makeRequest('GET', false, [
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
        return $this->makeRequest('GET', false, [
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
        return $this->makeRequest('GET', false, [
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
        return $this->makeRequest('GET', false, [
            'method' => 'exercises.get',
        ]);
    }
    
    public function getFoodEntries($entry_id = null, $date = null)
    {
        if (!$entry_id && !$date) {
            throw new FatException('You must provide either a food entry ID or the number of days since epoch.');
        }
        
        return $this->makeRequest('POST', true, array_filter([
            'method'        => 'food_entries.get',
            'date'          => $date,
            'food_entry_id' => $entry_id,
        ]));
    }
    
    /**
     * Add a meal to a user's food diary.
     * 
     * @param Integer $food_id
     * @param String  $entry_name
     * @param Integer $serving_id
     * @param Float   $portion
     * @param String  $meal
     *
     * @return Array
     * @throws FatException
     */
    public function addFoodEntry($food_id, $entry_name, $serving_id, $portion, $meal)
    {
        if (!in_array($meal, ['breakfast', 'lunch', 'dinner', 'other'])) {
            throw new FatException('Invalid meal type provided');
        }
        
        $this->checkOAuthTokenPresence();
        
        return $this->makeRequest('POST', true, [
            'method'          => 'food_entry.create',
            'food_id'         => $food_id,
            'food_entry_name' => $entry_name,
            'serving_id'      => $serving_id,
            'number_of_units' => $portion,
            'meal'            => $meal,
        ]);
    }
    
    public function weighIn($weight, $goal_weight_kg, $height_cm, $weight_type = 'lb', $height_type = 'inch')
    {
        $this->checkOAuthTokenPresence();
        
        return $this->makeRequest('POST', true, [
            'method'             => 'weight.update',
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
        $token = $user->getOAuthToken('fat_secret');
        
        $this->getOAuthClient()
            ->setOAuthToken($token->getToken())
            ->setOAuthTokenSecret($token->getSecret())
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
     * @param String  $method
     * @param Boolean $oauth_required
     * @param array   $params
     *
     * @return Array
     */
    protected function makeRequest($method, $oauth_required, array $params)
    {
        if ($oauth_required) {
            $client = $this->getOAuthClient();
            $params = array_merge($params, [
                'oauth_token'        => $client->getOAuthToken(),
                'oauth_token_secret' => $client->getOAuthTokenSecret(),
            ]);
        }
        
        if ($result = $this->checkCache($method, $params, $can_cache, $cache_key)) {
            return $result;
        }
        
        $response = $this->send($method, $this->base_url, array_merge(
            $params,
            ['format' => $this->getFormat()]
        ));
        
        
        $result = json_decode($response->getContent(), true);
        
        // Sometimes the API responses are wrapped in a useless wrapper array.
        $result = 1 === count($result) ? current($result) : $result;
        
        if ($can_cache) {
            $this->cache->save($cache_key, $result);
        }
        
        return $result;
    }
    
    /**
     * Make the OAuth request.
     * 
     * @param String   $method
     * @param String   $url
     * @param String[] $params
     *
     * @return \Buzz\Message\MessageInterface
     */
    private function send($method, $url, array $params)
    {
        try {
            return $this->getOAuthClient()->send($url, $params, $method);
        } catch (RequestException $e) {
            if (($timeout = $this->getTimeout()) && $timeout >= 30) {
                return null;
            }
            
            return $this->setTimeout(5 + $timeout)->send($method, $url, $params);
        }
    }
    
    /**
     * Check the cache before making a request to FS.
     * 
     * @param String   $method
     * @param array    $params
     * @param Boolean &$can_cache
     * @param String  &$cache_key
     *
     * @return mixed
     */
    private function checkCache($method, array $params, &$can_cache, &$cache_key)
    {
        $cache_key = implode('-', array_merge([$method], $params));
        $can_cache = 'GET' === $method;
        
        if ($can_cache && ($content = $this->cache->fetch($cache_key))) {
            return $content;
        }
        
        return null;
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
    
    /**
     * Gets the CURL client.
     * 
     * @return AbstractClient
     */
    protected function getHttpClient()
    {
        return $this->getOAuthClient()->getHttpClient()->getClient();
    }
    
    /**
     * Returns how long the CURL request can take before a timeout occurs.
     * 
     * @return int
     */
    public function getTimeout()
    {
        return $this->getHttpClient()->getTimeout();
    }
    
    /**
     * Sets how long a CURL request can take before it time out.
     * 
     * @param int $timeout
     *
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->getHttpClient()->setTimeout($timeout);
        return $this;
    }
}