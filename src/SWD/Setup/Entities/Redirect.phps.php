<?php
namespace App\Entities;


use SWD\Entities\Redirects;

class Redirect extends Redirects
{
    
    static function loadMetadata($m)
    {
        parent::__loadMetadata($m); 
    }
}