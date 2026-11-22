<?php

declare(strict_types=1);

namespace EMS\TableBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class EMSTableBundle extends AbstractBundle
{
    protected string $extensionAlias = 'ems_table';

    #[\Override]
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()->children()->end();
    }

    /** @param array<string, mixed> $config */
    #[\Override]
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');
    }
}
