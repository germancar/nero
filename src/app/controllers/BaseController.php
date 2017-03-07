<?php

namespace Nero\App\Controllers;


/**
 * BaseController implements validation logic.
 *
 */
class BaseController
{
    /**
     * Validate input data against specified rules.
     *
     * @param array $data 
     * @param array $rules 
     * @return bool
     */
    protected function validate($data, $rules = [])
    {
        $result = true;

        foreach ($data as $key => $value){
            $fieldRules = $rules[$key];

	    //apply all the specified rules against the data
            foreach (explode('|', $fieldRules) as $rule){
                if (!$this->validateRule($key, $value, $rule))
                    $result = false;
            }
        }

        return $result;
    }


    /**
     * Process single rule against data.
     *
     * @param string $key 
     * @param mixed $data 
     * @param string $rule 
     * @return bool
     */
    private function validateRule($key, $data, $rule)
    {
	//check if the rule has an associated parameter
        if (strpos($rule, ':')){
            $ruleWithParameter = explode(':', $rule);
            $rule = $ruleWithParameter[0];
            $ruleParameter = $ruleWithParameter[1];
        } 

	//process the rule accordingly
        switch ($rule){
            case 'required':
                if (!empty($data))
                    return true;

                $field = str_replace('_', ' ', $key);
                error("Field $field is required.");                 
                return false;
                break;

            case 'email':
                if ($this->isEmail($data))
                    return true;

                $field = str_replace('_', ' ', $key);
                error("Field $field must be a valid email.");
                return false;
                break;

            case 'unique':
                if ($this->isUnique($key, $data, $ruleParameter))
                    return true;

                $field = str_replace('_', ' ', $key);
                error("Field $field must be unique.");
                return false;
                break;

            case 'match':
                if ($this->matches($key, $ruleParameter))
                    return true;

                $field = str_replace('_', ' ', $key);
                $fieldParameter = str_replace('_', ' ', $ruleParameter);
                error("Field $field must match $fieldParameter field.");
                return false;
                break;

                //other rules can be added here
        }
    }


    /**
     * Check it he string is a valid email.
     *
     * @param string $value
     * @return bool
     */
    private function isEmail($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }


    /**
     * Check if the value is unique in a table.
     *
     * @param string $key
     * @param mixed $data
     * @param string $table
     * @return bool
     */
    private function isUnique($key, $data, $table)
    {
        if (QB::table($table)->where($key, '=', $data)->get())
            return false;

        return true;
    }


    /**
     * Check if the two fields in a form match.
     *
     * @param string $key
     * @param string $ruleParameter
     * @return bool
     */
    private function matches($key, $ruleParameter)
    {
        $request = container('Request');
        
        if ($request->request->get($key) == $request->request->get($ruleParameter))
            return true;

        return false;
    }
}
