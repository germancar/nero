<?php

namespace Nero\App\Models;

use Nero\Core\Database\DB;
use Nero\Core\Database\QB;

/********************************************************
 * Model base class, contains a db connection handle.
 * It implements the active record pattern.
 ********************************************************/
class Model
{
    /*
     *  $db is a connection to the database singleton
     *  $table represents table name that the model coresponds to
     *  $attributes hold the columns from the database
     */
    protected $db = null;
    protected $table;
    public $attributes = [];


    /**
     * Constructor, init the db handle
     *
     * @return void
     */
    public function __construct()
    {
        //lets get the db handle from the DB singleton
        $this->db = DB::getInstance();

        //lets parse the table name the default way, adding s at the end
        $this->table = strtolower($this->extractModelName(get_class($this)) . 's');
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
     * Implement dynamic methods for querying the db
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
     * Create a new instance of the model and persist it in the db
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
     * Get all rows as an array
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
     * Implement the has one relationship
     *
     * @param string $tableName 
     * @param string $foreignKey 
     * @return type
     */
    public function hasOne($tableName, $foreignKey = "")
    {
        $foreignKey = $this->createForeignKey($foreignKey);

        $queryResult = QB::table($tableName)->where($foreignKey, '=', $this->id)->get();

        return $this->packResults($queryResult);
    }
    

    /**
     * Implement the has many relationship
     *
     * @param string $tableName 
     * @param string $idColumn 
     * @return array
     */
    public function hasMany($tableName, $foreignKey = "")
    {
        $foreignKey = $this->createHasManyForeignKey($foreignKey);
        
        $queryResult = QB::table($tableName)->where($foreignKey, '=', $this->id)->get();

        return $this->packResults($queryResult);
    }


    /**
     * Implements the belongs to relationship
     *
     * @param string $tablename 
     * @param string $foreignKey 
     * @return array
     */
    public function belongsTo($tableName, $foreignKey = "")
    {
        $foreignKey = $this->createBelongsToForeignKey($foreignKey, $tableName);

        $queryResult = QB::table($tableName)->where('id', '=', $this->{$foreignKey})->get();

        return $this->packResults($queryResult);
    }


    /**
     * Implement saving of a model to a db row
     *
     * @return bool
     */
    public function save()
    {
        $attributesToBePersisted = $this->persistableAttributes($this->attributes);

        if($this->id){
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
            $result = QB::table($this->table)->where('id', '=', $this->id)->delete();
            $this->attributes = [];
            return $result;
        }

        $this->attributes = [];
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
    public function getTableName()
    {
        return $this->table;
    }

 
    /**
     * Extract the model name only,without namespace
     *
     * @param string $fullModelName 
     * @return string
     */
    private function extractModelName($fullModelName)
    {
        $exploded = explode('\\', $fullModelName);

        return $exploded[count($exploded) - 1];
    }


    /**
     * Utility for parsing the query results into model response
     *
     * @param array $queryResult 
     * @return mixed
     */
    private function packResults($queryResult)
    {
        if($queryResult){
            if(isMultidimensional($queryResult))
                return $this->packModelsIntoArray($queryResult);
            else{
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
    private function packModelsIntoArray($queryResult)
    {
        $packedResult = [];

        foreach($queryResult as $result){
            $model = new static;
            $model->attributes = $result;

            $packedResult[] = $model;
        }

        return $packedResult;
    }


    /**
     * Create an assoc array containing only the keys and values of the attributes which can be persisted in the db
     *
     * @param array $attributes 
     * @return assoc array
     */
    private function persistableAttributes(array $attributes)
    {
        //if fillable property is set
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
    private function createHasManyForeignKey($foreignKey)
    {
        if($foreignKey == ""){
            $modelName = $this->extractModelName(get_class($this));
            return strtolower($modelName) . "_id";
        }
        else
            return $foreignKey;
    }


    /**
     * Create a foreign key to be used in belongs to relationships queries
     *
     * @param string $foreignKey 
     * @param string $tableName 
     * @return string
     */
    private function createBelongsToForeignKey($foreignKey, $tableName)
    {
        if($foreignKey == ""){
            return rtrim(strtolower($tableName), 's') . "_id";
        }
        else
            return $foreignKey;
    }
}
