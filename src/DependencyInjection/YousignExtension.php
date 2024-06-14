<?php

namespace ComCompany\YousignBundle\DependencyInjection;

use App\Service\Supplier\Api\Advenis\Subscription\Initial\Bulletin;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class YousignExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $eventMapping = array_reduce(
            $config['eventHandlers'],
            fn($res, $elt) => array_merge($res, [$elt['event'] => $elt['service']]),
            []
        );

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $definitions = [];
        foreach($eventMapping as $event => $service) {
            $definition = $container->register('yousign.event_handler.'.$event);
            $definition->addTag('event_handler', ['event' => $event]);
            $definition->setAutowired(true);
            $definition->setAutoconfigured(true);
            $definition->setPublic(false);
            $definition->setClass($service);
            $definitions[$service] = $definition;
        }

        $container->addDefinitions($definitions);

        $loader->load('services.yaml');
    }
}
