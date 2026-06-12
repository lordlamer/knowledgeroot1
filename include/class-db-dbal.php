<?php

/**
 * Database access via Doctrine DBAL 3.
 *
 * Results are fully buffered into db_result so num_rows(), data_seek()
 * and repeated fetches behave the same on sqlite, mysql/mariadb and
 * postgresql (DBAL 3 results are forward-only and rowCount() is not
 * reliable for SELECTs on every driver).
 *
 * @author fhabermann
 */
class db extends db_core {
    var $dbtype = "dbal";
    var $dbname = "";

    /**
     * map of legacy adapter names to DBAL driver names
     */
    private static $driverAliases = array(
        'mysql' => 'pdo_mysql',
        'pgsql' => 'pdo_pgsql',
        'postgres' => 'pdo_pgsql',
        'sqlite' => 'pdo_sqlite',
    );

    /**
     * Connect to the database server
     *
     * @param string $adapter DBAL driver name (pdo_mysql, pdo_pgsql, pdo_sqlite, mysqli, ...)
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param string $db database name; for sqlite the path to the database file
     * @param string $schema not required
     * @param string $encoding not required
     * @return \Doctrine\DBAL\Connection
     */
    function connect($adapter, $host, $user, $pass, $db, $schema = "", $encoding = "") {
        if (isset(self::$driverAliases[$adapter])) {
            $adapter = self::$driverAliases[$adapter];
        }

        $connectionParams = array(
            'user' => (string) $user,
            'password' => (string) $pass,
            'host' => (string) $host,
            'driver' => $adapter,
        );

        if ($adapter == 'pdo_sqlite') {
            $connectionParams['path'] = $db;
        } else {
            $connectionParams['dbname'] = $db;
        }

        $this->connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);

        $this->dbtype = str_replace("pdo_", "", $adapter);

        if ($this->dbtype == 'pgsql' && $schema != "") {
            $this->connection->executeStatement('SET search_path TO ' . $this->connection->quoteIdentifier($schema));
        }

        return $this->connection;
    }

    /**
     * Close connection
     * @return void
     */
    function close() {
        if ($this->connection !== null) {
            $this->connection->close();
        }
    }

    /**
     * Run a query and return a buffered result
     * @param string $query
     * @return db_result
     */
    function query($query) {
        $this->lastquery = $query;
        $this->query_cache[] = $query;
        $this->querys += 1;

        $res = new db_result($this->CLASS);
        $res->setQuery($query);

        try {
            $result = $this->connection->executeQuery($query);

            $rows = array();
            if ($result->columnCount() > 0) {
                $rows = $result->fetchAllAssociative();
            }

            $res->setRows($rows, $result->rowCount());
            $res->setResult($result);
        } catch (\Doctrine\DBAL\Exception $e) {
            $this->CLASS['error']->log("ERROR IN QUERY: \"$query\"", 1, $e->getMessage());
            $res->setRows(array(), 0);
        }

        return $res;
    }

    /**
     * Will count the rows of a resultset
     * @param db_result $result
     * @return int
     */
    function num_rows($result) {
        return $result->num_rows();
    }

    /**
     * Fetch a result row as object
     * @param db_result $result
     * @return object|false
     */
    function fetch_object($result) {
        return $result->fetch_object();
    }

    /**
     * Fetch a result row as hash array
     * @param db_result $result
     * @return array|false
     */
    function fetch_assoc($result) {
        return $result->fetch_assoc();
    }

    /**
     * Fetch a result row as numeric array
     * @param db_result $result
     * @return array|false
     */
    function fetch_row($result) {
        return $result->fetch_row();
    }

    /**
     * Get number of affected rows
     * @param db_result $result
     * @return int
     */
    function affected_rows($result) {
        return $result->affected_rows();
    }

    /**
     * Move pointer on result
     * @param db_result $result
     * @param integer $number
     * @return bool
     */
    function data_seek($result, $number) {
        return $result->data_seek($number);
    }

    /**
     * Return the last inserted id
     * @param string $name not required
     * @return int
     */
    function last_id($name = "") {
        return (int) $this->connection->lastInsertId();
    }

    /**
     * Quote an identifier for the current platform
     * @param string $string
     * @return string
     */
    function quoteIdentifier($string) {
        return $this->connection->quoteIdentifier($string);
    }
}
