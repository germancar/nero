<?php

namespace Nero\Interfaces;

/**
 * AuthInterface defines methods for authenticating users.
 *
 */
interface AuthInterface 
{
    public function register(array $data);
    public function login($key, $password);
    public function logout();
    public function check();
    public function user();
}
