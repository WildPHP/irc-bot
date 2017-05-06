<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 6-5-17
 * Time: 16:16
 */

namespace WildPHP\Core;


trait ContainerTrait
{
	/**
	 * @var ComponentContainer
	 */
	protected $container;

	/**
	 * @return ComponentContainer
	 */
	public function getContainer(): ComponentContainer
	{
		return $this->container;
	}

	/**
	 * @param ComponentContainer $container
	 */
	public function setContainer(ComponentContainer $container)
	{
		$this->container = $container;
	}
}