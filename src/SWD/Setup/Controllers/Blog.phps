<?php
namespace App\Controllers;


use App\Entities\Blog\Article;
use App\Entities\Blog\Categories;
use App\Factories\EntityManagerFactory;
use SWD\Request\UrlParserFactory;
use Doctrine\ORM\NoResultException;
use SWD\DataController\ControlledUrl_interface;
use SWD\DataController\EntityController;
use SWD\PatternMatch\ArgumentList;
use SWD\Request\Request_interface;
use SWD\Response\Response_interface;

class Blog implements ControlledUrl_interface
{
    static function runController(Request_interface $request, Response_interface $response)
    {
        call_user_func(new self,$request,$response);
    }

    function __invoke(Request_interface $request, Response_interface $response)
    {
        $parser = UrlParserFactory::create($request);
        if ($parser->getControllerArguments() == []){
            $response->setResponseCode(200);
            $response->setTemplate('blog.twig');
            return;
        }

        $args = new ArgumentList($parser->getControllerArguments());

        //entity passthrough
        if ($args->startsWith('categories') || $args->startsWith('Categories')){
            $c = new EntityController(Categories::class, $request, $response);
            $c();
            return;
        }

        if ($args->startsWith('article') || $args->startsWith('Article')){
            $c = new EntityController(Article::class,$request,$response);
            $c();
            return;
        }

        if( $args->matches() ){
            $response->setResponseCode(200);
            $response->setTemplate('blog.twig');
            return ;

        }

        //Category!!!
        if($args->startsWith('category')){

            $cats = EntityManagerFactory::create()->createQueryBuilder()
                ->select('c')->from(Categories::class,'c')
                ->leftJoin('c.parent', 'parent');
            ;
            $urlPieces = array_slice($parser->getControllerArguments(),1 );

            //this allows for single level or single nested query. Refactor for 3+ or infinite
            switch (count($urlPieces)){
                case 1:
                    $cats->where('parent.id is null ')->andWhere('c.url = :url0')->setParameter('url0', $urlPieces[0]);
                    break;
                case 2:
                    $cats->leftJoin('parent.parent', 'parentParent');
                    $cats->where('parentParent.id is null')
                        ->andWhere('parent.url = :url0')->setParameter('url0', $urlPieces[0])
                        ->andWhere('c.url = :url1')->setParameter('url1', $urlPieces[1]);
                    break;
                case 3:
                    $cats->leftJoin('parentParent.parent', 'parentParentParent');
                    $cats->where('parentParentParent.id is null')
                        ->andWhere('parentParent.url = :url0')->setParameter('url0', $urlPieces[0])
                        ->andWhere('parent.url = :url1')->setParameter('url1', $urlPieces[1])
                        ->andWhere('c.url = :url2')->setParameter('url2', $urlPieces[2]);
                    break;
            }
            try{
                $category = $cats->getQuery()->getSingleResult();
            }catch (NoResultException $e){
                $response->setResponseCode(404);
                return;
            }

            $response->setData($category);
            $response->setTemplate('blog.category.twig');
            $response->setResponseCode(200);
            return;
        }

        $article = $this->getArticle($request);
        if ($article){
            $response->setResponseCode(200);
            $response->setData($article);
            $response->setTemplate('blog.article.twig');
            return;
        }

        $response->setResponseCode(404);
    }

    function getArticle(Request_interface $request_interface){
        $query = EntityManagerFactory::create()->createQueryBuilder()
            ->select('a')->from(Article::class,'a')
            ->where('a.loc = :url')->setParameter('url', $request_interface->url()->__toString())
            ->getQuery();
        try {
            return $query->getSingleResult();
        }catch (NoResultException $e){
            return null;
        }
    }
}