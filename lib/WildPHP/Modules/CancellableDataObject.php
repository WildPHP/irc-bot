<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 16-12-15
 * Time: 11:23
 */

namespace WildPHP\Modules;

class CancellableDataObject extends DataObject
{
	/**
	 * @var bool
	 */
	protected $cancelled = false;

	/**
	 * @return bool
	 */
	public function isCancelled()
	{
		return $this->cancelled;
	}

	/**
	 * @param bool $cancelled
	 */
	public function setCancelled($cancelled = true)
	{
		$this->cancelled = $cancelled;
	}
}