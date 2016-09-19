<?php

namespace QUI\Setup\Utils;

class Utils
{
    /**
     * Makes sure , that the path ends with a trailing slash.
     *
     * @param $path - Raw Path
     * @return string - Path with trailing slash.
     */
    public static function normalizePath($path)
    {
        return rtrim(trim($path), '/') . '/';
    }


    /**
     * Checks if a directory is empty.
     * @param $dir - Path to the directory.
     * @return bool|null - Null, if an error occured. True if dir is empty, false if it is not.
     */
    public static function isDirEmpty($dir)
    {
        if (!is_dir($dir) || !is_readable($dir)) {
            return null;
        }

        $dirHandle = opendir($dir);
        while (($entry = readdir($dirHandle)) !== false) {
            if ($entry != '.' && $entry != '..') {
                return false;
            }
        }

        return true;
    }

    public static function getDirMD5($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $fileHashes = array();

        $directory = dir($dir);

        while (($entry = $directory->read()) !== false) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            if (is_dir($dir . '/' . $entry)) {
                $fileHashes[] = self::getDirMD5($dir . '/' . $entry);
            } else {
                $fileHashes[] = md5($dir . '/' . $entry);
            }
        }
        $directory->close();

        return md5(implode('', $fileHashes));
    }
}
