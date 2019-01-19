<?php
namespace SWD\Modules\YoutubePlaylist;
use SWD\Factories\EntityManagerFactory;
use SWD\Modules\TwigRenderer\TwigRenderer;
use SWD\Website\Module;
use SWD\Website\Website;

class YoutubePlaylist extends Module
{
    const TEMPLATE_PLAYLIST_KEY = 'youtube-playlist.twig';
    function __invoke(string $hookName, Website $website)
    {
        /*
         * load overwrite-able class
         * If we have to load the class manually here -- it won't be picked up automatically by the EntityInstaller,
         * so we also trigger that registration. 
         */
        if ( ! class_exists('App\\Entities\\YoutubePlaylist')){
            include_once __DIR__.'/app.entities/YoutubePlaylist.php';
            EntityManagerFactory::registerEntityClass(\App\Entities\YoutubePlaylist::class);
        }
        
        // register template(s)
        TwigRenderer::registerTemplate(self::TEMPLATE_PLAYLIST_KEY, file_get_contents(__DIR__.'/tpl/youtube-playlist.twig'));
        
        
    }
    
    
}

