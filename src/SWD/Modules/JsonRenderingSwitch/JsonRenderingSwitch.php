<?php
namespace SWD\Modules\JsonRenderingSwitch;


use SWD\Response\Response_interface;
use SWD\Response\ResponseRenderer;
use SWD\Website\Module;
use SWD\Website\Website;

class JsonRenderingSwitch extends Module
{
    function __invoke(string $hookName, Website $website)
    {
        if ($website->request()->get()->getBool('json')){
            $website->response()->addRenderCallback(new ResponseRenderer(
                function (Response_interface $response){
                    $response->renderJson();},
                0,get_called_class(),function (){return true;}
            ));
        }
    }

}