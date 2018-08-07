<?php
namespace SWD\ApiBuilder\Exception;
class IllegalEventType extends \Exception
{
    function __construct()
    {
        parent::__construct('Unauthorized Event requested. ',403);
    }

}