<?php

namespace Nero\Core\Database;


/**
 * Database(DB) singleton class, manages the connection to the db.
 * Provides basic querying functionality, built on top of PDO.
 */
class DB
{
    /**
     * Singleton instance.
     *
     * @var Nero\Core\Database\DB;
     */
    private static $instance = null;

    /**
     * PDO handle.
     *
     * @var PDO
     */
    private $pdo = null;

    
    /**
     * Result of the query.
     *
     * @var array
     */
    private $result = null;
    

    /**
     * Get the instance.
     *
     * @return Nero\Core\Database\DB
     */
    public static function getInstance()
    {
        if (static::$instance == null){
            static::$instance = new static();
        }

        return static::$instance;
    }


    /**
     * Query the db and get the results.
     *
     * @param string $sql
     * @param array $arguments
     * @return mixed
     */
    public function query($sql, array $arguments = [])
    {
        $stmt = $this->pdo->prepare($sql);

        if ($stmt->execute($arguments)){
            if (stringStartsWith('INSERT', $sql)){
		//insert operation, return inserted id
                return $this->pdo->lastInsertId();
	    }
            else if (stringStartsWith('UPDATE', $sql) || stringStartsWith('DELETE', $sql)){
		//update or delete, return true to indicate success
		return true;
	    }
            else
                //we have results to fetch
                return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        return false;
    }


    /**
     * Constructor, connect to the db through PDO.
     *
     * @return void
     */
    private function __construct()
    {
        //get the config parameters
        $hostname = config('db_hostname');
        $dbname   = config('db_name');
        $username = config('db_username');
        $password = config('db_password');

        //instantiate the pdo - connect
        $this->pdo = new \PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    
    /**
     * Disable cloning of the singleton
     *
     * @return void
     */
    private function __clone(){}
}
