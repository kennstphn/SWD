<?php
namespace SWD\Entities;

use Doctrine\ORM\EntityManager;
use SWD\Structures\HelperTraits\DotClass;
use SWD\Structures\FormField;
use SWD\Structures\FormSelectOption;
use \App\Entities\File;

class Item extends EntityBase implements \IteratorAggregate, \SWD\Entities\DefinesAssociationFormFields_interface
{
    use Item_trait;
    use DotClass;


    protected $tags;
    protected $items;
    protected $parent;


    function __construct()
    {
        parent::__construct();
        $this->items = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
    }

    static function __loadMetadata($m)
    {
        parent::__loadMetadata($m);
        self::__loadItemMetadata($m);
        $b = new \Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder($m);
        $b->addOwningManyToMany('tags',\App\Entities\Tag::class,'items');
        $b->addManyToOne('parent', get_called_class(),'items');
        $b->addOneToMany('items', get_called_class(), 'parent' );

    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }


    public function getIterator()
    {
        return $this->getItems()->getIterator();
    }

    function __call($name, $arguments)
    {
        $collection = $this->getItems();
        if ( is_callable([$collection, $name])){
            return call_user_func_array([$this->getItems(),$name],$arguments );
        }
        return null;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|$this[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @return $this
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param $this $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }


    function getAssociationFormFields()
    {
        $em = \SWD\Factories\EntityManagerFactory::create();

        return [
            $this->getFileFormField($em),
            $this->getTagFormField($em),
            $this->getParentFormField($em),
        ];
    }

    protected function getParentFormField(EntityManager $em){
        $parent = new FormField($this->getFormFieldId('parent'));
        $parent->setTag('select');
        $parent->setName('parent');
        $parent->setLabel('Parent Item');

        $opts = [];
        foreach(
            $em->createQueryBuilder()->select('e.name')->addSelect('e.id')->from(get_called_class(),'e')->orderBy('e.name')->getQuery()->getResult()
            as
            $array
        ){
            $option = new FormSelectOption();
            $option->setText($array['name']);
            $option->setSelected($this->getParent() && $this->getParent()->getId() === $array['id'] );
            $option->setValue($array['id']);
            array_push($opts,$option);
        }

        $parent->setOptions($opts);
        return $parent;
    }

    protected function getFormFieldId(string $field){
        return $this->dotClass().'-'.$this->getId().'-'.$field;
    }

    protected function getTagFormField(EntityManager $em){
        $tags=new FormField(
            $this->getFormFieldId('tags')
        );
        $tags->setLabel('Tags');
        $tags->setName('tags');
        $tags->setReadOnly(false);

        $tagList = $em->createQueryBuilder()->select('t')->distinct(true)->from(\App\Entities\Tag::class,'t')->getQuery()->getResult();
        $options = [];
        foreach($tagList as $tag){
            $opt = new FormSelectOption();
            $opt->setSelected( $this->getTags()->contains($tag) );
            $opt->setText($tag->getName());
            $opt->setValue($tag->getId());
            array_push($options,$opt);
        }
        $tags->setOptions($options);
        $tags->setTag('select');
        $tags->setType('multiple');
        return $tags;
    }

    protected function getFileFormField(EntityManager $em){
        $file = new FormField(
            $this->getFormFieldId('file')
        );
        $file->setType('select');
        $file->setName('image');
        $file->setLabel('Image');
        $file->setTag('select');

        $options = [];
        $query = $em->createQueryBuilder();
        $query->select('e.name')
            ->groupBy('e.name')
            ->from(File::class,'e')
            ->orderBy('e.name')
        ;
        $query->addSelect('e.id')->addGroupBy('e.id')->addOrderBy('e.id');
        $query->addSelect('e.folder')->addGroupBy('e.folder')->addOrderBy('e.folder');

        foreach($query->getQuery()->getResult() as $f){
            $opt = new FormSelectOption();
            $opt->setSelected($this->getImage() && $this->getImage()->getId() == $f['id']);
            $opt->setValue($f['id']);
            $opt->setText($f['name']);
            $opt->setOptionGroup($f['folder']);
            array_push($options, $opt);
        }


        $file->setOptions($options);
        return $file;
    }









}