<?php

use WildPHP\Core\Storage\Providers\SQLiteDatabaseStorageProvider;

return [
    'connection' => [
        # The server to connect to.
        # The secure option turns on SSL for this connection.
        # Make sure to use an SSL-enabled server and port before turning this on.
        'server' => 'irc.freenode.net',
        'port' => 6697,
        'secure' => true,

        # Nickname to use.
        # Takes an array of strings to swap between nicknames if the first one is not available.
        'nickname' => [
            'MyBot'
        ],

        # The username to use. Not really used by modern IRC servers, pick a short string.
        'username' => 'MyBot',

        # The realname to use, identifying the bot on the network. May be a string of your choosing.
        'realname' => 'A WildPHP Bot',

        # Optional password to be sent while connecting to the server. Leave blank to disable.
        'password' => '',

        # The channels to join after connecting.
        'channels' => [
            '#channel'
        ],

        # Uncomment the following to enable SASL functionality if your network supports it.
        # This can be used to for example identify with services while connecting,
        # instead of logging in to services afterwards.
        //'sasl' => [
        //    'username' => 'myUsername',
        //    'password' => 'mySekritPassword'
        //],

        # You can set additional connection options here.
        # Please see https://github.com/reactphp/socket#connector for more details.
        # Example options below:
        //'options'  => [
        //    'dns' => false,
        //    'tcp' => [
        //        'bindto' => '192.168.1.11'
        //    ]
        //]
    ],

    # Storage driver used for persistent storage
    # Available configuration options depend on the chosen provider.
    #
    # Available providers:
    # - JSON (default): \WildPHP\Core\Storage\Providers\JsonStorageProvider
    # - SQLite: \WildPHP\Core\Storage\Providers\SQLiteDatabaseStorageProvider
    'storage' => [
        'provider' => new \WildPHP\Core\Storage\Providers\JsonStorageProvider(WPHP_ROOT_DIR . '/storage')
    ],

    # Command prefix used for command parsing.
    'prefix' => '!',

    # The bot owner who gets all permissions by default.
    # Set this to the username used to identify with authentication services like NickServ (freenode)
    'owner' => 'NickServUsername',

    # The log level to use.
    # One of debug, info, warning, error
    # It is recommended to leave this in debug for now as the other modes barely show information.
    'log_level' => 'debug',

    # The list of modules to use. Each line takes a PHP class name;
    # consult the module documentation if you are unsure what to place here.
    # Please note some modules may require additional configuration, please put this configuration below this section.
    'modules' => [

    ],

    ###
    ### MODULE SPECIFIC CONFIGURATION BELOW
    ###

];