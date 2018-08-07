<?php
namespace SWD\Modules\AccessControl;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use App\Factories\EntityManagerFactory;
use SWD\Website\Module;
use SWD\Website\Website;

class AccessControl extends Module
{
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
        if ( ! $factory){throw new \Exception('Missing '.UserFactory_interface::USER_FACTORY.' class ');}

        $u = $factory::getCurrentUser($website->request());
        if ( ! $factory::userCan($u, $website->request(), $this->em)){
            $website->response()->addError(new \Exception('User can not perform that action'));
            $website->response()->setResponseCode(403);
        }

        $website->modules()->invokeModulesByHook(self::ACCESS_CONTROL_DONE, $website);
    }

    /**
     * @return false | UserFactory_interface
     */
    static function getUserFactory(){
        if ( ! class_exists(UserFactory_interface::USER_FACTORY)){return false;}
        
        if ( ! in_array(UserFactory_interface::class, class_implements(UserFactory_interface::USER_FACTORY))){return false;}
        
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
}