<?php

/**
 * Return the array containing all the config information
 *
 * @return assoc array
 */
return [
    //build target, used for error reporting
    'build' => 'development',

    //database config
    'db_hostname' => 'localhost',
    'db_username' => 'root',
    'db_password' => 'street27',
    'db_name'     => 'dev',

    //default route config(used with CodeIgniterRouter)
    'default_controller' => 'Welcome',
    'default_method'     => 'index',

    //site base path config
    'base_path' => 'http://localhost/nero/public/',

    //auth config
    'auth_table' => 'users',
    'auth_key' => 'email',
    'auth_return_model' => 'User' 
];
