<?php
namespace SWD\Structures\Bootstrap;


interface Bootstrap_interface
{
    /**
     * @param array $array
     * @return $this
     */
    static function fromArray(array $array);
}