<?php

use Nero\Core\Routing\LaravelRouter as Router;

/*****************************************************************************
 * This is where you register the routes that you want to respond to.
 * It's really easy, supply the http method(verb), url, and controller@method.
 ****************************************************************************/

//simple routes demonstrate different possible responses(views, json, redirects and simple text)
Router::register('get', '/', 'IntroController@welcome');
Router::register('get', '/json', 'IntroController@json');
Router::register('get', '/redirect', 'IntroController@redirect');
Router::register('get', '/text', 'IntroController@text');


//development routes
Router::register('get', '/dev', 'DevController@dev');
Router::register('get', '/dev/{id}', 'DevController@id')->filters('TestFilter');
Router::register('get', '/op', 'DvController@dev');
