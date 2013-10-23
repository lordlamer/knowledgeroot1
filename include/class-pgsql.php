<?php
/**
 *
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-pgsql.php 959 2010-08-22 20:39:13Z lordlamer $
 */

$KNOWLEDGEROOTDB = 'PGSQL';

/**
 * Class for Connect to Postgresql
 */
class db extends db_core {
	var $dbtype = "pgsql";
	var $dbname = "postgresql";

	/**
	 * Make a Connect to the postgresql Server
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
			$this->connection = pg_pconnect("host=$host dbname=$db user=$user password=$pass");
		} else {
			$this->connection = pg_connect("host=$host dbname=$db user=$user password=$pass");
		}

		if(!$this->connection) {
			$this->CLASS['error']->log("Cannnot connect to host!",1,"class-pgsql.php::connect");
			exit();
		}

		if(!$this->schema($schema)) {
			$this->CLASS['error']->log("Could not set schema!",1,"class-pgsql.php::connect");
			exit();
		}

		if($this->set_client_encoding($encoding) == -1) {
			$this->CLASS['error']->log("Could not set client encoding!",1,"class-pgsql.php::connect");
			exit();
		}

		return $this->connection;
	}

	/**
	 * Close postgresql connection
	 * @return bool
	 */
	function close() {
		return pg_close($this->connection);
	}

	/**
	 * Set schema that should we use for the session
	 * @param string $schema
	 * @return resource
	 */
	function schema($schema = "") {
		if($schema != "") {
			$res = $this->query("SET search_path TO \"".$schema."\"");
			return $res;
		}

		return true;
	}

	/**
	 * Set client encoding
	 * @param string $encoding
	 * @return int 0 on success or -1 on error
	 */
	function set_client_encoding($encoding = "") {
		if($encoding != "") {
			return pg_set_client_encoding($this->connection, $encoding);
		}

		return 0;
	}

	/**
	 * Will make a query with the postgresql server
	 * @param string $query
	 * @return mixed return query result
	 */
	function query($query) {
		$this->lastquery = $query;
		$this->query_cache[] = $query;
		$this->querys += 1;

		$res = new db_result($this->CLASS);
		$res->setQuery($query);
		$res->setResult(pg_query($this->connection, $query));

		// if error in query
		if($res->getResult() === false) {
			$this->CLASS['error']->log("ERROR IN QUERY: \"$query\"",1,pg_last_error($this->connection));
		}

		return $res;
	}

	/**
	 * Will count the rows of a resultset
	 * @param mixed $result
	 * @return int
	 */
	function num_rows($result) {
		return pg_num_rows($result->getResult());
	}

	/**
	 * Fetch a Result as Object
	 * @param mixed $result
	 * @return object
	 */
	function fetch_object($result) {
		return pg_fetch_object($result->getResult());
	}

	/**
	 * Return Result as Array
	 * @param mixed $result
	 * @return array
	 */
	function fetch_row($result) {
		return pg_fetch_row($result->getResult());
	}

	/**
	 * Return Result as hash array
	 * @param mixed result
	 * @return array
	 */
	function fetch_assoc($result) {
		return pg_fetch_assoc($result->getResult());
	}

	/**
	 * Return affected rows of a result
	 * @param mixed $result
	 * @return int
	 */
	function affected_rows($result) {
		return pg_affected_rows($this->connection, $result->getResult());
	}

	/**
	 * Returns the text of the error message from previous PostgreSQL operation
	 * @return string
	 */
	function error() {
		return pg_last_error($this->connection);
	}

	/**
	 *
	 */
	function lo_open($oid,$mode) {
		return pg_lo_open($this->connection,$oid,$mode);
	}

	/**
	 *
	 */
	function lo_close($handle) {
		return pg_lo_close($handle);
	}

	/**
	 *
	 */
	function lo_read_all($handle) {
		return pg_lo_read_all($handle);
	}

	/**
	 *
	 */
	function lo_create() {
		return pg_lo_create($this->connection);
	}

	/**
	 *
	 */
	function lo_write($handle,$buffer) {
		return pg_lo_write($handle,$buffer);
	}

	/**
	 *
	 */
	function lo_unlink($oid) {
		return pg_lo_unlink($this->connection,$oid);
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

	/**
	 * do data seek on result
	 * @param resource $result
	 * @param integer $number
	 * @return bool
	 */
	function data_seek($result, $number) {
		return pg_result_seek($result->getResult(), $number);
	}
}

?>
