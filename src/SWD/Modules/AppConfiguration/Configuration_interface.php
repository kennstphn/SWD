<?php
namespace SWD\Modules\AppConfiguration;


interface Configuration_interface
{
    function getConfigureClass();
    function getField();
    function getValue();

}