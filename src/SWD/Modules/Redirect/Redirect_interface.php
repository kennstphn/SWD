<?php
namespace SWD\Modules\Redirect;


interface Redirect_interface
{
    const CLASSNAME = 'App\\Entities\\Redirect';
    function getFrom();
    
    function getTo();
    
    function getCode();

}