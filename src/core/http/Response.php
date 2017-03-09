<?php

namespace Nero\Core\Http;

use Nero\Services\Proxies\Session;

/**
 * HTTP response class, used for returning a response to the user.
 *
 */
class Response
{
    /**
     * Simple message to be returned.
     *
     * @var string
     */
    private $message;
    

    /**
     * Constructor.
     *
     * @param string $message
     * @return void
     */
    public function __construct($message = "")
    {
        $this->message = $message;
    }


    /**
     * Set a response header.
     *
     * @param string $value 
     * @return Nero\Core\Http\Response
     */
    public function header($value)
    {
        header($value);

        return $this;
    }

    
    /**
     * Add old input to the session.
     *
     * @param array $data 
     * @return $this
     */
    public function withOld(array $data)
    {
        //lets clear the old input
        Session::destroyOldInput();
        
        //lets populate the old input
        foreach($data as $key => $value){
            Session::setOldInput($key, $value);
        }

        return $this;
    }


    /**
     * Just return the message to the user, subclasses will overide this method.
     *
     * @return string
     */
    public function send()
    {
        echo $this->message;
    }
}
