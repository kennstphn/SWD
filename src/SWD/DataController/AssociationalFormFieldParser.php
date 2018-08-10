<?php


namespace SWD\DataController;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use SWD\Structures\FormField;
use SWD\Structures\FormSelectOption;

class AssociationalFormFieldParser
{
    /**
     * @var ClassMetadata
     */
    protected $m;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var FormField $fields
     */
    protected $fields;
    protected $entityClass;

    /**
     * @var null | object $entity
     */
    protected $entity;
    function setEntity($entity){$this->entity = $entity;}

    /**
     * AssociationalFormFieldGenerator constructor.
     * @param string $entityClass
     * @param EntityManager $entityManager
     */
    function __construct($entityClass, $entityManager)
    {
        $this->entityClass = $entityClass;
        $this->em = $entityManager;
        $this->m = $entityManager->getClassMetadata($entityClass);

    }

    /**
     * @return FormField
     */
    function getFormField($mapping){

        $field = new FormField(str_replace('\\', '-', $this->entityClass) . '-'.$mapping['fieldName']);
        $field->setType((ClassMetadata::TO_ONE - $mapping['type'] > 0) ? 'select' : 'multiple' );
        $field->setName($mapping['fieldName']);
        $field->setLabel($mapping['fieldName']);
        $field->setTag('select');

        $field->setOptions($this->getOptions($mapping['targetEntity']));

        if ($this->entity){$this->preSelectOptions($this->entity, $mapping['fieldName'], $field);}

        if ($mapping['type'] == ClassMetadata::MANY_TO_ONE){$field->setRequired(true);}

        return $field;
    }

    /**
     * @param $entity
     * @param $fieldName
     * @param FormField $field
     * @throws \Exception
     */
    function preSelectOptions($entity,$fieldName, $field){
        $getField = 'get'.ucfirst($fieldName);

        if ( ! is_callable(array($entity,$getField))){
            throw new \Exception('inaccessible '.$getField.' in '.get_class($entity),500);
        }

        $selectedEntities = call_user_func( array($entity,$getField) );
        if( is_null($selectedEntities) ) {return;}

        //make $selectedEntities iterate-able if it's a one-to-one or many-to-one
        $selectedEntities = (
            get_class($selectedEntities) === 'Doctrine\\ORM\\PersistentCollection'
            || get_class($selectedEntities)=== ArrayCollection::class
        )?$selectedEntities : array($selectedEntities) ;

        $idList = array();
        foreach($selectedEntities as $selectedEntity){
            if (is_null($selectedEntity) ){continue;} //empty PersistentCollection useCase

            if ( ! is_callable(array($selectedEntity, 'getId'))){ //sanity check against missing id Field getter
                throw new \Exception('class '.get_class($selectedEntity).' is missing callable "getId"', 500);
            }

            array_push($idList, $selectedEntity->getId());
        }
        foreach($field->getOptions() as $option){
            if (in_array($option->getValue(),$idList)){$option->setSelected(true);}
        }
    }

    /**
     * @return FormSelectOption[]
     * @throws \Exception
     */
    protected function getOptions($entityClass){
        $options = array();

        if ( ! in_array('getId',get_class_methods($entityClass))){
            throw new \Exception($entityClass.' does not have method "getId()" and is not compatible with '.get_class($this), 500);
        }

        $repo = $this->em->getRepository($entityClass);
        foreach($repo->findAll() as $e){

            $opt = new FormSelectOption();
            $opt->setValue($e->getId());

            $text = is_callable(array($e,'getName')) ? $e->getName() : $e->getId();
            $opt->setText($text);

            array_push($options, $opt);
        }

        return $options;

    }
}