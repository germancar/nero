<?php

namespace Nero\Terminators;

use Symfony\Component\HttpFoundation\Request;

/**
 * For demo purposes, log the current request.
 *
 */
class LogRequest 
{
    public function terminate(Request $request)
    {
	container('Logger')->info("Request for " . $request->getPathInfo());
    }
}
