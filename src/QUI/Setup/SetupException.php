<?php

namespace QUI\Setup;

class SetupException extends \QUI\Exception
{

    const ERROR_MISSING_RESSOURCE = 404;

    const ERROR_UNKNOWN = 500;
    const ERROR_INVALID_ARGUMENT = 501;
    const ERROR_PERMISSION_DENIED = 503;
}
