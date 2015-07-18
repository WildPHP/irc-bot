# Wild IRC Bot ([wildphp.com](http://wildphp.com/))
A modular IRC Bot built in PHP with the use of object-oriented programming.

It is designed to run off a local LAMP, WAMP, MAMP stack or just plain PHP installation.

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
1. Copy config.example.neon to config.neon
2. Open config.neon with your favourite text editor and change the settings to your liking (check the wiki for more information)
3. Launch the bot with the following command: `php wildphp.php`
4. Profit!

### Configuration

Copy the example configuration file and edit it to suit you. It uses the [Neon](http://ne-on.org/) syntax (borrowed from [Nette Framework](http://nette.org/en/)). It is similar to yaml but less strict and much faster to parse.

    cp config.example.neon config.neon

## Installing modules
Installing modules is as simple as dropping the module folder in the `modules` directory. The bot will automatically load it when it is next started.

## Official extra modules
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
