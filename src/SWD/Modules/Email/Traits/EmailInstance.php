<?php

namespace SWD\Modules\Email\Traits;


use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

trait EmailInstance
{
    use EmailEntity;

    static function __loadEmailInstanceMetadata($m){
        $b = new ClassMetadataBuilder($m);
        $b->createField('sent', 'datetime')->nullable(true)->build();
    }
    
    protected $sent;
    /**
     * @param \DateTime | null $dateTime
     */
    function setSent($dateTime){
        $this->sent = $dateTime;
    }

    /**
     * @return \DateTime|null
     */
    function getSent(){
        return $this->sent;
    }

}