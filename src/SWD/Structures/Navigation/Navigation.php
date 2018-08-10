<?php
namespace SWD\Structures\Navigation;

class Navigation extends \Doctrine\Common\Collections\ArrayCollection implements \JsonSerializable
{
    /** @var  Search|null */
    public $search;
    public $brand;
    public $fixed;

    function jsonSerialize()
    {
        return [
            'search'=>$this->search,
            'brand'=>$this->brand,
            'fixed'=>$this->fixed,
            $this->toArray()
        ];
    }

}