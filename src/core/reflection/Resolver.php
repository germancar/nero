<?php

namespace Nero\Core\Reflection;

/**
 * Resolver class, used for invoking methods on objects.
 * It uses the reflection API to inject the methods with 
 * url parameters or type hinted class parameteres which are
 * resolved from the IoC container.
 */
class Resolver
{
    /**
     * Target object to be instantiated
     *
     * @var mixed
     */
    private $target;


    /**
     * Which method will be inspected and invoked
     *
     * @var string
     */
    private $reflectionMethod;


    /**
     * Constructor, inject with target class and method
     *
     * @param string $target 
     * @param string $method 
     * @return void
     */
    public function __construct($target, $method)
    {
        $this->target = $target;
        $this->reflectionMethod = new \ReflectionMethod($target, $method);
    }


    /**
     * Invoke the requested method with supplied non class parameters and class type hinted parameters 
     *
     * @param array $args
     * @return mixed
     */
    public function invoke(array $args = [])
    {
	//instantiate the object on which the method needs to be invoked
        $object = new $this->target;

	//check for errors
	if (count($args) != $this->nonClassParameterCount())
	    throw new \Exception("Expected parameters mismatch");

	//merge the route(url) parameters and class type hinted parameters
	$classParameters = $this->resolveObjectsFromContainer();
	$mergedParameters = array_merge($args, $classParameters);
        
	//invoke the method on the target and return response
        return $this->reflectionMethod->invokeArgs($object, $mergedParameters);
    }


    /**
     * Get the targeted method expected non class parameter count
     *
     * @return int
     */
    private function nonClassParameterCount()
    {
        $expectedParameters = $this->reflectionMethod->getParameters();

	return array_reduce($expectedParameters, function($carry, $parameter){
	    return ($parameter->getClass() == false) ? $carry+1 : $carry;
	}, 0);
    }


    /**
     * Get the array of objects resolved from the IoC container
     *
     * @return array
     */
    private function resolveObjectsFromContainer()
    {
	$objects = [];
        $expectedParameters = $this->reflectionMethod->getParameters();
	
        //we can resolve the expected classes from the IoC container
        foreach ($expectedParameters as $parameter){
            if ($parameter->getClass()){
                //extract the class name
                $className = nonNamespacedClassName($parameter->getClass()->name);

                //resolve it from the container
                if(container($className))
                    $objects[] = container($className);
                else
                    throw new \Exception("$className class does not exist!");
            }
        }

        return $objects;
    }

}
