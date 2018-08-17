<?php
namespace SWD\HtmlPurifier\ConfigurationRepository;


use SWD\HtmlPurifier\ConfigSetting;
use SWD\HtmlPurifier\RepositoryInterface;

class Bootstrap_4_1 implements RepositoryInterface
{

    protected $revision = 1;
    
    function getRevision():string{
        return $this->revision;
    }
    
    function getDefinitionId():string{
        return self::class;
    }

    function getAdditionalSettings()
    {
        return [
            ConfigSetting::create('Attr.EnableID', true),
        ];
    }

    function overLoadDef(\HTMLPurifier_HTMLDefinition $def)
    {
        foreach($this->getHtmlTags() as $tag){
            foreach ($this->getBootstrapDataTags() as $attribute=> $allowedVals){
                $def->addAttribute($tag, $attribute,$allowedVals);
            }
        }
    }
    
    protected function getHtmlTags(){
        return [
            'a',
            'abbr',
            'acronym',
            'address',
            'applet',
            'area',
            'article',
            'aside',
            'audio',
            'b',
            'base',
            'basefont',
            'bdi',
            'bdo',
            'big',
            'blockquote',
            'body',
            'br',
            'button',
            'canvas',
            'caption',
            'center',
            'cite',
            'code',
            'col',
            'colgroup',
            'command',
            'datalist',
            'dd',
            'del',
            'details',
            'dfn',
            'dir',
            'div',
            'dl',
            'dt',
            'em',
            'embed',
            'fieldset',
            'figcaption',
            'figure',
            'font',
            'footer',
            'form',
            'frame',
            'frameset',
            'h1',
            'head',
            'header',
            'hgroup',
            'hr',
            'html',
            'i',
            'iframe',
            'img',
            'input',
            'ins'
        ];
    }

    function getBootstrapDataTags(){
        return [
            'data-'=>'CDATA',
            'data-anchor'=>'CDATA',
            'data-anchorjs'=>'CDATA',
            'data-animation'=>'CDATA',
            'data-api'=>'CDATA',
            'data-attributes'=>'CDATA',
            'data-backdrop'=>'CDATA',
            'data-background'=>'CDATA',
            'data-boundary'=>'CDATA',
            'data-clipboard'=>'CDATA',
            'data-container'=>'CDATA',
            'data-content'=>'CDATA',
            'data-descriptor'=>'CDATA',
            'data-dismiss'=>'CDATA',
            'data-display'=>'CDATA',
            'data-docs'=>'CDATA',
            'data-feather'=>'CDATA',
            'data-holder'=>'CDATA',
            'data-html'=>'CDATA',
            'data-interval'=>'CDATA',
            'data-keyboard'=>'CDATA',
            'data-margin'=>'CDATA',
            'data-method'=>'CDATA',
            'data-modal'=>'CDATA',
            'data-offset'=>'CDATA',
            'data-original'=>'CDATA',
            'data-padding'=>'CDATA',
            'data-parent'=>'CDATA',
            'data-placement'=>'CDATA',
            'data-reference'=>'CDATA',
            'data-ride'=>'CDATA',
            'data-siteurl'=>'CDATA',
            'data-slide'=>'CDATA',
            'data-spy'=>'CDATA',
            'data-src'=>'CDATA',
            'data-target'=>'CDATA',
            'data-title'=>'CDATA',
            'data-toggle'=>'CDATA',
            'data-trigger'=>'CDATA',
            'data-uri'=>'CDATA',
            'data-whatever'=>'CDATA',
            'data-wrap'=>'CDATA'

        ];
    }
    
    
    
}