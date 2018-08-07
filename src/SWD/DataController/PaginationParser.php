<?php
namespace SWD\DataController;


use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use SWD\Request\Request_interface;

class PaginationParser extends Paginator implements \JsonSerializable
{
    protected $request;
    protected $defaults;
    protected $total;

    /**
     * PaginationParser constructor.
     * @param Query|QueryBuilder $query
     * @param Request_interface $request
     * @param array $defaults
     */
    function __construct($query, Request_interface $request, $defaults = array(
        'perPage' => 10,
        'page' => 1,
    ), $fetchJoinCollection = true)
    {
        $this->defaults = array_merge(array(
            'perPage' => 10,
            'page' => 1,
        ), $defaults);
        $this->request = $request;

        $query->setMaxResults($this->getLimit());
        $query->setFirstResult($this->getOffset());

        parent::__construct($query,$fetchJoinCollection);

    }

    /**
     * @return int
     */
    function getLimit(){
        $limit = $this->request->get()->containsKey('limit') && $this->request->get()->isInteger('limit')
            ? $this->request->get()->getInteger('limit')
            : false;
        if ( ! $limit){
            $limit = $this->request->get()->containsKey('perPage') && $this->request->get()->isInteger('perPage')
                ? $this->request->get()->getInteger('perPage')
                : false;
        }
        if ( ! $limit){
            $limit = $this->defaults['perPage'];
        }
        return $limit;
    }

    /**
     * @return null | int
     */
    public function getTotal()
    {
        return $this->count();
    }

    /**
     * @return int
     */
    public function getPage(){

        $page = $this->request->get()->containsKey('offset') && $this->request->get()->isInteger('offset')
            ? ($this->request->get()->getInteger('offset') / $this->getLimit() ) + 1 // The integer offset divided by the limit + 1 gets us to the page
            : false;
        if ( ! $page){
            $page = $this->request->get()->containsKey('page') && $this->request->get()->isInteger('page')
                ? $this->request->get()->getInteger('page')
                : false;
        }
        if ( ! $page){
            $page = $this->defaults['page'];
        }
        return $page;

    }

    /**
     * @return int
     */
    public function getOffset(){
        if ($this->request->get()->containsKey('offset')){
            return $this->request->get()->getInteger('offset');
        }
        return ( $this->getPage() - 1 ) * $this->getPerPage();
    }

    /**
     * @return int
     */
    public function getPerPage(){
        return $this->getLimit();
    }
    
    
    function getPaginationArray(){
        $array =array(
            'perPage'=>$this->getPerPage(),
            'page'=>$this->getPage(),
            'total'=>$this->getTotal(),
            'offset'=>$this->getOffset()
        );
        return $array;
    }

    function jsonSerialize()
    {
        
        return array(
            'data'=>$this->toArray()
            ,'meta'=>array('pagination'=>$this->getPaginationArray())
        );
    }
    
    function toArray(){
        $array = array();
        foreach($this as $item){
            array_push($array, $item);
        }
        return $array;
    }
    
    static function isPaginationEnabled(Request_interface $request){
        if ($request->get()->containsKey('perPage')){return true;}
        if ($request->get()->containsKey('limit')){return true;}
        return false;
    }


}