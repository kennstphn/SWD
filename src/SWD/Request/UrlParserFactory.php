<?php
namespace SWD\Request;


class UrlParserFactory
{
    const APP_FACTORY_VERSION = 'App\\Factories\\UrlParserFactory';
    
    /** @return UrlParser_interface */
    static function create(Request_interface $request):UrlParser_interface{
        if(class_exists(self::APP_FACTORY_VERSION) ){
            return call_user_func(array(self::APP_FACTORY_VERSION,'create'),$request);
        }
        //return new RegexUrlParser($request);
        return UrlParser::create($request);
    }

}