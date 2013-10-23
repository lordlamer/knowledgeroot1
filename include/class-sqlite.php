<?php
/**
 *
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-sqlite.php 941 2010-06-07 20:57:58Z lordlamer $
 */

$KNOWLEDGEROOTDB = 'SQLITE';

/**
 * Class for Connect to Mysql
 */
class db extends db_core {
	var $dbtype = "sqlite";
	var $dbname = "sqlite";
	var $mode = "0666";

	/**
	 * Make a Connect to the mysql Server
	 *
	 * @param string $host
	 * @param string $user
	 * @param string $pass
	 * @param string $db
	 * @param string $schema not required
	 * @param string $enconding not required
	 * @return mixed return connection resource
	 */
	function connect($host,$user,$pass,$db,$schema="",$encoding="") {
		if($this->CLASS['config']->db->pconnect) {
			$this->connection = sqlite_popen($db, $this->mode);
		} else {
			$this->connection = sqlite_open($db, $this->mode);
		}

		if(!$this->connection) {
			$this->CLASS['error']->log("Cannnot connect to host!",1,"class-sqlite.php::connect");
			exit();
		}

		return $this->connection;
	}

	/**
	 * Close mysql connection
	 * @return bool
	 */
	function close() {
		return sqlite_close($this->connection);
	}

	/**
	 * Will make a query with the mysql server
	 * @param string $query
	 * @return mixed return query result
	 */
	function query($query) {
		$this->lastquery = $query;
		$this->query_cache[] = $query;
		$this->querys += 1;

		$res = new db_result($this->CLASS);
		$res->setQuery($query);
		$res->setResult(sqlite_query($query, $this->connection));

		if($res->getResult() === false) {
			$this->CLASS['error']->log("ERROR IN QUERY: \"$query\"",1,sqlite_errno() . ":".sqlite_errno());
		}

		return $res;
	}

	/**
	 * Will count the rows of a resultset
	 * @param mixed $result
	 * @return int
	 */
	function num_rows($result) {
		return sqlite_num_rows($result->getResult());
	}

	function _cleanName($array) {
		if(is_array($array)) {
			foreach ($array as $key => $value) {
				unset($array[$key]);
				if(strpos($key, '.') > 0) {
					$key = substr($key, strpos($key, '.')+1);
				}
				$array[$key] = $value;
			}
		}

		return $array;
	}

	/**
	 * Fetch a Result as Object
	 * @param mixed $result
	 * @return object
	 */
	function fetch_object($result) {
		return sqlite_fetch_object($result->getResult());
	}

	/**
	 * Return Result as Array
	 * @param mixed $result
	 * @return array
	 */
	function fetch_row($result) {
		$row = sqlite_fetch_array($result->getResult(), SQLITE_NUM);
		return $this->_cleanName($row);
	}

	/**
	 * Return Result as hash array
	 * @param mixed result
	 * @return array
	 */
	function fetch_assoc($result) {
		$row = sqlite_fetch_array($result->getResult(), SQLITE_ASSOC);
		return $this->_cleanName($row);
	}

	/**
	 * Return affected rows of a result
	 * @param mixed $result
	 * @return int
	 */
	function affected_rows($result) {
		return false;
	}

	/**
	 * Returns the text of the error message from previous MySQL operation
	 * @return string
	 */
	function error() {
		return sqlite_last_error($this->connection);
	}

	/**
	 * Return the last inserted id from a query
	 * @param string $name not required
	 * @return int
	 */
	function last_id($name = "") {
		return sqlite_last_insert_rowid($this->connection);
	}

	/**
	 * Quote a String with Mysql Quotes
	 * @param string $name
	 * @return string
	 */
	function quoteIdentifier($string) {
		return "\"" . $string . "\"";
	}

	/**
	 * do data seek on result
	 * @param resource $result
	 * @param integer $number
	 * @return bool
	 */
	function data_seek($result, $number) {
		return sqlite_seek($result->getResult(), $number);
	}
}

?>
