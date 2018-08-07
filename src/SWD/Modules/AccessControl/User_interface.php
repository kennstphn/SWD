<?php
namespace SWD\Modules\AccessControl;

interface User_interface
{
    function getId();
    
    function setPassword($password);
    
    function verifyPassword($password);
    
    function getUsername();
    function setUsername($username);

}