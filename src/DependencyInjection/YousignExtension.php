<?php

namespace ComCompany\YousignBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class YousignExtension extends Extension
{
    /** @param array<string, mixed> $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $eventMapping = array_reduce(
            $config['eventHandlers'],
            fn ($res, $elt) => array_merge($res, [$elt['event'] => $elt['service']]),
            []
        );

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $definitions = [];
        foreach ($eventMapping as $event => $service) {
            $definition = $container->register('yousign.event_handler.'.$event);
            $definition->addTag('event_handler', ['event' => $event]);
            $definition->setAutowired(true);
            $definition->setAutoconfigured(true);
            $definition->setPublic(false);
            $definition->setClass($service);
            $definitions[(string) $service] = $definition;
        }

        $container->addDefinitions($definitions);

        $loader->load('services.yaml');
    }
}
