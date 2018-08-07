<?php
namespace SWD\Entities;


interface EntityBase_interface
{
    const USER_FACTORY = 'App\\Factories\\UserFactory';

    /**
     * @return int
     */
    public function getId();

    public function getChangedBy();

    public function setChangedBy($changedBy);

    /**
     * @return \DateTime
     */
    public function getLastModified();

    /**
     * @param \DateTime $dateTime
     */
    //public function setLastModified(\DateTime $dateTime);

}