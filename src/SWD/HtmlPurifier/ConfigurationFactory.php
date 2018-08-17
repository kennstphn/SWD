<?php
namespace SWD\HtmlPurifier;
use SWD\HtmlPurifier\ConfigurationRepository\Bootstrap_4_1;

class ConfigurationFactory
{
    const BOOTSTRAP_4_1 = 'Bootstrap 4.1';
    const DEFAULT = self::BOOTSTRAP_4_1;

    static protected $repositories=[
        self::BOOTSTRAP_4_1=>Bootstrap_4_1::class
    ];

    /**
     * @param null $templateMode
     * @return \HTMLPurifier_Config
     * @throws ConfigurationLookupException
     */
    static function create($templateMode = ConfigurationFactory::DEFAULT){
        
        if ( ! array_key_exists($templateMode, self::$repositories)){
            throw new ConfigurationLookupException($templateMode);
        }

        $class = self::$repositories[$templateMode];

        $repo = self::assertCorrectInterface(
            new $class
        );

        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.DefinitionID', $repo->getDefinitionId());
        $config->set('HTML.DefinitionRev', $repo->getRevision());
        
        if($settingList = $repo->getAdditionalSettings()){
            foreach($settingList as $setting){
                $config->set($setting->key,$setting->value,$setting->a);
            }
        }
        
        if ($def = $config->maybeGetRawHTMLDefinition()) {
            $repo->overLoadDef($def);
        }
        return $config;
    }
    
    
    protected static function assertCorrectInterface(RepositoryInterface $ob){
        return $ob;
    }
    
    static function getConfigurations(){
        return array_keys(self::$repositories);
    }
    
}
    