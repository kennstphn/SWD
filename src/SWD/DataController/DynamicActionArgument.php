<?php
namespace SWD\DataController;


use Doctrine\ORM\EntityManager;
use SWD\Request\Request_interface;
use SWD\Response\Response_interface;

class DynamicActionArgument
{
    protected $actionName, $request, $response, $em, $id;

    function __construct($actionName,Request_interface $request, Response_interface $response, EntityManager $em, $id = null)
    {
        $this->actionName = $actionName;
        $this->request = $request;
        $this->response = $response;
        $this->em = $em;
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getActionName():string
    {
        return $this->actionName;
    }

    /**
     * @return Request_interface
     */
    public function getRequest():Request_interface
    {
        return $this->request;
    }

    /**
     * @return Response_interface
     */
    public function getResponse():Response_interface
    {
        return $this->response;
    }

    /**
     * @return EntityManager
     */
    public function getEm():EntityManager
    {
        return $this->em;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }


}