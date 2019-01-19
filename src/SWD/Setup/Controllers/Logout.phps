<?php
namespace App\Controllers;


use SWD\AppControllers\UrlController;
use SWD\Factories\UserFactory;

class Logout extends UrlController
{
    function __invoke()
    {
        UserFactory::removePersistantUser();
        $this->response->setRedirect('/',301);
    }


}