<?php
namespace SWD\DataController;


use Doctrine\ORM\EntityManager;
use SWD\Entities\DefinesAssociationFormFields_interface;
use SWD\Structures\FormField;

class FormParser
{

    protected $entityClass, $entityManager, $entity;

    function __construct($entityClass, EntityManager $em)
    {
        $this->entityClass = (is_object($entityClass))?get_class($entityClass) : $entityClass;
        $this->entityManager = $em;
        $this->entity = is_object($entityClass) ? $entityClass : new $entityClass;
    }

    protected function getMetadataInfo(){
        return $this->entityManager->getClassMetadata($this->entityClass);
    }
    
    protected function getFieldMaps(){
        $array = array();
        foreach($this->getMetadataInfo()->getFieldNames() as $name){
            array_push($array, $this->getMetadataInfo()->getFieldMapping($name));
        }
        return $array;
    }
    /**
     * @param object|null $entity
     * @throws \Exception
     * @return FormField[]
     */
    function getFormFields(){
        $entity = $this->entity;

        $fields = array();
        foreach($this->getFieldMaps() as $i => $map){


            $id = str_replace('\\', '-', $this->getEntityClass()).'-'.$map['fieldName'];
            if ($entity && is_callable(array($entity, 'getId'))){$id.='-'.$entity->getId();}

            $field = new FormField($id);

            //formfields with no setter are readonly
            if ( ! method_exists($this->entityClass, 'set'.ucfirst($map['fieldName']))){
                $field->setReadOnly(true);
            }

            if (in_array($map['fieldName'],$this->getMetadataInfo()->getIdentifierFieldNames())){
                $field->setReadOnly(true);
            }
            if($this->isTrackable($this->entityClass) && $map['fieldName'] == 'lastModified'){continue;}

            switch($map['type']){
                case 'integer':
                    $field->setType('number');
                    break;
                case 'float':
                    $field->setType('number');
                    $field->setStep('.01');
                    break;
                case 'text':
                    $field->setTag('textarea');
                    $field->setType('textarea');
                    break;
                case 'boolean':
                case 'bool':
                    $field->setType('boolean');
                    break;
                case 'blob':
                    $field->setType('file');
                    break;
                case 'datetime':
                    $field->setType('datetime');
                    break;
                case 'array':
                case 'json_array':
                    $field->setType('array');
                    break;
                default:
                    $field->setType('text');
                    break;
            }
            if(strtolower($map['fieldName']) == 'email'){$field->setType('email');}

            $field->setName($map['fieldName']);
            $field->setLabel(ucfirst($map['fieldName']));

            //INTEGRATE Entity Values if Entity Exists
            $method = 'get'.ucfirst($map['fieldName']);
            if (
                $entity && is_callable(array($entity,$method))
                && $method !== 'getPassword' //SKIP PASSWORD
            ){
                $field->setValue(call_user_func(array($entity,$method)));
            }

            //REQUIRED!
            $field->setRequired(true);
            if (array_key_exists('nullable', $map) && $map['nullable'] == true){
                $field->setRequired(false);
            }
            // Entity already exists, and file uploads aren't rendered.
            if ($field->getType() === 'file' && $entity){$field->setRequired(false);}

            // Entity already exists, don't want to force password updates
            if ($map['fieldName'] === 'password' && $entity){$field->setRequired(false);}

            array_push($fields , $field);
        }

        if(in_array(DefinesAssociationFormFields_interface::class, class_implements($this->getEntityClass() ) )){
            return array_merge($fields,call_user_func([$this->getEntityClass(),'getAssociationFormFields'],$this->getEntity()));
        }
        
        $generator = new AssociationalFormFieldParser($this->getEntityClass(),$this->getEntityManager() );

        foreach($this->getAssiociationMappings() as $mapping){
            if( array_key_exists('nullable',$mapping) &&  $mapping['nullable']){continue;}
            if( 
                array_key_exists('joinColumns', $mapping) 
                && ( ! array_key_exists('nullable', $mapping['joinColumns'][0]) || $mapping['joinColumns'][0]['nullable'] === true)
            ){continue;}
            
            if (
                $this->isTrackable($this->getEntityClass())
                && in_array($mapping['fieldName'], ['changedBy','lastModified'] )
            ){
                continue;
            }
            
            $generator->setEntity($entity);
            array_push($fields, $generator->getFormField($mapping));
        }

        return $fields;
    }

    protected function getAssiociationMappings(){
        return $this->getMetadataInfo()->getAssociationMappings();
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return null
     */
    public function getEntity()
    {
        return $this->entity;
    }
    
    protected function isTrackable($class){
        return (in_array(Tracking_interface::class, class_implements($class)));
    }



}