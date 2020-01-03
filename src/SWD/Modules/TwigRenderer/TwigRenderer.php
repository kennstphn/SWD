<?php
namespace SWD\Modules\TwigRenderer;


use SWD\Factories\EntityManagerFactory;
use SWD\Factories\UserFactory;
use SWD\Helper\TwigFilterCollection;
use SWD\Modules\TwigTemplateFunctions\TwigTemplateFunctions;
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
    const RENDERER_CALLBACK_ARRAY_NAME=Twig::class;


    function __invoke(string $hookName, Website $website)
    {
        $this->website = $website;

        $path = $this->getTwigTemplateDirectory();
        $loader =  new \Twig_Loader_Filesystem($path.'/default');

        foreach(scandir($path) as $subfolder){
            if(in_array($subfolder, array('.','..'))){continue;}
            if( ! is_dir($path.'/'.$subfolder)){continue;}
            $loader->addPath($path.'/'.$subfolder, $subfolder);
        }
        
        $options = array('debug'=>true,'cache'=>false);

        $loaderChain = new \Twig_Loader_Chain([$loader,new RegistrationLoader()]);
        
        $twig = new Twig($loaderChain,$options);

        $twig->init($website);
        
        $sandbox = self::sandbox();
        $sandbox->addFunction(TwigTemplateFunctions::api($website->request()));
        if( class_exists('App\\Entities\\User') ){
            $sandbox->addGlobal('currentUser', UserFactory::getCurrentUser($website->request()));
        }
        
        $twig->addFilter(new \Twig_SimpleFilter('sandbox', function ($string)use ($sandbox,$website) {
            try{
                return $sandbox->createTemplate($string)->render(['request'=>$website, 'response'=>$website]);
            }catch (\Throwable $e){
                return $e->getMessage();
            }
        }));


        $website->response()->addRenderCallback($twig);
    }

    protected function getTwigTemplateDirectory(){
        if(defined('TEMPLATE_DIR')){
            return TEMPLATE_DIR;
        }
        if($tpl = \SWD\Factories\EnvironmentFactory::get('template_dir')){
            return $tpl;
        }
        $lastSlash = strrpos(getcwd(),'/');
        return substr(getcwd(),0,$lastSlash).'/templates';
    }

    protected static function getDbLoader(){
        return new DatabaseLoader();
    }
    static function sandbox(){
        $twig = new \Twig_Environment(self::getDbLoader());

        $policy = new \Twig_Sandbox_SecurityPolicy(
            self::getSandboxTags(),
            self::getSandboxFilters(),
            self::getSandboxMethods(),
            self::getSandboxProperties(),
            self::getSandboxFunctions()
        );
        $sandbox = new \Twig_Extension_Sandbox($policy,true);
        $twig->addExtension($sandbox);
        $twig->addFilter(TwigFilterCollection::markdown());
        return $twig;
    }
    
    static protected function getSandboxTags(){
        return [
            'autoescape',
            'block',
            'do',
            'embed',
            'extends',
            'filter',
            'flush',
            'for',
            'from',
            'if',
            'import',
            'include',
            'macro',
            'sandbox',
            'set',
            'spaceless',
            'use',
            'verbatim',
            'with'
        ];
    }
    
    static protected function getSandboxFilters(){
        return [
            'abs',
            'batch',
            'capitalize',
            //'convert_encoding',
            'date',
            //'date_modify',
            'default',
            'escape',
            'first',
            'format',
            'join',
            'json_encode',
            'keys',
            'last',
            'length',
            'lower',
            'merge',
            'nl2br',
            'number_format',
            'raw',
            'replace',
            'reverse',
            'round',
            'slice',
            'sort',
            'split',
            'striptags',
            'title',
            'trim',
            'upper',
            'url_encode',
            'markdown'
        ];
    }

    static protected function getSandboxFunctions(){
        return [
            'attribute',
            //'block',
            //'constant',
            'cycle',
            'date',
            'dump',
            'include',
            'max',
            'min',
            //'parent',
            'random',
            'range',
            //'source',
            //'template_from_string'
            'api'
        ];
    }

    static protected function getSandboxMethods(){
        $ie = array(
            //'Article' => array('getTitle', 'getBody'),
        );
        $mlist = EntityManagerFactory::create()->getMetadataFactory()->getAllMetadata();
        foreach($mlist as $m){
            $ie[$m->getName()] = array_map(function($a){return 'get'.ucfirst($a);},
                array_merge($m->getFieldNames(),$m->getAssociationNames()) 
            );
            if(in_array($m->getName(),['App\Entities\File'])){
                array_push($ie[$m->getName()],'url');
            }
        }
        foreach($ie as $class => $array){
            $ie['DoctrineProxies__CG__\\'.$class] = $array;
        }
        $ie['SWD\Response\Response'] = ['getData'];
        $ie['DateTime'] = ['format'];
        return $ie;
    }
    
    static protected function getSandboxProperties(){
        return [];
    } 
    
    static function registerTemplate($name, $string, $overwritePreviouslyRegistered = false){
        RegistrationLoader::registerTemplate($name, $string,$overwritePreviouslyRegistered);
    }
}
