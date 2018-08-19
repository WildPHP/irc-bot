<?php

/**
 * Copyright 2018 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Channels;

use ValidationClosures\Types;
use Yoshi2889\Collections\Collection;
use Yoshi2889\Container\ComponentInterface;
use Yoshi2889\Container\ComponentTrait;

class ChannelCollection extends Collection implements ComponentInterface
{
	use ComponentTrait;

	/**
	 * ChannelCollection constructor.
	 */
	public function __construct()
	{
		parent::__construct(Types::instanceof(Channel::class));
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function containsChannelName(string $name): bool
	{
		return !empty($this->findByChannelName($name));
	}

	/**
	 * @param string $name
	 *
	 * @return false|Channel
	 */
	public function findByChannelName(string $name)
	{
		/** @var Channel $value */
		foreach ($this->values() as $value)
			if ($value->getName() == $name)
				return $value;

		return false;
	}
}