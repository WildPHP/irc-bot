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

namespace WildPHP\CoreModules\LinkSniffer;

use WildPHP\API\Remote;
use WildPHP\API\ShortenUri;
use WildPHP\BaseModule;
use WildPHP\CoreModules\Connection\IrcDataObject;
use WildPHP\Exceptions\ShortUriCreationFailedException;

class LinkSniffer extends BaseModule
{

	public function setup()
	{
		$this->getEventEmitter()->on('irc.data.in.privmsg', [$this, 'sniffLinks']);
	}

	/**
	 * @param IrcDataObject $message
	 * @return void
	 */
	public function sniffLinks(IrcDataObject $message)
	{
		$string = $message->getParams()['text'];
		$target = $message->getTargets()[0];

		// Break the message up in pieces so we can analyse each.
		$pieces = explode(' ', $string);

		foreach ($pieces as $piece)
		{
			if (Remote::isValidLink($piece))
				$link = $piece;
		}

		if (empty($link))
			return;

		try
		{
			$headerResource = Remote::getUriHeaders($link);

			if (!$headerResource->hasHeader('Content-Type'))
				return;

			$content_type = strtolower(explode(';', $headerResource->getHeaderLine('Content-Type'))[0]);

			$title = '(not a web page, content type: ' . $content_type . ')';
			if (in_array($content_type, ['text/html']))
			{
				$temp = $this->getTitleFromUri($link);

				if (!empty($temp))
					$title = $temp;
			}

			try
			{
				$shortUri = ShortenUri::createShortLink($link);
			}
			catch (ShortUriCreationFailedException $e)
			{
				$shortUri = 'No short url';
			}

			$connection = $this->getModule('Connection');
			$connection->write($connection->getGenerator()
				->ircPrivmsg($target, '[' . $shortUri . '] ' . $title));
		}
		catch (\Exception $e)
		{
		}
	}

	/**
	 * @param string $link
	 * @return string
	 */
	public function getTitleFromUri($link)
	{
		$contents = '';
		$title = '';
		Remote::getUriBodySplit($link, function ($partial) use (&$title, &$contents)
		{
			$contents .= $partial;

			if (preg_match('/\<title\>(.*)\<\/title\>/i', $contents, $matches) && !empty($matches[1]))
			{
				$title = htmlspecialchars_decode($matches[1], ENT_QUOTES);

				return false;
			}

			return true;
		});

		return $title;
	}
}
