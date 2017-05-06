<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 22-5-16
 * Time: 10:45
 */

namespace WildPHP\Core\Connection;


use WildPHP\Core\ComponentContainer;
use WildPHP\Core\Connection\IRCMessages\BaseMessage;
use WildPHP\Core\ContainerTrait;
use WildPHP\Core\Logger\Logger;

class IncomingIrcMessage
{
	use ContainerTrait;

	// This is necessary because PHP doesn't allow classes with numeric names.
	protected static $numbers = [
		0 => 'Zero',
		1 => 'One',
		2 => 'Two',
		3 => 'Three',
		4 => 'Four',
		5 => 'Five',
		6 => 'Six',
		7 => 'Seven',
		8 => 'Eight',
		9 => 'Nine'
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

		if (is_numeric($verb))
		{
			$numbers = str_split($verb);
			$verb = '';
			foreach ($numbers as $number)
				$verb .= self::$numbers[$number];
		}

		$expectedClass = '\WildPHP\Core\Connection\IRCMessages\\' . $verb;

		if (!class_exists($expectedClass))
		{
			/**Logger::fromContainer($this->getContainer())->warning('Not Implemented: Unable to specialize message; no valid class found', [
				'verb' => $verb
			]);*/

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