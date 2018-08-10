<?php

namespace App\Entities;
use SWD\Entities\Users;

/**
 * Created by PhpStorm.
 * User: ken
 * Date: 8/9/18
 * Time: 6:42 AM
 */
class User extends Users
{
    
    static function loadMetadata($m)
    {
        parent::__loadMetadata($m);
    }

    
}