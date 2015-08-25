<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2015 WildPHP

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace WildPHP\LogManager;

use WildPHP\Bot;
use WildPHP\Manager;

class LogManager extends Manager
{
	/**
	 * The file handle, used for manipulating the current log file.
	 * @var resource
	 */
	private $handle = '';

	/**
	 * The path to the current log file.
	 * @var string
	 */
	private $logFile = '';

	/**
	 * What entries do we write?
	 * @var string[]
	 */
	protected $write = array();

	/**
	 * And which do we print?
	 * @var string[]
	 */
	protected $print = array();

	/**
	 * Set up the class.
	 * @param Bot $bot The bot object
	 * @param string $logDir The directory to store logs in
	 */
	public function __construct(Bot $bot, $logDir = WPHP_LOG_DIR)
	{
		parent::__construct($bot);

		// Make sure we end up clean when quitting.
		register_shutdown_function(array($this, 'logShutdown'));

		// Fetch the configuration.
		$config = $bot->getConfig('log');

		// Set some flags.
		$this->write = $this->bot->getConfig('log.items');
		$this->print = $this->bot->getConfig('log.print');

		// Can't log to a file not set.
		if (empty($config['file']))
			throw new \RuntimeException('LogManager: A log file needs to be set to use logging. Aborting.');

		// Check for log dir and create it if necessary
		if (!file_exists($logDir))
			if (!mkdir($logDir, 0775))
				throw new \RuntimeException('Log directory (' . $logDir . ') does not exist. Attempt to create it failed.');

		// Check if the log dir is in fact a directory
		if (!is_dir($logDir))
			throw new \RuntimeException($logDir . ': ' . __CLASS__ . ' expected directory.');

		// Also can't log to a directory we can't write to.
		if (!is_writable($logDir))
			throw new \RuntimeException('Log directory (' . $logDir . ') has insufficient write permissions.');

		// Start off with the base path to the file.
		$this->logFile = $logDir . '/' . $config['file'];

		// Now, we're going to count up until we find a file that doesn't yet exist.
		$i = 0;
		do
		{
			$i++;
		}
		while (file_exists($this->logFile . $i . '.log'));

		// And fix up the final log name.
		$this->logFile = $this->logFile . $i . '.log';

		// Ready!
		if ($this->handle = fopen($this->logFile, 'w'))
			$this->log('Using log file ' . $this->logFile);

		// Well this went great...
		else
			trigger_error('LogManager: Cannot create file ' . $this->logFile . '. Aborting.', E_USER_ERROR);
	}

	/**
	 * Add data to the log.
	 * @param string $message The data to log.
	 * @param boolean $print Whether to print the data to screen.
	 * @param boolean $write Whether to write the data to file.
	 */
	protected function log($message, $print = true, $write = true)
	{
		if ($print)
		{
			// Print the message to the console.
			echo trim($message) . PHP_EOL;
		}

		// Otherwise, we can just write it.
		if ($write)
		{
			if (!fwrite($this->handle, trim($message) . PHP_EOL))
				throw new \RuntimeException('Unable to write message to log file; was its permission changed?');
		}
	}

	/**
	 * Log a critical error.
	 * @param string $message The message to log.
	 * @param array $context The context to use.
	 */
	public function error($message, array $context = array())
	{
		$message = $this->prepareMessage($message, 'ERROR', $context);
		if (!empty($message))
			$this->log($message, in_array('error', $this->print), in_array('error', $this->write));
	}

	/**
	 * Log a warning.
	 * @param string $message The message to log.
	 * @param array $context The context to use.
	 */
	public function warning($message, array $context = array())
	{
		$message = $this->prepareMessage($message, 'WARNING', $context);
		if (!empty($message))
			$this->log($message, in_array('warning', $this->print), in_array('warning', $this->write));
	}

	/**
	 * Log an informational message.
	 * @param string $message The message to log.
	 * @param array $context The context to use.
	 */
	public function info($message, array $context = array())
	{
		$message = $this->prepareMessage($message, 'INFO', $context);
		if (!empty($message))
			$this->log($message, in_array('info', $this->print), in_array('info', $this->write));
	}

	/**
	 * Log a debug message.
	 * @param string $message The message to log.
	 * @param array $context The context to use.
	 */
	public function debug($message, array $context = array())
	{
		$message = $this->prepareMessage($message, 'DEBUG', $context);
		if (!empty($message))
			$this->log($message, in_array('debug', $this->print), in_array('debug', $this->write));
	}

	/**
	 * Log a message from a channel.
	 * @param string $message The message to log.
	 * @param array $context The context to use.
	 */
	public function channel($message, array $context = array())
	{
		$message = $this->prepareMessage($message, 'CHANNEL', $context);
		if (!empty($message))
			$this->log($message, in_array('channel', $this->print), in_array('channel', $this->write));
	}

	/**
	 * Prepares a message to be logged.
	 * @param string $message The message.
	 * @param string $level The level to log at.
	 * @param array $context The context that is to be used.
	 * @return string|false
	 */
	protected function prepareMessage($message, $level = '', array $context = array())
	{
		if (empty($message))
			return false;

		$msg = '[' . date('Y-m-d G:i:s') . '] ';

		if (!empty($level))
			$msg .= '[' . $level . '] ';

		if (!empty($context))
			$message = $this->interpolate($message, $context);

		$msg .= $message;
		return $msg;
	}

	/**
	 * Merge the context into a message.
	 * @param string $message The message.
	 * @param array $context The context.
	 * @return string|false
	 */
	protected function interpolate($message, array $context = array())
	{
		if (empty($message))
			return false;

		if (empty($context))
			return $message;

		$replace = array();

		foreach ($context as $key => $value)
		{
			$replace['{' . $key . '}'] = $value;
		}

		return strtr($message, $replace);
	}

	/**
	 * Close the log file.
	 */
	public function close()
	{
		fclose($this->handle);
	}

	/**
	 * Cleanup the log on stop.
	 */
	public function logShutdown()
	{
		$this->log('Shutdown function called, closing log...');
		$this->close();
	}
}
