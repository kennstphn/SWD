<?php
namespace SWD\Response;


use Doctrine\Common\Util\Debug;
use SWD\Request\Request_interface;
use SWD\Website\Constructed_trait;
use SWD\Website\Controller_interface;
use SWD\Website\ControllerFactory_interface;

class RenderControllerFactory implements ControllerFactory_interface, Controller_interface
{

    use Constructed_trait;
    
    /**
     * @param Request_interface $request
     * @param Response_interface $response
     * @return Controller_interface | null
     */
    static function find(Request_interface $request, Response_interface $response):Controller_interface{
        $class  = get_called_class();
        return new $class($request,$response);
    }

    function __invoke()
    {
        if($this->isJsonRequest()){
            $this->response->renderJson();
            return;
        }

        $templateString = $this->response->hasTemplate()
            ? $this->response->getTemplate()
            : $this->getTemplateFromUrl($this->request->url())
        ;

        $twig = $this->getTwigApp();
        $template = $twig->load($templateString);


        $block = $this->onlyBlock();
        $renderContext = array(
            'request'=>$this->request,
            'data'=>$this->response->getData(),
            'meta'=>$this->response->getMeta()->toArray(),
            'errors'=>$this->response->getErrors()
        );

        $twig->addFunction(new \Twig_SimpleFunction('dumpVars', function($depth = 2)use($renderContext){
            Debug::dump($renderContext,$depth+1,true,false);
        }));

        $twig->addFunction(new \Twig_SimpleFunction('cacheBust',function($relativeToHome){
            $file = getcwd().$relativeToHome;
            $mtime = file_exists($file) ? '/'.filemtime($file) : '/000000000';
            $pos = strpos($relativeToHome, '/', 1);
            return substr($relativeToHome,0,$pos).$mtime.substr($relativeToHome,$pos);
        }));
        

        if ( ! $block){
            $template->render($renderContext);
            return;
        }
        $template->renderBlock($block,$renderContext);

    }

    /**
     * @return bool
     */
    protected function isJsonRequest(){
        return $this->request->get()->getBool('json');
    }

    protected function onlyBlock(){
        return $this->request->get()->get('renderBlock');
    }
    
    function getTemplateFromUrl(string $url){
        $template =  str_replace('/', '.', substr($url,1)).'.twig';
        if ($template == '.twig'){$template = 'index.twig';}
        return $template;
    }

    function getTwigApp(){
        $path = $this->getTwigTemplateDirectory();
        $loader = new \Twig_Loader_Filesystem($path.'/default');
        foreach(scandir($path) as $subfolder){
            if(in_array($subfolder, array('.','..','default'))){continue;}
            $loader->addPath($path.'/'.$subfolder, $subfolder);
        }
        $twig = new \Twig_Environment($loader,array('debug'=>true));
        return $twig;
    }
    
    protected function getTwigTemplateDirectory(){
        $lastSlash = strrpos(getcwd(),'/');
        return substr(getcwd(),0,$lastSlash).'/templates';
    } 
    


}