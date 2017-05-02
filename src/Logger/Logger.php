<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2016 WildPHP

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

namespace WildPHP\Core\Logger;

use Psr\Log\LoggerInterface;
use WildPHP\Core\ComponentTrait;

class Logger implements LoggerInterface
{
	use ComponentTrait;

	/**
	 * @var \Katzgrau\KLogger\Logger
	 */
	protected $logger = null;

	/**
	 * Logger constructor.
	 * @param \Katzgrau\KLogger\Logger $logger
	 */
	public function __construct(\Katzgrau\KLogger\Logger $logger)
	{
		$this->setLogger($logger);
	}

	/**
	 * KLogger does not natively support writing to stdout. This function works around that.
	 */
	public function echoLastLine()
	{
		$lastline = $this->getLogger()
			->getLastLogLine();
		echo $lastline . PHP_EOL;
	}

	/**
	 * @param $message
	 * @param array $context
	 */
	public function emergency($message, array $context = [])
	{
		$this->getLogger()
			->emergency($message, $context);
		$this->echoLastLine();
	}

	/**
	 * @param $message
	 * @param array $context
	 */
	public function alert($message, array $context = [])
	{
		$this->getLogger()
			->alert($message, $context);
		$this->echoLastLine();
	}

	/**
	 * @param $message
	 * @param array $context
	 */
	public function critical($message, array $context = [])
	{
		$this->getLogger()
			->critical($message, $context);
		$this->echoLastLine();
	}

	/**
	 * @param $message
	 * @param array $context
	 */
	public function error($message, array $context = [])
	{
		$this->getLogger()
			->error($message, $context);
		$this->echoLastLine();
	}

	/**
	 * @param $message
	 * @param array $context
	 */
	public function warning($message, array $context = [])
	{
		$this->getLogger()
			->warning($message, $context);
		$this->echoLastLine();
	}

	/**
	 * @param $message
	 * @param array $context
	 */
	public function notice($message, array $context = [])
	{
		$this->getLogger()
			->notice($message, $context);
		$this->echoLastLine();
	}

	/**
	 * @param $message
	 * @param array $context
	 */
	public function info($message, array $context = [])
	{
		$this->getLogger()
			->info($message, $context);
		$this->echoLastLine();
	}

	/**
	 * @param $message
	 * @param array $context
	 */
	public function debug($message, array $context = [])
	{
		$this->getLogger()
			->debug($message, $context);
		$this->echoLastLine();
	}

	/**
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 */
	public function log($level, $message, array $context = [])
	{
		$this->getLogger()
			->log($level, $message, $context);
	}

	/**
	 * @return \Katzgrau\KLogger\Logger
	 */
	public function getLogger(): \Katzgrau\KLogger\Logger
	{
		return $this->logger;
	}

	/**
	 * @param \Katzgrau\KLogger\Logger $logger
	 */
	public function setLogger(\Katzgrau\KLogger\Logger $logger)
	{
		$this->logger = $logger;
	}
}