<?php
namespace App\Entities;

use SWD\Structures\FormField;
use SWD\Structures\FormSelectOption;

class Item extends \SWD\Entities\Item implements \SWD\Entities\DefinesAssociationFormFields_interface
{
    static function loadMetadata($m)
    {
        parent::__loadMetadata($m);
    }

    function getAssociationFormFields()
    {
        $getId = function ($fieldname){
            $id = str_replace('\\', '-', get_called_class()).'-'.$fieldname;
            $id.='-'.$this->getId();
            return $id;
        };

        $tags=new FormField($getId('tags'));
        $tags->setLabel('Tags');
        $tags->setName('tags');
        $tags->setReadOnly(false);

        $em = \App\Factories\EntityManagerFactory::create();
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
        
        $file = new FormField($getId('image'));
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
        
        return [$file, $tags];
    }



}