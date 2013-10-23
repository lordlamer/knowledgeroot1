<?php
/**
 * This class is for session handling
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id
 */
class session {
	/**
	 * array $CLASS global array to access all classes
	 */
	var $CLASS = array();

	/**
	 * bool $handle enable to handle lifetime and checks of session
	 */
	var $handle = false;

	/**
	 * int $lifetime session lifetime in minutes
	 */
	var $lifetime = 60;

	/**
	 * string $name session name
	 */
	var $name = "";

	/**
	 * string $id session id
	 */
	var $id = "";

	/**
	 * bool $checkBrowser check session for same browser?
	 */
	var $checkBrowser = false;

	/**
	 * bool $checkIP check session for same ip?
	 */
	var $checkIP = false;

	/**
	 * bool $onlyCookie only allow cookies?
	 */
	var $onlyCookies = false;

	/**
	 * init/start class
	 * @param array $CLASS reference to class array
	 */
	function start(&$CLASS) {
		$this->CLASS =& $CLASS;

		// check if session lifetime and session checking should be enabled
		if($this->CLASS['config']->session->handle) {
			// check for lifetime
			if(is_int($this->CLASS['config']->session->lifetime)) {
				$this->lifetime = $this->CLASS['config']->session->lifetime;
			}

			// check for checkBrowser
                        if($this->CLASS['config']->session->check_browser) {
                                $this->checkBrowser = true;
                        }

			// check for checkIP
                        if($this->CLASS['config']->session->check_ip) {
                                $this->checkIP = true;
                        }

			// check for only allow cookies
                        if($this->CLASS['config']->session->only_cookies) {
                                $this->onlyCookies = true;
                        }
		}

		// set lifetime of session
		$this->setLifeTime();
	}

	/**
	 * start session
	 * @param string $name name of session
	 * @return bool
	 */
	function startSession($name) {
		// check for only allow cookies
		if($this->onlyCookies) {
			ini_set('session.use_trans_sid',0);
		}

		// set session name
		$this->name = session_name($name);

		// set session id
		$this->id = session_id();

		return session_start();
	}

	/**
	 * check session for user
	 * @return bool will return true if session is ok and false if not
	 */
	function checkSession() {
		// for default session is not ok
		$out = false;

		// check if lastAccess is set
		if(!isset($_SESSION['session']['lastAccess'])) {
			$_SESSION['session']['lastAccess'] = time();
		}

		// check if browser is set
		if(!isset($_SESSION['session']['browser']) && isset($_SERVER["HTTP_USER_AGENT"])) {
			$_SESSION['session']['browser'] = md5($_SERVER["HTTP_USER_AGENT"]);
		}

		// check if ip is set
                if(!isset($_SESSION['session']['ip']) && isset($_SERVER["REMOTE_ADDR"])) {
                        $_SESSION['session']['ip'] = $_SERVER["REMOTE_ADDR"];
                }

		// now check session
		if($this->checkLifeTime() && $this->checkBrowser() && $this->checkIP()) {
			$out = true;
		}

		// at the end set lastAccess time new
		$_SESSION['session']['lastAccess'] = time();

		return $out;
	}

	/**
	 * check lifetime of session
	 * @return bool
	 */
	function checkLifeTime() {
                $out = false;

		if($this->handle) {
			if(isset($_SESSION['session']['lastAccess']) && $_SESSION['session']['lastAccess'] >= (time() - $this->lifetime*60)) {
                        	$out = true;
			}
                } else {
			// no session lifetime check so session is ok
			$out = true;
		}

		return $out;
	}

	/**
	 * check for browser in session
	 * @return bool
	 */
	function checkBrowser() {
		$out = false;

                // now check for browser
                if($this->checkBrowser) {
			if(isset($_SESSION['session']['browser']) && isset($_SERVER["HTTP_USER_AGENT"]) && $_SESSION['session']['browser'] = md5($_SERVER["HTTP_USER_AGENT"])) {
	                        $out = true;
			}
                } else {
			// no session browser check is needed so session is ok
			$out = true;
		}

		return $out;
	}

	/**
	 * check for ip in session
	 * @return bool
	 */
	function checkIP() {
                $out = false;

                // now check for browser
                if($this->checkIP) {
			if(isset($_SESSION['session']['ip']) && isset($_SERVER["REMOTE_ADDR"]) && $_SESSION['session']['ip'] = $_SERVER["REMOTE_ADDR"]) {
                        	$out = true;
			}
                } else {
			// no session ip check is need so session is ok
			$out = true;
		}

                return $out;
	}

	/**
	 * set session lifetime
	 * @param integer $time time for session timeout in seconds
	 * @return integer return lifetime of session
	 */
	function setLifeTime($time = null) {
		if($time != null && is_int($time)) {
			$this->lifetime = $time;
		}

		ini_set("session.gc_maxlifetime", $this->lifetime);
		return ini_get("session.gc_maxlifetime");
	}
}

?>
