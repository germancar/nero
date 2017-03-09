<?php

/**
 * Return the array containing all the config information.
 *
 * @return assoc array
 */
return [
    //Build target, used for error reporting
    'build' => 'development',

    //Database config
    'db_hostname' => '',
    'db_username' => '',
    'db_password' => '',
    'db_name'     => '',

    //CodeIgniter router settings
    'default_controller' => 'Welcome',
    'default_method'     => 'index',

    //Site base path config
    'base_path' => 'http://localhost/nero/public/',

    //Auth service config
    'auth_table' => 'users',
    'auth_key' => 'email',
    'auth_return_model' => 'User',

    //Registered services
    'services' => [
	'Nero\Services\App',
	'Nero\Services\Auth',
	'Nero\Services\Twig',
	'Nero\Services\Logger',
	'Nero\Services\Session',
	'Nero\Services\Dispatcher',
	'Nero\Services\Routing\LaravelRouter',
    ]
];
