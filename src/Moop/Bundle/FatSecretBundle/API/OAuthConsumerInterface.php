<?php

namespace Moop\Bundle\FatSecretBundle\API;

use Moop\Bundle\FatSecretBundle\Entity\OAuthProvider;
use Moop\Bundle\FatSecretBundle\Entity\OAuthToken;

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
     * @return OAuthToken[]
     */
    public function getOAuthTokens();
    
    /**
     * Get the OAuth token.
     * 
     * @param mixed $provider
     * 
     * @return OAuthToken
     */
    public function getOAuthToken($provider);
    
    /**
     * Set the OAuth token.
     * 
     * @param OAuthProvider $provider
     * @param String        $token
     * @param String        $secret
     * 
     * @return $this
     */
    public function addOAuthToken(OAuthProvider $provider, $token, $secret);
    
    /**
     * Remove a token that's associated with a user.
     * 
     * @param OAuthToken $token
     * 
     * @return $this
     */
    public function removeOAuthToken(OAuthToken $token);
}