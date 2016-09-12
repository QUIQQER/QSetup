<?php

namespace QUI\Setup\Log;

class Log
{
    private static $logFile = "";
    private static $errorLogFile = "";

    /**
     * Will add a new Log entry and flag it as Info message
     * @param $msg - The log message
     */
    public static function info($msg)
    {
        $msg = "[INFO] " . $msg . PHP_EOL;
        if (is_writable(self::getLogFile())) {
            file_put_contents(self::getLogFile(), $msg, FILE_APPEND);
        }
    }

    /**
     * Will add a new Log entry and flag it as Warning message
     * @param $msg - The log message
     */
    public static function warning($msg)
    {
        $msg = "[WARNING] " . $msg . PHP_EOL;
        if (is_writable(self::getLogFile())) {
            file_put_contents(self::getLogFile(), $msg, FILE_APPEND);
        }
    }

    /**
     * Will add a new Log entry and flag it as Error message
     * Will add an additional entry into the error.log file.
     * @param $msg - The log message
     */
    public static function error($msg)
    {
        $msg = "[ERROR] " . $msg . PHP_EOL;
        if (is_writable(self::getLogFile())) {
            file_put_contents(self::getLogFile(), $msg, FILE_APPEND);
        }

        if (is_writable(self::getErrorLogFile())) {
            file_put_contents(self::getErrorLogFile(), $msg, FILE_APPEND);
        }
    }

    /**
     * Appends a new line with the given text to the logfile
     * @param $msg
     */
    public static function append($msg)
    {
        $msg = $msg . PHP_EOL;
        if (is_writable(self::getLogFile())) {
            file_put_contents(self::getLogFile(), $msg, FILE_APPEND);
        }
    }

    /**
     * Appends a new line with the given text to the error logfile
     * @param $msg
     */
    public static function appendError($msg)
    {
        $msg = $msg . PHP_EOL;
        if (is_writable(self::getLogFile())) {
            file_put_contents(self::getLogFile(), $msg, FILE_APPEND);
        }
    }

    /**
     * Returns the path to the log file
     * @return string
     */
    private static function getLogFile()
    {


        if (!empty(self::$logFile)) {
            return self::$logFile;
        }

        $logDir = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/logs/';
        $file   = $logDir . 'setup.log';

        if (!file_exists($file)) {
            file_put_contents($file, "");
        }

        echo "=============================" . PHP_EOL;
        echo "Log Dir : " . $logDir . PHP_EOL;
        echo "logfile : " . $file . PHP_EOL;
        echo "=============================" . PHP_EOL;

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        self::$logFile = $file;

        return $file;
    }

    /**
     * Returns the path to the error log file
     * @return string
     */
    private static function getErrorLogFile()
    {
        if (!empty(self::$errorLogFile)) {
            return self::$errorLogFile;
        }

        $logDir = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/logs/';
        $file   = $logDir . 'error.log';

        if (!file_exists($file)) {
            file_put_contents($file, "");
        }
        echo "=============================" . PHP_EOL;
        echo "Log Dir : " . $logDir . PHP_EOL;
        echo "logfile : " . $file . PHP_EOL;
        echo "=============================" . PHP_EOL;


        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        self::$errorLogFile = $file;

        return $file;
    }
}