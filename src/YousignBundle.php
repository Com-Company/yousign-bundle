<?php

namespace ComCompany\YousignBundle;

use ComCompany\YousignBundle\DependencyInjection\EventHandlerCompilerPass;
use ComCompany\YousignBundle\DependencyInjection\YousignExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class YousignBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new EventHandlerCompilerPass());
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new YousignExtension();
        }

        return new YousignExtension();
    }
}
