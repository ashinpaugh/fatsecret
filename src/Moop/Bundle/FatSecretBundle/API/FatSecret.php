<?php

namespace Moop\Bundle\FatSecretBundle\API;

use Buzz\Client\AbstractClient;
use Buzz\Exception\ExceptionInterface;
use Doctrine\Common\Cache\CacheProvider;
use Moop\Bundle\FatSecretBundle\Exception\FatException;
use Moop\oAuth\Util\oAuth;

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
     * @var oAuth
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
     * @param CacheProvider $cache
     * @param oAuth         $client
     * @param String        $base_url
     */
    public function __construct(CacheProvider $cache, oAuth $client, $base_url)
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
     * @return String[]
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
     * @return String[]
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
     * @return String[]
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
     * @return String[]
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
     * @return String[]
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
     * @return String[]
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
     * @return String[]
     */
    public function getExercisesTypes()
    {
        return $this->makeRequest('GET', false, [
            'method' => 'exercises.get',
        ]);
    }
    
    /**
     * Return the entries from the user's food diary for a particular date
     * or specified ID.
     *
     * @param Integer|null           $entry_id Optional. Get a specific entry.
     * @param \DateTime|Integer|null $date     Optional. Seconds since epoch.
     *
     * @return String[]
     * @throws FatException
     */
    public function getFoodEntries($entry_id = null, $date = null)
    {
        if (!$entry_id && !$date) {
            throw new FatException('You must provide either a food entry ID or the number of days since epoch.');
        }
        
        return $this->makeRequest('POST', true, array_filter([
            'method'        => 'food_entries.get',
            'date'          => $this->getDate($date),
            'food_entry_id' => $entry_id,
        ]));
    }
    
    /**
     * Add a meal to a user's food diary.
     *
     * @param Integer           $food_id
     * @param Integer           $serving_id
     * @param String            $entry_name
     * @param String            $meal
     * @param Float             $portion
     * @param \DateTime|Integer $date      Optional. Seconds since epoch.
     *
     * @return String[]
     * @throws FatException
     */
    public function addFoodEntry($food_id, $serving_id, $entry_name, $meal, $portion, $date = null)
    {
        if (!in_array($meal, ['breakfast', 'lunch', 'dinner', 'other'])) {
            throw new FatException('Invalid meal type provided');
        }
        
        $this->checkOAuthTokenPresence();
        
        return $this->makeRequest('POST', true, array_filter([
            'method'          => 'food_entry.create',
            'food_id'         => $food_id,
            'food_entry_name' => $entry_name,
            'serving_id'      => $serving_id,
            'number_of_units' => $portion,
            'meal'            => $meal,
            'date'            => $this->getDate($date),
        ]));
    }
    
    /**
     * Weigh in.
     * 
     * @param Int    $weight
     * @param Int    $goal_weight_kg
     * @param Int    $height_cm
     * @param string $weight_type
     * @param string $height_type
     *
     * @return String[]
     * @throws FatException
     */
    public function weighIn($weight, $goal_weight_kg, $height_cm, $weight_type = 'kg', $height_type = 'cm')
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
     * Sets the oAuth tokens for a user so we don't have to pass in the
     * tokens to the actual requests.
     * 
     * @param FatUserInterface $user
     *
     * @return FatSecret
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
     * @return String
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
     * Make the oAuth request to FS.
     *
     * @param String  $method
     * @param Boolean $oauth_required
     * @param Mixed[] $params
     *
     * @return String[]
     * @throws FatException
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
        
        if (!$response) {
            throw new FatException('An error occurred when attempting to contact FatSecret\s API.');
        }
        
        $result = json_decode($response->getContent(), true);
        
        // Sometimes the API responses are wrapped in a useless wrapper array.
        $result = 1 === count($result) ? current($result) : $result;
        
        if ($can_cache) {
            $this->cache->save($cache_key, $result);
        }
        
        return $result;
    }
    
    /**
     * Make the oAuth request.
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
        } catch (ExceptionInterface $e) {
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
     * @return oAuth
     */
    protected function getOAuthClient()
    {
        return $this->oauth_client;
    }
    
    /**
     * Ensures that valid oAuth tokens are passed to the API.
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
     * See if the oAuth tokens were set when required by the API.
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
    
    /**
     * Get a \DateTime object.
     *
     * @param \DateTime|Integer|null $date
     *
     * @return \DateTime|null
     */
    private function getDate($date)
    {
        if (!$date || (!$date instanceof \DateTime && !is_numeric($date))) {
            return null;
        }
        
        if (is_numeric($date)) {
            $date = \DateTime::createFromFormat('U', $date);
        }
        
        return floor($date->format('U') / 86400);
    }
}