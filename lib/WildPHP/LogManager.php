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

namespace WildPHP;

use WildPHP\Manager;

class LogManager extends Manager
{

	/**
	 * Use a buffer to temporarily store data in. Useful on systems with slow disk access.
	 * @var bool
	 */
	private $useBuffer = false;

	/**
	 * The buffer itself.
	 * @var string
	 */
	private $buffer = '';

	/**
	 * The flush interval, in seconds.
	 * @var int
	 */
	private $flushInterval = 600;

	/**
	 * The time the buffer was last flushed.
	 * @var double
	 */
	private $lastFlushed = 0;

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
	 * Set up the class.
	 * @param array $logDir The
	 */
	public function __construct(Bot $bot, $logDir = WPHP_LOG_DIR)
	{
		parent::__construct($bot);

		// Make sure we end up clean when quitting.
		register_shutdown_function(array($this, 'logShutdown'));

		// Fetch the configuration.
		$config = $bot->getConfig('log');

		// Can't log to a file not set.
		if(empty($config['file']))
			trigger_error('LogManager: A log file needs to be set to use logging. Aborting.', E_USER_ERROR);

		// Check for log dir and create it if necessary
		if(!file_exists($logDir))
			if(!mkdir($logDir, 0775))
				throw new RuntimeException('Log directory (' . $logDir . ') does not exist. Attempt to create it failed.');

		// Check if the log dir is in fact a directory
		if(!is_dir($logDir))
			throw new RuntimeException($logDir . ': ' . __CLASS__ . ' expected directory.');

		// Also can't log to a directory we can't write to.
		if(!is_writable($logDir))
			throw new RuntimeException('Log directory (' . $logDir . ') has insufficient write permissions.');

		// Start off with the base path to the file.
		$this->logFile = $logDir . '/' . $config['file'];

		// Now, we're going to count up until we find a file that doesn't yet exist.
		$i = 0;
		do
		{
			$i++;
		}
		while(file_exists($this->logFile . $i . '.log'));

		// And fix up the final log name.
		$this->logFile = $this->logFile . $i . '.log';

		// Ready!
		if($this->handle = fopen($this->logFile, 'w'))
			$this->log('Using log file ' . $this->logFile);

		// Well this went great...
		else
			trigger_error('LogManager: Cannot create file ' . $this->logFile . '. Aborting.', E_USER_ERROR);
	}

	/**
	 * Add data to the log.
	 * @param string $data   The data to add.
	 * @param string $status The status message to add to the data.
	 */
	public function log($data, $status = '')
	{
		// No status? Use log.
		if(empty($status))
			$status = 'LOG';

		// Add the date and status to the message.
		$msg = date('d.m.Y - H:i:s') . "\t  [ " . $status . " ] \t" . $data;

		// Print the message to the console.
		echo trim($msg) . PHP_EOL;

		// Are we using a buffer? If so, queue the message; we'll write it later.
		if($this->useBuffer)
			$this->buffer = $this->buffer . $msg;

		// Otherwise, we can just write it.
		else
		{
			if(!fwrite($this->handle, $msg))
				echo 'Failed to write message to file...';
		}
	}

	/**
	 * Flush any existing buffers to file.
	 */
	public function flush()
	{
		// We can't flush a buffer we don't have.
		if(!$this->hasBuffer())
		{
			$this->log('No buffer to flush. Either the buffer is disabled or has no data.', 'WARNING');
			return false;
		}

		// Notify that we're going to flush buffers.
		$this->log('Flushing log buffer to disk...', 'INFO');

		// Write it.
		fwrite($this->handle, $this->buffer);

		// Update the time we last flushed.
		$this->lastFlushed = microtime(true);

		// Clear the buffer.
		$this->buffer = '';
	}

	/**
	 * Flush the buffer, but only after a set interval.
	 */
	public function intervalFlush()
	{
		// Time yet?
		if(!$this->useBuffer || (microtime(true) < ($this->lastFlushed + $this->flushInterval)))
			return;

		// Make notes.
		$this->log('Doing routine buffer flush. Next buffer flush at ' . date('d-m-Y - H:i:s', (microtime(true) + $this->flushInterval)));

		// Yes! It's time!
		$this->flush();
	}

	/**
	 * Close the log file.
	 */
	public function close()
	{
		fclose($this->handle);
	}

	/**
	 * Check if the log has a buffer.
	 */
	public function hasBuffer()
	{
		// The log has a buffer if the buffer is enabled and not empty.
		return ($this->useBuffer && !empty($this->buffer));
	}

	/**
	 * Cleanup the log on stop.
	 */
	public function logShutdown()
	{
		$this->log('Shutdown function called, closing log...');
		if($this->hasBuffer())
			$this->flush();
		$this->close();
	}
}
