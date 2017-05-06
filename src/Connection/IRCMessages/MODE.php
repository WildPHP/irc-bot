<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 6-5-17
 * Time: 17:06
 */

namespace WildPHP\Core\Connection\IRCMessages;
use WildPHP\Core\Connection\IncomingIrcMessage;
use WildPHP\Core\Connection\UserPrefix;

/**
 * Class MODE
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax (initial): nickname MODE nickname :modes
 * Syntax (user): prefix MODE nickname flags
 * Syntax (channel): prefix MODE #channel flags [arguments]
 */
class MODE
{
	use PrefixTrait;
	use NicknameTrait;

	protected static $verb = 'MODE';

	protected $flags = '';
	protected $target = '';
	protected $arguments = [];

	public function __construct(string $target, string $flags, array $arguments = [])
	{
		$this->setTarget($target);
		$this->setFlags($flags);
		$this->setArguments($arguments);
	}

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 *
	 * @return MODE
	 * @throws \ErrorException
	 */
	public static function fromIncomingIrcMessage(IncomingIrcMessage $incomingIrcMessage): self
	{
		if ($incomingIrcMessage->getVerb() != self::$verb)
			throw new \InvalidArgumentException('Expected incoming ' . self::$verb . '; got ' . $incomingIrcMessage->getVerb());

		try
		{
			$prefix = UserPrefix::fromIncomingIrcMessage($incomingIrcMessage);
		}
		catch (\InvalidArgumentException $e)
		{
			$prefix = $incomingIrcMessage->getPrefix();
		}

		$args = $incomingIrcMessage->getArgs();
		$target = array_shift($args);
		$flags = array_shift($args);

		$object = new self($target, $flags, $args);
		$object->setPrefix($prefix);
		$object->setNickname($prefix->getNickname());

		return $object;
	}

	/**
	 * @return string
	 */
	public function getFlags(): string
	{
		return $this->flags;
	}

	/**
	 * @param string $flags
	 */
	public function setFlags(string $flags)
	{
		$this->flags = $flags;
	}

	/**
	 * @return string
	 */
	public function getTarget(): string
	{
		return $this->target;
	}

	/**
	 * @param string $target
	 */
	public function setTarget(string $target)
	{
		$this->target = $target;
	}

	/**
	 * @return array
	 */
	public function getArguments(): array
	{
		return $this->arguments;
	}

	/**
	 * @param array $arguments
	 */
	public function setArguments(array $arguments)
	{
		$this->arguments = $arguments;
	}

	public function __toString()
	{
		$arguments = implode(' ', $this->getArguments());
		return 'MODE ' . $this->getTarget() . ' ' . $this->getFlags() . ' ' . $arguments . "\r\n";
	}
}