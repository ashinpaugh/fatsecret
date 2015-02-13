<?php

namespace Moop\Bundle\FatSecretBundle\API;

/**
 * An OAuth interface for User objects.
 * 
 * @author Austin Shinpaugh
 */
interface OAuthConsumerInterface
{
    /**
     * Get the OAuth token.
     * 
     * @return string
     */
    public function getOAuthToken();
    
    /**
     * Set the OAuth token.
     * 
     * @return $this
     */
    public function setOAuthToken($token);
    
    /**
     * Get the OAuth token secret.
     * 
     * @return string
     */
    public function getOAuthTokenSecret();
    
    /**
     * Set the OAuth token secret.
     * 
     * @return $this
     */
    public function setOAuthTokenSecret($token_secret);
}