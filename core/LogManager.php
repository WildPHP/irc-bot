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

namespace WildPHP\Core;

class LogManager
{
	/**
	 * The Bot object. Used to interact with the main thread.
	 * @var object
	 */
	protected $bot;

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
	 * @var int
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
	 * Filter the output to only log a specific channel.
	 * @var array
	 */
	private $filterChannels = array();

	/**
	 * Set up the class.
	 * @param array $config The configuration variables.
	 */
	public function __construct($bot, $logDir = WPHP_LOG_DIR)
	{
		// Can't log to a file not set.
		if (empty($config['file']))
			trigger_error('LogManager: A log file needs to be set to use logging. Aborting.', E_USER_ERROR);

		// Also can't log to a directory we can't write to.
		if (!is_writable($logDir))
			trigger_error('LogManager: A log file cannot be created in the set directory (' . $logDir . '). Please make it writable. Aborting.', E_USER_ERROR);

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

		// Are we only logging output from channels?
		if (!empty($config['filter']))
			$this->filterChannels = $config['filter'];

		// Ready!
		if ($this->handle = fopen($this->logFile, 'w'))
			$this->log('Using log file ' . $this->logFile);

		// Well this went great...
		else
			trigger_error('LogManager: Cannot create file ' . $this->logFile . '. Aborting.', E_USER_ERROR);

		// Set up the bot.
		$this->bot = $bot;
	}

	/**
	 * Add data to the log.
	 * @param string $data   The data to add.
	 * @param string $status The status message to add to the data.
	 */
	public function log($data, $status = '')
	{
		// No status? Use log.
		if (empty($status))
			$status = 'LOG';

		// Add the date and status to the message.
		$msg = date('d.m.Y - H:i:s') . "\t  [ " . $status . " ] \t" . $data . PHP_EOL;

		// Print the message to the console.
		echo $msg;

		// If we're filtering channel messages, do so now.
		if (!empty($this->filterChannels))
		{
			$isFromChannel = false;
			foreach ($this->filterChannels as $channel)
			{
				if (stripos($data, 'PRIVMSG ' . $channel . ' :') !== false)
					$isFromChannel = true;
			}

			if (!$isFromChannel)
				return;
		}

		// Are we using a buffer? If so, queue the message; we'll write it later.
			if ($this->useBuffer)
				$this->buffer = $this->buffer . $msg;

		// Otherwise, we can just write it.
			else
			{
			if (!fwrite($this->handle, $msg))
				echo 'Failed to write message to file...';
		}
	}

	/**
	 * Flush any existing buffers to file.
	 */
	public function flush()
	{
		// We can't flush a buffer we don't have.
		if (!$this->hasBuffer())
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
		if (!$this->useBuffer || (microtime(true) < ($this->lastFlushed + $this->flushInterval)))
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
	 * Set the bot instance.
	 */
	public function setBot($bot)
	{
		if (is_object($bot))
			$this->bot = $bot;
	}
}
