<?php
namespace SWD\Entities;


use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

abstract class Sitemap extends EntityBase
{
    
    protected $loc, $changeFreq, $priority;
    
    function __construct()
    {
        parent::__construct();
        $this->changeFreq = 'weekly';
        $this->priority = 0.80;
    }
    
    static function __loadMetadata($m)
    {
        parent::__loadMetadata($m); 
        $b = new ClassMetadataBuilder($m);
        $b->addUniqueConstraint(array('loc'), 'unique_entry');
        
        $b->createField('loc', 'string')->nullable(true)->build();
        $b->addField('changeFreq', 'string');
        $b->addField('priority', 'float');
    }

    /**
     * @return string
     */
    public function getLoc()
    {
        return $this->loc;
    }

    /**
     * @param mixed $loc
     */
    public function setLoc($loc)
    {
        $this->loc = $loc;
    }

    /**
     * @return string
     */
    public function getChangeFreq()
    {
        return $this->changeFreq;
    }

    /**
     * @param string $changeFreq
     */
    public function setChangeFreq($changeFreq)
    {
        $this->changeFreq = $changeFreq;
    }

    /**
     * @return float
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param float $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    function getName()
    {
        return $this->loc;
    }





}