<?php
namespace SWD\Adapters\Ups\Entity;


use Ups\Entity\RatedShipment;

class RatedShipmentArray implements \JsonSerializable
{
    /**
     * @var RatedShipment[]
     */
    protected $ratedShipmentArray = [];

    function __construct(array $ratedShipmentArray =[])
    {
        $this->ratedShipmentArray = $ratedShipmentArray;
        
    }

    function jsonSerialize()
    {
        return $this->extractValue($this->ratedShipmentArray);
    }

    /**
     * @param $object
     * @return array
     */
    function extractProperties( $object){
        $array = [];
        
        foreach(get_object_vars($object) as $propName => $val){
            $array[lcfirst($propName)] = $this->extractValue( $val );
        }
        
        $reflection = new \ReflectionClass(get_class($object));
        $staticMethods = $reflection->getMethods(\ReflectionMethod::IS_STATIC);
        foreach(get_class_methods($object) as $method){
            if (count(array_filter($staticMethods, function (\ReflectionMethod $static)use($method){return $static->getName() == $method;}))){continue;}
            if ( substr($method,0,3) !== 'get'){continue;}
            if ( in_array($method, [
                //excluded methods
                'getAttribute','getElementsByTagName','getAttributeNS','getAttributeNodeNS',

            ])){continue;}

            $prop = lcfirst(substr($method, 3));
            if (array_key_exists($prop, $array)){continue;}

            $array[$prop] = $this->extractValue($object->$method(),$method);
        }
        return $array;
    }

    function extractArray(array $array){
        $arrayVals = [];
        foreach($array as $val){
            array_push($arrayVals, $this->extractValue($val));
        }
        return $arrayVals;
    }

    function extractValue($val){
        switch (gettype($val)){
            case 'object':return $this->extractProperties($val);break;
            case 'array': return $this->extractArray($val);break;
            default:return $val;
        }
    }


}