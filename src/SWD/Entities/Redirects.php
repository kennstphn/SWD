<?php
namespace SWD\Entities;


use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use SWD\Modules\Redirect\Redirect_interface;

abstract class Redirects extends EntityBase implements Redirect_interface
{
    
    protected $from, $to, $code;

    /**
     * Redirects constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->code = 301;
    }
    
    static function __loadMetadata($m)
    {
        parent::__loadMetadata($m); 
        $b = new ClassMetadataBuilder($m);
        $b->addField('from', 'string');
        $b->addField('to', 'string');
        $b->addField('code', 'integer');
        
    }

    function getName()
    {
        return $this->getFrom();
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }
    
    

}