<?php
namespace SWD\Structures\Bootstrap;


class Property
{
    public $name;
    public $type;

    /**
     * @var $required
     * @depreciated
     */
    public $required;

    public $isRequired;
    public $returnType;

    protected $isScalar;
    function isScalar():bool{
        if( ! is_null($this->isScalar)){return $this->isScalar;}

        $this->isScalar = in_array($this->type,[
            'float','int','integer','callable','array','bool','boolean','string','object'
        ]);

        return $this->isScalar;
    }
}