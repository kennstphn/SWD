<?php
namespace SWD\Helper;
class StringConversions
{
    static function classToDot($string){
        return str_replace(['\\','..'],'.' ,$string );
    }

    static function dotToClassName($dotString){
        $class = str_replace('.','\\',$dotString);
        return $class;
    }
}