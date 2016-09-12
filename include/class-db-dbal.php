<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once 'db_core_interface.php';

/**
 * Description of class-db-dbal
 *
 * @author fhabermann
 */
class db extends db_core implements db_core_interface {
	var $dbtype = "dbal";
	var $dbname = "";

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
	function connect($adapter, $host,$user,$pass,$db,$schema="",$encoding="") {
            $doctrineConfig = new \Doctrine\DBAL\Configuration();

            $connectionParams = array(
                'dbname' => $db,
                'user' => $user,
                'password' => $pass,
                'host' => $host,
                'driver' => $adapter,
            );

            $this->connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $doctrineConfig);

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
            return $result->getResult()->rowCount();
	}

	/**
	 * Fetch a Result as Object
	 * @param mixed $result
	 * @return object
	 */
	function fetch_object($result) {
            $this->connection->setFetchMode( \PDO::FETCH_OBJ );
            return $result->getResult()->fetch(); 
	}

	/**
	 * Return Result as hash array
	 * @param mixed result
	 * @return array
	 */
	function fetch_assoc($result) {
            $this->connection->setFetchMode( \PDO::FETCH_ASSOC );
            return $result->getResult()->fetch();
	}

	/**
	 * Return the last inserted id from a query
	 * @param string $name not required
	 * @return int
	 */
	function last_id($name = "") {
            return $this->connection->lastInsertId();
	}

	/**
	 * Quote a String with Mysql Quotes
	 * @param string $name
	 * @return string
	 */
	function quoteIdentifier($string) {
		return $this->connection->quoteIdentifier($string);
	}
}
