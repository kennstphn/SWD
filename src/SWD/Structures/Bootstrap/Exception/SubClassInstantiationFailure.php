<?php
namespace SWD\Structures\Bootstrap\Exception;


class SubClassInstantiationFailure extends BootstrapException
{
    function __construct($class){
        parent::__construct('Unable to instantiate class '.$class);
        $this->code = 500;
    }
}