<?php
namespace SWD\Website;


use Doctrine\Common\Util\Debug;
use SWD\DataController\DataControllerFactory;
use SWD\Request\Request_interface;
use SWD\Response\Response_interface;
use SWD\Response\RenderControllerFactory;

class Website
{
    const INIT = 'init';
    const INIT_DONE = 'init.done';
    const DONE = 'done';
    const RENDER_BEFORE = 'render.before';
    const RENDER_CONTROLLER_FOUND = 'render.init';
    const REDIRECT_BEFORE = 'redirect.before';
    const REDIRECT_AFTER = 'redirect.after';
    const RENDER_AFTER = 'render.after';
    const DATA_LOADED = 'data.loaded';
    const CONTROLLER_SECTION_DONE = 'controller.done';
    const NON_CONTROLLER_FACTORY_STRING = ' does not implement ControllerFactory_interface';

    const DEBUG_TRUE = 1;
    const DEBUG_FALSE = 0;
    protected $debugMode = 0;
    protected $request;
    protected $response;
    public $output;
    public $errorWarnings;
    
    /** @var ModularArray */
    protected $modules;
    
    protected $dataControllerFactory = DataControllerFactory::class;
    protected $renderControllerFactory = RenderControllerFactory::class;
    

    function __construct(Request_interface $request, Response_interface $response)
    {
        
        ob_start();
        $this->request = $request;
        $this->response = $response;
        $this->blockCsrf();
        $this->modules = new ModularArray($this->request, $this->response);
    }
    
    function blockCsrf(){
        $csrfProtector = new CsrfProtection($this->request, $this->response);
        $csrfProtector->blockInvalidRequests();
        $csrfProtector->persistToken();
    }
    
    function run(){
        
        $this->invokeModulesByHook(self::INIT);

        $this->invokeModulesByHook(self::INIT_DONE);

        if ($this->response()->isOk()){

            $c = $this->maybeFindController($this->request,$this->response, $this->dataControllerFactory);
            if ($c){
                $this->invokeController($c);
                $this->invokeModulesByHook(self::DATA_LOADED);
            }

        }

        $this->invokeModulesByHook(self::CONTROLLER_SECTION_DONE);

        $this->storeSession();
        $this->response()->sendCookies();
        switch ($this->response()->getResponseCode() >= 300 && $this->response()->getResponseCode() <= 399){
            case true:
                $this->invokeModulesByHook(self::REDIRECT_BEFORE);

                $this->response()->redirect();

                $this->invokeModulesByHook(self::REDIRECT_AFTER);
                break;
            default:
                $this->invokeModulesByHook(self::RENDER_BEFORE);

                ob_start();

                $this->response()->render();

                $this->output = ob_get_clean();

                $this->invokeModulesByHook(self::RENDER_AFTER);
                break;
        }
        $this->errorWarnings = ob_get_clean();

        echo $this->output;

        switch ($this->debugMode){
            case self::DEBUG_FALSE:
                break;
            case self::DEBUG_TRUE:
                echo $this->errorWarnings;
                break;
            default:throw new \Exception('Unrecognized debug mode');
        }

        $this->invokeModulesByHook(self::DONE);

    }

    function disableDebugMode(){
        $this->debugMode = 0;
    }

    function setDebugMode(int $mode){
        $this->debugMode = $mode;
    }
    
    /**
     * @param string $hookName
     */
    function invokeModulesByHook(string $hookName){
        $this->modules->invokeModulesByHook($hookName,$this);
    }

    function request(){return $this->request;}
    function response(){return $this->response;}
    function modules(){return $this->modules;}

    /**
     * @param Request_interface $request
     * @param Response_interface $response
     * @param string $cFactory
     * @return mixed
     * @throws \Exception
     */
    protected function maybeFindController(Request_interface $request, Response_interface $response, string $cFactory){
        if ( ! class_exists($cFactory)){throw new \Exception('factory '.$cFactory.' does not exist');}
        if ( ! in_array(ControllerFactory_interface::class,class_implements($cFactory) )){
            throw new \Exception('Factory '.$cFactory.' does not implement '.ControllerFactory_interface::class);
        }
        /** @var ControllerFactory_interface $cFactory */
        return $cFactory::find($request, $response);
    }

    /**
     * @param callable $tc
     */
    protected function invokeController(callable $c){
        call_user_func($c);
    }

    /**
     * @param ModularArray $modules
     */
    public function setModules($modules)
    {
        $this->modules = $modules;
    }

    function addModule(string $hookname, $module){
        if ( ! is_callable($module) && (is_string($module) && ! class_exists($module))){
            throw new \Exception('Failed to add module ');
        }
        $this->modules()->addModule($hookname,$module);
    }
    
    /**
     * @return ModularArray
     */
    public function getModules()
    {
        return $this->modules;
    }
    

    /**
     * @param $dataControllerFactory
     * @throws \Exception
     */
    public function setDataControllerFactory($dataControllerFactory)
    {
        if (! $this->stringIsControllerFactory($dataControllerFactory)){
            throw new \Exception($dataControllerFactory.self::NON_CONTROLLER_FACTORY_STRING);
        }
        $this->dataControllerFactory = $dataControllerFactory;
    }

    /**
     * @param $renderControllerFactory
     * @throws \Exception
     */
    public function setRenderControllerFactory($renderControllerFactory)
    {
        if (! $this->stringIsControllerFactory($renderControllerFactory)){
            throw new \Exception($renderControllerFactory.self::NON_CONTROLLER_FACTORY_STRING);
        }
        $this->renderControllerFactory = $renderControllerFactory;
    }
    
    protected function stringIsControllerFactory($string){
        return in_array(ControllerFactory_interface::class,class_implements($string));
    }

    public function isBefore(string $hookName){
        return in_array($hookName, $this->modules->getCalledHooks());
    }

    public function isAfter(string $hookName){
        return ! $this->isBefore($hookName);
    }

    public function storeSession(){
        $session = $this->response()->getSession();
        session_start();
        foreach($session as $key => $value){
            $_SESSION[$key] = $value;
        }
        session_write_close();
    }
    
}