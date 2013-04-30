<?php
/**                                                                      
 * mysql
 * adapter for connecting to mysql database servers. sql queries are automatically sanitized for injection protection.
 * 
 * @author      xero harrison <x@xero.nu>
 * @copyright   creative commons attribution-shareAlike 3.0 unported
 * @license     http://creativecommons.org/licenses/by-sa/3.0/ 
 * @version     2.42
 */
namespace qoob\core\db;
class mysql {
    /**
     * error constants
     */
    const
        E_Server = 'Failed to connect: %s',
        E_Database = 'Failed to select database: %s';
    protected 
        /**
         * @var object $db the database reference
         */
        $db = null,
        /**
         * @var string $sql the sql query
         */    
        $sql = null;
    private
        /**
         * @var string $dbhost the database hostname
         */
        $dbhost,
        /**
         * @var string $dbuser the database username
         */
        $dbuser,
        /**
         * @var string $dbpass the database password
         */
        $dbpass,
        /**
         * @var string $dbname the database name
         */
        $dbname,
        /**
         * @var bool $asciiOnly true will allow only ascii characters, false will allow all printable characters
         */
        $asciiOnly = true,
        /**
         * @var bool $keepAlive true will not close the mySQL connection on class destruction
         */
        $keepAlive = false,
        /**
         * @var int $count mysql_num_rows result
         */
        $count = 0;

    /**
     * initializer
     * set the database connection variables and optionally the asciiOnly variable
     *
     * @param string $db_host
     * @param string $db_user
     * @param string $db_pass
     * @param string $db_name  
     * @param boolean $asciiOnly default = true
     */
    public function init($db_host, $db_user, $db_pass, $db_name, $asciiOnly=true, $keepAlive=false) {
        $this->dbhost = $db_host;
        $this->dbuser = $db_user;
        $this->dbpass = $db_pass;
        $this->dbname = $db_name;
        $this->asciiOnly = $asciiOnly;
        $this->keepAlive = $keepAlive;
    }
    /**
     * is ascii
     * set the asciiOnly variable to true and allow only ascii characters in sql queries, false will allow all printable characters.
     *
     * @param boolean $asciiOnly default = true
     */
    public function isAscii($asciiOnly) {
        $this->asciiOnly = $asciiOnly;
    }
    /**
     * connect
     * connect to a mysql server and selects the appropriate database. throw a dbException on failure.
     */
    public function connect() {
        if(($db = @mysql_connect($this->dbhost, $this->dbuser, $this->dbpass)) === false) {
            throw new dbException(sprintf(self::E_Server, $this->dbuser.'@'.$this->dbhost));
        }
        
        if((@mysql_select_db($this->dbname, $db)) === false) {
            throw new dbException(sprintf(self::E_Database, $this->dbname));
        }
        $this->db = $db;
    }
    /**
     * reconnect function
     * connect to a new database server and optionally disconnect from the old one.
     *
     * @param string $db_host
     * @param string $db_user
     * @param string $db_pass
     * @param string $db_name
     * @param boolean $closeOld
     */
    public function reconnect($db_host, $db_user, $db_pass, $db_name, $closeOld=true) {
        if($closeOld){
            mysql_close($this->db);
        }
        $this->init($db_host, $db_user, $db_pass, $db_name);
        $this->connect();
    }
    /**
     * sanitize
     * mitigate attack vectors by removing offending slashes, removing non printable characters, and filtering it against the mysql server's own escape function.
     * 
     * @param string $string
     * @return string
     */
    public function sanitize($string) {
        if(get_magic_quotes_gpc()) {
            $string = stripslashes($string);
        }
        $filtered = $this->asciiOnly ? trim(preg_replace('/[^\x0A\x0D\x20-\x7E]/', '', $string)) : trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $string));
        return mysql_real_escape_string($filtered);
    }
    /**
     * SQL query function
     * executes a mysql query. make sure all insert, and update statements have the results flag set to false.
     * 
     * @param string $sql
     * @param array $args
     * @param boolean $results
     * @param boolean $count
     * @return object|boolean
     */
    public function query($sql, $args, $results = true, $count = false) {
        $find = array();
        $replace = array();
        foreach ($args as $key => $value) {
            $find[] = '/:'.$key.'/'; 
            $replace[] = $this->sanitize($value);
        }
        $this->sql = preg_replace($find, $replace, $sql);
        $query = new mysqlQuery($this->sql, $this->db);
        $this->count = $count ? $query->num_rows() : 0;
        if($results) {
            return $query->result();
        } else {
            return true;
        }
    }
    /**
     * get insertID
     * get the last inserted record's id
     * 
     * @return int|string
     */
    public function insertID() {
        return mysql_insert_id($this->db);
    }
    /**
     * get num_rows
     * returns the numbers of rows from a query
     * 
     * @return int|string
     */
    public function num_rows() {
        return $this->count;
    }
    /**
     * destructor
     * if keepAlive is false close the connection when finished
     */
    public function __destruct() {
        if(!$this->keepAlive) {
            @mysql_close($this->db);
        }
    }
}
/**
 * mysql query
 * class for executing queries and handling mysql results.
 */
class mysqlQuery {
    protected $result;    
    /**
     * constructor
     * gets the results of the mysql query or throws a dbException error
     * 
     * @param string $query
     * @param object $link mysql_connection
     */
    public function __construct($query, $link) {
        if(($this->result = @mysql_query($query, $link)) === false) {
            throw new dbException($query, 500);
        }
    }
    /**
     * get result
     * returns the results of the mysql query
     * 
     * @return array
     */
    public function result() {
        $result = array();              
        while (($row = @mysql_fetch_assoc($this->result)) != false) {
            $result[] = $row;
        }
        return $result;
    }
    /**
     * number of rows
     * returns the number of rows in a given result
     * 
     * @return int
     */
    public function num_rows() {
        return @mysql_num_rows($this->result);
    }    /**
     * destructor
     * call's free result only if one has been created
     */
    public function __destruct() {
        if(is_array($this->result)) {
            @mysql_free_result($this->result);
        }
    }
}
/**
 * database exception
 *
 */
class dbException extends \Exception {
    /**
     * constructor
     * sets the error code and message
     * 
     * @param string $message
     * @param int $code 500
     */
    public function __construct($message, $code = 500) {
        $this->code = $code;
        $this->message = mysql_error().PHP_EOL."<br/><br/>".PHP_EOL.$message;
    }
}
?>