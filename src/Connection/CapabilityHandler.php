<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2016 WildPHP

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace WildPHP\Core\Connection;


use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\Timer;
use WildPHP\Core\Events\EventEmitter;
use WildPHP\Core\Logger\Logger;

class CapabilityHandler
{
	/**
	 * @var array
	 */
	protected static $availableCapabilities = [];

	/**
	 * @var array
	 */
	protected static $acquiredCapabilities = [];

	public static function initialize(LoopInterface $loopInterface)
	{
		EventEmitter::on('stream.created', __NAMESPACE__ . '\CapabilityHandler::initNegotiation');
		EventEmitter::on('irc.line.in.cap', __NAMESPACE__ . '\CapabilityHandler::responseRouter');
		EventEmitter::on('irc.cap.ls', function (array $capabilities, Queue $queue) use ($loopInterface)
		{
			if (in_array('extended-join', $capabilities))
				$queue->cap('REQ :extended-join');

			if (in_array('account-notify', $capabilities))
				$queue->cap('REQ :account-notify');

			if (in_array('multi-prefix', $capabilities))
				$queue->cap('REQ :multi-prefix');

			$loopInterface->addPeriodicTimer(1, function (Timer $timer) use ($queue)
			{
				$canContinue = true;
				EventEmitter::emit('irc.cap.continue', [&$canContinue]);

				if ($canContinue)
				{
					$timer->cancel();
					$queue->cap('END');
					EventEmitter::emit('irc.cap.after', [$queue]);
				}
			});
		});
	}

	/**
	 * @param Queue $queue
	 */
	public static function initNegotiation(Queue $queue)
	{
		Logger::debug('Capability negotiation, stage 1...');
		$queue->cap('LS');
	}

	/**
	 * @param string $capability
	 *
	 * @return bool
	 */
	public static function capabilityExists(string $capability): bool
	{
		return in_array($capability, self::$availableCapabilities);
	}

	/**
	 * @param string $capability
	 *
	 * @return bool
	 */
	public static function isCapabilityActive(string $capability): bool
	{
		return in_array($capability, self::$acquiredCapabilities);
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 * @param Queue $queue
	 */
	public static function responseRouter(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		$args = $incomingIrcMessage->getArgs();
		$responseCommand = $args[1];

		switch ($responseCommand)
		{
			case 'LS':
				self::updateAvailableCapabilities($args[2], $queue);
				break;

			case 'ACK':
				self::updateAcquiredCapabilities($args[2], $queue);
				break;
		}
	}

	/**
	 * @param string $capabilities
	 * @param Queue $queue
	 */
	protected static function updateAvailableCapabilities(string $capabilities, Queue $queue)
	{
		$capabilities = explode(' ', trim($capabilities));
		self::$availableCapabilities = $capabilities;
		EventEmitter::emit('irc.cap.ls', [self::$availableCapabilities, $queue]);
	}

	/**
	 * @param string $capabilities
	 * @param Queue $queue
	 */
	public static function updateAcquiredCapabilities(string $capabilities, Queue $queue)
	{
		$capabilities = explode(' ', trim($capabilities));
		$acqCapabilities = array_unique(array_merge(self::$acquiredCapabilities, $capabilities));
		self::$acquiredCapabilities = $acqCapabilities;
		EventEmitter::emit('irc.cap.acquired', [$acqCapabilities, $queue]);
	}
}