<?php
/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Modules;

use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use WildPHP\Core\Modules\ModuleFactory;
use WildPHP\Core\Modules\ModuleInitializationException;

class ModuleFactoryTest extends TestCase
{
    protected $moduleFactory;

    protected function setUp(): void
    {
        $container = ContainerBuilder::buildDevContainer();
        $this->moduleFactory = new ModuleFactory($container, new NullLogger());
    }

    public function testInitializeModule()
    {
        $object = $this->moduleFactory->initializeModule(EmptyModule::class);

        $this->assertInstanceOf(EmptyModule::class, $object);
    }

    public function testInitializeModuleWithNonExistantClass()
    {
        $this->expectException(ModuleInitializationException::class);

        $this->moduleFactory->initializeModule('TestClass');
    }

    public function testInitializeBrokenModule()
    {
        $this->expectException(ModuleInitializationException::class);

        $this->moduleFactory->initializeModule(BrokenModule::class);
    }

    public function testInitializeModules()
    {
        $list = [
            EmptyModule::class,
            AnotherEmptyModule::class
        ];

        $modules = $this->moduleFactory->initializeModules($list);

        $this->assertInstanceOf(EmptyModule::class, $modules[0]);
        $this->assertInstanceOf(AnotherEmptyModule::class, $modules[1]);
    }
}
