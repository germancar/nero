<?php

namespace Nero\App\Controllers;


//simple controller that demonstrates different responses 
class IntroController extends BaseController
{
    public function welcome()
    {
        //lets greet the user with a view
        return view()->add('nero/welcome');
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
        return redirect('welcome');
    }
}
