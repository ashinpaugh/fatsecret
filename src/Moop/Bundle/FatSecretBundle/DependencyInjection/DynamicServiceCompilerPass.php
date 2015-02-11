<?php

namespace Moop\Bundle\FatSecretBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DynamicServiceCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $cache_id  = $container->getParameter('moop.fs.cache.provider.id');
        
        $container->setDefinition(
            'moop.fat_secret.cache',
            $container->getDefinition($cache_id)
        );
    }
}
