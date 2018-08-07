<?php
namespace SWD\Modules\AccessControl;


use Doctrine\ORM\EntityManager;
use SWD\Request\Request_interface;

interface UserFactory_interface
{
    const USER_FACTORY = 'App\\Factories\\UserFactory';
    
    static function getGuestUser(EntityManager $em):User_interface;
    static function getUserClass():string;
    static function getCurrentUser(Request_interface $request):User_interface;
    static function setPersistantUser(User_interface $user);
    static function userCan(User_interface $user, Request_interface $request, EntityManager $em):bool;
}