<?php
namespace SWD\Modules;


use SWD\Response\Response_interface;
use SWD\Website\Module;
use SWD\Website\Website;

class DataControllerHookBridge extends Module
{
    static protected $listeners = [];
    function __invoke(string $hookName, Website $website)
    {
        if ( $hookName !== $website::CONTROLLER_SECTION_DONE){
            throw new \Exception('Hook should be '.$website::CONTROLLER_SECTION_DONE.', not '.$hookName);
        }
        for($i = 0, $c = count(self::$listeners);$i< $c;$i++){
            if ($website->response() === self::$listeners[$i]['response']){
                $website->invokeModulesByHook(self::$listeners[$i]['hook']);
            }
        }
    }

    static function init(Website $website){
        $website->addModule($website::CONTROLLER_SECTION_DONE, DataControllerHookBridge::class);
    }

    static function queue(string $hook, Response_interface $response){
        array_push(self::$listeners, ['hook'=>$hook, 'response'=>$response]);
    }



}