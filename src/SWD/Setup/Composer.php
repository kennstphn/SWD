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

        $makeDir($assumeLoc.'/environments');

        /*
         * Init environment db connection
         */
        $createEntityManager = function ()use($event, $writeFile, $assumeLoc,$makeDir){
            if($event->getIO()->askConfirmation('would you like to create an environment in ./envoronments now?'.PHP_EOL)){
                $user = trim($event->getIO()->ask('please enter the username to use for mysql'.PHP_EOL));
                $password = trim($event->getIO()->ask('please enter the password to use for mysql'.PHP_EOL));
                $dbName = trim($event->getIO()->ask('please enter the database name to use for mysql'.PHP_EOL));
                $dbhost = trim($event->getIO()->ask('please enter the host or proceed to use "localhost" ' . PHP_EOL, 'localhost'));
                
                $filename = trim($event->getIO()->ask('Please enter the domain name that will be used to access the site. '.PHP_EOL));
                self::write_ini_file($assumeLoc.'/environments/'.$filename.'.ini',[
                    'dbuser'=>$user,
                    'dbpwd'=>$password,
                    'dbname'=>$dbName,
                    'dbhost'=>$dbhost
                ]);
            }
        };
        $createEntityManager();


        /*
         * index.php 
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
                    str_replace('.phps','.php',$file),          // local filename
                    file_get_contents($srcFile)                 // content
                );
            }
        };

        $copyFilesFromSiblingDir('Entities', $copyFilesFromSiblingDir);
        $copyFilesFromSiblingDir('Controllers', $copyFilesFromSiblingDir);
        
    }

    protected static function getHtaccessText(){
        return implode(PHP_EOL,[
            'RewriteEngine On',
            'RewriteBase /',
            '',
            'RewriteRule ^css(/?[0-9]*)/(.*)?$ css/$2 [QSA,L]',
            'RewriteRule ^js(/?[0-9]*)/(.*)?$ js/$2 [QSA,L]',
            'FallbackResource /index.php'
        ]);
    }

    protected static function getIndexPage(){
        return file_get_contents(__DIR__.'/index.phps');
    }

    static protected function write_ini_file($file, $array = []) {
        // check first argument is string
        if (!is_string($file)) {
            throw new \InvalidArgumentException('Function argument 1 must be a string.');
        }

        // check second argument is array
        if (!is_array($array)) {
            throw new \InvalidArgumentException('Function argument 2 must be an array.');
        }

        // process array
        $data = array();
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $data[] = "[$key]";
                foreach ($val as $skey => $sval) {
                    if (is_array($sval)) {
                        foreach ($sval as $_skey => $_sval) {
                            if (is_numeric($_skey)) {
                                $data[] = $skey.'[] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                            } else {
                                $data[] = $skey.'['.$_skey.'] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                            }
                        }
                    } else {
                        $data[] = $skey.' = '.(is_numeric($sval) ? $sval : (ctype_upper($sval) ? $sval : '"'.$sval.'"'));
                    }
                }
            } else {
                $data[] = $key.' = '.(is_numeric($val) ? $val : (ctype_upper($val) ? $val : '"'.$val.'"'));
            }
            // empty line
            $data[] = null;
        }

        // open file pointer, init flock options
        $fp = fopen($file, 'w');
        $retries = 0;
        $max_retries = 100;

        if (!$fp) {
            return false;
        }

        // loop until get lock, or reach max retries
        do {
            if ($retries > 0) {
                usleep(rand(1, 5000));
            }
            $retries += 1;
        } while (!flock($fp, LOCK_EX) && $retries <= $max_retries);

        // couldn't get the lock
        if ($retries == $max_retries) {
            return false;
        }

        // got lock, write data
        fwrite($fp, implode(PHP_EOL, $data).PHP_EOL);

        // release lock
        flock($fp, LOCK_UN);
        fclose($fp);

        return true;
    }

}