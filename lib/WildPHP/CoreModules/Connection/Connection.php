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

namespace WildPHP\CoreModules\Connection;

use Phergie\Irc\Parser;
use Phergie\Irc\ParserInterface;
use React\SocketClient\ConnectorInterface;
use React\Stream\Stream;
use WildPHP\BaseModule;

class Connection extends BaseModule
{
	/**
	 * @var string
	 */
	protected $partial = '';

	/**
	 * @var ParserInterface
	 */
	protected $parser = null;

	/**
	 * @var ConnectorInterface
	 */
	protected $connector;

	public function setup()
	{
		$this->getEventEmitter()->on('wildphp.init.after', [$this, 'create']);
		$this->setParser(new Parser());
	}

	public function create()
	{
		$configuration = $this->getModulePool()->get('Configuration');

		$connection = new \Phergie\Irc\Connection();
		$connection->setServerHostname($configuration->get('server'))
			->setServerPort($configuration->get('port'))
			->setNickname($configuration->get('nick'))
			->setUsername($configuration->get('name'))
			->setRealname('A WildPHP Bot');

		$factory = new ConnectorFactory($this->getLoop());

		if ($configuration->get('secure'))
			$connector = $factory->createSecure($configuration->get('server'), $configuration->get('port'));
		else
			$connector = $factory->create($configuration->get('server'), $configuration->get('port'));

		$connector->then(function (Stream $stream) use ($connector)
		{
			$stream->on('data', function ($data) use ($connector)
			{
				$this->getEventEmitter()->emit('irc.data.raw.in', [$data, $connector]);
			});

			return $stream;
		});


		$this->getEventEmitter()->on('irc.data.raw.in', [$this, 'parseData']);
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
			$this->getEventEmitter()->emit('irc.data.in', [$message]);

			if (!empty($message['command']))
				$this->getEventEmitter()->emit('irc.data.in.' . strtolower($message['command']), [$message]);
		}
	}

	/**
	 * @return ParserInterface
	 */
	protected function getParser()
	{
		return $this->parser;
	}

	/**
	 * @param ParserInterface $parser
	 */
	protected function setParser(ParserInterface $parser)
	{
		$this->parser = $parser;
	}

	/**
	 * @return ConnectorInterface
	 */
	protected function getConnector()
	{
		return $this->connector;
	}

	/**
	 * @param ConnectorInterface $connector
	 */
	protected function setConnector(ConnectorInterface $connector)
	{
		$this->connector = $connector;
	}
}