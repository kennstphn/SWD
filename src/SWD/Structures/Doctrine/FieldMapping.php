<?php
namespace SWD\Structures\Doctrine;


use App\Factories\EntityManagerFactory;

class FieldMapping
{
    protected $fieldName;
    protected $type;
    /** @var bool | null $nullable*/
    protected $nullable;
    protected $columnName;

    function __construct(array $mapping)
    {

        $this->fieldName = $mapping['fieldName'];
        $this->type = $mapping['type'];
        $this->nullable = (array_key_exists('nullable',$mapping )) ? $mapping['nullable'] : null;
        $this->columnName = $mapping['columnName'];
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool|null
     */
    public function isNullable()
    {
        return $this->nullable;
    }

    /**
     * @return string
     */
    public function getColumnName()
    {
        return $this->columnName;
    }



}