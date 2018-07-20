<?php

namespace App\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class CalibreConnectionCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $connection = $container
            ->getDefinition('doctrine.dbal.dynamic_connection')
            ->addMethodCall('setSession', [
                new Reference('session')
            ]);
    }
}