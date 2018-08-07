<?php
namespace SWD\Entities;


use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

trait SeoPage
{
    protected $seoTitle;
    protected $metaDescription;
    protected $h1;
    protected $metaKeywords;
    protected $includeInSitemap = 1;

    static function __loadSeoMetadata($m){
        $b = new ClassMetadataBuilder($m);
        $b->createField('seoTitle', 'string')->nullable(true)->build();
        $b->createField('metaDescription', 'string')->nullable(true)->build();
        $b->createField('h1', 'string')->nullable(true)->build();
        $b->createField('metaKeywords', 'array')->nullable(true)->build();
        $b->createField('includeInSitemap', 'boolean')->nullable(true)->build();

    }

    /**
     * @return string
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * @param string $seoTitle
     */
    public function setSeoTitle($seoTitle)
    {
        $this->seoTitle = $seoTitle;
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @param string $MetaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * @return string
     */
    public function getH1()
    {
        return $this->h1;
    }

    /**
     * @param string $h1
     */
    public function setH1($h1)
    {
        $this->h1 = $h1;
    }

    /**
     * @return array
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * @param array $metaKeywords
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;
    }

    /**
     * @return bool
     */
    public function getIncludeInSitemap()
    {
        return (bool)$this->includeInSitemap;
    }

    /**
     * @param bool $includeInSitemap
     */
    public function setIncludeInSitemap($includeInSitemap)
    {
        $this->includeInSitemap = (bool)$includeInSitemap;
    }





}