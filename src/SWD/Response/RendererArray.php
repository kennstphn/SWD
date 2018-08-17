<?php
namespace SWD\Response;


use Doctrine\Common\Collections\ArrayCollection;

class RendererArray 
{
    function __construct()
    {
        $this->data = new ArrayCollection();
    }
    
    function add(ResponseRenderer_interface $renderer){
        $this->data->add($renderer);
    }
    
    function filter(callable $callback){
        return $this->data->filter($callback);
    }
    
    function containsName($name){
        return $this->countByName($name) > 0;
    }

    /**
     * @param $name
     * @return ResponseRenderer_interface[]
     */
    function getByName($name){
        return $this->data->filter(
            function(ResponseRenderer_interface $r) use ($name){ return $r->getName() == $name;}
        );
    }
    
    function countByName($name){
        return $this->getByName($name)->count();
    }
    
    function isEmpty(){
        return $this->data->count() > 0 ? false : true;
    }
    
    function render(Response_interface $response){

        $array = $this->data->filter(
            function (ResponseRenderer_interface $renderer)use ($response){
                return $renderer->canRender($response);
            }
        )->toArray();
        usort($array, function(ResponseRenderer_interface $a, ResponseRenderer_interface $b){
            if ($a->getPriority() == $b->getPriority() && $a->getName() == $b->getName()){
                return 0;
            }
            if ($a->getPriority() == $b->getPriority()){
                return $a->getName() < $b->getName() ? -1 : 1;
            }
            return ( ( $a->getPriority() - $b->getPriority() ) < 0 ) ? -1 : 1 ;
        });


        $success = false;
        /** @var ResponseRenderer_interface $renderer */
        foreach($array as $renderer){if( ! $success){
            try{
                $renderer->render($response);
                $success = true;
            }catch (\Exception $e){
                $success = false;
            }
        }}

        return $success;
    }
    
}