<?php
namespace SWD\DataController\Search;


use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;

class SearchStatement
{
    static protected $int = 1;

    const REGEX = '/^([\.a-zA-Z0-9_]+)([<>=!]+|,LIKE,|,like,|LIKE|like|IN|in|,IN,|,in,|STARTS_WITH|,STARTS_WITH,|,starts_with,|starts_with|ENDS_WITH|,ENDS_WITH,|ends_with|,ends_with,)(.*)/';

    const NESTED_REGEX = '^(select|SELECT|Select)=([0-9]+|[0-9]+)\.?([a-zA-Z0-9_]*)$';


    /**
     * @param $entityClass
     * @param $string
     * @return SearchStatement|bool
     */
    static function createFromString($entityClass, $string){
        preg_match(self::REGEX, $string, $matches);
        if (count($matches)!== 4){return false;}
        $propertyChain = $matches[1];
        $logic = strtoupper(trim($matches[2],',')); // takes 'like' and ',like,' --> LIKE
        $value = $matches[3];


        try{
            $statement = new SearchStatement($entityClass, $propertyChain, $logic, $value);
        } catch (\Exception $e){
            echo '<pre>';
            var_dump($e);
            return false;
        }
        return $statement;
    }
    static function getClassName(){return get_called_class();}

    protected $entityClass, $property,$logic,$value;
    protected $logicWhiteList = array(
        Comparison::EQ
        ,Comparison::GT
        ,Comparison::GTE
        ,Comparison::IN
        ,Comparison::IS
        ,Comparison::LT
        ,Comparison::LTE
        ,Comparison::NEQ
        ,'LIKE'
        ,Comparison::STARTS_WITH
        ,Comparison::ENDS_WITH
    );

    protected $i;

    function __construct($entityClass,$propertyChain, $logic, $value)
    {
        $this->i = self::$int++; //safe join/param i for concatenation

        $this->setEntityClass($entityClass); // ROOT entity class for querybuilder

        $this->parsePropertyChain($propertyChain); // needs to be parsed for final property

        $this->setLogic($logic); //validate against whitelist

        $this->setValue($value); //parameterize
    }

    /**
     * @param string $safeProperty
     */
    protected function setProperty($safeProperty){
        $this->property = $safeProperty;
    }

    protected $propertyWithParent;
    /**
     * @param string $pwithP
     */
    protected function setPropertyWithParent($pwithP){
        $this->propertyWithParent = $pwithP;
    }

    protected $joins = array();
    
    /**
     * @var EntityMap[]
     */
    protected $mapList=array();

    protected function parsePropertyChain($propertyChain){
        $chain = explode('.', trim($propertyChain));

        //start the mapList with the top Entity Class
        array_push($this->mapList, new EntityMap($this->entityClass));
        
        //stop one shy of the last item
        for($i=0,$c=count($chain);$i<$c - 1;$i++){

            //Anything before last prop MUST be an association
            if ( $i < $c - 2 && ! $this->mapList[$i]->hasAssociation($chain[$i])){
                throw new \Exception('Illegal property '.$chain[$i].' of '.$this->mapList[$i], 404);
            }
            
            // prep the next entityMap instance
            if ( $this->mapList[$i]->getMappingType($chain[$i]) === EntityMap::ASSOCIATION){ 
                $newEntityClass = $this->mapList[$i]->getMetadata()->getAssociationMapping($chain[$i])['targetEntity'];
                $this->mapList[$i+1] = new EntityMap($newEntityClass);
            }
            
            //set joins
            $key = ($i > 0) ? $chain[$i - 1] . '.' . $chain[$i] : $chain[$i];
            $this->joins[$key] = $chain[$i];
        }


        $last2 = array_slice($chain, -2);
        $parent_property = (count($last2) == 2) ? implode('.', $last2) : 'e.'.$last2[0];
        $this->setPropertyWithParent($parent_property);
        
        
        //Verify the final property 
        if ($this->mapList[$c - 1]->getMappingType(end($chain)) === false){
            throw new \Exception('Illegal property -- '.end($chain).' for parent '.$parent_property, 404);
        }
        
        $this->setProperty(end($chain));


    }




    /**
     * @param string $logic
     * @throws \Exception
     */
    protected function setLogic($logic)
    {
        $logic = trim($logic);
        $this->logic = strtoupper($logic);

        if ( ! in_array($this->logic,$this->logicWhiteList)){
            throw new \Exception('illegal logic operator '.$logic, 404);
        }
    }


    /**
     * @param string $value
     * @throws \Exception
     */
    protected function setValue($value)
    {
        $this->value = ($value === 'NULL'|| $value=== 'null') ?null:$value;

        if ( ! is_string($value)){throw new \Exception('illegal value set', 500);}

        if ($this->valueShouldBeArray()){
            $this->convertValueToArray();
        }
    }

    /**
     * @param string $entityClass
     * @throws \Exception
     */
    protected function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;
        if ( ! class_exists($entityClass)){throw new \Exception('Class does not exist', 404);}
    }

    
    protected function getWhereStatement($paramPlaceholder){
        $paramPlaceholder = ( $this->valueShouldBeArray() )
            ? '('.$paramPlaceholder.')'
            : $paramPlaceholder;

        $logic = (in_array($this->logic,array(Comparison::STARTS_WITH,Comparison::ENDS_WITH) )) ? 'LIKE': $this->logic;
        if(is_null($this->value)){$logic = 'IS NULL';$paramPlaceholder = '';}
        return "{$this->getPropertyWithParent()} {$logic} {$paramPlaceholder}";
    }

    protected function convertValueToArray(){
        $ESC = '__ESCAPE_COMMA__';
        $val = str_replace('\\,', $ESC, $this->value);
        $valArray = explode(',', $val);

        $this->value = array(); //re-init from string to array
        foreach($valArray as $v){
            array_push($this->value, str_replace($ESC, ',', $v)); //re-integrate escaped commas
        }
    }

    protected function valueShouldBeArray(){
        return in_array($this->logic, array(
            Comparison::NIN, Comparison::MEMBER_OF, Comparison::IN
        ));
    }

    /**
     * @param QueryBuilder $qb
     * @param SearchJoins $joins
     */
    function updateQueryBuilderLogic(QueryBuilder $qb, SearchJoins $joins){
        $whereStatement = $this->getWhereStatement($this->getParam(true));

        $val = ($this->logic === 'LIKE') ? '%'.$this->value.'%' : $this->value ;
        $val = ($this->logic === 'STARTS_WITH') ? $this->value.'%' : $val ;
        $val = ($this->logic === 'ENDS_WITH') ? '%'.$this->value: $val ;


        $qb->andWhere( $whereStatement);
        if( ! is_null($this->value)){$qb->setParameter($this->getParam(), $val);}

        foreach($this->joins as $parentWithProp => $prop){
            $joins->addJoin($parentWithProp,$prop);
        }
    }

    /**
     * @return string
     */
    protected function getPropertyWithParent(){
        return $this->propertyWithParent;
    }

    protected function getParam($incColon = false){
        $colon = ($incColon) ? ':':'';
        return $colon.$this->property.'__'.$this->i;
    }






}