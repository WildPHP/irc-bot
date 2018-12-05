<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

use PHPUnit\Framework\TestCase;
use WildPHP\Core\ComponentContainer;
use WildPHP\Core\EventEmitter;
use WildPHP\Core\Logger\Logger;
use WildPHP\Core\Observers\CapabilityHandler;
use WildPHP\Core\Observers\IRCMessages\CAP;
use WildPHP\Core\Observers\Queue;
use WildPHP\Core\Observers\QueueItem;

class CapabilityHandlerTest extends TestCase
{
	/**
	 * @var ComponentContainer
	 */
	protected $componentContainer;
	
	public function setUp()
	{
		$this->componentContainer = new ComponentContainer();
		$this->componentContainer->add(new EventEmitter());
		$this->componentContainer->add(new Logger('wildphp'));
		$this->componentContainer->add(new Queue());
	}

	public function testRequestCapabilities()
	{
		$capabilityHandler = new CapabilityHandler($this->componentContainer);
		Queue::fromContainer($this->componentContainer)->flush();
		
		self::assertFalse($capabilityHandler->canEndNegotiation());
		self::assertFalse($capabilityHandler->requestCapability('extended-join'));
		
		$cap = new CAP('LS', ['extended-join', 'account-notify']);
		EventEmitter::fromContainer($this->componentContainer)->emit('irc.line.in.cap', [$cap, Queue::fromContainer($this->componentContainer)]);
		
		self::assertTrue($capabilityHandler->isCapabilityAvailable('extended-join'));
		self::assertTrue($capabilityHandler->isCapabilityAvailable('account-notify'));
		self::assertFalse($capabilityHandler->isCapabilityAvailable('multi-prefix'));
		self::assertEquals(['extended-join', 'account-notify'], $capabilityHandler->getAvailableCapabilities());
		
		self::assertEquals(1, Queue::fromContainer($this->componentContainer)->count());
		
		$cap = new CAP('REQ', ['extended-join', 'account-notify']);
		self::assertEquals([new QueueItem($cap, time())], Queue::fromContainer($this->componentContainer)->flush());
	}

	public function testAcknowledgeCapabilities()
	{
		$capabilityHandler = new CapabilityHandler($this->componentContainer);
		Queue::fromContainer($this->componentContainer)->flush();
		
		$cap = new CAP('ACK', ['extended-join']);
		
		EventEmitter::fromContainer($this->componentContainer)->on('irc.cap.acknowledged', function (array $capabilities) use ($capabilityHandler)
		{
			self::assertEquals(['extended-join'], $capabilities);
			self::assertEquals(['extended-join'], $capabilityHandler->getAcknowledgedCapabilities());
			self::assertTrue($capabilityHandler->isCapabilityAcknowledged('extended-join'));
			self::assertFalse($capabilityHandler->isCapabilityAcknowledged('account-notify'));
		});
		EventEmitter::fromContainer($this->componentContainer)->emit('irc.line.in.cap', [$cap, Queue::fromContainer($this->componentContainer)]);
	}

	public function testNotAcknowledgeCapabilities()
	{
		$capabilityHandler = new CapabilityHandler($this->componentContainer);
		Queue::fromContainer($this->componentContainer)->flush();

		$cap = new CAP('NAK', ['extended-join']);

		EventEmitter::fromContainer($this->componentContainer)->on('irc.cap.notAcknowledged', function (array $capabilities) use ($capabilityHandler)
		{
			self::assertEquals(['extended-join'], $capabilities);
			self::assertEquals(['extended-join'], $capabilityHandler->getNotAcknowledgedCapabilities());
			self::assertFalse($capabilityHandler->isCapabilityAcknowledged('extended-join'));
			self::assertFalse($capabilityHandler->isCapabilityAcknowledged('account-notify'));
		});
		EventEmitter::fromContainer($this->componentContainer)->emit('irc.line.in.cap', [$cap, Queue::fromContainer($this->componentContainer)]);
	}

	public function testEndNegotiation()
	{
		$capabilityHandler = new CapabilityHandler($this->componentContainer);
		Queue::fromContainer($this->componentContainer)->flush();
		
		self::assertFalse($capabilityHandler->tryEndNegotiation());
		self::assertFalse($capabilityHandler->canEndNegotiation());
		
		$cap = new CAP('ACK', ['extended-join', 'account-notify', 'multi-prefix']);
		EventEmitter::fromContainer($this->componentContainer)->emit('irc.line.in.cap', [$cap, Queue::fromContainer($this->componentContainer)]);

		self::assertFalse($capabilityHandler->tryEndNegotiation());
		self::assertFalse($capabilityHandler->canEndNegotiation());
		
		$capabilityHandler->setSaslHasCompleted();
		
		self::assertTrue($capabilityHandler->tryEndNegotiation());
		self::assertTrue($capabilityHandler->canEndNegotiation());
	}

	public function testIsCompatible()
	{
		if (!defined('WPHP_VERSION'))
			define('WPHP_VERSION', '3.0.0');

		self::assertEquals(WPHP_VERSION, CapabilityHandler::getSupportedVersionConstraint());
	}
}
