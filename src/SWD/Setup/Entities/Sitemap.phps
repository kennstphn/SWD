<?php
namespace App\Entities;
use App\Entities\Blog\Article;
use SWD\Entities\SeoPage;

class Sitemap extends \SWD\Entities\Sitemap
{
    use SeoPage;
    static function loadMetadata($m){
        parent::__loadMetadata($m);
        self::__loadSeoMetadata($m);
        $b = new ClassMetadataBuilder($m);

        $b->setJoinedTableInheritance();
        $b->setDiscriminatorColumn('disc','string');

        $b->addDiscriminatorMapClass('page', Page::class);
        $b->addDiscriminatorMapClass('sitemap', self::class);
        $b->addDiscriminatorMapClass('article',Article::class);
    }


}