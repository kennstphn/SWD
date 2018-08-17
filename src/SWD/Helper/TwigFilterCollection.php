<?php
namespace SWD\Helper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use SWD\Factories\HtmlPurifier;

class TwigFilterCollection
{
    static function dotClass():\Twig_SimpleFilter{
        return new \Twig_SimpleFilter('dotClass', function ($ob) {
            if( ! is_object($ob)){return gettype($ob);}
            return str_replace(['\\', '..'], '.', get_class($ob));
        });
    }

    static function base64():\Twig_SimpleFilter{
        return new \Twig_SimpleFilter('base64', function ($string) {
            return base64_encode($string);
        });
    }

    static function markdown():\Twig_SimpleFilter{
        return new \Twig_SimpleFilter('markdown',function($text){return \Parsedown::instance()->text($text);}); 
    }

    static function purify():\Twig_SimpleFilter{
        return new \Twig_SimpleFilter('purify', function($text){
            return HtmlPurifier::create()->purify($text,HtmlPurifier::safeConfigForUserInput());
        });
    }

    static function entityUrl():\Twig_SimpleFilter{
        return new \Twig_SimpleFilter('entityUrl', function ($entityOrCollection) {
            $classUrl = function($e){
                return str_replace(['\\','App/Entities','DoctrineProxies/__CG__','//'],['/','','','/'] , get_class($e));
            };
            if(in_array(get_class($entityOrCollection),[PersistentCollection::class,ArrayCollection::class] )){
                return $classUrl($entityOrCollection->first()).'/search/id,in,'.implode(',',array_map(
                    function($a){return $a->getId();}
                    ,$entityOrCollection->toArray() ));
            }
            return $classUrl($entityOrCollection).'/'.$entityOrCollection->getId();
        });
    }
}