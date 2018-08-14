<?php
namespace SWD\Modules\Navigation;
use SWD\Structures\Navigation;
use SWD\Website\Module;
use SWD\Website\Website;

class EntityNav extends Module
{
    function __invoke(string $hookName, Website $website)
    {
        $em = \App\Factories\EntityManagerFactory::create();
        $nav = new Navigation\Navigation();

        //$nav->search = new Navigation\Search;
        //$nav->search->submitText = 'Go!';
        //$nav->search->placeholder = 'Enter search term';
        //$nav->search->onSubmit = 'alert();return false;';
        $nav->fixed = true;
        foreach($em->getMetadataFactory()->getAllMetadata() as $m){
            $item = new Navigation\Collection();
            $urlStyleName = str_replace(['\\','//'],'/',$m->getName());
            $item->text = str_replace('App/Entities','',$urlStyleName);
            $item->href =$item->text;
            
            $nav->add($item);
            $view = new Navigation\Single();
            $view->text = 'List';
            $view->href = $item->href.'/?perPage=10';
            $item->add($view);
            
            $create = new Navigation\Single();
            $create->text = 'Create';
            $create->href= $item->href.'/create';
            $item->add($create);
        }
        $website->response()->addMeta('navigation', $nav);
    }


}