<?php
/******************************
 * Knowledgeroot
 * Frank Habermann
 * 15.04.2007
 *
 * Version 0.1
 * This Class will help to secure knowledgeroot and to disable XSS
 ******************************/

class libsecure extends extension_base {
	var $htmlpurifier = null;
	var $safehtml = null;

	/**
	 * main Function to start Extension
	 */
	function main() {
		$this->CLASS['hooks']->execAtHook("kr_header","check_vars","start","libsecure","doCheck");
	}

	/**
	 * This function starts black and white listing in the right order
	 */
	function doCheck() {
		$order = explode(",", $this->CONF['order']);
		foreach($order as $key => $value) {
			$value = trim($value);
			if($value == "whitelist" && $this->CONF['whitelist'] == 1) $this->doWhitelisting();
			if($value == "blacklist" && $this->CONF['blacklist'] == 1) $this->doBlacklisting();
		}

		// check if fileuploads should be checked
		if($this->CONF['fileuploads'] == 1) $this->doFileUploadFilter();
	}

	/**
	 * This function start the whitelisting
	 */
	function doWhitelisting() {
		// load htmlpurifier
		require_once('htmlpurifier/HTMLPurifier.auto.php');
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Core.Encoding', $this->CLASS['config']->base->charset); //replace with your encoding
		$config->set('HTML.XHTML', $this->CONF['whitelistconfig']['useXhtml']); //replace with false if HTML 4.01
		$this->htmlpurifier = new HTMLPurifier($config);

                // do blacklisting on global vars if enabled in the config
                if($this->CONF['whitelistitems']['POST'] == 1) $this->whitelistVar($_POST,true);
                if($this->CONF['whitelistitems']['GET'] == 1) $this->whitelistVar($_GET,true);
                if($this->CONF['whitelistitems']['COOKIE'] == 1) $this->whitelistVar($_COOKIE,true);
                if($this->CONF['whitelistitems']['SESSION'] == 1) $this->whitelistVar($_SESSION);
                if($this->CONF['whitelistitems']['SERVER'] == 1) $this->whitelistVar($_SERVER,true);
		if($this->CONF['whitelistitems']['REQUEST'] == 1) $this->whitelistVar($_REQUEST,true);
	}

	/**
	 * This function is doing whitelisting on variables
	 */
	function whitelistVar(&$var, $convertslashes = false) {
		if(is_array($var)) {
			reset($var);
			while(list($key,$val)=each($var)) {
				if(is_array($val)) {
					$this->whitelistVar($var[$key],$convertslashes);
				} else {
					if($this->stringShouldBeChecked($var[$key])) {
						if($convertslashes) $var[$key] = addslashes($this->htmlpurifier->purify(stripslashes($val)));
						else $var[$key] = $this->htmlpurifier->purify($val);
					}
				}
			}
			reset($var);
		} else {
			if($this->stringShouldBeChecked($var[$key])) {
				if($convertslashes) $var = addslashes($this->htmlpurifier->purify(stripslashes($var)));
				else $var = $this->htmlpurifier->purify($var);
			}
		}
	}

	/**
	 * This function start the blacklisting
	 */
	function doBlacklisting() {
		// load safehtml
		if(!defined('XML_HTMLSAX3')) define('XML_HTMLSAX3', dirname(__FILE__)."/safehtml/classes/");
		require_once('safehtml/classes/safehtml.php');

		// init safehtml
		$this->safehtml = new safehtml();

		// do blacklisting on global vars if enabled in the config
		if($this->CONF['blacklistitems']['POST'] == 1) $this->blacklistVar($_POST,true);
		if($this->CONF['blacklistitems']['GET'] == 1) $this->blacklistVar($_GET,true);
		if($this->CONF['blacklistitems']['COOKIE'] == 1) $this->blacklistVar($_COOKIE,true);
		if($this->CONF['blacklistitems']['SESSION'] == 1) $this->blacklistVar($_SESSION);
		if($this->CONF['blacklistitems']['SERVER'] == 1) $this->blacklistVar($_SERVER,true);
		if($this->CONF['blacklistitems']['REQUEST'] == 1) $this->blacklistVar($_REQUEST,true);
	}

	/**
	 * This function is doing blacklisting on variables
	 */
	function blacklistVar(&$var,$convertslashes = false) {
		if(is_array($var)) {
			reset($var);
			while(list($key,$val)=each($var)) {
				if(is_array($val)) {
					$this->blacklistVar($var[$key],$convertslashes);
				} else {
					if($this->stringShouldBeChecked($var[$key])) {
						if($convertslashes) $var[$key] = addslashes($this->safehtml->parse(stripslashes($val)));
						else $var[$key] = $this->safehtml->parse($val);
						$this->safehtml->clear();
					}
				}
			}
			reset($var);
		} else {
			if($this->stringShouldBeChecked($var[$key])) {
				if($convertslashes) $var = addslashes($this->safehtml->parse(stripslashes($var)));
				else $var = $this->safehtml->parse($var);
				$this->safehtml->clear();
			}
		}
	}

	/**
	 * Do check on Fileuploads
	 */
	function doFileUploadFilter() {
		if(isset($_FILES) && is_array($_FILES)) {
			foreach($_FILES as $key => $value) {
				if(is_array($_FILES[$key]['name'])) {
					foreach($_FILES[$key]['name'] as $keymulti => $valuemulti) {
						$_FILES[$key]['name'][$keymulti] = $this->doFileUploadWhiteList($_FILES[$key]['name'][$keymulti]);
						if(isset($_FILES[$key]['type'][$keymulti])) $_FILES[$key]['type'][$keymulti] = $this->doFileUploadWhiteList($_FILES[$key]['type'][$keymulti]);
					}
				} else {
					$_FILES[$key]['name'] = $this->doFileUploadWhiteList($_FILES[$key]['name']);
					if(isset($_FILES[$key]['type'])) $_FILES[$key]['type'] = $this->doFileUploadWhiteList($_FILES[$key]['type']);
				}
			}
		}
	}

	/**
	 * Do a whitelist check on a filename or type
	 */
	function doFileUploadWhiteList($name) {
		if(function_exists('mb_ereg_replace')) $name = mb_ereg_replace('/[^a-zA-Z0-9 .\-_\/]/m', '', $name);
		else $name = preg_replace('/[^a-zA-Z0-9 .\-_\/]/m', '', $name);
		return $name;
	}

	/**
	 * check if string should be checked with white or blacklist
	 * @param $string string
	 * @return bool
	 */
	function stringShouldBeChecked($string) {
		if(preg_match('/^[a-zA-Z0-9 ]+$/', $string)) return false;
		else return true;
	}
}

?>
