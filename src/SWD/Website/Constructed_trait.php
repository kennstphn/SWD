<?php
namespace SWD\Website;


use SWD\Request\Request_interface;
use SWD\Response\Response_interface;

trait Constructed_trait
{
    protected $request, $response;
    function __construct(Request_interface $request, Response_interface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}