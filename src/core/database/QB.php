<?php

namespace Nero\Core\Database;

/**
 * QueryBuilder used for fluent database queries.
 *
 */
class QB
{
    private $db;
    private $tables;
    private $select;
    private $set;
    private $whereClauses;
    private $orderBy;
    private $groupBy;
    private $limit = null;
    private $bindings = [];
    private $operators = ['=', '<=' , '>=', '<', '>', '<>', 'LIKE'];
    private $model;
    private $sql;


    /**
     * Method for starting the query build process.
     *
     * @param string $tables 
     * @return QB instance
     */
    public static function table($tables = "")
    {
        //lets create the instance of the builder and hook up the db connection
        $instance = new static;
        $instance->db = DB::getInstance();

        if ($tables == "")
            throw new \Exception('Table name empty.');

        $instance->tables = $tables;

        return $instance;
    }


    /**
     * Used for inserting data into database.
     *
     * @param array $data 
     * @return array
     */
    public function insert(array $data)
    {
        // ['?', '?' ...] 
        $questionMarks = [];

        //lets add bindings and populate question marks
        foreach ($data as $key => &$value){
            $this->addBinding($value);
            $questionMarks[] = '?';
        }
        
        //lets format the column names
        $formatedColumnNames = "(". implode(',', array_keys($data)) . ")";

        //lets format the question marks
        $formatedQuestionsMarks = "(" . implode(',', $questionMarks) . ")";

        //generate sql
        $this->sql = "INSERT INTO {$this->tables} {$formatedColumnNames} VALUES {$formatedQuestionsMarks} ;";

        return  $this->db->query($this->sql, $this->bindings);
    }


    /**
     * Used for setting the columns for update.
     *
     * @param array $data 
     * @return QB instance
     */
    public function set(array $data)
    {
        foreach ($data as $key => $value){
            $this->addBinding($value);
            $columns[] = "{$key}=?";
        }

        $formatedColumns = implode(',', $columns);

        $this->set = "SET {$formatedColumns}";

        return $this;
    }


    /**
     * Execute the update query.
     *
     * @return bool
     */
    public function update()
    {
        $this->sql = "UPDATE {$this->tables} {$this->set} {$this->whereClauses};";

        return $this->db->query($this->sql, $this->bindings);
    }


    /**
     * Execute the delete query.
     *
     * @return bool
     */
    public function delete()
    {
        $this->sql = "DELETE FROM {$this->tables} {$this->whereClauses};";

        return $this->db->query($this->sql, $this->bindings);
    }


    /**
     * Set the select part of the statement.
     *
     * @param variable  
     * @return QB instance
     */
    public function select()
    {
        //lets format the columns that we are interested in
        $formatedColumns = implode(',', func_get_args());

        //generate the SELECT portion of the query
        $this->select =  "SELECT {$formatedColumns}";

        return $this;
    }

    
    /**
     * Implement distinct.
     *
     * @return QB instance
     */
    public function distinct()
    {
        //just insert "DISTINCT" substring into select statement
        $this->select = substr_replace($this->select, "DISTINCT ", 7, 0);

        return $this;
    }


    /**
     * Add a WHERE clause.
     *
     * @param  $column 
     * @param  $operator 
     * @param  $value 
     * @return QB instance
     */
    public function where()
    {
	//parse the arguments
	$args = func_get_args();

	if(count($args) == 3){
	    $column = $args[0];
	    $operator = $args[1];
	    $value = $args[2];
	}
	else if (count($args) == 2){
	    $column = $args[0];
	    $operator = '=';
	    $value = $args[1];
	}

        //lets check that the supplied operator is supported
        if (!in_array($operator, $this->operators))
            throw new \Exception("Operator {$operator} not supported.");

        $this->whereClauses = "WHERE {$column} {$operator} ?";

        //finaly lets bind the value
        $this->addBinding($value);

        return $this;
    }


    /**
     * Add an AND part of the WHERE clause.
     *
     * @param  $column 
     * @param  $operator 
     * @param  $value 
     * @return QB instance
     */
    public function andWhere()
    {
	//parse the arguments
	$args = func_get_args();

	if(count($args) == 3){
	    $column = $args[0];
	    $operator = $args[1];
	    $value = $args[2];
	}
	else if (count($args) == 2){
	    $column = $args[0];
	    $operator = '=';
	    $value = $args[1];
	}

        //lets check that the supplied operator is supported
        if (!in_array($operator, $this->operators))
            throw new \Exception("Operator {$operator} not supported.");

        $this->whereClauses .= " AND {$column} {$operator} ?";

        //finaly lets bind the value
        $this->addBinding($value);

        return $this;
    }


    /**
     * Add an OR part of the WHERE clause.
     *
     * @param  $column 
     * @param  $operator 
     * @param  $value 
     * @return QB instance
     */
    public function orWhere()
    {
	//parse the arguments
	$args = func_get_args();

	if(count($args) == 3){
	    $column = $args[0];
	    $operator = $args[1];
	    $value = $args[2];
	}
	else if (count($args) == 2){
	    $column = $args[0];
	    $operator = '=';
	    $value = $args[1];
	}

        //lets check that the supplied operator is supported
        if (!in_array($operator, $this->operators))
            throw new \Exception("Operator {$operator} not supported.");

        $this->whereClauses .= " OR {$column} {$operator} ?";

        //finaly lets bind the value
        $this->addBinding($value);

        return $this;
    }


    /**
     * Implement where between functionality.
     *
     * @param string $column 
     * @param value $start 
     * @param value $end 
     * @return QB instance
     */
    public function whereBetween($column, $start, $end)
    {
        $this->whereClauses = "WHERE {$column} BETWEEN ? AND ?";

        //finaly lets bind the values
        $this->addBinding($start);
        $this->addBinding($end);

        return $this;
    }


    /**
     * Implements the WHERE NOT BETWEEN clause.
     *
     * @param string $column 
     * @param int $start 
     * @param int $end 
     * @return QB instance
     */
    public function whereNotBetween($column, $start, $end)
    {
        $this->whereClauses = "WHERE {$column} NOT BETWEEN ? AND ?";

        //finaly lets bind the values
        $this->addBinding($start);
        $this->addBinding($end);

        return $this;
    }


    /**
     * Implements the WHERE IN clause.
     *
     * @param string $column 
     * @return QB instance
     */
    public function whereIn($column, array $values)
    {
        //lets bind the values and populate coresponding question marks
        foreach ($values as $value){
            $questionMarks[] = "?";
            $this->addBinding($value);
        }

        $formatedQuestionMarks = "(" . implode(',', $questionMarks) . ")";
        
        //lets create the where clause
        $this->whereClauses = "WHERE {$column} IN {$formatedQuestionMarks}";

        return $this;
    }


    /**
     * Implements WHERE NOT IN clause.
     *
     * @param string $column 
     * @return QB instance
     */
    public function whereNotIn($column, array $values)
    {
        //lets bind the values and populate coresponding question marks
        foreach ($values as $value){
            $questionMarks[] = "?";
            $this->addBinding($value);
        }

        $formatedQuestionMarks = "(" . implode(',', $questionMarks) . ")";
        
        //lets create the where clause
        $this->whereClauses = "WHERE {$column} NOT IN {$formatedQuestionMarks}";

        return $this;
    }


    /**
     * Implement the order by clause.
     *
     * @param array $columns 
     * @return QB instance
     */
    public function orderBy(array $columns)
    {
        //lets parse the columns, they are in the format ['column1' => 'ASC', 'column2' => 'DESC']
        $formatedColumns = "";

        $i = 0;
        $length = count($columns);
        foreach ($columns as $column => $order){
            $formatedColumns .= "{$column} {$order}";
            if ($i < $length - 1)
                $formatedColumns .= ', ';
            $i++;
        }

        //create the full order by clause
        $this->orderBy = "ORDER BY {$formatedColumns}";

        return $this;
    }


    /**
     * Implements the GROUP BY clause.
     *
     * @return QB instance
     */
    public function groupBy($columns)
    {
        if (is_array($columns))
            $formatedColumns = implode(',', $columns);
        else
            $formatedColumns = implode(',', func_get_args());

        $this->groupBy = "GROUP BY {$formatedColumns}";

        return $this;
    }


    /**
     * Limit clause.
     *
     * @param int $value
     * @return QB
     */
    public function limit($value)
    {
        $this->limit = "LIMIT {$value}";

        return $this;
    }


    /**
     * Method for retrieving results from the query.
     *
     * @param mixed $columns 
     * @return array
     */
    public function get($column = "")
    {
        //lets check if the select is specified(if not then select all)
        if (!isset($this->select))
            $this->select = "SELECT * ";

        //generate sql
        $this->sql = "{$this->select} FROM {$this->tables} {$this->whereClauses} {$this->orderBy} {$this->groupBy} {$this->limit};";

        //lets execute the query
        $result = $this->db->query($this->sql, $this->bindings);

	//return a model if requested
	if (isset($this->model))
	    return arrayOfModels($result, $this->model);

        //return only the specified column if supplied
        if ($column != "")
            return $result[$column];

	//return results as array
        return $result;
    }


    /**
     * Retrieve only the first element from the result set.
     *
     * @return mixed
     */
    public function first()
    {
	return $this->get()[0];
    }


    /**
     * Set the model.
     *
     * @param string $modelName
     * @return QB
     */
    public function model($modelName)
    {
	$this->model = $modelName;

	return $this;
    }


    /**
     * Add a binding to be used in a query.
     *
     * @param  $value 
     * @return void
     */
    private function addBinding($value)
    {
        $this->bindings[] = $value;
    }


    /**
     * For testing.
     *
     */
    private function echoSQL()
    {
        echo "SQL = " . $this->sql . "<br/>";
        echo "Bindings = ";

        foreach ($this->bindings as $binding){
            echo $binding . " | ";
        }

        echo "<br/>";
    }
    
}
