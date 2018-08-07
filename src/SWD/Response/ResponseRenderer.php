<?php
namespace SWD\Response;


class ResponseRenderer implements ResponseRenderer_interface
{

    protected $renderCallback, $priority, $name, $abilityCheckCallback;

    function __construct(callable $callback, $priority = 50, $name = null, $abilityCheckCallback = null){
        $this->renderCallback = $callback;
        $this->priority = $priority;
        $this->name = $name ? $name : get_called_class();
        $this->abilityCheckCallback = $abilityCheckCallback ? $abilityCheckCallback : function(Response_interface $r){return true;};
    }

    function getPriority():int
    {
        return $this->priority;
    }

    function canRender(Response_interface $response):bool
    {
        return call_user_func($this->abilityCheckCallback, $reponse);
    }

    function render(Response_interface $response)
    {
        call_user_func($this->renderCallback, $response);
    }

    function getName():string
    {
        return $this->name;
    }

}