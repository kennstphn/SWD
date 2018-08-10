<?php
namespace SWD\Setup;
use Composer\Script\Event;
use SWD\Entities\Users;
use SWD\Factories\EntityManagerFactory;
use SWD\Modules\EntityInstaller\EntityInstaller;

class Composer
{
    const DIR_PERMISSIONS = 0755;

    static function postUpdateCmd($event){
        // install templates
        // install App Directories
        /** @var Event $event */

        $makeDir = function($dir){
            if(is_dir($dir)){return true;}
            return mkdir($dir,self::DIR_PERMISSIONS,true);
        };

        $writeFile = function($dir, $file, $contents)use($makeDir){
            if( ! is_dir($dir)){$makeDir($dir);}
            $file = str_replace('.phps','.php',$file);
            file_put_contents($dir.'/'.$file,$contents);
        };

        $assumeLoc = dirname($event->getComposer()->getConfig()->get('vendor-dir'));

        /*
         * import the twig templates
         */
        $importTemplates = function()use($assumeLoc,$writeFile){
            $tmplDir =$assumeLoc.'/templates';
            $srcDirectory = str_replace('Composer.php', 'templates', __FILE__);
            foreach(scandir($srcDirectory) as $file){
                if( in_array($file, ['.', '..'])){continue;}
                $writeFile($tmplDir.'/default',$file,file_get_contents($srcDirectory.'/'.$file));
            }
        };
        $importTemplates();


        /*
         * Make sure the App directories are in place for easy use
         */
        $createAppSubDirectories=function()use($makeDir, $assumeLoc, $writeFile){
            foreach(['Factories','Entities','Modules','Controllers'] as $d){
                $subDir = $assumeLoc.'/src/App/'.$d;
                if ( ! is_dir($subDir)){
                    if ( ! $makeDir($subDir)){throw new \Exception('unable to create missing directory'.$subDir);}
                }
            }
        };
        $createAppSubDirectories();

        /*
         * Init the EntityManager
         */
        $createEntityManager = function ()use($event, $writeFile, $assumeLoc){
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
                    'entityDirectory():string{ return \''.$assumeLoc.'/src/App/Entities\' ;}',
                    ''
                ],file_get_contents($file));

                $writeFile($assumeLoc . '/src/App/Factories','EntityManagerFactory.php', $file);
            }
        };
        $createEntityManager();


        /*
         * INDEX.PHP PAGE
         */
        $createIndexPage = function ()use($assumeLoc,$writeFile){
            $writeFile($assumeLoc.'/public_html','index.php',self::getIndexPage());
        };
        if( file_exists($assumeLoc . '/public_html/index.php')){
            if( $event->getIO()->askConfirmation('overwrite index page in public_html?'.PHP_EOL,true)){
                $createIndexPage();
            }
        }else{
            $createIndexPage();
        }

        /*
         * .htaccess
         */
        if ( ! file_exists($assumeLoc . '/public_html/.htaccess')){
            $writeFile($assumeLoc . '/public_html','.htaccess', self::getHtaccessText());
        }


        /*
         * Write Entity Files
         * NOTE: currently non-recursive
         */
        $copyFilesFromSiblingDir=function($siblingDir, $callableForNested)use($writeFile,$assumeLoc){
            $srcFolder = str_replace('Composer.php', $siblingDir, __FILE__);
            $files = scandir($srcFolder);

            foreach($files as $file){
                if(in_array($file,['.','..'])){continue;}
                $srcFile = $srcFolder.'/'.$file;
                if ( is_dir($srcFile)){
                    $callableForNested($siblingDir.'/'.$file, $callableForNested);
                    continue;
                }
                $writeFile(
                    $assumeLoc.'/src/App/'.$siblingDir,         // folder
                    $file,                                      // local filename
                    file_get_contents($srcFile)                 // content
                );
            }
        };

        $copyFilesFromSiblingDir('Entities', $copyFilesFromSiblingDir);
        $copyFilesFromSiblingDir('Controllers', $copyFilesFromSiblingDir);
        
    }

    protected static function getHtaccessText(){
        return 'FallbackResource /index.php';
    }

    protected static function getIndexPage(){
        return file_get_contents(__DIR__.'/index.phps');
    }

}