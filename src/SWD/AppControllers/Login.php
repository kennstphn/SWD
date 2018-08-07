<?php
namespace SWD\AppControllers;


use SWD\DataController\ControlledUrl_interface;
use SWD\Modules\AccessControl\AccessControl;
use SWD\Request\Request_interface;
use SWD\Request\UrlParserFactory;
use SWD\Response\Response_interface;
use SWD\Website\Constructed_trait;
use SWD\Website\Controller_interface;

class Login implements ControlledUrl_interface, Controller_interface
{
    use Constructed_trait;

    static function runController(Request_interface $request, Response_interface $response)
    {
        if ( ! $response->isOk()){return; }
        $thisClass = get_called_class();
        $c = new $thisClass($request,$response);
        $c();
    }

    function __invoke()
    {
        $parser = UrlParserFactory::create($this->request);
        $arguments = $parser->getControllerArguments();

        switch (strtolower($this->request->server()->get('REQUEST_METHOD'))){
            case 'post':
                $this->loginSubmissionHandler($arguments);
                break;

            case 'get':
                $this->getLoginPage($arguments);
                break;
            default:
                throw new \Exception('request method not implemented');
                break;
        }
    }


    protected function getLoginPage(array $arguments){
        $argCount = count($arguments);
        if ( ! $argCount){
            $this->response->setTemplate('login.twig');
            return;
        }
        
        if (count($arguments) > 1){
            $this->response->setResponseCode(404);
        }
        
        $user = AccessControl::findUser($arguments[0]);
        if ( ! $user){$this->response->setResponseCode(404);return;}
        
        $this->response->setData($user);
        $this->response->setTemplate('login.twig');
        
        
    }
    
    protected function loginSubmissionHandler(array $arguments){
        $username = $this->request->post()->get('username');
        $pass = $this->request->post()->get('password');
        
        //unspecified username / password failure
        if ( ! $username || ! $pass){
            $this->response->setResponseCode(403);
            return;
        }
        
        //no such user failure
        $user = AccessControl::findUser($username);
        if ( ! $user){
            $this->response->setResponseCode(403);
            return;
        }
        
        //invalid password failure
        if ( ! $user->verifyPassword($pass)){
            $this->response->setResponseCode(403);
            return;
        }
        
        //success!
        $factory = AccessControl::getUserFactory();
        $factory::setPersistantUser($user);
    }

}