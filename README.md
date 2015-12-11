# Wild IRC Bot ([wildphp.com](http://wildphp.com/))
----------
[![Build Status](https://scrutinizer-ci.com/g/WildPHP/Wild-IRC-Bot/badges/build.png?b=master)](https://scrutinizer-ci.com/g/WildPHP/Wild-IRC-Bot/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/WildPHP/Wild-IRC-Bot/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/WildPHP/Wild-IRC-Bot/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/wildphp/Wild-IRC-Bot/v/stable)](https://packagist.org/packages/wildphp/Wild-IRC-Bot)
[![Latest Unstable Version](https://poser.pugx.org/wildphp/Wild-IRC-Bot/v/unstable)](https://packagist.org/packages/wildphp/Wild-IRC-Bot)
[![Total Downloads](https://poser.pugx.org/wildphp/Wild-IRC-Bot/downloads)](https://packagist.org/packages/wildphp/Wild-IRC-Bot)
Initially designed to run as an IRC bot, it now serves as a general-purpose framework for interactive applications.


It is designed to run off a local LAMP, WAMP, MAMP stack or just plain PHP installation.
No web server is required, only a working PHP installation.

## System requirements
In order to run WildPHP, we ask a few things from your system. Notably:

- A PHP version equal to or higher than **5.5.0**.
- **SSH** or other local access to the system you plan on running WildPHP on.
	- WildPHP does **NOT** run on services where you can host your website.
- WildPHP has been tested to work on Linux. Other platforms are supported but not guaranteed to work.
- For the best experience, we recommend using **[tmux](https://en.wikipedia.org/wiki/Tmux)** or **[screen](https://en.wikipedia.org/wiki/GNU_Screen)** to allow the bot to run in the background.

## IRC Community & Support
If you need help or just want to idle in the IRC channel join us at
[#wildphp@irc.freenode.net](http://webchat.freenode.net/?channels=wildphp)

## Features and Functions
The framework itself has been designed to include the least features as possible. That means that only installing the core will only get you the runtime.

The framework relies completely on modules, or plugins if you will.

Modules are installed using composer:

    composer require [the package name of the module]
     
For example:

    composer require wildphp/module-pingpong
    
After installation with composer, modules must be enabled and possibly configured. Please read the module's description on how to do this.

We have developed a few official modules:

### Core modules:
- [module-ircconnection](https://github.com/WildPHP/module-ircconnection), which provides the connection to IRC networks.
- [module-channelmanager](https://github.com/WildPHP/module-channelmanager), which provides the `join` and `part` commands, and provides auto-joining of channels.
- [module-commandparser](https://github.com/WildPHP/module-commandparser), which allows other modules to listen to user commands on the bot.
- [module-nickwatcher](https://github.com/WildPHP/module-nickwatcher), which updates internal references to the nickname when it is changed.

All of these should be installed to get a usable IRC bot.

### Optional modules:
- [module-sasl](https://github.com/WildPHP/module-sasl), which allows the bot to authenticate itself using SASL.
- [module-linksniffer](https://github.com/WildPHP/module-linksniffer), with which the bot can detect links pasted in a channel and show their titles.
- [module-wiki](https://github.com/WildPHP/module-wiki), with which users can search MediaWikis (like Wikipedia) through the `wiki` command.

## Installation
To install the latest build, you need [Composer](https://getcomposer.org/). Install WildPHP using the following command:

	composer create-project wildphp/wild-irc-bot directory-name
	
Where directory-name is the name of the directory where to install the bot.

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
