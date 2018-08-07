<?php
namespace SWD\Request;


class TypeException extends \Exception
{
    function __construct($message, $code = 400, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}