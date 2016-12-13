<?php

namespace Nero\Core\Routing;

use Nero\Interfaces\RouterInterface;
use Symfony\Component\HttpFoundation\Request;


/********************************************************************
 * Router inspired by the Laravel routing implementation.
 * You register your routes in a separate file and the router
 * does the loading and parsing of it for use by the dispatcher.
 * This is done by assigning to every route a regEx that is used
 * to capture segments of the url to be used as arguments for
 * controller method. Each route regEx is matched against a
 * requested url and if there is a match that route is parsed into
 * the router response(which is used by the dispatcher)... 
 ********************************************************************/
class LaravelRouter implements RouterInterface
{
    /**
     * @var Holds all registered routes
     *
     */
    private $routes = [];


    /**
     * Register a route that you want to respond to in your app
     *
     * @param string $method 
     * @param string $url
     * @param string $handler
     * @return void
     */
    public function register($method, $url, $handler)
    {
        //lets setup a route
        $route = new Route;
        $route->method = $method;
        $route->url = $this->sanitizeURL($url);
        $route->handler = $handler;
        $route->patternRegEx = $this->generateRegExPattern($route->url);
        
        //lets add the route to the collection
        $this->routes[] = $route;

        //return route to allow chaining of request filters
        return $route;
    }


    public function has($url)
    {
	foreach ($this->routes as $route){
	    if ($route->url === $url)
		return true;
	}

	return false;
    }


    /**
     * Main method for routing a request
     *
     * @param Request $request 
     * @return assoc array
     */
    public function route(Request $request)
    {
        //lets load in the routes from the app/routes file
        $this->loadRoutes($this);

        //route will hold the matched route
        $route = $this->matchRoute($request);

        //return an assoc array containing info needed by the dispatcher, its that simple
        return [
            'controller' => $this->getController($route),
            'method'     => $this->getHandlingMethod($route),
            'params'     => $route->params,
            'filters'    => $route->filters
        ];
    }
  

    /**
     * Load the registered routes from a file
     *
     * @return void
     */
    private function loadRoutes($router)
    {
	//extract the router so that it can be accessed from the routes file
	$data['router'] = $router;
	extract($data);

	//load up the routes file(register routes)
        require_once __DIR__ . "/../../app/routes.php";
    }


    /**
     * Pattern match the requested route against the registered routes and return the matched route
     *
     * @param Request $request 
     * @return stdClass $route
     */
    private function matchRoute(Request $request)
    {
        //get the url from the current request
        $url = $this->sanitizeURL($request->getPathInfo());

        //match the url to the routes
        foreach($this->routes as $route){
            //matches will contain the captured segments of the url
            $matches = [];

            //get the request method or http verb(if its supplied by the form use that value instead)
            $requestMethod = strtoupper($request->getMethod());
            if($request->get('_method'))
                $requestMethod = strtoupper($request->get('_method'));

            //match the current route regEx with the supplied url and the request method(verb)
            if(preg_match($route->patternRegEx, $url, $matches) && strtoupper($route->method) === $requestMethod){
                $route->params = $this->extractParams($matches);
                return $route;
            }
        }

        //if there was no match throw a 404 not found exception(user haven't registered that route)
        throw new \Nero\Exceptions\HttpNotFoundException("Route not matched", 404);
    }


    /**
     * Extract the controller name from the route stdClass
     *
     * @param stdClass $route 
     * @return string
     */
    private function getController($route)
    {
        return explode('@',$route->handler)[0];
    }


    /**
     * Extract the method name from the route stdClass
     *
     * @param stdClass $route 
     * @return string
     */
    private function getHandlingMethod($route)
    {
        return explode('@',$route->handler)[1];
    }


    /**
     * Extract the parameters from the regEx matches array
     *
     * @param array $matches 
     * @return array
     */
    private function extractParams(array $matches)
    {
        //unset the 0 index that contains the whole matched string
        unset($matches[0]);

        //return a reindexed array of matches(params)
        return array_values($matches);
    }


    /**
     * Generate the regEx pattern from a url string
     *
     * @param string $url 
     * @return string(regEx)
     */
    private function generateRegExPattern($url)
    {
        //lets first explode all the segments of the url and process them one by one
        $explodedUrl = explode('/', $url);
        $result = [];

        foreach($explodedUrl as $part){
            //if part = {segment}
            if(preg_match("/^{[0-9a-zA-Z]+}$/", $part))
                //add the regex for capturing the segment
                $result[] = "([0-9a-zA-Z@.]+)";
            else
                //just append the part as it is (plain text)
                $result[] = $part;
        }

        //lets join the exploded parts back into a url regEx pattern
        $regexPattern = implode("/", $result);

        //return the full regEx pattern that coresponds to the given url /^url$/ 
        return '/^' . $this->escapeSlashes($regexPattern) . '$/';
    }


    /**
     * Escape slashes from a string, used for regEx manipulation
     *
     * @param string $string 
     * @return string
     */
    private function escapeSlashes($string)
    {
        return str_replace("/", "\/", $string);
    }


    /**
     * Sanitize url with php built-in filters
     *
     * @param string $url 
     * @return string
     */
    private function sanitizeURL($url)
    {
        return filter_var(trim($url, '/'), FILTER_SANITIZE_URL);
    }

}
