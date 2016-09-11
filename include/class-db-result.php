<?php
/**
 * class for results of a db query
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-db-result.php 860 2009-09-25 12:15:19Z lordlamer $
 */
class db_result {
	/**
	 * reference to all classes
	 * @param array $CLASS
	 */
	private $CLASS = null;

	/**
	 * result of query
	 * @param resource $result
	 */
	private $result = null;

	/**
	 * query that was used
	 * @param string $query
	 */
	private $query = "";

	/**
	 * constructor for php5
	 * @param array $CLASS
	 */
	public function __construct(&$CLASS) {
		$this->CLASS =& $CLASS;
	}

	/**
	 * get result
	 * @return resource
	 */
	public function getResult() {
		return $this->result;
	}

	/**
	 * set result
	 * @param resource $result
	 */
	public function setResult($result) {
		$this->result = $result;
	}

	/**
	 * get query
	 * @return string $query
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * set query
	 * @param string $query
	 */
	public function setQuery($query) {
		$this->query = $query;
	}

	/**
	 * fetch as assoc
	 * @return array
	 */
	public function fetch_assoc() {
		return $this->CLASS['db']->fetch_assoc($this);
	}

	/**
	 * fetch as object
	 * @return object
	 */
	public function fetch_object() {
		return $this->CLASS['db']->fetch_object($this);
	}

	/**
	 * fetch as row
	 * @return array
	 */
	public function fetch_row() {
		return $this->CLASS['db']->fetch_row($this);
	}

	/**
	 * count rows
	 * @return integer
	 */
	public function num_rows() {
		return $this->CLASS['db']->num_rows($this);
	}

	/**
	 * get affected rows
	 * @return integer
	 */
	public function affected_rows() {
		return $this->CLASS['db']->affected_rows($this);
	}

	/**
	 * move pointer on result
	 * @param integer $number
	 * @return bool
	 */
	public function data_seek($number) {
		return $this->CLASS['db']->data_seek($this, $number);
	}
}

?>
