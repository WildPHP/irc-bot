<?php
/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core;

use React\EventLoop\LoopInterface;

class ComponentContainer extends \Yoshi2889\Container\ComponentContainer
{
	/**
	 * @var LoopInterface
	 **/
	protected $loop = null;

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