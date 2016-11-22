<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 06-06-16
 * Time: 16:44
 */

namespace WildPHP\Core\Commands;


class Command
{
	/**
	 * @var callable
	 */
	protected $callback;

    /**
     * @var CommandHelp
     */
    protected $help = null;

	/**
	 * @return callable
	 */
	public function getCallback(): callable
	{
		return $this->callback;
	}

	/**
	 * @param callable $callback
	 */
	public function setCallback(callable $callback)
	{
		$this->callback = $callback;
	}

	/**
	 * @return CommandHelp
	 */
	public function getHelp(): CommandHelp
	{
		return $this->help;
	}

	/**
	 * @param CommandHelp $help
	 */
	public function setHelp(CommandHelp $help)
	{
		$this->help = $help;
	}
}