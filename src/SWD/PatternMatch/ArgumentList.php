<?php
namespace SWD\PatternMatch;


class ArgumentList

{

    protected $urlPieces;
    protected $method;

    function __construct($urlPieces )
    {
        $this->urlPieces  = $urlPieces;
    }

    /**
     * @param string[] ...$arguments
     * @return bool
     */
    function matches(...$arguments){
        $arguments = func_get_args();
        return $this->__matches($arguments);
    }

    protected function __matches($checkList ,  $checkArrayCount = true){

        $urlPieces = $this->urlPieces;
        $c = count($checkList);

        if( $checkArrayCount && $c !== count($urlPieces)){return false;}

        for ($i=0;$i<$c;$i++){
            if ( ! $this->isMatching($checkList[$i], $urlPieces[$i])){return false;}
        }

        return true;
    }

    protected function isMatching($check, $checkagainst){
        if (
            ! $this->isAnything($check)
            && ! $this->is($check,$checkagainst)
            && ! $this->isNumeric($check, $checkagainst)
            && ! $this->isCustom($check, $checkagainst)
            && ! $this->isOneOf($check, $checkagainst)
            && ! $this->isRegexMatching($check, $checkagainst)
        ){
            return false;
        }
        return true;
    }

    function getPiece($int){
        if (array_key_exists($int,$this->urlPieces)){return $this->urlPieces[$int];}
        return null;
    }

    function startsWith(){
        return $this->__matches(func_get_args(),false);
    }
    

    protected function isAnything($arg){
        return $arg === '?' || $arg === '*';
    }

    protected function isNumeric($arg,$urlParam){

        if( $arg !== '0-9' && $arg !== -9 ){return false;} // -9 allows for 0-9 without quotes
        return is_numeric($urlParam);
    }

    protected function isCustom($callback, $urlParam){
        return is_callable($callback) ?  call_user_func($callback,$urlParam) : false;
    }

    protected function isOneOf($options,$urlParam){
        return is_array($options)? in_array($urlParam,$options): false;
    }

    protected function is($arg,$urlPiece){
        return $arg === $urlPiece;
    }

    protected function isRegex($arg){
        return ( strlen($arg) >= 2 &&  substr($arg,0,1) === '/' && substr($arg,-1) === '/');
    }

    protected $regexMatches = array();

    /**
     * @param $arg
     * @param $urlPiece
     * @return bool
     */
    protected function isRegexMatching($arg,$urlPiece){
        if ( ! $this->isRegex($arg)){return false;}
        preg_match($arg, $urlPiece, $output_array);

        if (count($output_array) == 0){return false;}
        if ($output_array[0] !== $urlPiece){return false;}//we must match the ENTIRE urlPiece

        array_push($this->regexMatches, array($arg,$output_array));
        return true;
    }

    /**
     * @param $arg
     * @return bool|int
     */
    public function getKeyOrFalse($arg){
        foreach($this->urlPieces as $key=> $piece){
            if ($this->isMatching($arg, $piece)){
                return $key;
            }
        }
        return false;
    }

}