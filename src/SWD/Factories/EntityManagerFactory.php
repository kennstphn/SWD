<?php
namespace SWD\Factories;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;

class EntityManagerFactory
{
    protected static $em;
    protected static $driver;
    const APP_VERSION = 'App\\Factories\\EntityManagerFactory';

    static $file = __FILE__;

    protected static function driver(){
        return EnvironmentFactory::find()->dbdriver ?? 'pdo_mysql';
    }
    static protected function user():string{
        return EnvironmentFactory::find()->dbuser();
    }
    static protected function password():string{
        return EnvironmentFactory::find()->dbpwd();
    }
    static protected function dbname():string{
        return EnvironmentFactory::find()->dbname();
    }

    static protected function host():string{
        return EnvironmentFactory::find()->host?? 'localhost';
    }

    static protected function getDriver(){

        $class = get_called_class();

        if( $class === \SWD\Factories\EntityManagerFactory::class && class_exists(self::APP_VERSION)){
            return call_user_func([self::APP_VERSION, 'getDriver']);
        }

        if ( ! $class::$driver){
            $class::$driver = new MappingDriverChain();
            $class::$driver->addDriver(new StaticPHPDriver(ROOT_DIR.'/src/App/Entities'), 'App\\Entities');
        }
        return $class::$driver;
    }
    /**
     * @return EntityManager
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    static function create(){
        if( get_called_class() === \SWD\Factories\EntityManagerFactory::class && class_exists(self::APP_VERSION)){
            return call_user_func([self::APP_VERSION, 'create']);
        }
        if (self::$em){return self::$em;}

        $proxyDir = substr(getcwd(),0,strrpos(getcwd(),'/' )).'/proxy-entities';
        if ( ! file_exists($proxyDir)){
            $oldMask = umask(0);
            if ( ! mkdir($proxyDir,0775,true)){
                throw new \Exception('unable to create proxy directory '.$proxyDir );
            };
            umask($oldMask);
        }


        if ( file_exists($proxyDir) && ! is_dir($proxyDir)){
            throw new \Exception('Proxy directory is not a directory -- '.$proxyDir);
        }


        $config = Setup::createConfiguration();
        $config->setMetadataDriverImpl(call_user_func([get_called_class(),'getDriver']));
        $config->setProxyDir($proxyDir);
        $config->setAutoGenerateProxyClasses(true);

        $thisClass = get_called_class();
        $connArray = array(
            'driver'   => call_user_func(array($thisClass,'driver')),
            'user'     => call_user_func(array($thisClass,'user')),
            'password' => call_user_func(array($thisClass,'password')),
            'dbname'   => call_user_func(array($thisClass,'dbname')),
            'host'     => call_user_func([$thisClass,'host'])
        );

        self::$em = EntityManager::create($connArray, $config);

        return self::$em;
    }

    protected static function entityDirectory():string{
        return ROOT_DIR.'/src/App/Entities';
    }

    static function listEntityClasses():array{
        return call_user_func([get_called_class(),'getCrawlableClasses']);
    }

    protected static function getCrawlableClasses($dir = null, $classPrefix = 'App\\Entities\\'):array{
        $dir = $dir ? $dir : call_user_func([get_called_class(),'entityDirectory']);
        $classes = array();
        foreach(scandir($dir) as $file){ if ( ! in_array($file, ['.', '..'])){
            switch (is_dir($dir.DIRECTORY_SEPARATOR.$file)){
                case true:
                    foreach(self::getCrawlableClasses($dir.'/'.$file, $classPrefix. $file.'\\') as $class){
                        array_push($classes, $class);
                    }
                    break;
                default:
                    $class = $classPrefix.str_replace('.php', '', $file);
                    if (class_exists($class)){array_push($classes, $class);}
                    break;
            }
        }}
        foreach(self::$classList as $registeredClass){
            if ( ! in_array($registeredClass, $classes)){
                array_push($classes, $registeredClass);
            }
        }
        return $classes;
    }

    static protected $classList = [];
    static function registerEntityClass($class){
        if(class_exists(self::APP_VERSION)){
            call_user_func([self::APP_VERSION, 'registerEntityClass'],$class);
        }
        array_push(self::$classList, $class);
    }
}