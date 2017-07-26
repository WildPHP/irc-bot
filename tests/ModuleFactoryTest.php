<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Modules\ModuleFactory;
use WildPHP\Core\Modules\ModuleInitializationException;

class ModuleFactoryTest extends TestCase
{
	/**
	 * @return \WildPHP\Core\ComponentContainer
	 */
	public function initContainer(): \WildPHP\Core\ComponentContainer
	{
		$componentContainer = new \WildPHP\Core\ComponentContainer();
		$componentContainer->add(new \WildPHP\Core\Logger\Logger('wildphp'));
		return $componentContainer;
	}

	/**
	 * @param $componentContainer
	 *
	 * @return ModuleFactory
	 */
	public function init($componentContainer): ModuleFactory
	{
		if (!defined('WPHP_VERSION'))
			define('WPHP_VERSION', '3.0.0');


		return new ModuleFactory($componentContainer);
	}

	public function testNonExistingClass()
	{
		$mf = $this->init($this->initContainer());

		$this->expectException(ModuleInitializationException::class);
		$mf->initializeModule('tlskafjioweajklsjfiowlajkefolaskdfa');
	}

	public function testClassNotImplementingInterface()
	{
		$mf = $this->init($this->initContainer());

		$this->expectException(ModuleInitializationException::class);
		$mf->initializeModule(stdClass::class);
	}

	public function testClassNotMeetingVersionConstraint()
	{
		$mf = $this->init($this->initContainer());

		$this->expectException(ModuleInitializationException::class);
		$mf->initializeModule(ModuleNotMeetingVersionConstraint::class);
	}

	public function testValidModule()
	{
		$mf = $this->init($this->initContainer());

		$mf->initializeModule(ValidModule::class);
		self::assertTrue($mf->isModuleLoaded(ValidModule::class));

		// can't initiate a module twice
		$this->expectException(ModuleInitializationException::class);
		$mf->initializeModule(ValidModule::class);
	}

	public function testModuleThrowsException()
	{
		$mf = $this->init($this->initContainer());

		$this->expectException(ModuleInitializationException::class);
		$mf->initializeModule(ModuleThrowsException::class);
	}

	public function testInitializeMultipleModules()
	{
		$modules = [
			ValidModule::class,
			ValidModule2::class
		];

		$mf = $this->init($this->initContainer());
		$mf->initializeModules($modules);
		self::assertTrue($mf->isModuleLoaded(ValidModule::class));
		self::assertTrue($mf->isModuleLoaded(ValidModule2::class));
	}

	public function testGetInstance()
	{
		$container = $this->initContainer();
		$mf = $this->init($container);

		$mf->initializeModule(ValidModule::class);
		self::assertTrue($mf->isModuleLoaded(ValidModule::class));

		$validModule = new ValidModule($container);

		self::assertEquals($validModule, $mf->getModuleInstance(ValidModule::class));
		self::assertFalse($mf->getModuleInstance(ValidModule2::class));
	}
}

class ModuleNotMeetingVersionConstraint extends \WildPHP\Core\Modules\BaseModule
{
	/**
	 * ModuleNotMeetingVersionConstraint constructor.
	 *
	 * @param \WildPHP\Core\ComponentContainer $container
	 */
	public function __construct(\WildPHP\Core\ComponentContainer $container)
	{

	}

	/**
	 * @return string
	 */
	public static function getSupportedVersionConstraint(): string
	{
		return '2.9.9';
	}
}

class ValidModule extends \WildPHP\Core\Modules\BaseModule
{
	/**
	 * ValidModule constructor.
	 *
	 * @param \WildPHP\Core\ComponentContainer $container
	 */
	public function __construct(\WildPHP\Core\ComponentContainer $container)
	{

	}

	/**
	 * @return string
	 */
	public static function getSupportedVersionConstraint(): string
	{
		return '3.0.0';
	}
}

class ValidModule2 extends \WildPHP\Core\Modules\BaseModule
{
	/**
	 * ValidModule2 constructor.
	 *
	 * @param \WildPHP\Core\ComponentContainer $container
	 */
	public function __construct(\WildPHP\Core\ComponentContainer $container)
	{

	}

	/**
	 * @return string
	 */
	public static function getSupportedVersionConstraint(): string
	{
		return '3.0.0';
	}
}

class ModuleThrowsException extends \WildPHP\Core\Modules\BaseModule
{
	/**
	 * ModuleThrowsException constructor.
	 *
	 * @param \WildPHP\Core\ComponentContainer $container
	 */
	public function __construct(\WildPHP\Core\ComponentContainer $container)
	{
		throw new InvalidArgumentException();
	}

	/**
	 * @return string
	 */
	public static function getSupportedVersionConstraint(): string
	{
		return '3.0.0';
	}
}