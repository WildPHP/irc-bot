<?php
/**
 * Created by PhpStorm.
 * User: rick2
 * Date: 30-4-2017
 * Time: 21:21
 */

namespace WildPHP\Core;

use React\EventLoop\LoopInterface;

class ComponentContainer
{
	/**
	 * @var LoopInterface
	 **/
	protected $loop = null;

	/**
	 * @var object[]
	 */
	protected $storedComponents = [];

	/**
	 * @param $object
	 */
	public function store($object)
	{
		$this->storedComponents[get_class($object)] = $object;
	}

	/**
	 * @param string $className
	 * @return object
	 */
	public function retrieve(string $className)
	{
		if (!array_key_exists($className, $this->storedComponents))
			throw new \InvalidArgumentException('Could not retrieve object from container: ' . $className);

		return $this->storedComponents[$className];
	}

	/**
	 * @return LoopInterface
	 */
	public function getLoop(): LoopInterface
	{
		return $this->loop;
	}

	/**
	 * @param LoopInterface $loop
	 */
	public function setLoop(LoopInterface $loop)
	{
		$this->loop = $loop;
	}
}