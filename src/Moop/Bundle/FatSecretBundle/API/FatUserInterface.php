<?php

namespace Moop\Bundle\FatSecretBundle\API;

use Moop\Bundle\OAuthBundle\Decorator\oAuthConsumerInterface;

/**
 * Interface for getting and setting FatSecret's OAuth tokens.
 * 
 * @author Austin Shinpaugh
 */
interface FatUserInterface extends oAuthConsumerInterface { }
