<?php namespace Nero\App\Models;


use Nero\Core\Database\DB;
use Nero\Core\Database\QB;


/************************************************************
 * Model base class, implementation of active record pattern.
 ************************************************************/
class Model
{
    /**
     *  $db is a connection to the database singleton
     *  $table represents table name that the model coresponds to
     *  $attributes hold the columns from the database
     */
    protected $db = null;
    protected $table;
    protected $attributes = [];


    /**
     * Constructor, init the db handle and table name
     *
     * @return void
     */
    public function __construct()
    {
        //lets get the db handle from the DB singleton
        $this->db = DB::getInstance();

        //if the table name is not explicitly set, lets parse the table name the default way - lowercase and adding 's' at the end
	if(!isset($this->table))
            $this->table = strtolower(nonNamespacedClassName(get_class($this)) . 's');
    }


    /**
     * Magic get method for accessing properties
     *
     * @param string $name 
     * @return mixed
     */
    public function __get($name)
    {
        if(!in_array($name, array_keys($this->attributes)))
            return false;

        return $this->attributes[$name];
    }


    /**
     * Magic method for setting attributes(properties)
     *
     * @param string $name 
     * @param mixed $value 
     * @return void
     */
    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }


    /**
     * Magic method for checking if the property is set
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        if(in_array($name, array_keys($this->attributes)))
            return true;

	return false;
    }


    /**
     * Implement dynamic method for querying the database
     *
     * @param string $name
     * @param array $arguments        
     * @return Model instance
     */
    public static function __callStatic($name, $arguments)
    {
        if(stringStartsWith("where", $name)){
            $propertyName = strtolower(substr($name, 5));

            $instance = new static;

            $result = QB::table($instance->table)->where($propertyName, '=', $arguments[0])->get();

            return $instance->packResults($result);
        }

        throw new \Exception("Calling nonexistant method {$name}.");
    }


    /**
     * Used for setting custom table names
     *
     * @param string $table 
     * @return void
     */
    public function setTable($table)
    {
        $this->table = $table;
    }


    /**
     * Return the model table name
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }


    /**
     * Return the attributes as array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }


    /**
     * Create a model from array
     *
     * @param array $data 
     * @return Model instance
     */
    public static function fromArray(array $data)
    {
        $instance = new static;

        $instance->attributes = $data;

        return $instance;
    }


    /**
     * Create a new instance of the model and persist it in the database
     *
     * @param array $data 
     * @return Model
     */
    public static function create(array $data)
    {
        $instance = static::fromArray($data);

        $instance->save();

        return $instance;
    }


    /**
     * Get all database rows as an array
     *
     * @return array
     */
    public static function all()
    {
        $instance = new static;

        $queryResult = QB::table($instance->table)->get();

        return $instance->packResults($queryResult);
    }


    /**
     * Find by id
     *
     * @param int $id 
     * @return array
     */
    public static function find($id)
    {
        $instance = new static;
        
        $instance->attributes = QB::table($instance->table)->where('id', '=', $id)->get()[0];

        return $instance;
    }

    
    /**
     * Find by id or throw an exception
     *
     * @param int $id 
     * @return array
     */
    public static function findOrFail($id)
    {
        $instance = new static;
        
        $instance->attributes = QB::table($instance->table)->where('id', '=', $id)->get()[0];

        if(empty($instance->attributes))
            throw new \Exception("Lookup for an id of {$id} on table {$instance->table} failed.");

        return $instance;
    }


    /**
     * Implement retrival of models based on column 
     *
     * @param string $column 
     * @param string $operator 
     * @param string $value 
     * @return array
     */
    public static function where($column, $operator, $value)
    {
        $instance = new static;

        $queryResult = QB::table($instance->table)->where($column, $operator, $value)->get();

        return $instance->packResults($queryResult);
    }


    /**
     * Implements has one relationship
     *
     * @param string $model
     * @param string $foreignKey
     * @param string $tableName
     * @return Model
     */
    public function hasOne($model, $foreignKey = "", $tableName = "")
    {
	//collect the info needed for the query
        $foreignKey = $this->hasForeignKey($foreignKey);
	$tableName = ($tableName == "") ? nonNamespacedClassName($model) : $tableName;

        $queryResult = QB::table($tableName)->where($foreignKey, '=', $this->id)->limit(1)->get();

        return $this->packResults($queryResult, $model);
    }


    /**
     * Implements the has many relationship
     *
     * @param string $model 
     * @param string $foreignKey 
     * @param string $tableName 
     * @return array
     */
    public function hasMany($model, $foreignKey = "", $tableName = "")
    {
	//collect the info needed for the query
	$tableName = ($tableName == "") ? $this->extractDefaultTableName($model) : $tableName;
        $foreignKey = $this->hasForeignKey($foreignKey);

	//query the db
        $queryResult = QB::table($tableName)->where($foreignKey, '=', $this->id)->get();

	//return array of requested models
        return $this->packResults($queryResult, $model);
    }


    /**
     * Implements the belongs to relationship
     *
     * @param string $model
     * @param string $foreignKey 
     * @param string $tableName 
     * @return array
     */
    public function belongsTo($model, $foreignKey = "", $tableName = "")
    {
	//collect the info needed for the query
	$tableName = ($tableName == "") ? $this->extractDefaultTableName($model) : $tableName;
        $foreignKey = $this->belongsForeignKey($foreignKey, $tableName);

	//query the db 
        $queryResult = QB::table($tableName)->where('id', '=', $this->{$foreignKey})->limit(1)->get();

	//return singular instance of the requested model
        return $this->packResults($queryResult, $model)[0];
    }


    /**
     * Implement saving of a model to a database
     *
     * @return bool
     */
    public function save()
    {
	//create the array consisting of the attributes which can be saved in the database
        $attributesToBePersisted = $this->persistableAttributes($this->attributes);

        if($this->id){//TODO primary key?
            //we need to update the model in the db(it contains the id, which means it already exists in the database)
            return QB::table($this->table)
                     ->set($attributesToBePersisted)
                     ->where('id', '=', $this->id)
                     ->update();
        }
        else{
            //we need to insert a new record into the db(id is missing which means the model exist in memory only)
            $lastInsertedId = QB::table($this->table)->insert($attributesToBePersisted);
            
            //set the newly obtained id 
            $this->attributes['id'] = $lastInsertedId;

            //return the id 
            return $lastInsertedId;
        }
    }


    /**
     * Implement deleting a row from the db
     *
     * @return bool
     */
    public function delete()
    {
        if(isset($this->id)){
	    //if the row exist in the db delete it and return the result of the query
            $result = QB::table($this->table)->where('id', '=', $this->id)->delete();
            $this->attributes = [];
            return $result;
        }

	//else just delete the in memory data and return true to indicate success
        $this->attributes = [];
	return true;
    }

 
    /**
     * Extract the default table name from the model class.
     * Just return lowercase with appended 's' at the end.
     * 
     * @param string @model
     * @return string
     */ 
    private function extractDefaultTableName($model)
    {
	return strtolower(nonNamespacedClassName($model) . 's');
    }


    /**
     * Pack results of a query into array or single model instance
     *
     * @param array $queryResult 
     * @return mixed
     */
    protected function packResults($queryResult, $model = "")//TODO
    {
        if($queryResult){
            if(isMultidimensional($queryResult))
                return $this->packModelsIntoArray($queryResult, $model);
            else{
		//TODO
                //we have a single assoc array, convert it to model and return it
                $this->attributes = $queryResult[0];
                return $this;
            }
        }

        //query returned false, no results, return empty array
        return [];
    }


    /**
     * Create an array of models
     *
     * @param array $queryResult 
     * @return array of models
     */
    private function packModelsIntoArray($queryResult, $modelName = "")
    {
        $packedResult = [];

        foreach($queryResult as $result){
	    //create the instance of the requested model
	    if($modelName == "")
		$model = new static;
	    else
		$model = new $modelName;

	    //setup data
            $model->attributes = $result;

	    //add the instance to array
            $packedResult[] = $model;
        }

        return $packedResult;
    }


    /**
     * Create an assoc array containing only the keys and values of the attributes which can be persisted in the database
     *
     * @param array $attributes 
     * @return assoc array
     */
    private function persistableAttributes(array $attributes)
    {
        //if fillable property is set do the filtering
        if(isset($this->fillable) && !empty($this->fillable)){
            //array to be populated
            $result = [];

            //loop through the attributes array and filter elements so that they correspond to the fillable property array
            foreach($attributes as $key => $value){
                if(in_array($key, $this->fillable))
                    $result[$key] = $value;
            }
	    
            return $result;
        }

        //fillable property is not set on the model, throw an exception to indicate error
        $modelName = get_class($this);
        throw new \Exception("Fillable properties not set on the model {$modelName}");
    }


    /**
     * Create a foreign key to be used in has many relationships queries
     *
     * @param string $foreignKey 
     * @return string
     */
    private function hasForeignKey($foreignKey)  
    {       
	if($foreignKey == ""){
	    //default foreign key generation, "model"_id
            $modelName = nonNamespacedClassName(get_class($this));
            return strtolower($modelName) . "_id";
        }
        else
	    //return the user supplied explicit foreign key, no need for processing
            return $foreignKey;
    }


    /**
     * Create a foreign key to be used in belongs to relationship queries
     *
     * @param string $foreignKey 
     * @param string $tableName 
     * @return string 
     */
    private function belongsForeignKey($foreignKey, $tableName)
    {
        if($foreignKey == "")
	    //default foreign key generation, ("tableName" - 's')_id
            return rtrim(strtolower($tableName), 's') . "_id";
        else
            return $foreignKey;
    }
}
