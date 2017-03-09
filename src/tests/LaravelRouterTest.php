<?php

namespace Nero\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class LaravelRouterTest extends TestCase
{
    public function testRegisterRoutes()
    {
	//register routes
	$router = new \Nero\Services\Routing\LaravelRouter;
	$router->register('get', '/route', 'TestController@route');

	//assert the router has the registered route
	$this->assertTrue($router->has('route'));
    }


    public function testRouteRequestToArray()
    {
	//setup the router
	$router = new \Nero\Services\Routing\LaravelRouter;
	$router->register('get', '/test', 'TestController@test');

	//create the simulated request 
	$request = Request::create('/test', 'GET');

	//get the response from the router
	$response = $router->route($request);

	//assert info
	$this->assertEquals('TestController', $response['controller']);
	$this->assertEquals('test', $response['method']);
    }


}
