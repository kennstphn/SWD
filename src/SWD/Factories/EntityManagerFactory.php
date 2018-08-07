<?php
namespace SWD\Factories;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;

abstract class EntityManagerFactory
{
    protected static $em;

    static $file = __FILE__;

    protected static function driver(){return 'pdo_mysql';}
    abstract static protected function user():string;
    abstract static protected function password():string;
    abstract static protected function dbname():string;

    static protected function getDriver(){
        $driver = new MappingDriverChain();
        $driver->addDriver(new StaticPHPDriver(str_replace('Factories', 'Entities',__DIR__)),'App\\Entities');
        return $driver;
    }
    /**
     * @return EntityManager
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    static function create(){
        if (self::$em){return self::$em;}

        $proxyDir = substr(getcwd(),0,strrpos(getcwd(),'/' )).'/proxy-entities';
        if ( ! file_exists($proxyDir)){
            $oldMask = umask(0);
            if ( ! mkdir($proxyDir,0775)){
                throw new \Exception('unable to create proxy directory '.$proxyDir );
            };
            umask($oldMask);
        }


        if ( file_exists($proxyDir) && ! is_dir($proxyDir)){
            throw new \Exception('Prodxy directory is not a directory -- '.$proxyDir);
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
            'dbname'   => call_user_func(array($thisClass,'dbname'))
        );

        self::$em = EntityManager::create($connArray, $config);

        return self::$em;
    }

    abstract protected static function entityDirectory():string;

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
        return $classes;
    }
}