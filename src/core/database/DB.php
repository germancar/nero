<?php namespace Nero\Core\Database;


/*************************************************************
 * Database(DB) singleton class, manages the connection to db.
 * Provides basic querying functionality, built on top of PDO.
 ************************************************************/
class DB
{
    private static $instance = null;
    private $pdo = null;
    private $result = null;
    

    /**
     * Get the instance
     *
     * @return DB
     */
    public static function getInstance()
    {
        if(static::$instance == null){
            static::$instance = new static();
        }

        return static::$instance;
    }


    /**
     * Query the db and get the results
     *
     * @param string $sql
     * @param array $arguments
     * @return mixed
     */
    public function query($sql, array $arguments = [])
    {
	//prepare statement
        $stmt = $this->pdo->prepare($sql);

	//execute statement
        if($stmt->execute($arguments)){
            if(stringStartsWith('INSERT', $sql))
		//insert operation, return inserted id
                $this->result = $this->pdo->lastInsertId();
            else if(stringStartsWith('UPDATE', $sql) || stringStartsWith('DELETE', $sql))
	    //update or delete, return true to indicate success
            $this->result = true;
            else
                //we have results to fetch
                $this->result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        else
	    //statement not executed successfully, false to indicate error
            $this->result = false;

	//return result of the query
        return $this->result;
    }


    /**
     * Return the results of the last executed query
     *
     * @return mixed
     */
    private function getResults()
    {
        return $this->result;
    }


    /**
     * Constructor, connect to the db through PDO
     *
     * @return void
     */
    private function __construct()
    {
        try{
            //get the config parameters
            $hostname = config('db_hostname');
            $dbname   = config('db_name');
            $username = config('db_username');
            $password = config('db_password');

            //instantiate the pdo - connect
            $this->pdo = new \PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        catch(\PDOException $e){
            echo "PDO exception, " . $e->getMessage();
        }
    }

    
    /**
     * Disable cloning of the singleton
     *
     * @return void
     */
    private function __clone(){}
}
