<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 22-5-16
 * Time: 10:45
 */

namespace WildPHP\Core\Connection;


use WildPHP\Core\Connection\IncomingIrcMessages\BaseMessage;

class IncomingIrcMessage
{
	protected $prefix = '';
	protected $verb = '';
	protected $args = [];

	public function __construct(ParsedIrcMessageLine $line)
	{
		$this->setPrefix($line->prefix);
		$this->setVerb($line->verb);

		// The first argument is the same as the verb.
		$args = $line->args;
		unset($args[0]);
		$this->setArgs(array_values($args));
	}

	public function specialize(): BaseMessage
    {
        $verb = $this->getVerb();
        $expectedClass = '\WildPHP\Core\Connection\IncomingIrcMessages\\' . $verb;

        if (!class_exists($expectedClass))
            throw new MessageNotImplementedException('Incoming message not implemented, cannot specialize: ' . $verb);

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
{}