<?php
namespace SWD\Modules\Redirect;


use Doctrine\ORM\NoResultException;
use SWD\Website\Module;
use SWD\Website\Website;
use App\Factories\EntityManagerFactory;

class Redirect extends Module
{
    const HOOK = Website::INIT;
    /** @var  Website */
    protected $website;
    
    function __invoke(string $hookName, Website $website)
    {
        if( strtolower($website->request()->method()) == 'post' && $website->request()->get()->containsKey('redirect')){
            $website->addModule($website::CONTROLLER_SECTION_DONE, function ($hookName, Website $website) {
                $website->response()->setRedirect(
                    $website->request()->get()->get('redirect')
                );
            });
        }
        if ( ! class_exists(Redirect_interface::ClASSNAME)){
            return;
        }
        if ( ! in_array(Redirect_interface::class, class_implements(Redirect_interface::ClASSNAME))){
            trigger_error(Redirect_interface::ClASSNAME . ' does not implement ' . Redirect_interface::class.'. Redirect module not able to run. ',E_USER_NOTICE);
            return;
        }
        
        $this->website = $website;
        try{
            $redirect = EntityManagerFactory::create()->createQueryBuilder()
                ->select('r')->from(Redirect_interface::class, 'r')
                ->where('r.from = :url')->setParameter('url', $website->request()->url())
                ->getQuery()->getSingleResult();
        }catch (NoResultException $e ){
            return;
        }
        
        /** @var Redirect_interface $redirect */
        $website->response()->setRedirect($redirect->getTo(),$redirect->getCode());

    }
    
    
    

}