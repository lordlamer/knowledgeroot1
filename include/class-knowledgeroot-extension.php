<?php
/**
 * This Class is for extension work
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-knowledgeroot-extension.php 1006 2011-03-24 20:34:20Z lordlamer $
 */
class knowledgeroot_extension {
	var $CLASS = array();

	var $menu = array(); // var for the menu

	var $content = ""; // content that should be displayed in the mainframe
	var $default_content = ""; // will be shown if content is empty

	/**
	 * init/start class
	 * @param array $CLASS
	 * @param integer $adminext
	 */
	function start(&$CLASS, $adminext = 0) {
		$this->CLASS =& $CLASS;

		// load extensions
		$this->loading_extensions($adminext);
		return 0;
	}

	/**
	 * get list of extensions to load them
	 * @param integer $adminext
	 */
	function loading_extensions($adminext = 0) {
		$hashkey = md5('extensions_'.$adminext);
		if(!($data = $this->CLASS['cache']->load($hashkey))) {
			if($adminext) {
				$query = "SELECT * FROM extensions WHERE active=1 AND admin=1";
			} else {
				$query = "SELECT * FROM extensions WHERE active=1 AND admin=0";
			}

			$res = $this->CLASS['db']->query($query);
			while($row = $this->CLASS['db']->fetch_assoc($res)) {
				$data[] = $row;
			}

			$this->CLASS['cache']->save($data, $hashkey, array('system','extensions'));
		}

		foreach($data as $row) {
			// set default extension folder
			$folder = "system/extension/";


			$this->load_extension($row['keyname'], $this->checkExtensionFolder($row['keyname']));
		}

		return 0;
	}

	/**
	 * will check in which folder is extension located
	 * @param string $extension keyname of extension
	 * @return string relativ path of extension
	 */
	function checkExtensionFolder($extension) {
		$path = "";

		/*
		 * check folders in that order
		 * 1. extension/
		 * 2. system/extension/
		 * 3. system/sysext/
		 */
		if(is_file($this->CLASS['config']->base->base_path . "extension/" . $extension . "/info.php")) {
			$path = "extension/";
		} elseif(is_file($this->CLASS['config']->base->base_path . "system/extension/" . $extension . "/info.php")) {
			$path = "system/extension/";
		} elseif(is_file($this->CLASS['config']->base->base_path . "system/sysext/" . $extension . "/info.php")) {
			$path = "system/sysext/";
		} else {
			$path = "extension/";
		}

		return $path;
	}

	/**
	 * load a extension to knowledgeroot
	 * @param string $extension name of extension
	 * @param string $ext_folder folder of extension
	 */
	function load_extension($extension, $ext_folder = "") {
		if (!isset ($this->CLASS['extension'][$extension]['init']) or $this->CLASS['extension'][$extension]['init'] != TRUE) {
			$classfile = $this->CLASS['config']->base->base_path . $ext_folder . $extension . "/class-" . $extension . ".php";
			$classname = $extension;
			$infofile = $this->CLASS['config']->base->base_path . $ext_folder . $extension . "/info.php";
			$langfile = $this->CLASS['config']->base->base_path . $ext_folder . $extension . "/language.php";
			$configfile = $this->CLASS['config']->base->base_path . $ext_folder . $extension . "/config.php";
			$langfolder = $this->CLASS['config']->base->base_path . $ext_folder . $extension . "/language";
			$gettextFolder = "locale";
			$gettextSubFolder = "LC_MESSAGES";
			$gettextDomain = $extension;

			if(is_file($infofile)) {
				$CONF = array();
				include($infofile);

				$this->CLASS['extension'][$extension]['info'] = $CONF;
				unset($CONF);

				// check for different classname
				if(isset($this->CLASS['extension'][$extension]['info']['classname']) && $this->CLASS['extension'][$extension]['info']['classname'] != '')
				{
						$classname = $this->CLASS['extension'][$extension]['info']['classname'];
				}

				// check for different classfile
				if(isset($this->CLASS['extension'][$extension]['info']['classfile']) && $this->CLASS['extension'][$extension]['info']['classfile'] != '')
				{
						$classfile = $this->CLASS['config']->base->base_path . $ext_folder . $extension . "/" . $this->CLASS['extension'][$extension]['info']['classfile'];
				}

				// check for gettext_folder
				if(isset($this->CLASS['extension'][$extension]['info']['gettext_folder']) && $this->CLASS['extension'][$extension]['info']['gettext_folder'] != '')
				{
						$gettextFolder = $this->CLASS['extension'][$extension]['info']['gettext_folder'];
				}

				// check for gettext_subfolder
				if(isset($this->CLASS['extension'][$extension]['info']['gettext_subfolder']) && $this->CLASS['extension'][$extension]['info']['gettext_subfolder'] != '')
				{
						$gettextSubFolder = $this->CLASS['extension'][$extension]['info']['gettext_subfolder'];
				}

				// check for gettext_domain
				if(isset($this->CLASS['extension'][$extension]['info']['gettext_domain']) && $this->CLASS['extension'][$extension]['info']['gettext_domain'] != '')
				{
						$gettextDomain = $this->CLASS['extension'][$extension]['info']['gettext_domain'];
				}

				// check for dependencies
				if($this->CLASS['extension'][$extension]['info']['dependencies'] != "") {
					$ext_list = explode(",",$this->CLASS['extension'][$extension]['info']['dependencies']);

					if(!is_array($ext_list)) {
						$ext_list = array();
					}

					foreach($ext_list as $ext_key => $ext_val) {
						if((!isset($this->CLASS['extension'][$ext_val]['init'])) || (isset($this->CLASS['extension'][$ext_val]['init']) && $this->CLASS['extension'][$ext_val]['init'] != TRUE)) {
							// load dependend extension
							if(!$this->load_extension($ext_val, $this->checkExtensionFolder($ext_val))) {
								return 0;
							}
						}
					}
				}
			}

			if(is_file($classfile)) {
				include($classfile);

				$this->CLASS['extension'][$extension]['class_file'] = $classfile;
				$this->CLASS['extension'][$extension]['init'] = TRUE;
				$this->CLASS['extension'][$extension]['class'] = new $classname($this->CLASS);
				$this->CLASS['extension'][$extension]['class']->myPath = $ext_folder . $extension . "/";
				$this->CLASS['extension'][$extension]['class']->myAbsolutePath = $this->CLASS['config']->base->base_path . $ext_folder . $extension . "/";

				if(is_file($configfile)) {
					include($configfile);

					// load default config from the extension config
					$this->CLASS['extension'][$extension]['class']->CONF = $CONF;

					// load config from global config file and replace values
					if (isset ($this->CLASS['vars']['ext'][$extension]) and is_array($this->CLASS['vars']['ext'][$extension])) {
						$this->CLASS['extension'][$extension]['class']->CONF = $this->CLASS['knowledgeroot']->replace_array($this->CLASS['extension'][$extension]['class']->CONF,$this->CLASS['vars']['ext'][$extension]);
					}

					unset($CONF);
				}

				// load language from language.php
				if(is_file($langfile)) {
					$LANG = array();
					include($langfile);

					$this->CLASS['language']->load_ext_lang($extension, $LANG);
					unset($LANG);
				}

				// load gettext stuff
				$gettextFile = $this->CLASS['extension'][$extension]['class']->myAbsolutePath . $gettextFolder . "/" . $this->CLASS['translate']->getLocale() . ".UTF8/" . $gettextSubFolder . "/" . $gettextDomain . ".mo";
				if(is_file($gettextFile))
				{
					$this->CLASS['translate']->addTranslation($gettextFile, $this->CLASS['translate']->getLocale());
				}

				return 1;
			}

			return 0;
		}
	}

	/**
	 * this function start all extensions with the function main
	 */
	function start_extensions() {
		if (isset ($this->CLASS['extension']) and is_array($this->CLASS['extension'])) {
			foreach($this->CLASS['extension'] as $key => $value) {
				// check for dependencies
				if($this->CLASS['extension'][$key]['info']['dependencies'] != "") {
					$ext_list = explode(",",$this->CLASS['extension'][$key]['info']['dependencies']);

					if(!is_array($ext_list)) {
						$ext_list = array();
					}

					foreach($ext_list as $ext_key => $ext_val) {
						if((!isset($this->CLASS['extension'][$ext_val]['init'])) || (isset($this->CLASS['extension'][$ext_val]['init']) && $this->CLASS['extension'][$ext_val]['init'] != TRUE)) {
							// load dependend extension
							$this->content .= $this->start_extension($ext_val);
						}
					}
				}

				$this->content .= $this->start_extension($key);
			}
		}

		// loading menus
		$this->load_menus();
	}

	/**
	 * this function will start one extension
	 */
	function start_extension($extension) {
		$res = "";

		// set started to false if it is not set
		if(!isset($this->CLASS['extension'][$extension]['started']))
			$this->CLASS['extension'][$extension]['started'] = FALSE;

		if(isset($this->CLASS['extension'][$extension]['init']) && $this->CLASS['extension'][$extension]['init'] == TRUE && $this->CLASS['extension'][$extension]['started'] == FALSE) {
			$content = $this->CLASS['extension'][$extension]['class']->main();
			$this->CLASS['extension'][$extension]['started'] = TRUE;

			if($content != "0" && $content != "1" && $content != "") {
				$res = $content;
			}
		}

		return $res;
	}

	/**
	 * load the menues from the extensions
	 */
	function load_menus() {
		if (isset ($this->CLASS['extension']) and is_array($this->CLASS['extension'])) {
			foreach($this->CLASS['extension'] as $key => $value) {
				if(isset($this->CLASS['extension'][$key]['init']) && $this->CLASS['extension'][$key]['init'] == TRUE) {
					$this->menu = array_merge_recursive($this->menu, $this->CLASS['extension'][$key]['class']->menu);
				}
			}
		}
	}

	/**
	 * Create a menu
	 *
	 * @param string $name name of menu
	 * @param integer $id id of page
	 * @param integer $pagerights rights of the page
	 * @param integer $contentrights rights of the content
	 * @param string $extension name of extension
	 * @return string return menu as html
	 */
	function show_menu($name,$id = "", $pagerights = null, $contentrights = null,$extension = "") {
		$out = "";
		$menuarr = array();

		if(!isset($this->menu[$name])) $this->menu[$name] = array();

		if(!is_array($this->menu[$name])) {
			$this->menu[$name] = array();
		}

		foreach($this->menu[$name] as $key => $value) {
			// check for config - config is no real menu
			if($key == "config") {
				continue;
			}

			if(isset($_SESSION['_hide_menu_']) && $_SESSION['_hide_menu_'] == true && ($name == 'content' || $name == 'page') && (!isset($this->menu[$name][$key]['donothide']) || (isset($this->menu[$name][$key]['donothide']) && $this->menu[$name][$key]['donothide'] != 1)))
				continue;

			// check for extension to display the navi
			if($name == "content" || $name == "contentline" || $name == "contentcontext") {
				$show = 0;

				if (isset($this->menu[$name][$key]['contenttype'])) {
					$list = explode(",", $this->menu[$name][$key]['contenttype']);

					if(!is_array($list)) {
						$list = array();
					}

					foreach($list as $listkey => $listvalue) {
						// check if menu item should be displayed at this content
						$listvalue = trim($listvalue);
						$extension = trim($extension);

						if(($listvalue == $extension) || (($listvalue == "" || $listvalue == "text") && ($extension == "" || $extension == "text"))) {
							$show = 1;
							continue;
						}
					}
				} else { // no contenttype is set so menuitem will be shown at every content/contentline
					$show = 1;
				}

				// check if item showed be shown, if not go to next item
				if($show == 0) {
					continue;
				}
			}

			// check for tooltip
			if (isset ($this->menu[$name][$key]['tooltip']) and $this->menu[$name][$key]['tooltip'] != "") {
				$title = " title=\"" . $this->menu[$name][$key]['tooltip'] . "\"";
			} else {
				$title = " title=\"" . $this->menu[$name][$key]['name'] . "\"";
			}

			// check for target
			if (isset ($this->menu[$name][$key]['target']) and $this->menu[$name][$key]['target'] != "") {
				$target = " target=\"" . $this->menu[$name][$key]['target'] . "\"";
			} else {
				$target = "";
			}

			// check for extra a tag parameter
			if (isset ($this->menu[$name][$key]['atagparams']) and $this->menu[$name][$key]['atagparams'] != "") {
				$atagparams = " " . trim($this->menu[$name][$key]['atagparams']);
			} else {
				$atagparams = "";
			}

			if(isset($this->menu[$name][$key]['link'])) $menulink = $this->menu[$name][$key]['link'];
			else $menulink = '';

			// check if id should be added
			if (isset ($this->menu[$name][$key]['addid']) and $this->menu[$name][$key]['addid'] == "1") {
				$href = $menulink . $id;
			} else {
				$href = $menulink;
			}

			// check imagewidth
			if (isset ($this->menu[$name][$key]['imagewidth']) and $this->menu[$name][$key]['imagewidth'] != "") {
				$imagewidth = " width=\"" . $this->menu[$name][$key]['imagewidth'] . "\"";
			} else {
				$imagewidth = "";
			}

			// check imageheight
			if (isset ($this->menu[$name][$key]['imageheight']) and $this->menu[$name][$key]['imageheight'] != "") {
				$imageheight = " height=\"" . $this->menu[$name][$key]['imageheight'] . "\"";
			} else {
				$imageheight = "";
			}

			// make image link or normal link
			if(isset($this->menu[$name][$key]['nolink']) && $this->menu[$name][$key]['nolink']) {
				$link = $this->menu[$name][$key]['name'];
			} else {
				if (isset ($this->menu[$name][$key]['image']) and $this->menu[$name][$key]['image'] != "") {
					$link = "<a href=\"" . $href . "\"" . $target . $title . $atagparams . "><img src=\"" . $this->menu[$name][$key]['image'] . "\" border=\"0\"" . $imagewidth . $imageheight . $title . " alt=\"". $this->menu[$name][$key]['name'] ."\"/></a>";
				} else {
					$link = "<a href=\"" . $href . "\"" . $target . $title . $atagparams . ">" . $this->menu[$name][$key]['name'] . "</a>";
				}
			}

			// check for toolbar of for image
			if ($name != "toolbar" &&
				  (!isset ($this->menu[$name][$key]['image']) or $this->menu[$name][$key]['image'] == "") &&
				  (!isset ($this->menu[$name][$key]['wrap']) or $this->menu[$name][$key]['wrap'] == "") &&
				  (!isset ($this->menu[$name]['config']['defaultelementwrap']) or $this->menu[$name]['config']['defaultelementwrap'] == "")) {
				$link = "[" . $link . "]";
			}

			// set wrap
			if (isset ($this->menu[$name][$key]['wrap']) and $this->menu[$name][$key]['wrap'] != "") {
				$link = $this->CLASS['knowledgeroot']->setWrap($link,$this->menu[$name][$key]['wrap']);
			} elseif(isset ($this->menu[$name]['config']['defaultelementwrap']) and $this->menu[$name]['config']['defaultelementwrap'] != "") {
				$link = $this->CLASS['knowledgeroot']->setWrap($link,$this->menu[$name]['config']['defaultelementwrap']);
			}

			// check for dynamic vars and replace them
			$link = str_replace('{$ID}', $id, $link);
			if(isset($_SESSION['cid'])) $link = str_replace('{$PAGEID}', $_SESSION['cid'], $link);

			// check for adminrights
			if ((!isset ($_SESSION['admin']) or $_SESSION['admin'] != "1") &&
					isset ($this->menu[$name][$key]['admin']) and $this->menu[$name][$key]['admin'] == "1") {
				// do not display item
				continue;
			}

			// check pagerights
			if ($pagerights != null && isset ($this->menu[$name][$key]['pagerights']) and $pagerights < $this->menu[$name][$key]['pagerights']) {
				// do not display item
				continue;
			}

			// check contenrights
			if ($contentrights != null && isset ($this->menu[$name][$key]['contentrights']) and $contentrights < $this->menu[$name][$key]['contentrights']) {
				// do not display item
				continue;
			}

			// check if login is required
			if (isset ($this->menu[$name][$key]['login']) and $this->menu[$name][$key]['login'] == "1" && (!isset ($_SESSION['userid']) or $_SESSION['userid'] == "0" || $_SESSION['userid'] == "" || !isset ($_SESSION['groupid']) or $_SESSION['groupid'] == "0" || $_SESSION['groupid'] == "")) {
				// do not display item
				continue;
			}

			// check if logout is required
			if (isset ($this->menu[$name][$key]['logout']) and $this->menu[$name][$key]['logout'] == "1" && (!isset ($_SESSION['userid']) or $_SESSION['userid'] != "0" || $_SESSION['userid'] != "" || !isset ($_SESSION['groupid']) or $_SESSION['groupid'] != "0" || $_SESSION['groupid'] != "")) {
				// do not display item
				continue;
			}

			// set priority
			if(isset($this->menu[$name][$key]['priority']) && $this->menu[$name][$key]['priority'] != "") {
				$priority = $this->menu[$name][$key]['priority'];
			} else {
				$priority = "90";
			}

			// build menu array
			$menuarr[] = array( "pri" => $priority, "link" => $link . "\n");
		}

		// sort menu array for priority
		array_multisort($menuarr);

		// count array
		$i = 0;
		$arr_len = count($menuarr);

		// display menu
		foreach($menuarr as $key => $value) {
			// define first and last css element
			if(isset($this->menu[$name]['config']['css_class'])) {
				$css_first_element = $this->menu[$name]['config']['css_class'] . '-first';
				$css_last_element = $this->menu[$name]['config']['css_class'] . '-last';
			} else {
				$css_first_element = $key . '-first';
				$css_last_element = $key. '-last';
			}

			if($i == 0 && $i == ($arr_len - 1)) { // first and last element
				$menuarr[$key]['link'] = str_replace('{$CSS_CLASS}', $css_first_element.' '.$css_last_element, $menuarr[$key]['link']);
			} elseif($i == 0) { // first element
				$menuarr[$key]['link'] = str_replace('{$CSS_CLASS}', $css_first_element, $menuarr[$key]['link']);
			} elseif($i == ($arr_len - 1)) { // last element
				$menuarr[$key]['link'] = str_replace('{$CSS_CLASS}', $css_last_element, $menuarr[$key]['link']);
			} else { // not first and not last element
				$menuarr[$key]['link'] = str_replace('{$CSS_CLASS}', '', $menuarr[$key]['link']);
			}

			$out .= $menuarr[$key]['link'];

			$i++;
		}

		// check for wrap in this menu
		if(($out != '' && isset($this->menu[$name]['config']['wrap']) && $this->menu[$name]['config']['wrap'] != "") || (isset($this->menu[$name]['config']['showAlways']) && $this->menu[$name]['config']['showAlways'] == '1' && isset($this->menu[$name]['config']['wrap']) && $this->menu[$name]['config']['wrap'] !=  "")) {
			if(isset($this->menu[$name]['config']['admin']) && $this->menu[$name]['config']['admin'] == "1") {
				if(isset($_SESSION['admin']) && $_SESSION['admin'] == 1) {
					$out = $this->CLASS['knowledgeroot']->setWrap($out,$this->menu[$name]['config']['wrap']);
				}
			} else {
				$out = $this->CLASS['knowledgeroot']->setWrap($out,$this->menu[$name]['config']['wrap']);
			}
		}

		return $out;
	}

	/**
	 * Create admin menu
	 * @param string $name name of menu
	 * @param integer $parent
	 */
	function show_admin_menu($name, $parent = "") {
		$menuarr = array();

		if(!is_array($this->menu[$name])) {
			$this->menu[$name] = array();
		}

		$count_items = 0;

		foreach($this->menu[$name] as $key => $value) {
			// check for config - config is no real menu
			if($key == "config") {
				continue;
			}

			if($parent != "") {
				if (!isset ($this->menu[$name][$key]['parent']) or $this->menu[$name][$key]['parent'] != $parent) {
					continue;
				}
			}

			if ($parent == "" && isset ($this->menu[$name][$key]['parent']) and $this->menu[$name][$key]['parent'] != "") {
				continue;
			}

			// check for tooltip
			if (isset ($this->menu[$name][$key]['tooltip']) and $this->menu[$name][$key]['tooltip'] != "") {
				$title = " alt=\"" . $this->menu[$name][$key]['tooltip'] . "\" title=\"" . $this->menu[$name][$key]['tooltip'] . "\"";
			} else {
				$title = " alt=\"" . $this->menu[$name][$key]['name'] . "\" title=\"" . $this->menu[$name][$key]['name'] . "\"";
			}

			// check for target
			if (isset ($this->menu[$name][$key]['target']) and $this->menu[$name][$key]['target'] != "") {
				$target = " target=\"" . $this->menu[$name][$key]['target'] . "\"";
			} else {
				$target = "";
			}

			// check for extra a tag parameter
			if (isset ($this->menu[$name][$key]['atagparams']) and $this->menu[$name][$key]['atagparams'] != "") {
				$atagparams = " " . trim($this->menu[$name][$key]['atagparams']);
			} else {
				$atagparams = "";
			}

			// make normal link
			if($parent != "") {
				$link = "<div class=\"submenuitem\"><a href=\"" . $this->menu[$name][$key]['link'] . "\"" . $target . $title . $atagparams . ">" . $this->menu[$name][$key]['name'] . "</a></div>";
			} else {
				$link = "<div class=\"menuitem\"><a href=\"" . $this->menu[$name][$key]['link'] . "\"" . $target . $title . $atagparams . ">" . $this->menu[$name][$key]['name'] . "</a></div>";
			}

			// set priority
			if($this->menu[$name][$key]['priority'] != "") {
				$priority = $this->menu[$name][$key]['priority'];
			} else {
				$priority = "90";
			}

			// build menu array
			$menuarr[] = array( "pri" => $priority, "link" => $link . "\n", "name" => $key);

			// count items
			$count_items++;
		}

		// sort menu array for priority
		array_multisort($menuarr);

		if($count_items > 0 && $parent != "") {
			echo "<div class=\"submenu\">\n";
		}

		// display menu
		foreach($menuarr as $key => $value) {
			echo $menuarr[$key]['link'];
			$this->show_admin_menu($name, $menuarr[$key]['name']);
		}

		if($count_items > 0 && $parent != "") {
			echo "</div>\n";
		}
	}

	/**
	 * show extension content
	 */
	function show_ext_content() {
		if($this->content != "") {
			echo $this->content;
		} else {
			echo $this->default_content;
		}
	}

	/*
	 * now the functions for extension handling follows
	 */

	/**
	 * return an array with all files and folders from a directory
	 * @param string $path
	 * @param string $filepath
	 * @return array
	 */
	function getFileArr($path, $filepath = "") {
		if($filepath != "" && substr($filepath,-1,1) != "/") {
			$filepath .= "/";
		}

		$fileArr = array();

		$handle = opendir($path);
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && $file != ".svn" && $file != "CVS") {
				if(is_dir($path."/".$file)) {
					$fileArr[$file]['type'] = "dir";
					$fileArr[$file]['content'] = $this->getFileArr($path."/".$file,$file);
				} else {
					$filecontent = $this->getFileContent($path."/".$file);
					$fileArr[$file]['type'] = "file";
					$fileArr[$file]['content'] = $filecontent;
					$fileArr[$file]['md5'] = md5($filecontent);
				}
			}
		}
		closedir($handle);

		return $fileArr;
	}

	/**
	 * return content of a file
	 * @param string $file
	 * @return string
	 */
	function getFileContent($file) {
		$content = "";

		if(function_exists('file_get_contents')) {
			$content = file_get_contents($file);
		} else {
			$lines = file($file);
			foreach($lines as $line_num => $line) {
				$content .= $line;
			}
		}

		return $content;
	}

	/**
	 * return a full extension as string
	 * @param string $keyname
	 * @param string $path
	 * @return string
	 */
	function makeExtension($keyname, $path) {
		//print_r(gzcompress(base64_encode(serialize($this->getFileArr("/www/projekte/knowledgeroot/system/extension/livediagram/")))));
		//print_r($this->getFileArr("/tmp/test2/"));

		$ext_arr = array();
		$ext_arr['keyname'] = $keyname;
		$ext_arr['files'] = $this->getFileArr($path);

		$CONF = '';
		if(is_file($path . "info.php")) {
			include($path . "info.php");

			$ext_arr['info'] = $CONF;

			unset($CONF);
		}

		$hash = serialize($ext_arr);
		$md5sum = md5($hash);

		if(function_exists('gzcompress')) {
			$ext = $md5sum . ":gzcompress:" . base64_encode(gzcompress($hash));
		} else {
			$ext = $md5sum . ":text:" . base64_encode($hash);
		}

		return $ext;
	}

	/**
	 * fetch extension from repository
	 * url is url to extension repository
	 * @param string $url
	 */
	function fetchExtension($url) {

	}

	/**
	 * return filearray from a extensionfilecontent
	 * @param string $content
	 * @return array
	 */
	function getExtensionData($content) {
		$parts = explode(":", $content);
		$md5sum = $parts[0];
		$method = $parts[1];

		$fileArr = array();

		if($method == "gzcompress") {
			if(function_exists('gzuncompress')) {
				$data = gzuncompress(base64_decode($parts[2]));
			} else {
				echo "gzuncompress is not available";
			}
		} else {
			$data = base64_decode($parts[2]);
		}

		if(md5($data) == $md5sum) {
			$fileArr = unserialize($data);
		} else {
			echo "wrong md5sum";
		}

		return $fileArr;
	}

	/**
	 * write content to a file
	 * @param string $file
	 * @param string $content
	 * @return bool
	 */
	function putFileContent($file,$content="") {
		//echo "$file\n";
		$f=@fopen($file,"w");
		if (!$f) {
			return false;
		} else {
			fwrite($f,$content);
			fclose($f);
			return true;
		}
	}

	/**
	 * save a extension to a path
	 * @param array $extArr
	 * @param string $path
	 */
	function saveExtension($extArr,$path) {
		$this->createExtFolders($extArr,$path);
		$this->createExtFiles($extArr,$path);
	}

	/**
	 * create files from a extension array to a path
	 * @param array $extArr
	 * @param string $path
	 */
	function createExtFiles($extArr,$path) {
		if(substr($path,-1,1) != "/") {
			$path .= "/";
		}

		foreach($extArr as $key => $value) {
			if($extArr[$key]['type'] == "dir") {
				$this->createExtFiles($extArr[$key]['content'],$path.$key);
			} else {
				$this->putFileContent($path.$key,$extArr[$key]['content']);
			}
		}
	}

	/**
	 * create folders from a extension array to a path
	 * @param array $extArr
	 * @param string $path
	 */
	function createExtFolders($extArr, $path) {
		if(substr($path,-1,1) != "/") {
			$path .= "/";
		}

		foreach($extArr as $key => $value) {
			if($extArr[$key]['type'] == "dir") {
				if(!is_dir($path.$key)) {
					$this->mkdir_r($path.$key);
				}

				$this->createExtFolders($extArr[$key]['content'],$path.$key);
			}
		}
	}

	/**
	 * create folders recursiv
	 * @param string $dirname
	 * @param integer $rights
	 * @return bool
	 */
	function mkdir_r($dirName, $rights=0777){
		$dirs = explode('/', $dirName);
		$dir='';

		foreach ($dirs as $part) {
			$dir.=$part.'/';
			if (!@is_dir($dir) && strlen($dir)>1)
				@mkdir($dir, $rights);
		}

		// check if folder was created
		if(@is_dir($dirName)) {
			return true;
		}

		return false;
	}

	/**
	 * enable a extension
	 * @param string $keyname
	 */
	function enableExtension($keyname) {
		$res = $this->CLASS['db']->query(sprintf("UPDATE extensions SET active=1 WHERE keyname='%s'",$keyname));
	}

	/**
	 * disable a extension
	 * @param string $keyname
	 */
	function disableExtension($keyname) {
		$res = $this->CLASS['db']->query(sprintf("UPDATE extensions SET active=0 WHERE keyname='%s'",$keyname));
	}

	//
	/**
	 * install a extension in database table
	 * not on disk
	 * @param string $keyname
	 * @param integer $admin
	 */
	function installExtension($keyname, $admin = 0) {
		$res = $this->CLASS['db']->query(sprintf("SELECT id FROM extensions WHERE keyname='%s'",$keyname));
		$anz = $this->CLASS['db']->num_rows($res);

		if($anz == 0) {
			$res = $this->CLASS['db']->query(sprintf("INSERT INTO extensions (keyname, active, admin) VALUES ('%s', 0, %d)",$keyname,$admin));
		}
	}
}

?>
