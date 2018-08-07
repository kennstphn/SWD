<?php
namespace SWD\Request;
use SWD\Request\ArrayCollection_superglobal;
use SWD\Request\UrlExtended;

interface Request_interface
{
    function get():ArrayCollection_superglobal;

    function post():ArrayCollection_superglobal;
    
    function session():ArrayCollection_superglobal;
    
    function server():ArrayCollection_superglobal;
    
    function env():ArrayCollection_superglobal;
    
    function files():ArrayCollection_superglobal;
    
    function cookie():ArrayCollection_superglobal;
    
    function request():ArrayCollection_superglobal;
    
    function url():UrlExtended;

    function method():string;
}
