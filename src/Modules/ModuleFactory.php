<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Modules;

use Composer\Semver\Semver;
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
     * @throws ModuleInitializationException
     * @throws \ReflectionException
     * @throws \Yoshi2889\Container\ContainerException
     * @throws \Yoshi2889\Container\NotFoundException
     */
    public function initializeModules(array $entryClassNames)
    {
        foreach ($entryClassNames as $entryClassName) {
            $this->initializeModule($entryClassName);
        }
    }

    /**
     * @param string $entryClassName
     *
     * @return ModuleInterface
     * @throws ModuleInitializationException
     * @throws \Yoshi2889\Container\ContainerException
     * @throws \Yoshi2889\Container\NotFoundException
     * @throws \ReflectionException
     * @throws \Yoshi2889\Container\ContainerException
     */
    public function initializeModule(string $entryClassName)
    {
        if (!class_exists($entryClassName)) {
            throw new ModuleInitializationException('The given class does not exist.');
        }

        if ($this->loadedModules->has($entryClassName)) {
            throw new ModuleInitializationException('Cannot initialize modules twice!');
        }

        if (!$this->validateModuleInterface($entryClassName)) {
            throw new ModuleInitializationException('The given class is not a (valid) WildPHP module!');
        }

        if (!$this->dependenciesSatisfied($entryClassName)) {
            throw new ModuleInitializationException('The given module does not have all its dependencies satisfied!');
        }

        /** @noinspection PhpUndefinedMethodInspection */
        if (!Semver::satisfies(WPHP_VERSION, $entryClassName::getSupportedVersionConstraint())) {
            throw new ModuleInitializationException('This module does not support this version of WildPHP!');
        }

        try {
            $object = new $entryClassName($this->getContainer());
        } catch (\Throwable $exception) {
            throw new ModuleInitializationException('An exception occurred when initializing the module', 0,
                $exception);
        }

        Logger::fromContainer($this->getContainer())->debug('Initialized module', [
            'class' => $entryClassName
        ]);

        $this->loadedModules->add($object);
        return $object;
    }

    /**
     * @param string $moduleClass
     * @return bool
     * @throws \ReflectionException
     */
    public function validateModuleInterface(string $moduleClass)
    {
        $reflection = new \ReflectionClass($moduleClass);

        return $reflection->implementsInterface(ModuleInterface::class);
    }

    /**
     * @param string $moduleClass
     * @return bool
     * @throws \ReflectionException
     * @throws \Yoshi2889\Container\ContainerException
     */
    public function dependenciesSatisfied(string $moduleClass): bool
    {
        if (!$this->validateModuleInterface($moduleClass))
            return false;

        /** @var BaseModule $moduleClass */
        $dependencies = $moduleClass::getDependentModules();

        foreach ($dependencies as $dependency) {
            if (!$this->getContainer()->has($dependency))
                return false;
        }

        return true;
    }

    /**
     * @param string $class
     *
     * @return false|object
     * @throws \Yoshi2889\Container\ContainerException
     */
    public function getModuleInstance(string $class)
    {
        if (!$this->isModuleLoaded($class)) {
            return false;
        }

        return $this->loadedModules->get($class);
    }

    /**
     * @param string $class
     *
     * @return bool
     * @throws \Yoshi2889\Container\ContainerException
     */
    public function isModuleLoaded(string $class): bool
    {
        return $this->loadedModules->has($class);
    }
}