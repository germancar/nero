<?php

//we are using Pimple for dependency injection and symfony http request class
use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;


//create the container to handle the services
$container = new Container();

//Let's add the services
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



//lets return the container 
return $container;
