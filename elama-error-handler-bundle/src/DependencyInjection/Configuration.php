<?php

namespace eLama\ErrorHandler\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @var string
     */
    private $logDir;

    /**
     * @param string $logDir
     */
    public function __constructor($logDir)
    {
        $this->logDir = $logDir;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('error_handler');

        $rootNode
            ->children()
                ->scalarNode("log_path")->end()
                ->scalarNode("logger")->end()
                ->arrayNode('matchers')
                    ->children()
                        ->arrayNode('fatal')
                            ->children()
                                ->booleanNode('handle')
                                    ->defaultValue(true)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('exception')
                            ->children()
                                ->booleanNode('handle')
                                    ->defaultValue(true)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('user_error')
                            ->children()
                                ->booleanNode('handle')
                                    ->defaultValue(true)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('unknown_file')
                            ->children()
                                ->booleanNode('handle')
                                    ->defaultValue(true)
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('file_paths')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('path')->end()
                                    ->booleanNode('handle')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('code_messages')
                            ->prototype('array')
                                ->children()
                                    ->enumNode('error_code')
                                        ->values([
                                            'E_ERROR',
                                            'E_WARNING',
                                            'E_NOTICE',
                                            'E_COMPILE_WARNING',
                                            'E_USER_WARNING',
                                            'E_USER_NOTICE',
                                            'E_RECOVERABLE_ERROR',
                                            'E_DEPRECATED',
                                            'E_USER_DEPRECATED'
                                        ])
                                    ->end()
                                    ->scalarNode('message')->end()
                                    ->booleanNode('handle')->defaultValue(false)->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
