<?php
namespace SWD\ApiBuilder\Exception;
class MissingEventType extends \Exception
{
    function __construct()
    {
        parent::__construct($message = 'Missing required "type" from array', $code = 400);
    }

}