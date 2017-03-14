<?php

namespace Nero\Exceptions;

class ExceptionManager 
{
    /**
     * Handle exception and return view to display stack trace.
     *
     * @param Exception $exception
     * @return Nero\Core\Http\ViewResponse
     */
    public static function handleException($exception)
    {
	//extract exception data
	$data['exception_name'] = get_class($exception);
	$data['exception_message'] = $exception->getMessage();
	$data['exception_line'] = $exception->getLine();
	$data['exception_file'] = $exception->getFile();

	if ($data['exception_name'] == "Nero\Exceptions\HttpNotFoundException" && !inDevelopment())
	    return view("nero/http404");

	//generate stack trace
        $traceString = $exception->getTraceAsString();
        $trace = explode('#', $traceString);
        unset($trace[0]);
	$data['trace'] = $trace;

	return view("nero/error")->with($data);
    }
}
