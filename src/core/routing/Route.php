<?php namespace Nero\Core\Routing;


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


    public function filters()
    {
        $filters = func_get_args();

        foreach($filters as $filter)
            $this->filters[] = $filter;
    }
    

}
