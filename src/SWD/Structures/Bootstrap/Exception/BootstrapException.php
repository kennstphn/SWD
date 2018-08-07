<?php
namespace SWD\Structures\Bootstrap\Exception;
class BootstrapException extends \Exception
{
    function __construct( $message = '', $code = 400, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}