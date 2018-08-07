<?php
namespace SWD\Request;


use SWD\PatternMatch\ArgumentList;

class UrlParser implements UrlParser_interface
{
    protected $url;
    protected static $cache = array(
        //url => $this
    );

    protected
        $controllerClass,
        $controllerArguments,
        $entityClass,
        $entityAction,
        $entityId,
        $folder,
        $searchString
    ;

    protected function __construct(Request_interface $request)
    {
        $this->url = $request->url();
        $this->parse();
    }

    /**
     * @param Request_interface $request
     * @return UrlParser
     */
    static function create(Request_interface $request){
        if (array_key_exists($request->url()->__toString(), self::$cache)){
            return self::$cache[$request->url()->__toString()];
        }
        $me = new self($request);
        return $me;
    }

    static function urlIze($string){
        return trim(strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $string)),'-');
    }
    
    static function classIze($urlPiece){
        return str_replace('-','',ucwords($urlPiece, '-'));
    }
    
    protected function classIzeList($array){
        $output = array();
        foreach($array as $item){array_push($output, $this::classIze($item));}
        return $output;
    }

    protected function parse(){
        $possiblePieces = explode('/', trim($this->url,'/'));


        for ( $i=0,$c=count($possiblePieces); $i < $c; $i++){
            $testControllerClass = 'App\\Controllers\\'.implode('\\', $this->classIzeList(array_slice($possiblePieces, 0,$i + 1)));
            $testControllerClass = substr($testControllerClass, -4) === '.php' ? substr($testControllerClass,0, -4) : $testControllerClass;
            if ( class_exists($testControllerClass) ){
                $this->controllerClass = $testControllerClass;
                $this->controllerArguments = array_slice($possiblePieces, $i + 1);
            }

            $testEntityClass = 'App\\Entities\\'.implode('\\', $this->classIzeList(array_slice($possiblePieces,0, $i + 1)));
            if ( class_exists($testEntityClass)){
                $this->entityClass = $testEntityClass;
                if ( ! array_key_exists($i + 1, $possiblePieces)){
                    $this->entityAction = 'listAll';
                }
                $matcher = new ArgumentList(array_slice($possiblePieces, $i + 1));
                if ($matcher->matches('0-9', 'edit') || $matcher->matches('0-9', 'delete')){
                    $this->entityAction = $possiblePieces[$i + 2];
                    $this->entityId = $possiblePieces[$i+1];
                }
                
                if ($matcher->matches('0-9')){
                    $this->entityAction = 'render';
                    $this->entityId = $possiblePieces[$i+1];
                }
                
                if ($matcher->matches('create')){
                    $this->entityAction = 'create';
                }

                if ($matcher->matches('group')){
                    $this->entityAction = 'group';
                }
                
                if ($matcher->startsWith('search')){
                    $this->entityAction = 'search';
                    $this->searchString = implode('/', array_slice($possiblePieces, $i + 2));
                }

            }
        }

        //$parser = new RegexUrlParser($this->url);
        //$e = $parser->getEntityClass();
        //if ($e){$this->entityClass = $e;}
        //if ($e){$this->entityAction = $parser->getAction();}

        self::$cache[$this->url->__toString()] = $this;
    }

    /**
     * @return mixed
     */
    public function getControllerClass()
    {
        return $this->controllerClass;
    }

    /**
     * @return mixed
     */
    public function getControllerArguments():array
    {
        return $this->controllerArguments ? $this->controllerArguments : array();
    }

    /**
     * @return mixed
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        if ($this->entityAction){return $this->entityAction;}
        if (count($this->controllerArguments) > 0){return $this->controllerArguments[0];}
        return null;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return (int)$this->entityId;
    }

    /**
     * @return mixed
     */
    public function isFolder()
    {
        return is_null($this->folder) ? $this->entityAction === 'listAll' : $this->folder;
    }

    /**
     * @return mixed
     */
    public function getSearchString()
    {
        return $this->searchString;
    }

    function getUseCase()
    {
        if ($this->entityId && $this->entityAction === 'render') {
            return $this::CASE_ID_RENDER;
        }
        if ($this->entityAction === 'listAll'){return $this::CASE_RENDER_ALL;}
        
        if ($this->entityAction === 'search'){return $this::CASE_SEARCH;}
        
        if ($this->entityAction === 'create' || $this->entityAction === 'group'){return $this::CASE_ACTION_NO_ID;}
        
        if (in_array($this->entityAction, ['edit', 'delete'])){return $this::CASE_ID_ACTION;}
        
        return $this::CASE_CONTROLLER_DEPENDANT;
    }

    function getClassForUrl(string $nameSpace)
    {
        if ($this->controllerClass){return $this->controllerClass;}
        if ($this->entityClass){return $this->entityClass;}
        return null;
    }

    function setControllerForUrl($url, $controllerClass){
        $this->controllerClass = $this->url->__toString() === $url ? $controllerClass : $this->controllerClass;
    }

}