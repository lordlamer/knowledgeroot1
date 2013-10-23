<?php
/******************************
 * Knowledgeroot
 * Frank Habermann
 * 21.09.2006
 *
 * Version 0.1
 * This Class shows informations in the admin interface
 ******************************/

class admin_extension extends extension_base {

	function main() {
		$content = "";

		// load css file for register extension
		$this->CLASS['kr_header']->addcsssrc("../" . $this->myPath . $this->CONF['cssfile']);

		// add menu item to admin navi
		$this->menu['admin']['extension']['name'] = $this->CLASS['translate']->_('extensions');
		$this->menu['admin']['extension']['link'] = "index.php?action=show_ext";
		$this->menu['admin']['extension']['priority'] = "50";

		$this->menu['admin']['extension_sub2']['parent'] = "extension";
		$this->menu['admin']['extension_sub2']['name'] = $this->CLASS['translate']->_('import');
		$this->menu['admin']['extension_sub2']['link'] = "index.php?action=import_ext";
		$this->menu['admin']['extension_sub2']['priority'] = "52";

		// check if informations should be shown
		if((isset($_GET['action']) and $_GET['action'] == "show_ext") || (isset($_GET['action']) and $_GET['action'] == "configure_ext") || (isset($_POST['action']) and $_POST['action'] == "configure_ext")) {
			if(isset($_GET['do']) and $_GET['do'] == "admin_extension_disable_ext") {
				$this->CLASS['kr_extension']->disableExtension($_GET['ext']);
			}

			if(isset($_GET['do']) and $_GET['do'] == "admin_extension_enable_ext") {
				$this->CLASS['kr_extension']->enableExtension($_GET['ext']);
			}

			if(isset($_GET['do']) and $_GET['do'] == "admin_extension_install_ext") {
				$content = $this->installExtension($_GET['ext']);
			}

			if(isset($_POST['do']) and $_POST['do'] == "admin_extension_install_ext") {

				$content = $this->sqlInstallExtension($_POST['ext'],$_POST['performsql']);
			}

			if($content == "") {
				$content = $this->show_ext();
			}
		}

		if((isset($_GET['action']) and $_GET['action'] == "import_online_extension") || (isset($_POST['action']) and $_POST['action'] == "import_online_extension")) {
			if(!isset($_GET['ext'])) $_GET['ext'] = "";
			if(!isset($_POST['ext'])) $_POST['ext'] = "";
			$keyname = $_GET['ext'] != "" ? $_GET['ext'] : $_POST['ext'];
			if(!isset($_GET['overwrite'])) $_GET['overwrite'] = "";
			if(!isset($_POST['overwrite'])) $_POST['overwrite'] = "";

			$overwrite = $_GET['overwrite'] != "" ? $_GET['overwrite'] : $_POST['overwrite'];
			$overwrite = $overwrite == 1 ? true : false;

			$content = $this->onlineImportForm($keyname, $overwrite);
		}

		if(isset($_POST['action']) and $_POST['action'] == "fetch_rep_list") {
			if(!$this->fetch_ext_list()) {
				$content = "could not connect to repository server";
			}

			$content .= $this->show_import_ext();
		}

		if(isset($_GET['action']) and $_GET['action'] == "import_ext") {
			$content = $this->show_import_ext();
		}

		if(isset($_GET['action']) and $_GET['action'] == "ext_download") {
			$this->download_ext();
		}

		if(isset($_POST['action']) and $_POST['action'] == "import_ext") {
			//print_r($_POST);
			$content = $this->import_ext();
		}

		return $content;
	}

	// show informations
	function show_ext() {
		$out = "";
		$keynames = array();

		$out .= "<div class=\"extension_list_header\">Extensions</div>\n";
		$out .= "<table class=\"extension_list\" cellpadding=\"2\" cellspacing=\"1\">\n";
		$out .= "<tr class=\"extension_list_title\"><td>title</td><td>keyname</td><td>version</td><td>sys</td><td>DL</td><td>state</td><td>action</td></tr>";

		// select extensions from db
		$ext_db = array();
		$res = $this->CLASS['db']->query("SELECT * FROM extensions");
		while($row = $this->CLASS['db']->fetch_assoc($res)) {
			$ext_db[$row['keyname']]['active'] = $row['active'];
		}

		$ext_path_rep[1] = $this->CLASS['config']->base->base_path . "extension/";
		$ext_path_rep[2] = $this->CLASS['config']->base->base_path . "/system/extension/";
		$ext_path_rep[3] = $this->CLASS['config']->base->base_path . "system/sysext/";

		foreach($ext_path_rep as $key_rep => $ext_path) {
			if (is_dir($ext_path) && $handle = opendir($ext_path)) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != "CVS" && $file != ".svn" && !isset($keynames[$file])) {
						$ext_info_file = $ext_path . $file . "/info.php";

						if(is_file($ext_info_file)) {
							$keynames[$file] = true;
							include($ext_info_file);

							$protectme = false;
							$protect = explode(",",$this->CONF['protect_extensions']);
							foreach($protect as $pkey => $pvalue) {
								if(trim($pvalue) == $file) {
									$protectme = true;
								}
							}

							// install or enable or disable ext
							if(isset($ext_db[$file]['active']) and $ext_db[$file]['active'] == "1") {
								$action = "<a href=\"index.php?action=show_ext&amp;do=admin_extension_disable_ext&amp;ext=".$file."\" alt=\"disable\" title=\"disable\">disable</a>";
								$css_class = "extension_list_action_disable";
							} elseif(isset($ext_db[$file]['active']) and $ext_db[$file]['active'] == "0") {
								$action = "<a href=\"index.php?action=show_ext&amp;do=admin_extension_enable_ext&amp;ext=".$file."\" alt=\"enable\" title=\"enable\">enable</a>";
								$css_class = "extension_list_action_enable";
							} else {
								$action = "<a href=\"index.php?action=show_ext&amp;do=admin_extension_install_ext&amp;ext=".$file."\" alt=\"install\" title=\"install\">install</a>";
								$css_class = "extension_list_action_install";
							}

							// save sys extensions
							if($protectme == true) {
								$action = "";
							}

							$out .= "\t<tr class=\"".$css_class."\"><td>".$CONF['title']."</td><td>".$file."</td><td>".$CONF['version']."</td><td>".(($ext_path == $this->CLASS['config']->base->base_path . "system/sysext/") ? "X" : "")."</td><td><a href=\"index.php?action=ext_download&name=" . $file . "\"><img src=\"../" . $this->myPath . "res/download.png\" alt=\"download\" title=\"download\" border=\"0\" /></a></td><td class=\"extension_list_state_".$CONF['state']."\">".$CONF['state']."</td><td>".$action."</td></tr>\n";

							unset($CONF);
						}
					}
				}
				closedir($handle);
			}
		}

		$out .= "</table>\n";

		return $out;
	}

	// download ext
	function download_ext() {
		if(isset($_GET['name']) && $_GET['name'] != "") {
			// clean extensionname
			$_GET['name'] = preg_replace('/[^a-zA-Z0-9 \-_]/m', '', $_GET['name']);

			$ext_path = $this->CLASS['config']->base->base_path . "/" . $this->CLASS['kr_extension']->checkExtensionFolder($_GET['name']) . $_GET['name'] . "/";

			include($ext_path . "info.php");

			$ext = $this->CLASS['kr_extension']->makeExtension($_GET['name'], $ext_path);

			header('Accept-Ranges: bytes');
			header('Content-Length: '.strlen($ext).'');
			header('Keep-Alive: timeout=15, max=100');
			header('Content-type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.$_GET['name'].'_' . $CONF['version'] . '.krx"');

			echo $ext;

			exit();
		}
	}

	// show import extensions
	function show_import_ext() {
		$out = '
			<fieldset>
				<legend>Upload Extension File directly</legend>
			<form action="index.php" method="post" enctype="multipart/form-data">
				<input type="hidden" name="action" value="import_ext">
				Extension to import (.krx):<input type="file" name="extension"><br>
				Overwrite existing files: <input type="checkbox" name="overwrite" value="true"><br>
				<input type="submit" name="submit" value="import">
			</form>

			</fieldset>

			<fieldset>
				<legend>Import from online repository</legend>
			<form action="index.php" method="post">
				<input type="hidden" name="action" value="fetch_rep_list">
				<input type="submit" name="submit" value="update repository list">
			</form>
		';

		// select extensions from db
		$ext_db = array();
		$res = $this->CLASS['db']->query("SELECT * FROM extensions");
		while($row = $this->CLASS['db']->fetch_assoc($res)) {
			$ext_db[$row['keyname']]['active'] = $row['active'];
		}

		$out .= "<table class=\"extension_list\" cellpadding=\"2\" cellspacing=\"1\">\n";
		$out .= "<tr class=\"extension_list_title\"><td>title</td><td>keyname</td><td>version</td><td>current version</td><td>DL</td><td>state</td><td>action</td></tr>";

		$cache_file = $this->CLASS['config']->base->base_path . "/" . $this->CLASS['config']->upload->path . "admin_ext_rep_list.cache";

		if(is_file($cache_file)) {
			$out .= "last repository update: " . date ("F d Y H:i:s.", filemtime($cache_file)) . "<br>\n";

			$cache = file($cache_file);
			$file_content = "";
			foreach($cache as $key => $value) {
				$file_content .= $value;
			}

			$ext_arr = unserialize($file_content);

			if(is_array($ext_arr)) {
				foreach($ext_arr as $key => $value) {
					if(isset($ext_db[$key]['active']) and $ext_db[$key]['active'] == "1") {
						$css_class = "extension_list_action_disable";
					} elseif(isset($ext_db[$key]['active']) and $ext_db[$key]['active'] == "0") {
						$css_class = "extension_list_action_enable";
					} else {
						$css_class = "extension_list_action_install";
					}

					$cur_version = "";

					$ext_path = $this->CLASS['config']->base->base_path . $this->CLASS['kr_extension']->checkExtensionFolder($key);
					if(file_exists($ext_path . $key . "/info.php")) {
						include($ext_path . $key . "/info.php");
						$cur_version = $CONF['version'];
						unset($CONF);
					}

					$action = "<a href=\"index.php?action=import_online_extension&amp;ext=".$key."\">import</a>";
					$dl_ext_ling = $this->CONF['repository-server'] . "index.php?action=kx_ext_fetch_extension&keyname=".$key;
					$out .= "\t<tr class=\"".$css_class."\"><td>".$ext_arr[$key]['title']."</td><td>".$key."</td><td>".$ext_arr[$key]['version']."</td><td>".$cur_version."</td><td><a href=\"".$dl_ext_ling."\"><img src=\"../" . $this->myPath . "res/download.png\" alt=\"download\" title=\"download\" border=\"0\" /></a></td><td class=\"extension_list_state_".$ext_arr[$key]['state']."\">".$ext_arr[$key]['state']."</td><td>".$action."</td></tr>\n";
				}
			}
		}

		$out .= "</table></fieldset>";

		return $out;
	}

	function onlineImportForm($keyname, $overwrite = false) {
		$ext_path = $this->CLASS['config']->base->base_path . "/extension/";

		$out = "";

		if(file_exists($ext_path . $keyname . "/class-".$keyname.".php") && $overwrite == false) {
			$out .= "<div>Extension '".$keyname."' already exists!</div>";
			$out .= '
				<form action="index.php" method="post">
					<input type="hidden" name="action" value="import_online_extension">
					<input type="hidden" name="ext" value="'.$keyname.'">
					overwrite? <input type="checkbox" name="overwrite" value="1"><br />
					<input type="submit" name="submit" value="import">
				</form>
			';
		} else {
			$out .= $this->fetch_extension($keyname, $overwrite);
		}

		return $out;
	}

	function import_ext() {
		$uploaddir = $this->CLASS['config']->base->base_path . "/" . $this->CLASS['config']->upload->path;

		if(!is_dir($uploaddir)) {
			echo "no uploadfolder";
			return 0;
		}

		if(move_uploaded_file($_FILES['extension']['tmp_name'], $uploaddir . $_FILES['extension']['name'])) {
			$fp = fopen($uploaddir.$_FILES['extension']['name'],"r");
			$buffer = fread($fp,filesize($uploaddir.$_FILES['extension']['name']));
			fclose($fp);
			unlink($uploaddir . $_FILES['extension']['name']);

			$ext_arr = $this->CLASS['kr_extension']->getExtensionData($buffer);

			if(!is_dir($this->CLASS['config']->base->base_path . "/extension/" . $ext_arr['keyname'])) {
				mkdir($this->CLASS['config']->base->base_path . "/extension/" . $ext_arr['keyname']);
				$this->CLASS['kr_extension']->saveExtension($ext_arr['files'], $this->CLASS['config']->base->base_path . "/extension/" . $ext_arr['keyname']);
				$out = "extension " . $ext_arr['keyname'] . " importet";
			} elseif(is_dir($this->CLASS['config']->base->base_path . "/extension/" . $ext_arr['keyname']) && $_POST['overwrite'] == "true") {
				$this->remove_directory($this->CLASS['config']->base->base_path . "/extension/" . $ext_arr['keyname']);
				mkdir($this->CLASS['config']->base->base_path . "/extension/" . $ext_arr['keyname']);
				$this->CLASS['kr_extension']->saveExtension($ext_arr['files'], $this->CLASS['config']->base->base_path . "/extension/" . $ext_arr['keyname']);
				$out = "extension " . $ext_arr['keyname'] . " importet and old was overwritten!";
			}
		} else {
			echo "cannot move to cache";
			return 0;
		}

		return $out;
	}

	function remove_directory($dir) {
		if ($handle = opendir("$dir")) {
			while (false !== ($item = readdir($handle))) {
				if ($item != "." && $item != "..") {
					if (is_dir("$dir/$item")) {
						$this->remove_directory("$dir/$item");
					} else {
						if(!@unlink("$dir/$item")) return false;
					}
				}
			}

			closedir($handle);
			if(!@rmdir($dir)) return false;

			return true;
		}
	}

	function fetch_ext_list() {
		$server = $this->CONF['repository-server'];
		$serverscript = "index.php?action=kx_ext_fetch_ext_list";

		if($file_arr = @file($server . $serverscript)) {
			$file_content = "";
			foreach($file_arr as $key => $value) {
				$file_content .= $value;
			}

			$parts = explode(":", $file_content);
			$data = base64_decode($parts[1]);
			if(md5($data) == $parts[0]) {
				$uploaddir = $this->CLASS['config']->base->base_path . "/" . $this->CLASS['config']->upload->path;
				if($handle = @fopen($uploaddir."admin_ext_rep_list.cache", "w")) {
					fwrite($handle, $data);
					fclose($handle);
					return 1;
				}
			}

			return 0;
		} else {
			return 0;
		}
	}

	function fetch_extension($keyname, $overwrite=false) {
		$out = "";

		$server = $this->CONF['repository-server'];
		$serverscript = "index.php?action=kx_ext_fetch_extension&keyname=".$keyname;

		if($keyname != "") {
			if($file_arr = @file($server . $serverscript)) {
				$file_content = "";
				foreach($file_arr as $key => $value) {
					$file_content .= $value;
				}

				$ext_arr = $this->CLASS['kr_extension']->getExtensionData($file_content);

				$install = true;

				if(isset($ext_arr['keyname']) and is_dir($this->CLASS['config']->base->base_path . "/extension/" . $ext_arr['keyname'])) {
					if($overwrite == true) {
						if(!$this->remove_directory($this->CLASS['config']->base->base_path . "/extension/" . $ext_arr['keyname'])) {
							$install = false;
							$out = "could not delete existing directory";
						}
					} else {
						$install = false;
						$out = "could not write in existing directory";
					}
				}

				if($install == true && isset($ext_arr['keyname'])) {
					if(@mkdir($this->CLASS['config']->base->base_path . "/extension/" . $ext_arr['keyname'])) {
						$this->CLASS['kr_extension']->saveExtension($ext_arr['files'], $this->CLASS['config']->base->base_path . "/extension/" . $ext_arr['keyname']);
						$out = "extension " . $ext_arr['keyname'] . " importet";
					} else {
						$out = "could not create extension directory - permission denied";
					}
				}
			} else {
				$out = "could not connect to repository server";
			}
		}

		return $out;
	}

	function installExtension($keyname) {
		$ext_path = $this->CLASS['config']->base->base_path . "/extension/";

		$out = "";
		$sql = $this->getSqlCommands($keyname);

		if($sql != "") {
			$out .= '
				<form action="index.php" method="post">
					<input type="hidden" name="action" value="configure_ext">
					<input type="hidden" name="do" value="admin_extension_install_ext">
					<input type="hidden" name="ext" value="'.$keyname.'">
			';

			$out .= "Following SQL-Commands will be done by install:<br />\n";

			$out .= "<div>";
			$out .= nl2br($sql);
			$out .= "</div>";

			$out .= '
				Do SQL?<input type="checkbox" name="performsql" value="1" checked="checked">
				<input type="submit" name="submit" value="Install">
				</form>
			';
		} else {
			$this->CLASS['kr_extension']->installExtension($keyname);
		}

		return $out;
	}

	function sqlInstallExtension($keyname, $dosql) {
		$content = "";

		if($dosql == 1) {
			$sql = $this->getSqlCommands($keyname);
			$sqlsplit = '';

			$this->PMA_splitSqlFile($sqlsplit,$sql,'');

			if (is_array($sqlsplit)) {
				foreach ($sqlsplit as $qry) {
					$sql_arr[] = $qry['query'];
				}
			}

			$this->doSql($sql_arr);

			$this->CLASS['kr_extension']->installExtension($keyname);
			$content = "extension installed with sql!";

		} else {
			$this->CLASS['kr_extension']->installExtension($keyname);
			$content = "extension installed!";
		}

		return $content;
	}

	function getSqlCommands($keyname) {
		$ext_path = $this->CLASS['config']->base->base_path . "/extension/";

		$out = "";
		if(is_file($ext_path . $keyname . "/install.php")) {
			include($ext_path . $keyname . "/install.php");

			// check for installed version and get version number
			$res = $this->CLASS['db']->query("SELECT * FROM extensions WHERE keyname='".$keyname."'");
			$anz = $this->CLASS['db']->num_rows($res);

			if($anz == 0) {
				$installed_version = "";
			} else {
				$row = $this->CLASS['db']->fetch_assoc($res);
				$installed_version = $row['version'];
			}

			// show sqlcommands
			if($installed_version == "") {
				if(is_file($ext_path . $keyname . "/dumps/".$CONF['version']."/".$this->CLASS['db']->dbname.".sql")) {
					$sql_commands = file($ext_path . $keyname . "/dumps/".$CONF['version']."/".$this->CLASS['db']->dbname.".sql");
					if(is_array($sql_commands)) {
						foreach($sql_commands as $key => $value) {
							$out .= trim($value) . "\n";
						}
					} else {
						$out .= $sql_commands;
					}
				}
			} else {
				$get_commands = 0;
				$version_arr = explode(";", $CONF['version_history']);
				foreach($version_arr as $key => $value) {
					if($version_arr[$key] == $installed_version) {
						$get_commands = 1;
					}

					if($get_commands == 1) {
						if(is_file($ext_path . $keyname . "/dumps/".$version_arr[$key]."/upgrade_".$this->CLASS['db']->dbname.".sql")) {
							$sql_commands = file($ext_path . $keyname . "/dumps/".$version_arr[$key]."/upgrade_".$this->CLASS['db']->dbname.".sql");
							if(is_array($sql_commands)) {
								foreach($sql_commands as $key => $value) {
									$out .= trim($value) . "\n";
								}
							} else {
								$out .= $sql_commands;
							}
						}
					}
				}
			}

			unset($CONF);
		}

		return $out;
	}

	function doSql($arr) {
		if(is_array($arr)) {
			foreach($arr as $key => $value) {
				$this->CLASS['db']->query($value);
			}
		}

		return "";
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

}

?>
