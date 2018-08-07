# SWD

Sherman Web Design

## Installation

composer install coming *soon*

## Big Idea

This is the codebase that shermanwebdesign.com
uses for new projects. May eventually support sub-packages
and modules, but for now releasing as an all-or-nothing.

The core concept is driven by a desire to do anything, and be
able to quickly respond to creative ideas without having to
massage the idea into a framework's "way of doing things".


### HTTP_
To support that, SWD conceptualizes out the following elements
of the http request.

#### Request
The Request represents the (real or mocked)
request from the client browser, and the response is a container
for all output from the application.
>While technically possible
to edit the variables within the Request, all implementations must
refrain from doing so. New Requests may be created (mocked) for
any necessary purposes

#### Response
The Response is a container for all output from the application,
and must be used for all output, including environmental state changes
(Cookies, Session data, output).

## QuickStart
The Website may be initialized with

    $website = new \SWD\Website\Website(
        \SWD\Request\Request::create(),
        new \SWD\Response\Response()
    );

Once initialised, simple run the website with

    $website->run();

## Controllers
By default, Controller initialization is handled by

    SWD\DataController\DataControllerFactory

and may be overriden via

    $website->setDataControllerFactory($yourFactoryClass);

prior to `$website->run();`

The default DataController uses the url parsing found in `SWD\Request\UrlParser`.
If you don't want to override the DataControllerFactory, and just adjust the url parsing,
create a `\App\Factories\UrlParserFactory` which has a `create()` method returning
an instantiated `SWD\Request\UrlParser_interface`. This will automatically
be loaded instead of the default UrlParser for any calls to `SWD\Request\UrlParserFactory::create();`


## Modules
Modules may be a callable function, invokable class, or a string
string classname referencing an invokeable class.

### Loading a module
Prior to the `$website->run();` call, load the module via one of the following methods;

#### Closure
    $website->addModule( $website::FOO_BAR_ETC, function($hookname, \SWD\Website\Website $website){
        //your code here
    } );

#### Instantiated Class
    $website->addModule( $website::FOO_BAR_ETC, $invokeableObject );

#### ClassName
    $website->addModule( $website::FOO_BAR_ETC, My\Class\Name::class);

### Module callback cycle
The string classname will be constructed with `SWD\Request\Request_interface` and `SWD\Response\Response_interface` parameters.

    $module = new $moduleClass( $request, $response);

The callable (closure, instantiated invokeable class, etc) will be invoked with a string $hookname and the instantiated `SWD\Website\Website` object.

    $module( $hookName, $website );

## CONTROLLERS vs MODULES
While Modules may be stacked, there should only
ever be one DataController hit for the request (unless that controller invokes a subordinate).

While this means DataControllers are technically less powerful, the expectation of SWD is that every response will
have a single data value (array of like objects, single object) which is published by the DataController.
The meta object is intended for modular output, ideally referenced by a published key, or a dotClass name for the module.

If this expectation is followed, debugging should always be a modular affair, and the callstack for any given bug
should be minimal and mockable.
