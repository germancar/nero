<?php namespace Nero\App\Filters;


use Nero\Services\Auth;


class AuthFilter
{
    /**
     * Simple filter to check if the user is logged in
     *
     * @param Auth $auth 
     * @return mixed
     */
    public function handle(Auth $auth)
    {
        if(!$auth->check())
            return redirect('login');

        return true;
    }
    
}
