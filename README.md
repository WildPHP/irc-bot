# Wild IRC Bot ([wildphp.com](http://wildphp.com/))
----------
[![Build Status](https://scrutinizer-ci.com/g/WildPHP/Wild-IRC-Bot/badges/build.png?b=3.0)](https://scrutinizer-ci.com/g/WildPHP/Wild-IRC-Bot/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/WildPHP/Wild-IRC-Bot/badges/quality-score.png?b=3.0)](https://scrutinizer-ci.com/g/WildPHP/Wild-IRC-Bot/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/wildphp/Wild-IRC-Bot/v/stable)](https://packagist.org/packages/wildphp/Wild-IRC-Bot)
[![Latest Unstable Version](https://poser.pugx.org/wildphp/Wild-IRC-Bot/v/unstable)](https://packagist.org/packages/wildphp/Wild-IRC-Bot)
[![Total Downloads](https://poser.pugx.org/wildphp/Wild-IRC-Bot/downloads)](https://packagist.org/packages/wildphp/Wild-IRC-Bot)
Initially designed to run as an IRC bot, it now serves as a general-purpose framework for interactive applications.


It is designed to run off a local LAMP, WAMP, MAMP stack or just plain PHP installation.
No web server is required, only a working PHP installation.

## System requirements
In order to run WildPHP, we ask a few things from your system. Notably:

- A PHP version equal to or higher than **7.0.0**.
- **SSH** or other local access to the system you plan on running WildPHP on.
	- WildPHP does **NOT** run on services where you can host your website.
- WildPHP has been tested to work on Linux. Other platforms are supported but not guaranteed to work.
    - Windows is NOT tested on and will most likely NOT work.
- For the best experience, we recommend using **[tmux](https://en.wikipedia.org/wiki/Tmux)** or **[screen](https://en.wikipedia.org/wiki/GNU_Screen)** to allow the bot to run in the background.

## IRC Community & Support
If you need help or just want to idle in the IRC channel join us at
[#wildphp@irc.freenode.net](http://webchat.freenode.net/?channels=wildphp)

## Features and Functions
Right now this version of the bot is under heavy development, therefore the feature list is not definitive. We will update this once a reliable list becomes available.

## Installation
To install the latest build, you need [Composer](https://getcomposer.org/). Install WildPHP using the following command:

	composer create-project wildphp/wild-irc-bot:3.0.x-dev directory-name
	
Where directory-name is the name of the directory where to install the bot.

### Configuration

Copy the example configuration file and edit it to suit you. It uses the [Neon](http://ne-on.org/) syntax (borrowed from [Nette Framework](http://nette.org/en/)). It is similar to yaml but less strict and much faster to parse.

    cp config.neon.sample config.neon

## Running the bot

While you can run the bot in the terminal it is best to run it in [tmux](https://en.wikipedia.org/wiki/Tmux) or [screen](https://en.wikipedia.org/wiki/GNU_Screen) so that it can run in background.

    php wildphp.php

### Sample Usage and Output
Right now the bot does not have any commands yet. These will be added in the future. The bot automatically enters channels specified in the configuration file and will do nothing more than keeping its internal data structures up to date.

## Contributors

You can see the full list of contributors [in the GitHub repository](https://github.com/WildPHP/Wild-IRC-Bot/graphs/contributors).

### Major Contributors
* [Super3](http://super3.org)
* [Pogosheep](http://layne-obserdia.de)
* [Matejvelikonja](http://velikonja.si)
* [Yoshi2889/NanoSector](https://github.com/Yoshi2889)
* [TimTims](https://timtims.me)
* [Amunak](https://github.com/Amunak)
