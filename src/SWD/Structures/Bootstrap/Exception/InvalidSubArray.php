<?php
namespace SWD\Structures\Bootstrap\Exception;
class InvalidSubArray extends BootstrapException
{
    function __construct($key, $type, $code = 400, \Exception $previous = null)
    {
        parent::__construct("Key {$key} is not a valid sub array, expected {$type}", $code, $previous);
    }
}