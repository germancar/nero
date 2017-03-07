<?php

namespace Nero\Interfaces;

/**
 * Dispatcher should invoke the the method on the controller based on the route.
 *
 */
interface DispatcherInterface
{
    public function dispatchRoute(array $route);
}
