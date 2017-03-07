<?php

namespace Nero\App\Filters;

use Symfony\Component\HttpFoundation\Request;

/**
 * Test filter, just return json.
 *
 */
class JsonFilter 
{
    public function handle(Request $request)
    {
	return json([
	    'name' => 'Nero',
	    'url' => $request->getPathInfo()
	]);

    }
}
