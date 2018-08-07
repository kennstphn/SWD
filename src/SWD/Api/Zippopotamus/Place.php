<?php
namespace SWD\Api\Zippopotamus;

class Place{
    public $placeName, $longitude,$state, $stateAbbreviation, $latitude;

    static function createFromJsonObject($ob){
        $me = new self;
        foreach(get_object_vars($ob) as $prop => $val){
            $prop = str_replace(['place name', 'state abbreviation'],['placeName', 'stateAbbreviation'], $prop);
            $me->$prop = $val;
        }
        return $me;
    }
}


