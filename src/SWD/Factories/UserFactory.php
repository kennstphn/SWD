<?php
namespace SWD\Factories;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use SWD\Factories\EntityManagerFactory;
use SWD\DataController\DataControllerFactory;
use SWD\Modules\AccessControl\User_interface;
use SWD\Modules\AccessControl\UserFactory_interface;
use SWD\Request\Request_interface;
use SWD\Request\UrlParserFactory;
use SWD\Website\Website;

class UserFactory implements UserFactory_interface
{
    const STORED_USER_KEY = 'user';
    protected static $entityActions =  ['create','edit','delete','group','search'] ;

    static function getUserClass():string
    {
        return 'App\\Entities\\User';
    }



    static function getGuestUser(EntityManager $em):User_interface
    {
        $thisClass = get_called_class();
        try{
            $u = $em->createQueryBuilder()->select('e')->from(call_user_func(array($thisClass,'getUserClass')), 'e')
                ->where('e.username = :username')->setParameter('username', 'guest')
                ->getQuery()->getSingleResult();
        }catch (NoResultException $e){
            $class = call_user_func(array($thisClass,'getUserClass'));
            /** @var User_interface $u */
            $u = new $class;
            $u->setUsername('guest');
            $em->persist($u);
            $em->flush($u);
        }
        return $u;
    }

    static function getCurrentUser(Request_interface $request):User_interface
    {
        if(get_called_class() !== 'App\\Factories\\UserFactory' && class_exists('App\\Factories\\UserFactory')){
            return call_user_func(['App\\Factories\\UserFactory', 'getCurrentUser'],$request);
        }
        /** @var UserFactory_interface $thisClass */
        $thisClass = get_called_class();

        $em = EntityManagerFactory::create();
        $uId = $request->session()->get(self::STORED_USER_KEY);

        $u = $em->find(call_user_func(array($thisClass,'getUserClass')), (int) $uId );
        if ( $u){return $u;}

        return $thisClass::getGuestUser($em);
    }


    static function setPersistantUser(User_interface $user){
        if ( session_status() === PHP_SESSION_DISABLED){throw new \Exception('Sessions are disabled');}
        if ( session_status() === PHP_SESSION_NONE){session_start();}
        $_SESSION[self::STORED_USER_KEY] = $user->getId();
    }

    static function removePersistantUser(){
        if ( session_status() === PHP_SESSION_DISABLED){throw new \Exception('Sessions are disabled');}
        if ( session_status() === PHP_SESSION_NONE){session_start();}
        $_SESSION[self::STORED_USER_KEY] = null;
    }

    static function userCan(User_interface $user, Request_interface $request, EntityManager $em):bool
    {
        if ( substr($request->url()->__toString(), 0,6) == '/login'){return true;}
        $class  ='App\\Entities\\Sitemap';
        if ( ! class_exists($class)){throw new \Exception(self::class.' depends on (missing) '.$class);}


        //allow non-entityAction requests to /sitemap.xml
        if (
            $request->url()->__toString() === '/sitemap.xml'
            || $request->url()->__toString() === '/sitemap.html'
        ){
            return true;
        }

        if (
            $request->url()->__toString() === 'robots.txt'
        ){return true;}

        //allow non-entityAction requests to /file
        $parser = UrlParserFactory::create($request);
        if (
            $request->url()->first() == 'file'
            && ! in_array($parser->getAction(), self::$entityActions)
        ){
            return true;
        }



        $count = $em->createQueryBuilder()->select('count(e.id)')->from($class,'e')
            ->where('e.loc = :url')->setParameter('url', $request->url()->__toString())
            ->getQuery()->getSingleScalarResult();
        if ( $count == 0 && strtolower($user->getUsername()) == 'guest'){
            return false;
        }

        
        return true;
    }



}