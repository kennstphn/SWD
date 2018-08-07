<?php
namespace SWD\Structures\Doctrine;


use Doctrine\ORM\Mapping\ClassMetadataInfo;

class AssociationMapping
{
    protected $fieldName;
    protected $targetEntity;
    protected $type;
    protected $mappedBy;
    protected $inversedBy;
    protected $owningSide;
    protected $sourceEntity;
    protected $fetch;
    protected $cascade;
    protected $cascadeRemove;
    protected $cascadePersist;
    protected $cascadeRefresh;
    protected $cascadeMerge;
    protected $cascadeDetach;
    protected $joinColumns;
    protected $sourceToTargetKeyColumns;
    protected $joinColumnFieldNames;
    protected $targetToSourceKeyColumns;
    protected $orphanRemoval;

    function __construct(array $mapping)
    {
        $this->fieldName = $mapping['fieldName'];
        $this->targetEntity = $mapping['targetEntity'];
        $this->type = $mapping['type'];
        $this->mappedBy = $mapping['mappedBy'];
        $this->inversedBy = $mapping['inversedBy'];
        $this->owningSide = (bool) $mapping['isOwningSide'];
        $this->sourceEntity = $mapping['sourceEntity'];
        $this->fetch = $mapping['fetch'];
        $this->cascade = $mapping['cascade'];
        $this->cascadeRemove = (bool) $mapping['isCascadeRemove'];
        $this->cascadePersist = (bool) $mapping['isCascadePersist'];
        $this->cascadeRefresh = (bool) $mapping['isCascadeRefresh'];
        $this->cascadeMerge = (bool) $mapping['isCascadeMerge'];
        $this->cascadeDetach = (bool) $mapping['isCascadeDetach'];
        $this->joinColumns = $mapping['joinColumns'];
        $this->sourceToTargetKeyColumns = $mapping['sourceToTargetKeyColumns'];
        $this->joinColumnFieldNames = $mapping['joinColumnFieldNames'];
        $this->targetToSourceKeyColumns = $mapping['targetToSourceKeyColumns'];
        $this->orphanRemoval = $mapping['orphanRemoval'];
    }

    /**
     * @return string | null
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return string | null
     */
    public function getTargetEntity()
    {
        return $this->targetEntity;
    }

    /**
     * @return string | null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string | null
     */
    public function getMappedBy()
    {
        return $this->mappedBy;
    }

    /**
     * @return string | null
     */
    public function getInversedBy()
    {
        return $this->inversedBy;
    }

    /**
     * @return boolean
     */
    public function isOwningSide()
    {
        return $this->owningSide;
    }

    /**
     * @return string | null
     */
    public function getSourceEntity()
    {
        return $this->sourceEntity;
    }

    /**
     * @return string | null
     */
    public function getFetch()
    {
        return $this->fetch;
    }

    /**
     * @return string | null
     */
    public function getCascade()
    {
        return $this->cascade;
    }

    /**
     * @return boolean
     */
    public function isCascadeRemove()
    {
        return $this->cascadeRemove;
    }

    /**
     * @return boolean
     */
    public function isCascadePersist()
    {
        return $this->cascadePersist;
    }

    /**
     * @return boolean
     */
    public function isCascadeRefresh()
    {
        return $this->cascadeRefresh;
    }

    /**
     * @return boolean
     */
    public function isCascadeMerge()
    {
        return $this->cascadeMerge;
    }

    /**
     * @return boolean
     */
    public function isCascadeDetach()
    {
        return $this->cascadeDetach;
    }

    /**
     * @return string | null
     */
    public function getJoinColumns()
    {
        return $this->joinColumns;
    }

    /**
     * @return string | null
     */
    public function getSourceToTargetKeyColumns()
    {
        return $this->sourceToTargetKeyColumns;
    }

    /**
     * @return string | null
     */
    public function getJoinColumnFieldNames()
    {
        return $this->joinColumnFieldNames;
    }

    /**
     * @return string | null
     */
    public function getTargetToSourceKeyColumns()
    {
        return $this->targetToSourceKeyColumns;
    }

    /**
     * @return string | null
     */
    public function getOrphanRemoval()
    {
        return $this->orphanRemoval;
    }

    public function isManyToOne(){
        return $this->getType() === ClassMetadataInfo::MANY_TO_ONE;
    }

    public function isManyToMany(){
        return $this->getType() === ClassMetadataInfo::MANY_TO_MANY;
    }

    public function isOneToMany(){
        return $this->getType() === ClassMetadataInfo::ONE_TO_MANY;
    }

    public function isOneToOne(){
        return $this->getType() === ClassMetadataInfo::ONE_TO_ONE;
    }
}