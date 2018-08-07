<?php

namespace SWD\DataController\Search;


use SWD\AutoRouter\ArgumentPattern;
use SWD\AutoRouter\AutoRouterDependency;
use SWD\AutoRouter\Trait_FallbackOrFalse;
use SWD\Truitt\EntityManager;

class SearchController
{
    use Trait_FallbackOrFalse;
    protected $entityClass;
    protected $endPoint,$dependency;
    
    function __construct($endPoint, AutoRouterDependency $dependency)
    {
        $this->endPoint = $endPoint;
        $this->dependency = $dependency;
    }

    function __invoke()
    {
        $this->validateEntityClass();

        $args = func_get_args();

        $searchTerms = array_slice($args, 1);

        $pattern = new ArgumentPattern($args, $this->dependency->getRequestMethod());
        
        if ( ! $pattern->startsWith('search')){ return $this->fallbackControllerOrFalse($args);}

        $this->validateEndpoint('search');
        
        if (strtolower($this->dependency->getRequestMethod()) === 'post'){
            $this->validateEndpoint('searchHandler');
        }
        
        if ($pattern->isPost()){
            return call_user_func_array(array($this->endPoint,'searchHandler'),$searchTerms);
        }
        
        if ($pattern->isGet()){
            return call_user_func_array(array($this->endPoint, 'search'), $searchTerms);
        }
        
        return $this->fallbackControllerOrFalse($args);
        
    }


    function setEntityClass($entityClass){
        $this->entityClass = $entityClass;
    }
    /**
     * @throws \Exception
     */
    protected function validateEntityClass(){
        if (is_null($this->entityClass)){throw new \Exception('Missing setEntityClass call in SearchController', 500);}
        if ( ! class_exists($this->entityClass)){
            throw new \Exception('Unable to find class ('.$this->entityClass.')', 404);
        }
    }
    
    protected function validateEndpoint($func){
        if ( ! is_object($this->endPoint)){throw new \Exception("Invalid endpoint of type(".gettype($this->endPoint).")", 500);}
        if ( ! is_callable(array($this->endPoint, $func))){
            throw new \Exception('Endpoint ('.get_class($this->endPoint).') is missing callable "'.$func.'"', 500);
        }
        
    }

}