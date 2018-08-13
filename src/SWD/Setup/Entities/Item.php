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

        $em = \App\Entities\EntityManagerFactory()->create();
        $tagList = $em->select('t')->distinct(true)->from(\App\Entities\Tag::class,'t')->getQuery()->getResult();
        $options = [];
        foreach($tagList as $tag){
            $opt = new FormSelectOption();
            $opt->setSelected($this->getTags()->contains($tag));
            $opt->setText($tag->getName());
            $opt->setValue($tag->getId());
            array_push($options,$opt);
        }
        $tags->setOptions($options);
        $tags->setTag('select');
        $tags->setType('multiple');
        
        $file = new FormField($getId('image'));
        $file->setType('input');
        $file->setValue($this->getImage()->getData());
        $file->setName('image[data]');
        $file->setType('file');
        $file->setLabel('Image');
        
        return [$file, $tags];
    }



}