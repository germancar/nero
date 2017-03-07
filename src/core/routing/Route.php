<?php

namespace Nero\Core\Routing;

/**
 * Router class, used for grouping info about the registered routes in our app
 *
 */
class Route
{
    public $method;
    public $url;
    public $handler;
    public $patternRegEx;
    public $filters = [];


    /**
     * Add request filters to the route.
     *
     * @return void
     */
    public function filters()
    {
        foreach (func_get_args() as $filter)
            $this->filters[] = $filter;
    }
    

}
