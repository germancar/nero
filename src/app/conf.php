<?php

/**
 * Return the array containing all the config information.
 *
 * @return assoc array
 */
return [
    //build target, used for error reporting
    'build' => 'development',

    //database config
    'db_hostname' => '',
    'db_username' => '',
    'db_password' => '',
    'db_name'     => '',

    //CodeIgniter router settings
    'default_controller' => 'Welcome',
    'default_method'     => 'index',

    //site base path config
    'base_path' => 'http://localhost/nero/public/',

    //Auth service config
    'auth_table' => 'users',
    'auth_key' => 'email',
    'auth_return_model' => 'User' 
];
