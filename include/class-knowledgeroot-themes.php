<?php
/**
 * This Class inerhits functions for themes in knowledgeroot
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-knowledgeroot-themes.php 1159 2011-07-20 20:47:07Z lordlamer $
 */
class knowledgeroot_themes {
	var $CLASS;
	var $theme = array();
	var $default_theme = "green";
	var $use_theme = "";
	var $theme_folder = "system/themes/";

	/**
	 * init/start class
	 */
	function start(&$CLASS) {
		$this->CLASS =& $CLASS;

		if(defined("KR_INCLUDE_PREFIX") && KR_INCLUDE_PREFIX != "") {
			$this->theme_folder = KR_INCLUDE_PREFIX . $this->theme_folder;
		}

		$this->search_themes();

		if($this->CLASS['config']->base->theme != '') {
			$this->use_theme = $this->CLASS['config']->base->theme;
		}
	}

	/**
	 *
	 */
	function search_themes() {
		if(is_dir($this->theme_folder)) {
			if($handle = opendir($this->theme_folder)) {
				while(false !== ($file = readdir($handle))) {
					if($file != "." && $file != "..") {
						$infofile = $this->theme_folder.$file."/info.php";
						if(is_file($infofile)) {
							$theme = array();
							include($infofile);
							$cssfile = $this->theme_folder.$file."/".$theme['css_file'];
							if(is_file($cssfile)) {
								$this->themes[$theme['name']] = $theme;
							}
						}
					}
				}
			}
		}
	}

	/**
	 * load a theme to internal theme array
	 */
	function load_ext_theme($themeArr) {
		if($themeArr['name'] != "") {
			$this->themes[$themeArr['name']] = $themeArr;
		}
	}

	/**
	 * return path to css_file
	 */
	function load_theme() {
		if (isset ($_SESSION['theme']) and $_SESSION['theme'] != "") {
			$this->use_theme = $_SESSION['theme'];
		}

		if(isset($this->themes[$this->default_theme]['path']) && is_file($this->themes[$this->default_theme]['path']."/".$this->themes[$this->default_theme]['css_file'])) {
			$default_css_file = $this->themes[$this->default_theme]['path']."/".$this->themes[$this->default_theme]['css_file'];
		} else {
			$default_css_file = $this->theme_folder . $this->default_theme . "/" . $this->themes[$this->default_theme]['css_file'];
		}

		if(isset($this->themes[$this->use_theme]['path']) && is_file($this->themes[$this->use_theme]['path']."/".$this->themes[$this->use_theme]['css_file'])) {
			$use_css_file = $this->themes[$this->use_theme]['path']."/".$this->themes[$this->use_theme]['css_file'];
		} else {
			$use_css_file = $this->theme_folder . $this->use_theme . "/" . $this->themes[$this->use_theme]['css_file'];
		}

		if(is_file($use_css_file)) {
			$css = $use_css_file;
		} else {
			$css = $default_css_file;
		}

		return $css;
	}

	/**
	 * return dropdown with themes
	 */
	function theme_dropdown($default = "", $name = "theme") {
		$out = "";
		$out .= "<select dojoType=\"dijit.form.Select\" name=\"".$name."\">\n";

		foreach($this->themes as $key => $value) {
			$selected = $default == $value['name'] ? "selected=\"selected\"" : "";
			$out .= "\t<option value=\"".$value['name']."\" ".$selected.">".$value['name']."</option>\n";
		}

		$out .= "</select>\n";

		return $out;
	}

}
?>
