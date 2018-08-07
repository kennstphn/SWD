<?php
namespace SWD\Modules\TwigRenderer;


use SWD\Request\Request_interface;

interface TemplateDefining_interface
{
    static function getTemplate(Request_interface $request):string ;
}