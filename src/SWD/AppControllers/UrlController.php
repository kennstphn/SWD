<?php
namespace SWD\AppControllers;


use SWD\DataController\ControlledUrl_interface;
use SWD\PatternMatch\ArgumentList;
use SWD\Request\Request_interface;
use SWD\Response\Response_interface;

abstract class UrlController implements ControlledUrl_interface
{
    /**
     * UrlController constructor.
     * @param Request_interface $request
     * @param Response_interface $response
     */
    public function __construct(Request_interface $request, Response_interface $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->parser = \SWD\Request\UrlParserFactory::create($request);
        $this->pattern = new ArgumentList($this->parser->getControllerArguments());
    }

    static function runController(Request_interface $request, Response_interface $response)
    {
        $selfClass = get_called_class();
        $controller = new $selfClass($request, $response);
        $controller();
    }

    /**
     * @var Request_interface $request
     */
    protected $request;

    /**
     * @var Response_interface $response
     */
    protected $response;

    /**
     * @var \SWD\Request\UrlParser_interface
     */
    protected $parser;

    /**
     * @var ArgumentList
     */
    protected $pattern;
    
    abstract function __invoke();


}