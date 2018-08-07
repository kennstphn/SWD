<?php
namespace SWD\Modules\AppConfiguration;


interface ConfiguredClass_interface
{
    static function setAppConfiguration(Configuration_interface $configuration);
}