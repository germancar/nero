<?php

namespace Nero\Services;


class Session extends Service
{
    /**
     * Install the service into the container.
     *
     * @return void
     */
    public static function install()
    {
	container()['Session'] = function($c){
	    return new Session;
	};
    }


    /**
     * Set a new session variable
     *
     * @param string $key 
     * @param string $value 
     * @return void
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }


    /**
     * Get a session variable
     *
     * @param string $key 
     * @return mixed
     */
    public function get($key)
    {
        if(isset($_SESSION[$key]))
            return $_SESSION[$key];
        else
            return false;
    }


    /**
     * Flash a variable to session
     *
     * @param string $key 
     * @param string $value 
     * @return void
     */
    public function flash($key, $value)
    {
        $_SESSION['flash'] = [$key => $value];
    }


    /**
     * Return flash variable
     *
     * @param string $key 
     * @return mixed
     */
    public function getFlash($key)
    {
        if(isset($_SESSION['flash'][$key]))
            return $_SESSION['flash'][$key];

        return false;
    }


    /**
     * Destroy a flash variable
     *
     * @return void
     */
    public function destroyFlash()
    {
        unset($_SESSION['flash']);
    }


    /**
     * Add a new error to the session
     *
     * @param mixed $value 
     * @return void
     */
    public function error($value)
    {
        $_SESSION['errors'][] = $value;
    }


    /**
     * Get all errors from the session
     *
     * @return mixed
     */
    public function getErrors()
    {
        if(isset($_SESSION['errors']))
            return $_SESSION['errors'];

        return false;
    }


    /**
     * Destroy errors
     *
     * @return void
     */
    public function destroyErrors()
    {
        unset($_SESSION['errors']);
    }


    /**
     * Set old input
     *
     * @param string $key 
     * @param string $value 
     * @return void
     */
    public function setOldInput($key, $value)
    {
        $_SESSION['old'][$key] = $value;
    }


    /**
     * Get the old value
     *
     * @param string $key 
     * @return string
     */
    public function old($key)
    {
        if(isset($_SESSION['old'][$key]))
            return $_SESSION['old'][$key];

        return "";
    }


    /**
     * Destroy the old input from the session
     *
     * @return void
     */
    public function destroyOldInput()
    {
        unset($_SESSION['old']);
    }
}
