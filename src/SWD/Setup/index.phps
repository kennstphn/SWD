<?php
include_once '../vendor/autoload.php';
spl_autoload_register(function($class){
    $relfile = str_replace('\\','/',$class).'.php';
    $directory = str_replace('public_html', 'src', __DIR__);
    $file = str_replace('//', '/', $directory.'/'.$relfile);
    if($relfile == 'SWD/Modules/TwigRenderer/Twig.php'){
        @include_once $file;return; // patch for renderer call interface inconsistency
    }
    if (file_exists($file)){include_once $file;}
});

define('VENDOR_DIRECTORY',str_replace('public','vendor',__DIR__));
define('PUBLIC_DIR',__DIR__);




try {
    $theRequest = \SWD\Request\Request::create(array(
        //todo remove the following line in production.
        'get'=> array_merge($_GET,array('install'=>'force')),

    ));

    $website = new \SWD\Website\Website($theRequest,new \SWD\Response\Response() );
    $website->setDebugMode(
        $theRequest->get()->getBool('debug') ? $website::DEBUG_TRUE : $website::DEBUG_FALSE
    );

    \SWD\Modules\DataControllerHookBridge::init($website);

    $website->addModule($website::INIT,                 \SWD\Modules\EntityInstaller\EntityInstaller::class);   // Installs entities on each request. Helpful for development mode.
    $website->addModule($website::INIT,                 \SWD\Modules\EntityNav::class);                         // Creates default Navigation related to entities.
//    $website->addModule($website::INIT,                 \SWD\Modules\Redirect\Redirect::class);                 // enables redirect through \App\Entities\Redirect entities
    $website->addModule($website::INIT,                 \SWD\Modules\TwigRenderer\TwigRenderer::class);         // twig rendering module
    $website->addModule($website::INIT,                 \SWD\Modules\AppConfiguration\AppConfiguration::class);

    $website->addModule($website::INIT_DONE,            \SWD\Modules\AccessControl\AccessControl::class);       // basic sitemap-enabled access control.

    $website->addModule($website::RENDER_BEFORE,        \SWD\Modules\JsonRenderingSwitch\JsonRenderingSwitch::class);       // enables render-engine skipping via $_GET['json'] parameter
    $website->addModule($website::RENDER_BEFORE,        \SWD\Modules\TwigTemplateFunctions\TwigTemplateFunctions::class);   // adds helpful twig function add-ons.


    $website->run();

} catch (Throwable $e){
    $httpCode = $e->getCode() === 0 ? 500  : $e->getCode();
    http_response_code($httpCode);
    if (array_key_exists('json',$_GET)){
        header('Content-type: application/json');
        echo json_encode(['errors'=>[
            array(
                'details'=>$e->getMessage() //todo depreciate this
            ,'code'=>(string)$httpCode
            ,'detail'=>$e->getMessage()
            ,'meta'=>[
                'type'=>get_class($e)
                ,'file'=>$e->getFile()
                ,'line'=>$e->getLine()
                ,'trace'=>$e->getTraceAsString()
            ]
            )
        ]]);
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
