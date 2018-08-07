<?php
namespace SWD\Structures\Bootstrap;

/**
 * Class SelfScraping
 * @package SWD\Structures\Bootstrap
 *
 * #Implementation
 *
 * This uses non-static setters and getters to scrape structures.
 *
 * ## Included Properties
 * Properties to be included must meet both the following rules.
 *
 * ### Setter
 * There must be a setter defined for that property with exactly 1 required parameter (parameter index 0)
 *
 * ### Getter or Public
 * The property must have public access OR have a getter defined
 *
 * ## Property->{property} calculations
 *
 * ### name
 * The property name is calculated from the setter name, lowercased leading character. "setValue" will lead to property
 * name of "value". The property name is NOT verified against actual properties, which means that a structure is defined
 * primarily via methods, and may choose to ignore properties entirely.
 * 
 * ### type
 * The property type is defined by the type-hint on the setter's required parameter. scalar and class type-hints are allowed
 *
 * ### required
 * The property is considered required if there is a getter AND that getter has a return value enforced;
 *
 * ### returnType
 * The property may have a different return type from the getter than the setter receives. If so, returnType will be scraped
 * from the getter returned value that is enforced. If there is no returnType specified, the returnType will be "mixed"
 *
 */
trait SelfScraping
{
    /**
     * @return Property[]
     *
     */
    static function _scrape(){
        $reflection  =new \ReflectionClass(get_called_class());
        $props = [];

        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $isSetter=function(\ReflectionMethod $m){return strpos($m->name,'set') === 0 ;};

        foreach($methods as $method){
            if($method->isStatic()){continue;} //skip static methods
            if( ! $isSetter($method)){continue;}//only setters
            if($method->getNumberOfRequiredParameters() !== 1){continue;}//setters can only have 1 required parameter

            $property = lcfirst(substr($method->name,3));
            if ($property===''){continue;}//ignore method "set"

            $getter = 'get'.ucfirst($property);
            if(
                ! $reflection->hasMethod($getter)  //no getter
                && ! ($reflection->hasProperty($property) && $reflection->getProperty($property)->isPublic() ) //no public access
            ){
                continue; // ignore setter only protected fields
            }

            $type=$method->getParameters()[0]->getType();

            $required = $reflection->hasMethod($getter) && $reflection->getMethod($getter)->getReturnType();
            $returnType = $reflection->hasMethod($getter) && $reflection->getMethod($getter)->getReturnType()
                ? $reflection->getMethod($getter)->getReturnType()->__toString()
                : 'mixed';

            $type = $type ? $type->__toString() : $type;

            $prop = new Property();
            $prop->name = $property;
            $prop->type = $type;
            $prop->required  = $required;
            $prop->returnType = $returnType;
            
            array_push($props, $prop);
        }
        return $props;
    }
}