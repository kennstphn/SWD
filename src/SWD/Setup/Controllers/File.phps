<?php
namespace App\Controllers;


use App\Factories\EntityManagerFactory;
use SWD\Request\UrlParserFactory;
use Doctrine\ORM\NoResultException;
use SWD\DataController\ControlledUrl_interface;
use SWD\DataController\DynamicActionArgument;
use SWD\DataController\EntityController;
use SWD\Request\Request_interface;
use SWD\Request\UrlParser_interface;
use SWD\Response\Response_interface;

class File implements ControlledUrl_interface
{
    static protected  $cache = array();
    protected $file;

    static function runController(Request_interface $request, Response_interface $response)
    {
        if ( ! $response->isOk()){return;}

        $entityController = new EntityController(\App\Entities\File::class, $request, $response);

        $entityController->setAction(
            function (Request_interface $request) {
                $parser = UrlParserFactory::create($request);
                return self::createFromParser($parser) !== false;
            },
            function(DynamicActionArgument $a){
                $parser = UrlParserFactory::create($a->getRequest());
                $app = self::createFromParser($parser);
                $app->updateResponse($a->getRequest(), $a->getResponse());
            }
        );

        $entityController();
    }

    static function createFromParser(UrlParser_interface $p){
        $cacheLookup = implode('/',$p->getControllerArguments());

        if (array_key_exists($cacheLookup,self::$cache )){return new self(self::$cache[$cacheLookup]);}

        $args = $p->getControllerArguments();
        $folder = '/'.implode('/',array_slice($args, 0,-1));
        $fileName = end($args);
        try{
            $file = EntityManagerFactory::create()->createQueryBuilder()
                ->select('f')->from(\App\Entities\File::class,'f')
                ->where('f.folder = :folder')->setParameter('folder', $folder)
                ->andWhere('f.name = :fileName')->setParameter('fileName', $fileName)
                ->getQuery()->getSingleResult();
        }catch (NoResultException $e){
            return false;
        }
        self::$cache[$cacheLookup]  = $file;

        return new self($file);
    }

    function __construct(\App\Entities\File $file)
    {
        $this->file = $file;
    }

    function updateResponse(Request_interface$request, Response_interface$response){


        if ($request->get()->getBool('json')){
            $response->setData($this->file);
            $response->setResponseCode(200);
            return;
        }


        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        $b64 = $this->file->getData();
        $mimetype = $finfo->buffer($b64);

        $response->setResponseCode(200);
        $response->setData($b64);
        $response->headers()->set('Content-Type',$mimetype);
        $response->headers()->set('Cache-Control','max-age="1296000"');
        return;
    }


}