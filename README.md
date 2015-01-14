# Wild IRC Bot
A IRC Bot built in PHP (using sockets) with OOP.
It is designed to run off a local LAMP, WAMP, MAMP stack or just a plain and simple PHP installation.

IRC Community & Support
-------

If you need support or just want to idle in the channel our IRC Channel is
[#wildphp@irc.freenode.net](http://webchat.freenode.net/?channels=wildphp)

## Features and Functions

### Commands
Currently the bot commands with many pre-configured commands, we are always increasing the functionality and performance of the commands, along with increasing the amount of commands to be used.

The authentication system allows owners & trusted users to be able to run specific commands that normal users are not able to do.

If you have any commands you have, that you want added, feel free to make a pull request and we will look into it.


### Listeners

Listeners are a plugin that search for a specific input, in the IRC chatroom. Listeners react based on, if this input is found. 

For example the join listener, listens for channel joins and sends a welcome message to them.

We are re-designing the listener system to make listener writing easier for developers. 
If you have a listener and want it to be added to the bot by default, send in a pull request and we will look into it.

## Dependencies, Installation and Running

### Dependecies

The bot itself doesn't require anything to run, but for full functionality we recommend installing these dependencies.

#### Ubuntu & Debian
    [sudo] apt-get install php-pear php5-curl screen
    [sudo] pecl install proctitle-alpha
    
#### CentOS
    [sudo] yum install php-pear php5-curl screen
    [sudo] pecl install proctitle-alpha
    
#### Other OS'
Packages that need to be installed are php-pear and php5-curl


### Config

Rename the configuration file and then edit it, to suit you.

    cp config.example.php config.php

### Run

Running the bot is very simple. We recommend running it in a screen

We do not recommend running the bot as root/sudo.

Running as screen:

    screen -dm php phpbot404.php

Running without screen

    php phpbot404.php

### Sample Usage and Output

    <random-user> !say #wildphp hello there
    <wildphp-bot> hello there
    <random-user> !poke #wildphp random-user
    * wildphp-bot pokes random-user

### To-Do List

* Add User Levels
* (Fully Functioning) Restart Command
* More Commands and Listeners
* Channel Manager
* In-Channel Add and Remove Command
* Fix Bugs
* Renew Upstart Command
* Add Full Documentation

Extra Information
-------
Official Website: [http://wildphp.com](http://wildphp.com)

Major Contributors: [Super3](http://super3.org), [Pogosheep](http://layne-obserdia.de), [Matejvelikonja](http://velikonja.si), [Yoshi2889/NanoSector](https://github.com/Yoshi2889), [TimTims](https://timtims.me)
