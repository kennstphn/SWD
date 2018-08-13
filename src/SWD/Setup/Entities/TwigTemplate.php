<?php

namespace App\Entities;


class TwigTemplate extends \SWD\Modules\TwigRenderer\TwigTemplate
{
    static function loadMetadata($m)
    {
        parent::__loadMetadata($m);
    }


}