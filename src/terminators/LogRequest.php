<?php

namespace Nero\Terminators;

use Nero\Services\Proxies\Log;
use Symfony\Component\HttpFoundation\Request;

/**
 * For demo purposes, log the current request.
 *
 */
class LogRequest 
{
    public function terminate(Request $request)
    {
	Log::info("Request for " . $request->getPathInfo());
    }
}
