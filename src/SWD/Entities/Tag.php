<?php
namespace SWD\Entities;
class Tag extends EntityBase
{
    
    protected $name;
    
    static function __loadMetadata($m)
    {
        parent::__loadMetadata($m);
        $b = new \Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder($m);
        $b->addUniqueConstraint(array('name'), 'unique_tag');

        $b->addField('name','string');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    


}