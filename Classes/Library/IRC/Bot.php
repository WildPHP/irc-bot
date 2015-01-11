<?php
/**
 * IRC Bot
 *
 * LICENSE: This source file is subject to Creative Commons Attribution
 * 3.0 License that is available through the world-wide-web at the following URI:
 * http://creativecommons.org/licenses/by/3.0/.  Basically you are free to adapt
 * and use this script commercially/non-commercially. My only requirement is that
 * you keep this header as an attribution to my work. Enjoy!
 *
 * @license http://creativecommons.org/licenses/by/3.0/
 *
 * @package IRCBot
 * @subpackage Library
 *
 * @encoding UTF-8
 * @created 30.12.2011 20:29:55
 *
 * @author Daniel Siepmann <coding.layne@me.com>
 */

namespace Library\IRC;

/**
 * A simple IRC Bot with basic features.
 *
 * @package IRCBot
 * @subpackage Library
 *
 * @author Super3 <admin@wildphp.com>
 * @author Daniel Siepmann <coding.layne@me.com>
 */
class Bot {

	/**
	 * Holds the server connection.
	 * @var \Library\IRC\Connection
	 */
	private $connection = null;

	/**
	 * A list of all channels the bot should connect to.
	 * @var array
	 */
	private $channel = array ( );

	/**
	 * The name of the bot.
	 * @var string
	 */
	private $name = '';

	/**
	 * The nick of the bot. Two instances, one for the original nick and one for the current nick.
	 * @var string
	 */
	private $nick = '';
	private $nickToUse = '';

	/**
	 * The IRC password for the bot. 
	 * @var string
	 */ 
	private $password = '';

	/**
	 * The number of reconnects before the bot stops running.
	 * @var integer
	 */
	private $maxReconnects = 0;

	/**
	 * The current log object; usually an instance of LogManager.
	 * @var \Library\IRC\Log
	 */
	public $log;

	/**
	 * The source of the data.
	 * @var string
	 */
	public $source = '';

	/**
	 * Defines the prefix for all commands interacting with the bot.
	 * @var String
	 */
	public $commandPrefix = '!';

	/**
	 * The nick counter, used to generate a available nick.
	 * @var integer
	 */
	private $nickCounter = 0;

	/**
	 * Contains the number of reconnects.
	 * @var integer
	 */
	private $numberOfReconnects = 0;

	/**
	 * The command manager.
	 * @var \Library\IRC\Command\Manager
	 */
	public $commandManager = null;

	/**
	 * Version of the Bot
	 * @var string
	 */
	public $botVersion = "1.1.0.0"";

	/**
	 * The listener manager.
	 * @var \Library\IRC\Listener\Manager
	 */
	public $listenerManager = null;

	/**
	 * Holds the reference to the file.
	 * @var type
	 */
	private $logFileHandler = null;

	/**
	 * The NickServ username. Usually NickServ, but exceptions are possible.
	 * @var string
	 */
	public $nickserv = '';

	/**
	 * Creates a new IRCBot.
	 *
	 * @param array $configuration The whole configuration, you can use the setters, too.
	 * @return void
	 * @author Daniel Siepmann <coding.layne@me.com>
	 */
	public function __construct(array $configuration, \Library\IRC\Log $log) {

		$this->connection = new \Library\IRC\Connection\Socket;

	// Add a new command manager.
	$this->commandManager = new \Library\IRC\Command\Manager($this);

	// And a listener manager.
	$this->listenerManager = new \Library\IRC\Listener\Manager($this);

	// Setup the log.
	$this->log = $log;
	$this->log->setBot($this);

		if (empty($configuration))
			trigger_error('Cannot start without a configuration. Aborting.', E_USER_ERROR);

	// We need this for the connection.
		$this->connection->setServer( $configuration['server'] );
		$this->connection->setPort( $configuration['port'] );
		$this->connection->setName( $configuration['name'] );

	// Then come the bits the bot needs itself.
		$this->setChannel( $configuration['channels'] );
		$this->setNick( $configuration['nick'] );

	// We can only set a password if we have one. If we don't, don't bother.
		if (!empty($configuration['password']))
			$this->setPassword($configuration['password']);

	// Nickserv may differ between servers.
	$this->setNickServ($configuration['nickserv']);
		$this->setMaxReconnects( $configuration['max_reconnects'] );

	// Set the command prefix.
	$this->setCommandPrefix($configuration['prefix']);
	}

	/**
	 * Run the bot.
	 */
	public function run()
	{
	if ($this->connection->isConnected())
	{
		trigger_error('Cannot start multiple instances of the bot; it is already connected. Ignoring run request.', E_USER_WARNING);
		return;
	}
		$this->log('The following commands are known by the bot: "' . implode( ',', array_keys($this->commandManager->listCommands())) . '".', 'INFO');
		$this->log('The following listeners are known by the bot: "' . implode( ',', array_keys( $this->listenerManager->listListeners())) . '".', 'INFO');

	$this->connection->connect();

	$this->log('Fueling the main loop...', 'STARTUP');
	$this->main();
	}

	/**
	 * This is the workhorse function, grabs the data from the server and displays on the browser
	 *
	 * @author Super3 <admin@wildphp.com>
	 * @author Daniel Siepmann <coding.layne@me.com>
	 */
	private function main() {
	// And fire up a connection.
	$this->log('Main loop ignited! GO GO GO!', 'STARTUP');
		do {
			$command = '';
			$arguments = array ( );
			$data = $this->connection->getData();

			// Check for some special situations and react:
			// The nickname is in use, create a now one using a counter and try again.
			if (stripos($data, 'Nickname is already in use.') !== false && \Library\FunctionCollection::getUserNickName($data) == $this->nickserv)
		{
				$this->nickToUse = $this->nick . (++$this->nickCounter);
				$this->sendDataToServer( 'NICK ' . $this->nickToUse );
			}

		if (stripos($data, 'This nickname is registered.') !== false && \Library\FunctionCollection::getUserNickName($data) == $this->nickserv)
		$this->sendDataToServer('PRIVMSG ' . $this->nickserv . ' :IDENTIFY ' . $this->password);

			// We're welcome without password or identified with password. Lets join.
			if ((empty($this->password) && stripos( $data, 'Welcome' ) !== false) || (!empty($this->password) && stripos($data, 'You are now identified') && \Library\FunctionCollection::getUserNickName($data) == $this->nickserv))
				$this->join_channel( $this->channel );

			// Something realy went wrong.
			if (stripos( $data, 'Registration Timeout' ) !== false ||
				stripos( $data, 'Erroneous Nickname' ) !== false ||
				stripos( $data, 'Closing Link' ) !== false) {
				// If the error occurs to often, create a log entry and exit.
				if ($this->numberOfReconnects >= (int) $this->maxReconnects) {
					$this->log( 'Closing Link after "' . $this->numberOfReconnects . '" reconnects.', 'EXIT' );
					exit;
				}

				// Notice the error.
				$this->log( $data, 'CONNECTION LOST' );
				// Wait before reconnect ...
				sleep( 60 * 1 );
				++$this->numberOfReconnects;
				// ... and reconnect.
				$this->connection->connect();
				return;
			}

			// Get the response from irc:
			$args = explode(' ', $data);
			if (!empty($data))
				$this->log( $data );

			// Play ping pong with server, to stay connected:
			if ($args[0] == 'PING') {
				$this->sendDataToServer( 'PONG ' . $args[1] );
			}

		// Try to flush log buffers, if needed.
		$this->log->intervalFlush();

			// Nothing new from the server, step over.
			if ($args[0] == 'PING' || !isset($args[1])) {
				unset($data, $args);
				continue;
			}

			if (isset($args[3])) {
				// Explode the server response and get the command.
				// $source finds the channel or user that the command originated.
				$source = substr( trim( \Library\FunctionCollection::removeLineBreaks( $args[2] ) ), 0 );
				$command = substr( trim( \Library\FunctionCollection::removeLineBreaks( $args[3] ) ), 1 );

				// Someone PMed me? Oh noes.
				if ($source == $this->nickToUse && $args[1] == 'PRIVMSG')
					$source = \Library\FunctionCollection::getUserNickName($args[0]);

				$this->source = $source;
				$arguments = array_slice( $args, 4 );

				// Check if the response was a command.
				if (stripos( $command, $this->commandPrefix ) === 0) {
					$command = ucfirst( substr( $command, strlen($this->commandPrefix) ) );
					// Command does not exist:
					if (!$this->commandManager->commandExists($command)) {
						$this->log( 'The following, not existing, command was called: "' . $command . '".', 'MISSING' );
						continue;
					}

					$this->commandManager->executeCommand( $source, $command, $arguments, $data );
				}
			}

		// Call the listeners!
		$this->listenerManager->listenerHook($args, $data);
			unset($data, $args);
		} while (true);
	}

	/**
	 * Displays stuff to the broswer and sends data to the server.
	 * @param string $cmd The command to execute.
	 *
	 * @author Daniel Siepmann <coding.layne@me.com>
	 */
	public function sendDataToServer( $cmd ) {
	if (mb_substr($cmd, 0, 4) != 'PASS')
		$this->log( $cmd, 'COMMAND' );
	else
		$this->log('PASS *****', 'COMMAND');
		$this->connection->sendData( $cmd );
	}

	/**
	 * Joins one or multiple channel/-s.
	 * @param mixed $channel An string or an array containing the name/-s of the channel.
	 *
	 * @author Super3 <admin@wildphp.com>
	 */
	private function join_channel( $channel ) {
		if (is_array( $channel )) {
			foreach ($channel as $chan) {
				$this->sendDataToServer( 'JOIN ' . $chan );
			}
		}
		else {
			$this->sendDataToServer( 'JOIN ' . $channel );
		}
	}

	/**
	 * Adds a log entry to the log file. Redirects to the LogManager for legacy purposes.
	 *
	 * @param string $log	The log entry to add.
	 * @param string $status The status, used to prefix the log entry.
	 */
	public function log( $log, $status = '' ) {
	$this->log->log($log, $status);
	}

	// Setters

	/**
	 * Sets the server.
	 * E.g. irc.quakenet.org or irc.freenode.org
	 * @param string $server The server to set.
	 */
	public function setServer( $server ) {
		$this->connection->setServer( $server );
	}

	/**
	 * Sets the port.
	 * E.g. 6667
	 * @param integer $port The port to set.
	 */
	public function setPort( $port ) {
		$this->connection->setPort( $port );
	}

	/**
	 * Sets the channel.
	 * E.g. '#testchannel' or array('#testchannel','#helloWorldChannel')
	 * @param string|array $channel The channel as string, or a set of channels as array.
	 */
	public function setChannel( $channel ) {
		$this->channel = (array) $channel;
	}

	/**
	 * Sets the name of the bot.
	 * "Yes give me a name!"
	 * @param string $name The name of the bot.
	 */
	public function setName( $name ) {
		$this->connection->setName((string) $name);
	}

	/**
	 * Sets the IRC password for the bot
	 * @param string $password The password for the IRC server.
	 */ 

	 public function setPassword($password) {
	$this->password = $password;
		$this->connection->setPassword((string) $password);
	 }

	/**
	 * Sets the nick of the bot.
	 * "Yes give me a nick too. I love nicks."
	 *
	 * @param string $nick The nick of the bot.
	 */
	public function setNick( $nick ) {
		$this->nickToUse = (string) $nick;
	$this->connection->setNick($this->nickToUse);
	}

	public function setNickServ($nickserv) {
	$this->nickserv = (string) $nickserv;
	}
	public function setCommandPrefix($prefix) {
	$this->commandPrefix = (string) $prefix;
	}

	/**
	 * Sets the limit of reconnects, before the bot exits.
	 * @param integer $maxReconnects The number of reconnects before the bot exits.
	 */
	public function setMaxReconnects( $maxReconnects ) {
		$this->maxReconnects = (int) $maxReconnects;
	}

	/**
	 * Returns the current command prefix.
	 */
	public function getCommandPrefix() {
		return $this->commandPrefix;
	}

	/**
	 * Returns the current connection.
	 */
	public function getConnection()
	{
	return $this->connection;
	}

	/**
	 * Handles closing log files and, if needed, flushing buffers.
	 * Called at shutdown.
	 */
	public function onShutdown()
	{	
		// It is possible that we have not had a chance to create a log object yet.
		// In that case, we're in early initialisation. There's nothing we can do at that point.
		if (is_object($this->log))
		{
		$this->log->log('Shutdown function called, closing log...');
		if ($this->log->hasBuffer())
			$this->log->flush();
			$this->log->close();
		}
	}
}
