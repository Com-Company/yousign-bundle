<?php

namespace ComCompany\YousignBundle\DependencyInjection;

use ComCompany\YousignBundle\Service\WebhookManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EventHandlerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(WebhookManager::class)) {
            return;
        }

        $definition = $container->getDefinition(WebhookManager::class);
        $taggedServices = $container->findTaggedServiceIds('event_handler');

        foreach ($taggedServices as $id => $tags) {
            $event = $tags[0]['event'] ?? false;
            if ($event) {
                $definition->addMethodCall(
                    'addEventHandler',
                    [$event, new Reference($id)]
                );
            }
        }
    }
}
