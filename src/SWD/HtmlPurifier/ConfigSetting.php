<?php
namespace SWD\HtmlPurifier;
class ConfigSetting
{
    public $key,$value,$a;

    /**
     * Sets a value to configuration.
     *
     * @param string $key key
     * @param mixed $value value
     * @param mixed $a
     */
    static function create($key, $value, $a = null){
        $me = new self();
        $me->key=$key;
        $me->value = $value;
        $me->a = $a;
        return $me;
    }
}