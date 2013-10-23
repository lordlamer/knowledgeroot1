<?php
/**
 * authentication class
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-knowledgeroot-auth.php 941 2010-06-07 20:57:58Z lordlamer $
 */
class knowledgeroot_auth {
	/**
	 * TODO: garbage collector fuer Tabelle users_login
	 * Alte Benutzereintraege koennen nach X Stunden/Tagen/Monaten automatisch geloescht werden.
	 * DELETE FROM users_login WHERE Differenz zwischen time()-lasttrydate > X
	 */

	var $CLASS;

	/**
	 * Benutzername
	 * @access protected
	 */
	var $user = '';

	/**
	 * Benutzer-ID
	 * @access protected
	 */
	var $userid = 0;

	/**
	 * lasttrydate
	 * mittels PHP-Funktion time() gespeicherter Wert
	 * @access protected
	 */
	var $lasttrydate = 0;

	/**
	 * Wartezeit
	 * Wird bei fehlerhaften Login gesetzt, wenn die Zeit zwischen erstem
	 * Versuch und zweitem Versuch < lasttrydate + $CONFIG['login']['delay'] ist.
	 * @access protected
	 */
	var $wartezeit = false;

	/**
	 * Account Sperre
	 * Ist $this->login_trial >= $this->CLASS['config']->login->max wird kein Login
	 * mehr ermoeglicht.
	 * @access protected
	 */
	var $loginblock = false;

	/**
	 * Loginversucheszaehler
	 * @access protected
	 */
	var $login_trial = 0;

	/**
	 * garbage collector time
	 * entrys in table 'users_login' will be delete after
	 * this time:
	 * time() - ($this->gc_time + $this->CLASS['config']->login->delay)
	 *
	 * @access private
	 * @param  int  time in seconds
	 */
	var $gc_time = 6000;

	/**
	 * init/start class
	 */
	function start(&$CLASS) {
		$this->CLASS =& $CLASS;
	}

	/**
	 * check login
	 *
	 * @public
	 * @return boolean
	 */
	function login() {
		$this->user = $_POST['user'];

		// war der Benutzer fr�her schon mal angemeldet,
		// pruefe lasttrydate
		if ($this->checkUsersLogin()) {
			if ($this->lasttrydate + $this->CLASS['config']->login->delay > time() and $this->login_trial > 0) {
				$this->wartezeit = true;

				return false;
			}

			if ($this->login_trial >= $this->CLASS['config']->login->max) {
				$this->loginblock = true;

				return false;
			}
		}

		$user = addslashes($this->user);
		$pass = md5(addslashes($_POST['password']));

		$query = sprintf("SELECT id, defaultgroup, admin, rightedit, treecache, theme, language FROM users WHERE name='%s' AND password='%s' AND enabled=1", $user, $pass);

		$res = $this->CLASS['db']->query($query);
		$anz = $this->CLASS['db']->num_rows($res);

		if($anz == 1) {
			$row = $this->CLASS['db']->fetch_assoc($res);

			$_SESSION['userid'] = $row['id'];
			$_SESSION['groupid'] = $row['defaultgroup'];
			$_SESSION['user'] = $user;
			$_SESSION['password'] = $pass;
			$_SESSION['md5hash'] = md5($user . $pass);
			$_SESSION['admin'] = $row['admin'];
			$_SESSION['rightedit'] = $row['rightedit'];
			$_SESSION['open'] = array();
			$_SESSION['open'] = ($row['treecache'] == "") ? array() : unserialize($row['treecache']);
			$_SESSION['theme'] = $row['theme'];
			$_SESSION['language'] = $row['language'];

			if (isset($_POST['language'])) {
				$this->CLASS['language']->load_language($_POST['language']);
			}

			$this->CLASS['auth']->writeTrueLogin();
			$this->CLASS['auth']->gc();

			return true;
		} else {
			return false;
		}
	}

	/**
	 * vermerkt einen fehlgeschlagenen Anmeldeversuch in der DB
	 *
	 * @access public
	 */
	function writeFalseLogin() {
		// gibt es berreits einen Eintrag in der Tabelle users_login,
		// dann mache ein Update, sonst ein Insert
		if ($this->checkUsersLogin()) {
			// fehlversuch speichern | login_trial++
			$query = sprintf("UPDATE users_login SET login_trial=login_trial+1, lasttrydate=%u WHERE usersid=%u", time(), $this->userid);
			$res_check = $this->CLASS['db']->query($query);
		} else if ($this->userid != 0) {
			// fehlversuch speichern | login_trial++
			$query = sprintf("INSERT INTO users_login (usersid, login_trial, lasttrydate) VALUES (%u, 1, %u)", $this->userid, time());
			$res_check = $this->CLASS['db']->query($query);
		}
	}

	/**
	 * setzt bei gelungener Anmeldung die Werte auf Null
	 *
	 * @access public
	 */
	function writeTrueLogin() {
		// gibt es bereits einen Eintrag in der Tabelle users_login,
		// dann mache ein Update, sonst ein Insert
		if ($this->checkUsersLogin()) {
			// fehlversuch speichern | login_trial++
			$query = sprintf("UPDATE users_login SET login_trial=0, lasttrydate=%u WHERE usersid=%u", time(), $this->userid);
			$res_check = $this->CLASS['db']->query($query);
		}
	}

	/**
	 * prueft vorhandensein eines Eintrages in der Tabelle users_login
	 *
	 * @access private
	 * @return bool
	 */
	function checkUsersLogin() {
		$query = sprintf("SELECT users_login.usersid, users_login.lasttrydate, users_login.login_trial FROM users_login JOIN users ON (users.id = users_login.usersid) WHERE users.name='%s' AND users.enabled=1", addslashes($this->user));

		$res_check = $this->CLASS['db']->query($query);
		$anz = $this->CLASS['db']->num_rows($res_check);

		if ($anz == 1) {
			$row_check = $this->CLASS['db']->fetch_assoc($res_check);

			$this->userid = $row_check['usersid'];
			$this->lasttrydate = $row_check['lasttrydate'];
			$this->login_trial = $row_check['login_trial'];

			return true;
		} else {
			$this->userId();

			return false;
		}
	}

	/**
	 * Liest die UserID aus der Tabelle users aus.
	 *
	 * @access public
	 * @return bool
	 */
	function userId() {
		$query = sprintf("SELECT id FROM users WHERE name='%s' AND enabled=1", addslashes($this->user));

		$res = $this->CLASS['db']->query($query);
		$anz = $this->CLASS['db']->num_rows($res);

		if($anz == 1) {
			$row = $this->CLASS['db']->fetch_assoc($res);
			$this->userid = $row['id'];

			return true;
		} else {
			return false;
		}
	}

	/**
	 * garbage collector
	 *
	 */
	function gc() {
		if(isset($this->CLASS['config']->login->delay)) {
			$this->gc_time = time() - ($this->gc_time + $this->CLASS['config']->login->delay);
			$query = sprintf("DELETE FROM users_login WHERE lasttrydate<%d", $this->gc_time);

			$this->CLASS['db']->query($query);
		}
	}
}
?>
