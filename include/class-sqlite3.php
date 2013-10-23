<?php
/**
 *
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-sqlite3.php 860 2009-09-25 12:15:19Z lordlamer $
 */

$KNOWLEDGEROOTDB = 'SQLITE3';

/**
 * Class for Connect to Mysql
 */
class db extends db_core {
	var $dbtype = "sqlite3";
	var $dbname = "sqlite3";
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
		$this->connection = new SQLite3($db, SQLITE3_OPEN_READWRITE);

		if(!$this->connection) {
			$this->CLASS['error']->log("Cannnot connect to host!",1,"class-sqlite3.php::connect");
			exit();
		}

		return $this->connection;
	}

	/**
	 * Close mysql connection
	 * @return bool
	 */
	function close() {
		return $this->connection->close();
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
		$res->setResult($this->connection->query($query));

		if($res->getResult() === false) {
			echo "#".$query."#";
			$this->CLASS['error']->log("ERROR IN QUERY: \"$query\"",1,$this->connection->lastErrorMsg() . ":".$this->connection->lastErrorCode());
		}

		return $res;
	}

	/**
	 * Will count the rows of a resultset
	 * @param mixed $result
	 * @return int
	 */
	function num_rows($result) {
		$cnt = 0;

		while($row = $this->fetch_assoc($result)) {
			$cnt++;
		}
		$this->data_seek($result);

		return $cnt;
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
		return $result->getResult()->fetchArray(SQLITE3_ASSOC);
	}

	/**
	 * Return Result as Array
	 * @param mixed $result
	 * @return array
	 */
	function fetch_row($result) {
		$row = $result->getResult()->fetchArray(SQLITE3_NUM);
		return $this->_cleanName($row);
	}

	/**
	 * Return Result as hash array
	 * @param mixed result
	 * @return array
	 */
	function fetch_assoc($result) {
		$row = $result->getResult()->fetchArray(SQLITE3_ASSOC);
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
		return $this->connection->lastErrorMessage;
	}

	/**
	 * Return the last inserted id from a query
	 * @param string $name not required
	 * @return int
	 */
	function last_id($name = "") {
		return $this->connection->lastInsertRowID();
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
	function data_seek($result, $number = null) {
		return $result->getResult()->reset();
	}
}

?>
