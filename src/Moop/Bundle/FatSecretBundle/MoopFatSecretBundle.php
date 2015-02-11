<?php

namespace Moop\Bundle\FatSecretBundle;

use Moop\Bundle\FatSecretBundle\DependencyInjection\DynamicServiceCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MoopFatSecretBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        
        $container->addCompilerPass(new DynamicServiceCompilerPass());
    }
}
