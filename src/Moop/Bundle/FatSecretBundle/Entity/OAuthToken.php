<?php

namespace Moop\Bundle\FatSecretBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Moop\Bundle\FatSecretBundle\API\OAuthConsumerInterface;

/**
 * @ORM\Entity()
 */
class OAuthToken
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @var Integer
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="OAuthProvider", inversedBy="tokens")
     */
    protected $provider;
    
    /**
     * @ORM\Column(type="string")
     * @var String
     */
    protected $token;
    
    /**
     * @ORM\Column(type="string")
     * @var String
     */
    protected $secret;
    
    /**
     * Constructor.
     * 
     * @param String $token
     * @param String $secret
     */
    public function __construct(OAuthProvider $provider, $token, $secret)
    {
        $this->token  = $token;
        $this->secret = $secret;
        
        $provider->addToken($this);
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
     * @return OAuthToken
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * @return String
     */
    public function getToken()
    {
        return $this->token;
    }
    
    /**
     * @param String $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }
    
    /**
     * @return String
     */
    public function getSecret()
    {
        return $this->secret;
    }
    
    /**
     * @param String $token_secret
     * @return $this
     */
    public function setSecret($token_secret)
    {
        $this->secret = $token_secret;
        return $this;
    }
    
    /**
     * @return OAuthProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }
    
    /**
     * @param OAuthProvider $provider
     *
     * @return OAuthToken
     */
    public function setProvider(OAuthProvider $provider)
    {
        $this->provider = $provider;
        return $this;
    }
}