<?php
namespace Library\IRC\Listener;

// This is a manager folks, not a class to toy around with. Non-expandable!
final class Manager {
	private $bot = null;
	
	/**
	 * The list of commands.
	 * @var array
	 */
	private $listeners = array();
	
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
	public function addListener(\Library\IRC\Listener\Base $listener)
	{
		$listenerName = \Library\FunctionCollection::getClassName($listener);
		$listener->setIRCConnection($this->bot->getConnection());
		$listener->setIRCBot($this->bot);
		$this->listeners[$listenerName] = $listener;
		$this->bot->log( 'The following Listener was added to the Bot: "' . $listenerName . '".', 'INFO' );
	}
	
	public function listListeners()
	{
		return $this->listeners;
	}
	
	public function listenerExists($listener)
	{
		return array_key_exists($listener, $this->listeners);
	}
	

	public function listenerHook(array $arguments, $data ) {
		/* @var $listener \Library\IRC\Listener\Base */
		foreach ($this->listeners as $listener) {
			if (method_exists($listener, 'getKeywords') && is_array($listener->getKeywords())) {
				foreach ($listener->getKeywords() as $keyword) {
					if ($keyword === $arguments[1]) {
						$this->bot->log('Running listener, command detected: ' . $keyword, 'LISTENER');
						$listener->execute( $data );
					}
				}
			}
			if (method_exists($listener, 'getMessageKeywords') && is_array($listener->getMessageKeywords())) {
				foreach ($listener->getMessageKeywords() as $keyword) {
					if (stripos($data, $keyword)) {
						$this->bot->log('Running listener, message contains "' . $keyword . '"', 'LISTENER');
						$listener->execute( $data );
						
					}
				}
			}
		}
	}
}


?>