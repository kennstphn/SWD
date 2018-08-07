<?php
namespace SWD\Website;


use Doctrine\Common\Collections\ArrayCollection;

class ModularArray
{
    const MODULE_CLASS_NOT_FOUND = 'Modules must be class names or callable';
    const MODULE_NOT_CALLABLE ='instantiated Modules must be callable';

    protected $calledHooks = [];

    use Constructed_trait;
    /** @var  ArrayCollection $dataContainer */
    protected $dataContainer;

    /**
     * @param string $hookName
     * @param Module | callable | string $moduleOrModuleName
     */
    function addModule(string $hookName, $moduleOrModuleName){
        if ( ! $this->dataContainer){
            $this->dataContainer = new ArrayCollection();
        }
        
        if ( ! $this->dataContainer->containsKey($hookName)) {
            $this->dataContainer->set($hookName,new ArrayCollection());
        }
        
        $this->getModuleCollection($hookName)->add($moduleOrModuleName);
    }
        
    /** @return ArrayCollection|null */
    protected function getModuleCollection($hookName){
        if ( ! $this->dataContainer){return null;}
        if ( ! $this->dataContainer->containsKey($hookName)){return null;}
        return $this->dataContainer->get($hookName);
    }

    /**
     * @param string $hookName
     * @throws \Exception
     */
    function invokeModulesByHook(string $hookName, Website $website){
        array_push($this->calledHooks, $hookName);
        $collection = $this->getModuleCollection($hookName);
        if ( ! $collection){return;}
        
        foreach($collection as $module){
            $this->invokeModule($module,$hookName, $website);
        }
    }

    /**
     * @param string | Module $module
     * @throws \Exception
     */
    protected function invokeModule($module,string $hookName, $website){
        if (is_string($module)){
            $module = $this->constructModuleFromString($module);
        }

        if ( ! is_callable($module)){
            throw new \Exception(self::MODULE_NOT_CALLABLE);
        }

        call_user_func($module,$hookName,$website);
    }

    /**
     * @param string $moduleName
     * @return mixed
     * @throws \Exception
     */
    protected function constructModuleFromString(string $moduleName){
        if ( ! class_exists($moduleName)){throw new \Exception(self::MODULE_CLASS_NOT_FOUND .' -- '. $moduleName);}
        return new $moduleName($this->request,$this->response);
    }

    /**
     * @return array
     */
    public function getCalledHooks()
    {
        return $this->calledHooks;
    }

}