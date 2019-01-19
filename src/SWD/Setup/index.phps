<?php
define('ROOT_DIR',implode(DIRECTORY_SEPARATOR,array_slice(explode(DIRECTORY_SEPARATOR,__DIR__),0,-1 )));
define('VENDOR_DIRECTORY',ROOT_DIR.'/vendor');
define('PUBLIC_DIR',__DIR__);

include_once VENDOR_DIRECTORY.'/autoload.php';

spl_autoload_register(function($class){
    $relfile = str_replace('\\','/',$class).'.php';
    $directory = ROOT_DIR.'/src';
    $file = str_replace('//', '/', $directory.'/'.$relfile);
    if($relfile == 'SWD/Modules/TwigRenderer/Twig.php'){
        @include_once $file;return; // patch for renderer call interface inconsistency
    }
    if (file_exists($file)){include_once $file;}
});



try {
    SWD\Factories\EnvironmentFactory::init($_SERVER['HTTP_HOST']);
    $theRequest = \SWD\Request\Request::create();
    
    $website = new \SWD\Website\Website($theRequest,new \SWD\Response\Response() );
    $website->setDebugMode(\SWD\Factories\EnvironmentFactory::find()->debug ?? false);

    \SWD\Modules\DataControllerHookBridge::init($website);

    $website->addModule($website::INIT,                 \SWD\Modules\EntityInstaller\EntityInstaller::class);   // Installs entities on each request. Helpful for development mode.
    $website->addModule($website::INIT,                 \SWD\Modules\TwigRenderer\TwigRenderer::class);         // twig rendering module
    $website->addModule($website::INIT_DONE,            \SWD\Modules\AccessControl\AccessControl::class);       // basic sitemap-enabled access control.
    $website->addModule($website::RENDER_BEFORE,        \SWD\Modules\JsonRenderingSwitch\JsonRenderingSwitch::class);       // enables render-engine skipping via $_GET['json'] parameter
    $website->addModule($website::RENDER_BEFORE,        \SWD\Modules\TwigTemplateFunctions\TwigTemplateFunctions::class);   // adds helpful twig function add-ons.

    $website->run();

} catch (Throwable $e){
    $httpCode = $e->getCode() === 0 ? 500  : $e->getCode();
    http_response_code($httpCode);
    if (array_key_exists('json',$_GET)){
        header('Content-type: application/json');
        echo json_encode(
            ['errors'=>[
                [
                    'code'=>(string)$httpCode
                    ,'detail'=>$e->getMessage()
                    ,'meta'=> 
                    [
                        'type'=>get_class($e)
                        ,'file'=>$e->getFile()
                        ,'line'=>$e->getLine()
                        ,'trace'=>$e->getTraceAsString()
                    ]
                ]
            ]]
        );
    }else{
        echo array_key_exists('json',$_GET) ? : '<pre>'. implode(PHP_EOL,array(
                'CLASS/Type: '.get_class($e),
                'CODE: '.$e->getCode(),
                'MSG: '.$e->getMessage(),
                'FILE: '.$e->getFile(),
                'LINE: '.$e->getLine(),
            )).PHP_EOL
            .PHP_EOL
            .'TRACE:'.PHP_EOL
            .$e->getTraceAsString();
    }
}
