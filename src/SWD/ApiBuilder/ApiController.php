<?php
namespace SWD\ApiBuilder;

use SWD\AppControllers\UrlController;

class ApiController extends UrlController
{
    protected $getEvents = [];
    protected $postEvents = [];

    function getGetData(){
        return $this->request->get()->toArray();
    }

    function getPostData(){
        return $this->request->post()->toArray();
    }

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

        $getData = 'get'.ucfirst($method).'Data';
        $event = $d->findEvent(is_callable([$this,$getData]) ? call_user_func([$this,$getData]) : $this->request->$method()->toArray() );

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