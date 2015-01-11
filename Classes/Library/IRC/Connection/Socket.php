<?php
    /**
     * IRC Bot
     *
     * LICENSE: This source file is subject to Creative Commons Attribution
     * 3.0 License that is available through the world-wide-web at the following URI:
     * http://creativecommons.org/licenses/by/3.0/.  Basically you are free to adapt
     * and use this script commercially/non-commercially. My only requirement is that
     * you keep this header as an attribution to my work. Enjoy!
     *
     * @license    http://creativecommons.org/licenses/by/3.0/
     *
     * @package IRCBot
     * @subpackage Library
     *
     * @encoding UTF-8
     * @created Jan 11, 2012 11:02:00 PM
     *
     * @author Daniel Siepmann <coding.layne@me.com>
     */

    namespace Library\IRC\Connection;

    /**
     * Delivers a connection via socket to the IRC server.
     *
     * @package IRCBot
     * @subpackage Library
     * @author Daniel Siepmann <Daniel.Siepmann@wfp2.com>
     */
    class Socket implements \Library\IRC\Connection {

        /**
         * The server you want to connect to.
         * @var string
         */
        private $server = '';

        /**
         * The port of the server you want to connect to.
         * @var integer
         */
        private $port = 0;

        /**
         * The TCP/IP connection.
         * @var resource
         */
        private $socket;
        
        /**
         * The password used for connecting.
         * @var string
         */
        private $password = '';
        private $name = '';
        private $nick = '';

        /**
         * Close the connection.
         */
        public function __destruct() {
            $this->disconnect();
        }

        /**
         * Establishs the connection to the server. If no arguments passed, will use the defaults.
         * @param string $server The server to connect to.
         * @param int    $port   The port to use for connecting to the server.
         * @param string $nick   The nickname used to talk with the server.
         * @param string $name   The name (ident) used to connect to the server.
         * @param string $pass   The password to use to log in to the server.
         * @return boolean True or false, depending on whether the connection succeeded.
         */
        public function connect($server = '', $port = '', $nick = '', $name = '', $pass = '') {
            // Set defaults if no arguments passed.
            if (empty($server))
                $server = $this->server;
            if (empty($port))
                $port = $this->port;
            if (empty($nick))
                $nick = $this->nick;
            if (empty($name))
                $name = $this->name;
            if (empty($pass))
                $pass = $this->password;
                
            // Open a connection.
            $this->socket = fsockopen($server, $port);
            if (!$this->isConnected())
                throw new Exception('Unable to connect to server via fsockopen with server: "' . $server . '" and port: "' . $port . '".');
          
            if (!empty($pass))
                $this->sendData('PASS ' . $pass);

            $this->sendData( 'USER ' . $nick . ' Layne-Obserdia.de ' . $nick . ' :' . $name);
            $this->sendData( 'NICK ' . $nick );
        }

        /**
         * Disconnects from the server.
         *
         * @return boolean True if the connection was closed. False otherwise.
         */
        public function disconnect() {
            if ($this->socket) {
                return fclose( $this->socket );
            }
            return false;
        }
        
        public function reconnect()
        {
            $this->disconnect();
            $this->connect();
        }

        /**
         * Interaction with the server.
         * For example, send commands or some other data to the server.
         *
         * @return boolean|int the number of bytes written, or FALSE on error.
         */
        public function sendData( $data ) {
            return fwrite( $this->socket, $data . "\r\n" );
        }

        /**
         * Returns data from the server.
         *
         * @return string|boolean The data as string, or false if no data is available or an error occured.
         */
        public function getData() {
            return trim(fgets( $this->socket, 256 ));
        }

        /**
         * Check wether the connection exists.
         *
         * @return boolean True if the connection exists. False otherwise.
         */
        public function isConnected() {
            return is_resource( $this->socket );
        }

        /**
         * Sets the server.
         * E.g. irc.quakenet.org or irc.freenode.org
         * @param string $server The server to set.
         */
        public function setServer( $server ) {
            $this->server = (string) $server;
        }

        /**
         * Sets the port.
         * E.g. 6667
         * @param integer $port The port to set.
         */
        public function setPort( $port ) {
            $this->port = (int) $port;
        }
        
        /**
         * Set the password used for connecting.
         * @param string $pass The password to set.
         */
        public function setPassword($pass)
        {
            $this->password = (string) $pass;
        }
        
        /**
         * Set the hostname used for connecting.
         * @param string $name The hostname to set.
         */
        public function setName($name)
        {
            $this->name = (string) $name;
        }
        
        /**
         * Set the nick used for connecting.
         * @param string $nick The nickname to set.
         */
        public function setNick($nick)
        {
            $this->nick = (string) $nick;
        }

    }
?>
