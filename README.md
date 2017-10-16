# Wild IRC Bot
----------
[![Build Status](https://scrutinizer-ci.com/g/WildPHP/irc-bot/badges/build.png)](https://scrutinizer-ci.com/g/WildPHP/irc-bot/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/WildPHP/irc-bot/badges/quality-score.png)](https://scrutinizer-ci.com/g/WildPHP/irc-bot/?branch=master)
[![Scrutinizer Code Coverage](https://scrutinizer-ci.com/g/WildPHP/irc-bot/badges/coverage.png)](https://scrutinizer-ci.com/g/WildPHP/irc-bot/code-structure/master/code-coverage)
[![Latest Stable Version](https://poser.pugx.org/wildphp/Wild-IRC-Bot/v/stable)](https://packagist.org/packages/wildphp/Wild-IRC-Bot)
[![Latest Unstable Version](https://poser.pugx.org/wildphp/Wild-IRC-Bot/v/unstable)](https://packagist.org/packages/wildphp/Wild-IRC-Bot)
[![Total Downloads](https://poser.pugx.org/wildphp/Wild-IRC-Bot/downloads)](https://packagist.org/packages/wildphp/Wild-IRC-Bot)


An advanced and scriptable PHP IRC bot.


It is designed to run off a local LAMP, WAMP, MAMP stack or just plain PHP installation.
No web server is required, only a working PHP installation.

## System requirements
In order to run WildPHP, we ask a few things from your system. Notably:

- A PHP version equal to or higher than **7.1.0**.
- **SSH** or other local access to the system you plan on running WildPHP on.
	- WildPHP does **NOT** run on services where you can host your website. Do not ask for support for doing so.
- WildPHP has been tested to work on **Linux** and **Windows**. Other platforms are supported but not guaranteed to work.
- For the best experience, we recommend using **[tmux](https://en.wikipedia.org/wiki/Tmux)** or **[screen](https://en.wikipedia.org/wiki/GNU_Screen)** to allow the bot to run in the background.

## IRC Community & Support
If you need help or just want to idle in the IRC channel join us at
[#wildphp@irc.freenode.net](http://webchat.freenode.net/?channels=wildphp). Development discussion in [#wildphp-dev@irc.freenode.net](http://webchat.freenode.net/?channels=wildphp-dev).

## Features and Functions
Right now this version of the bot is under heavy development, therefore the feature list is not definitive. We will update this once a reliable list becomes available.

## Installation
To install the latest development build, you need [Composer](https://getcomposer.org/). Install WildPHP using the following commands:

    $ git clone https://github.com/WildPHP/irc-bot
    $ cd irc-bot
    $ composer install

This will pull all Composer dependencies required to run the bot.

### Configuration

Copy the example configuration file and edit it to suit you. It uses the [Neon](http://ne-on.org/) syntax (borrowed from [Nette Framework](http://nette.org/en/)). It is similar to yaml but less strict and much faster to parse.

    $ cp config.neon.sample config.neon

## Running the bot

While you can run the bot in the terminal it is best to run it in [tmux](https://en.wikipedia.org/wiki/Tmux) or [screen](https://en.wikipedia.org/wiki/GNU_Screen) so that it can run in background.

    $ php wildphp.php

## Contributors

You can see the full list of contributors [in the GitHub repository](https://github.com/WildPHP/irc-bot/graphs/contributors).

### Major & Past Major Contributors
* [Super3](http://super3.org)
* [Pogosheep](http://layne-obserdia.de)
* [Matejvelikonja](http://velikonja.si)
* [Yoshi2889](https://github.com/Yoshi2889)
* [TimTims](https://timtims.me)
* [Amunak](https://github.com/Amunak)
