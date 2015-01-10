<?php
// Namespace
namespace Command;

/**
 * Updates Bot to the Latest Version.
 * arguments[0] == Channel or User to say message to.
 *
 * @package IRCBot
 * @subpackage Command
 * @author Tim Vos <timtimss@outlook.com>
 */
class Update extends \Library\IRC\Command\Base {
    /**
     * The command's help text.
     *
     * @var string
     */
    protected $help = 'Updates the Bot to the Latest Version';

    /**
     * How to use the command.
     *
     * @var string
     */
    protected $usage = 'update';
	
	/**
     * Verify the user before executing this command.
     *
     * @var bool
     */
    protected $verify = true;

    /**
     * The number of arguments the command needs.
     *
     * @var integer
     */
    protected $numberOfArguments = 0;

    /**
     * Updates Bot
	 */
	 
	public function command() {

		$this->bot->log('Checking for Bot Update');
		$this->say('UPDATE: Checking for Bot Updates');
		$update = shell_exec('git pull --progress 2>&1');

		if(preg_match("/up-to-date/i", $update)){
			$this->bot->log('Bot is Already Up to Date');
			$this->say('UPDATE: Bot is Already Up to Date');
		}
		elseif(preg_match("/error/i", $update)){
			$this->bot->log('Bot Updating Ran into an Error');
			$this->say('UPDATE: There was an Error Updating the Bot');
			$this->say($update);
		}
		else{
			$this->bot->log('Bot Updated Successfully');
			$this->say('UPDATE: Bot Was Updated Successfully');
		}
	}
}

class Version extends \Library\IRC\Command\Base {
    /**
     * The command's help text.
     *
     * @var string
     */
    protected $help = 'Checks the Latest Version of the Bot and the Version of this.';

    /**
     * How to use the command.
     *
     * @var string
     */
    protected $usage = 'version';
	
	/**
     * Verify the user before executing this command.
     *
     * @var bool
     */
    protected $verify = true;

    /**
     * The number of arguments the command needs.
     *
     * @var integer
     */
    protected $numberOfArguments = 0;

    /**
     * Checks Version of Bot and Latest Version
	 */
	 
	public function command() {

		$jsonfile = "http://wildphp.github.io/Wild-IRC-Bot/data/updater.json";
		$jsondata = json_decode($jsonfile);
		
		$latestversion = $jsondata[0]->version;
		$botversion = $this->bot->botVersion;
		
		if($latestversion > $botversion){
			$updated = chr(3) . "07Out of Date";
		}
		elseif($latestversion = $botversion){
			$updated = chr(3) . "03Up to Date";
		} 
		else{
			$updated = chr(3) . "04You Broke Something :'(";
		}
		
		$this->say('The Latest Bot Version is ' . $latestversion . '. You\'re Bot is Version is ' . $botversion . ".");
		$this->say('Your Bot is ' . $updated);
	}
}