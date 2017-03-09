<?php

namespace Nero\Services;

use Nero\Core\Database\QB;
use Nero\Services\Proxies\Session;
use Nero\Interfaces\AuthInterface;

/**
 * Auth service is used for registering and logging in of the users.
 *
 */
class Auth extends Service implements AuthInterface
{
    /**
     * Install the service into the container.
     *
     * @return void
     */
    public static function install()
    {
	container()['Auth'] = function($c){
	    return new Auth;
	};
    }


    /**
     * Register a new user.
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
        Session::destroyOldInput();

        //persist the model
        return $model->save();
    }


    /**
     * Implement logging in of a user.
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
        if ($queryResult){
            if (password_verify($password, $queryResult[0]['password'])){
                //set the session
                Session::set("user_id", $queryResult[0]['id']);

                //clean up the old input from the form
                Session::destroyOldInput();

                return true;
            }
        }

        return false;
    }


    /**
     * Logout a user.
     *
     */
    public function logout()
    {
        session_destroy();
    }


    /**
     * Check if the user is logged in.
     *
     * @return bool
     */
    public function check()
    {
        if(Session::get('user_id'))
            return true;

        return false;
    }


    /**
     * Return the currently logged in user model.
     *
     * @return Model
     */
    public function user()
    {
        if($userID = Session::get('user_id')){
	    $model = $this->namespacedAuthModel();

            return $model::find($userID);
        }

        return false;
    }


    /**
     * Create a model instance from array data.
     *
     * @param array $queryResult 
     * @return Model
     */
    private function createModelFromData(array $data)
    {
	$model = $this->namespacedAuthModel();

        if (!class_exists($model))
            throw new \Exception("Model $model does not exist.");


        return $model::fromArray($data);
    }


    /**
     * Get the full namespaced model name from the auth config.
     *
     * @return string
     */
    private function namespacedAuthModel()
    {
        $modelName = ucfirst(config('auth_return_model'));

        return "Nero\App\Models\\$modelName";
    }

}
