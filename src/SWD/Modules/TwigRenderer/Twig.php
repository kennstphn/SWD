<?php
namespace SWD\Modules\TwigRenderer;


use App\Factories\TwigFunctionFactory;
use SWD\Request\Request_interface;
use SWD\Request\UrlParserFactory;
use SWD\Response\Response_interface;
use SWD\Response\ResponseRenderer_interface;
use SWD\Website\Website;

class Twig extends \Twig_Environment implements ResponseRenderer_interface
{
    const TWIG_FUNCTION_FACTORY = '\App\Factories\TwigFunctionFactory';

    /**
     * @var Website $website
     */
    protected $website;

    /**
     * @param Website $website
     * @throws \Exception
     */
    public function init(Website $website)
    {
        $this->website = $website;
        $this->addExtension(new \Twig_Extension_Debug());
        $this->addFunction(new \Twig_SimpleFunction('dateTime', function ($date,$format = null) {
            if (is_string($date)){$date = new \DateTime($date);}
            if(is_null($format)){return $date;}
            if(is_string($format)){return $date->format($format);}
            throw new \Exception('failed to identify function call usecase for dateTime');
        }));
        $this->addFunction(new \Twig_SimpleFunction('class', function ($object){
            if (! is_object($object)){return gettype($object);}
            return str_replace('DoctrineProxies\\__CG__\\', '', get_class($object));
        }));
        
        if ( class_exists($this::TWIG_FUNCTION_FACTORY)){
            $factory = $this::TWIG_FUNCTION_FACTORY;
            /** @var TwigFunctionFactory $factory */
            $factory = new $factory;
            foreach($factory->getFunctions() as $function){
                if ( ! get_class($function) == \Twig_Function::class){throw new \Exception('class '.get_class($function).' is not '.\Twig_Function::class);}
                $this->addFunction($function);
            }

            foreach($factory->getFilters() as $filter){
                $this->addFilter($filter);
            }
        }
    }


    function render(Response_interface $response)
    {
        $templateString = $this->getTemplateString($response);

        $templateStringArray = is_array($templateString) ? $templateString : array($templateString);
        foreach($templateStringArray as $templateString){
            try{
                $template = $this->load($templateString);
            }catch (\Twig_Error_Loader $e){
                //not found! try next
            }
        }
        if ( ! isset($template)){throw new \Exception('No template loaded. ');}

        $renderContext = array(
            'request'=>$this->website->request(),
            'response'=>$this->website->response(),
            'data'=>$response->getData(),
            'meta'=>$response->getMeta()->toArray(),
            'errors'=>$response->getErrors(),
        );

        foreach($response->topLevelItems() as $key => $item){
            if ( ! in_array($key,['request','data','meta','errors'])){
                $renderContext[$key] = $item;
            }
        }

        http_response_code($response->getResponseCode());
        foreach($response->headers() as $key => $header){
            if(is_string($key)){
                header($key.':'.$header);
            }else{
                header($header);
            }
        }
        
        if ( $this->isBlockCall()){
            echo $template->renderBlock($this->getRequestedBlock(),$renderContext);
            return;
        }
        echo $template->render($renderContext);
        return;
    }

    function getPriority():int
    {
        return 50;
    }

    /**
     * @param Response_interface $response
     * @return bool
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    function canRender(Response_interface $response):bool
    {

        $templateString = $this->getTemplateString($response);

        $templateStringArray = is_array($templateString) ? $templateString : array($templateString);


        foreach($templateStringArray as $templateString){
            try{
                $response->setTemplate($templateString);
                $this->load($templateString);
                return true;
            }catch (\Twig_Error_Loader $e){
                // template can't be found!
                $response->addMeta($templateString, 'not found');
            }catch (\Twig_Error_Runtime $e){
                // corrupted Cache
                if (is_callable(array($this->website,'log'))){
                    $this->website->log($e);
                }
                throw $e;
            }catch (\Twig_Error_Syntax $e){
                //compilation error
                if (is_callable(array($this->website, 'log'))){
                    $this->website->log($e);
                }
                throw $e;
            }
        }

        return false;
    }

    function getName():string
    {
        return get_called_class();
    }

    
    protected function classNameToTwigSlug($str) {
        $str = str_replace('App\\Controllers\\', '', $str);
        return trim(strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $str)),'-').'.twig';

    }

    protected function parseTemplateString(Request_interface $request){


        //home page is always index
        if ($request->url() == '' || $request->url() == '/'){
            return 'index.twig';
        }
        
        $parser = UrlParserFactory::create($request);

        if ($parser->getEntityClass() && in_array($parser->getAction(),['listAll','render','edit','delete','create','search'] )){
            return 'entity.'.$parser->getAction().'.twig';
        }

        /*
         * App\Controllers\{CLASS_NAME} options
         */
        $controller = $parser->getControllerClass();
        if ($controller){
            if(in_array(TemplateDefining_interface::class, class_implements($controller))){
                /** @var TemplateDefining_interface $controller */
                return $controller::getTemplate($request);
            }
            return $this->classNameToTwigSlug($controller);
        }
        
        /*
         * App\Entities\{ENTITY_NAME} options
         */
        $controller = $parser->getEntityClass();

        if ($controller){
            if(in_array(TemplateDefining_interface::class, class_implements($controller))){
                /** @var TemplateDefining_interface $controller */
                return $controller::getTemplate($request);
            }
            return 'entity.'.$parser->getAction().'.twig';
        }
     
        
        /*
         * One-offs use case
         */
        return trim(str_replace('/','.',$request->url()),'.').'.twig';
    }
    /**
     * @param Response_interface $response
     * @return string
     */
    protected function getTemplateString(Response_interface $response){
        $namespaceController = TwigNamespaceControllerFactory::create($this->website->request(), $response);

        if ( ! $response->isOk() ){return array(
            $namespaceController->getNamespacedString($response->getResponseCode().'.twig')
            ,$namespaceController->getNamespacedString(($response->getResponseCode() - ($response->getResponseCode() % 100) ) . '.twig')
        );}


        if($response->hasTemplate()){
            return $namespaceController->getNamespacedString($response->getTemplate());
        }
        $templateString = $this->parseTemplateString($this->website->request());
        return $namespaceController->getNamespacedString($templateString);
    }


    protected function isBlockCall():bool{
        return $this->website->request()->get()->containsKey('renderBlock');
    }

    protected function getRequestedBlock():string{
        return $this->website->request()->get()->get('renderBlock');
    }
}