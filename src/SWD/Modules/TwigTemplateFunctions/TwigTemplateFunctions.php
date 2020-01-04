<?php
namespace SWD\Modules\TwigTemplateFunctions;


use App\Entities\Item;
use SWD\Factories\EntityManagerFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use SWD\DataController\EntityController;
use SWD\DataController\FormParser;
use SWD\Helper\TwigFilterCollection;
use SWD\Modules\AccessControl\AccessControl;
use SWD\Modules\TwigRenderer\Twig;
use SWD\Request\RegexUrlParser;
use SWD\Request\Request;
use SWD\Request\Request_interface;
use SWD\Response\Response;
use SWD\Website\Module;
use SWD\Website\Website;

class TwigTemplateFunctions extends Module
{
    protected $server;

    function __invoke(string $hookName, Website $website)
    {
        $this->server = $website->request()->server()->toArray();//init server


        if($website->response()->getRenderCallbackArray()->containsName(Twig::class)){
            /** @var Twig $callback */
            foreach(
                $website->response()->getRenderCallbackArray()->getByName(Twig::class)
                as $callback
            ) {
                $this->addFunctionsToTwig($callback, $website);
            }
        }
    }
    
    protected function addFunctionsToTwig(\Twig_Environment $twig, Website $website){
        $entityManager = EntityManagerFactory::create();

        $twig->addFunction(new \Twig_SimpleFunction('css', function ($link) {
            $fileLink = str_replace('//','/',getcwd().$link);
            if (file_exists($fileLink)) {
                return str_replace(
                    array('/css','//'),
                    array('/css/' . filemtime($fileLink),'/'),
                    $link
                );
            }
            return $link;
        }));

        $twig->addFunction(new \Twig_SimpleFunction('metadata',function($entity){
            $metadata = EntityManagerFactory::create()->getClassMetadata(is_string($entity) ? $entity : get_class($entity));
            return $metadata;
        }));

        $twig->addFunction(new \Twig_SimpleFunction('js', function ($link) {
            $fileLink = str_replace('//','/',getcwd().$link);
            if (file_exists($fileLink)) {
                return str_replace(
                    array('/js','//'),
                    array('/js/' . filemtime($fileLink),'/'),
                    $link
                );
            }
            return $link;
        }));

        $twig->addFunction(new \Twig_SimpleFunction('json', 'json_encode'));
        $twig->addFilter(TwigFilterCollection::base64());

        $twig->addFunction(new \Twig_SimpleFunction('entityMetadataList',function(){
            $classes=EntityManagerFactory::listEntityClasses();
            $em = EntityManagerFactory::create();
            $mList = [];
            foreach($classes as $class){
                array_push($mList,$em->getClassMetadata($class));
            }
            return $mList;
        }));

        $twig->addFunction(self::api($website->request()));


        $twig->addFunction(new \Twig_SimpleFunction('formFields',function ($entity,$includeAssociations = false) use ($entityManager){
            $parser = new FormParser($entity, $entityManager);

            return $parser->getFormFields();
        }));

        $twig->addFunction(new \Twig_SimpleFunction('cacheBust',function($relativeToHome){
            $file = getcwd().$relativeToHome;
            $mtime = file_exists($file) ? '/'.filemtime($file) : '/000000000';
            $pos = strpos($relativeToHome, '/', 1);
            return substr($relativeToHome,0,$pos).$mtime.substr($relativeToHome,$pos);
        }));

        $twig->addGlobal('domain', $website->request()->server()->get('HTTP_HOST'));
        $userFactory = AccessControl::getUserFactory();
        if ($userFactory){
            $twig->addGlobal('currentUser', $userFactory::getCurrentUser($website->request()));
        }

        $twig->addFunction(new \Twig_SimpleFunction('groupBy', function ($entityClass, $att){
            $entityClass = is_string($entityClass) ? str_replace(['..','.'], ['.','\\'], $entityClass) : $entityClass;
            $attributeList = array_slice(func_get_args(), 1);
            $entityClass = is_object($entityClass) ? get_class($entityClass) : $entityClass;
            if ( ! class_exists($entityClass)){throw new \Exception('Entity class not found for '.$entityClass);}

            $em = EntityManagerFactory::create();
            $m = $em->getClassMetadata($entityClass);
            $query = $em->createQueryBuilder();
            $first = true;
            foreach($attributeList as $att){
                if ( ! in_array($att, $m->getFieldNames())){throw new \Exception('Field '.$att.' not found in entity '.$entityClass);}

                switch ($first){
                    case true:
                        $query->select('e.'.$att)
                            ->groupBy('e.'.$att)
                            ->from($entityClass,'e')
                            ->orderBy('e.' . $att)
                        ;
                        break;
                    case false:
                        $query->addSelect('e.'.$att)->addGroupBy('e.'.$att)->addOrderBy('e.'.$att);
                        break;
                }

                $first = false;
            }
            $result = $query->getQuery()->getResult();
            return $result;
        }));
        $twig->addFunction(new \Twig_Function('header',function($string,$replace = true,$http_response_code = null){
            header($string,$replace,$http_response_code);
        }));

        $twig->addFilter(TwigFilterCollection::dotClass());
        $twig->addFilter(TwigFilterCollection::markdown());
        $twig->addFilter(TwigFilterCollection::purify());
        $twig->addGlobal('safeTags','<p><br><hr><li><ul><ol><i><b><u><strong><h2><h3><h4><h5><h6><h7><h8><span>');
        $twig->addGlobal('token', $website->response()->getMeta()->get('antiCsrfToken'));
        $twig->addFilter(TwigFilterCollection::entityUrl());

    }

    static function api(Request_interface $request){
        return new \Twig_SimpleFunction('api',function($url, $get = array())use ($request){
            $request = Request::create(array(
                'server'=>array_merge($request->server()->toArray(), array('REQUEST_METHOD'=>'GET','REQUEST_URI'=>$url)),
                'get'=>$get
            ));
            $parser = new RegexUrlParser($request->url());
            $response = new Response();
            $controller = new EntityController($parser->getEntityClass(),$request,$response);
            $controller();
            return $response;
        });
        
    }

}
