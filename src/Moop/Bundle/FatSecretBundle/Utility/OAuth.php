<?php

namespace Moop\Bundle\FatSecretBundle\Utility;


use Buzz\Browser;

/**
 * Generic OAuth v1.0 utility built around Buzz.
 * 
 * @author Austin Shinpaugh
 */
class OAuth
{
    /**
     * @var Browser
     */
    protected $client;
    
    /**
     * @var String
     */
    protected $consumer_key;
    
    /**
     * @var String
     */
    protected $consumer_secret;
    
    /**
     * @var String
     */
    protected $auth_token;
    
    /**
     * @var String
     */
    protected $auth_token_secret;
    
    /**
     * @Construct
     * 
     * @param Browser $browser
     * @param String  $key
     * @param String  $secret
     */
    public function __construct(Browser $browser, $key, $secret)
    {
        $this->client          = $browser;
        $this->consumer_key    = $key;
        $this->consumer_secret = $secret;
    }
    
    /**
     * Make a request against the FatSecret APIs.
     * 
     * @param String $base_url
     * @param array  $params
     * @param string $method
     *
     * @return \Buzz\Message\MessageInterface
     */
    public function send($base_url, array $params = [], $method = 'GET')
    {
        $params = array_merge($this->getOAuthParams(), $params);
        ksort($params);
        
        $params['oauth_signature'] = $this->getSignature(
            $base_url,
            $params,
            $method
        );
        
        if ('GET' === strtoupper($method)) {
            $base_url .= '?' . http_build_query($params, null, '&', PHP_QUERY_RFC3986);
        }
        
        return $this->client->call($base_url, $method, [], $params);
    }
    
    /**
     * Retrieve the OAuth sig.
     * 
     * @param String   $base_url
     * @param String[] $params
     * @param String   $method
     *
     * @return string
     */
    protected function getSignature($base_url, $params, $method)
    {
        $sig_base   = $this->getSignatureBase($base_url, $method, $params);
        $sig_secret = $this->consumer_secret . '&' . $this->auth_token_secret;
        
        return base64_encode(hash_hmac('sha1', $sig_base, $sig_secret, true));
    }
    
    /**
     * Build the Signature Base string that's used to encrypt the OAuth
     * Consumer Secret Key and Access Token Secret.
     * @param String   $base_url
     * @param String   $method
     * @param String[] $params
     *
     * @return string
     */
    protected function getSignatureBase($base_url, $method, array $params)
    {
        $normalized_url    = $base_url;
        $normalized_params = http_build_query($params, null, '&', PHP_QUERY_RFC3986);
        
        return strtoupper($method)
            . '&' . rawurlencode($normalized_url)
            . '&' . rawurlencode($normalized_params)
        ;
    }
    
    /**
     * Returns a list of OAuth params used to security check.
     * 
     * @return array
     */
    protected function getOAuthParams()
    {
        return array_filter([
            'oauth_token'            => $this->auth_token,
            'oauth_consumer_key'     => $this->consumer_key,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp'        => time(),
            'oauth_nonce'            => md5(uniqid()),
            'oauth_version'          => '1.0',
        ]);
    }
}