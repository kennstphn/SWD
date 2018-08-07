<?php
namespace SWD\Website;


class CsrfException extends \Exception
{
    const MISSING_CLIENT_CODE = 400;
    const MISSING_SERVER_CODE = 409;
    const CLIENT_SERVER_MISMATCH = 403;

    function __construct($code, \Exception $previous = null)
    {
        parent::__construct('potential csrf attack detected', $code, $previous);
    }

}