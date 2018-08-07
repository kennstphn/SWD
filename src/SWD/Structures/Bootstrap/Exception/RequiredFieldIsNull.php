<?php
namespace SWD\Structures\Bootstrap\Exception;

class RequiredFieldIsNull extends BootstrapException{
    public $field;
    function __construct($field, $code = 400, \Exception $previous=null)
    {
        $this->field = $field;
        parent::__construct("Field {$field} is null", $code, $previous);
    }
}