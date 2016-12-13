<?php

namespace Nero\App\Controllers;


/*******************************************************
 * Base controller implements validation logic for now
 ******************************************************/
class BaseController
{
    /**
     * Validate input data against specified rules, work in progress
     *
     * @param array $data 
     * @param array $rules 
     * @return bool
     */
    protected function validate($data, $rules = [])
    {
        $result = true;

        foreach($data as $key => $value){
            //get the rules for the current key
            $relevantRules = $rules[$key];

            //iterate over the rules and apply them to the value
            foreach(explode('|', $relevantRules) as $singleRule){
                if(!$this->validateRule($key, $value, $singleRule))
                    $result = false;
            }
        }

        return $result;
    }


    /**
     * Process single rule against data
     *
     * @param string $key 
     * @param mixed $data 
     * @param string $rule 
     * @return bool
     */
    private function validateRule($key, $data, $rule)
    {
        if(strpos($rule, ':')){
            //rule has a parameter
            $ruleWithParameter = explode(':', $rule);
            $rule = $ruleWithParameter[0];
            $ruleParameter = $ruleWithParameter[1];
        } 


        switch($rule){
            case 'required':
                if(!empty($data))
                    return true;

                $field = str_replace('_', ' ', $key);
                error("Field $field is required.");                 
                return false;
                break;

            case 'email':
                if($this->isEmail($data))
                    return true;

                $field = str_replace('_', ' ', $key);
                error("Field $field must be a valid email.");
                return false;
                break;

            case 'unique':
                if($this->isUnique($key, $data, $ruleParameter))
                    return true;

                $field = str_replace('_', ' ', $key);
                error("Field $field must be unique.");
                return false;
                break;

            case 'match':
                if($this->matches($key, $ruleParameter))
                    return true;

                $field = str_replace('_', ' ', $key);
                $fieldParameter = str_replace('_', ' ', $ruleParameter);
                error("Field $field must match $fieldParameter field.");
                return false;
                break;

                //here we will add other validation options
        }
    }


    private function isEmail($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }


    private function isUnique($key, $data, $table)
    {
        $result = QB::table($table)->where($key, '=', $data)->get();

        if($result)
            return false;

        return true;
    }


    private function matches($key, $ruleParameter)
    {
        $request = container('Request');
        
        if($request->request->get($key) == $request->request->get($ruleParameter))
            return true;

        return false;
    }

}
