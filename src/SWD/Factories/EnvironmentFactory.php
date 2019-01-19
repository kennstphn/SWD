<?php
namespace SWD\Factories;
use SWD\Environment\Environment;
use SWD\Environment\Environment_interface;
use SWD\Environment\EnvironmentNotFound;
use SWD\Environment\EnvironmentNotInitialized;

class EnvironmentFactory
{
    const APP_VERSION = '\\App\\Factories\\EnvironmentFactory';


    static protected $found;

    /**
     * @return Environment_interface
     * @throws EnvironmentNotInitialized
     */
    static function find():Environment_interface
    {
        if(class_exists(self::APP_VERSION) && ! get_called_class() == self::APP_VERSION){
            return call_user_func([self::APP_VERSION, 'find']);
        }
        if( ! self::$found){throw new EnvironmentNotInitialized();}
        return self::$found;
    }
    
    static function init($environment,$sourceFolder = null){
        $sourceFolder = $sourceFolder ?? ROOT_DIR.'/environments';

        $file = $sourceFolder.'/'.$environment.'.ini';
        
        if ( ! file_exists($file)){throw new EnvironmentNotFound($environment);}
        if ( ! is_readable($file)){throw new \Exception($file.' is not readable',500);}
        if ( ! is_file($file) ){ throw new \Exception($file.' is not a regular file',500);}
        
        self::$found= new Environment(parse_ini_file($file));
        
    }
    
    static function get($name){
        return self::find()->$name;
    }
    


}