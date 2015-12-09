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

namespace WildPHP\Modules;

use WildPHP\BaseModule;
use WildPHP\CoreModules\Connection\IrcDataObject;
use WildPHP\Event\CommandEvent;

class Wiki extends BaseModule
{
    /**
     * The wiki URL. No trailing slash please.
     * @var string
     */
    protected $wikiURL = 'https://wiki.archlinux.org';
    
    /**
     * The wiki directory. To set this, you need to locate your api.php file.
     * If it is in the same directory as the entire wiki, leave this empty.
     * Otherwise, put the directory the wiki resides in in this field.
     * Include a trailing slash if set.
     * @var string
     */
    protected $wikiDir = 'index.php/';

	/**
	 * Set up the module.
	 */
	public function setup()
	{
		// Register our command.
		$this->getEventEmitter()->on('irc.command.wiki', array($this, 'wikiCommand'));
	}

	/**
	 * The wiki command itself.
	 * @param IrcDataObject $data The data received.
	 */
	public function wikiCommand($command, $params, IrcDataObject $data)
	{
		$params = trim($params);
		$user = $data->getMessage()['nick'];
		
		if (preg_match('/ @ ([\S]+)$/', $params, $out) && !empty($out[1]))
		{
                    $user = $out[1];
                    $params = preg_replace('/ @ ([\S]+)$/', '', $params);
		}
		
		// Basically, this makes it valid. ;)
		$query = strtolower(urlencode(trim(preg_replace('/\s\s+/', ' ',  $params))));
		
		// Do the actual result.
		$result = $this->fetch($this->wikiURL . '/api.php?action=opensearch&limit=1&namespace=0&format=json&redirects=resolve&search=' . $query, true);
		
                $connection = $this->getModule('Connection');
		
		// Check for valid JSON, otherwise something went wrong!
		if ($result === false)
		{
                        $connection->write($connection->getGenerator()->ircPrivmsg($data->getTargets()[0], $user . ': Something went wrong while searching the wiki. Please try again.'));
			return;
		}
		
		if (empty($result[1]))
		{
                        $connection->write($connection->getGenerator()->ircPrivmsg($data->getTargets()[0], $user . ': Sorry, I could not find a page matching your query. Please try again.'));
                        return;
                }
		
		// OpenSearch API
		$query = $result[0];
		$title = $result[1][0];
		$summary = $result[2][0];
		$link = $result[3][0];
			
		$connection->write($connection->getGenerator()->ircPrivmsg($data->getTargets()[0], $user . ': ' . $title . ' - ' . $link));
	}
	
	public function fetch($uri, $decode = false)
	{
		// create curl resource
		$ch = curl_init();
		// set url
		curl_setopt($ch, CURLOPT_URL, $uri);
		// user agent.
		curl_setopt($ch, CURLOPT_USERAGENT, 'WildPHP/IRCBot');
		//return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		// $output contains the output string
		$output = curl_exec($ch);
		if (!empty($decode) && ($output = json_decode($output)) === null)
			$output = false;
		// close curl resource to free up system resources
		curl_close($ch);
		return $output;
	}
}
?>
