<?php
namespace SWD\Entities;


use App\Factories\EntityManagerFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use SWD\DataController\PaginationParser;
use SWD\DataController\Tracking_interface;
use SWD\Modules\AccessControl\AccessControl;
use SWD\Request\Request;
use SWD\Request\Request_interface;
use SWD\Structures\Bootstrap\Bootstrap;
use SWD\Structures\Doctrine\AssociationMapping;
use SWD\Structures\Doctrine\FieldMapping;
use SWD\Structures\HelperTraits\DotClass;

abstract class EntityBase implements Tracking_interface, EntityBase_interface, \JsonSerializable
{
    const DATE_FORMAT = \DateTime::ATOM;
    use DotClass;
    protected $id, $changedBy, $lastModified;

    /**
     * @param ClassMetadata $m
     */
    static function __loadMetadata($m){
        $b = new ClassMetadataBuilder($m);
        $b->setTable(str_replace(array('App\\Entities\\','\\'),array('','_') ,get_called_class() ));
        $b->createField('id', 'integer')->makePrimaryKey()->generatedValue('IDENTITY')->build();
        
        
        if ($userFactory = AccessControl::getUserFactory()){
            $b->addManyToOne('changedBy', $userFactory::getUserClass());
        }
        
        $b->addField('lastModified', 'datetime');
        
    }
    

    function __construct()
    {
        $this->lastModified = new \DateTime();
        if ( $userFactory = AccessControl::getUserFactory()){
            $this->setChangedBy($userFactory::getCurrentUser(Request::create()));
        }
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getChangedBy()
    {
        return $this->changedBy;
    }
    
    public function setChangedBy($changedBy)
    {
        $this->changedBy = $changedBy;
    }

    /**
     * @param \DateTime $lastModified
     */
    public function setLastModified($lastModified)
    {
        $lastModified = is_object($lastModified) ? $lastModified : new \DateTime($lastModified);
        $this->lastModified = $lastModified;
    }


    /**
     * @return \DateTime
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }
    
    abstract function getName();

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

        $metadata = EntityManagerFactory::create()->getClassMetadata($type);
        foreach($metadata->getFieldNames() as $fieldName){
            //IGNORE id
            if ($fieldName == 'id'){continue;}

            // IGNORE password
            if ($fieldName == 'password'){$data['attributes'][$fieldName] = '******';continue;}

            //IGNORE blobs
            $fieldMapping = new FieldMapping($metadata->getFieldMapping($fieldName));
            if (strtolower($fieldMapping->getType() == 'blob')){continue;}

            if(
                $fieldMapping->getType() == 'datetime'
                && is_callable([$this,'get'.ucfirst($fieldName)])
                && is_object(call_user_func([$this,'get'.ucfirst($fieldName)]))
            ){
                $data['attributes'][$fieldName] = call_user_func([$this,'get'.ucfirst($fieldName)])->format(self::DATE_FORMAT);
                continue;
            }

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
                $otherList = $this->$method();
                foreach($otherList as $other){
                    $id = is_callable(array($other, 'getId')) ? $other->getId() : null;
                    array_push($data['relationships'][$name], array('id'=>$id,'type'=>$mapping->getTargetEntity()));
                }
                continue;
            }
            if($mapping->isManyToOne() || $mapping->isOneToOne()){
                $method = 'get'.ucfirst($name);
                $other = $this->$method();
                $id = is_callable(array($other, 'getId')) ? $other->getId() : null;
                $data['relationships'][$name] = array('id'=>$id,'type'=>$mapping->getTargetEntity());
            }
        }

        return $data;

    }

    /**
     * @param EntityManager $em
     * @param Request_interface $request
     * @return PaginationParser
     */
    /*
    static function findAllPaginated(EntityManager $em, Request_interface $request){
        $class = get_called_class();
        $q = $em->createQueryBuilder()->select('e')->from($class,'e');
        $paginator = new PaginationParser($q,$request);
        return $paginator;
    }
    */
}