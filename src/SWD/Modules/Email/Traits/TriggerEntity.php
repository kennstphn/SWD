<?php
namespace SWD\Modules\Email\Traits;


use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use SWD\Website\Website;

trait TriggerEntity
{
    
    protected $triggerCondition,$url;
    static function __loadTriggerMetadata($m){
        $b = new ClassMetadataBuilder($m);
        $b->addField('triggerCondition', 'string');
        $b->addField('url', 'string');
        
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }


    

    /**
     * @return string
     */
    public function getTriggerCondition()
    {
        return $this->triggerCondition;
    }

    /**
     * @param string $triggerCondition
     */
    public function setTriggerCondition($triggerCondition)
    {
        $this->triggerCondition = $triggerCondition;
    }

    /**
     * @param Website $website
     * @return $this[]
     * @throws \Exception
     */
    static function findTriggersBy(Website $website){
        /** @var self $me */
        $me = get_called_class();
        
        $url = $website->request()->url()->__toString();
        $list = \App\Factories\EntityManagerFactory::create()->createQueryBuilder()
            ->select('t')->distinct(true)->from($me,'t')
            ->where('t.url = :url')->setParameter('url', $url)
            ->getQuery()->getResult();
        
        /** @var \Twig_Environment $twig */
        $twig = $me::getTwig();
        return array_filter($list, function ($trigger)use($website,$twig) {
            /** @var self $trigger */
            return (bool) (
                $twig->createTemplate(
                    "{{ {$trigger->getTriggerCondition()} }}"
                )->render([
                    'request'=>$website->request(),
                    'response'=>$website->response()
                ]) 
            );
        });
    }
    
    static function getTwig(){
        return new \Twig_Environment(new \Twig_Loader_Array([]));
    }
    
    
}