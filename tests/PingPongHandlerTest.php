<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\Connection\IRCMessages\PING;
use WildPHP\Core\Connection\PingPongHandler;
use WildPHP\Core\Connection\Queue;
use WildPHP\Core\EventEmitter;

class PingPongHandlerTest extends TestCase
{
	protected $container;

	public function setUp()
	{
		$container = new \WildPHP\Core\ComponentContainer();
		$container->add(new Queue());
		$container->add(new EventEmitter());
		$container->setLoop(\React\EventLoop\Factory::create());
		$neonBackend = new \WildPHP\Core\Configuration\NeonBackend(dirname(__FILE__) . '/emptyconfig.neon');
		$configuration = new \WildPHP\Core\Configuration\Configuration($neonBackend);
		$container->add($configuration);
		$container->add(new \WildPHP\Core\Logger\Logger('wildphp'));
		
		$configuration['serverConfig'] = [
			'hostname' => 'test'
		];
		
		$this->container = $container;
	}

	public function testIncomingMessage()
	{
		$pingPongHandler = new PingPongHandler($this->container);
		
		self::assertEquals(time(), $pingPongHandler->getLastMessageReceivedTime());
		sleep(1);
		EventEmitter::fromContainer($this->container)->emit('irc.line.in');
		self::assertEquals(time(), $pingPongHandler->getLastMessageReceivedTime());
	}

	public function testIncomingPing()
	{
		$pingPongHandler = new PingPongHandler($this->container);

		self::assertEquals(time(), $pingPongHandler->getLastMessageReceivedTime());
		$ping = new PING('test');
		EventEmitter::fromContainer($this->container)->emit('irc.line.in.ping', [$ping, Queue::fromContainer($this->container)]);
		self::assertEquals(time(), $pingPongHandler->getLastMessageReceivedTime());
		self::assertEquals(1, Queue::fromContainer($this->container)->count());
	}
}
