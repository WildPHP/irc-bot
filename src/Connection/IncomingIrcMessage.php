<?php
/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection;


class IncomingIrcMessage
{
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
	 * @param ParsedIrcMessage $line
	 */
	public function __construct(ParsedIrcMessage $line)
	{
		$this->setPrefix($line->prefix);
		$this->setVerb($line->verb);

		// The first argument is the same as the verb.
		$args = $line->args;
		unset($args[0]);
		$this->setArgs(array_values($args));
	}

	/**
	 * @return string
	 */
	public function getPrefix(): string
	{
		return $this->prefix;
	}

	/**
	 * @param string $prefix
	 */
	public function setPrefix(string $prefix)
	{
		$this->prefix = $prefix;
	}

	/**
	 * @return string
	 */
	public function getVerb(): string
	{
		return $this->verb;
	}

	/**
	 * @param string $verb
	 */
	public function setVerb(string $verb)
	{
		$this->verb = $verb;
	}

	/**
	 * @return array
	 */
	public function getArgs(): array
	{
		return $this->args;
	}

	/**
	 * @param array $args
	 */
	public function setArgs(array $args)
	{
		$this->args = $args;
	}
}