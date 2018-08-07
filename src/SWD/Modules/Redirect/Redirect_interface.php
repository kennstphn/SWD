<?php
namespace SWD\Modules\Redirect;


interface Redirect_interface
{
    const ClASSNAME = 'App\\Entities\\Redirect';
    function getFrom();
    
    function getTo();
    
    function getCode();

}