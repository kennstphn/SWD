<?php
namespace SWD\Modules\TwigRenderer;
use Doctrine\Common\Collections\ArrayCollection;
use SWD\Structures\HelperTraits\DotClass;
use Twig_Error_Loader;
use Twig_Source;

class RegistrationLoader implements \Twig_LoaderInterface
{
    use DotClass;
    
    /** @var  ArrayCollection|null $templates */
    protected static $templates;

    static function registerTemplate($name, $string, $overwrite = false){
        self::$templates = self::$templates ?? new ArrayCollection();
        if( ! $overwrite && self::$templates->containsKey($name)){
            throw new \Exception('Cannot overwrite template '.$name.' without "overwrite" parameter set to true');
        }
        self::$templates->set($name,$string);
    }

    public function getSourceContext($name)
    {
        $code = self::$templates ? self::$templates->get($name) : null;
        if ( is_null($code)){
            throw new Twig_Error_Loader($name.' not registered as a template in '.get_called_class());
        }

        return new Twig_Source($code,$name);
    }

    public function exists($name)
    {
        return self::$templates && self::$templates->containsKey($name);
    }

    public function getSource($name)
    {
        return $this->getSourceContext($name);
    }

    public function getCacheKey($name)
    {
        return self::dotClass().'-'.$name;
    }

    public function isFresh($name, $time)
    {
        if( ! self::$templates || ! self::$templates->containsKey($name)){
            throw new Twig_Error_Loader($name.' not registered as a template in '.get_called_class());
        }
        
        return false; //todo implement logic to avoid rebuilding cache every call
    }


}