<?php

namespace Nero\App\Controllers;


/**
 * Simple controller that demonstrates different responses 
 *
 */
class IntroController extends BaseController
{
    public function welcome()
    {
        //lets greet the user with a view
	return view('nero/welcome');
    }

    public function json()
    {
        //sample data
        $data['greeting'] = 'Welcome to Nero';

        //lets return the data in json format
        return json($data);
    }


    public function redirect()
    {
        //lets redirect the user to the welcome page
        return redirect();
    }


    public function text()
    {
        //lets just return string, which will be converted to response behind the scenes
        return "Welcome to Nero!";
    }

    public function user($id)
    {
	//showcase route parameters
	return json([
	    "msg" => "You request a user with an ID",
	    "ID" => $id
	]);
    }

}
