<?php

$KNOWLEDGEROOTDB = 'MDB2';

class db extends db_core {
	var $mdb2 = false; // used for adodb-lite object

	/**
	 * init/start class
	 */
	function start(&$CLASS) {
		$this->CLASS =& $CLASS;

		require_once('MDB2.php');
	}

	/**
	 *
	 */
	function connect($host,$user,$pass,$db,$schema="public",$encoding="LATIN1") {
		$dsn = array();
		$options = array();

		$dsn = $this->CLASS['config']->db->dsn;
		$options = $this->CLASS['config']->db->options->toArray();

		// do connect
		$this->mdb2 =& MDB2::factory($dsn, $options);

		if (PEAR::isError($this->mdb2)) {
			$this->CLASS['error']->log($this->mdb2->getMessage(),1,"class-mdb2.php::connect");
			exit();
		}

		// set dbname und dbdriver
		$dsn = $this->mdb2->getDSN("array");

		if($dsn['phptype'] == "pgsql") {
			$this->dbname = "postgresql";
		} else {
			$this->dbname = $dsn['phptype'];
		}

		$this->dbtype = $dsn['phptype'];

		return true;
	}

	/**
	 * Close connection
	 * @return bool
	 */
	function close() {
		return $this->mdb2->disconnect();
	}

	/**
	 * Will make a query with the server
	 * @param string $query
	 * @return resource return query result
	 */
	function query($query) {
		$this->lastquery = $query;
		$this->query_cache[] = $query;
		$this->querys += 1;

		$res = new db_result($this->CLASS);
		$res->setQuery($query);
		$res->setResult($this->mdb2->query($query));

		// if error in query
		if (PEAR::isError($res->getResult())) {
			 $this->CLASS['error']->log("ERROR IN QUERY: \"$query\"",1,$res->getResult()->getMessage());
		}

		return $res;
	}

	/**
	 * Fetch a Result as assoc
	 * @param mixed $result
	 * @return array
	 */
	function fetch_assoc($result) {
		return $result->getResult()->fetchRow(MDB2_FETCHMODE_ASSOC);
	}

	/**
	 * Fetch a Result as Object
	 * @param mixed $result
	 * @return object
	 */
	function fetch_object($result) {
		return $result->getResult()->fetchRow(MDB2_FETCHMODE_OBJECT);
	}

	/**
	 * Fetch a Result as array
	 * @param mixed $result
	 * @return array
	 */
	function fetch_row($result) {
		return $result->getResult()->fetchRow(MDB2_FETCHMODE_ORDERED);
	}

	/**
	 * Will count the rows of a resultset
	 * @param mixed $result
	 * @return int
	 */
	function num_rows($result) {
		return $result->getResult()->numRows();
	}


	/**
	 * Returns the text of the error message from previous PostgreSQL operation
	 * @return string
	 */
	function error() {
		return "";
	}


	/**
	 * Return the last inserted id from a query
	 * @param string $name not required
	 * @return int
	 */
	function last_id($name) {
		return $this->mdb2->lastInsertID();
	}

	/**
	 * Quote a String with Postgresql Quotes
	 * @param string $name
	 * @return string
	 */
	function quoteIdentifier($string) {
		return $this->mdb2->quoteIdentifier($string);
	}

	/**
	 * do data seek on result
	 * @param resource $result
	 * @param integer $number
	 * @return bool
	 */
	function data_seek($result, $number) {
		return $result->getResult()->seek($number);
	}
}

?>
