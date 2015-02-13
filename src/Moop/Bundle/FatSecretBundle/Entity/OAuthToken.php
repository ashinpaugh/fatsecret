<?php

namespace Moop\Bundle\FatSecretBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Moop\Bundle\FatSecretBundle\API\OAuthConsumerInterface;

/**
 * @ORM\Entity()
 */
class OAuthToken implements OAuthConsumerInterface
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
    protected $oauth_token;
    
    /**
     * @ORM\Column(type="string")
     * @var String
     */
    protected $oauth_token_secret;
    
    /**
     * Constructor.
     * 
     * @param String $token
     * @param String $secret
     */
    public function __construct($token, $secret)
    {
        $this->oauth_token        = $token;
        $this->oauth_token_secret = $secret;
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
     * {@inheritdoc}
     */
    public function getOAuthToken()
    {
        return $this->oauth_token;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setOAuthToken($token)
    {
        $this->oauth_token = $token;
        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getOAuthTokenSecret()
    {
        return $this->oauth_token_secret;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setOAuthTokenSecret($token_secret)
    {
        $this->oauth_token_secret = $token_secret;
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
     * @param mixed $provider
     *
     * @return OAuthToken
     */
    public function setProvider(OAuthProvider $provider)
    {
        $this->provider = $provider;
        return $this;
    }
}