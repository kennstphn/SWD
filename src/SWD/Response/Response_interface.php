<?php
namespace SWD\Response;

use Doctrine\Common\Collections\ArrayCollection;

interface Response_interface extends \JsonSerializable
{
    const RESPONSE_TEMPLATE_KEY = 'responseTemplate';
    function hasData():bool;
    function hasErrors():bool;
    
    function setData($data);
    function addError(\Throwable $e);
    function addMeta(string $key, $value);
    
    function getData();
    function getErrors():ArrayCollection;
    function getMeta():ArrayCollection;
    
    function hasTemplate():bool;
    function setTemplate(string $template);
    function getTemplate():string;

    function getRenderCallbackArray():RendererArray;
    function addRenderCallback(ResponseRenderer_interface $renderer);
    function render();
    
    function getResponseCode():int;
    function setResponseCode(int $int);

    function topLevelItems():ArrayCollection;

    function headers():ArrayCollection;
    
    function setRedirect(string $url, int $statusCode = 301);
    function redirect();

    function setCookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null);
    function sendCookies();

    function isOk():bool;
    
    function setSessionVar(string $key, $value);
    function getSession();

    function renderJson();
    function __toString();
}