<?php

namespace Nero\Tests;

use PHPUnit\Framework\TestCase;
use Nero\Core\Routing\LaravelRouter as Router;
use Symfony\Component\HttpFoundation\Request;

class LaravelRouterTest extends TestCase
{

    /**
     * Test registering new routes
     *
     */
    public function testRegisterRoutes()
    {
	//create a new router
	$router = new Router;

	//register routes
	$router->register('get', '/home', 'Intro@welcome');

	//assert the router has the registered route
	$this->assertTrue($router->has('home'));
    }


    /**
     * Test routing to controller and method
     *
     */
    public function testRouteRequestToArray()
    {
	//setup the router
	$router = new Router;
	$router->register('get', 'home', 'TestController@test');

	//create the simulated request 
	$request = Request::create('/home', 'GET');

	//get the response from the router
	$response = $router->route($request);

	//assert info
	$this->assertEquals('TestController', $response['controller']);
	$this->assertEquals('test', $response['method']);
    }
}
