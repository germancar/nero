<?php

namespace Nero\App\Controllers;

use Symfony\Component\HttpFoundation\Request;

/**
 * Simple controller that demonstrates different responses.
 *
 */
class IntroController extends BaseController
{
    //lets greet the user with a view
    public function welcome()
    {
	return view('nero.welcome');
    }


    //lets return the data in json format
    public function json()
    {
        $data['greeting'] = 'Welcome to Nero';
        return json($data);
    }


    //lets redirect the user to the welcome page
    public function redirect()
    {
        return redirect();
    }


    //lets just return string, which will be converted to response behind the scenes
    public function text()
    {
        return "Welcome to Nero!";
    }


    //demo route segment capture 
    public function resource($id, Request $request)
    {
	return json([
	    "response" => "Request for a resource with an id $id",
	    "url"      => $request->getPathInfo()
	]);
    }
}
