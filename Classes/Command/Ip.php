<?php
// Namespace
namespace Command;

/**
 * Sends the user's IP to the channel.
 *
 * @package IRCBot
 * @subpackage Command
 * @author Matej Velikonja <matej@velikonja.si>
 */
class Ip extends \Library\IRC\Command\Base {
	/**
	 * The command's help text.
	 *
	 * @var string
	 */
	protected $help = 'Return the IP of the given hostname.';

	/**
	 * How to use the command.
	 *
	 * @var string
	 */
	protected $usage = 'ip [hostname]';

	/**
	 * The number of arguments the command needs.
	 *
	 * @var integer
	 */
	protected $numberOfArguments = 1;

	/**
	 * Sends the arguments to the channel. An IP.
	 *
	 * IRC-Syntax: PRIVMSG [#channel]or[user] : [message]
	 */
	public function command() {
	$ip = gethostbyname($this->arguments[0]);

	// gethostbyname() returns the unmodified hostname on failure. That's why we check it like this.
		if ($ip != $this->arguments[0]) {
			$this->say('The IP of this host is: ' . $ip);
		} else {
		   $this->say('This host does not have an IP');
		}
	}
}
