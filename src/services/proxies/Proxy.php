<?php

namespace Nero\Services\Proxies;

/**
 * Proxies are static interfaces to instances in the container. Something like Facades in Laravel.
 *
 */
abstract class Proxy
{
    /**
     * Description
     *
     * @param type name
     * @return type
     */
    abstract public static function getAccessor();


    /**
     * Retrive the instance from the container based on accessor(key) and call the requested method on it.
     *
     * @param string $name
     * @param string $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
	$serviceKey = static::getAccessor();

	$instance = container($serviceKey);

	$reflectionMethod = new \ReflectionMethod($instance, $name);

	return $reflectionMethod->invokeArgs($instance, $arguments);
    }
}
