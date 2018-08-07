<?php
namespace SWD\Modules\TwigRenderer;


use SWD\Request\Request_interface;
use SWD\Response\Response_interface;

class TwigNamespaceControllerFactory implements TwigNamespaceController_interface
{
    const OVERRIDE_CLASS = 'App\\Factories\\TwigNamespaceControllerFactory';
    protected $request,$response;
    public function __construct(Request_interface $request, Response_interface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    function getNamespacedString($string):string
    {
        if ($this->request->get()->containsKey(TwigRenderer::TEMPLATE_NS_KEY)){
            return '@'.$this->request->get()->get(TwigRenderer::TEMPLATE_NS_KEY).'/'.$string;
        }

        if($this->request->cookie()->containsKey(TwigRenderer::TEMPLATE_NS_KEY)){
            return '@'.$this->request->cookie()->get(TwigRenderer::TEMPLATE_NS_KEY).'/'.$string;
        }
        return $string;
    }


    static function create(Request_interface $request, Response_interface $response):\SWD\Modules\TwigRenderer\TwigNamespaceController_interface{
        if ( class_exists(self::OVERRIDE_CLASS)){
            return call_user_func([self::OVERRIDE_CLASS, 'create']);
        }
        $me = new self($request,$response);
        return $me;
    }

}