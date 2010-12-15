<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Logger.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * Logger singleton
 *
 * Crawler logger outputs information about crawler to terminal or to file, depending on configuration.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Logger {
    /**
     *
     * @var Logger singleton instance
     */
    private static $instance;
    /**
     *
     * @var resource Open file pointer
     */
    var $log = null;
    /**
     *
     * @var str $network_username The user we're logging about
     */
    var $network_username = null;
    /**
     *
     * @var int All (user and developer) messages
     */
    const ALL_MSGS = 0;
    /**
     *
     * @var int User-level messages
     */
    const USER_MSGS = 1;
    /**
     *
     * @var int Information-type messages
     */
    const INFO = 0;
    /**
     *
     * @var int Error-type messages
     */
    const ERROR = 1;
    /**
     *
     * @var int Success-type messages
     */
    const SUCCESS = 2;
    /**
     * @var int debugging messages -- log only
     */
    const DEBUG = 3;
    /**
     *
     * @var int Log verbosity level (either self::ALL_MSGS or self::USER_MSGS)
     */
    var $verbosity = 0;
    /**
     * @var bool whether to output debug-level log stmts
     */
    var $debug = false;
    /**
     * @var bool Whether or not output should be HTML
     */
    var $html_output = false;
    /**
     * Open the log file; Append to any prior file
     * @param str $location
     */
    public function __construct($location, $debug = false) {
        if ( $location != false ) {
            $this->log = $this->openFile($location, 'a');
        }
        $this->debug = $debug;
    }

    /**
     * The singleton constructor
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            $config = Config::getInstance();
            $debug = $config->getValue('debug') ? true : false;
            self::$instance = new Logger($config->getValue('log_location'), $debug);
        }
        return self::$instance;
    }

    /**
     * Set username
     * @param str $username
     */
    public function setUsername($username) {
        $this->network_username = $username;
    }

    /**
     * Set the verbosity level of the log.
     * @param int $level Either self::ALL_MSGS or self::USER_MSGS
     */
    public function setVerbosity($level) {
        $this->verbosity = $level;
    }

    /**
     * Turn on HTML output.
     */
    public function enableHTMLOutput() {
        $this->html_output = true;
    }
    /**
     * Write to log
     * @param str $status_message
     * @param str $classname The name of the class logging the info
     */
    private function logStatus($status_message, $classname, $verbosity = self::ALL_MSGS, $type = self::INFO) {
        if ($this->verbosity <= $verbosity) {
            if (!$this->html_output) {
                $status_signature = date("Y-m-d H:i:s", time())." | ".
                (string) number_format(round(memory_get_usage() / 1024000, 2), 1)."MB | ";
                switch ($type) {
                    case self::ERROR:
                        $status_signature .= 'ERROR  | ';
                        break;
                    case self::SUCCESS:
                        $status_signature .= 'SUCCESS| ';
                        break;
                    case self::DEBUG:
                        $status_signature .= 'DEBUG  | ';
                        break;
                    default:
                        $status_signature .= 'INFO   | ';
                }
                if (isset($this->network_username)) {
                    $status_signature .= $this->network_username .' | ';
                }
                $status_signature .= $classname." | ";
                if (strlen($status_message) > 0) {
                    $this->output($status_signature.$status_message); # Write status to log
                }
            } else {
                $message_wrapper = '<span style="color:#ccc">'.date("H:i", time()).'</span> ';
                $just_classname = explode('::', $classname);
                if (isset($just_classname[0])) {
                    if ( $just_classname[0] == 'CrawlerTwitterAPIAccessorOAuth') {
                        $just_classname[0] = 'TwitterCrawler';
                    }
                    if ( strtoupper(substr ( $just_classname[0] , strlen($just_classname[0])-3, 3  ))  == 'DAO') {
                        $just_classname[0] = 'Database';
                    }
                    $message_wrapper .= $just_classname[0].": ";
                }
                $message_wrapper .= '<span style="color:';
                switch ($type) {
                    case self::ERROR:
                        $message_wrapper .= 'red">';
                        break;
                    case self::SUCCESS:
                        $message_wrapper .= 'green">';
                        break;
                    default:
                        $message_wrapper .= 'black">';
                }
                if (strlen($status_message) > 0) {
                    $this->output($message_wrapper.$status_message."</span><br >"); // Write status to log
                }
            }
        }
    }

    /**
     * Write info message to log.
     * @param str $status_message
     * @param str $classname
     */
    public function logInfo($status_message, $classname) {
        $this->logStatus($status_message, $classname, self::ALL_MSGS, self::INFO);
    }

    /**
     * Write debug message to log if 'debug' config var is set to 'true'.
     * @param str $status_message
     * @param str $classname
     */
    public function logDebug($status_message, $classname) {
        if ($this->debug) {
            $this->logStatus($status_message, $classname, self::ALL_MSGS, self::DEBUG);
        }
    }

    /**
     * Write error message to log.
     * @param str $status_message
     * @param str $classname
     */
    public function logError($status_message, $classname) {
        $this->logStatus($status_message, $classname, self::ALL_MSGS, self::ERROR);
    }

    /**
     * Write success message to log.
     * @param str $status_message
     * @param str $classname
     */
    public function logSuccess($status_message, $classname) {
        $this->logStatus($status_message, $classname, self::ALL_MSGS, self::SUCCESS);
    }


    /**
     * Write user-level info message to log.
     * @param str $status_message
     * @param str $classname
     */
    public function logUserInfo($status_message, $classname) {
        $this->logStatus($status_message, $classname, self::USER_MSGS, self::INFO);
    }

    /**
     * Write user-level error message to log.
     * @param str $status_message
     * @param str $classname
     */
    public function logUserError($status_message, $classname) {
        $this->logStatus($status_message, $classname, self::USER_MSGS, self::ERROR);
    }

    /**
     * Write user-level success message to log.
     * @param str $status_message
     * @param str $classname
     */
    public function logUserSuccess($status_message, $classname) {
        $this->logStatus($status_message, $classname, self::USER_MSGS, self::SUCCESS);
    }

    /**
     * Add a little whitespace to log file
     */
    private function addBreaks() {
        $this->output("");
    }

    /**
     * Close the log file
     */
    public function close() {
        $this->addBreaks();
        $this->closeFile($this->log);
        self::$instance = null;
    }

    /**
     * Open log file
     * @param str $filename
     * @param unknown_type $type
     */
    protected function openFile($filename, $type) {
        if (array_search($type, array('w', 'a')) < 0) {
            $type = 'w';
        }
        $filehandle = null;
        if (is_writable($filename)) {
            $filehandle = fopen($filename, $type);// or die("can't open file $filename");
        }
        return $filehandle;
    }

    /**
     * Output log message to file or terminal
     * @param str $message
     */
    protected function output($message) {
        if (isset($this->log)) {
            return fwrite($this->log, $message."\n");
        } else {
            echo $message.'
';
            @flush();
        }
    }

    /**
     * Close file
     * @param resource $filehandle
     */
    protected function closeFile($filehandle) {
        if (isset($filehandle)) {
            return fclose($filehandle);
        }
    }

    /**
     * Delete log file
     * @param str $filename
     */
    protected function deleteFile($filename) {
        return unlink($filename);
    }
}