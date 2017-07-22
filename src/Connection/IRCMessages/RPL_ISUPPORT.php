<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Connection\IRCMessages;

use WildPHP\Core\Connection\IncomingIrcMessage;

/**
 * Class RPL_WELCOME
 * @package WildPHP\Core\Connection\IRCMessages
 *
 * Syntax: :server 005 nickname VARIABLE[=key] VARIABLE[=key] ... :greeting
 */
class RPL_ISUPPORT extends BaseIRCMessage implements ReceivableMessage
{
	use NicknameTrait;
	use ServerTrait;

	protected static $verb = '005';

	protected $variables = [];

	/**
	 * @param IncomingIrcMessage $incomingIrcMessage
	 *
	 * @return \self
	 * @throws \InvalidArgumentException
	 */
	public static function fromIncomingIrcMessage(IncomingIrcMessage $incomingIrcMessage): self
	{
		if ($incomingIrcMessage->getVerb() != self::getVerb())
			throw new \InvalidArgumentException('Expected incoming ' . self::getVerb() . '; got ' . $incomingIrcMessage->getVerb());

		$args = $incomingIrcMessage->getArgs();
		$nickname = array_shift($args);
		$server = $incomingIrcMessage->getPrefix();

		$variables = [];
		foreach ($args as $arrayKey => $value)
		{
			$parts = explode('=', $value);
			$key = strtolower($parts[0]);
			$value = !empty($parts[1]) ? $parts[1] : true;
			$variables[$key] = $value;
		}

		$object = new self();
		$object->setNickname($nickname);
		$object->setServer($server);
		$object->setVariables($variables);

		return $object;
	}

	/**
	 * @return array
	 */
	public function getVariables(): array
	{
		return $this->variables;
	}

	/**
	 * @param array $variables
	 */
	public function setVariables(array $variables)
	{
		$this->variables = $variables;
	}
}