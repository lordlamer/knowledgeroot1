<?php

class db_core {
	var $dbtype = "";
	var $dbname = "";

	var $CLASS = null;
	var $connection = null;
	var $lastquery = "";
	var $querys = 0;
	var $query_cache = array();

	/**
	 * init/start class
	 */
	function start(&$CLASS) {
		$this->CLASS =& $CLASS;
	}

	/**
	 * secure query
	 * this function will wrap all over sprintf - so see sprintf for help
	 * the result of sprintf will be send as query to database
	 * @return resource resource of query
	 */
	function squery() {
		$args = func_num_args();
		if($args > 0) {
			$param = func_get_args();
			return $this->query(call_user_func_array('sprintf', $param));
		}
	}

	/**
	 * query format
	 * this function will wrap all over sprintf - so see sprintf for help
	 * the result of sprintf will be send as query to database
	 * @return resource resource of query
	 */
	function queryf() {
		$args = func_num_args();
		if($args > 0) {
			$param = func_get_args();
			return $this->query(call_user_func_array('sprintf', $param));
		}
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
	 * create database insert with array
	 *
	 * @param string $table
	 * @param array $values
	 * @return mixed will return the result from the query
	 */
	function db_insert($table, $values) {
		if($table != "" && is_array($values)) {
			$sql = "INSERT INTO " . $this->quoteIdentifier($table) . " ";

			$coloum = "(";
			$coloumval = "(";

			foreach($values as $key => $value) {
				if(isset($values[$key]['value'])) {
					$coloum .= $this->quoteIdentifier($key) . ", ";

					if(isset($values[$key]['type'])) {
						switch($values[$key]['type']) {
						case "integer":
						case "INTEGER":
							if(isset($values[$key]['format'])) {
								$coloumval .= sprintf($values[$key]['format'].", ",$values[$key]['value']);
							} else {
								$coloumval .= sprintf("%d, ",$values[$key]['value']);
							}
							break;
						case "float":
						case "FLOAT":
							if(isset($values[$key]['format'])) {
								$coloumval .= sprintf($values[$key]['format'].", ",$values[$key]['value']);
							} else {
								$coloumval .= sprintf("%f, ",$values[$key]['value']);
							}
							break;
						default:
							if(isset($values[$key]['format'])) {
								$coloumval .= sprintf("'".$values[$key]['format']."', ",$values[$key]['value']);
							} else {
								$coloumval .= sprintf("'%s', ",$values[$key]['value']);
							}
						}
					} else {
						$coloumval .= sprintf("'%s', ",$values[$key]['value']);
					}
				} elseif(isset($values[$key]) && !is_array($values[$key]) && $values[$key] != "") {
					$coloum .= $this->quoteIdentifier($key) . ", ";
					$coloumval .= sprintf("'%s', ",$values[$key]);
				}
			}

			// remove last comma
			$coloum = substr($coloum,0,strlen($coloum)-2);
			$coloumval = substr($coloumval,0,strlen($coloumval)-2);

			$coloum .= ")";
			$coloumval .= ")";

			$sql .= $coloum . " VALUES " . $coloumval;

			return $this->query($sql);
		} else {
			$this->CLASS['error']->log('$table not set or $values are not a array',2,'class-db-core.php::db_insert');
			return false;
		}
	}

	/**
	 * create database update with array
	 *
	 * @param string $table
	 * @param array $values
	 * @param string $where
	 * @return mixed will return the result from the query
	 */
	function db_update($table, $values, $where) {
		if($table != "" && is_array($values)) {
			$sql = "UPDATE " . $this->quoteIdentifier($table) . " SET ";

			$coloum = "";

			foreach($values as $key => $value) {
				if(isset($values[$key]['value'])) {
					if(isset($values[$key]['type'])) {
						switch($values[$key]['type']) {
						case "integer":
						case "INTEGER":
							if(isset($values[$key]['format'])) {
								$coloum .= sprintf($this->quoteIdentifier('%s')."=".$values[$key]['format'].", ",$key,$values[$key]['value']);
							} else {
								$coloum .= sprintf($this->quoteIdentifier('%s')."=%d, ",$key,$values[$key]['value']);
							}
							break;
						case "float":
						case "FLOAT":
							if(isset($values[$key]['format'])) {
								$coloum .= sprintf($this->quoteIdentifier('%s')."=".$values[$key]['format'].", ",$key,$values[$key]['value']);
							} else {
								$coloum .= sprintf($this->quoteIdentifier('%s')."=%f, ",$key,$values[$key]['value']);
							}
							break;
						default:
							if(isset($values[$key]['format'])) {
								$coloum .= sprintf($this->quoteIdentifier('%s')."=".$values[$key]['format'].", ",$key,$values[$key]['value']);
							} else {
								$coloum .= sprintf($this->quoteIdentifier('%s')."='%s', ",$key,$values[$key]['value']);
							}
						}
					} else {
						$coloum .= sprintf($this->quoteIdentifier('%s')."='%s'",$key,$values[$key]['value']);
					}
				} elseif(isset($values[$key]) && !is_array($values[$key]) && $values[$key] != "") {
					$coloum .= sprintf("\"%s\"='%s'",$key,$values[$key]);
				}
			}

			// remove last comma
			$coloum = substr($coloum,0,strlen($coloum)-2);

			if($where == "") $where = "1";

			$sql .= $coloum . " WHERE " . $where;

			return $this->query($sql);
		} else {
			$this->CLASS['error']->log('$table not set or $values are not a array',2,'class-db-core.php::db_update');
			return false;
		}
	}

	/**
	 * create a database delete
	 *
	 * @param string $table
	 * @param string $where
	 * @return mixed will return the result from the query
	 */
	function db_delete($table, $where) {
		if($table != "" && $where != "") {
			$sql = "DELETE FROM ".$this->quoteIdentifier($table);

			if($where != "") {
				$sql .= " WHERE " . $where;
			}

			return $this->query($sql);
		} else {
			$this->CLASS['error']->log('$table not set or $where are not a array',2,'class-db-core.php::db_delete');
			return false;
		}
	}
}

?>
