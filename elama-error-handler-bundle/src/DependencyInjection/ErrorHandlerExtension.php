<?php

namespace eLama\ErrorHandler\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class ErrorHandlerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration;
        $config = $processor->processConfiguration($configuration, $configs);

        $container->setParameter('error_handler.matchers', $config['matchers']);
    }
}
