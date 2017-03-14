<?php

namespace Nero\Services\Routing;

use Symfony\Component\HttpFoundation\Request;

/**
 * CodeIgniter routing implementation
 *
 */
class CodeIgniterRouter extends Service implements RouterInterface
{
    /**
     * Register the router into the container
     *
     * @return void
     */
    public static function install()
    {
	container()->bind("RouterInterface", function($c){
	    return new CodeIgniterRouter;
	});
    }

    /**
     * Process the requested url and return route info as assoc array
     *
     * @param Request $request
     * @return assoc array 
     */
    public function route(Request $request)
    {
        //lets work on this thing, set the defaults first
        $config = require  "../src/config/conf.php";

        $result = [
            "controller" => $config['default_controller'],
            "method" => $config['default_method'],
            "params" => []
        ];

        //get the safe exploded url
        $explodedURL = $this->explodePath($request);

        //resolve the url
        if (is_array($explodedURL)){
            //resolve the controller
            $result['controller'] = $explodedURL[0];
            unset($explodedURL[0]);

            //resolve the method
            if (isset($explodedURL[1]) && $explodedURL[1] != ""){
                $result['method'] = $explodedURL[1];
                unset($explodedURL[1]);
            }

            //resolve the remaining params
            if (isset($explodedURL[2]) && $explodedURL[2] != ""){
                $result['params'] = array_values($explodedURL);
            }
        }

        //lets return the route info
        return $result;
    }

    
    /**
     * Explode the request path for processing
     *
     * @return mixed
     */
    private function explodePath(Request $request)
    {
        if ($request->getPathInfo() == '/')
            return null;
            
   
        return explode('/', $this->sanitizeUrl($request->getPathInfo()));
    }


    /**
     * Sanitize the supplied url with php filters
     *
     * @param string $url
     * @return string
     */
    private function sanitizeUrl($url)
    {
        return filter_var(trim($url, '/'), FILTER_SANITIZE_URL);
    }
}
