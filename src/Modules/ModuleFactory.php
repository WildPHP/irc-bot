<?php
/*
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

declare(strict_types=1);

namespace WildPHP\Core\Modules;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class ModuleFactory
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ModuleFactory constructor.
     *
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->container = $container;
    }

    /**
     * @param array $entryClassNames
     * @return array list of initialized modules
     * @throws ModuleInitializationException
     */
    public function initializeModules(array $entryClassNames): array
    {
        $initialized = [];
        foreach ($entryClassNames as $entryClassName) {
            $initialized[] = $this->initializeModule($entryClassName);
        }
        return $initialized;
    }

    /**
     * @param string $entryClassName
     *
     * @return object
     * @throws ModuleInitializationException
     */
    public function initializeModule(string $entryClassName)
    {
        $this->logger->debug('Initializing module...', [
            'class' => $entryClassName
        ]);
        if (!class_exists($entryClassName)) {
            throw new ModuleInitializationException('The given class does not exist.');
        }

        try {
            $object = $this->container->get($entryClassName);
        } catch (Throwable $exception) {
            throw new ModuleInitializationException(
                'An exception occurred when initializing the module',
                0,
                $exception
            );
        }

        $this->logger->debug('Initialized module', [
            'class' => $entryClassName
        ]);
        return $object;
    }
}
