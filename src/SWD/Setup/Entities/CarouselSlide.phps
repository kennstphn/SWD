<?php
namespace App\Entities;
use SWD\Entities\EntityBase;

class CarouselSlide extends EntityBase
{
    
    protected $image;
    protected $align;
    protected $title;
    protected $content;
    protected $callToAction;
    protected $href;
    protected $tags =[];
    protected $priority = 1;

    static function loadMetadata($m)
    {
        parent::__loadMetadata($m);
        $b = new \Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder($m);
        $b->createManyToOne('image',File::class)->addJoinColumn('image_id','id',false)->build();

        $b->createField('align','string')->nullable(true)->build();

        $b->createField('title','string')->nullable(true)->build();
        $b->createField('content','text')->nullable(true)->build();
        $b->createField('callToAction','string')->nullable(true)->build();
        $b->createField('href','string')->nullable(true)->build();
        $b->addField('tags','array');
        $b->addField('priority','integer');

    }

    /**
     * @return File
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param File $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return mixed
     */
    public function getAlign()
    {
        return $this->align;
    }

    /**
     * @param mixed $align
     */
    public function setAlign($align)
    {
        $this->align = $align;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
    public function getCallToAction()
    {
        return $this->callToAction;
    }

    /**
     * @param mixed $callToAction
     */
    public function setCallToAction($callToAction)
    {
        $this->callToAction = $callToAction;
    }

    /**
     * @return mixed
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * @param mixed $href
     */
    public function setHref($href)
    {
        $this->href = $href;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    function getName()
    {
        return $this->getTitle() ?? $this->getId();
    }


}