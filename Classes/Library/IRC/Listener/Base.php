<?php
namespace Library\IRC\Listener;

abstract class Base
{
	/**
	 * Reference to the IRC Connection.
	 * @var \Library\IRC\Connection
	 */
	protected $connection = null;

	/**
	 * Reference to the IRC Bot
	 * @var \Library\IRC\Bot
	 */
	protected $bot = null;

	abstract function execute($data);

	/**
	 * Sends PRIVMSG to source with $msg
	 *
	 * @param string $msg
	 */
	protected function say($msg, $source) {
			$message = 'PRIVMSG ' . $source . ' :' . $msg;
			$this->connection->sendData($message);
			$this->bot->log($message, 'COMMAND');
	}

	/**
	 * Set's the IRC Connection, so we can use it to send data to the server.
	 * @param \Library\IRC\Connection $ircConnection
	 */
	public function setIRCConnection(\Library\IRC\Connection $ircConnection) {
		$this->connection = $ircConnection;
	}

	/**
	 * Set's the IRC Bot, so we can use it to send data to the server.
	 * @param \Library\IRCBot $ircBot
	 */
	public function setIRCBot( \Library\IRC\Bot $ircBot ) {
		$this->bot = $ircBot;
	}

	/**
	 *
	 * @param string $data
	 * @return array
	 */
	protected function getArguments($data) {
		$args = explode( ' ', $data );
		$func = function($value) {
			return trim( \Library\FunctionCollection::removeLineBreaks( $value ) );
		};

		return array_map($func, $args);
	}
	
	/**
	 * Fetches data from $uri
	 *
	 * @param string $uri
	 * @return string
	 */
	protected function fetch($uri) {

	$this->bot->log("Fetching data from URI: " . $uri);

	// create curl resource
	$ch = curl_init();

	// set url
	curl_setopt($ch, CURLOPT_URL, $uri);
	
	// user agent.
	curl_setopt($ch, CURLOPT_USERAGENT, 'WildPHP/IRCBot');

	//return the transfer as a string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);

	// $output contains the output string
	$output = curl_exec($ch);

	// close curl resource to free up system resources
	curl_close($ch);

	//$this->bot->log("Data fetched: " . $output);

	return $output;
	}
}
