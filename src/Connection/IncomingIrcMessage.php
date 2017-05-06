<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 22-5-16
 * Time: 10:45
 */

namespace WildPHP\Core\Connection;


use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\IncomingIrcMessages\BaseMessage;
use WildPHP\Core\Logger\Logger;

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
	 * @var
	 */
	protected $container;

	/**
	 * IncomingIrcMessage constructor.
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
	 * @return BaseMessage|IncomingIrcMessage
	 */
	public function specialize()
	{
		$verb = $this->getVerb();
		$expectedClass = '\WildPHP\Core\Connection\IncomingIrcMessages\\' . $verb;

		if (!class_exists($expectedClass) || !($expectedClass instanceof BaseMessage))
		{
			Logger::fromContainer($this->getContainer())->warning('Not Implemented: Unable to specialize message; no valid class found', [
				'verb' => $verb
			]);

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

	/**
	 * @return ComponentContainer
	 */
	public function getContainer(): ComponentContainer
	{
		return $this->container;
	}

	/**
	 * @param ComponentContainer $container
	 */
	public function setContainer(ComponentContainer $container)
	{
		$this->container = $container;
	}
}

class MessageNotImplementedException extends \ErrorException
{
}