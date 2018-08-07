<?php
namespace SWD\Request;


interface UrlParser_interface
{

    const CASE_ID_ACTION = 7;
    const CASE_ID_RENDER = 5;
    const CASE_RENDER_ALL = 4;
    const CASE_ACTION_NO_ID = 8;
    const CASE_SEARCH = 10;
    const CASE_SEARCH_FOLDER = 13;
    const CASE_CONTROLLER_DEPENDANT = 0;
    const ENTITY_NAMESPACE = 'App\\Entities\\';
    const CONTROLLER_NAMESPACE = 'App\\Controllers\\';


    function getUseCase();

    function getClassForUrl(string $nameSpace);
    
    function getEntityClass();
    
    function getControllerClass();
    
    function getControllerArguments():array;

    function getId();
    
    function getAction();

    function getSearchString();
        
    function isFolder();
    

}