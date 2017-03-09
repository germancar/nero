<?php

namespace Nero\Services\Proxies;

class Session extends Proxy
{
    public static function getAccessor()
    {
	return "Session";
    }
}
