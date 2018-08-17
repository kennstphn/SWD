<?php
namespace SWD\Entities;
use \App\Entities\File;

trait Item_trait
{
    protected $image;
    protected $title;
    protected $name;
    protected $description;
    protected $action;
    protected $href;
    protected $priority = 1;

    static function __loadItemMetadata($m)
    {
        $b = new \Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder($m);
        $b->addUniqueConstraint(['name'], 'unique_name');
        $b->addManyToOne('image',File::class);
        $b->createField('title','string')->nullable(true)->build();
        $b->addField('name', 'string');
        $b->createField('description','text')->nullable(true)->build();
        $b->createField('action','string')->nullable(true)->build();
        $b->createField('href','string')->nullable(true)->build();
        $b->createField('priority', 'integer')->nullable(true)->build();

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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
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
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    



}