<?php

/**
 * Copyright 2017 The WildPHP Team
 *
 * You should have received a copy of the MIT license with the project.
 * See the LICENSE file for more information.
 */

namespace WildPHP\Core\Configuration;


class ConfigurationItem
{
	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * @var string
	 */
	protected $key;

	/**
	 * @param string $key
	 * @param mixed $val
	 */
	public function __construct(string $key, $val)
	{
		$this->setKey($key);
		$this->setValue($val);
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param mixed $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @param string $key
	 */
	public function setKey(string $key)
	{
		$this->key = $key;
	}
}