<?php
// Namespace
namespace Command;

/**
 * Sends raw command to the server.
 * arguments[0] == the command to be sent
 *
 * @package IRCBot
 * @subpackage Command
 * @author Amunak <git@amunak.net>
 */
class Raw extends \Library\IRC\Command\Base {
	/**
	 * The command's help text.
	 *
	 * @var string
	 */
	protected $help = 'Make the bot send a raw IRC command to the server.';

	/**
	 * How to use the command.
	 *
	 * @var string
	 */
	protected $usage = 'raw <command>';

	/**
	 * The number of arguments the command needs.
	 *
	 * @var integer
	 */
	protected $numberOfArguments = -1;

	/**
	 * Verify the user before executing this command.
	 *
	 * @var bool
	 */
	protected $verify = true;

	/**
	 * Sends the arguments to the channel, like say from a user.
	 *
	 * IRC-Syntax: PRIVMSG [#channel]or[user] : [message]
	 */
	public function command() {

		if (!strlen($this->arguments[0]) OR !strlen($this->arguments[1]))
		{
			$this->say($this->usage);
			return;
		}

		$this->connection->sendData(implode(' ', $this->arguments));
	}
}
