<?php
namespace Command;
use \Library\FunctionCollection as func;
class Note extends \Library\IRC\Command\Base
{
	protected $help = 'Set a note.';
	protected $usage = 'note [message] [@ optional name]';
	protected $numberOfArguments = -1;

	protected $notes = array();

	// Run a command on behalf of the bot.
	public function command()
	{
		// We don't want any trailing \n, \r or anything in that area.
		$subcomm = $this->arguments[0];
		$command = \Library\FunctionCollection::removeLineBreaks(implode(' ', $this->arguments));
		$user = \Library\FunctionCollection::getUserNickName($this->data);

		if (!empty($subcomm))
		{
			switch ($subcomm)
			{
				case 'show':
					$this->arguments[1] = (int) $this->arguments[1];
					if (!empty($this->notes[$user][$this->arguments[1]]))
						$this->say('Note #' . $this->arguments[1] . ': ' . $this->notes[$user][$this->arguments[1]]['note']);
					else
						$this->say('No note with ID ' . $this->arguments[1] . ' found.');
					break;
				case 'delete':
					$this->arguments[1] = (int) $this->arguments[1];
					if (!empty($this->notes[$user][$this->arguments[1]]))
					{
						$this->say('Note #' . $this->arguments[1] . ': "' . $this->notes[$user][$this->arguments[1]]['name'] . '" was deleted.');
						unset($this->notes[$user][$this->arguments[1]]);
					}
					else
						$this->say('No note with ID ' . $this->arguments[1] . ' found.');
					break;

				case 'list':
					if (!empty($this->notes[$user]))
					{
						$notenames = array();
						foreach ($this->notes[$user] as $id => $note)
							$notenames[] = '#' . $id . ': ' . $note['name'];
						$this->say($user . ', I have ' . count($this->notes[$user]) . ' notes for you: ' . implode(', ', $notenames));
					}
					else
						$this->say($user . ', I don\'t have any notes for you.');

					break;

				default:
					$dname = '(unnamed)';
					$message = $command;

					if (preg_match('/@ (.+)$/', $command, $name))
					{

						$dname = preg_replace('/[^a-zA-Z0-9!?_ -]+/', '', $name[1]);

						echo var_dump($dname, $name);
						if ($name[1] != $dname)
						{
							$this->say('Invalid note name. Names can contain the characters A-Z, a-z, 0-9, !, ?, _ and - only.');
							return;
						}

						// Cut it off the message.
						$message = preg_replace('/ @ ' . preg_quote($dname) . '/', '', $message);
					}

					// Add the note.
					$note = array('name' => $dname, 'note' => $message);

					// Count it.
					$count = !empty($this->notes[$user]) ? end(array_keys($this->notes[$user])) + 1 : 1;
					$this->notes[$user][$count] = $note;

					$this->say('Note ' . $dname . ' set with ID ' . $count . '. You can use "' . $this->bot->commandPrefix . 'note list" to view your saved notes, or "' . $this->bot->commandPrefix . 'note show ' . $count . '" to show this specific note.');

			}
		}
	}
}
?>
