<?php
/**
 * class for mysqli connect
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-mysqli.php 941 2010-06-07 20:57:58Z lordlamer $
 */

$KNOWLEDGEROOTDB = 'MYSQL';

/**
 * Class for Connect to Mysql over mysqli
 */
class db extends db_core {
	var $dbtype = "mysqli";
	var $dbname = "mysql";

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
		if($this->CLASS['config']->db->pcconect) {
			$this->connection = mysqli_pconnect($host,$user,$pass);
		} else {
			$this->connection = mysqli_connect($host,$user,$pass);
		}

		if(!$this->connection) {
			$this->CLASS['error']->log("Cannnot connect to host!",1,"class-mysqli.php::connect");
			exit();
		}

		$conndb = mysqli_select_db($this->connection, $db);
		if(!$conndb) {
			$this->CLASS['error']->log("Wrong Database!",1,"class-mysqli.php::connect");
			exit();
		}

		return $this->connection;
	}

	/**
	 * Close mysql connection
	 * @return bool
	 */
	function close() {
		return mysqli_close($this->connection);
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
		$res->setResult(mysqli_query($this->connection, $query));

		if($res->getResult() === false) {
			$this->CLASS['error']->log("ERROR IN QUERY: \"$query\"",1,mysqli_errno() . ":".mysqli_errno());
		}

		return $res;
	}

	/**
	 * Will count the rows of a resultset
	 * @param mixed $result
	 * @return int
	 */
	function num_rows($result) {
		return mysqli_num_rows($result->getResult());
	}

	/**
	 * Fetch a Result as Object
	 * @param mixed $result
	 * @return object
	 */
	function fetch_object($result) {
		return mysqli_fetch_object($result->getResult());
	}

	/**
	 * Return Result as Array
	 * @param mixed $result
	 * @return array
	 */
	function fetch_row($result) {
		return mysqli_fetch_row($result->getResult());
	}

	/**
	 * Return Result as hash array
	 * @param mixed result
	 * @return array
	 */
	function fetch_assoc($result) {
		return mysqli_fetch_assoc($result->getResult());
	}

	/**
	 * Return affected rows of a result
	 * @param mixed $result
	 * @return int
	 */
	function affected_rows($result) {
		return mysqli_affected_rows($result->getResult());
	}

	/**
	 * Returns the text of the error message from previous MySQL operation
	 * @return string
	 */
	function error() {
		return mysqli_error($this->connection);
	}

	/**
	 * Return the last inserted id from a query
	 * @param string $name not required
	 * @return int
	 */
	function last_id($name = "") {
		return mysqli_insert_id($this->connection);
	}

	/**
	 * Quote a String with Mysql Quotes
	 * @param string $name
	 * @return string
	 */
	function quoteIdentifier($string) {
		return "`" . $string . "`";
	}

	/**
	 * do data seek on result
	 * @param resource $result
	 * @param integer $number
	 * @return bool
	 */
	function data_seek($result, $number) {
		return mysqli_data_seek($result->getResult(), $number);
	}
}

?>
