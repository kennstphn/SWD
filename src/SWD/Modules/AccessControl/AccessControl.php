<?php
namespace SWD\Modules\AccessControl;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use App\Factories\EntityManagerFactory;
use SWD\Factories\UserFactory;
use SWD\Structures\HelperTraits\DotClass;
use SWD\Website\Module;
use SWD\Website\Website;

class AccessControl extends Module
{
    use DotClass;
    const ACCESS_CONTROL_DONE = self::class.'-done';
    protected $em;
    protected $parser;
    
    function __construct($request, $response, EntityManager $em = null)
    {
        $this->em = $em ? $em : EntityManagerFactory::create();
        
    }

    function __invoke(string $hookName, Website $website)
    {
        $factory = AccessControl::getUserFactory();
        if ( ! $factory){
            $website->response()->addMeta($this->dotClass(), 'missing '.UserFactory_interface::USER_FACTORY.' class' );
            return;
        }

        $u = $factory::getCurrentUser($website->request());
        if ( ! $factory::userCan($u, $website->request(), $this->em)){
            $website->response()->addError(new \Exception('User can not perform that action',403));
            $website->response()->setResponseCode(403);
        }

        $website->modules()->invokeModulesByHook(self::ACCESS_CONTROL_DONE, $website);
    }

    /**
     * @return UserFactory_interface
     */
    static function getUserFactory(){
        if ( ! class_exists(UserFactory_interface::USER_FACTORY)){return UserFactory::class;}
        if ( ! in_array(UserFactory_interface::class, class_implements(UserFactory_interface::USER_FACTORY))){return UserFactory::class;}

        return UserFactory_interface::USER_FACTORY;
    }

    /**
     * @param $name
     * @param EntityManager|null $em
     * @return false|User_interface|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    static function findUser($name, EntityManager $em = null){
        $factory  = self::getUserFactory();
        if ( ! $factory){return null;}

        $em = $em ? $em : EntityManagerFactory::create();
        try{
            return $em->createQueryBuilder()
                ->select('u')->from($factory::getUserClass(),'u')
                ->where('u.username = :username')->setParameter(':username', $name)
                ->getQuery()->getSingleResult();
        }catch (NoResultException $e){
            return false;
        }

    }

    function bash(){
        $charlist = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_');
        $search = [];
        $ns = 'App\\Entities\\';
        $length = count($charlist);
        $push = function($string)use($search){
            array_push($search, $string);
            echo PHP_EOL.$string;
        };

        $character=function($i)use($charlist){return $charlist[$i];};

        $iterate = function(string $prepend, callable $reiterate, int $depth, $first = true)use($ns,$character, $length, $push){
            $length = $first ? 26 : $length;
            for($i=0;$i<$length;$i++){
                $string = $prepend.$character($i);
                $push($ns.$string);
                if(strlen($string) < $depth){
                    $reiterate($string,$reiterate, $depth,false);
                }
            }
        };

        $iterate('',$iterate,15);



    }
}