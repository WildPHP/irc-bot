<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Modules;

use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Logger\Logger;
use Yoshi2889\Container\ComponentInterface;
use Yoshi2889\Container\ComponentTrait;
use Yoshi2889\Container\ContainerTrait;

class ModuleFactory implements ComponentInterface
{
	use ContainerTrait;
	use ComponentTrait;

	/**
	 * @var ComponentContainer
	 */
	protected $loadedModules;

	/**
	 * ModuleFactory constructor.
	 *
	 * @param ComponentContainer $container
	 */
	public function __construct(ComponentContainer $container)
	{
		$this->setContainer($container);
		$this->loadedModules = new ComponentContainer();
	}

	/**
	 * @param array $entryClassNames
	 */
	public function initializeModules(array $entryClassNames)
	{
		foreach ($entryClassNames as $entryClassName)
		{
			$this->initializeModule($entryClassName);
		}
	}

	/**
	 * @param string $entryClassName
	 *
	 * @return ModuleInterface
	 * @throws ModuleInitializationException
	 */
	public function initializeModule(string $entryClassName)
	{
		if (!class_exists($entryClassName))
			throw new ModuleInitializationException('The given class does not exist.');

		if ($this->loadedModules->has($entryClassName))
			throw new ModuleInitializationException('Cannot initialize modules twice!');

		$reflection = new \ReflectionClass($entryClassName);

		if (!$reflection->implementsInterface(ModuleInterface::class))
			throw new ModuleInitializationException('The given class is not a (valid) WildPHP module!');

		try
		{
			$object = new $entryClassName($this->getContainer());
		}
		catch (\Throwable $exception)
		{
			throw new ModuleInitializationException('An exception occurred when initializing the module', 0, $exception);
		}

		Logger::fromContainer($this->getContainer())->debug('Initialized module', [
			'class' => $entryClassName
		]);

		$this->loadedModules->add($object);
		return $object;
	}
}