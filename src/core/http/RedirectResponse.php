<?php

namespace Nero\Core\Http;

/**
 * Response used for redirecting user
 *
 */
class RedirectResponse extends Response
{
    /**
     * Where to redirect
     *
     * @var string
     */
    private $redirectPath;


    /**
     * Constructor
     *
     * @param string $to 
     * @return void
     */
    public function __construct($to)
    {
        $this->redirectPath = basePath() . $to;
    }


    /**
     * Used to specify the redirect location
     *
     * @param string $location 
     * @return Nero\Core\Http\RedirectResponse
     */
    public function to($location)
    {
        $this->redirectPath = basePath() . $location;

        return $this;
    }


    /**
     * Redirect back to the same page
     *
     * @return $this
     */
    public function back()
    {
        $request = container('Request');

        $this->redirectPath = basePath() . ltrim($request->getPathInfo(), '/');

        return $this;
    }


    /**
     * Send the response to the user
     *
     * @return void
     */
    public function send()
    {
        header("Location: $this->redirectPath");
    }
}
