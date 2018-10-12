<?php
namespace SWD\Modules\EntityInstaller;

use App\Factories\EntityManagerFactory;
use Doctrine\ORM\Tools\SchemaTool;
use SWD\Website\Module;
use SWD\Website\Website;

class EntityInstaller extends Module
{
    const PREFERRED_HOOK = Website::INIT_DONE;
    static $install = false;

    function __invoke(string $hookName, Website $website)
    {
        $classList = EntityManagerFactory::listEntityClasses();
        $entityManager = EntityManagerFactory::create();

        $metadataArray = array();
        foreach($classList as $class){
            array_push($metadataArray,$entityManager->getClassMetadata($class));
        }

        if ( ! $website->request()->get()->get('install') == 'force'){
            throw new \Exception('For safety, installation will not proceed without ?install=force url override');
        }

        $tool = new SchemaTool($entityManager);
        $tool->updateSchema($metadataArray);

    }


}