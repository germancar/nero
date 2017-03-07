<?php

namespace Nero\Core\Http;

use Nero\Core\Database\Model;

/**
 * Implement the JSON response subclass, used for returning json data to the user
 *
 */
class JsonResponse extends Response
{
    private $jsonData = [];

    /**
     * Constructor
     *
     * @param array $data 
     * @return void
     */
    public function __construct($data)
    {
        if (is_subclass_of($data, 'Nero\App\Models\Model')){
            //we have single instance of a model
            $this->jsonData = $data->toArray();
        }
        else if ($this->isArrayOfModels($data)){
            //we have an array of models
            foreach ($data as $model)
                $this->jsonData[] = $model->toArray();
        }
        else
            //we have a simple array
            $this->jsonData = $data;
    }


    /**
     * Set the data
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
     * Send the information back to the user
     *
     * @return void
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
            if (!is_subclass_of($element, 'Nero\App\Models\Model'))
                return false;
        }

        return true;
    }


}
