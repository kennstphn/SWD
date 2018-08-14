<?php
namespace SWD\Entities;

class Item extends EntityBase
{
    use Item_trait;
    
    protected $tags;
    
    function __construct()
    {
        parent::__construct();
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
    }

    static function __loadMetadata($m)
    {
        parent::__loadMetadata($m);
        self::__loadItemMetadata($m);
        $b = new \Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder($m);
        $b->addOwningManyToMany('tags',\App\Entities\Tag::class,'items');

    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }
    
    
    
    


}