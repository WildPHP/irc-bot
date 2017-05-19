<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 27-5-16
 * Time: 19:25
 */

namespace WildPHP\Core\Connection\IRCMessages;

use WildPHP\Core\Connection\IncomingIrcMessage;

/**
 * Class CAP
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: prefix CAP nickname command [:capabilities]
 */
class CAP implements BaseMessage, SendableMessage
{
    protected static $verb = 'CAP';

    use NicknameTrait;

	/**
	 * @var string
	 */
	protected $command = '';

    /**
     * @var array
     */
	protected $capabilities = [];

    /**
     * CAP constructor.
     * @param string $command
     * @param array $capabilities
     */
	public function __construct(string $command, array $capabilities = [])
	{
		$this->setCommand($command);
	}

    /**
     * @param IncomingIrcMessage $incomingIrcMessage
     *
     * @return \self
     * @throws \InvalidArgumentException
     */
    public static function fromIncomingIrcMessage(IncomingIrcMessage $incomingIrcMessage): self
    {
        if ($incomingIrcMessage->getVerb() != self::$verb)
            throw new \InvalidArgumentException('Expected incoming ' . self::$verb . '; got ' . $incomingIrcMessage->getVerb());

        $args = $incomingIrcMessage->getArgs();
        $nickname = array_shift($args);
        $command = array_shift($args);
        $capabilities = explode(' ', array_shift($args));

        $object = new self($command, $capabilities);
        $object->setNickname($nickname);
        return $object;
    }

	/**
	 * @return string
	 */
	public function getCommand(): string
	{
		return $this->command;
	}

	/**
	 * @param string $command
	 */
	public function setCommand(string $command)
	{
		$this->command = $command;
	}

    /**
     * @return array
     */
    public function getCapabilities(): array
    {
        return $this->capabilities;
    }

    /**
     * @param array $capabilities
     */
    public function setCapabilities(array $capabilities)
    {
        $this->capabilities = $capabilities;
    }

	/**
	 * @return string
	 */
	public function __toString(): string
	{
	    $capabilities = implode(' ', $this->getCapabilities());
		return 'CAP ' . $this->getCommand() . (!empty($capabilities) ? ' :' . $capabilities : '') . "\r\n";
	}
}