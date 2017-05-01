<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 27-5-16
 * Time: 19:25
 */

namespace WildPHP\Core\Connection\Commands;

class Raw extends BaseCommand
{
	/**
	 * @var string
	 */
	protected $command;

	/**
	 * Raw constructor.
	 * @param string $command
	 */
	public function __construct(string $command)
	{
		$this->setCommand($command);
	}

	/**
	 * @return string
	 */
	public function getCommand(): string
	{
		return $this->command;
	}

	/**
	 * @param string $command
	 */
	public function setCommand(string $command)
	{
		$this->command = $command;
	}

	/**
	 * @return string
	 */
	public function formatMessage(): string
	{
		return $this->getCommand() . "\r\n";
	}
}