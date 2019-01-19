<?php
namespace SWD\Environment;
class EnvironmentNotFound extends \Exception
{
    public $environment;
    function __construct($environment, $code = 404, \Exception $previous = null)
    {
        $this->environment = $environment;
        parent::__construct("Environment ({$environment}) not found", $code, $previous);
    }


}