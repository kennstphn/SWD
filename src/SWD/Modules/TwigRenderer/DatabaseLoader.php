<?php
namespace SWD\Modules\TwigRenderer;

use App\Factories\EntityManagerFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NoResultException;
use SWD\Structures\HelperTraits\DotClass;
use Twig_Error_Loader;

class DatabaseLoader implements \Twig_LoaderInterface
{
    use DotClass;
    /** @var \Doctrine\ORM\EntityManager  */
    protected $em;
    /** @var ArrayCollection  */
    protected $retrieved;

    function __construct()
    {
        $this->em = EntityManagerFactory::create();
        $this->retrieved = new ArrayCollection();
    }

    public function getSource($name)
    {
        return $this->getSourceContext($name);
    }


    public function getSourceContext($name)
    {

        return new \Twig_Source($this->getTemplateByName($name)->getSource(), $name);
    }

    public function getCacheKey($name)
    {
        return "{$this->dotClass()}-{$name}-{$this->getTemplateByName($name)->getId()}";
    }

    public function isFresh($name, $time)
    {
        return $this->getTemplateByName($name)->getLastModified()->getTimestamp() < $time;
    }

    public function exists($name)
    {
        try{
            return class_exists('App\\Entities\\TwigTemplate') && $this->getTemplateByName($name);
        }catch (Twig_Error_Loader $e){
            return false;
        }
    }

    /**
     * @param $name
     * @return TwigTemplate
     * @throws Twig_Error_Loader
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getTemplateByName($name){
        try{
            if ( ! $this->retrieved->containsKey($name)){
                $template = $this->em->createQueryBuilder()->select('t')->distinct(true)->from(\App\Entities\TwigTemplate::class,'t')
                    ->where('t.name = :name')->setParameter('name', $name)->getQuery()->getSingleResult();
                $this->retrieved->set($name, $template);
            }
        }catch (NoResultException $e){
            throw new Twig_Error_Loader('can\'t find template '.$name.' for retrieval');
        }
        return $this->retrieved->get($name);
    }


}