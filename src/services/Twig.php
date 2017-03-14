<?php

namespace Nero\Services;

class Twig extends Service
{
    /**
     * Install the service into the container.
     *
     * @return void
     */
    public static function install()
    {
	container()->bind("TwigLoader", function($c){
	    return new \Twig_Loader_Filesystem('../src/app/views');
	});

	container()->bind("Twig", function($c){
	    $twig = new \Twig_Environment($c['TwigLoader'], [
		'debug' => true
	    ]);
	    $twig->addExtension(new \Twig_Extension_Debug());
	    
	    return $twig;
	});
    }
}
