<?php

namespace ComCompany\YousignBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('yousign');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('eventHandlers')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('event')->isRequired()->end()
                            ->scalarNode('service')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}