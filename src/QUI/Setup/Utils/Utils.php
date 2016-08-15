<?php

namespace QUI\Setup\Utils;

class Utils
{
    public static function normalizePath($path)
    {
        return rtrim(trim($path), '/').'/';
    }
}
