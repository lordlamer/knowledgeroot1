<?php
/**
 * This Class is for language work
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-language.php 1159 2011-07-20 20:47:07Z lordlamer $
 */
class language {
	var $CLASS = array();
	var $default_lang = "en_US.UTF8";
	var $get = array(); // array with languagetokens

	var $sys = array(); // array with syslanguagetokens

	var $lang = ""; // loaded language

	var $locales = array();

	/**
	 * init/start class
	 * @param object &$CLASS
	 * @param string $language
	 * @return bool
	 */
	function start(&$CLASS,$language) {
		$this->CLASS =& $CLASS;

		// search for locales
		$this->searchLocales();

		// set userlanguage if available
		if (isset ($_SESSION['language']) and $_SESSION['language'] != "") {
			$language = $_SESSION['language'];
		} else if (!isset ($_SESSION['language']) or $_SESSION['language'] == '') {
			$_SESSION['language'] = $language;
		}

		$this->lang = $language;

		return true;
	}

	/**
	 * search for locales in language folder
	 */
	function searchLocales() {
		if ($handle = opendir($this->CLASS['config']->base->base_path . 'system/language')) {
			while (false !== ($file = readdir($handle))) {
				if(is_file($this->CLASS['config']->base->base_path . 'system/language/' . $file . '/LC_MESSAGES/knowledgeroot.mo')) {
					$this->locales[] = $file;
				}
			}

			closedir($handle);
		}
	}

	/**
	 * load extension language file
	 * @param string $extension
	 * @param string $lang
	 */
	function load_ext_lang($extension,$lang) {
		if(isset($lang[$this->lang])) {
			$this->get['ext'][$extension] = $lang[$this->lang];
			if(isset($this->CLASS['extension'][$extension]['class']->getLang) && is_array($this->CLASS['extension'][$extension]['class']->getLang))
				$this->CLASS['extension'][$extension]['class']->getLang = $lang[$this->lang];
		} elseif(isset($lang[$this->default_lang])) {
			$this->get['ext'][$extension] = $lang[$this->default_lang];
			if(isset($this->CLASS['extension'][$extension]['class']->getLang) && is_array($this->CLASS['extension'][$extension]['class']->getLang))
				$this->CLASS['extension'][$extension]['class']->getLang = $lang[$this->default_lang];
		}
	}

	/**
	 * dropdown with available languages
	 * @param string $name
	 * @param string $default
	 * @param string $optionparams
	 * @param bool $submitOnChange
	 * @return string
	 */
	function lang_dropdown($name = "language",$default = "", $optionparams = "", $submitOnChange = true) {
		if($default == "") {
			$default = $this->CLASS['config']->base->locale;
		}

		if($this->CLASS['config']->tree->ajax && $submitOnChange) {
			$jsscript = "onchange=\"\$('#change_language').submit();\"";
		} else {
			$jsscript = "";
		}

		$out = '<select class="form-control form-control-sm" name="'.$name.'" '.$jsscript.'>'."\n";
		$selected = '';

		asort ($this->locales);
		foreach ($this->locales as $key => $value) {
			if ($value == $default || $value == $default.".UTF8") {
				$selected = ' selected="selected"';
			}

			$out .= "\t<option ".$optionparams." value=\"".$value."\" $selected>".$value."</option>\n";
			$selected = "";
		}

		$out .= "</select>\n";
		return $out;
	}
}

?>
