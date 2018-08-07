<?php
namespace SWD\Request;


class Pagination implements \JsonSerializable
{
    protected $request;
    public $defaultPerPage = 10;
    public $defaultPage = 1;

    function __construct(Request_interface $request)
    {
        $this->request = $request;
    }

    /**
     * @return int
     * @throws TypeException
     */
    function perPage(){
        if ($this->request->get()->containsKey('perPage')){return $this->request->get()->getInteger('perPage');}
        if ($this->request->get()->containsKey('limit')){return $this->request->get()->getInteger('limit');}
        return $this->defaultPerPage;
    }

    /**
     * @return int
     */
    function limit(){return $this->perPage();}

    /**
     * @return int
     * @throws TypeException
     */
    function page(){
        if ($this->request->get()->containsKey('page')){return $this->request->get()->getInteger('page');}
        return $this->defaultPage;
    }

    /**
     * @return int
     * @throws TypeException
     */
    function offset(){
        if ($this->request->get()->containsKey('offset')){return $this->request->get()->getInteger('offset');}
        return $this->perPage() * ($this->page() - 1);
    }

    function toArray(){
        return [
            'perPage'=>$this->perPage()
            ,'page'=>$this->page()
        ];
    }

    function jsonSerialize()
    {
        return [
            'perPage'=>$this->perPage()
            ,'page'=>$this->page()
            ,'limit'=>$this->limit()
            ,'offset'=>$this->offset()
        ];
    }


}