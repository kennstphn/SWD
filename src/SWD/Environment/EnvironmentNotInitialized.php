<?php
namespace SWD\Environment;
class EnvironmentNotInitialized extends \Exception
{
    function __construct($message = 'Environment not initialized', $code = 404, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}