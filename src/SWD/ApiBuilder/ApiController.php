<?php
namespace SWD\ApiBuilder;

use SWD\AppControllers\UrlController;

class ApiController extends UrlController
{
    protected $getEvents = [];
    protected $postEvents = [];

    protected $additionalGetContext=[];
    protected $additionalPostContext=[];

    /**
     * @param EventBase[] $getEvents
     */
    public function setGetEvents(array $getEvents)
    {
        $this->getEvents = $getEvents;
    }

    /**
     * @param EventBase[] $postEvents
     */
    public function setPostEvents(array $postEvents)
    {
        $this->postEvents = $postEvents;
    }

    protected function runGetController(){$this->_runController('get');}
    protected function runPostController(){$this->_runController('post');}

    protected function _runController($method){

        // load events into a distributor
        $methodEvents = $method.'Events';
        $d = new Distributor($this->$methodEvents);

        //add context and find event
        $additionalMethodContext = 'additional'.ucfirst($method).'Context';
        $event = $d->findEvent(array_merge($this->request->$method(), $this->$additionalMethodContext));

        //trigger event
        $event->run($this->response);
    }


    function __invoke()
    {
        try{
            $method = 'run'.ucfirst(strtolower($this->request->method())).'Controller';
            
            if( ! is_callable([$this, $method])){throw new \Exception('Method not implemented',404);}
            
            call_user_func([$this, $method]);
            
        }catch (\Throwable $e){
            $this->response->addError($e);
        }
    }



}