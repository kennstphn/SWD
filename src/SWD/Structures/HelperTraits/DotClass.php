<?php
namespace SWD\Structures\HelperTraits;
trait DotClass
{
    static protected function dotClass(){
        return str_replace(['\\', '..'], '.', get_called_class());
    }

}