<?php

namespace Nero\App\Models;

use Nero\Core\Database\DB;
use Nero\Core\Database\QB;

/**
 * Model implements the active record design pattern. It provides nice object oriented interface to the database.
 * Methods allow to retrieve records from the db, manipulate them as objects and save them back to storage.
 */
class Model
{
    /**
     * DB handle.
     *
     * @var Nero\Core\Database\DB
     */
    protected $db = null;

    /**
     * Database table that the model coresponds to.
     *
     * @var string
     */
    protected $table;

    
    /**
     * Model class.
     *
     * @var string
     */
    protected $className;


    /**
     * Attributes of the model.
     *
     * @var array
     */
    protected $attributes = [];


    /**
     * Primary key.
     *
     * @var mixed
     */
    protected $primaryKey = "id";


    /**
     * Query retrieval modes, MODEL or RAW array.
     *
     */
    const MODEL = 0;
    const RAW = 1;


    /**
     * Constructor, init the db handle, table and class name.
     *
     * @return void
     */
    public function __construct()
    {
        $this->db = DB::getInstance();

        //if the table name is not explicitly set, lets parse the table name the default way - lowercase model name + 's' at the end
	if (!isset($this->table))
            $this->table = strtolower(nonNamespacedClassName(get_class($this)) . 's');

	//setup class name
	if (!isset($this->className))
	    $this->className = nonNamespacedClassName(get_class($this));
    }


    /**
     * Magic get method for accessing properties.
     *
     * @param string $name 
     * @return mixed
     */
    public function __get($name)
    {
	//check that the attribute is set
        if (!in_array($name, array_keys($this->attributes)))
            throw new \InvalidArgumentException("Trying to access nonexistant property '{$name}' on model {$this->className}.");

	//if attribute transformator is defined invoke it
	$attributeTransformatorMethod = "get" . ucfirst($name) . "Attribute";
	if (method_exists($this, $attributeTransformatorMethod) && in_array($name, array_keys($this->attributes))){
	    return $this->$attributeTransformatorMethod($this->attributes[$name]);
	}

	//else just return the attribute as it is
        return $this->attributes[$name];
    }


    /**
     * Magic method for setting attributes(properties).
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
     * Magic method for checking if the property is set.
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        if (in_array($name, array_keys($this->attributes)))
            return true;

	return false;
    }


    /**
     * Implement dynamic static method, whereAttribute(value) format. 
     *
     * @param string $name
     * @param array $arguments        
     * @return Model instance
     */
    public static function __callStatic($name, $arguments)
    {
	//where dynamic method
        if (stringStartsWith("where", $name)){
	    //extract the attribute name from the method called
            $attributeName = strtolower(substr($name, 5));

            $instance = new static;

            $data = QB::table($instance->table)->where($attributeName, '=', $arguments[0])->get();

            return arrayOfModels($data, $instance->className);
        }


        throw new \Exception("Calling nonexistant method '{$name}'.");
    }

    
    /**
     * Dynamic methods.
     *
     * @param string $name
     * @param string $arguments
     * @return bool
     */
    public function __call($name, $arguments)
    {
	//implement the foreach dynamic method on has many result
	if (stringStartsWith("foreach", $name)){
	    $relationName = strtolower(substr($name, 7));
	    $result = $this->$relationName();
	    array_walk($result, $arguments[0]);
	    return true;
	}

	//map the relation
	if (stringStartsWith("map", $name)){
	    $relationName = strtolower(substr($name, 3));
	    $result = $this->$relationName();
	    return array_map($arguments[0], $result);
	}

	//reduce the relation
	if (stringStartsWith("reduce", $name)){
	    $relationName = strtolower(substr($name, 6));
	    $result = $this->$relationName();

	    if (isset($arguments[1]))
		return array_reduce($result, $arguments[0], $arguments[1]);
	    else
		return array_reduce($result, $arguments[0]);
	}

        throw new \Exception("Calling nonexistant method '{$name}'.");	
    }


    /**
     * Used for setting custom table names.
     *
     * @param string $table 
     * @return void
     */
    public function setTable($table)
    {
        $this->table = $table;
    }


    /**
     * Return the model table name.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }


    /**
     * Return the attributes as array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }


    /**
     * Create a model from array.
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
     * Create a new instance of the model and persist it in the database.
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
     * Get all database rows as an array of models.
     *
     * @return array
     */
    public static function all()
    {
        $instance = new static;

        $data = QB::table($instance->table)->get();

        return arrayOfModels($data, $instance->className);
    }


    /**
     * Find by id.
     *
     * @param int $id 
     * @return array
     */
    public static function find($id)
    {
        $instance = new static;
        
        $instance->attributes = QB::table($instance->table)->where('id', '=', $id)->first();

	if (empty($instance->attributes))
	    return null;
	
        return $instance;
    }

    
    /**
     * Find by id or throw an exception.
     *
     * @param int $id 
     * @return array
     */
    public static function findOrFail($id)
    {
        $instance = new static;
        
        $instance->attributes = QB::table($instance->table)->where('id', '=', $id)->first();

        if (empty($instance->attributes))
            throw new \Exception("Lookup for an id of {$id} on table {$instance->table} failed.");

        return $instance;
    }


    /**
     * Implement retrival of models based on column.
     *
     * @return array
     */
    public static function where()
    {
	//parse the arguments
	$args = func_get_args();

	if (count($args) == 3){
	    $column = $args[0];
	    $operator = $args[1];
	    $value = $args[2];
	}
	else if (count($args) == 2){
	    $column = $args[0];
	    $operator = '=';
	    $value = $args[1];
	}

        $instance = new static;

        $data = QB::table($instance->table)->where($column, $operator, $value)->get();

	return arrayOfModels($data, $instance->className);
    }


    /**
     * Raw sql query, or start a query builder tied to this model(QB will return an array of model instances).
     *
     * @return mixed
     */
    public static function query($sql = "", $bindings = [], $retrieveMode = self::MODEL)
    {
	$instance = new static;

	//raw sql query, parse result into an array of models
	if ($sql != "" && $retrieveMode == self::MODEL){
	    return arrayOfModels($instance->db->query($sql, $bindings), $instance->className);
	}

	//raw sql query, return raw results(assoc array)
	if ($sql != "" && $retrieveMode == self::RAW){
	    return $instance->db->query($sql, $bindings);
	}

	//return QueryBuilder tied to this model class, so the user can chain methods on it.
	return QB::table($instance->table)->model($instance->className);
    }


    /**
     * Implements has one relationship.
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
	$tableName = ($tableName == "") ? $this->extractDefaultTableName($model) : $tableName;

	//query the db
        $data = QB::table($tableName)->where($foreignKey, '=', $this->{$this->primaryKey})->first();

	//return instance of the related model
        return $model::fromArray($data);
    }


    /**
     * Implements the has many relationship.
     *
     * @param string $model 
     * @param string $foreignKey 
     * @param string $tableName 
     * @return array
     */
    public function hasMany($model, $foreignKey = "", $tableName = "")
    {
	//collect the info needed for the query
	$foreignKey = ($foreignKey == "") ? $this->createDefaultHasForeignKey() : $foreignKey;
	$tableName = ($tableName == "") ? $this->extractDefaultTableName($model) : $tableName;

	//query the db
        $data = QB::table($tableName)->where($foreignKey, '=', $this->{$this->primaryKey})->get();

	//return array of requested models
        return arrayOfModels($data, $model);
    }


    /**
     * Implements the belongs to relationship.
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
        $foreignKey = ($foreignKey == "") ? $this->createDefaultBelongsForeignKey($tableName) : $foreignKey;
	$model = "Nero\\App\Models\\" . $model;
	$primaryKey = (new $model)->primaryKey;

	//query the db 
        $data = QB::table($tableName)->where($primaryKey, '=', $this->{$foreignKey})->first();

	//return singular instance of the requested model
        return $model::fromArray($data);
    }


    /**
     * Implement saving of a model to a database.
     *
     * @return mixed
     */
    public function save()
    {
	//create the array consisting of the attributes which can be saved in the database(the ones that exist in the schema)
        $attributesToBePersisted = $this->persistableAttributes($this->attributes);

        if (isset($this->{$this->primaryKey}) && $this->isStored()){
            //we need to update the model in the db(it contains the id, which means it already exists in the database)
            return QB::table($this->table)
                     ->set($attributesToBePersisted)
                     ->where($this->primaryKey, '=', $this->{$this->primaryKey})
                     ->update();
        }
        else{
            //we need to insert a new record into the db(id is missing which means the model exist in memory only)
	    if ($this->primaryKey == "id"){
		$lastInsertedId = QB::table($this->table)->insert($attributesToBePersisted);

		$this->attributes['id'] = $lastInsertedId;

		return $lastInsertedId;
	    }

	    //if primary key is not id, just insert the values into the db
	    return QB::table($this->table)->insert($attributesToBePersisted);
        }
    }


    /**
     * Delete the model from the database.
     *
     * @return bool
     */
    public function delete()
    {
        if (isset($this->{$this->primaryKey}) && $this->isStored()){
	    //if the row exist in the db delete it and return the result of the query
            $result = QB::table($this->table)->where($this->primaryKey, '=', $this->{$this->primaryKey})->delete();
            $this->attributes = [];
            return $result;
        }

	//else just delete the in memory data and return true to indicate success
        $this->attributes = [];
	return true;
    }

 
    /**
     * Helper method to check if the model instance is already stored in database.
     *
     * @return bool
     */
    private function isStored()
    {
	return QB::table($this->table)->where($this->primaryKey, '=', $this->{$this->primaryKey})->get();
    }


    /**
     * Extract the default table name from the model class.
     * Just return lowercase with appended 's' at the end.
     * 
     * @param string $model
     * @return string
     */ 
    private function extractDefaultTableName($model)
    {
	return strtolower(nonNamespacedClassName($model) . 's');
    }


    /**
     * Create an assoc array containing only the keys and values of the attributes which can be persisted in the database.
     *
     * @param array $attributes 
     * @return assoc array
     */
    private function persistableAttributes(array $attributes)
    {
	//fillable property determines which attributes are persisted in the db
        if (isset($this->fillable) && !empty($this->fillable)){
            //filter elements so that they correspond to the fillable property array
            foreach ($attributes as $key => $value){
                if (in_array($key, $this->fillable))
                    $result[$key] = $value;
            }
	    
            return $result;
        }

        //fillable property is not set on the model, throw an exception to indicate error
        $modelName = get_class($this);
        throw new \Exception("Fillable properties not set on the model {$modelName}");
    }


    /**
     * Default hasMany foreign key.
     *
     * @return string
     */
    private function createDefaultHasForeignKey()
    {
	//default foreign key generation, "model"_id
        return strtolower($this->className) . "_id";	
    }


    /**
     * Default belongsTo foreign key
     *
     * @return string
     */
    private function createDefaultBelongsForeignKey($tableName)
    {
	//default foreign key generation, ("tableName" - 's')_id
        return rtrim(strtolower($tableName), 's') . "_id";	
    }
}
