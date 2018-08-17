<?php
namespace SWD\HtmlPurifier;


class ConfigurationLookupException extends \Exception
{
    public $lookupKey;
    function __construct($key, $code = 500, \Exception $previous = null)
    {
        $this->lookupKey = $key;
        parent::__construct("Unable to find configuration class for  {$key} ", $code, $previous);
        
    }
    
    

}