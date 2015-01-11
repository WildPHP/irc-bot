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
     * @author Daniel Siepmann <coding.layne@me.com>
     *
     * @encoding UTF-8
     * @created 30.12.2011 21:45:07
     *
     * @filesource
     */

    namespace Library;

    /**
     * Description of FunctionCollection
     *
     * @package IRCBot
     * @subpackage Library
     * @author Daniel Siepmann <Daniel.Siepmann@wfp2.com>
     */
    class FunctionCollection {

        /**
         * Removes line breaks from a string.
         *
         * @param string $string The string with line breaks.
         * @return string
         * @author Daniel Siepmann <coding.layne@me.com>
         */
        public static function removeLineBreaks( $string ) {
            return str_replace( array ( chr( 10 ), chr( 13 ) ), '', $string );
        }
        /**
         * Returns class name of $object without namespace
         *
         * @param mixed $object
         * @author Matej Velikonja <matej@velikonja.si>
         * @return string
         */
        public static function getClassName( $object) {
            $objectName = explode( '\\', get_class( $object ) );
            $objectName = $objectName[count( $objectName ) - 1];
    
            return $objectName;
        }
        
        public static function getUserNickName($data) {
            $result = preg_match('/:([a-zA-Z0-9_]+)!/', $data, $matches);
    
            if ($result !== false) {
                if (!empty($matches[1]))
                    return $matches[1];
            }
    
            return false;
        }
        
        /**
         * Fetches data from $uri
         *
         * @param string $uri
         * @return string
         */
        public static function fetch($uri) {
            // create curl resource
            $ch = curl_init();
    
            // set url
            curl_setopt($ch, CURLOPT_URL, $uri);
            
            // user agent.
            curl_setopt($ch, CURLOPT_USERAGENT, 'WildPHP/IRCBot');
    
            //return the transfer as a string
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    
            // $output contains the output string
            $output = curl_exec($ch);
    
            // close curl resource to free up system resources
            curl_close($ch);
    
            //$this->bot->log("Data fetched: " . $output);
    
            return $output;
        }

    }
?>
