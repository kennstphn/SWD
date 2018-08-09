<?php
namespace SWD\Setup;
use Composer\Script\Event;
use SWD\Factories\EntityManagerFactory;

class Composer
{

    static function postUpdateCmd($event){
        // install templates
        // install App Directories
        /** @var Event $event */

        $vendorPath = $event->getComposer()->getConfig()->get('vendor-dir');
        $assumeLoc = dirname($vendorPath);

        $tmplDir =$assumeLoc.'/templates';
        if ( ! is_dir($tmplDir)){
            $success = mkdir($tmplDir);
            if ( ! $success){throw new \Exception('Unable to create directory '.$tmplDir);}
        }

        $src = str_replace('Composer.php', 'templates', __FILE__);
        foreach(scandir($src) as $file){
            if( in_array($file, ['.', '..'])){continue;}
            copy($src.'/'.$file,$tmplDir.'/'.$file);
        }

        if ( ! is_dir($assumeLoc.'/src')){
            mkdir($assumeLoc.'/src');
        }

        $appDir = $assumeLoc.'/src/App';

        if (! is_dir($appDir)){
            if ( ! mkdir($appDir)){throw new \Exception('unable to make missing directory '.$appDir);}
        }

        foreach(['Factories','Entities','Modules','Controllers'] as $d){
            if ( ! is_dir($appDir.'/'.$d)){
                if ( ! mkdir($appDir.'/'.$d)){throw new \Exception('unable to create missing directory'.$appDir.'/'.$d);}
            }
        }

        if($event->getIO()->askConfirmation('would you like to create App\\Factories\\EntityManagerFactory now?'.PHP_EOL)){
            $user = $event->getIO()->ask('please enter the username to use for mysql'.PHP_EOL);
            $password = $event->getIO()->ask('please enter the password to use for mysql'.PHP_EOL);
            $dbName = $event->getIO()->ask('please enter the database name to use for mysql'.PHP_EOL);

            $file = EntityManagerFactory::$file;
            $file = str_replace([
                'namespace SWD\Factories',
                'abstract class EntityManagerFactory',
                'user():string;',
                'password():string;',
                'dbname():string;',
                'entityDirectory():string;',
                'abstract'
            ],[
                'namespace App\Factories',
                'class EntityManagerFactory',
                'user():string{return \''.$user.'\'; }',
                'password():string{return \''.$password.'\'; }',
                'dbname():string{return \''.$dbName.'\'; }',
                'entityDirectory():string{ return \''.$appDir.'/Entities\' ;}',
                ''
            ],file_get_contents($file));

            file_put_contents($appDir . '/Factories/EntityManagerFactory.php', $file);
        }

        if( ! is_dir($assumeLoc.'/public_html')){
            mkdir($assumeLoc.'/public_html');
        }
        file_put_contents($assumeLoc.'/public_html/index.php',self::getIndexPage());

    }

    protected static function getIndexPage(){
        return file_get_contents(__DIR__.'/index.phps');
    }

}