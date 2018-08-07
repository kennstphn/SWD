<?php
namespace SWD\DataController;


use Doctrine\ORM\EntityManager;
use SWD\Request\Request_interface;

class OrderingParser
{
    function __construct(EntityManager $em, string $class, Request_interface $request, string $defaultOrderBy = 'id', string $defaultOrderDirection = 'ASC')
    {
        $this->em = $em;
        $this->class = $class;
        $this->request = $request;
        
        $this->defaultOrderBy = $defaultOrderBy;
        $this->defaultOrderDir = $defaultOrderDirection;
    }
    
    function getSafeOrderBy(string $entityAlias){
        return in_array($this->requestOrderBy(),$this->em->getClassMetadata($this->class)->getFieldNames())
            ? $entityAlias.'.'.$this->requestOrderBy()
            : $entityAlias.'.'.$this->defaultOrderBy;
    }
    
    function getOrderDirection(){
        return $this->request->get()->getBool('desc') 
            ? 'DESC'
            : 'ASC';
    }
    
    protected function requestOrderBy (){
        return $this->request->get()->containsKey('orderBy') ? $this->request->get()->get('orderBy') : null;
    }
    
    function getSafeDQLOrderingFor(string $entityAlias){
        return $this->getSafeOrderBy($entityAlias).' '.$this->getOrderDirection();
    }
}