<?php

namespace Nero\Services;

use Nero\Core\Http\Response;
use Nero\Core\Reflection\Resolver;
use Nero\Interfaces\RouterInterface;
use Nero\Interfaces\DispatcherInterface;
use Symfony\Component\HttpFoundation\Request;


/**
 * High level representation of the application.
 *
 */
class App
{
    /**
     * Router implementation to be used.
     *
     * @var Nero\Interfaces\RouterInterface
     */
    private $router = null;


    /**
     * Dispatcher implementation.
     *
     * @var Nero\Interfaces\DispatcherInterface
     */
    private $dispatcher = null;


    /**
     * Array of bootstrapers to be booted up before we handle a request.
     */
    private $bootstrappers = [
        'Nero\Bootstrap\StartSession',
    ];


    /**
     * Array of terminators to be run after we handle the request.
     *
     */
    private $terminators = [
	'Nero\Terminators\LogRequest',
    ];

    
    /**
     * Install the service into the container.
     *
     * @return void
     */
    public static function install()
    {
	container()['App'] = function($c){
	    return new App($c['RouterInterface'], $c['DispatcherInterface']);
	};

	container()['Request'] = function($c){
	    return Request::createFromGlobals();
	};

    }


    /**
     * Constructor, injected with router and dispatcher  implementation.
     *
     * @param Nero\Interfaces\RouterInterface $router 
     * @param Nero\Interfaces\DispatcherInterface $dispatcher
     */
    public function __construct(RouterInterface $router, DispatcherInterface $dispatcher)
    {
        $this->router = $router;
        $this->dispatcher = $dispatcher;

        $this->bootstrap();
    }
    
    
    /**
     * High level method for handling a http request.
     *
     * @param Request $request
     * @return Nero\Core\Http\Response
     */
    public function handle(Request $request)
    {
        //resolve the requested url from the router
        $route = $this->router->route($request);

        //run the route filters
        $filterResponse = $this->runRouteFilters($route);

	//if some filter returns a HTTP response, send it directly back to the user, bypassing further processing
        if (is_subclass_of($filterResponse, Response::class))
            return $filterResponse;

        //pass the route to the dispatcher for invoking the requested method on the controller
        $response = $this->dispatcher->dispatchRoute($route);

        //lets return the response we got back
        return $response;
    }


    /**
     * Run the route filters.
     *
     * @param array $route 
     * @return mixed
     */
    private function runRouteFilters($route)
    {
        foreach ($route['filters'] as $filter){
            $filterName = "Nero\\App\\Filters\\" . $filter;

	    //setup a resolver so we can inject dependencies into the handle method of the filter
            $resolver = new Resolver($filterName, 'handle');

	    //invoke the handle method on the filter
            $result = $resolver->invoke();
            
            if (is_subclass_of($result, Response::class))
                return $result;
        }
		
        return true;
    }


    /**
     * Run bootstrappers.
     *
     * @return void
     */
    private function bootstrap()
    {
        foreach ($this->bootstrappers as $bootstrapper){
	    $resolver = new Resolver($bootstrapper, 'boot');
	    $resolver->invoke();
        }
    }


    /**
     * Run terminators.
     *
     * @return void
     */
    public function terminate()
    {
        foreach ($this->terminators as $terminator){
	    $resolver = new Resolver($terminator, 'terminate');
	    $resolver->invoke();
        }
    }
}
