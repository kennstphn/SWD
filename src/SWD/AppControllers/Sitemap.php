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

    static function html(Response_interface $response){
        $response->setData(self::getEntries());
        $response->setTemplate('sitemap.twig');
    }
    
    static function xml(Response_interface $response){
        $response->setData(self::getEntries());
        $response->addRenderCallback(new ResponseRenderer(
            function(Response_interface $response){
                try{
                    header('Content-type: application/xml');
                    echo TwigRenderer::sandbox()
                        ->createTemplate(
                            
    '<?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
        {% for item in response.data %}
        <url>
            <loc>{{ item.loc }}</loc>
            <lastmod>{{ item.lastModified.format(\'Y-m-d\') }}</lastmod>
            <changefreq>{{ item.changeFrequency ?? \'weekly\' }}</changefreq>
            <priority>{{ item.priority ?? \'0.8\' }}</priority>
        </url>
        {% endfor %}
    </urlset>'
                        )->render(['response'=>$response]);
                }catch (\Throwable $e){
                    echo $e->getMessage();
                }
            },
            1,//priority
            'xml-closure' //name
            // check for ability to render
        ));
    }
    
    protected static function getEntries(){
        return EntityManagerFactory::create()->createQueryBuilder()
            ->select('s')->from(\App\Entities\Sitemap::class,'s')->where('s.includeInSitemap = 1')
            ->getQuery()->getResult();
    }
    
}