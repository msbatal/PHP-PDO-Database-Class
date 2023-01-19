<?php

/**
 * SunDB Class
 *
 * @category  Database Access
 * @package   SunDB
 * @author    Mehmet Selcuk Batal <batalms@gmail.com>
 * @copyright Copyright (c) 2020, Sunhill Technology <www.sunhillint.com>
 * @license   https://opensource.org/licenses/lgpl-3.0.html The GNU Lesser General Public License, version 3.0
 * @link      https://github.com/msbatal/PHP-PDO-Database-Class
 * @version   2.5.4
 */

class SunDB
{
    /**
     * Database credentials
     * @var array
     */
    private $connectionParams = [
        'driver' => 'mysql',
        'url' => null,
        'host' => 'localhost',
        'port' => '3306',
        'dbname' => null,
        'username' => null,
        'password' => null,
        'charset' => 'utf8'
    ];

    /**
     * PDO instance
     * @var object
     */
    private $pdo;

    /**
     * Static instance of self
     * @var object
     */
    private static $instance;

    /**
     * Dynamic table control (on/off)
     * @var boolean
     */
    private $checkTable = true;

    /**
     * Dynamic column control (on/off)
     * @var boolean
     */
    private $checkColumn = true;

    /**
     * SQL query
     * @var string
     */
    private $query;

    /**
     * Array that holds query result
     * @var array
     */
    private $queryResult;

    /**
     * Action for query string
     * @var string
     */
    private $action;

    /**
     * Table name
     * @var string
     */
    private $table;

    /**
     * Array that holds Insert/Update values
     * @var array
     */
    private $values = [];

    /**
     * Array that holds Where conditions (And)
     * @var array
     */
    private $where = [];

    /**
     * Array that holds Where conditions (Or)
     * @var array
     */
    private $orWhere = [];

    /**
     * Array that holds Where values
     * @var array
     */
    private $whereValues = [];

    /**
     * Dynamic type list for Group By condition value
     * @var string
     */
    private $groupBy;

    /**
     * Dynamic type list for Having condition value
     * @var string
     */
    private $having;

    /**
     * Dynamic type list for Order By condition value
     * @var array
     */
    private $orderBy = [];

    /**
     * Limit condition value for SQL query
     * @var string
     */
    private $limit;

    /**
     * Value of the auto increment column
     * @var integer
     */
    private $lastInsertId = 0;

    /**
     * Number of affected rows
     * @var integer
     */
    private $rowCount = 0;

    /**
     * @param string|array|object $type
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $dbname
     * @param integer $port
     * @param string $charset
     */
    public function __construct($type = null, $host = null, $username = null, $password = null, $dbname = null, $port = null, $charset = null) {
        set_exception_handler(function($exception) {
            echo '<b>[SunClass] Exception:</b> '.$exception->getMessage();
        });
        if (is_array($type)) { // connect to db using parameters in the array
            $this->connectionParams = $type;
        } else if (is_object($type)) { // connect to db using pdo object
            $this->pdo = $type;
        } else { // connect to db using parameters
            foreach ($this->connectionParams as $key => $value) {
                if (isset($$key) && !is_null($$key)) {
                    $this->connectionParams[$key] = $$key;
                }
            }
        }
        self::$instance = $this;
    }

    /**
     * Create PDO connection
     *
     * @throws exception
     * @return pdo
     */
    private function connect() {
        if (empty($this->connectionParams['driver'])) {
            throw new \Exception('Database Driver is not set.');
        }
        if ($this->connectionParams['driver'] == 'sqlite') {
            $connectionString = 'sqlite:' . $this->connectionParams['url'];
            $this->pdo = new \PDO($connectionString);

        } else if ($this->connectionParams['driver'] == 'mssql') {
            $connectionString = 'sqlsrv:Server='.$this->connectionParams['host'].';Database='.$this->connectionParams['dbname'];
            $this->pdo = new \PDO($connectionString, $this->connectionParams['username'], $this->connectionParams['password']);
        } else {
            $connectionString = $this->connectionParams['driver'].':';
            $connectionParams = ['host', 'dbname', 'port', 'charset'];
            foreach ($connectionParams as $connectionParam) {
                if (!empty($this->connectionParams[$connectionParam])) {
                    $connectionString .= $connectionParam.'='.$this->connectionParams[$connectionParam].';';
                }
            }
            $connectionString = rtrim($connectionString, ';');
            $this->pdo = new \PDO($connectionString, $this->connectionParams['username'], $this->connectionParams['password']);
        }
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $this->pdo->setAttribute(\PDO::ATTR_CURSOR, \PDO::CURSOR_SCROLL);
        $this->pdo->setAttribute(\PDO::ATTR_ORACLE_NULLS, \PDO::NULL_EMPTY_STRING);
        $this->pdo->setAttribute(\PDO::ATTR_PERSISTENT, true);
        if ($this->connectionParams['driver'] == 'mysql') {
          $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
          $this->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
          $this->pdo->setAttribute(\PDO::MYSQL_ATTR_FOUND_ROWS, true);
          $this->pdo->setAttribute(\PDO::MYSQL_ATTR_INIT_COMMAND, 'SET CHARACTER SET utf8, NAMES utf8');
        }
        if (!($this->pdo instanceof \PDO)) {
            throw new \Exception('This object is not an instance of PDO.');
        }
    }

    /**
     * Check/Call PDO connection
     *
     * @throws exception
     * @return object
     */
    private function pdo() {
        if (!$this->pdo) {
            $this->connect(); // call connection method
        }
        if (!($this->pdo instanceof \PDO)) {
            throw new \Exception('This object is not an instance of PDO.');
        }
        return $this->pdo;
    }

    /**
     * Reset SunDB internal variables
     */
    private function reset() {
        $this->query        = '';
        $this->action       = '';
        $this->table        = '';
        $this->values       = [];
        $this->where        = [];
        $this->orWhere      = [];
        $this->whereValues  = [];
        $this->groupBy      = '';
        $this->having       = '';
        $this->orderBy      = [];
        $this->limit        = '';
        $this->lastInsertId = 0;
        $this->rowCount     = 0;
    }

    /**
     * Check if table exists
     *
     * @param string $table
     * @throws exception
     * @return boolean
     */
    private function checkTable($table = null) {
        $result = $this->pdo()->query("SHOW TABLES LIKE '".$table."'");
        if ($result->rowCount() != 1) {
            throw new \Exception('Table "'.$table.'" does not exist.');
        }
    }

    /**
     * Check if column exists
     *
     * @param string $column
     * @throws exception
     * @return boolean
     */
    private function checkColumn($column = null) {
        $result = $this->pdo()->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".$this->connectionParams['dbname']."' AND TABLE_NAME = '".$this->table."' AND COLUMN_NAME = '".$column."'");
        if ($result->rowCount() != 1) {
            throw new \Exception('Column "'.$column.'" does not exist.');
        }
    }

    /**
     * Build a SELECT part of the query
     *
     * @param string $table
     * @param string|array $columns
     * @return object
     */
    public function select($table = null, $columns = '*') {
        $this->reset();
        if ($this->connectionParams['driver'] != 'sqlite' && $this->checkTable) {
            $this->checkTable($table);
        }
        if (is_array($columns) && count($columns) > 0) {
            $columns = implode(',', $columns);
        } else {
            $columns = '*';
        }
        $this->table = $table;
        $this->action = 'select';
        $this->query = 'select '.$columns.' from `'.$table.'`';
        return $this;
    }
    
    /**
     * Build an INSERT part of the query
     *
     * @param string $table
     * @param array $data
     * @throws exception
     * @return object
     */
    public function insert($table = null, $data = []) {
        $this->reset();
        if ($this->connectionParams['driver'] != 'sqlite' && $this->checkTable) {
            $this->checkTable($table);
        }
        if (!is_array($data) || count($data) <= 0) {
            throw new \Exception('Insert clause must contain an array data.');
        }
        foreach ($data as $key => $value) {
            $keys[] = '`'.$key.'`';
            $alias[] = '?';
            if (empty($value)) {$value = NULL;}
            $this->values[] = $value;
        }
        $strKeys = implode(',', $keys);
        $strAlias = implode(',', $alias);
        $this->table = $table;
        $this->action = 'insert';
        if (is_int($keys[0])) {
            $this->query = 'insert into `'.$table.'` ('.$strKeys.') values ('.$strAlias.')';
        } else {
            $this->query = 'insert into `'.$table.'` values ('.$strAlias.')';
        }
        return $this;
    }

    /**
     * Build an UPDATE part of the query
     *
     * @param string $table
     * @param string|array $data
     * @throws exception
     * @return object
     */
    public function update($table = null, $data = []) {
        $this->reset();
        if ($this->connectionParams['driver'] != 'sqlite' && $this->checkTable) {
            $this->checkTable($table);
        }
        if (!is_array($data) || count($data) <= 0) {
            throw new \Exception('Update clause must contain an array data.');
        }
        foreach ($data as $key => $value) {
            $keys[] = '`'.$key.'`=?';
            if (empty($value)) {$value = '';}
            $this->values[] = $value;
        }
        $keys = implode(',', $keys);
        $this->table = $table;
        $this->action = 'update';
        $this->query = 'update `'.$table.'` set '.$keys;
        return $this;
    }

    /**
     * Build a DELETE part of the query
     *
     * @param string $table
     * @return object
     */
    public function delete($table = null) {
        $this->reset();
        if ($this->connectionParams['driver'] != 'sqlite' && $this->checkTable) {
            $this->checkTable($table);
        }
        $this->table = $table;
        $this->action = 'delete';
        $this->query = 'delete from `'.$table.'`';
        return $this;
    }

    /**
     * Build the AND WHERE part of the query string
     *
     * @param string $column
     * @param string $value
     * @param string $operator
     * @param string $condition
     * @throws exception
     * @return object
     */
    public function where($column = null, $value = null, $operator = null, $condition = 'and') {
        if (empty($value) && empty($operator)) {
            $this->where[] = $condition.' '.$column;
        } else {
            if (empty($column) || empty($operator)) {
                throw new \Exception('Where clause must contain a value and operator.');
            }
            if ($this->connectionParams['driver'] != 'sqlite' && $this->checkColumn) {
                $this->checkColumn($column);
            }
            if ($operator == 'between' || $operator == 'not between') {
                if (!empty($value[0]) && !empty($value[1])) {
                    $this->whereValues[] = $value[0];
                    $this->whereValues[] = $value[1];
                    $this->where[] = $condition.' (`'.$column.'` '.$operator.' ? and ?)';
                }
            } else if ($operator == 'in' || $operator == 'not in') {
                if (is_array($value) && count($value)>0) {
                    foreach ($value as $val) {
                        $values[] = '?';
                        $this->whereValues[] = $val;
                    }
                    $this->where[] = $condition.' (`'.$column.'` '.$operator.' ('.implode(',', $values).'))';
                }
            } else {
                $this->where[] = $condition.' (`'.$column.'`'.$operator.'?) ';
                if (empty($value)) {$value = '';}
                $this->whereValues[] = $value;
            }
        }
        return $this;
    }

    /**
     * Build the OR WHERE part of the query string
     *
     * @param string $column
     * @param string $value
     * @param string $operator
     * @throws exception
     * @return object
     */
    public function orWhere($column = null, $value = null, $operator = null) {
        return $this->where($column, $value, $operator, 'or');
    }

    /**
     * Build the GROUP BY part of the WHERE statement
     *
     * @param string $column
     * @throws exception
     * @return object
     */
    public function groupBy($column = null) {
        if (empty($column)) {
            throw new \Exception('Group By clause must contain a column name.');
        }
        if ($this->connectionParams['driver'] != 'sqlite' && $this->checkColumn) {
            $this->checkColumn($column);
        }
        $this->groupBy = '`'.$column.'`';
        return $this;
    }

    /**
     * Build the HAVING part of the GROUP BY clause
     *
     * @param string $value
     * @throws exception
     * @return object
     */
    public function having($value = null) {
        if (empty($value)) {
            throw new \Exception('Having clause must contain a value.');
        }
        if ($this->connectionParams['driver'] != 'sqlite') {
            $this->having = $value;
        }
        return $this;
    }
    
    /**
     * Build the ORDER BY part of the WHERE statement
     *
     * @param string $column
     * @param string $order
     * @throws exception
     * @return object
     */
    public function orderBy($column = null, $order = null) {
        if (strpos(strtoupper($column), 'RAND') !== false && empty($order)) {
            $this->orderBy[] = $column;
        } else {
            if (empty($column) || !in_array(strtoupper($order), ['ASC', 'DESC'], true)) {
                throw new \Exception('Order By clause must contain a column name and order value.');
            }
            if ($this->connectionParams['driver'] != 'sqlite' && $this->checkColumn) {
                $this->checkColumn($column);
            }
            $this->orderBy[] = '`'.$column.'` '.$order;
        }
        return $this;
    }

    /**
     * Build the LIMIT part of the WHERE statement
     *
     * @param integer $start
     * @param integer $page
     * @throws exception
     * @return object
     */
    public function limit($start = 0, $page = null) {
        if (!is_int($start)) {
            throw new \Exception('Limit clause must be 0 or above.');
        }
        if (empty($page) || !is_int($page)) {
            $page = $start;
            $start = 0;
        }
        $this->limit = $start.','.$page;
        return $this;
    }

    /**
     * Perform SQL query
     *
     * @param string $query
     * @param array $values
     * @return object
     */
    public function rawQuery($query = null, $values = []) {
        $this->reset();
        if (is_array($values) && count($values)>0) {
            foreach ($values as $value) {
                if (empty($value)) {$value = '';}
                $this->values[] = $value;
            }
        }
        $this->action = 'query';
        $this->query = $query;
        return $this;
    }

    /**
     * Compile/Execute the SQL query and return the result
     *
     * @throws exception
     * @return array|object|boolean
     */
    public function run() {
        if (is_array($this->where) && count($this->where) > 0) { // add Where condition
            $count = 0;
            $clnWhere = array();
            foreach ($this->where as $key => $value) { // remove first And/OR part 
                $count++;
                if ($count == 1) {
                    $clnWhere[] = ltrim(ltrim($value, 'or'), 'and');
                } else {
                    $clnWhere[] = $value;
                }
            }
            $this->query .= ' where '.implode('', $clnWhere).'';
        }
        if (!empty($this->groupBy)) { // add Group By condition
            $this->query .= ' group by '.$this->groupBy;
        }
        if (!empty($this->groupBy) && !empty($this->having)) { // add Having condition
            $this->query .= ' having '.$this->having;
        }
        if (is_array($this->orderBy) && count($this->orderBy) > 0) { // add Order By condition
            $this->query .= ' order by '.implode(',', $this->orderBy);
        }
        if (!empty($this->limit)) { // add Limit condition
            $this->query .= ' limit '.$this->limit;
        }
        switch ($this->action) {
            case 'select': // run Select query and return the result (array|object)
                $query = $this->pdo()->prepare($this->query);
                $result = $query->execute($this->whereValues);
                $this->queryResult = $query->fetchAll();
                $this->rowCount = $query->rowCount(); // affected row count
                $query->closeCursor(); unset($query);
                if ($this->rowCount == 1) {
                    return $this->queryResult[0];
                } else {
                    return $this->queryResult;
                }
            break;
            case 'insert': // run Insert query and return the result (bool)
                $query = $this->pdo()->prepare($this->query);
                $result = $query->execute($this->values);
                $this->rowCount = $query->rowCount(); // affected row count
                $this->lastInsertId = $this->pdo()->lastInsertId(); // auto increment value
                $query->closeCursor(); unset($query);
                return $result;
            break;
            case 'update': // run Update query and return the result (bool)
                $query = $this->pdo()->prepare($this->query);
                $result = $query->execute(array_merge($this->values,$this->whereValues));
                $this->rowCount = $query->rowCount(); // affected row count
                $query->closeCursor(); unset($query);
                return $result;
            break;
            case 'delete': // run Delete query and return the result (bool)
                $query = $this->pdo()->prepare($this->query);
                $result = $query->execute($this->whereValues);
                $this->rowCount = $query->rowCount(); // affected row count
                $query->closeCursor(); unset($query);
                return $result;
            break;
            case 'query': // run Raw query and return the result (bool)
                $query = $this->pdo()->prepare($this->query);
                $result = $query->execute($this->values);
                $this->rowCount = $query->rowCount(); // affected row count
                $exp = explode(' ', $this->query); // for determine the action
                if ($exp[0] == 'select') {
                    $this->queryResult = $query->fetchAll();
                    $query->closeCursor(); unset($query);
                    return $this->queryResult;
                } else {
                    $query->closeCursor(); unset($query);
                    return $result;
                }
            break;
            default:
                throw new \Exception('Command "'.$this->action.'" is not allowed.');
            break;
        }
    }

    /**
     * Perform backup the database and print/download backup file
     *
     * @param string $fileName
     * @param string $action
     * @param array $excludeTables
     * @throws exception
     * @return string|file
     */
    public function backup($fileName = null, $action = null, $excludeTables = []) {
        if ($this->connectionParams['driver'] == 'sqlite') {
            throw new \Exception('SQLite database backup is not allowed. Download "'.$this->connectionParams['url'].'" file directly.');
        }
        if (empty($fileName)) {$fileName = 'SunDB-Backup-'.date("dmYHis").'.sql';} else {$fileName .= '.sql';} // define file name
        if (empty($action)) {$action = 'save';} // default action
        if ($action == 'save') { // if selected the Save method
            header('Content-disposition: attachment; filename='.$fileName);
            header('Content-type: application/force-download'); // header for download
        }
        $show = $this->pdo()->query('show tables')->fetchAll(); // list all tables
        $tables = [];
        foreach ($show as $rows) {
            $content = [];
            $table = reset($rows);
            if (!in_array($table, $excludeTables)) {
                $create = $this->pdo()->query("show create table `$table`")->fetchAll(); // list table structures
                $content[] = $create[0]['Create Table'].";\n"; // select Create Table structure
                $query = $this->pdo()->prepare("select * from `$table`"); // list all values in selected table
                $query->execute(array());
                $select = $query->fetchAll();
                if ($query->rowCount() > 0) {
                    foreach ($select as $row) {
                        if (count($row) < 1) {continue;}
                        $header = "INSERT INTO `$table` VALUES ('"; // add Insert query
                        $body = implode("', '", array_values($row)); // add listed values
                        $footer = "');";
                        $content[] = $header.$body.$footer;
                    }
                    if (count($content) < 1) {continue;}
                    $tables[$table] = implode("\n", $content);
                }
            }
        }
        if ($action == 'save') {
            echo "# SunDB Database Backup File\n# Backup Date: ".date("Y-m-d H:i:s")."\n# Backup File: ".$fileName."\n\n\n";
            echo implode("\n\n", array_values($tables));
        } else { // if selected the Show method
            echo nl2br(implode('<br><br>', array_values($tables)));
        }
    }

    /**
     * Analyze, check, optimize and repair tables
     * 
     * @throws exception
     * @return boolean
     */
    public function maintenance() {
        $tables = [];
        $show = $this->pdo()->query('show tables')->fetchAll(); // list tables
        foreach ($show as $rows) {
            if (!is_array($rows)) continue;
            if (count($rows) < 1) continue;
            $tables[] = $this->connectionParams['dbname'] . '.' . reset($rows);
        }
        if (count($tables) > 0) {
            $tables = implode(', ', $tables);
            try {
                $analyze = $this->pdo()->query("analyze table $tables"); // analyze tables
                $check = $this->pdo()->query("check table $tables"); // check tables
                $optimize = $this->pdo()->query("optimize table $tables"); // optimize tables
                $repair = $this->pdo()->query("repair table $tables"); // repair tables
            } catch (Exception $e) {
                throw new \Exception($e->getMessage());
            }
            if ($analyze && $check && $optimize && $repair) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Return a static instance of connection
     *
     * @return object
     */
    public static function getInstance() {
        return self::$instance;
    }

    /**
     * Show/Print executed query as a string
     */
    public function showQuery() {
        if (empty($this->query)) {
            echo '<b>[SunDB] Error:</b> SQL query not found.';
        } else {
            if ($this->action == 'query') {
                echo '<p><b>[SunDB] Query:</b> ' . $this->query . '</p>';
            } else {
                $queryArray = explode('?', $this->query);
                for ($i = 0; $i < count($queryArray)-1; $i++) {
                    $result .= $queryArray[$i] . "'" . $this->whereValues[$i] . "'";
                }
                $result .= $queryArray[count($queryArray)-1];
                echo '<p><b>[SunDB] Query:</b> ' . $result . '</p>';
            }
        }
    }

    /**
     * Return the total record count in a table
     *
     * @param string $table
     * @return integer
     */
    public function tableCount($table = null) {
        if ($this->connectionParams['driver'] != 'sqlite' && $this->checkTable) {
            $this->checkTable($table);
        }
        $query = $this->pdo()->query('select count(*) as total from '.$table)->fetchAll();
        return (int) $query[0]['total'];
    }

    /**
     * Return the number of affected rows
     *
     * @return integer
     */
    public function rowCount(){
        return (int) $this->rowCount;
    }

    /**
     * Return the value of the auto increment column
     *
     * @return integer
     */
    public function lastInsertId() {
        return (int) $this->lastInsertId;
    }

    /**
     * Generate user defined function call
     *
     * @param string $func
     * @param string $param
     * @throws exception
     * @return string
     */
    public function func($func = null, $param = null) {
        if (empty($func) || empty($param)) {
            throw new \Exception('Missing parameters for "' . $func . '" function.');
        }
        return $func($param);
    }

}

?>
