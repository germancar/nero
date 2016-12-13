<?php namespace Nero\Core\Routing;


use Nero\Core\Reflection\Resolver;
use Nero\Interfaces\DispatcherInterface;


/***************************************************************************
 * Dispatcher is responsible for dispatching the route
 * to the right controller and method and injecting it with dependecies.
 * This is done through the use of reflection API. Dispatcher
 * gets the route info(assoc array) as argument to the dispatchRoute
 * method. It then examines the methods signature to find out its
 * dependecies which are resolved from the container and injected into
 * the invocation of the method. It is assumed that the arguments of
 * the built-in types(which are resolved from the route) are listed first
 * in the method, followed by the class type ones which are resolved from
 * the IoC container.
 ****************************************************************************/
class Dispatcher implements DispatcherInterface
{
    private $method = "";
    private $resolver = null;
    private $urlParameters = [];


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
        $this->method = $route['method'];

        //contains parameters extracted from the url
        $this->urlParameters = $route['params'];

        //lets create the resolver which will do all the reflection work for us
        $this->resolver = new Resolver($controllerName, $this->method);

	//invoke the method on the controller
	$response = $this->resolver->invoke($this->urlParameters);

        //if its a simple string, wrap it into the response class
        if(is_string($response))
            return new \Nero\Core\Http\Response($response);


        return $response;
    }
}
