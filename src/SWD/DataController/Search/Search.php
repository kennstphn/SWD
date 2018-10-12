<?php

namespace SWD\DataController\Search;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use SWD\DataController\OrderingParser;
use SWD\DataController\PaginationParser;
use SWD\Request\Request_interface;

class Search
{
    protected $queryEntity;

    /**
     * @var SearchStatement[]
     */
    protected $statementCollection = array();

    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var EntityManager $em
     */
    protected $em;

    /**
     * @var \Doctrine\ORM\Query $query
     */
    protected $query;

    /**
     * @var ArrayCollection $get
     */
    protected $get;
    
    function __construct(EntityManager $em, $entityClass,$get)
    {
        $this->setEntityManager($em);
        $this->setEntityName($entityClass);
        $this->get = new ArrayCollection($get);
    }

    /**
     * @var OrderingParser $ordering;
     */
    protected $ordering;
    function setOrdering(OrderingParser $parser){
        $this->ordering = $parser;
    }

    /**
     * @param string | string[] $statementCollection
     * @return $this
     * @throws \Exception
     */
    function searchBy($statementCollection){
        if ( ! is_array($statementCollection)){$statementCollection = array($statementCollection);}

        foreach($statementCollection as $string){

            $statement = SearchStatement::createFromString($this->queryEntity, $string);
            if ( ! $statement){throw new \Exception('unable to initialize SearchStatement from string ('.$string.')',404);}

            array_push($this->statementCollection, $statement );
        }

        return $this;
    }
    
    /** 
     * @param string $fullEntityNamespace
     * @throws \Exception
     */
    function setEntityName($fullEntityNamespace){
        if ( ! class_exists($fullEntityNamespace)){throw new \Exception('class not found '.$fullEntityNamespace,500);}
        $this->queryEntity = $fullEntityNamespace;
    }
    
    function setEntityManager(EntityManager $em){$this->em = $em;}

    /**
     * @return array
     */
    function getResult(){

        return $this->getQuery()->getResult();
    }

    function getPaginatedResults($perPage, $page){
        $query = $this->getQuery();
        $first = ((int)$page - 1 >= 0) ? ((int)$page - 1) * $perPage : 0;

        $paginator = new Paginator($query->setMaxResults((int)$perPage)->setFirstResult($first),false);
        
        $results = array();
        foreach($paginator as $result ){
            array_push($results, $result);
        }
        return $results;

    }

    function getPaginator(Request_interface $request,$defaults=['perPage'=>10,'page'=>1]){
        $pagination = new PaginationParser($this->getQuery(),$request,$defaults);

        return $pagination;
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    protected function getQuery(){
        if ($this->query){return $this->query;}

        $joins = new SearchJoins();

        $qb = $this->getQueryBuilder();
        foreach($this->statementCollection as $statement){
            $statement->updateQueryBuilderLogic($qb, $joins);
        }
        $joins->addJoinsToQueryBuilder($qb);
        if ($this->ordering){
            $qb->orderBy($this->ordering->getSafeOrderBy('e'),$this->ordering->getOrderDirection());
        }
        $this->query = $qb->getQuery();
        return $this->query;
    }


    /**
     * @return string
     */
    function getDQL(){
        return $this->getQuery()->getDQL();
    }

    /**
     * @return array
     */
    function getParameters(){
        $array = array();
        foreach($this->queryBuilder->getParameters() as $param){
            array_push($array, array(
                'name'=>$param->getName()
                ,'value'=>$param->getValue()
                ,'type'=>$param->getType()
            ));
        }
        return $array;
    }
    
    
    protected function initQueryBuilder(){
        if (is_null($this->em)){throw new \Exception('missing "setEntityManager" call', 500);}
        $this->queryBuilder = $this->em->createQueryBuilder()->select('e')->distinct(true)->from($this->queryEntity,'e');
        if($this->get->get('orderBy') && in_array($this->get->get('orderBy'),$this->em->getClassMetadata($this->queryEntity)->getFieldNames())){
            $sort = ($this->get->containsKey('desc')) ? 'DESC' : 'ASC';
            $this->queryBuilder->orderBy('e.'.$this->get->get('orderBy'),$sort);
        }
    }
    
    protected function getQueryBuilder(){
        if (is_null($this->queryBuilder)){$this->initQueryBuilder();}
        return $this->queryBuilder;
    }

    function getCount(){
        $qb = $this->em->createQueryBuilder()->select('count(e.id)')->from($this->queryEntity,'e');

        $joins=new SearchJoins();

        foreach($this->statementCollection as $statement){
            $statement->updateQueryBuilderLogic($qb, $joins);
        }
        $joins->addJoinsToQueryBuilder($qb);
        return $qb->getQuery()->getSingleScalarResult();
    }
}