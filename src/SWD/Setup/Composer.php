<?php
namespace SWD\Setup;
use Composer\Installer\InstallerEvent;
use SWD\Factories\EntityManagerFactory;

class Composer
{

    static function postDependenciesSolving(InstallerEvent $event ){
        // install templates
        // install App Directories

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

    static function postUpdateCmd(){
        //todo

        //review templates for updates
    }

    protected static function getIndexPage(){
        return
'<?php
 include_once \'../vendor/autoload.php\';
spl_autoload_register(function($class){
    $relfile = str_replace(\'\\\',\'/\',$class).\'.php\';
    $directory = str_replace(\'public\', \'src\', __DIR__);
    $file = str_replace(\'//\', \'/\', $directory.\'/\'.$relfile);
    if($relfile == \'SWD/Modules/TwigRenderer/Twig.php\'){
        @include_once $file;return; // patch for renderer call interface inconsistency
    }
    if (file_exists($file)){include_once $file;}
});

define(\'VENDOR_DIRECTORY\',str_replace(\'public\',\'vendor\',__DIR__));
define(\'PUBLIC_DIR\',__DIR__);



try{
    $theRequest = \SWD\Request\Request::create();
    
    $website = new \SWD\Website\Website($theRequest,new \SWD\Response\Response() );
    $website->addModule($website::INIT,                 \SWD\Modules\EntityInstaller\EntityInstaller::class);
    $website->addModule($website::INIT,                 \SWD\Modules\EntityInstaller\EntityInstaller::class);   // Installs entities on each request. Helpful for development mode. todo remove in production.
        
    $website->addModule($website::INIT,                 \SWD\Modules\Redirect\Redirect::class);                 // enables redirect through \App\Entities\Redirect entities
    $website->addModule($website::INIT,                 \SWD\Modules\TwigRenderer\TwigRenderer::class);         // twig rendering module
    $website->addModule($website::INIT_DONE,            \SWD\Modules\AccessControl\AccessControl::class);       // basic sitemap-enabled access control. 
    $website->addModule($website::RENDER_BEFORE,        \SWD\Modules\JsonRenderingSwitch\JsonRenderingSwitch::class);       // enables render-engine skipping via $_GET[\'json\'] parameter
    $website->addModule($website::RENDER_BEFORE,        \SWD\Modules\TwigTemplateFunctions\TwigTemplateFunctions::class);   // adds helpful twig function add-ons. 
    $website->addModule($website::INIT,                 \SWD\Modules\AppConfiguration\AppConfiguration::class);

    $website->run();
    }catch (Throwable $e){
    $httpCode = $e->getCode() === 0 ? 500  : $e->getCode();
    http_response_code($httpCode);
    if (array_key_exists(\'json\',$_GET)){
        header(\'Content-type: application/json\');
        echo json_encode([\'errors\'=>[
            array(
                ,\'code\'=>(string)$httpCode
                ,\'detail\'=>$e->getMessage()
                ,\'meta\'=>[
                    \'type\'=>get_class($e)
                    ,\'file\'=>$e->getFile()
                    ,\'line\'=>$e->getLine()
                    ,\'trace\'=>$e->getTraceAsString()
                ]
            )
        ]]);
    }else{
        echo array_key_exists(\'json\',$_GET) ? : \'<pre>\'. implode(PHP_EOL,array(
                \'CLASS/Type: \'.get_class($e),
                \'CODE: \'.$e->getCode(),
                \'MSG: \'.$e->getMessage(),
                \'FILE: \'.$e->getFile(),
                \'LINE: \'.$e->getLine(),
            )).PHP_EOL
            .PHP_EOL
            .\'TRACE:\'.PHP_EOL
            .$e->getTraceAsString();
    }
}

?>';
    }

}