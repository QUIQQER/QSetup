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
        return rtrim(trim($path), '/').'/';
    }
}
