<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 5-12-15
 * Time: 0:12
 */

namespace WildPHP\CoreModules\LinkSniffer;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use WildPHP\BaseModule;
use WildPHP\CoreModules\Connection\IrcDataObject;

class LinkSniffer extends BaseModule
{
	public function setup()
	{
		$this->getEventEmitter()->on('irc.data.in.privmsg', array($this, 'sniffLinks'));
	}

	public function sniffLinks(IrcDataObject $message)
	{
		$string = $message->getParams()['text'];
		$target = $message->getTargets()[0];

		$result = preg_match('/(https?:\/\/\S+)/i', $string, $matches);

		if (!$result)
			return;

		$link = $matches[1];

		if (!$this->checkValidLink($link))
			return;

		try {
			$shorturl = $this->createShortLink($link);

			if (!$this->checkValidLink($link))
				return false;

			$httpClient = new \GuzzleHttp\Client();

			$resource = $httpClient->head($link);

			if (!$resource->hasHeader('Content-Type'))
				return false;

			$content_type = strtolower(explode(';', $resource->getHeaderLine('Content-Type'))[0]);

			if (!in_array($content_type, ['text/html']))
				$title = '(not a web page, content type: ' . $content_type . ')';

			else
			{
				$resource = $httpClient->get($link);
				$body = $resource->getBody();

				$contents = '';
				$title = '(no title)';

				$maxBytes = 1024 * 1024 * 3; // 3 MB max per page
				$readBytes = 0;
				while (!$body->eof() && $readBytes < $maxBytes)
				{
					$buffer = $body->read(1024);
					$readBytes += 1024;
					$contents .= $buffer;

					$result = preg_match('/\<title\>(.*)\<\/title\>/i', $contents, $matches);
					if ($result == false)
						continue;

					if (!empty($matches[1]))
						$title = htmlspecialchars_decode($matches[1]);
				}
				$body->close();
			}

			if (empty($shorturl))
				$shorturl = 'No short url';

			$connection = $this->getModule('Connection');
			$connection->write($connection->getGenerator()->ircPrivmsg($target, '[' . $shorturl . '] ' . $title));
		}
		catch (\Exception $e)
		{}
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
