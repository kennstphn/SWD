<?php
namespace SWD\DataController;

use Doctrine\ORM\EntityManager;
use SWD\Structures\Doctrine\AssociationMapping;
use SWD\Structures\Doctrine\FieldMapping;

trait JsonEntity_trait
{
    abstract function getId();
    /** @return EntityManager */
    abstract function entityManager():EntityManager;

    function jsonSerialize()
    {
        $type = get_class($this);
        $id = $this->getId();

        $data = array(
            'id'=>$id,
            'type'=>$type,
            'attributes'=>array(),
            'relationships'=>array()
        );

        $metadata = $this->entityManager()->getClassMetadata($type);
        foreach($metadata->getFieldNames() as $fieldName){
            // IGNORE password
            if ($fieldName == 'password'){$data['attributes'][$fieldName] = '******';continue;}

            //IGNORE blobs
            $fieldMapping = new FieldMapping($metadata->getFieldMapping($fieldName));
            if (strtolower($fieldMapping->getType() == 'blob')){continue;}


            //check conventional getters
            if (is_callable(array($this,'get'.ucfirst($fieldName)))){
                $data['attributes'][$fieldName] = call_user_func(array($this,'get'.ucfirst($fieldName)));
                continue;
            }

            //check boolean getters
            if (is_callable(array($this,'is'.ucfirst($fieldName)))){
                $data['attributes'][$fieldName] = call_user_func(array($this,'is'.ucfirst($fieldName)));
            }

        }

        foreach ($metadata->getAssociationNames() as $name){
            $mapping = new AssociationMapping($metadata->getAssociationMapping($name));
            if ($mapping->isOneToMany() || $mapping->isManyToMany()){
                $data['relationships'][$name] = array();
                $method = 'get'.ucfirst($name);
                foreach($this->$method() as $other){
                    $id = is_callable(array($other, 'getId')) ? $other->getId() : 'no callable getId';
                    array_push($data['relationships'][$name], array('id'=>$id,'type'=>$mapping->getTargetEntity()));
                }
                continue;
            }
            if($mapping->isManyToOne() || $mapping->isOneToOne()){
                $method = 'get'.ucfirst($name);
                $other = $this->$method();
                $id = is_callable(array($other, 'getId')) ? $other->getId() : 'no callable getId';
                $data['relationships'][$name] = array('id'=>$id,'type'=>$mapping->getTargetEntity()); 
            }
        }
        
        return $data;

    }


}