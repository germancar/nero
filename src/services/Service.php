<?php

namespace Nero\Services;

/**
 * Services are classes used in the framework, you can add your own as you please, just implement the install method.
 *
 */
abstract class Service 
{
    /**
     * Each service needs to implement the install method to register itself with the container.
     *
     * @return void
     */
    abstract public static function install();
}
