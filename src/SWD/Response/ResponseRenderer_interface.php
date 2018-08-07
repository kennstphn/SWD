<?php
namespace SWD\Response;


interface ResponseRenderer_interface
{
    
    function getPriority():int;
    function canRender(Response_interface $response):bool;
    function render(Response_interface $response);
    function getName():string;
    
}