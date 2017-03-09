<?php

/*****************************************************************************
 * This is where you register the routes that you want to respond to.
 * It's really easy, supply the http method(verb), url, and controller@method.
 ****************************************************************************/
   
use Nero\Services\Proxies\Router;

//simple routes demonstrate different possible responses(views, json, redirects and simple text)
Router::register('get', '/', 'IntroController@welcome');
Router::register('get', '/json', 'IntroController@json');
Router::register('get', '/redirect', 'IntroController@redirect');
Router::register('get', '/text', 'IntroController@text');



