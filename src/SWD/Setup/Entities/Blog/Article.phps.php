<?php
namespace App\Entities\Blog;


use App\Entities\Sitemap;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

class Article extends Sitemap
{
    static $articleBase = '/blog';

    function getName(){return $this->getSeoTitle();}

    protected $content, $published, $publishedDate, $slug;

    protected $hero, $category;

    protected $snippetFilters;

    static function loadMetadata($m)
    {

        $b = new ClassMetadataBuilder($m);

        $b->setTable(str_replace(array('App\\Entities\\','\\'),array('','_') ,get_called_class() ));

        $b->addField('content', 'text');
        $b->addField('published', 'boolean');
        $b->addField('publishedDate', 'datetime');
        $b->addField('slug', 'string');
        $b->addField('hero','string');

        $b->addManyToOne('category', Categories::class,'articles');

    }

    function setH1($h1){
        if (is_null($this->slug)){$this->setSlug($h1);}
        parent::setH1($h1);
    }

    function __construct()
    {
        parent::__construct();
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
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getPublished()
    {
        return (bool)$this->published;
    }

    /**
     * @param mixed $published
     */
    public function setPublished($published)
    {
        $this->published = (integer)$published;
    }

    /**
     * @return \DateTime
     */
    public function getPublishedDate()
    {
        return $this->publishedDate;
    }

    /**
     * @param \DateTime $publishedDate
     */
    public function setPublishedDate($publishedDate)
    {
        $this->publishedDate = is_string($publishedDate) ?new \DateTime($publishedDate):$publishedDate;
    }

    /**
     * @return string
     */
    public function getHero()
    {
        return $this->hero;
    }

    /**
     * @param string $hero
     */
    public function setHero($hero)
    {
        $this->hero = $hero;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->getH1();
    }

    public function getSeoTitle(){
        return is_null($this->seoTitle) ? $this->getH1() : $this->seoTitle;
    }

    /**
     * @return Categories
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Categories $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
        $this->updateLoc();
    }

    function getUrlPiece(){return $this->getSlug();}

    /**
     * @return mixed
     */
    public function getSlug()
    {
        if (is_null($this->slug)){$this->setSlug($this->getH1());}
        return $this->slug;
    }

    /**
     * @param mixed $slug
     */
    public function setSlug($slug)
    {
        $this->slug = preg_replace("/[^0-9a-zA-Z-]/", "$1", strtolower(str_replace([' ', '--'], '-', $slug)));
        $this->updateLoc();

    }

    public function updateLoc(){
        if ( !$this->getCategory()){return;}
        $this->setLoc(
            str_replace(Categories::$catBase, $this::$articleBase, $this->getCategory()->getLoc()) . '/'. $this->getSlug()
        );
    }

    /**
     * @return $this
     * @deprecated
     */
    function getSitemap(){
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getSnippetFilters()
    {
        return $this->snippetFilters;
    }

    /**
     * @param ArrayCollection $snippetFilters
     */
    public function setSnippetFilters($snippetFilters)
    {
        $this->snippetFilters = $snippetFilters;
    }





}