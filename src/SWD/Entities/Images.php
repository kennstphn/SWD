<?php
namespace SWD\Entities;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

abstract class Images extends EntityBase
{
    protected $name, $mimeType, $data;
    
    static function __loadMetadata($m)
    {
        parent::__loadMetadata($m);
        $b = new ClassMetadataBuilder($m);
        $b->addField('name', 'string');
        $b->addField('mimeType', 'string');
        $b->addField('data', 'blob');

    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
    
    
}