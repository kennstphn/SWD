<?php
namespace SWD\AppControllers;


use SWD\DataController\ControlledUrl_interface;
use SWD\DataController\DynamicActionArgument;
use SWD\DataController\EntityController;
use SWD\Request\Request_interface;
use SWD\Response\Response_interface;

abstract class Sitemap implements ControlledUrl_interface
{
    static function runController(Request_interface $request, Response_interface $response)
    {
        $entityController = new EntityController('App\\Entities\\Sitemap', $request, $response);

        $entityController->setAction('html', array(get_called_class(),'html'));
        $entityController->setAction('xml', array(get_called_class(), 'xml'));

    }

    abstract static function html(DynamicActionArgument $a);
    
    abstract static function xml(DynamicActionArgument $a);
}