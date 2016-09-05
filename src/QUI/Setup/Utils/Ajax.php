<?php

namespace QUI\Setup\Utils;

class Ajax
{
    public static function output($data, $code = 200)
    {
        if (!is_string($data)) {
            $data = json_encode($data);
        }

        header($_SERVER["SERVER_PROTOCOL"] . " " . $code);
        echo $data;
        exit;
    }
}
