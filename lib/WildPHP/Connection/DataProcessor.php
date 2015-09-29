<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2015 WildPHP

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

namespace WildPHP\Connection;

use Evenement\EventEmitterInterface;
use Phergie\Irc\Parser;
use React\Stream\DuplexStreamInterface;
use WildPHP\Traits\EventEmitterTrait;
use WildPHP\Traits\ParserTrait;
use WildPHP\Traits\StreamTrait;

/*
 * This class emits events based on incoming data and parses incoming data.
 */
class DataProcessor
{
	use EventEmitterTrait;
	use ParserTrait;
	use StreamTrait;

	/**
	 * @var string
	 */
	private $partial;

	/**
	 * Sets up initial events.
	 *
	 * @param DuplexStreamInterface $stream
	 * @param EventEmitterInterface $emitter
	 */
	public function __construct(DuplexStreamInterface $stream, EventEmitterInterface $emitter)
	{
		$this->setStream($stream);
		$this->setEventEmitter($emitter);
		$this->setParser(new Parser());

		$stream->on('data', function ($data) use ($emitter)
		{
			$emitter->emit('irc.data.raw.in', array($data));
		});

		$emitter->on('irc.data.raw.in', array($this, 'parseData'));
	}

	/**
	 * @param string $data
	 */
	public function parseData($data)
	{
		$consumable = $this->partial . $data;

		// Consume all valid messages and leave partial ones behind.
		$messages = $this->getParser()->consumeAll($consumable);
		$this->partial = $consumable;

		foreach ($messages as $message)
		{
			$this->getEventEmitter()->emit('irc.data.in', array($message));

			if (!empty($message['command']))
				$this->getEventEmitter()->emit('irc.data.in.' . strtolower($message['command']), array($message));
		}
	}

}