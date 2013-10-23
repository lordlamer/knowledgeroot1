<?php
/**
 *
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-oracle.php 941 2010-06-07 20:57:58Z lordlamer $
 */

$KNOWLEDGEROOTDB = 'OCI';

/**
 * Class for Connect to Postgresql
 */
class db extends db_core {
	var $dbtype = "oci";
	var $dbname = "oracle";

	/**
	 * Make a Connect to the oracle Server
	 *
	 * @param string $host
	 * @param string $user
	 * @param string $pass
	 * @param string $db
	 * @param string $schema optional
	 * @param string $enconding optional
	 * @return mixed return connection resource
	 */
	function connect($host,$user,$pass,$db,$schema="",$encoding="") {
		if($this->CLASS['config']->db->pconnect) {
			$this->connection = oci_pconnect($user, $pass, $db, $encoding);
		} else {
			$this->connection = oci_connect($user, $pass, $db, $encoding);
		}

		if(!$this->connection) {
			$this->CLASS['error']->log("Cannnot connect to host!",1,"class-oracle.php::connect");
			exit();
		}

		return $this->connection;
	}

	/**
	 * Close oracle connection
	 * @return bool
	 */
	function close() {
		return oci_close($this->connection);
	}

	/**
	 * Will make a query with the oracle server
	 * @param string $query
	 * @return mixed return query result
	 */
	function query($query) {
		$this->lastquery = $query;
		$this->query_cache[] = $query;
		$this->querys += 1;

		$res = new db_result($this->CLASS);
		$res->setQuery($query);
		$res->setResult(oci_parse($this->connection, $query));
		oci_execute($res->getResult());

		// if error in query
		if($res->getResult() === false) {
			//$this->CLASS['error']->log("ERROR IN QUERY: \"$query\"",1,oci_last_error($this->connection));
		}

		return $res;
	}

	/**
	 * Will count the rows of a resultset
	 * @param mixed $result
	 * @return int
	 */
	function num_rows($result) {
		return oci_num_rows($result->getResult());
	}

	/**
	 * Fetch a Result as Object
	 * @param mixed $result
	 * @return object
	 */
	function fetch_object($result) {
		return oci_fetch_object($result->getResult());
	}

	/**
	 * Return Result as Array
	 * @param mixed $result
	 * @return array
	 */
	function fetch_row($result) {
		return oci_fetch_row($result->getResult());
	}

	/**
	 * Return Result as hash array
	 * @param mixed result
	 * @return array
	 */
	function fetch_assoc($result) {
		return oci_fetch_assoc($result->getResult());
	}

	/**
	 * Return affected rows of a result
	 * @param mixed $result
	 * @return int
	 */
	function affected_rows($result) {
		return null;
	}

	/**
	 * Returns the text of the error message from previous oracle operation
	 * @return string
	 */
	function error() {
		return null;
	}

	/**
	 * Return the last inserted id from a query
	 * @param string $name not required
	 * @return int
	 */
	function last_id($name) {
		$res = $this->query("select last_value FROM $name");
		$row = $this->fetch_assoc($res);

		return $row['last_value'];
	}

	/**
	 * Quote a String with Postgresql Quotes
	 * @param string $name
	 * @return string
	 */
	function quoteIdentifier($string) {
		return "\"" . $string . "\"";
	}
}

?>
