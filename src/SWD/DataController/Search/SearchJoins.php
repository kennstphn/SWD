<?php
namespace SWD\DataController\Search;


use Doctrine\ORM\QueryBuilder;

class SearchJoins
{

    protected $joinList = array();
    function addJoin($parentWithProp, $child){
        $this->joinList[$parentWithProp] = $child;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    function addJoinsToQueryBuilder($queryBuilder){
        foreach($this->joinList as $join => $alias){
            if (count(explode('.', $join)) === 1){$join = 'e.'.$join;}

            $queryBuilder->join($join,$alias);
        }
    }
}