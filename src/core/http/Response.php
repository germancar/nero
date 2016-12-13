<?php

namespace Nero\Core\Http;


/*******************************************************
 * Simple Response class encapsulates working with
 * responses that we are sending to the user.
 * You can add a view to be rendered with data,
 * redirect the user or send a json response for APIs.
 *******************************************************/
class Response
{
    private $message;
    
    public function __construct($message = "")
    {
        $this->message = $message;
    }


    /**
     * Set a response header
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
     * Add old input to the session
     *
     * @param array $data 
     * @return $this
     */
    public function withOld(array $data)
    {
        $session = container('Session');

        //lets clear the old input
        $session->destroyOldInput();
        
        //lets populate the old input
        foreach($data as $key => $value){
            $session->setOldInput($key, $value);
        }

        return $this;
    }


    /**
     * Just return the message to the user, subclasses will implement this method
     *
     * @return mixed
     */
    public function send()
    {
        echo $this->message;
    }
}
