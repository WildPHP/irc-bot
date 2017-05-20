<?php
/**
 * WildPHP - an advanced and easily extensible IRC bot written in PHP
 * Copyright (C) 2017 WildPHP
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace WildPHP\Core\Connection;


use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\IRCMessages\ReceivableMessage;
use WildPHP\Core\ContainerTrait;

class IncomingIrcMessage
{
	use ContainerTrait;

	// This is necessary because PHP doesn't allow classes with numeric names.
	protected static $numbers = [
		'001' => 'RPL_WELCOME',
		'005' => 'RPL_ISUPPORT',
		'332' => 'RPL_TOPIC',
		'353' => 'RPL_NAMREPLY',
		'354' => 'RPL_WHOSPCRPL',
		'366' => 'RPL_ENDOFNAMES',
	];

	/**
	 * @var string
	 */
	protected $prefix = '';
	/**
	 * @var string
	 */
	protected $verb = '';
	/**
	 * @var array
	 */
	protected $args = [];

	/**
	 * IncomingIrcMessage constructor.
	 *
	 * @param ParsedIrcMessageLine $line
	 * @param ComponentContainer $container
	 */
	public function __construct(ParsedIrcMessageLine $line, ComponentContainer $container)
	{
		$this->setPrefix($line->prefix);
		$this->setVerb($line->verb);

		// The first argument is the same as the verb.
		$args = $line->args;
		unset($args[0]);
		$this->setArgs(array_values($args));
		$this->setContainer($container);
	}

	/**
	 * @return ReceivableMessage|IncomingIrcMessage
	 */
	public function specialize()
	{
		$verb = $this->getVerb();

		if (is_numeric($verb))
			$verb = array_key_exists($verb, self::$numbers) ? self::$numbers[$verb] : $verb;

		$expectedClass = '\WildPHP\Core\Connection\IRCMessages\\' . $verb;

		if (!class_exists($expectedClass))
		{
			return $this;
		}

		return $expectedClass::fromIncomingIrcMessage($this);
	}

	/**
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}

	/**
	 * @param string $prefix
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
	}

	/**
	 * @return string
	 */
	public function getVerb()
	{
		return $this->verb;
	}

	/**
	 * @param string $verb
	 */
	public function setVerb($verb)
	{
		$this->verb = $verb;
	}

	/**
	 * @return array
	 */
	public function getArgs()
	{
		return $this->args;
	}

	/**
	 * @param array $args
	 */
	public function setArgs($args)
	{
		$this->args = $args;
	}
}

class MessageNotImplementedException extends \ErrorException
{
}