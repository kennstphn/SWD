<?php
namespace SWD\DataController;


interface Tracking_interface
{
    function getChangedBy();
    function setChangedBy($user);
    function getLastModified();
    //function setLastModified(\DateTime $dateTime);
    
}