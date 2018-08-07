<?php
namespace SWD\Website;


use SWD\Request\Request_interface;
use SWD\Response\Response_interface;

interface Controller_interface
{
    function __invoke();
}