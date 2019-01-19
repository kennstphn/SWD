<?php
namespace SWD\Modules\Bootstrap4;
use SWD\Modules\TwigRenderer\Twig;
use SWD\Modules\TwigRenderer\TwigRenderer;
use SWD\Website\Module;
use SWD\Website\Website;

class Bootstrap4 extends Module
{
    const TEMPLATE_DIR = __DIR__.'/templates';
    const BASE_TPL = '_bs4.twig';
    
    function __invoke(string $hookName, Website $website)
    {
        $renderers = $website->response()->getRenderCallbackArray()->getByName(TwigRenderer::RENDERER_CALLBACK_ARRAY_NAME);
        
        /**
         * @var Twig $renderer
         */
        foreach($renderers as $renderer){
            if($renderer instanceof Twig){
                $renderer->setLoader(new \Twig_Loader_Chain([
                    $renderer->getLoader(),
                    $this->getLoader()
                ]));
            }
        }
    }
    
    function getLoader(){
        return new \Twig_Loader_Filesystem(self::TEMPLATE_DIR);
    }

}