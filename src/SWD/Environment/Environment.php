<?php
namespace SWD\Environment;
class Environment implements Environment_interface
{
    
    
    public function __construct(array $data)
    {
        $this->data =$data;
    }

    function get(string $key)
    {
        return array_key_exists($key, $this->data )?$this->data[$key]:null;
    }
    
    function dbname():string
    {
        return $this->dbname;
    }

    function dbuser():string
    {
        return $this->dbuser;
    }

    function dbpwd():string
    {
        return $this->dbpwd;
    }

    function environment():string
    {
        return $this->environment;
    }

    function domain():string
    {
        return $this->domain;
    }


    function __get(string $name)
    {
        return $this->get($name);
    }
    
}