# Wild IRC Bot ([wildphp.com](http://wildphp.com/))
----------
[![Build Status](https://scrutinizer-ci.com/g/WildPHP/Wild-IRC-Bot/badges/build.png?b=master)](https://scrutinizer-ci.com/g/WildPHP/Wild-IRC-Bot/build-status/master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/WildPHP/Wild-IRC-Bot/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/WildPHP/Wild-IRC-Bot/?branch=master)

A modular IRC Bot built in PHP with the use of object-oriented programming.

It is designed to run off a local LAMP, WAMP, MAMP stack or just plain PHP installation.

## System requirements
In order to run WildPHP, we ask a few things from your system. Notably:

- A PHP version equal to or higher than **5.4.0**.
- **SSH** or other local access to the system you plan on running WildPHP on.
	- WildPHP does **NOT** run on services where you can host your website.
- For the best experience, we recommend using **[tmux](https://en.wikipedia.org/wiki/Tmux)** or **[screen](https://en.wikipedia.org/wiki/GNU_Screen)** to allow the bot to run in the background.

## IRC Community & Support
If you need help or just want to idle in the IRC channel join us at
[#wildphp@irc.freenode.net](http://webchat.freenode.net/?channels=wildphp)

## Features and Functions
Apart from being awesome, this bot features the following functions:

- Pre-installed modules:
	- Auth: Provides basic hostname-based authentication. Most modules depend on this
	- ChannelAdmin: Provides commands like `op`, `voice`, `kick`
	- ChannelManager: Provides commands like `join`, `part`, and keeps track of channel joins and parts
	- CoreCommands: Provides `quit` and `say` commands.
	- Help: Provides the `help` command
	- Watchdog: Watches for PING PONG requests and automatically attempts to reconnect the bot if the connection is assumed lost
- Easy to install modules, complete with dependency management
- Full-featured event-driven API that's easy to hook into,
- Timers that trigger after a set time,

## Installation
1. Download the latest release.
2. Copy config.example.neon to config.neon
3. Open config.neon with your favourite text editor and change the settings to your liking (check the wiki for more information)
4. Launch the bot with the following command: `php wildphp.php`
5. Profit!

### Installing the latest revision
To install the latest development build, you need [Composer](https://getcomposer.org/). Install WildPHP using the following command:

	composer create-project wildphp/wild-irc-bot directory-name

### Configuration

Copy the example configuration file and edit it to suit you. It uses the [Neon](http://ne-on.org/) syntax (borrowed from [Nette Framework](http://nette.org/en/)). It is similar to yaml but less strict and much faster to parse.

    cp config.example.neon config.neon

## Installing modules
Installing modules is as simple as dropping the module folder in the `modules` directory. The bot will automatically load it when it is next started.

### Official extra modules
Modules which have been tested to work with the latest version of the bot by us can be found [here](https://github.com/WildPHP/Wild-IRC-Bot-Plugins).

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
