<?php
namespace SWD\Structures\Bootstrap;

use SWD\Structures\Bootstrap\Exception\BootstrapException;
use SWD\Structures\Bootstrap\Exception\InvalidSubArray;
use SWD\Structures\Bootstrap\Exception\MissingArrayKey;
use SWD\Structures\Bootstrap\Exception\RequiredFieldIsNull;
use SWD\Structures\Bootstrap\Exception\SubClassInstantiationFailure;
use SWD\Structures\Bootstrap\Exception\TypeError;

/**
 * Class Bootstrap
 * @package SWD\Structures\Bootstrap
 *
 * #Scraping Rules
 * see notes on .\SelfScraping for scraping rules
 *
 * #Exceptions
 * All Exceptions extend from the base .\Exception\BootstrapException and have a code of 400,
 * with the single distinction of .\Exception\SubClassInstantiationFailure which has a code of 500, indicating an server-side
 * implementation failure
 *
 * #Implementation/Integration
 * to enable integration, simple use the trait .\Bootstrap. Alternately, you can implement .\Bootstrap_interface instead
 * and define your own rules. This will allow your class to be recursively constructed via any .\Bootstrap useing class
 *
 */
trait Bootstrap
{
    use SelfScraping;

    /**
     * @param array $array
     * @return $this
     * @throws InvalidSubArray
     * @throws MissingArrayKey
     * @throws RequiredFieldIsNull
     * @throws SubClassInstantiationFailure
     * @throws TypeError
     */
    static function fromArray(array $array){
        /** @var Bootstrap $class */
        $class = get_called_class();
        $properties = $class::_scrape();
        $me = new $class;

        foreach($properties as $property){
            //invalidate missing required field
            if ($property->required && ! array_key_exists($property->name, $array)){
                throw new MissingArrayKey("Missing Field: {$property->name}");
            }

            $val = $array[$property->name];
            $val = $val===''? null : $val;


            if($property->required && is_null($val)){
                throw new RequiredFieldIsNull($property->name);
            }

            /*
             * Instantiate non-scalar sub-entities;
             */
            if( ! $property->isScalar()){
                // non-scalar properties require instantiation arrays of their own
                if ( ! is_array($val)){ throw new InvalidSubArray($property->name, $property->type); }

                // non-scalar property->type (class) must use Bootstrap or independantly implement interface
                if(
                    ! in_array(Bootstrap::class, class_uses($property->type))
                    && ! in_array(Bootstrap_interface::class, class_implements($property->type))
                ){
                    throw new SubClassInstantiationFailure($property->type); }

                /** @var Bootstrap|Bootstrap_interface $subArrayClass */
                $subArrayClass = $property->type;
                $val = $subArrayClass::fromArray($val);

            }

            //enforce scalar types
            if( $property->isScalar() ){
                switch ($property->type){
                    case 'object':
                        $val = (object) $val;
                        break;
                    case $property->type == 'int' && $val === '0':
                    case $property->type == 'integer' && $val === '0':
                        $val = 0;
                        break;
                    default:
                        // let php internal coercion handle these
                        break;
                }
            }

            try{
                if( ! is_null($val)){
                    $setter = 'set'.ucfirst($property->name);
                    $me->$setter($val);
                }
            }catch (\TypeError $e){
                throw new TypeError($e->getMessage(),400,$e);
            }

        }
        return $me;
    }

    function toArray(){
        $array=[];
        foreach($this::_scrape() as $property){
            $name = $property->name;

            if($property->isScalar()){
                $array[$name] = $this->$name;
            }else{
                $array[$name] = $this->$name->toArray();
            }
        }
        return $array;
    }
}