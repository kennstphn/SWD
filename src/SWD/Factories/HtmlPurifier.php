<?php
namespace SWD\Factories;


class HtmlPurifier
{
    protected static $purifier;

    function purify($htmlString, \HTMLPurifier_Config $config = null ){
        return $this->getPurifier()->purify($htmlString,$config);
    }

    protected function getPurifier(){
        if ( is_null(self::$purifier)){
            self::$purifier = new \HTMLPurifier();
        }
        return self::$purifier;
    }

    static function create(){
        $appVersion = str_replace('SWD','App',get_called_class());
        if(class_exists($appVersion)){
            return new $appVersion();
        }
        return new self();
    }
    
    static function safeConfigForUserInput(){
        return null;
    }

}