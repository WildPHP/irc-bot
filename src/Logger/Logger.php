<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Logger;

use Psr\Log\LoggerInterface;
use Yoshi2889\Container\ComponentInterface;
use Yoshi2889\Container\ComponentTrait;

class Logger implements LoggerInterface, ComponentInterface
{
	use ComponentTrait;

	/**
	 * @var LoggerInterface
	 */
	protected $logger = null;

	/**
	 * @var string
	 */
	protected $lastLine = '';

	/**
	 * Logger constructor.
	 *
	 * @param LoggerInterface $logger
	 */
	public function __construct(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * The logger does not natively support writing to stdout. This function works around that.
	 *
	 * @param string $lastline
	 */
	public function echoLastLine(string $lastline)
	{
		if ($lastline == $this->lastLine || empty($lastline))
			return;

		$this->lastLine = $lastline;
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
		$this->echoLastLine($message);
	}

	/**
	 * @param $message
	 * @param array $context
	 */
	public function alert($message, array $context = [])
	{
		$this->getLogger()
			->alert($message, $context);
		$this->echoLastLine($message);
	}

	/**
	 * @param $message
	 * @param array $context
	 */
	public function critical($message, array $context = [])
	{
		$this->getLogger()
			->critical($message, $context);
		$this->echoLastLine($message);
	}

	/**
	 * @param $message
	 * @param array $context
	 */
	public function error($message, array $context = [])
	{
		$this->getLogger()
			->error($message, $context);
		$this->echoLastLine($message);
	}

	/**
	 * @param $message
	 * @param array $context
	 */
	public function warning($message, array $context = [])
	{
		$this->getLogger()
			->warning($message, $context);
		$this->echoLastLine($message);
	}

	/**
	 * @param $message
	 * @param array $context
	 */
	public function notice($message, array $context = [])
	{
		$this->getLogger()
			->notice($message, $context);
		$this->echoLastLine($message);
	}

	/**
	 * @param $message
	 * @param array $context
	 */
	public function info($message, array $context = [])
	{
		$this->getLogger()
			->info($message, $context);
		$this->echoLastLine($message);
	}

	/**
	 * @param $message
	 * @param array $context
	 */
	public function debug($message, array $context = [])
	{
		$this->getLogger()
			->debug($message, $context);
		$this->echoLastLine($message);
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
	 * @return LoggerInterface
	 */
	public function getLogger(): LoggerInterface
	{
		return $this->logger;
	}
}