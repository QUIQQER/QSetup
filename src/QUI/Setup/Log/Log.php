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
        if (!is_writable(self::getLogFile())) {
            echo "\033[1;31m Logfile (" . self::getLogFile() . ") is not writeable! \033[0m" . PHP_EOL;

            return;
        }
        file_put_contents(self::getLogFile(), $msg, FILE_APPEND);
    }

    /**
     * Will add a new Log entry and flag it as Warning message
     * @param $msg - The log message
     */
    public static function warning($msg)
    {
        $msg = "[WARNING] " . $msg . PHP_EOL;
        if (!is_writable(self::getLogFile())) {
            echo "\033[1;31m Logfile (" . self::getLogFile() . ") is not writeable! \033[0m" . PHP_EOL;

            return;
        }
        file_put_contents(self::getLogFile(), $msg, FILE_APPEND);
    }

    /**
     * Will add a new Log entry and flag it as Error message
     * Will add an additional entry into the error.log file.
     * @param $msg - The log message
     */
    public static function error($msg)
    {
        $msg = "[ERROR] " . $msg . PHP_EOL;
        if (!is_writable(self::getLogFile())) {
            echo "\033[1;31m Logfile (" . self::getLogFile() . ") is not writeable! \033[0m" . PHP_EOL;

            return;
        }
        file_put_contents(self::getLogFile(), $msg, FILE_APPEND);


        if (!file_exists(self::getErrorLogFile())) {
            file_put_contents(self::getErrorLogFile(), '');
        }

        if (!is_writable(self::getErrorLogFile())) {
            echo "\033[1;31m Logfile (" . self::getErrorLogFile() . ") is not writeable! \033[0m" . PHP_EOL;

            return;
        }
        file_put_contents(self::getErrorLogFile(), $msg, FILE_APPEND);
    }

    /**
     * Appends a new line with the given text to the logfile
     * @param $msg
     */
    public static function append($msg)
    {
        $msg = $msg . PHP_EOL;
        if (!is_writable(self::getLogFile())) {
            echo "\033[1;31m Logfile (" . self::getLogFile() . ") is not writeable! \033[0m" . PHP_EOL;

            return;
        }
        file_put_contents(self::getLogFile(), $msg, FILE_APPEND);
    }

    /**
     * Appends a new line with the given text to the error logfile
     * @param $msg
     */
    public static function appendError($msg)
    {
        $msg = $msg . PHP_EOL;

        if (!file_exists(self::getErrorLogFile())) {
            file_put_contents(self::getErrorLogFile(), '');
        }

        if (!is_writable(self::getErrorLogFile())) {
            echo "\033[1;31m Logfile (" . self::getErrorLogFile() . ") is not writeable! \033[0m" . PHP_EOL;

            return;
        }
        file_put_contents(self::getErrorLogFile(), $msg, FILE_APPEND);
    }

    /**
     * Returns the path to the log file
     * @return string
     */
    public static function getLogFile()
    {
        if (!empty(self::$logFile)) {
            return self::$logFile;
        }

        $logDir = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/var/log/';
        $file   = $logDir . 'setup.log';


        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        if (!file_exists($file)) {
            file_put_contents($file, "");
        }


        self::$logFile = $file;

        return $file;
    }

    /**
     * Returns the path to the error log file
     * @return string
     */
    public static function getErrorLogFile()
    {
        if (!empty(self::$errorLogFile)) {
            return self::$errorLogFile;
        }

        $logDir = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/var/log/';
        $file   = $logDir . 'error.log';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }


        self::$errorLogFile = $file;

        return $file;
    }
}
