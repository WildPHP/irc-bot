<?php

/**
 * Copyright 2020 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Tests\Configuration;

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Configuration\Configuration;
use WildPHP\Core\Configuration\PhpBackend;

class ConfigurationTest extends TestCase
{
    public function testNeonBackend()
    {
        $path = __DIR__ . '/testconfig.php';
        $neonBackend = new PhpBackend($path);
        static::assertEquals($path, $neonBackend->getConfigFile());

        $allEntries = $neonBackend->getAllEntries();
        static::assertEquals(
            [
                'test' => [
                    'ing' => 'data',
                    'array' => ['test', 'ing']
                ],
                'owner' => 'SomeUser'
            ],
            $allEntries
        );
    }

    public function testNeonBackendFileDoesNotExist()
    {
        $path = __DIR__ . '/nonexistingfile.php';

        $this->expectException(\RuntimeException::class);
        $neonBackend = new PhpBackend($path);
    }

    public function testConfigurationStorage()
    {
        $neonBackend = new PhpBackend(dirname(__FILE__) . '/testconfig.php');
        $configurationStorage = new Configuration($neonBackend);
        self::assertEquals($neonBackend, $configurationStorage->getBackend());

        $configurationItem = $configurationStorage['test']['ing'];
        $expected = 'data';

        static::assertEquals($expected, $configurationItem);
    }

    public function testPutConfigurationStorage()
    {
        $configurationStorage = new Configuration(new PhpBackend(dirname(__FILE__) . '/testconfig.php'));

        $configurationStorage['testmore'] = 'Test';
        $expected = 'Test';

        $configurationItem = $configurationStorage['testmore'];

        static::assertEquals($expected, $configurationItem);
    }
}