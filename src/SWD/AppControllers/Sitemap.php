<?php
namespace SWD\AppControllers;


use SWD\DataController\ControlledUrl_interface;
use SWD\DataController\EntityController;
use SWD\Factories\EntityManagerFactory;
use SWD\Modules\TwigRenderer\TwigRenderer;
use SWD\Request\Request_interface;
use SWD\Response\Response_interface;
use SWD\Response\ResponseRenderer;

abstract class Sitemap implements ControlledUrl_interface
{
    static function runController(Request_interface $request, Response_interface $response)
    {
        
        if($request->url()->__toString() == '/sitemap.html'){
            
            self::html($response);
            return;
        }
        if($request->url()->__toString() == '/sitemap.xml'){
            self::xml($response);
            return;
        }

        $entityController = new EntityController('App\\Entities\\Sitemap', $request, $response);
        $entityController();

    }

    protected static function html(Response_interface $response){
        $response->setData(self::getEntries());
        $response->setTemplate('sitemap.twig');
    }
    
    protected static function xml(Response_interface $response)
    {
        $response->setData(self::getEntries());
        $response->setTemplate('sitemap.xml.twig');

    }    
    protected static function getEntries(){
        return EntityManagerFactory::create()->createQueryBuilder()
            ->select('s')->from(\App\Entities\Sitemap::class,'s')->where('s.includeInSitemap = 1')
            ->getQuery()->getResult();
    }
    
}