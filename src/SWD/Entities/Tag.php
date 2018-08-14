<?php
namespace SWD\Entities;

class Tag extends EntityBase
{
    
    protected $name;
 
    protected $items;

    public function __construct()
    {
        parent::__construct();
        $this->items = new \Doctrine\Common\Collections\ArrayCollection();
    }


    static function __loadMetadata($m)
    {
        parent::__loadMetadata($m);
        $b = new \Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder($m);
        $b->addUniqueConstraint(array('name'), 'unique_tag');
        $b->addInverseManyToMany('items',\App\Entities\Item::class ,'tags' );

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

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }
    
    
    


}