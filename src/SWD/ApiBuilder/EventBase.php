<?php
namespace SWD\ApiBuilder;


use SWD\Response\Response_interface;
use SWD\Structures\Bootstrap\Bootstrap;

abstract class EventBase
{
    use Bootstrap;
    
    abstract function run(Response_interface $response);

    protected static function setType(){
        throw new \Exception('may not set type');
    }
    protected static function getType(){
        return get_called_class();
    }
}