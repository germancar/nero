<?php

namespace Nero\Interfaces;

use Symfony\Component\HttpFoundation\Request;

/**
 * RouterInterface defines main route method which should match a received route with the registered ones.
 */
interface RouterInterface
{
    public function route(Request $request);
}
