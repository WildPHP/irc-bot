<?php
namespace Command;
use \Library\FunctionCollection as func;
class Remind extends \Library\IRC\Command\Base
{
	protected $help = 'Set a reminder.';
	protected $usage = 'remind [message] in ##[s/m/h/d]';
	protected $numberOfArguments = -1;

	// Run a command on behalf of the bot.
	public function command()
	{
		// We don't want any trailing \n, \r or anything in that area.
		$command = \Library\FunctionCollection::removeLineBreaks(implode(' ', $this->arguments));
		$user = \Library\FunctionCollection::getUserNickName($this->data);

		$time = @end(explode(' in ', $command));
		$message = str_replace(' in ' . $time, '', $command);

		$currTime = time();

		if (!preg_match('/(\d+)(s|m|h|d)/i', $time, $out))
		{
			$this->say('Invalid arguments.');
			return;
		}

		$newtime = 0;
		switch ($out[2])
		{
			case 's':
				$newtime = $currTime + $out[1];
				break;
			case 'm':
				$newtime = $currTime + ($out[1] * 60);
				break;
			case 'h':
				$newtime = $currTime + ($out[1] * 60 * 60);
				break;
			case 'd':
				$newtime = $currTime + ($out[1] * 60 * 60 * 24);
				break;
		}

		$message = $user . ', ' . $message;
		$this->bot->reminders[$newtime] = $message;

		$this->say('Reminder "' . $message . '" set at ' . date('d-m-Y H:i:s', $newtime) . ' GMT+1, current time: ' . date('d-m-Y H:i:s'));
	}
}
?>
