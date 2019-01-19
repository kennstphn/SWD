<?php
namespace SWD\Modules\AppConfiguration;

use SWD\Factories\EntityManagerFactory;
use SWD\Website\Module;
use SWD\Website\Website;

class AppConfiguration extends Module
{
    const TRIGGER_HOOK = Website::INIT;
    const CONFIG_ENTITY = 'App\Entities\Configuration';
    const CONFIG_INTERFACE = Configuration_interface::class;
    const CONFIGURED_CLASS_INTERFACE = ConfiguredClass_interface::class;
    

    function __invoke(string $hookName, Website $website)
    {
        if ( ! class_exists(self::CONFIG_ENTITY)){
            return;
            trigger_error('Entity class does not exist for App Configurations. Please create ' . self::CONFIG_ENTITY, E_USER_WARNING);
        }
        
        if ( ! in_array(Configuration_interface::class, class_implements(self::CONFIG_ENTITY))){
            throw new \Exception('class '.self::CONFIG_ENTITY.' does not implement required interface '.self::CONFIG_INTERFACE);
        }

        /** @var Configuration_interface[] $configurationList */
        $configurationList = $this->getConfigurationFields();

        for($i=0, $c = count($configurationList); $i < $c; $i++){
            $class = $configurationList[$i]->getConfigureClass();
            $fieldName = $configurationList[$i]->getField();
            $value = $configurationList[$i]->getValue();
            $setter = 'set'.ucfirst($fieldName);

            //Use interface if it's implemented
            if ( in_array(self::CONFIGURED_CLASS_INTERFACE, class_implements($class) )){
                /** @var ConfiguredClass_interface $class */
                $class::setAppConfiguration($configurationList[$i]);
            
            //Or use static setter
            } elseif(is_callable([$class,$setter])){
                    $class->$setter($value);

            //last ditch, assume field is static.
            }else{
                $class::$$fieldName = $value;
            }
        }
        
    }

    /**
     * @throws \Exception
     * @return Configuration_interface[]
     */
    function getConfigurationFields(){
        
        $em = EntityManagerFactory::create();
        $repo = $em->getRepository(self::CONFIG_ENTITY);
        
        $fields = $repo->findAll();
        return $fields;
    }
    
}