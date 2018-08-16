<?php
namespace SWD\Request;


use SWD\Request\ArrayCollection_superglobal as ArrayCollection;

class Request implements Request_interface
{
    protected static $superglobals = ['get', 'post', 'session', 'server', 'env', 'files', 'cookie', 'request'];

    /**
     * @var ArrayCollection
     */
    protected $cookie, $env, $files, $get, $post, $request, $server, $session;
    
    function __construct(
        $cookie = null
        , $env = null
        , $files = null
        , $get = null
        , $post = null
        , $request = null
        , $server = null
        , $session = null
    ){
        foreach(self::$superglobals as $arg){
            $this->$arg = $$arg ?  new ArrayCollection($$arg) : null;
        }

    }
    
    
    
    static function create($optArray = array())
    {
        $optArray = (is_object($optArray) && get_class($optArray) == ArrayCollection::class)
            ? $optArray
            : new ArrayCollection($optArray);
        foreach (self::$superglobals as $global) {
            $$global = ($optArray->containsKey($global))
                ? $optArray->get($global)
                : null;
        }

        return new self(
            $cookie
            , $env
            , $files
            , $get
            , $post
            , $request
            , $server
            , $session
        );
    }

    static function mockUrl($url, $getParameters = [], $mergeGetGlobal = false){
        $get = $mergeGetGlobal ? array_merge($_GET,$getParameters) : $getParameters;

        $request = Request::create(array(
            'server'=>array_merge($_SERVER, array('REQUEST_METHOD'=>'GET','REQUEST_URI'=>$url)),
            'get'=>$get
        ));
        return $request;
    }
    

     function get():ArrayCollection_superglobal{
        return $this->get = $this->get ? $this->get : $this->get = new ArrayCollection_superglobal($_GET);
    }

     function post():ArrayCollection_superglobal{
        return $this->post ? $this->post : $this->post = new ArrayCollection_superglobal($_POST);
    }

    function session():ArrayCollection_superglobal{
        if ($this->session ){return $this->session;}
        //if ( in_array(PHP_SAPI, ['cli','cli-server'] )){return new ArrayCollection_superglobal();}
        session_start();
        $this->session = new ArrayCollection_superglobal( $_SESSION);
        return $this->session;
    }

     function server():ArrayCollection_superglobal{
        return $this->server ? $this->server : $this->server = new ArrayCollection_superglobal($_SERVER);
    }

     function env():ArrayCollection_superglobal{
        return $this->env ? $this->env : $this->env = new ArrayCollection_superglobal($_ENV);
    }

     function files():ArrayCollection_superglobal{
        return $this->files ? $this->files : $this->files = new ArrayCollection_superglobal($_FILES);
    }

     function cookie():ArrayCollection_superglobal{
        return $this->cookie ? $this->cookie : $this->cookie = new ArrayCollection_superglobal($_COOKIE);
    }

     function request():ArrayCollection_superglobal{
        return $this->request ? $this->request : $this->request = new ArrayCollection_superglobal($_REQUEST);
    }

    function url():UrlExtended{
        return new UrlExtended($this->server()->get('REQUEST_URI'));
    }

    function method():string{
        return is_string($this->server()->get('REQUEST_METHOD') )
            ? $this->server()->get('REQUEST_METHOD')
            : 'GET';
    }
}