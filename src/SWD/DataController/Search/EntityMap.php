<?php
namespace SWD\DataController\Search;


use SWD\Factories\EntityManagerFactory;
use Doctrine\ORM\Mapping\ClassMetadata;

class EntityMap
{
    const FIELD = 1;
    const ASSOCIATION = 2;
    
    protected $e, $m;
    function __construct($entityName){
        $this->e = $entityName;
        if ( ! is_callable($entityName,'loadMetadata')){throw new \Exception('No callable loadMetadata for class '.$entityName,500);}
    }

    /**
     * @return ClassMetadata
     */
    function getMetadata(){
        if (is_null($this->m)){$this->loadMetadata();}
        return $this->m;
    }
    
    protected function loadMetadata(){
        $this->m = EntityManagerFactory::create()->getClassMetadata($this->e);
    }

    /**
     * @param $prop
     * @return integer | false
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    function getMappingType($prop){
        
        if ($this->getMetadata()->hasField($prop)){
            return self::FIELD;
        }

        //throws exception if field not found. 
        if ($this->getMetadata()->hasAssociation($prop)){
            return self::ASSOCIATION;
        } 
        
        return false;
    }

    function hasField($field){return $this->getMetadata()->hasField($field);}
    function hasAssociation($name){return $this->getMetadata()->hasAssociation($name);}
}