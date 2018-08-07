<?php

namespace SWD\Request;


use Doctrine\Common\Collections\ArrayCollection;

class RegexUrlParser implements UrlParser_interface
{
    /** @var  ArrayCollection */
    static protected $cache;
    
    protected $matches = array();
    function __construct(string $url)
    {
        $this->matches = self::parse($url);
    }

    protected static $regex = "/^((\/[a-zA-Z]{1}[a-zA-Z-_0-9]*)*)(\/$|\/([0-9]+$)|\/([0-9]+)\/([a-zA-Z-_]+)$|\/([a-zA-Z]{1}[a-zA-Z-_]*$)|\/((search)\/.*)$)/";


    static protected function parse($string){
        if (is_null(self::$cache)){self::$cache = new ArrayCollection();}
        if (self::$cache->containsKey($string)){
            return self::$cache->get($string);
        }
        $self = get_called_class();
        preg_match(
            $self::$regex
            ,$string
            , $matches);
        self::$cache->set($string, $matches);
        return self::$cache->get($string);
    }
    
    protected function getCount(){
        return count($this->matches);
    }
    
    function getUseCase(){
        return $this->getCount();
    }

    //todo remove
    function getClassForUrl(string $nameSpace){
        if ( ! $this->getCount()){return null;}
        $string = $this->matches[1];

        if ($string == ''){
            return class_exists($nameSpace.'\\Index') ? $nameSpace.'\\Index' : null;
        }

        if (substr($string,0,1)=== '/'){$string = substr($string,1);}

        $string = ucwords($string,'-');
        $string = $nameSpace.'\\'.str_replace('/', '\\', $string);
        if (class_exists($string)){return $string;}
        
        $classPieces = explode('\\', $string);


        for($i=count($classPieces); $i > 0; $i--){
            $class = implode('\\', array_slice($classPieces, 0, $i));
            if (class_exists($class)){return $class;}
        }
        return null;
    }

    function getControllerClass()
    {
        if ( ! $this->getCount()){ return null;}
        $string = $this->matches[1];
        if ( $string == '' ){
            return class_exists('App\\Controllers\\Index') ? 'App\\Controllers\\Index' : null;
        }
        $classPieces = explode('\\',$this->convertUrlToClassStyle($this->matches[1]));
        for($i = count($classPieces); $i > 0; $i--){
            $potentialClass = self::CONTROLLER_NAMESPACE.implode('\\', array_slice($classPieces, 0,$i));
            if (class_exists($potentialClass)){return $potentialClass;}
        }
        return null;
    }

    function getEntityClass()
    {
        if ( ! $this->getCount()){return null;}

        $potentialClass = $this->convertUrlToClassStyle($this->matches[1]);
        if (class_exists(self::ENTITY_NAMESPACE . $potentialClass)){
            return self::ENTITY_NAMESPACE.$potentialClass;
        }
        return null;
    }
    

    protected function convertUrlToClassStyle($string){
        $string = trim($string);
        $string = ucwords($string,'/');
        $string = ucwords($string,'-');
        return str_replace(array('/', '-'), array('\\', ''), trim($string,'/'));
        
    }

    function getId(){
        switch ($this->getCount()){
            case self::CASE_ID_RENDER:
                return (int)$this->matches[4];
                break;
            case self::CASE_ID_ACTION:
                return (int)$this->matches[5];
                break;
            default:
                throw new \Exception('ID is not avalable for this use case ('.$this->getCount().')');
        }
    }

    protected function getIdAction(){
        return $this->matches[6];
    }

    protected function getAppAction(){
        return $this->matches[7];
    }

    function getSearchString(){
        switch ($this->getCount()){
            case self::CASE_SEARCH:
                return substr($this->matches[8],7);
                break;
            case self::CASE_SEARCH_FOLDER:
                return substr($this->matches[10],7);
                break;
            default:
                throw new \Exception('search string is not available for non search actions');

        }
    }

    function getAction()
    {
        $controller = self::getEntityClass();
        if ( ! $controller){return $this->getControllerAction();}
        
        switch ($this->getUseCase()){
            case self::CASE_ID_RENDER:return 'render';break;
            case self::CASE_RENDER_ALL: return 'listAll';break;
            case self::CASE_ACTION_NO_ID: return $this->getAppAction();break;
            case self::CASE_ID_ACTION: return $this->getIdAction();break;
            case self::CASE_SEARCH: return 'search';break;
            case self::CASE_SEARCH_FOLDER: return 'search';break;
            default:throw new \Exception('unknown use case');break;
        }
    }

    function isFolder(){
        return in_array($this->getCount() ,[self::CASE_RENDER_ALL, self::CASE_SEARCH_FOLDER] );
    }
    
    
    function getControllerArguments():array{
        return $this->getPiecesAfterController();
    }
    
    protected function getControllerAction():string{
        $arg = array_slice($this->getPiecesAfterController(),0,1 );
        $arg = is_array($arg) && count($arg)===1 ? $arg[0] : $arg;
        return $arg;
    }
    
    protected function getPiecesAfterController():array{
        $pieces = explode('\\', str_replace(self::CONTROLLER_NAMESPACE, '', $this->getControllerClass()));
        $urlPieces = explode('/', trim($this->matches[0], '/'));
        return array_slice($urlPieces,count($pieces) );
    }

}