<?php

declare(strict_types=1);

namespace EMS\TableBundle\Tests\Unit;

use EMS\TableBundle\EMSTableBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EMSTableBundleTest extends TestCase
{
    public function testContainerCompilesWithEmptyConfig(): void
    {
        $bundle = new EMSTableBundle();
        $extension = $bundle->getContainerExtension();
        self::assertNotNull($extension);
        self::assertSame('ems_table', $extension->getAlias());

        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');
        $container->setParameter('kernel.build_dir', \sys_get_temp_dir());
        $container->registerExtension($extension);
        $container->loadFromExtension('ems_table', []);
        $bundle->build($container);
        $container->compile();

        self::assertTrue($container->isCompiled());
    }

    public function testBundlePathIsPackageRoot(): void
    {
        $bundle = new EMSTableBundle();

        self::assertDirectoryExists($bundle->getPath().'/templates');
        self::assertDirectoryExists($bundle->getPath().'/translations');
    }
}
