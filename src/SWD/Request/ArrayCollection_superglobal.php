<?php
namespace SWD\Request;


use Doctrine\Common\Collections\ArrayCollection;

class ArrayCollection_superglobal extends ArrayCollection
{
    const OPTION_NULLABLE = 'nullable';
    const OPTION_ESCAPE_HTML = 'escape';

    /**
     * @param $key
     * @return float
     * @throws TypeException
     */
    function getFloat($key){
        if ( ! is_numeric($this->get($key))){
            throw new TypeException('can not parse number from ('.$this->get($key).')');
        }
        return (float) $this->get($key);
    }

    /**
     * @param $key
     * @return bool
     * @throws TypeException
     */
    function isInteger($key){
        if ( ! is_numeric($this->get($key))){return false;}
        return $this->getFloat($key) == (int)$this->getFloat($key);
    }
    
    /**
     * @param $key
     * @return int
     * @throws TypeException
     */
    function getInteger($key){
        
        if ( ! is_numeric($this->get($key))){
            throw new TypeException('string is not numeric ('.$this->get($key).')',400);
        }
        if ( ! $this->isInteger($key)){
            throw new TypeException($this->get($key) .' is not an integer',400);
        }
        return (int) $this->getFloat($key); //get float first to allow for 1e2 --> 100
    }

    /**
     * @param $key
     * @return bool
     *
     * this function provides the additional "truthyness" of 'false' equating to false.
     * This will return false for key 'foo' when 'foo' is '', 'faLsE' (not case sensitive), does not exist as a key,
     * or is false when cast to bool
     */
    function getBool($key){
        if ( ! $this->containsKey($key)){return false;}
        $val = $this->get($key);
        if (is_null($val) || $val === ''){return true;}
        if (strtolower($val) === 'false'){return false;}
        
        return (bool) $val;
    }

    /**
     * @param $key
     * @return bool
     */
    function isArray($key){
        $var = $this->get($key);
        return is_array($var);
    }

    /**
     * @param $key
     * @return ArrayCollection_superglobal
     * @throws TypeException
     */
    function getAsArrayCollection($key){
        if ( ! $this->isArray($key)){
            throw new TypeException('Field '.$key.' does not hold an array');
        }
        
        return new ArrayCollection_superglobal($this->get($key));
    }

}