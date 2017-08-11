<?php

namespace Moop\Bundle\FatSecretBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MoopFatSecretExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        $container->setParameter('moop.fs.api_base_url',    $config['api_base_url']);
        $container->setParameter('moop.fs.consumer_key',    $config['consumer_key']);
        $container->setParameter('moop.fs.consumer_secret', $config['consumer_secret']);
        
        $this->rLoad($container, 'moop.fs.cache.', $config['cache_providers']);
        
        // {@see DynamicServiceCompilerPass}
        $container->setParameter(
            'moop.fs.cache.provider_id',
            vsprintf("moop.fs.cache.%s", [$config['cache_provider_type']])
        );
        
        
    }
    
    /**
     * @param ContainerBuilder $container
     * @param String           $prefix
     * @param array            $params
     */
    protected function rLoad(ContainerBuilder $container, $prefix, array $params)
    {
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $this->rLoad($container, $prefix . "$key.", $value);
                continue;
            }
            
            if (!$container->hasParameter($prefix . $key)) {
                $container->setParameter($prefix . $key, $value);
            }
        }
    }

}
