<?php
namespace SWD\HtmlPurifier;
interface RepositoryInterface
{
    /**
     * @return string
     */
    function getRevision():string;

    /**
     * @return string
     */
    function getDefinitionId():string;

    /**
     * @return ConfigSetting[]|null
     */
    function getAdditionalSettings();

    /**
     * @param \HTMLPurifier_HTMLDefinition $def
     */
    function overLoadDef(\HTMLPurifier_HTMLDefinition $def);
}