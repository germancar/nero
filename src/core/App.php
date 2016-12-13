<?php namespace Nero\Core;

use Nero\Core\Reflection\Resolver;
use Nero\Interfaces\RouterInterface;
use Nero\Interfaces\DispatcherInterface;
use Symfony\Component\HttpFoundation\Request;


/************************************************************************************
 * App is the next bottleneck of the application after the front controller bootstrap.
 * It is a high level representation of the framework. It is responsible
 * for handling a http request and returning back the response to the user.
 * This is done by utilizing the router and a dispatcher. 
 ***********************************************************************************/
class App
{
    /**
     * RouterInterface implementation
     * DispatcherInterface implementation
     */
    private $router = null;
    private $container = null;
    private $dispatcher = null;


    /**
     * Array of bootstrapers to be booted up before we handle a request
     */
    private $bootstrappers = [
        'Nero\Bootstrap\StartSession',
    ];


    /**
     * Array of terminators to be run after we handle the request
     *
     */
    private $terminators = [

    ];


    /**
     * Constructor, injected with router and dispatcher  implementation
     *
     * @param RouterInterface $router 
     * @param DispatcherInterface $dispatcher
     */
    public function __construct(RouterInterface $router, DispatcherInterface $dispatcher)
    {
        //setup the dependencies
        $this->router = $router;
        $this->dispatcher = $dispatcher;

        //lets run bootstrappers
        $this->bootstrap();
    }
    
    
    /**
     * High level method for handling a http request
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

        if(is_subclass_of($filterResponse, 'Nero\\Core\\Http\\Response'))
            return $filterResponse;

        //pass the route to the dispatcher for invoking the controller and constructing the response
        $response = $this->dispatcher->dispatchRoute($route);

        //lets return the response we got back
        return $response;
    }


    /**
     * Run the route filters
     *
     * @param array $route 
     * @return mixed
     */
    private function runRouteFilters($route)
    {
        foreach($route['filters'] as $filter){
            $filterName = "Nero\\App\\Filters\\" . $filter;

            $resolver = new Resolver($filterName, 'handle');

            $result = $resolver->invoke();
            
            if(is_subclass_of($result, "Nero\\Core\\Http\Response"))
                return $result;
        }

        return true;
    }


    /**
     * Run bootstrapers
     *
     * @return void
     */
    private function bootstrap()
    {
        foreach($this->bootstrappers as $bootstrapper){
	    $resolver = new Resolver($bootstrapper, 'boot');
	    $resolver->invoke();
        }
    }


    /**
     * Run terminators
     *
     * @return void
     */
    public function terminate()
    {
        foreach($this->terminators as $terminator){
            $instance = new $terminator;
            $instance->run();
        }
    }
}
