<?php

namespace Nero\Bootstrap;

class StartSession
{
    /**
     * Just start a session in boot method.
     *
     * @return void
     */
    public function boot()
    {
        session_start();
    }


}
