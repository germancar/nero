<?php

//Create a new instance of the container to handle the services(classes needed in the project)
$container = new Pimple\Container();

//Register all the services which are defined in the config file
foreach (config('services') as $service){
    $service::install();
}

//return the container so that it can be used
return $container;
