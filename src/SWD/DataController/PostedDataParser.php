<?php
namespace SWD\DataController;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityManager;
use SWD\Entities\EntityBase_interface;
use SWD\Modules\AccessControl\AccessControl;
use SWD\Request\Request_interface;
use SWD\Structures\Doctrine\AssociationMapping;
use SWD\Structures\Doctrine\FieldMapping;

class PostedDataParser
{
    protected $user = null;
    function __construct(Request_interface $request, $purifier = null )
    {
        $this->request = $request;
    }

    protected function setUser($user){
        $this->user = $user;
    }

    protected function getUser(Request_interface $request){
        if ( ! is_null($this->user)){return $this->user;}
        $factory = AccessControl::getUserFactory();
        if ( ! $factory){$this->user = false; return false;}
        $this->user = $factory::getCurrentUser($request);
        return $this->user;

    }


    /**
     * @param EntityBase_interface $entity
     * @param FieldMapping $fieldMapping
     * @throws \Exception
     */
    function updateField(EntityBase_interface $entity, FieldMapping $fieldMapping){

        $name = $fieldMapping->getFieldName();
        
        if ($this->isFieldInRequest($name)){
            $data = $this->typeCastFieldData($this->getData($name),$fieldMapping->getType(),$fieldMapping->isNullable());
            $method = 'set'.ucfirst($name);
            if( ! is_callable([$entity, $method])){return;}
            call_user_func(array($entity,$method),$data);
            return;
        }
        
    }
    
    protected function isFieldInRequest($name){
        if ($this->request->post()->containsKey($name)){return true;}
        if ($this->request->files()->containsKey($name)){return true;}
        return false;
    }
    
    protected function getData($name){
        if ($this->request->post()->containsKey($name) && ! $this->request->files()->containsKey($name)){
            return $this->request->post()->get($name);
        } 
        if ( $this->request->files()->containsKey($name) && $this->request->files()->isArray($name)){
            return
                file_get_contents(
                    $this->request->files()->getAsArrayCollection($name)->get('tmp_name')
                );

        }

        return null;
    }

    
    protected function typeCastFieldData($data,$type,$nullable){
        if ( $nullable && is_string($data) && strtolower($data) === 'null'){return null;}
        if ($data === ''){return null;}

        if ($type == 'integer'){
            if ( ! is_numeric($data)){throw new \Exception('Illegal variable data type for integer -- '.$data);}
            return (int)(float)trim($data);
        }
        
        if ($type == 'float'){
            if ( ! is_numeric($data)){throw new \Exception('Illegal variable data type for float -- '.$data);}
            return (float)trim($data);
        }
        
        if ( $type == 'boolean'){
            if (strtolower(trim($data)) === 'false'){return false;}
            if (strtolower(trim($data)) === 'true') {return true;}
            if (trim($data) === '1'){return true;}
            if (trim($data) === '0'){return false;}
            if (is_bool($data)){return (bool) $data;}
            throw new \Exception('illegal type for boolean '.$data);
        }
        if ($type == 'string'){
            return trim($data);
        }

        if ($type == 'array'){
            $returnedArray = [];
            for($i=0,$c = count($data); $i < $c; $i++){
                $val = trim($data[$i]);
                if(strlen($val) > 0){
                    array_push($returnedArray, $val);
                }
            }
            $data = $returnedArray;
        }

        return $data;
        
    }

    /**
     * @param object $entity
     * @param AssociationMapping $mapping
     * @param EntityManager $em
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     * @throws \SWD\Request\TypeException
     */
    function updateAssociation($entity, AssociationMapping $mapping, EntityManager $em){
        if ( ! $this->isFieldInRequest($mapping->getFieldName())){return;}
        $mappedName = $mapping->getInversedBy() ? $mapping->getInversedBy() : $mapping->getMappedBy();
     
        if ( $mapping->isOneToMany()){
            if ( ! $mappedName ){throw new \Exception('Inversed by not defined for '.get_class($entity).' -- '.$mapping->getTargetEntity() );}
            
            foreach($this->getData($mapping->getFieldName()) as $eId){
                $other = $em->find($mapping->getTargetEntity(),(int) $eId);
                $em->persist($other);
                $method = 'set'.ucfirst($mappedName);
                call_user_func(array($other,$method),$entity);
                return;
            }
        }
        
        if ( $mapping->isManyToMany() ){
            if ( ! $mappedName ){throw new \Exception('Inversed by not defined for '.get_class($entity).' -- '.$mapping->getTargetEntity() );}

            $others = $em->createQueryBuilder()->select('e')->from($mapping->getTargetEntity(), 'e')
                ->addSelect('e2')->leftJoin('e.'.$mappedName,'e2')
                ->where('e.id IN ( :list )')->setParameter('list', $this->getData($mapping->getFieldName()))
                ->getQuery()->getResult();

            switch ( $mapping->isOwningSide()) {
                case true:
                    foreach($others as $o){$em->persist($o);}
                    $a = new ArrayCollection($others);
                    $method = 'set' . $mapping->getFieldName();
                    call_user_func(array($entity, $method), $a);
                    break;
                default:
                    foreach($others as $o){
                        $em->persist($o);
                        $getter = 'get'.ucfirst($mappedName);
                        if ( ! $o->$getter()->contains($entity)){
                            $o->$getter()->add($entity);
                        }
                    }
                    break;
            }
            return;
        }
        // should be only One to One and Many To One
        if ( ! is_numeric($this->request->post()->get($mapping->getFieldName()))){
            if(
                (
                    $this->request->post()->get($mapping->getFieldName()) === ''
                    ||
                    $this->request->post()->get($mapping->getFieldName()) === null
                    ||
                    $this->request->post()->get($mapping->getFieldName()) === 'null'
                ) && (
                    $mapping->isManyToOne()
                    ||
                    ( $mapping->isOneToOne() )
                )
            ){
                $method = 'set'.ucfirst($mapping->getFieldName());
                $entity->$method(null);
                return;
            }
            throw new \Exception('id is requred for associational updates. Received --> '. $this->request->post()->get($mapping->getFieldName()).' for ('.$mapping->getFieldName().')' );
        }
        
        $id = $this->request->post()->getInteger($mapping->getFieldName());
        $other = $em->find($mapping->getTargetEntity(),$id);
        
        if ($mapping->isManyToOne() || $mapping->isOneToOne() && $mapping->isOwningSide()){
            $method = 'set'.ucfirst($mapping->getFieldName());
            $entity->$method($other);
            return;
        }
        
        if ($mapping->isOneToOne() && ! $mapping->isOwningSide()){
            $method = 'set'.ucfirst($mappedName);
            $other->$method($entity);
            $em->persist($other);
            return;
        }
        
        
    }
    
}