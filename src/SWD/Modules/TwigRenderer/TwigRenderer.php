<?php
namespace SWD\Modules\TwigRenderer;


use SWD\Website\Module;
use SWD\Website\Website;

/**
 * Class TwigRenderer
 * @package SWD\Modules
 *
 * This class depends on .twig files living inside a templates/default (or templates/{namespace}) directory, where
 * "templates" is a sibling of the current_working_directory ( getcwd() )
 */
class TwigRenderer extends Module 
{
    /**
     * @var Website | null
     */
    protected $website;

    const TEMPLATE_NS_KEY='templateNS';


    function __invoke(string $hookName, Website $website)
    {
        $this->website = $website;

        $path = $this->getTwigTemplateDirectory();
        $loader =  new \Twig_Loader_Filesystem($path.'/default');

        foreach(scandir($path) as $subfolder){
            if(in_array($subfolder, array('.','..'))){continue;}
            $loader->addPath($path.'/'.$subfolder, $subfolder);
        }

        $options = array('debug'=>true,'cache'=>false);

        $loader2 = new DatabaseLoader();
        $loaderChain = new \Twig_Loader_Chain([$loader, $loader2]);
        
        $twig = new Twig($loaderChain,$options);

        $twig->init($website);
        
        $website->response()->addRenderCallback($twig);
    }

    protected function getTwigTemplateDirectory(){
        $lastSlash = strrpos(getcwd(),'/');
        return substr(getcwd(),0,$lastSlash).'/templates';
    }

}