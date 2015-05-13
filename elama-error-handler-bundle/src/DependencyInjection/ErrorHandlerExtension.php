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
        $configuration = new Configuration($container->getParameter('kernel.logs_dir'));
        $config = $processor->processConfiguration($configuration, $configs);

        $container->setParameter('error_handler.matchers', $config['matchers']);
        $container->setParameter('error_handler.log_path', $config['log_path']);

        if ($config['logger'] !== null) {
            $container->setParameter('error_handler.logger', $config['logger']);
        }
    }
}
