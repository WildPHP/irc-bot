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
	 * @return string
	 */
	public function getHelp(): string
	{
		return $this->help;
	}

	/**
	 * @param string $help
	 */
	public function setHelp(string $help)
	{
		$this->help = $help;
	}

	/**
	 * @var string
	 */
	protected $help = '';
}