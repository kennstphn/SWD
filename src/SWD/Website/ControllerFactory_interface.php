<?php
namespace SWD\Website;


use SWD\Request\Request_interface;
use SWD\Response\Response_interface;

interface ControllerFactory_interface
{
    /**
     * @param Request_interface $request
     * @param Response_interface $response
     * @return Controller_interface
     */
    static function find(Request_interface $request, Response_interface $response):Controller_interface;
}