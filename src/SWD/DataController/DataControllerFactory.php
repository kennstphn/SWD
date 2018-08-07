<?php
namespace SWD\DataController;


use SWD\Request\Request_interface;
use SWD\Request\UrlParserFactory;
use SWD\Response\Response_interface;
use SWD\Website\Constructed_trait;
use SWD\Website\Controller_interface;
use SWD\Website\ControllerFactory_interface;

class DataControllerFactory implements ControllerFactory_interface, Controller_interface
{
    use Constructed_trait;
    protected $endpointNamespace = 'Urls';
    /**
     * @param Request_interface $request
     * @param Response_interface $response
     * @return Controller_interface|null
     */
    static function find(Request_interface $request, Response_interface $response):Controller_interface{
        return new self($request, $response);
    }

   

    function __invoke()
    {
        $parser = UrlParserFactory::create($this->request);

        //CONTROLLER Path --> App\Controllers\{Entity_Name}
        $class = $parser->getControllerClass();

        if ( $class && in_array(ControlledUrl_interface::class, class_implements($class) )){
            /** @var ControlledUrl_interface $class */
            $class::runController($this->request, $this->response);
            return;
        }

        if ($class){throw  new \Exception('Controller '.$class.' does not implement necessary interface ('.
            ControlledUrl_interface::class
            .')');}

        //CONTROLLER Path --> App\Entities\{Entity_Name}
        $class = $parser->getEntityClass();

        if ( ! $class){return ;}

        if ( in_array(ControlledUrl_interface::class, class_implements($class))){
            /** @var ControlledUrl_interface $class */
            $class::runController($this->request, $this->response);
            return;
        }

        $controller = new EntityController($class, $this->request, $this->response);
        $controller();

        return;
    }
    
}