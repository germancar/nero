<?php namespace Nero\Core\Routing;


use Nero\Core\Reflection\Resolver;
use Nero\Interfaces\DispatcherInterface;


/**
 * Dispatcher is responsible for invoking the correct method on the correct controller
 * and to return the response
 */
class Dispatcher implements DispatcherInterface
{
    /**
     * Reflection resolver
     *
     * @var Nero\Core\Reflection\Resolver
     */
    private $resolver = null;


    /**
     * Dispatch the route to the controller and inject it with dependencies
     *
     * @param assoc array $route 
     * @return Nero\Core\Http\Response
     */
    public function dispatchRoute(array $route)
    {
        //contains the full name of the controller to be used by the reflection api
        $controllerName = "Nero\\App\\Controllers\\". ucfirst($route['controller']);

        //contains the name of the method that should be invoked
        $method = $route['method'];

        //contains parameters extracted from the url
        $urlParameters = $route['params'];

        //lets create the resolver which will do all the reflection work for us
        $this->resolver = new Resolver($controllerName, $method);

	//var_dump($urlParameters);

	//invoke the method on the controller
	$response = $this->resolver->invoke($urlParameters);

        //if its a simple string, wrap it into the response class
        if (is_string($response))
            return new \Nero\Core\Http\Response($response);


        return $response;
    }
}
