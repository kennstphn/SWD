<?php
namespace SWD\Modules\TwigRenderer;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use SWD\Entities\EntityBase;

abstract class TwigTemplate extends EntityBase
{
    protected $name, $source;

    static function __loadMetadata($m)
    {
        parent::__loadMetadata($m);
        $b = new ClassMetadataBuilder($m);
        $b->addUniqueConstraint(['name'], 'unique_template');
        $b->addField('name', 'string');
        $b->addField('source', 'text');
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
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }



}