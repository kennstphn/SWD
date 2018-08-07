<?php
namespace SWD\Helper;
class TwigFilterCollection
{
    static function dotClass():\Twig_Filter{
        return new \Twig_Filter('dotClass', function ($ob) {
            if( ! is_object($ob)){return gettype($ob);}
            return str_replace(['\\', '..'], '.', get_class($ob));
        });
    }

    static function base64():\Twig_SimpleFilter{
        return new \Twig_SimpleFilter('base64', function ($string) {
            return base64_encode($string);
        });
    }
}