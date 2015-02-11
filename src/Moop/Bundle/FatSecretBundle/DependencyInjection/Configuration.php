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
        $rootNode    = $treeBuilder->root('moop_fat_secret');
        
        $rootNode
            ->children()
                ->arrayNode('cache_providers')
                    ->children()
                        ->append($this->buildCacheProviderNode('array'))
                        ->append($this->buildCacheProviderNode('redis'))
                    ->end()
                ->end()
                
                ->scalarNode('cache_provider_type')
                    ->cannotBeEmpty()
                    ->defaultValue('array')
                    ->info('The cache provider name.')
                ->end()
                
                ->scalarNode('api_base_url')
                    ->cannotBeEmpty()
                    ->defaultValue('http://platform.fatsecret.com/rest/server.api')
                    ->info('The FatSecret API Endpoint.')
                ->end()
                
                ->scalarNode('consumer_key')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info('The FatSecret Consumer Key.')
                ->end()
                
                ->scalarNode('consumer_secret')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info('The FatSecret Consumer Secret.')
                ->end()
            ->end()
        ;
        
        return $treeBuilder;
    }
    
    /**
     * Build the supported list of cache providers.
     * 
     * @param String $type
     *
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    protected function buildCacheProviderNode($type)
    {
        $tree = new TreeBuilder();
        $root = $tree->root($type);
        
        $root
            ->children()
                ->scalarNode('class')
                    ->cannotBeEmpty()
                ->end()
                
                ->scalarNode('host')->defaultValue('localhost')->end()
                ->scalarNode('port')->end()
                ->scalarNode('username')->defaultValue('root')->end()
                ->scalarNode('password')->end()
            ->end()
            ->info('A Doctrine Cache provider to minimize API calls made for similar queries.')
        ;
        
        return $root;
    }
}
