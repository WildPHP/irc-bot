<?php
namespace Library\IRC\Command;

// This is a manager folks, not a class to toy around with. Non-expandable!
final class Manager {
	private $bot = null;
	
	/**
	 * The list of commands.
	 * @var array
	 */
	public $commands = array();
	
	public function __construct(\Library\IRC\Bot $bot)
	{
		$this->bot = $bot;
	}
	
	/**
	 * Adds a single command to the bot.
	 *
	 * @param IRCCommand $command The command to add.
	 * @author Daniel Siepmann <coding.layne@me.com>
	 */
	public function addCommand(\Library\IRC\Command\Base $command)
	{
		$commandName = \Library\FunctionCollection::getClassName($command);
		$command->setIRCConnection($this->bot->getConnection());
		$command->setIRCBot($this->bot);
		$this->commands[$commandName] = $command;
		$this->bot->log( 'The following Command was added to the Bot: "' . $commandName . '".', 'INFO' );
	}
	
	public function listCommands()
	{
		return $this->commands;
	}
	
	public function commandExists($command)
	{
		return array_key_exists($command, $this->commands);
	}
	

	public function executeCommand( $source, $commandName, array $arguments, $data ) {
		// Execute command:
		$command = $this->commands[$commandName];
		/** @var $command IRCCommand */
		$command->executeCommand( $arguments, $source, $data );
	}
}


?>