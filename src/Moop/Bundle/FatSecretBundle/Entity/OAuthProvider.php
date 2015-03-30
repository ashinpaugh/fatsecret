<?php

namespace Moop\Bundle\FatSecretBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class OAuthProvider
{
    const v1 = '1';
    const v2 = '2';
    
    /**
     * @ORM\OneToMany(targetEntity="OAuthToken", mappedBy="provider")
     * @var OAuthToken[]|ArrayCollection
     */
    protected $tokens;
    
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @var Integer
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string")
     * @var String
     */
    protected $name;
    
    /**
     * @ORM\Column(type="string")
     * @var String
     */
    protected $base_url;
    
    /**
     * @ORM\Column(type="string")
     * @var String
     */
    protected $client_token;
    
    /**
     * @ORM\Column(type="string")
     * @var String
     */
    protected $client_secret;
    
    /**
     * @ORM\Column(type="string")
     * @var String
     */
    protected $oauth_version;
    
    /**
     * Construct.
     * 
     * @param String $name
     * @param String $token
     * @param String $secret
     * @param String $url
     * @param string $version
     */
    public function __construct($name, $token, $secret, $url, $version = self::v1)
    {
        $this->tokens        = new ArrayCollection();
        $this->name          = $name;
        $this->base_url      = $url;
        $this->oauth_version = $version;
        $this->client_token  = $token;
        $this->client_secret = $secret;
    }
    
    /**
     * @return OAuthToken[]
     */
    public function getTokens()
    {
        return $this->tokens;
    }
    
    /**
     * @param OAuthToken[] $tokens
     *
     * @return OAuthProvider
     */
    public function setTokens(array $tokens)
    {
        $this->tokens = new ArrayCollection($tokens);
        
        return $this;
    }
    
    /**
     * @param OAuthToken $token
     *
     * @return OAuthProvider
     */
    public function addToken(OAuthToken $token)
    {
        $this->tokens->add($token);
        return $this;
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param int $id
     *
     * @return OAuthProvider
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param String $name
     *
     * @return OAuthProvider
     */
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getBaseUrl()
    {
        return $this->base_url;
    }
    
    /**
     * @param String $base_url
     *
     * @return OAuthProvider
     */
    public function setBaseUrl($base_url)
    {
        $this->base_url = $base_url;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getClientToken()
    {
        return $this->client_token;
    }
    
    /**
     * @param String $client_token
     *
     * @return OAuthProvider
     */
    public function setClientToken($client_token)
    {
        $this->client_token = $client_token;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getClientSecret()
    {
        return $this->client_secret;
    }
    
    /**
     * @param String $client_secret
     *
     * @return OAuthProvider
     */
    public function setClientSecret($client_secret)
    {
        $this->client_secret = $client_secret;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getOauthVersion()
    {
        return $this->oauth_version;
    }
    
    /**
     * @param String $oauth_version
     *
     * @return OAuthProvider
     */
    public function setOauthVersion($oauth_version)
    {
        $this->oauth_version = $oauth_version;
        return $this;
    }
}