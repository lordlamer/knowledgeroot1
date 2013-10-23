<?php
/**
 * autoloader function for classes
 * @param string $class name of class
 */
function __autoload($class) {
        Zend_Loader::loadClass($class);
}

// base path
$base_path = realpath(dirname(__FILE__).'/../') . '/';

// set include path
set_include_path($base_path . '/lib/' . PATH_SEPARATOR . get_include_path());

require_once('Zend/Loader.php');

/**
 * This Class inerhits functions for installation of knowledgeroot
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-knowledgeroot-installer.php 1066 2011-05-05 21:41:45Z lordlamer $
 */
class knowledgeroot_installer {
	var $file_config = "config/app.ini";
	var $file_config_old = "config/config.inc.php";
	var $file_config_dist = "config/app.ini.dist";
	var $file_install = "install.php";
	var $file_update = "update.php";
	var $file_pgsql_dump = "dumps/postgre.sql";
	var $file_mysql_dump = "dumps/mysql.sql";
	var $file_sqlite_dump = "dumps/sqlite.sql";
	var $file_pgsql_upgrade_dump = "dumps/upgrade_postgre.sql";
	var $file_mysql_upgrade_dump = "dumps/upgrade_mysql.sql";
	var $file_sqlite_upgrade_dump = "dumps/upgrade_sqlite.sql";
	var $file_class_knowledgeroot = "include/class-knowledgeroot.php";
	var $file_class_knowledgeroot_error = "include/class-error.php";
	var $file_class_mysql = "include/class-mysql.php";
	var $file_class_mysqli = "include/class-mysqli.php";
	var $file_class_pgsql = "include/class-pgsql.php";
	var $file_class_sqlite = "include/class-sqlite.php";
	var $file_class_db_core ="include/class-db-core.php";
	var $file_class_db_result = "include/class-db-result.php";

	var $base_path = "";

	var $error_msg = "";

	var $CONFIG = array();
	var $CLASS = array();

	var $db_connection = "";

	function mainInstall() {
		$out = "";

		$this->base_path = realpath(dirname(__FILE__).'/../') . '/';

		if(isset($_POST['submit'])) {
			if($this->doInstallConnect()) {
				$out .= $this->doInstall();
			} else {
				$out .= $this->error_msg;
				$out .= $this->getInstallForm();
			}
		} else {
			$out .= $this->getInstallForm();
		}

		return $out;
	}

	function mainUpdate() {
		$out = "";

		$this->base_path = realpath(dirname(__FILE__).'/../') . '/';

		if(isset($_POST['submit'])) {
			if($this->doUpdateConnect()) {
				$out .= $this->doUpdate();
			} else {
				$out .= $this->error_msg;
				$out .= $this->getUpdateForm();
			}
		} else {
			$out .= $this->getUpdateForm();
		}

		return $out;
	}

	function doInstallConnect() {
		if($_POST['db_type'] == "pgsql") {
			if($_POST['db_create'] == 1) {
				if($this->isPgsqlConnect($_POST['db_host'],$_POST['db_user'],$_POST['db_pass'],"template1",$_POST['db_schema'],$_POST['db_encoding'])) {
					$res = pg_query($this->db_connection,"CREATE DATABASE \"" . $_POST['db_database'] . "\"" . ($_POST['db_encoding'] != "" ? " WITH ENCODING='".$_POST['db_encoding']."'" : ""));

					pg_close($this->db_connection);

					if(!$res ) {
						$this->error_msg = "Could not create database!";
						return 0;
					}

					if(!$this->isPgsqlConnect($_POST['db_host'],$_POST['db_user'],$_POST['db_pass'],$_POST['db_database'],$_POST['db_schema'],$_POST['db_encoding'])) {
						$this->error_msg = "Could not connect to database!";
						return 0;
					}
				} else {
					$this->error_msg = "Could not connect to database!";
					return 0;
				}
			} else {
				if(!$this->isPgsqlConnect($_POST['db_host'],$_POST['db_user'],$_POST['db_pass'],$_POST['db_database'],$_POST['db_schema'],$_POST['db_encoding'])) {
					$this->error_msg = "Could not connect to database!";
					return 0;
				}
			}
		} elseif($_POST['db_type'] == "mysql") {
			if($_POST['db_create'] == 1) {
				if(!$this->isMysqlConnect($_POST['db_host'],$_POST['db_user'],$_POST['db_pass'])) {
					$this->error_msg = "Could not connect to database!";
					return 0;
				}

				$res = mysql_query("CREATE DATABASE `".$_POST['db_database']."`", $this->db_connection);

				if(!$res) {
					$this->error_msg = "Could not create database!";
					return 0;
				}

				if(!$this->isMysqlConnect($_POST['db_host'],$_POST['db_user'],$_POST['db_pass'],$_POST['db_database'])) {
					$this->error_msg = "Could not connect to database!";
					return 0;
				}
			} else {
				if(!$this->isMysqlConnect($_POST['db_host'],$_POST['db_user'],$_POST['db_pass'],$_POST['db_database'])) {
					$this->error_msg = "Could not connect to database!";
					return 0;
				}
			}
		} elseif($_POST['db_type'] == "mysqli") {
			if($_POST['db_create'] == 1) {
				if(!$this->isMysqliConnect($_POST['db_host'],$_POST['db_user'],$_POST['db_pass'])) {
					$this->error_msg = "Could not connect to database!";
					return 0;
				}

				$res = mysqli_query($this->db_connection, "CREATE DATABASE `".$_POST['db_database']."`");

				if(!$res) {
					$this->error_msg = "Could not create database!";
					return 0;
				}

				if(!$this->isMysqliConnect($_POST['db_host'],$_POST['db_user'],$_POST['db_pass'],$_POST['db_database'])) {
					$this->error_msg = "Could not connect to database!";
					return 0;
				}
			} else {
				if(!$this->isMysqliConnect($_POST['db_host'],$_POST['db_user'],$_POST['db_pass'],$_POST['db_database'])) {
					$this->error_msg = "Could not connect to database!";
					return 0;
				}
			}
		} elseif($_POST['db_type'] == "sqlite") {
			if(!$this->isSqliteConnect($_POST['db_host'],$_POST['db_user'],$_POST['db_pass'],$_POST['db_database'])) {
				return 0;
			}
		} else {
			return 0;
		}

		return 1;
	}

	function doUpdateConnect() {
		$newConfig = new Zend_Config_Ini($this->file_config_dist, null, array('allowModifications' => true));
		$this->CLASS['config'] =& $newConfig;
		$CONFIG = '';
		require_once($this->file_config_old);
		require_once($this->file_class_knowledgeroot);
		require_once($this->file_class_knowledgeroot_error);

		// set config
		$this->CLASS['vars'] = $CONFIG;

		require_once($this->file_class_db_result);
		require_once($this->file_class_db_core);

		// load databaseclass
		if($CONFIG['db']['type'] == "mysql") {
			require_once($this->file_class_mysql);
		}

		if($CONFIG['db']['type'] == "mysqli") {
			require_once($this->file_class_mysqli);
		}

		if($CONFIG['db']['type'] == "pgsql") {
			require_once($this->file_class_pgsql);
		}

		if (!isset ($KNOWLEDGEROOTDB) || ($KNOWLEDGEROOTDB != 'PGSQL' && $KNOWLEDGEROOTDB != 'MYSQL')) {
			return 0;
		}

		// init error class
		$this->CLASS['error'] = new knowledgeroot_error();
		$this->CLASS['error']->start($this->CLASS);

		// init databaseclass
		$this->CLASS['db'] = new db();
		$this->CLASS['db']->start($this->CLASS);

		// connect to database
		$this->CLASS['db']->connect($CONFIG['db']['host'],$CONFIG['db']['user'],$CONFIG['db']['pass'],$CONFIG['db']['database'],$CONFIG['db']['schema'],$CONFIG['db']['encoding']);

		if(!$this->CLASS['db']->connection) {
			$this->error_msg = "Could not connect to database!";
			return 0;
		}

		$this->db_connection =& $this->CLASS['db']->connection;

		$this->CLASS['knowledgeroot'] = new knowledgeroot();
		$this->CLASS['knowledgeroot']->start($this->CLASS);

		return 1;
	}

	function doInstall() {
		$config = new Zend_Config_Ini($this->file_config_dist, null, array('allowModifications' => true));

		$out = "";
		$out .= '
		<form action="install.php" method="post">
		<table align="center" width="548" cellpadding="1" cellspacing="1" border="0">
		<tr><td><img src="images/knowledgeroot-install.png"></td></tr>
		';

		if($_POST['db_type'] == "pgsql") {
			$dump_file = $this->file_pgsql_dump;
		} else if($_POST['db_type'] == "mysql" || $_POST['db_type'] == "mysqli") {
			$dump_file = $this->file_mysql_dump;
		} else if($_POST['db_type'] == "sqlite") {
			$dump_file = $this->file_sqlite_dump;

		} else {
			return "Wrong dbtype!";
		}

		$this->doSql($this->readSqlDump($dump_file),$_POST['db_type']);

		// set baseurl
		$config->base->base_url = $_POST['baseurl'];

		// write config.php
		if($this->isFileWriteable($this->file_config) && $_POST['write_config'] == 1 && $this->writeFileContent($this->getConfigContent($_POST['db_type'],$_POST['db_host'],$_POST['db_user'],$_POST['db_pass'],$_POST['db_database'],$_POST['db_schema'],$_POST['db_encoding'],$_POST['admin_user'],$_POST['admin_pass'],$config),$this->file_config)) {
			$out .= "<tr><td>Config (".$this->file_config.") was written!</td></tr>\n";
		} else {
			$out .= '<tr><td>Create ' . $this->file_config . ' and put the following content in it:<br><textarea cols="50" rows="20">'. $this->getConfigContent($_POST['db_type'],$_POST['db_host'],$_POST['db_user'],$_POST['db_pass'],$_POST['db_database'],$_POST['db_schema'],$_POST['db_encoding'],$_POST['admin_user'],$_POST['admin_pass'],$config) ."</textarea></td></tr>\n";
		}

		// delete install
		if(isset($_POST['delete_install']) && $_POST['delete_install'] == 1 && $this->isFileDeleteable($this->file_install) && @unlink($this->file_install)) {
			$out .= "<tr><td>Install file (".$this->file_install.") was deleted!</td></tr>\n";
		} else {
			$out .= "<tr><td style=\"color:#ff0000;\">Delete installation file (".$this->file_install.")!</td></tr>\n";
		}

		// delete update
		if(isset($_POST['delete_update']) && $_POST['delete_update'] == 1 && $this->isFileDeleteable($this->file_update) && @unlink($this->file_update)) {
			$out .= "<tr><td>Update file (".$this->file_update.") was deleted!</td></tr>\n";
		} else {
			$out .= "<tr><td style=\"color:#ff0000;\">Delete update file (".$this->file_update.")!</td></tr>\n";
		}

		$out .= "<tr bgcolor=\"#94AA83\"><td style=\"padding:10px 10px 10px 10px; font-weight:bold;\">Congratulation you have installed Knowledgeroot successfully!</td></tr>\n";
		$out .= "<tr bgcolor=\"#94AA83\"><td style=\"padding:0px 10px 20px 10px;\">For help and more documentation visit the <a href=\"http://www.knowledgeroot.org\">Knowledgeroot project page</a> or the <a href=\"http://forum.linuxdelta.de\">forum</a>.</td></tr>\n";

		$out .= "<tr bgcolor=\"#94AA83\"><td style=\"padding:0px 10px 20px 10px;\">Link to Frontend: <a href=\"index.php\">Frontend</a></td></tr>\n";
		$out .= "<tr bgcolor=\"#94AA83\"><td style=\"padding:0px 10px 20px 10px;\">Link to Backend: <a href=\"admin/index.php\">Backend</a></td></tr>\n";

		$out .= "</table>\n";

		return $out;
	}

	function doUpdate() {
		$out = "";
		$out .= '
		<form dojoType="dijit.form.Form" action="install.php" method="post">
		<table align="center" width="548" cellpadding="1" cellspacing="1" border="0">
		<tr><td><img src="images/knowledgeroot-install.png"></td></tr>
		';

		$config = new Zend_Config_Ini($this->file_config_dist, null, array('allowModifications' => true));

		if($this->CLASS['vars']['db']['type'] == "pgsql") {
			$dump_file = $this->file_pgsql_upgrade_dump;
		} else if($this->CLASS['vars']['db']['type'] == "mysql" || $this->CLASS['vars']['db']['type'] == "mysqli") {
			$dump_file = $this->file_mysql_upgrade_dump;
		} else if($this->CLASS['vars']['db']['type'] == "sqlite") {
			$dump_file = $this->file_sqlite_upgrade_dump;
		} else {
			return "Wrong dbtype!";
		}

		// run sql updates
		$this->doSql($this->readSqlDump($dump_file),$this->CLASS['vars']['db']['type']);

		// set baseurl
		$config->base->base_url = $_POST['baseurl'];

		// set version in database
		//$this->setVersion($this->CLASS['config']->db->adapter);

		// copy all config vars to table settings
		//$this->CLASS['knowledgeroot']->setConfigArr($this->CLASS['vars']);

		// write config.php
		if($this->isFileWriteable($this->file_config) && $_POST['write_config'] == 1 && $this->writeFileContent($this->getConfigContent($this->CLASS['vars']['db']['type'],$this->CLASS['vars']['db']['host'],$this->CLASS['vars']['db']['user'],$this->CLASS['vars']['db']['pass'],$this->CLASS['vars']['db']['database'],$this->CLASS['vars']['db']['schema'],$this->CLASS['vars']['db']['encoding'],"","",$config),$this->file_config)) {
			$out .= "<tr><td>Config (".$this->file_config.") was written!</td></tr>\n";
		} else {
			$out .= '<tr><td>Create ' . $this->file_config . ' and put the following content in it:<br><textarea cols="50" rows="20">'. $this->getConfigContent($this->CLASS['vars']['db']['type'],$this->CLASS['vars']['db']['host'],$this->CLASS['vars']['db']['user'],$this->CLASS['vars']['db']['pass'],$this->CLASS['vars']['db']['database'],$this->CLASS['vars']['db']['schema'],$this->CLASS['vars']['db']['encoding'],"","",$config) ."</textarea></td></tr>\n";
		}

		// delete install
		if(isset($_POST['delete_install']) && $_POST['delete_install'] == 1 && $this->isFileDeleteable($this->file_install) && @unlink($this->file_install)) {
			$out .= "<tr><td>Install file (".$this->file_install.") was deleted!</td></tr>\n";
		} else {
			$out .= "<tr><td style=\"color:#ff0000;\">Delete installation file (".$this->file_install.")!</td></tr>\n";
		}

		// delete update
		if(isset($_POST['delete_update']) && $_POST['delete_update'] == 1 && $this->isFileDeleteable($this->file_update) && @unlink($this->file_update)) {
			$out .= "<tr><td>Update file (".$this->file_update.") was deleted!</td></tr>\n";
		} else {
			$out .= "<tr><td style=\"color:#ff0000;\">Delete update file (".$this->file_update.")!</td></tr>\n";
		}

		$out .= "<tr bgcolor=\"#94AA83\"><td style=\"padding:10px 10px 10px 10px; font-weight:bold;\">Congratulation you have updated Knowledgeroot successfully!</td></tr>";
		$out .= "<tr bgcolor=\"#94AA83\"><td style=\"padding:0px 10px 20px 10px;\">For help and more documentation visit the <a href=\"http://www.knowledgeroot.org\">Knowledgeroot project page</a> or the <a href=\"http://forum.linuxdelta.de\">forum</a>.</td></tr>";

		$out .= "</table>\n";

		return $out;
	}

	function isFileWriteable($file) {
		if(is_file($file)) {
			if(is_writeable($file)) {
				return 1;
			} else {
				return 0;
			}
		} elseif(is_writeable(dirname($file))) {
			return 1;
		} else {
			return 0;
		}
	}

	function isFileDeleteable($file) {
		if(is_file($file)) {
			return $this->isFileWriteable($file);
		}

		return 0;
	}

	function writeFileContent($content,$file) {
		if($this->isFileWriteable($file)) {
			if(($handle = fopen($file,"w+")) === FALSE) return 0;
			if((fwrite($handle, $content)) === FALSE) return 0;
			fclose($handle);

			return 1;
		}

		return 0;
	}

	function getConfigContent($db_type="", $db_host="", $db_user="", $db_pass="", $db_database="", $db_schema="", $db_encoding="", $adminuser, $adminpass, &$config) {
		// set base path
		$config->base->base_path = $this->base_path;

		// set db connection params
		$config->db->adapter = $db_type;
		$config->db->params->host = $db_host;
		$config->db->params->username = $db_user;
		$config->db->params->password = $db_pass;
		$config->db->params->dbname = $db_database;
		$config->db->encoding = $db_encoding;
		$config->db->schema = $db_schema;

		// init writer
		$writer = new Zend_Config_Writer_Ini(array('config' => $config));

		return $writer->render();
	}

	function getInstallForm() {
		$content = '';
		$content .= '
		<script type="text/javascript">
			function changeDbType() {
				switch(dijit.byId(\'dbtype\').value) {
					case "mysql":
					case "mysqli":
						dojo.byId(\'host\').style.visibility = "visible";
						dojo.byId(\'user\').style.visibility = "visible";
						dojo.byId(\'password\').style.visibility = "visible";
						dojo.byId(\'dbname\').style.visibility = "visible";
						dojo.byId(\'dbschema\').style.visibility = "hidden";
						dojo.byId(\'dbencoding\').style.visibility = "hidden";
						break;

					case "pgsql":
						dojo.byId(\'host\').style.visibility = "visible";
						dojo.byId(\'user\').style.visibility = "visible";
						dojo.byId(\'password\').style.visibility = "visible";
						dojo.byId(\'dbname\').style.visibility = "visible";
						dojo.byId(\'dbschema\').style.visibility = "visible";
						dojo.byId(\'dbencoding\').style.visibility = "visible";
						break;

					case "sqlite":
						dojo.byId(\'host\').style.visibility = "hidden";
						dojo.byId(\'user\').style.visibility = "hidden";
						dojo.byId(\'password\').style.visibility = "hidden";
						dojo.byId(\'dbname\').style.visibility = "visible";
						dojo.byId(\'dbschema\').style.visibility = "hidden";
						dojo.byId(\'dbencoding\').style.visibility = "hidden";
						break;
				}
			}
		</script>
		<form dojoType="dijit.form.Form" action="install.php" method="post">
		<table align="center" width="548" cellpadding="1" cellspacing="1" border="0">
		<tr><td colspan="2"><img src="images/knowledgeroot-install.png"></td></tr>
		<tr>
			<td style="padding:4px 4px 25px 4px;" colspan="2"><h2>Welcome to the installation of Knowledgeroot.</h2>
			Now, we need some informations from you to install Knowledgeroot. Fill out
			the following form and click "Start Installation" to install Knowledgeroot.
			</td>
		</tr>
		<tr><td>Base URL:</td><td><input dojoType="dijit.form.TextBox" type="text" name="baseurl" value="'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '') ? 'https://' : 'http://').$this->getBaseUrl().'"></td></tr>
		<tr><td>Type of database:</td><td>
			<select dojoType="dijit.form.Select" id="dbtype" name="db_type" onchange="changeDbType();">
				<option value="mysql" selected="selected">MySQL</option>
				<option value="mysqli">MySQL (mysqli)</option>
				<option value="pgsql">PostgreSQL</option>
				<option value="sqlite">SQLite</option>
			</select>
		</td></tr>
		<tr id="host"><td>Database host:</td><td><input dojoType="dijit.form.TextBox" type="text" name="db_host" value=""></td></tr>
		<tr id="user"><td>Database user:</td><td><input dojoType="dijit.form.TextBox" type="text" name="db_user" value=""></td></tr>
		<tr id="password"><td>Database password:</td><td><input dojoType="dijit.form.TextBox" type="text" name="db_pass" value=""></td></tr>
		<tr id="dbname"><td>Database name:</td><td><input dojoType="dijit.form.TextBox" type="text" name="db_database" value=""></td></tr>
		<tr><td>Create database?</td><td><select dojoType="dijit.form.Select" name="db_create"><option value="0" selected="selected">no</option><option value="1">yes</option></select></td></tr>
		<tr id="dbschema" style="visibility:hidden;"><td>Database schema (only Postgresql):</td><td><input dojoType="dijit.form.TextBox" type="text" name="db_schema" value=""></td></tr>
		<tr id="dbencoding" style="visibility:hidden;"><td>Database encoding (only Postgresql):</td><td><input dojoType="dijit.form.TextBox" type="text" name="db_encoding" value=""></td></tr>
		<tr><td>Username for Adminbackend:</td><td><input dojoType="dijit.form.TextBox" type="text" name="admin_user" value=""></td></tr>
		<tr><td>Password for Adminbackend:</td><td><input dojoType="dijit.form.TextBox" type="text" name="admin_pass" value=""></td></tr>';

		if($this->isFileWriteable($this->file_config)) {
			$content .= '<tr><td>Write config?</td><td><select dojoType="dijit.form.Select" name="write_config"><option value="0">no</option><option value="1" selected="selected">yes</option></select></td></tr>';
		} else {
			$content .= '<tr><td>Write config?</td><td>cannot write config file</td></tr>';
		}

		$content .= '<tr bgcolor="#FF5D5D"><td>Delete install.php?</td><td><select dojoType="dijit.form.Select" name="delete_install"><option value="0">no</option><option value="1" selected="selected">yes</option></select></td></tr>';
		$content .= '<tr bgcolor="#FF5D5D"><td>Delete update.php?</td><td><select dojoType="dijit.form.Select" name="delete_update"><option value="0">no</option><option value="1" selected="selected">yes</option></select></td></tr>';

		$content .= '	<tr bgcolor="#94AA83"><td colspan="2" align="center"><button dojoType="dijit.form.Button" name="submit" type="submit">Start Intallation</button></td></tr>
		</table>
		';

		return $content;
	}

	function getUpdateForm() {
		$content = '';
		$content .= '
		<form dojoType="dijit.form.Form" action="update.php" method="post">
		<table align="center" width="548" cellpadding="1" cellspacing="1" border="0">
		<tr><td colspan="2"><img src="images/knowledgeroot-install.png"></td></tr>
		<tr>
			<td style="padding:4px 4px 25px 4px;" colspan="2"><h2>Welcome to the update of Knowledgeroot.</h2>
			Now, we need some informations from you to update Knowledgeroot. Fill out
			the following form an click "Start Update" to update Knowledgeroot.
			</td>
		</tr>
		<tr><td>Base URL:</td><td><input dojoType="dijit.form.TextBox" type="text" name="baseurl" value="'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '') ? 'https://' : 'http://').$this->getBaseUrl().'"></td></tr>';

		if($this->isFileWriteable($this->file_config)) {
			$content .= '<tr><td>Write config?</td><td><select dojoType="dijit.form.Select" name="write_config"><option value="0">no</option><option value="1" selected="selected">yes</option></select></td></tr>';
		} else {
			$content .= '<tr><td>Write config?</td><td>cannot write config file</td></tr>';
		}

		$content .= '<tr bgcolor="#FF5D5D"><td>Delete install.php?</td><td><select dojoType="dijit.form.Select" name="delete_install"><option value="0">no</option><option value="1" selected="selected">yes</option></select></td></tr>';
		$content .= '<tr bgcolor="#FF5D5D"><td>Delete update.php?</td><td><select dojoType="dijit.form.Select" name="delete_update"><option value="0">no</option><option value="1" selected="selected">yes</option></select></td></tr>';

		$content .= '	<tr bgcolor="#94AA83"><td colspan="2" align="center"><button dojoType="dijit.form.Button" name="submit" type="submit">Start Update</button></td></tr>
		</table>
		';

		return $content;
	}

	function isPgsqlConnect($host,$user,$pass,$db,$schema="",$encoding="") {
		$this->db_connection = pg_connect("host=".$host." dbname=" . $db . " user=".$user." password=".$pass."");

		if($this->db_connection) {
			if($schema != "") {
				pg_query("SET search_path TO ".$schema);
			}

			if($encoding != "") {
				pg_set_client_encoding($this->db_connection, $encoding);
			}

			return 1;
		}

		return 0;
	}

	function isMysqlConnect($host,$user,$pass,$db="") {
		$this->db_connection = mysql_connect($host,$user,$pass);

		if($this->db_connection) {
			if($db != "") {
				$db_conn = mysql_select_db($db, $this->db_connection);

				if($db_conn) {
					return 1;
				}
			} else {
				return 1;
			}
		}

		return 0;
	}

	function isMysqliConnect($host,$user,$pass,$db="") {
		$this->db_connection = mysqli_connect($host,$user,$pass);

		if($this->db_connection) {
			if($db != "") {
				$db_conn = mysqli_select_db($this->db_connection, $db);

				if($db_conn) {
					return 1;
				}
			} else {
				return 1;
			}
		}

		return 0;
	}

	function isSqliteConnect($host,$user,$pass,$db="") {
		$this->db_connection = sqlite_open($db, "0666");

		if($this->db_connection) {
			return 1;
		}

		return 0;
	}

        /**
         * Check if a update is required
         * @param string $dbtype
         * @return bool
         */
        function shouldRunUpdate($dbtype) {
                return true;
        }

	/**
	* Removes comment lines and splits up large sql files into individual queries
	*
	* Last revision: September 23, 2001 - gandon
	*
	* @param   array    the splitted sql commands
	* @param   string   the sql commands
	* @param   integer  the MySQL release number (because certains php3 versions
	*                   can't get the value of a constant from within a function)
	*
	* @return  boolean  always true
	*
	* @access  public
	*/
	function PMA_splitSqlFile(&$ret, $sql, $release)
	{
		// do not trim, see bug #1030644
		//$sql          = trim($sql);
		$sql          = rtrim($sql, "\n\r");
		$sql_len      = strlen($sql);
		$char         = '';
		$string_start = '';
		$in_string    = FALSE;
		$nothing      = TRUE;
		$time0        = time();

		for ($i = 0; $i < $sql_len; ++$i) {
			$char = $sql[$i];

			// We are in a string, check for not escaped end of strings except for
			// backquotes that can't be escaped
			if ($in_string) {
			for (;;) {
				$i         = strpos($sql, $string_start, $i);
				// No end of string found -> add the current substring to the
				// returned array
				if (!$i) {
				$ret[] = array('query' => $sql, 'empty' => $nothing);
				return TRUE;
				}
				// Backquotes or no backslashes before quotes: it's indeed the
				// end of the string -> exit the loop
				elseif ($string_start == '`' || $sql[$i-1] != '\\') {
				$string_start      = '';
				$in_string         = FALSE;
				break;
				}
				// one or more Backslashes before the presumed end of string...
				else {
				// ... first checks for escaped backslashes
				$j                     = 2;
				$escaped_backslash     = FALSE;
				while ($i-$j > 0 && $sql[$i-$j] == '\\') {
					$escaped_backslash = !$escaped_backslash;
					$j++;
				}
				// ... if escaped backslashes: it's really the end of the
				// string -> exit the loop
				if ($escaped_backslash) {
					$string_start  = '';
					$in_string     = FALSE;
					break;
				}
				// ... else loop
				else {
					$i++;
				}
				} // end if...elseif...else
			} // end for
			} // end if (in string)

			// lets skip comments (/*, -- and #)
			elseif (($char == '-' && $sql_len > $i + 2 && $sql[$i + 1] == '-' && $sql[$i + 2] <= ' ') || $char == '#' || ($char == '/' && $sql_len > $i + 1 && $sql[$i + 1] == '*')) {
			$i = strpos($sql, $char == '/' ? '*/' : "\n", $i);
			// didn't we hit end of string?
			if ($i === FALSE) {
				break;
			}
			if ($char == '/') {
				$i++;
			}
			}

			// We are not in a string, first check for delimiter...
			elseif ($char == ';') {
			// if delimiter found, add the parsed part to the returned array
			$ret[]      = array('query' => substr($sql, 0, $i), 'empty' => $nothing);
			$nothing    = TRUE;
			$sql        = ltrim(substr($sql, min($i + 1, $sql_len)));
			$sql_len    = strlen($sql);
			if ($sql_len) {
				$i      = -1;
			} else {
				// The submited statement(s) end(s) here
				return TRUE;
			}
			} // end elseif (is delimiter)

			// ... then check for start of a string,...
			elseif (($char == '"') || ($char == '\'') || ($char == '`')) {
			$in_string    = TRUE;
			$nothing      = FALSE;
			$string_start = $char;
			} // end elseif (is start of string)

			elseif ($nothing) {
			$nothing = FALSE;
			}

			// loic1: send a fake header each 30 sec. to bypass browser timeout
			$time1     = time();
			if ($time1 >= $time0 + 30) {
			$time0 = $time1;
			header('X-pmaPing: Pong');
			} // end if
		} // end for

		// add any rest to the returned array
		if (!empty($sql) && preg_match('@[^[:space:]]+@', $sql)) {
			$ret[] = array('query' => $sql, 'empty' => $nothing);
		}

		return TRUE;
	} // end of the 'PMA_splitSqlFile()' function

	/**
	* Reads a file and split all statements in it.
	*
	* @param $file String Path to the SQL-dump-file
	*/
	function readSqlDump($file) {
		if (is_file($file) && is_readable($file)) {
			$ret = array ();
			$sqlsplit = '';
			$fileContent = file_get_contents($file);
			$this->PMA_splitSqlFile($sqlsplit, $fileContent, '');

			if (is_array($sqlsplit)) {
				foreach ($sqlsplit as $qry) {
					$ret[] = $qry['query'];
				}
			}

			return $ret;
		}

		return false;
	}

	function doSql($arr, $dbtype) {
		if(is_array($arr)) {
			foreach($arr as $key => $value) {
				if($dbtype == "pgsql") {
					pg_query($this->db_connection, $value);
				}

				if($dbtype == "mysql") {
					mysql_query($value, $this->db_connection);
				}

				if($dbtype == "mysqli") {
					mysqli_query($this->db_connection, $value);
				}

				if($dbtype == "sqlite") {
					sqlite_query($this->db_connection, $value);
				}
			}
		}

		return "";
	}

	/**
	 * FROM Typo3
	 * AddSlash array
	 * This function traverses a multidimentional array and adds slashes to the values.
	 * NOTE that the input array is and argument by reference.!!
	 * Twin-function to stripSlashesOnArray
	 * Usage: 8
	 *
	 * @param	array		Multidimensional input array, (REFERENCE!)
	 * @return	array
	 */
	function addSlashesOnArray(&$theArray)	{
		if(get_magic_quotes_gpc() == 0) {
			if (is_array($theArray))	{
				reset($theArray);
				while(list($Akey,$AVal)=each($theArray))	{
					if (is_array($AVal))	{
						$this->addSlashesOnArray($theArray[$Akey]);
					} else {
						$theArray[$Akey] = addslashes($AVal);
					}
				}
				reset($theArray);
			}
		}
	}

	/**
	 * try to get the baseurl
	 *
	 * @return string
	 */
	function getBaseUrl() {
		$baseurl = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		preg_match("/(.*\/).*/", $baseurl, $url_arr);

		if($url_arr[1] == "")
			$url_arr[1] = $_SERVER['HTTP_HOST'];

		return $url_arr[1];
	}

	/**
	 * write baseurl to database
	 *
	 * @param string $baseurl base url
	 * @param string $dbtype db type
	 */
	function setBaseUrl($baseurl, $dbtype) {
		$sql = "UPDATE settings SET value='".$baseurl."' WHERE name='baseurl'";
		$this->doSql(array($sql), $dbtype);
	}

	/**
	 * Check if a small update is required
	 * @param string $dbtype
	 * @return bool
	 */
	function shouldRunSmallUpdate($dbtype) {
		if($dbtype == "mysql") {
			$res = @mysql_query("SELECT id FROM settings WHERE name='knowledgeroot.showlogo'");
		} elseif($dbtype == "mysqli") {
			$res = @mysqli_query("SELECT id FROM settings WHERE name='knowledgeroot.showlogo'");
		} elseif($dbtype == "pgsql") {
			$res = @pg_query("SELECT id FROM settings WHERE name='knowledgeroot.showlogo'");
		} else {
			return false;
		}

		if(!$res) {
			// should run normal update
			return false;
		} else {
			if($dbtype == "mysql") $count = mysql_num_rows($res);
			elseif($dbtype == "mysqli") $count = mysqli_num_rows($res);
			else $count = pg_num_rows($res);

			if($count == 1) return true;
			// do not run update
			return false;
		}
	}

	function setVersion($dbtype) {
		require_once('version.php');
		if($dbtype == "pgsql") {
			pg_query($this->db_connection, "UPDATE settings SET value='".$version."' WHERE name='version'");
		}

		if($dbtype == "mysql") {
			mysql_query("UPDATE settings SET value='".$version."' WHERE name='version'", $this->db_connection);
		}

		if($dbtype == "mysqli") {
			mysqli_query($this->db_connection, "UPDATE settings SET value='".$version."' WHERE name='version'");
		}

		if($dbtype == "sqlite") {
			sqlite_query($this->db_connection, "UPDATE settings SET value='".$version."' WHERE name='version'");
		}
	}
}
?>
