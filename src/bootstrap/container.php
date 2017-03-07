<?php

use Pimple\Container;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\HttpFoundation\Request;

//Create a new instance of the container to handle the services(classes needed in the project)
$container = new Container();


//Bind all the services that we need, you can add your own bindings here as you wish
$container["RouterInterface"] = function($c){
    return new Nero\Core\Routing\LaravelRouter;
};


$container["DispatcherInterface"] = function($c){
    return new Nero\Core\Routing\Dispatcher;
};


$container['App'] = function($c){
    return new Nero\Core\App($c['RouterInterface'], $c['DispatcherInterface']);
};


$container['Request'] = function($c){
    return Request::createFromGlobals();
};


$container['Dispatcher'] = function($c){
    return new Nero\Core\Routing\Dispatcher;
};


$container['Auth'] = function($c){
    return new Nero\Services\Auth;
};


$container['Session'] = function($c){
    return new Nero\Services\Session;
};


$container['TwigLoader'] = function($c){
    return new Twig_Loader_Filesystem('../src/app/views');
};


$container['Twig'] = function($c){
    $twig = new Twig_Environment($c['TwigLoader'], [
	'debug' => true
    ]);
    $twig->addExtension(new Twig_Extension_Debug());
    
    return $twig;
};


$container['Logger'] = function($c){
    // Create the logger
    $logger = new Logger('logger');
 
    // Now add some handlers
    $logger->pushHandler(new StreamHandler(__DIR__.'/../app.log', Logger::INFO));

    return $logger;
};


//return the container so that it can be used
return $container;
