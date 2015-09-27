# Wild IRC Bot ([wildphp.com](http://wildphp.com/))
----------
[![Build Status](https://scrutinizer-ci.com/g/WildPHP/Wild-IRC-Bot/badges/build.png?b=master)](https://scrutinizer-ci.com/g/WildPHP/Wild-IRC-Bot/build-status/master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/WildPHP/Wild-IRC-Bot/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/WildPHP/Wild-IRC-Bot/?branch=master)

A modular IRC Bot built in PHP with the use of object-oriented programming.

It is designed to run off a local LAMP, WAMP, MAMP stack or just plain PHP installation.

## System requirements
In order to run WildPHP, we ask a few things from your system. Notably:

- A PHP version equal to or higher than **5.5.0**.
- **SSH** or other local access to the system you plan on running WildPHP on.
	- WildPHP does **NOT** run on services where you can host your website.
- For the best experience, we recommend using **[tmux](https://en.wikipedia.org/wiki/Tmux)** or **[screen](https://en.wikipedia.org/wiki/GNU_Screen)** to allow the bot to run in the background.

## IRC Community & Support
If you need help or just want to idle in the IRC channel join us at
[#wildphp@irc.freenode.net](http://webchat.freenode.net/?channels=wildphp)

## Features and Functions
The bot itself has been designed to include the least features as possible. That means that only installing the core does **not** get you a functional bot.

The bot relies completely on modules, or plugins if you will.

Modules are installed using composer:

    composer require [the package name of the module]
     
For example:

    composer require wildphp/module-pingpong
    
After installation with composer, modules must be enabled in the configuration file. Please read the module description on how to do this.

We have developed a few official modules:

- [module-channelmanager](https://github.com/WildPHP/module-channelmanager), which provides the `join` and `part` commands, and provides auto-joining of channels.
- [module-commandparser](https://github.com/WildPHP/module-commandparser), which allows other modules to listen to commands on the bot.
- [module-nickwatcher](https://github.com/WildPHP/module-nickwatcher), which updates internal references to the nickname.
- [module-pingpong](https://github.com/WildPHP/module-pingpong), which allows the bot to stay online for long periods.

It is recommended to install all of those modules to get a basic bot working which sits in channels. Functionality can be extended from there on with more modules.

## Installation
To install the latest build, you need [Composer](https://getcomposer.org/). Install WildPHP using the following command:

	composer create-project wildphp/wild-irc-bot directory-name --stability=alpha
	
Where directory-name is the name of the directory where to install the bot. The stability flag is required since the bot is in alpha stage.

### Configuration

Copy the example configuration file and edit it to suit you. It uses the [Neon](http://ne-on.org/) syntax (borrowed from [Nette Framework](http://nette.org/en/)). It is similar to yaml but less strict and much faster to parse.

    cp config.example.neon config.neon

## Running the bot

While you can run the bot in the terminal it is best to run it in [tmux](https://en.wikipedia.org/wiki/Tmux) or [screen](https://en.wikipedia.org/wiki/GNU_Screen) so that it can run in background.

    php wildphp.php

### Sample Usage and Output

    <random-user> !say hello there
    <wildphp-bot> hello there

## Contributors

You can see the full list of contributors [in the GitHub repository](https://github.com/WildPHP/Wild-IRC-Bot/graphs/contributors).

### Major Contributors
* [Super3](http://super3.org)
* [Pogosheep](http://layne-obserdia.de)
* [Matejvelikonja](http://velikonja.si)
* [Yoshi2889/NanoSector](https://github.com/Yoshi2889)
* [TimTims](https://timtims.me)
* [Amunak](https://github.com/Amunak)
