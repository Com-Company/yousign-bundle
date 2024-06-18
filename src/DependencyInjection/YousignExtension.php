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
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $this->registerHandlers($configs, $container);
        $loader->load('services.yaml');
    }

    /** @param array<string, mixed> $configs */
    private function registerHandlers(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        if (!$config['eventHandlers']) {
            return;
        }
        $defaultHandler = $config['eventHandlers']['default'] ?? null;
        $eventMapping = array_reduce(
            $config['eventHandlers']['bindings'] ?? [],
            fn ($res, $elt) => array_merge($res, [$elt['event'] => $elt['service']]),
            []
        );

        if ($defaultHandler) {
            $definition = $container->register('yousign.default_handler');
            $definition->addTag('default_handler');
            $definition->setAutowired(true);
            $definition->setAutoconfigured(true);
            $definition->setPublic(false);
            $definition->setClass($defaultHandler);
        }

        foreach ($eventMapping as $event => $service) {
            $definition = $container->register('yousign.event_handler.'.$event);
            $definition->addTag('event_handler', ['event' => $event]);
            $definition->setAutowired(true);
            $definition->setAutoconfigured(true);
            $definition->setPublic(false);
            $definition->setClass($service);
        }
    }
}
