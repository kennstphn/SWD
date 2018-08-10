<?php
namespace App\Entities;


use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use SWD\Factories\HtmlPurifier;

class Page extends Sitemap
{

    protected $content, $published;

    static function loadMetadata($m)
    {
        $b = new ClassMetadataBuilder($m);
        $b->setTable(str_replace(array('App\\Entities\\','\\'),array('','_') ,get_called_class() ));
        $b->addField('content', 'text');
        $b->addField('published', 'boolean');

    }

    function __construct()
    {
        parent::__construct();
        $this->published = 1;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = HtmlPurifier::create()->purify($content);
    }

    /**
     * @return boolean
     */
    public function getPublished()
    {
        return (bool)$this->published;
    }

    /**
     * @param boolean $published
     */
    public function setPublished($published)
    {
        $this->published = (integer)$published;
    }

    function getName()
    {
        return $this->getSeoTitle();
    }

}