<?php
namespace SWD\Setup;
use Composer\Installer\PackageEvent;
use SWD\Factories\EntityManagerFactory;

class Composer
{

    static function postDependenciesSolving(PackageEvent $event ){
        // install templates
        // install App Directories


        $dir =$event->getIO()->ask('please enter the location of the "templates" directory. This should be beside your src and vendor directories',getcwd().'/templates');
        if ( ! is_dir($dir)){
            $success = mkdir($dir);
            if ( ! $success){throw new \Exception('Unable to create directory '.$dir);}
        }

        $src = str_replace('Composer.php', 'templates', __FILE__);
        while($file = scandir($src)){
            if( in_array($file, ['.', '..'])){continue;}
            copy($src.'/'.$file,$dir.'/'.$file);
        }


        $appDir = $event->getIO()->ask('Enter the location of your Autoloaded App Directory');

        if (! is_dir($appDir)){
            if ( ! mkdir($appDir)){throw new \Exception('unable to make missing directory '.$appDir);}
        }

        foreach(['Factories','Entities','Modules','Controllers'] as $d){
            if ( ! is_dir($appDir.'/'.$d)){
                if ( ! mkdir($appDir.'/'.$d)){throw new \Exception('unable to create missing directory'.$appDir.'/'.$d);}
            }
        }

        if($event->getIO()->askConfirmation('would you like to create App\\Factories\\EntityManagerFactory now?')){
            $user = $event->getIO()->ask('please enter the username to use for mysql');
            $password = $event->getIO()->ask('please enter the password to use for mysql');
            $dbName = $event->getIO()->ask('please enter the database name to use for mysql');

            $file = EntityManagerFactory::$file;
            $file = str_replace([
                'namespace SWD\Factories',
                'abstract class EntityManagerFactory',
                'user():string;',
                'password():string;',
                'dbname():string;',
                'entityDirectory():string;'
            ],[
                'namespace App\Factories',
                'class EntityManagerFactory',
                'user():string{return '.$user.'; }',
                'password():string{return '.$password.'; }',
                'dbname():string{return '.$dbName.'; }',
                'entityDirectory():string{ return '.$appDir.'/Entities'.';}'
            ],file_get_contents($file));

            file_put_contents($appDir . '/Factories/EntityManagerFactory.php', $file);
        }

    }

    static function postUpdateCmd(){
        //todo

        //review templates for updates
    }

}