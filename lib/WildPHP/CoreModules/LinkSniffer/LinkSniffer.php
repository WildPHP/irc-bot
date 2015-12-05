<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 5-12-15
 * Time: 0:12
 */

namespace WildPHP\CoreModules\LinkSniffer;

use WildPHP\BaseModule;

class LinkSniffer extends BaseModule
{
	public function setup()
	{
		$this->getEventEmitter()->on('irc.data.in.privmsg', array($this, 'sniffLinks'));
	}

	public function sniffLinks($message)
	{
		$string = $message['params']['text'];
		$target = $message['targets'][0];

		$result = preg_match('/(https?:\/\/\S+)/i', $string, $matches);

		if (!$result)
			return;

		$link = $matches[1];

		if (!$this->checkValidLink($link))
			return;

		$title = $this->getPageTitle($link);
		$shorturl = $this->createShortLink($link);

		if (empty($shorturl))
			$shorturl = 'No short url';

		$connection = $this->getModule('Connection');
		$connection->write($connection->getGenerator()->ircPrivmsg($target, '[' . $shorturl . '] ' . $title));
	}

	public function getPageTitle($link)
	{
		if (!$this->checkValidLink($link))
			return false;

		$contents = file_get_contents($link);

		if (strlen($contents) == 0)
			return false;

		$result = preg_match('/\<title\>(.*)\<\/title\>/i', $contents, $matches);

		if ($result == false)
			return false;

		return $matches[1];
	}

	public function checkValidLink($link)
	{
		return filter_var($link, FILTER_VALIDATE_URL) == $link;
	}
	
	public function createShortLink($link)
	{
		if (!$this->checkValidLink($link))
			return false;

		$contents = file_get_contents('http://is.gd/create.php?format=json&url=' . urlencode($link));

		$result = json_decode($contents);

		if ($result == false || empty($result->shorturl))
			return false;

		return $result->shorturl;
	}
}