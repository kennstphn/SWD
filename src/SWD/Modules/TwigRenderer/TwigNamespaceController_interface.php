<?php
namespace SWD\Modules\TwigRenderer;


use SWD\Request\Request_interface;
use SWD\Response\Response_interface;

interface TwigNamespaceController_interface
{
    function __construct(Request_interface $request, Response_interface $response);
    function getNamespacedString($templateString):string;
}