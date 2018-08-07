<?php

namespace SWD\DataController;


use SWD\Request\Request_interface;
use SWD\Response\Response_interface;

interface ControlledUrl_interface
{
    static function runController(Request_interface $request, Response_interface $response);
}