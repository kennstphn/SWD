<?php
namespace SWD\Website;


abstract class Module
{
    abstract function __invoke(string $hookName, Website $website);
    
    function addListener(string $eventName, $callback, Website $website){
        $website->addModule($eventName, $callback);
        $website->modules()->invokeModulesByHook($eventName, $website);
    }
    
    function triggerEvent(string $eventName, Website $website){
        $website->modules()->invokeModulesByHook($eventName, $website);
    }
}