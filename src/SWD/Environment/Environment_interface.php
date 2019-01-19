<?php

namespace SWD\Environment;


interface Environment_interface
{
    function __construct(array $data);

    function get(string $key);

    function dbname():string;
    function dbuser():string;

    function dbpwd():string;

    function environment():string;

    function domain():string;

    function __get(string $name);
    
}