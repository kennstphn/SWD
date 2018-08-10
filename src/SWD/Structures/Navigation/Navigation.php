<?php
namespace SWD\Structures\Navigation;

class Navigation extends Doctrine\Common\Collections\ArrayCollection
{
    /** @var  Search|null */
    public $search;
    public $brand;
}