<?php
// Namespace
namespace Listener;
use \Library\FunctionCollection as func;

/**
*
* @package IRCBot
* @subpackage Listener
* @author NeXxGeN (https://github.com/NeXxGeN)
*/
class Youtube extends \Library\IRC\Listener\Base {

	private $apiUri = "http://gdata.youtube.com/feeds/api/videos/%s";

	/**
	* Main function to execute when listen even occurs
	*/
	public function execute($data)
	{
		$ytTitle = $this->getYtTitle($data);
		if ($ytTitle)
		{
			$args = $this->getArguments($data);
			$this->say(sprintf("01,00You00,05Tube %s", $ytTitle),$args[2]);
		}
	}

	private function getYtTitle($data)
	{
		
		preg_match('/https?:\/\/(?:www.)?(?:youtu.be|youtube.com)\/(?:[a-zA-Z0-9_?=]+)/', $data, $matches);
		if (!empty($matches) && !empty($matches[0]))
		{
			// We've got a YT URL. Parse it.
			$matches = parse_url($matches[0]);
			
			// We could have parsed a youtube.com link which parses in more pieces, and one piece must always be the same.
			// We take advantage of this.
			if ($matches['path'] == '/watch')
				$vid_id = substr($matches['query'], 2);
				
			// Otherwise we have a youtu.be link.
			// In that case, the video ID is automatically the part after the youtu.be URL.
			else
				$vid_id = substr($matches['path'], 1);
			
			// We got a video ID longer or smaller than 11 characters...? That's, um, special.
			if (empty($vid_id) || strlen($vid_id) != 11)
				return false;
			
			// Put it into the final URL.
			$ytApi	= sprintf($this->apiUri, $vid_id);
			
			// Make a request.
			$Ytdata	= func::fetch($ytApi);
			
			// Attempt to fetch the title from the garbage we received.
			preg_match("/(?<=<title type=\'text\'>).*(?=<\/title>)/", $Ytdata, $ytTitle);
			
			// And return it.
			return $ytTitle[0];
		}
		
		// Sorry pals, non-YouTube URLs ain't going to cut it.
		return false;
	}
	

	/**
	* Returns keywords that listener is listening to.
	*
	* @return array
	*/
	public function getMessageKeywords() {
		return array('youtu.be', 'youtube.com');
	}
}
