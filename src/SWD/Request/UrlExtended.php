<?php
namespace SWD\Request;


use Closure;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;

class UrlExtended implements Collection, Selectable
{
    /** @var  string */
    protected $REQUEST_URI;
    /** @var  ArrayCollection_superglobal $data */
    protected $data;
    
    /** @var  bool $folder */
    protected $folder;
    
    function __construct($REQUEST_URI)
    {
        $qPos = strpos((string)$REQUEST_URI,'?');
        $this->REQUEST_URI = $qPos !== false ? substr($REQUEST_URI, 0, $qPos): $REQUEST_URI;
        $this->refreshDataFromURIString();
    }
    
    protected function refreshDataFromURIString(){
        $url = $this->REQUEST_URI;
        
        $qStart = strpos($url,'?');

        $url = substr($url,0,$qStart === false ? strlen($url): $qStart);
        $pieceArray =  array_slice(explode('/',$url),1);
        
        $this->folder = end($pieceArray) === '' ? true : false;
        
        $this->data = ($this->folder)
            ? new ArrayCollection_superglobal(array_slice($pieceArray, 0, -1)) 
            : new ArrayCollection_superglobal($pieceArray)
        ;

    }

    function get( $integer){
        if ( $integer > $this->count()){
            throw new \Exception('url does not have '.($integer + 1 ).' pieces');
        }
        return $this->data->get($integer);
    }

    function count(){
        $this->data->count();
    }

    function isFolder(){return $this->folder;}

    /**
     * @param $index
     * @return int
     * @throws TypeException
     */
    function getInteger($index){
        return $this->data->getInteger($index);
    }

    /**
     * @param $index
     * @return float
     * @throws TypeException
     */
    function getFloat($index){
        return $this->data->getFloat($index);
    }

    function toArray(){
        return $this->data->toArray();
    }

    public function add($element)
    {
        return $this->data->add($element);
    }

    public function clear()
    {
        return $this->data->clear();
    }

    public function contains($element)
    {
        $bool = $this->data->contains($element);
        if ($bool){return $bool;}
        $inStr = strpos($this->REQUEST_URI,$element);
        if ($inStr === false){return false;}
        return true;
    }

    public function isEmpty()
    {
        return $this->data->count() < 2;
    }

    /**
     * @param int|string $key
     * @throws \Exception
     */
    public function remove($key)
    {
        throw new \Exception('URL is read only once constructed');
    }

    /**
     * @param mixed $element
     * @throws \Exception
     */
    public function removeElement($element)
    {
        throw new \Exception('URL is read only once constructed');
    }

    
    public function containsKey($key)
    {
        return $this->data->containsKey($key);
    }

    public function getKeys()
    {
        return $this->data->getKeys();
    }

    public function getValues()
    {
        return $this->data->getValues();
    }

    /**
     * @param int|string $key
     * @param mixed $value
     * @throws \Exception
     */
    public function set($key, $value)
    {
        throw new \Exception('URL is read only once constructed');
    }

    public function first()
    {
        return $this->data->first();
    }

    public function last()
    {
        return $this->data->last();
    }

    public function key()
    {
        return $this->data->key();
    }

    public function current()
    {
        return $this->data->current();
    }

    public function next()
    {
        return $this->data->next();
    }

    public function exists(Closure $p)
    {
        return $this->data->exists($p);
    }

    public function filter(Closure $p)
    {
        return $this->data->filter($p);
    }

    public function forAll(Closure $p)
    {
        return $this->data->forAll($p);
    }

    public function map(Closure $func)
    {
        return $this->data->map($func);
    }

    public function partition(Closure $p)
    {
        return $this->data->partition($p);
    }

    public function indexOf($element)
    {
        if ( ! $this->contains($element)){return false;}
        $index =  $this->data->indexOf($element);
        if ($index){return $index;}

        $element = trim($element,'/');
        $pos = strpos($this->REQUEST_URI, $element);
        $slashPos = strpos($this->REQUEST_URI, '/',$pos);
        return $this->data->indexOf(substr($this->REQUEST_URI,$pos,$slashPos - $pos));

    }

    public function slice($offset, $length = null)
    {
        return $this->data->slice($offset,$length);
    }

    public function getIterator()
    {
        return $this->data->getIterator();
    }

    public function offsetExists($offset)
    {
        return $this->data->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->data->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->data->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->data->offsetUnset($offset);
    }

    public function matching(Criteria $criteria)
    {
        return $this->data->matching($criteria);
    }

    function __toString()
    {
        return $this->REQUEST_URI;
    }



}