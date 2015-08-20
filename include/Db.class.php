<?php

/**
 * Wrapper over MySQLi php interface
 */
class Db {

    private $link;
    private $host;
    private $port;
    private $current_db = null;
    private $mysql_version;
    private $mysql_subversion;
    private $mysql_subversion2;

    const ASSOC = MYSQLI_ASSOC;
    const NUM = MYSQLI_NUM;
    const BOTH = MYSQLI_BOTH;

    private static $instance;


    /**
     * Constructor: creates a new query handler
     * <br />Exemple of use:
     * <br />
     * <code>
     * <br />$handler = new WPU_db(host, username, password);
     * <br />$query = $handler->query('SELECT NOW()');
     * <br />list($date) = WPU_db::fetch_row($query);
     * <br />WPU_db::free_result($query);
     * </code>
     *
     * @param	string $host	Server host
     * @param	string $username	MySQL username
     * @param	string $password	MySQL password
     */
    public function  __construct($host, $username, $password, $port = null) {
        if (!extension_loaded('mysqli')) {
            throw new Exception('You need mysqli extension to use this software !');
        }
        $this->host = $host;
        $this->port = $port;
        if ($this->port) {
            $this->link = @mysqli_connect($host, $username, $password, '', $this->port);
        } else {
            $this->link = @mysqli_connect($host, $username, $password);
        }
        if ($this->link) {
            $ver = explode('-', $this->get_server_info());
            list($ver, $sub1, $sub2) = explode('.', $ver[0]);
            $this->mysql_version = $ver;
            $this->mysql_subversion = $sub1;
            $this->mysql_subversion2 = $sub2;
        }
    }

    /**
     * Return MySQL handler
     *
     * @return Db
     */
    static public function getDb() {

        if (self::$instance == null) {
            self::$instance = new self(DB_HOST, DB_USER, DB_PASSWORD, DB_PORT);
        }

        self::$instance->query('SET NAMES UTF8');
        self::$instance->query("SET time_zone = '+01:00'");
        self::$instance->query('USE ' . DB_NAME);

        return self::$instance;
    }

    /**
     * Move internal result pointer
     * Adjusts the result pointer to an arbitary row in the result
     *
     * @param	mysqli_result $result	mysqli_result object
     * @param	int $offset	The field offset
     * @return	bool true in case on success
     */
    static public function data_seek($result, $offset) {
        return mysqli_data_seek($result, $offset);
    }

    /**
     * Frees the memory associated with a result
     *
     * @param	mysqli_result $result	mysqli_result object
     * @return	void
     */
    static public function free_result($result) {
        mysqli_free_result($result);
    }

    /**
     * Get a result row as an enumerated array
     *
     * @param	mysqli_result $result	mysqli_result object
     * @return	array with query results (keys are db columns)
     */
    static public function fetch_row($result) {
        return mysqli_fetch_row($result);
    }

    /**
     * Fetch a result row as an associative, a numeric array, or both
     *
     * @param	mysqli_result $result	mysqli_result object
     * @param	$result_type	PHP options
     * @return	Array with query results
     */
    static public function fetch_array($result, $result_type = MYSQLI_BOTH) {
        return mysqli_fetch_array($result, $result_type);
    }

    /**
     * Number of rows on the query result
     *
     * @param	$result	mysqli_result object
     * @return	Number of rows returned by MySQL
     */
    static public function num_rows($result) {
        return mysqli_num_rows($result);
    }

    /**
     * Server host
     *
     * @return	string server host
     */
    function get_host() {
        return $this->host;
    }

    /**
     * PHP internal link to MySQLi connexion
     *
     * @return	mysqli PHP internal link
     */
    function get_link() {
        return $this->link;
    }

    /**
     * MySQL version
     *
     * @return	string MySQL version
     */
    function get_mysql_version() {
        return $this->mysql_version;
    }


    /**
     * MySQL subversion
     *
     * @return	string MySQL subversion
     */
    function get_mysql_subversion() {
        return $this->mysql_subversion;
    }

    /**
     * MySQL second subversion
     *
     * @return	string MySQL second subversion
     */
    function get_mysql_subversion2() {
        return $this->mysql_subversion2;
    }


    /**
     * Security function
     * Escape string to avoid SQL injection
     *
     * Use :
     * $sql = 'UPDATE field = "'.$handler->realEscapeString($text).'" FROM table';
     *
     * @param	string $text	Unsafe text
     * @return	string Safe text
     */
    function real_escape_string($text) {
        return mysqli_real_escape_string($this->link, $text);
    }

    /**
     * Get MySQL server informations
     *
     * @return	string with informations about MySQL server.
     *			 Referer to PHP documentation about "mysqli_get_server_info"
     */
    function get_server_info() {
        return mysqli_get_server_info($this->link);
    }

    /**
     * Get MySQL host infos
     *
     * @return	string with informations about MySQL host.
     *			 Referer to PHP documentation about "mysqli_get_host_info"
     */
    function get_host_info() {
        return mysqli_get_host_info($this->link);
    }

    /**
     * Launch SQL query
     *
     * @param	string $query	SQL query, with escaped datas!
     * @return	mixed mysqli_result object
     */
    function query($query) {
        //echo $query;
        $res = mysqli_query($this->link, $query);
        if (!$res && intval(ini_get('mysqli.reconnect')) == 1) {
            if (mysqli_errno($this->link) == '2006') { /* mysql has gone away */
                $result = mysqli_ping($this->link);
                if ($result) {
                    $res = mysqli_query($this->link, $query);
                }
            }
        }
        return $res;
    }

    /**
     * Launch SQL query, without PHP buffering
     *
     * @param	string $query	SQL query, with escaped datas!
     * @return	mixed mysqli_result object
     */
    function unbuffered_query($query) {
        $res = mysqli_query($this->link, $query, MYSQLI_USE_RESULT);
        if (!$res && intval(ini_get('mysqli.reconnect')) == 1) {
            if (mysqli_errno($this->link) == '2006') { /* mysql has gone away */
                $result = mysqli_ping($this->link);
                if ($result) {
                    $res = mysqli_query($this->link, $query, MYSQLI_USE_RESULT);
                }
            }
        }
        if (!$res) {
            $this->failure($query);
        }
        return $res;
    }

    /**
     * Affected rows by the last query (on this handler)
     *
     * @return	int Count of affected rows by the previous query
     */
    function affected_rows() {
        return mysqli_affected_rows($this->link);
    }


    /**
     * Change selected database
     *
     * @param	string $database	Database name
     * @return	boolean Returns true in case on success
     */
    function select_db($database) {
        if ($this->current_db != $database) {
            $return = mysqli_select_db($this->link, $database);
            if ($return) {
                $this->current_db = $database;
            }
            return $return;
        } else {
            return true;
        }
    }
    /**
     * Close connexion
     *
     * @return	boolean Returns true in case on success
     */
    function close() {
        return mysqli_close($this->link);
    }

    /**
     * Last error
     *
     * @return	string Text of the last MySQL error
     */
    function error() {
        return mysqli_error($this->link);
    }

    /**
     * Last auto increment value generated (on this handler)
     *
     * @return	int Last auto increment value
     */
    function insert_id() {
        return mysqli_insert_id($this->link);
    }
}

?>