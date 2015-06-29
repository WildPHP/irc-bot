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
namespace WildPHP\Event;

class SayEvent implements ISayEvent
{
    /**
     * Boolean determining the state of the event.
     * @var bool
     */
    protected $cancelled = false;
    
    /**
     * The text that's going to be said.
     * @var string
     */
    protected $text = '';
    
    /**
     * The recipients.
     * @var string[]
     */
    protected $recipients = array();
    
    /**
     * Construct method.
     * @param string $text The initial text to send.
     * @param string|string[] $to The recipients.
     */
    public function __construct($text, $to)
    {
        $this->setText($text);
        $this->addRecipient($to);
    }
    
    public function setText($text)
    {
        $this->text = $text;
    }
    
    public function getText()
    {
        return $this->text;
    }
    
    public function addRecipient($to)
    {
        if (is_array($to))
        {
            // We want no duplicates.
            foreach ($to as $id => $recp)
            {
                if (!is_string($recp) || $this->recipientExists($recp))
                    unset($to[$id]);
            }
            $this->recipients = array_merge($this->recipients, $to);
        }
        else
        {
            if (!$this->recipientExists($to))
                $this->recipients[] = $to;
        }
    }
    
    public function removeRecipient($to)
    {
        if (is_array($to))
        {
            foreach ($to as $recp)
            {
                if ($this->recipientExists($recp))
                    unset($this->recipients[array_search($recp, $this->recipients)]);
            }
        }
        else
        {
            if ($this->recipientExists($to))
                unset($this->recipients[array_search($to, $this->recipients)]);
        }
    }
    
    public function recipientExists($to)
    {
        return in_array($to, $this->recipients);
    }
    
    public function setCancelled($cancel = true)
    {
        $this->cancelled = (bool) $cancel;
    }
    
    public function isCancelled()
    {
        return $this->cancelled;
    }
}
