<?php

namespace Nero\Tests;

use Nero\Services\Dispatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Nero\Services\Routing\LaravelRouter as Router;

class DispatcherTest extends TestCase
{
    public function testDispatchesRoute()
    {
	//assign
	$router = new Router;
	$dispatcher = new Dispatcher;
	$router->register('get', 'testdispatch', 'TestController@dispatch');

	//create the simulated request and dispatch it to the test controller
	$request = Request::create('/testdispatch', 'GET');
	$routeInfo = $router->route($request);
	$response = $dispatcher->dispatchRoute($routeInfo);

	//assert
	$this->assertInstanceOf('Nero\Core\Http\Response', $response);
    }


    public function testConvertResponseToJson()
    {
	//assign
	$router = new Router;
	$dispatcher = new Dispatcher;
	$router->register('get', 'testjson', 'TestController@json');

	//create the simulated request and dispatch it to the test controller
	$request = Request::create('/testjson', 'GET');
	$response = $dispatcher->dispatchRoute($router->route($request));
	$response->send();

	//assert
	$this->expectOutputString("{\"msg\":\"Test\"}");
    }


    public function testConvertResponseToMessage()
    {
	//assign
	$router = new Router;
	$dispatcher = new Dispatcher;
	$router->register('get', 'testtext', 'TestController@text');

	//create the simulated request and dispatch it to the test controller
	$request = Request::create('/testtext', 'GET');
	$response = $dispatcher->dispatchRoute($router->route($request));
	$response->send();

	//assert
	$this->expectOutputString("Testing");
    }


}
