<?php

namespace Moop\Bundle\FatSecretBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('moop_fat_secret');
        
        $rootNode
            ->children()
                ->variableNode('api_base_url')
                    ->cannotBeEmpty()
                    ->defaultValue('http://platform.fatsecret.com/rest/server.api')
                    ->info('The FatSecret API Endpoint.')
                ->end()
            
                ->variableNode('consumer_key')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info('The FatSecret Consumer Key.')
                ->end()
                
                ->variableNode('consumer_secret')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info('The FatSecret Consumer Secret.')
                ->end()
            ->end()
        ;
        
        return $treeBuilder;
    }
}
