<?php
/**
 * class for results of a db query
 *
 * Holds the complete, buffered resultset of a query so that
 * num_rows(), data_seek() and repeated fetches work identically
 * on all supported database drivers.
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 */
class db_result {
	/**
	 * reference to all classes
	 * @var array
	 */
	var $CLASS = null;

	/**
	 * raw driver result of query
	 * @var mixed
	 */
	var $result = null;

	/**
	 * query that was used
	 * @var string
	 */
	var $query = "";

	/**
	 * buffered rows (assoc arrays)
	 * @var array
	 */
	var $rows = array();

	/**
	 * current fetch position
	 * @var int
	 */
	var $position = 0;

	/**
	 * number of rows affected by a write query
	 * @var int
	 */
	var $affectedRows = 0;

	/**
	 * constructor
	 * @param array $CLASS
	 */
	function __construct(&$CLASS) {
		$this->CLASS =& $CLASS;
	}

	/**
	 * get raw driver result
	 * @return mixed
	 */
	function getResult() {
		return $this->result;
	}

	/**
	 * set raw driver result
	 * @param mixed $result
	 */
	function setResult($result) {
		$this->result = $result;
	}

	/**
	 * set buffered rows
	 * @param array $rows
	 * @param int $affectedRows
	 */
	function setRows($rows, $affectedRows = 0) {
		$this->rows = $rows;
		$this->position = 0;
		$this->affectedRows = $affectedRows;
	}

	/**
	 * get query
	 * @return string $query
	 */
	function getQuery() {
		return $this->query;
	}

	/**
	 * set query
	 * @param string $query
	 */
	function setQuery($query) {
		$this->query = $query;
	}

	/**
	 * fetch as assoc
	 * @return array|false
	 */
	function fetch_assoc() {
		if (!isset($this->rows[$this->position])) {
			return false;
		}

		return $this->rows[$this->position++];
	}

	/**
	 * fetch as object
	 * @return object|false
	 */
	function fetch_object() {
		$row = $this->fetch_assoc();

		return $row === false ? false : (object) $row;
	}

	/**
	 * fetch as numeric row
	 * @return array|false
	 */
	function fetch_row() {
		$row = $this->fetch_assoc();

		return $row === false ? false : array_values($row);
	}

	/**
	 * count rows
	 * @return integer
	 */
	function num_rows() {
		return count($this->rows);
	}

	/**
	 * get affected rows
	 * @return integer
	 */
	function affected_rows() {
		return $this->affectedRows;
	}

	/**
	 * move pointer on result
	 * @param integer $number
	 * @return bool
	 */
	function data_seek($number) {
		if ($number < 0 || ($number > 0 && !isset($this->rows[$number]))) {
			return false;
		}

		$this->position = $number;

		return true;
	}
}
