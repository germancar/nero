<?php

namespace Nero\Services;

use Session;
use Nero\Core\Database\QB;


class Auth
{
    /**
     * Register a new user
     *
     * @param array $data 
     * @return bool
     */
    public function register(array $data)
    {
        //get the auth config 
        $authTable = config('auth_table');

        //hash the password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        //lets create the instance of the model which contains the data 
        $model = $this->createModelFromData($data);

        //lets clean up the old input from the form
        container('Session')->destroyOldInput();

        //persist the model
        return $model->save();
    }


    /**
     * Implement logging in of a user
     *
     * @param string $key 
     * @param string $password 
     * @return bool
     */
    public function login($key, $password)
    {
        //get the auth config 
        $authTable = config('auth_table');
        $authKey = config('auth_key');

        //query db 
        $queryResult = QB::table($authTable)->where($authKey, '=', $key)->limit(1)->get();

        //check password
        if($queryResult){
            if(password_verify($password, $queryResult[0]['password'])){
                //set the session
                container('Session')->set("user_id", $queryResult[0]['id']);

                //clean up the old input from the form
                container('Session')->destroyOldInput();

                return true;
            }
        }

        return false;
    }


    /**
     * Logout a user
     *
     */
    public function logout()
    {
        session_destroy();
    }


    /**
     * Check if the user is logged in
     *
     * @return bool
     */
    public function check()
    {
        if(container('Session')->get('user_id'))
            return true;

        return false;
    }


    /**
     * Return the user in the form of a model
     *
     * @return Model
     */
    public function user()
    {
        if($userID = container('Session')->get('user_id')){
            //create the model from the id thats stored in the session
            $modelName = ucfirst(config('auth_return_model'));
            $fullModelName = "Nero\App\Models\\$modelName";

            return $fullModelName::find($userID);
        }

        return false;
    }


    /**
     * Create a model instance from array data
     *
     * @param array $queryResult 
     * @return Model
     */
    private function createModelFromData(array $data)
    {
        $modelName = ucfirst(config('auth_return_model'));

        $fullModelName = "Nero\App\Models\\$modelName";

        if(! class_exists($fullModelName))
            throw new \Exception("Model $fullModelName does not exist.");


        return $fullModelName::fromArray($data);
    }

}
