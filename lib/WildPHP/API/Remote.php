<?php

/*
	WildPHP - a modular and easily extendable IRC bot written in PHP
	Copyright (C) 2015 WildPHP

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace WildPHP\API;


use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use WildPHP\Exceptions\InvalidUriException;

class Remote
{
	/**
	 * @param string $uri
	 * @return ResponseInterface
	 */
	public static function getUriHeaders($uri)
	{
		if (!self::isValidLink($uri))
			throw new InvalidUriException($uri . ' is not a valid link');

		$httpClient = new Client();
		$resource = $httpClient->head($uri, [
			'allow_redirects' => true,
			'connect_timeout' => 2,
			'timeout' => 5
		]);
		unset($httpClient);

		return $resource;
	}

	/**
	 * @param string $uri
	 * @return bool
	 */
	public static function isValidLink($uri)
	{
		return filter_var($uri, FILTER_VALIDATE_URL) == $uri;
	}

	/**
	 * The purpose of this function is to provide a means to collect data in steps of 1 KB.
	 * This can greatly increase performance when big web pages have to be loaded.
	 *
	 * The callback function may return false to abort the operation.
	 *
	 * @param string $uri
	 * @param callback $callback
	 * @param int $maximumBytes
	 * @param int $steps
	 *
	 * @return void
	 */
	public static function getUriBodySplit($uri, $callback, $maximumBytes = 1024 * 1024 * 3, $steps = 1024)
	{
		if (!self::isValidLink($uri))
			throw new InvalidUriException($uri . ' is not a valid link');

		if (!is_callable($callback))
			throw new \InvalidArgumentException('getUriBodySplit must have a valid callback parameter');

		$body = self::getUriBody($uri);

		$readBytes = 0;
		while (!$body->eof() && $readBytes < $maximumBytes)
		{
			$partial = $body->read($steps);
			$result = call_user_func($callback, $partial);

			if ($result === false)
				break;
		}
		$body->close();
		unset($body);
	}

	/**
	 * @param string $uri
	 * @return StreamInterface
	 */
	public static function getUriBody($uri)
	{
		if (!self::isValidLink($uri))
			throw new InvalidUriException($uri . ' is not a valid link');

		$httpClient = new Client();
		$resource = $httpClient->get($uri, [
			'allow_redirects' => true,
			'connect_timeout' => 2,
			'timeout' => 5
		]);
		unset($httpClient);

		$contents = $resource->getBody();
		unset($resource);

		return $contents;
	}
}