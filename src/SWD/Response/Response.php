<?php
namespace SWD\Response;


use Doctrine\Common\Collections\ArrayCollection;

class Response implements Response_interface
{
    const MISSING_RENDERER = 'No renderCallable set ';
    protected $template;
    protected $data;
    protected $redirect;
    protected $cookies = [];
    protected $session = [];

    protected $otherTopLevel;
    
    protected $renderCallbackArray;

    protected $responseCode;
    protected $headerArray;
    
    /**
     * @var ArrayCollection|null
     */
    protected $errors;

    /**
     * @var ArrayCollection
     */
    protected $meta;


    function __construct($meta = null, $otherTopLevel = null, ResponseRenderer_interface $renderCallbackArray = null){
        $this->meta = $meta ? $meta : new ArrayCollection();
        $this->otherTopLevel = $otherTopLevel ? $otherTopLevel : new ArrayCollection();
        $this->renderCallbackArray = $renderCallbackArray ? $renderCallbackArray : new RendererArray();
        $this->errors = new ArrayCollection();
        $this->headerArray = new ArrayCollection();
    }

    function topLevelItems():ArrayCollection{
        return $this->otherTopLevel;
    }

    function getRenderCallbackArray():RendererArray
    {
        return $this->renderCallbackArray;
    }

    function addRenderCallback(ResponseRenderer_interface $renderer)
    {
        $this->renderCallbackArray->add($renderer);
    }
    
    function hasTemplate():bool
    {
        return $this->template ? true : false;
    }

    function setTemplate(string $template)
    {
        $this->template = $template;
    }

    function getTemplate():string
    {
        return $this->template;
    }

    function setResponseCode(int $int){
        if ($int >999 || $int < 100){
            throw new \Exception('Invalid response code, should be ###');
        }
        $this->responseCode = $int;
    }

    /**
     * @return int
     */
    public function getResponseCode():int
    {
        return $this->responseCode ? $this->responseCode : 200;
    }


    function hasData():bool
    {
        return ( ! is_null($this->data));
    }

    function hasErrors():bool
    {
        return $this->errors->count() > 0;
    }

    function setData($data)
    {
        $this->data = $data;
    }

    function addError(\Throwable $e)
    {
        if ( ! $this->errors){$this->errors = new ArrayCollection();}
        $this->errors->add($e);
        if ($e->getCode() !== 0){
            $this->responseCode = $e->getCode();
        }
    }

    function addMeta(string $key, $value)
    {
        $this->meta->set($key, $value);
    }

    function getData()
    {
        return $this->data;
    }

    /**
     * @return ArrayCollection
     */
    public function getMeta():ArrayCollection
    {
        return $this->meta;
    }

    /**
     * @return ArrayCollection|null
     */
    public function getErrors():ArrayCollection
    {
        return $this->errors;
    }

    function renderJson()
    {
        header('Content-type: application/json');
        if (is_null($this->responseCode)){
            $this->responseCode = $this->hasData() ? 200 : 404;
        }
        http_response_code($this->responseCode );
        ob_start();
        $json = json_encode($this);
        $warnings = ob_get_clean();
        if( strlen($warnings)){
            $object = json_decode($json);
            $object->meta->warnings = $warnings;
            $json = json_encode($object);
        }
        echo $json;
    }
    
    function __toString()
    {
        try{
            $output = (string)($this->data);
        }catch (\Throwable $e){
            $output = $e->getMessage();
        }
        return $output;
    }

    function jsonSerialize()
    {
        $r = (object)array();
        switch ($this->errors->count()){
            case true:
                $r->errors = array();
                /** @var \Exception $e */
                foreach($this->errors as $e){
                    array_push($r->errors,
                        array(
                            'details'=>$e->getMessage() //todo depreciate this
                            ,'code'=>(string)($e->getCode() !== 0 ? $e->getCode() : 500)
                            ,'detail'=>$e->getMessage()
                            ,'meta'=>[
                                'type'=>get_class($e)
                                ,'file'=>$e->getFile()
                                ,'line'=>$e->getLine()
                                ,'trace'=>$e->getTraceAsString()
                            ]
                        )
                    );
                }
                break;
            default:
                $r->data = $this->data;
                break;
        }

        if ($this->meta->count()){
            $r->meta = $this->meta->toArray();
        }

        if ( ! $this->meta->containsKey('status')) {
            if (200 <= $this->responseCode && $this->responseCode <= 299){
                $r->meta['status'] = 'success';
            }elseif (300 <= $this->responseCode && $this->responseCode <= 399) {
                $r->meta['status']= 'redirect';
            }elseif (400 <= $this->responseCode && $this->responseCode <= 599){
                $r->meta['status'] = 'failed';
            }
        }

        $r->responseCode = $this->responseCode ? $this->responseCode : 404;


        foreach($this->otherTopLevel as $key=>$val){
            $r->$key = $val;
        }
        return $r;
        
    }

    function isOk():bool{
        return (is_null($this->responseCode)||( $this->responseCode <= 299 && $this->responseCode >= 200 ));
    }
    
    function render(){
        if($this->hasErrors()){
            $this->responseCode = $this->responseCode > 299 ? $this->responseCode : 500;
        }
        if (
            is_null($this->responseCode) && $this->data
        ){$this->responseCode = 200;}

        $status = $this->renderCallbackArray->render($this);

        if ($status){return;}
        if ($this->headers()->containsKey('Content-Type')){
            foreach($this->headers() as $type=>$val){
                header($type.': '.$val);
                echo $this->data;
            }
        }else{
            $this->renderJson();
        }


        return;
    }

    function headers():ArrayCollection
    {
        return $this->headerArray;
    }


    function redirect(){
        $url = $this->redirect ? $this->redirect['url'] : '/';
        $statusCode = $this->redirect ? $this->redirect['code'] : 303;
        header('Location: '.$url,true,$statusCode);
    }

    function setRedirect(string $url, int $statusCode = 301){
        $this->responseCode = $statusCode;
        $this->redirect = array('url'=>$url,'code'=>$statusCode);
    }
    
    function setCookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null){
        array_push($this->cookies, func_get_args());
    }
    
    function sendCookies(){
        $this->addMeta('cookies', $this->cookies);
        foreach($this->cookies as $cookie){
            call_user_func_array('setcookie', $cookie);
        }
    }
    
    function setSessionVar(string $key,$data){
        $this->session[$key] = $data;
    }
    function getSession(){
        return $this->session;
    }
}