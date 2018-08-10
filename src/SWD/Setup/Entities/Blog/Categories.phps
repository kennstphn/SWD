<?php
namespace App\Entities\Blog;
use App\Entities\Sitemap;
use App\Factories\EntityManagerFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

class Categories extends Sitemap
{
    static $catBase = '/blog/category';

    protected $url, $description, $priority;

    protected $parent, $categoryChain, $descendants, $children, $image, $articles;

    static function loadMetadata($m)
    {

        $b = new ClassMetadataBuilder($m);

        $b->setTable(str_replace(array('App\\Entities\\','\\'),array('','_') ,get_called_class() ));
        $b->addField('url', 'string');
        $b->addField('description', 'text');

        $b->addManyToOne('parent', get_called_class(),'children');
        $b->addOneToMany('children', get_called_class(),'parent');
        $b->addOneToMany('articles', Article::class, 'category');
        $b->createField('image', 'string')->nullable(true)->build();

        $b->addOwningManyToMany('categoryChain', Categories::class,'descendants');
        $b->addInverseManyToMany('descendants', Categories::class, 'categoryChain');

    }

    function __construct()
    {
        parent::__construct();
        $this->children = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->categoryChain = new ArrayCollection([$this]);
        $this->descendants = new ArrayCollection([$this]);
    }

    function getName()
    {
        return $this->getH1();
    }


    /**
     * @return string
     */
    public function getUrl()
    {
        if (is_null($this->url)){$this->setUrl($this->getH1());}
        return $this->url;
    }

    public function setH1($h1)
    {
        parent::setH1($h1);
        $this->getUrl();
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        if( ! trim($url)){return;}
        $this->url = preg_replace("/[^0-9a-zA-Z-]/", "$1", strtolower(str_replace([' ', '--'], '-', $url)));
        $this->updateLoc();
    }

    function updateLoc(){
        $this->setLoc(
            $this->getParent() ? $this->getParent()->getLoc() .'/'.$this->getUrl() : $this::$catBase.'/'.$this->getUrl()
        );
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
     * @return Categories
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Categories $parent
     * @param bool | EntityManager $propogateChanges
     * @throws \Exception
     */
    public function setParent($parent, $propogateChanges = true)
    {
        if ($parent === $this){
            throw new \Exception('Parent cannot be set to self. ');
        }

        $this->parent = $parent;

        if ( $propogateChanges === false){return;}
        switch ( is_object($propogateChanges) and get_class($propogateChanges) == EntityManager::class){
            case true:
                $em = $propogateChanges;
                break;
            default:
                $em = EntityManagerFactory::create();
                break;
        }

        //Propogate changes to category chain
        $catChain = new ArrayCollection($parent ? [$this, $parent] : [$this] );

        while($parent && $parent->getParent()){
            $catChain->add($parent->getParent());
            $parent = $parent->getParent();
        }

        $this->categoryChain = $catChain;


        //trigger updates on all children
        /** @var Categories $childCat */
        foreach($this->getChildren() as $childCat){
            $em->persist($childCat);
            $childCat->setParent($this);
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param ArrayCollection $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * @return ArrayCollection
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * @param ArrayCollection $articles
     */
    public function setArticles($articles)
    {
        $this->articles = $articles;
    }


    public function rebuildSitemapEntry(){
        $loc = $this::$catBase;
        $parent = $this->getParent();
        while ( ! is_null($parent)){
            $loc.= '/'.$parent->getUrl();
            $parent = $parent->getParent();
        }
        $loc.='/'.$this->getUrl();


        $this->loc = $loc;


    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = (string)$image;
    }

    /**
     * @param sorting = null | asc | desc
     * @return ArrayCollection
     */
    public function getCategoryChain($sorting = null)
    {
        switch ($sorting){
            case 'asc':
                $array = $this->categoryChain->toArray();
                usort($array, function(self $a, self $b){return $a->getParent()->getId() == $b->getId();});
                return $array;
                break;
            case 'desc':
                $array = $this->categoryChain->toArray();
                usort($array, function(self $a, self $b){return $b->getParent()->getId() == $a->getId();});
                return $array;
                break;
            default:

                return $this->categoryChain;
                break;
        }
    }

    /**
     * @param ArrayCollection $categoryChain
     */
    public function setCategoryChain($categoryChain)
    {
        $this->categoryChain = $categoryChain;
    }

    /**
     * @return ArrayCollection
     */
    public function getDescendants()
    {
        return $this->descendants;
    }

    /**
     * @param ArrayCollection $descendants
     */
    public function setDescendants($descendants)
    {
        $this->descendants = $descendants;
    }

    function __toString()
    {
        return $this->getUrl();
    }

    /**
     * @return $this
     * @deprecated
     */
    function getSitemap(){return $this;}

    function hasArticles(){
        if ($this->getArticles()->count()){return true;}
        foreach($this->getChildren() as $child){
            if($child->getArticles()->count()){return true;}
        }
        return false;
    }
}