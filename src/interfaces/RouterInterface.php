<?php namespace Nero\Interfaces;

use Symfony\Component\HttpFoundation\Request;


/**************************************************************
 * Interface for routing the url to controllers and methods.
 * Returns associative array containing information on which
 * controller to load, and which method to call.
 ************************************************************/
interface RouterInterface
{
    public function route(Request $request);
}
