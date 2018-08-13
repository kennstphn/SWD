<?php
namespace SWD\Entities;
use SWD\Structures\FormField;

interface DefinesAssociationFormFields_interface
{
    /** @return FormField[] */
    function getAssociationFormFields();
}