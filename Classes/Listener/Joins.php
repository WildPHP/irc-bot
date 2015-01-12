<?php
// Namespace
namespace Listener;

/**
 *
 * @package IRCBot
 * @subpackage Listener
 * @author Matej Velikonja <matej@velikonja.si>
 */
class Joins extends \Library\IRC\Listener\Base {
	/**
	 * Main function to execute when listen even occurs
	 */
	public function execute($data) {
		$args = $this->getArguments($data);
		$user = \Library\FunctionCollection::getUserNickName($args[0]);
		
		// Remove any leading colons, if they exist.
		if (substr($args[2], 0, 1) == ':')
			$args[2] = substr($args[2], 1);

		if ($user != $this->bot->getNick())
			$this->say('Welcome ' . $user . '!', $args[2]);
	}

	/**
	 * Returns keywords that listener is listening to.
	 *
	 * @return array
	 */
	public function getKeywords() {
		return array("JOIN");
	}
}
