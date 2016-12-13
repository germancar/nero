<?php

namespace Nero\Core\Http;

use Nero\Core\Database\Model;

/**************************************************************************
 * JsonResponse implements the functionality needed to send back JSON data
 * to the user. It imeplements the send abstract method.
 ***************************************************************************/
class JsonResponse extends Response
{
    private $jsonData = [];

    /**
     * Constructor, data can be passed in directly
     *
     * @param array $data 
     * @return void
     */
    public function __construct($data)
    {
        //lets parse the data
        if(is_subclass_of($data, 'Nero\App\Models\Model')){
            //we have single instance of a model
            $this->jsonData = $data->toArray();
        }
        else if($this->isArrayOfModels($data)){
            //we have an array of models
            foreach($data as $model)
                $this->jsonData[] = $model->toArray();
        }
        else
            //we have a simple array
            $this->jsonData = $data;
    }


    /**
     * Data, used for setting the json data
     *
     * @param array $data 
     * @return Nero\Core\Http\JsonResponse
     */
    public function data(array $data)
    {
        $this->jsonData = $data;

        return $this;
    }


    /**
     * Send, send the information back to the user
     *
     * @return json data
     */
    public function send()
    {
        echo json_encode($this->jsonData);
    }


    /**
     * Utility method to check for array of models
     *
     * @param array $array 
     * @return bool
     */
    private function isArrayOfModels($array)
    {
        foreach($array as $element){
            if(!is_subclass_of($element, 'Nero\App\Models\Model'))
                return false;
        }

        return true;
    }


}
