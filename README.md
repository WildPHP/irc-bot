# Wild IRC Bot ([wildphp.com](http://wildphp.com/))
A modular IRC Bot built in PHP with the use of object-oriented programming.

It is designed to run off a local LAMP, WAMP, MAMP stack or just plain PHP installation.

## IRC Community & Support
If you need help or just want to idle in the IRC channel join us at
[#wildphp@irc.freenode.net](http://webchat.freenode.net/?channels=wildphp)

## Features and Functions

    todo

## Installation

    todo

### Configuration

Copy the example configuration file and edit it to suit you. It uses the [Neon](http://ne-on.org/) syntax (borrowed from [Nette Framework](http://nette.org/en/)). It is similar to yaml but less strict and much faster to parse.

    cp config.example.neon config.neon

## Running the bot

While you can run the bot in the terminal it is best to run it in [tmux](https://en.wikipedia.org/wiki/Tmux) or [screen](https://en.wikipedia.org/wiki/GNU_Screen) so that it can run in background.

    php wildphp.php

### Sample Usage and Output

    <random-user> !say #wildphp hello there
    <wildphp-bot> hello there
    <random-user> !poke #wildphp random-user
    * wildphp-bot pokes random-user

## Contributors

You can see the full list of contributors [in the GitHub repository](https://github.com/WildPHP/Wild-IRC-Bot/graphs/contributors).

### Major Contributors
* [Super3](http://super3.org)
* [Pogosheep](http://layne-obserdia.de)
* [Matejvelikonja](http://velikonja.si)
* [Yoshi2889/NanoSector](https://github.com/Yoshi2889)
* [TimTims](https://timtims.me)
* [Amunak](https://github.com/Amunak)
