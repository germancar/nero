<?php

/*****************************************************************************
 * This is where you register the routes that you want to respond to.
 * It's really easy, supply the http method(verb), url, and controller@method.
 ****************************************************************************/
   

//simple routes demonstrate different possible responses(views, json, redirects and simple text)
$router->register('get', '/', 'IntroController@welcome');
$router->register('get', '/json', 'IntroController@json');
$router->register('get', '/redirect', 'IntroController@redirect');
$router->register('get', '/text', 'IntroController@text');

//segment capture
$router->register('get', '/user/{id}', 'IntroController@user');

//filter demo
$router->register('get', '/filter', 'IntroController@user')->filters('JsonFilter');;
