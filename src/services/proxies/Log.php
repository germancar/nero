<?php

namespace Nero\Services\Proxies;

class Log extends Proxy
{
    public static function getAccessor()
    {
	return "Logger";
    }
}
