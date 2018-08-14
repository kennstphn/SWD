<?php
namespace SWD\DataController;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use App\Factories\EntityManagerFactory;
use SWD\DataController\Search\Search;
use SWD\Modules\AccessControl\AccessControl;
use SWD\Request\Request_interface;
use SWD\Request\UrlParserFactory;
use SWD\Response\Response_interface;
use SWD\Structures\Doctrine\AssociationMapping;
use SWD\Structures\Doctrine\FieldMapping;
use SWD\Website\Controller_interface;

class EntityController implements Controller_interface
{
    protected $request, $response;
    
    protected $em;

    protected $entityClass;

    protected $actions;

    function __construct(string $entityClass, Request_interface $request, Response_interface $response, EntityManager $em = null)
    {
        $this->entityClass = $entityClass;
        $this->request = $request;
        $this->response = $response;
        $this->em = $em ? $em : EntityManagerFactory::create();
        $this->actions = new ArrayCollection();
    }

    function setAction($actionName, callable $function){
        $this->actions->add(array('actionName'=>$actionName,'callback'=>$function));
    }

    protected function attemptAction(string $actionName = null,$id = null){
        $action = $this->findAction($actionName,$this->request);
        $argument = new DynamicActionArgument($actionName, $this->request, $this->response, $this->em,$id);
        if ( ! $action ){
            $this->response->setResponseCode(404);
            return;
        }
        $action['callback']($argument);
    }

    protected function findAction($actionName, $request){
        return $this->actions->filter(function ($action)use($actionName,$request){
            if (is_string($action['actionName']) && $actionName = $action['actionName']){return true;}
            if ( is_callable($action['actionName']) && $action['actionName']($request)){return true;}
            return false;
        })->first();
    }
    

    function __invoke()
    {
        try{

            $parser = UrlParserFactory::create($this->request);

            switch ($parser->getUseCase()){
                case $parser::CASE_RENDER_ALL:
                    $this->listAll();
                    break;
                case $parser::CASE_ID_RENDER:
                    $this->render($parser->getId());
                    break;
                case $parser::CASE_ID_ACTION:
                    //e.g. $this->edit(1);
                    $actionCase = strtolower( $this->request->server()->get('REQUEST_METHOD') ) == 'post'
                        ? $parser->getAction().'Handler'
                        : $parser->getAction();
                    switch (is_callable(array($this,$actionCase))){
                        case true:
                            call_user_func( array( $this,$actionCase ), $parser->getId());
                            break;
                        default:
                            $this->attemptAction($actionCase, $parser->getId());
                            break;
                    }
                    break;
                case $parser::CASE_ACTION_NO_ID:
                    //e.g. $this->create();, $this->createHandler();
                    $actionCase = strtolower( $this->request->server()->get('REQUEST_METHOD') ) == 'post'
                        ? $parser->getAction().'Handler'
                        : $parser->getAction();
                    switch (is_callable(array($this,$actionCase))){
                        case true: call_user_func(array($this,$actionCase));
                            break;
                        default:
                            $this->attemptAction($actionCase);
                            break;
                    }
                    break;
                case $parser::CASE_SEARCH:
                case $parser::CASE_SEARCH_FOLDER:
                    $searchCase = strtolower( $this->request->server()->get('REQUEST_METHOD') ) == 'post' ? 'searchHandler' : 'search';
                    call_user_func(array($this,$searchCase), $parser->getSearchString() );
                    break;
                case $parser::CASE_CONTROLLER_DEPENDANT:
                    if ( ! $parser->getControllerClass()){break;}
                    $this->attemptAction($parser->getControllerArguments()[0]);
                    break;
                default:
                    throw new EntityControllerException('Use case not implemented');
                    break;
            }
        }catch (\Throwable $e){
            $this->response->addError($e);
        }
        if($this->response->hasData()){return;}
        $this->response->setResponseCode(
            $this->response->getResponseCode() ?? 404
        );
    }
    
    protected function listAll(){
        if ( ! $this->response->isOk()){return ;}
        
        $ordering = new OrderingParser($this->em,$this->entityClass,$this->request);
        $query = $this->em->createQueryBuilder()
            ->select('e')->from($this->entityClass, 'e')
            ->orderBy($ordering->getSafeOrderBy('e'),$ordering->getOrderDirection());

        switch(PaginationParser::isPaginationEnabled($this->request)){
            case true:
                $pagination = new PaginationParser($query, $this->request);
                $this->response->setData($pagination->toArray());
                $this->response->addMeta('pagination', $pagination->getPaginationArray());
                break;
            default:
                $this->response->setData($query->getQuery()->getResult());
                break;
        }
        $this->response->setResponseCode(200);

    }

    protected function render(int $id){
        if ( ! $this->response->isOk() ){return;}
        
        $entity = $this->em->find($this->entityClass, $id);
        $this->response->setData($entity);
        $this->response->setResponseCode(200);
    }

    protected function create(){
        if ( ! $this->response->isOk() ){return;}

        $class = $this->entityClass;
        $entityBlank = new $class;

        $this->response->setData($entityBlank);
        $this->response->setResponseCode(200);
    }

    protected function createHandler(){
        $class = $this->entityClass;
        $newEntity = new $class;
        
        $this->em->persist($newEntity);
        $this->updateEntityFromPostedData($newEntity);
        $this->em->flush();
        
        if($this->request->get()->get('redirect') == 'this'){
            $this->response->setRedirect(
                str_replace('/create', '/'.$newEntity->getId().'/edit',$this->request->url()->__toString() ),
                301
            );
        }else{
            $this->response->setResponseCode(200);
            $this->response->setData($newEntity);
        }
    }

    protected function edit(int $id){
        if ( ! $this->response->isOk() ){return;}

        $entity = $this->em->find($this->entityClass, $id);

        $this->response->setData($entity);
        $this->response->setResponseCode(200);
    }

    protected function editHandler(int $id)
    {
        if (!$this->response->isOk()) {
            return;
        }

        $entity = $this->em->find($this->entityClass, $id);

        if (!$entity) {
            $this->response->setResponseCode(400);
            return;
        }

        $this->em->persist($entity);
        $this->updateEntityFromPostedData($entity);
        $this->em->flush();
        
        $this->response->setResponseCode(200);
        $this->response->setData($entity);
        return;
    }

    protected function delete(int $id){
        if ( ! $this->response->isOk() ){return;}

        $entity = $this->em->find($this->entityClass, $id);
        if ( ! $entity){
            $this->response->setResponseCode(400);
            return;
        }

        $this->response->setData($entity);
        $this->response->setResponseCode(200);
    }
    

    protected function deleteHandler(int $id){
        if ( ! $this->response->isOk() ){return;}

        $entity = $this->em->find($this->entityClass, $id);

        if ( ! $entity){
            $this->response->setResponseCode(400);
            return;
        }
        $this->response->setData(json_decode(json_encode($entity)));
        
        $this->em->persist($entity);
        $this->em->remove($entity);
        $this->em->flush();
        
        $this->response->setResponseCode(200);
    }
    
    protected function search(string $searchString){
        $statements = explode('/', $searchString);
        for($i=0,$c=count($statements);$i<$c;$i++){
            $statements[$i] = rawurldecode($statements[$i]);
        }
        
        $search = new Search($this->em,$this->entityClass, $this->request->get()->toArray());

        $orderingParser = new OrderingParser($this->em,$this->entityClass,$this->request);
        $search->setOrdering($orderingParser);

        $search->searchBy($statements);

        /*
        $page = $this->request->get()->containsKey('page') 
            ? $this->request->get()->get('page') 
            : 1 ;
        $perPage = $this->request->get()->containsKey('perPage')
            ? $this->request->get()->get('perPage')
            : 10 ;
        $results = $search->getPaginatedResults($perPage, $page);
        */
        if (PaginationParser::isPaginationEnabled($this->request)){
            $paginator = $search->getPaginator($this->request);
            
            $this->response->setData($paginator->toArray());
            $this->response->addMeta('pagination', $paginator->getPaginationArray());
        } else{
            $this->response->setData($search->getResult());
        }
        $this->response->setResponseCode(200);
    }
    
    protected function searchHandler(string $searchString){
        $statements = explode('/', $searchString);
        for($i=0,$c=count($statements);$i<$c;$i++){
            $statements[$i] = rawurldecode($statements[$i]);
        }

        $search = new Search($this->em,$this->entityClass, $this->request->get()->toArray());
        $search->searchBy($statements);
        $idList = array();
        try{
            foreach($search->getResult() as $entity){
                $this->em->persist($entity);
                if(is_callable(array($entity, 'getId'))){array_push($idList, $entity->getId());}
                $this->updateEntityFromPostedData($entity);
            }
            
            $this->em->flush();
            
            $this->response->setResponseCode(200);
            $this->response->addMeta('status', 'success');
            $this->response->addMeta('count',$search->getCount());
            $this->response->addMeta('idList', $idList);
        }catch (\Exception $e){
            $this->response->setResponseCode(500);
            $this->response->addMeta('status', 'failed');
            $this->response->addMeta('count',$search->getCount() );
            $this->response->addError($e);
        }
    }

    /**
     * @param object $entity
     * @throws \Doctrine\ORM\Mapping\MappingException
     * @throws \Exception
     */
    protected function updateEntityFromPostedData($entity){
        if ( ! is_object($entity)){throw new \Exception('Expected object, received'.gettype($entity));}
        $metadata = $this->em->getClassMetadata($this->entityClass);
        $parser = new PostedDataParser($this->request);
        foreach($metadata->getFieldNames() as $fieldName){
            $mapping = new FieldMapping($metadata->getFieldMapping($fieldName));
            $parser->updateField($entity, $mapping);
        }
        
        foreach($metadata->getAssociationNames() as $associationName ){
            $mapping = new AssociationMapping($metadata->getAssociationMapping($associationName));
            $parser->updateAssociation($entity, $mapping, $this->em);
        }

        if( in_array( Tracking_interface::class, class_implements($entity)) && is_callable([$entity, 'setLastModified'])){
            /** @var Tracking_interface $entity */
            if ($uFactory = AccessControl::getUserFactory()){
                $entity->setChangedBy($uFactory::getCurrentUser($this->request));
            }
            $entity->setLastModified(new \DateTime());
        }
        
    }

    protected function group(){
        if ( ! $this->response->isOk()){return ;}

        if ( ! $this->request->get()->isArray('groupBy')){
            $this->response->setResponseCode(400);
            $this->response->addError(new \Exception('Malformed group request. Missing, or non-array groupBy url parameter'));
        }
        $m = $this->em->getClassMetadata($this->entityClass);
        $fieldNames = $m->getFieldNames();
        $assNames = $m->getAssociationNames();

        $attList = $this->request->get()->getAsArrayCollection('groupBy');
        $qb = $this->em->createQueryBuilder();

        $first = true;
        foreach($attList as $att){
            if ( ! in_array($att, $fieldNames) && ! in_array($att, $assNames)){
                $this->response->addError(new \Exception($att.' is not a field/association in '.$this->entityClass));
            }
            switch ($first){
                case true:
                    $qb->select('e.'.$att)->from($this->entityClass, 'e');
                    $qb->groupBy('e.'.$att);
                    break;
                default:
                    $qb->addSelect('e.'.$att);
                    $qb->addGroupBy('e.'.$att);
                    break;
            }
            $first = false;
        }

        $results = $qb->getQuery()->getResult();

        $this->response->setData($results);

    }

}