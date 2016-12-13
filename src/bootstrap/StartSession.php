<?php namespace Nero\Bootstrap;



class StartSession
{
    /**
     * Boot method
     *
     * @return void
     */
    public function boot()
    {
        session_start();
    }


}
