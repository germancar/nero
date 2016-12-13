<?php namespace Nero\Interfaces;


interface AuthInterface 
{
    public function register(array $data);
    public function login($key, $password);
    public function logout();
    public function check();
    public function user();
}
