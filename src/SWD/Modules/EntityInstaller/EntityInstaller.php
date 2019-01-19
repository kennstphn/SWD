<?php
namespace SWD\Modules\EntityInstaller;

use SWD\Factories\EntityManagerFactory;
use Doctrine\ORM\Tools\SchemaTool;
use SWD\Factories\EnvironmentFactory;
use SWD\Website\Module;
use SWD\Website\Website;

class EntityInstaller extends Module
{
    const PREFERRED_HOOK = Website::INIT_DONE;
    static $install = false;

    function __invoke(string $hookName, Website $website)
    {
        if( ! EnvironmentFactory::find()->runEntityInstaller ){
            return;
        }
        $classList = EntityManagerFactory::listEntityClasses();
        $entityManager = EntityManagerFactory::create();

        $metadataArray = array();
        foreach($classList as $class){
            array_push($metadataArray,$entityManager->getClassMetadata($class));
        }

        $tool = new SchemaTool($entityManager);
        $tool->updateSchema($metadataArray);

    }


}