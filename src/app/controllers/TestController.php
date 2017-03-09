<?php

namespace Nero\App\Controllers;

/**
 * Test Controller for unit testing the dispatcher.
 *
 */
class TestController extends BaseController
{
    public function dispatch()
    {
	return "Dispatcher test";
    }

    public function json()
    {
	return ['msg' => 'Test'];
    }

    public function text()
    {
	return "Testing";
    }
}
