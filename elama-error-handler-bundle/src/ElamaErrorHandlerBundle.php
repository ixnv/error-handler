<?php

namespace eLama\ErrorHandler\Bundle;

use eLama\ErrorHandler\Bundle\DependencyInjection\ErrorHandlerExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ElamaErrorHandlerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->registerExtension(new ErrorHandlerExtension());

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/Resources/config'));
        $loader->load('error-handler.yml');
    }
}
