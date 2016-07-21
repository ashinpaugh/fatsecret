<?php

namespace Moop\Bundle\FatSecretBundle\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use Moop\Bundle\FatSecretBundle\API\FatUserInterface;
use Moop\oAuth\Entity\OAuthProvider;
use Moop\oAuth\Entity\OAuthToken;

class FatSecretUser implements FatUserInterface
{
    /**
     * @var OAuthToken[]
     */
    protected $tokens;
    
    /**
     * FatSecretUser constructor.
     *
     * @param String $token
     * @param String $secret
     */
    public function __construct($token, $secret)
    {
        $this->tokens = new ArrayCollection();
        
        $this->tokens->set('fat_secret', new OAuthToken(
            new OAuthProvider('fat_secret', '', '', ''),
            $token,
            $secret
        ));
    }
    
    /**
     * Get the OAuth token.
     *
     * @return OAuthToken[]
     */
    public function getOAuthTokens()
    {
        return $this->tokens;
    }
    
    /**
     * Get the OAuth token.
     *
     * @param mixed $provider
     *
     * @return OAuthToken
     */
    public function getOAuthToken($provider)
    {
        return $this->tokens->get($provider);
    }
    
    /**
     * Set the OAuth token.
     *
     * @param OAuthProvider $provider
     * @param String        $token
     * @param String        $secret
     *
     * @return $this
     */
    public function addOAuthToken(OAuthProvider $provider, $token, $secret) { }
    
    /**
     * Remove a token that's associated with a user.
     *
     * @param OAuthToken $token
     *
     * @return $this
     */
    public function removeOAuthToken(OAuthToken $token) { }
}