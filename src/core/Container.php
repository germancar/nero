<?php

namespace Nero\Core;

/**
 * Object oriented wrapper around Pimple container.
 *
 */
class Container 
{
    /**
     * Pimple container handle.
     *
     * @var Pimple\Container
     */
    private $container = null;


    /**
     * Constructor, injected with pimple instance.
     *
     * @param Pimple\Container $pimple
     * @return void
     */
    public function __construct(\Pimple\Container $pimple)
    {
	$this->container = $pimple;
    }


    /**
     * Bind a new service into the container.
     *
     * @param string $key
     * @param closure $func
     * @return void
     */
    public function bind($key, $func)
    {
	$this->container[$key] = $func;
    }


    /**
     * Same as bind, but calls to resolve return different instances of the class.
     *
     * @param string $key
     * @param Closure $func
     * @return void
     */
    public function instance($key, $func)
    {
	$this->container[$key] = $this->container->factory($func);
    }


    /**
     * Resolve a service from the container.
     *
     * @param string $key
     * @return mixed
     */
    public function resolve($key)
    {
	return $this->container[$key];
    }
    
}
