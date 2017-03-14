<?php

namespace Nero\Services;

class Logger extends Service
{
    public static function install()
    {
	container()->bind("Logger", function($c){
	    //Create the logger
	    $logger = new \Monolog\Logger('logger');
	    
	    //Add some handlers
	    $logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__.'/../app.log', \Monolog\Logger::INFO));

	    return $logger;
	});
    }
}
