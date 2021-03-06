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
                ->append($this->buildCacheProviderNode())
                
                ->scalarNode('cache_provider_type')
                    ->cannotBeEmpty()
                    ->defaultValue('array')
                    ->info('The cache provider. You can build your own service, or use the built in providers: array or redis. Default: array.')
                ->end()
                
                ->scalarNode('api_base_url')
                    ->cannotBeEmpty()
                    ->defaultValue('http://platform.fatsecret.com/rest/server.api')
                    ->info("FatSecret's API endpoint.")
                ->end()
                
                ->scalarNode('consumer_key')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info("The oAuth consumer key that FatSecret generated for your application.")
                ->end()
                
                ->scalarNode('consumer_secret')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info("The oAuth consumer secret that FatSecret generated for your application.")
                ->end()
            ->end()
        ;
        
        return $treeBuilder;
    }
    
    /**
     * Build the supported list of cache providers.
     * 
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    protected function buildCacheProviderNode()
    {
        $builder = new TreeBuilder();
        $root    = $builder->root('cache_providers');
        
        $root
            ->prototype('array')
                ->children()
                    ->scalarNode('id')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('class')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('host')->end()
                    ->scalarNode('port')->end()
                    ->scalarNode('username')->end()
                    ->scalarNode('password')->end()
                ->end()
            ->end()
        ;
        
        return $root;
    }
}
