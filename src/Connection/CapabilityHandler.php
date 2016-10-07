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
    protected static $capabilitiesToRequest = [];

	/**
	 * @var array
	 */
	protected static $acknowledgedCapabilities = [];

    /**
     * @var array
     */
    protected static $notAcknowledgedCapabilities = [];

	public static function initialize(LoopInterface $loopInterface)
	{
		EventEmitter::on('stream.created', __NAMESPACE__ . '\CapabilityHandler::initNegotiation');
		EventEmitter::on('irc.line.in.cap', __NAMESPACE__ . '\CapabilityHandler::responseRouter');
		EventEmitter::on('irc.cap.ls', function (array $capabilities, Queue $queue) use ($loopInterface)
		{
			if (in_array('extended-join', $capabilities))
				self::requestCapability('extended-join');

			if (in_array('account-notify', $capabilities))
				self::requestCapability('account-notify');

			if (in_array('multi-prefix', $capabilities))
				self::requestCapability('multi-prefix');

            SASL::initialize($queue);
		});
        EventEmitter::on('irc.cap.ls.after', __NAMESPACE__ . '\CapabilityHandler::flushRequestQueue');
        EventEmitter::on('irc.cap.acknowledged', __NAMESPACE__ . '\CapabilityHandler::tryEndNegotiation');
        EventEmitter::on('irc.cap.notAcknowledged', __NAMESPACE__ . '\CapabilityHandler::tryEndNegotiation');
        EventEmitter::on('irc.sasl.complete', __NAMESPACE__ . '\CapabilityHandler::tryEndNegotiation');
        EventEmitter::on('irc.sasl.error', __NAMESPACE__ . '\CapabilityHandler::tryEndNegotiation');
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
     * @param Queue $queue
     */
	public static function flushRequestQueue(Queue $queue)
    {
        $queue->cap('REQ :' . implode(' ', self::$capabilitiesToRequest));
    }

    /**
     * @param array $capabilities
     * @param Queue $queue
     */
	public static function tryEndNegotiation(array $capabilities, Queue $queue)
    {
        if (!self::canEndNegotiation())
            return;

        $queue->cap('END');
        EventEmitter::emit('irc.cap.end', [$queue]);
    }

    public static function requestCapability(string $capability)
    {
        if (!self::isCapabilityAvailable($capability))
            return false;

        if (self::isCapabilityAcknowledged($capability))
            return true;

        if (in_array($capability, self::$capabilitiesToRequest))
            return true;

        self::$capabilitiesToRequest[] = $capability;
        return true;
    }

	/**
	 * @param string $capability
	 *
	 * @return bool
	 */
	public static function isCapabilityAvailable(string $capability): bool
	{
		return in_array($capability, self::$availableCapabilities);
	}

	/**
	 * @param string $capability
	 *
	 * @return bool
	 */
	public static function isCapabilityAcknowledged(string $capability): bool
	{
		return in_array($capability, self::$acknowledgedCapabilities);
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 * @param Queue $queue
	 */
	public static function responseRouter(IncomingIrcMessage $incomingIrcMessage, Queue $queue)
	{
		$args = $incomingIrcMessage->getArgs();
		$responseCommand = $args[1];
        $capability = $args[2];

		switch ($responseCommand)
		{
			case 'LS':
				self::updateAvailableCapabilities($capability, $queue);
                EventEmitter::emit('irc.cap.ls.after', [$queue]);
				break;

			case 'ACK':
			    $capabilities = explode(' ', $capability);
				self::updateAcknowledgedCapabilities($capabilities, $queue);
				break;

            case 'NAK':
                $capabilities = explode(' ', $capability);
                self::updateNotAcknowledgedCapabilities($capabilities, $queue);
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
	 * @param string[]|string $capabilities
	 * @param Queue $queue
	 */
	public static function updateAcknowledgedCapabilities($capabilities, Queue $queue)
	{
		if (is_string($capabilities))
		    $capabilities = [$capabilities];

		$ackCapabilities = array_filter(array_unique(array_merge(self::$acknowledgedCapabilities, $capabilities)));
		self::$acknowledgedCapabilities = $ackCapabilities;

		EventEmitter::emit('irc.cap.acknowledged', [$ackCapabilities, $queue]);
	}

    /**
     * @param string[]|string $capabilities
     * @param Queue $queue
     */
	public static function updateNotAcknowledgedCapabilities($capabilities, Queue $queue)
    {
        if (is_string($capabilities))
            $capabilities = [$capabilities];

        $nakCapabilities = array_filter(array_unique(array_merge(self::$notAcknowledgedCapabilities, $capabilities)));
        self::$notAcknowledgedCapabilities = $nakCapabilities;

        EventEmitter::emit('irc.cap.notAcknowledged', [$nakCapabilities, $queue]);
    }

    /**
     * @return bool
     */
    public static function canEndNegotiation(): bool
    {
        $saslIsComplete = SASL::hasCompleted();

        $reqCount = count(self::$capabilitiesToRequest);
        $ackCount = count(self::$acknowledgedCapabilities);
        $nakCount = count(self::$notAcknowledgedCapabilities);
        $handledCount = $ackCount + $nakCount;

        return $handledCount === $reqCount && $saslIsComplete;
    }
}