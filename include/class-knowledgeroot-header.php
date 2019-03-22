<?php
/**
 * This Class inerhits functions that do header/background work
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-knowledgeroot-header.php 1204 2012-01-02 19:37:41Z lordlamer $
 */
class knowledgeroot_header {
	/**
	 * @param array $CLASS reference to global $CLASS var
	 */
	var $CLASS;

	/**
	 * @param string $messages string with messages that will be displayed on top of page
	 */
	var $messages = "";

	/**
	 * @param string $htmlheader string with tags that will be included in html header
	 */
	var $htmlheader = "";

	/**
	 * @param string $messagetype
	 */
	var $messagetype = "success"; // default messagetype

	/**
	 * init/start class
	 * @param array &$CLASS reference to global $CLASS var
	 */
	function start(&$CLASS) {
		$this->CLASS =& $CLASS;
	}

	/**
	 * will check all required vars and do right actions
	 */
	function check_vars() {
		$this->CLASS['hooks']->setHook("kr_header","check_vars","start");

		//check if userid and groupid is set, if not set to 0
		if ((!isset ($_SESSION['userid']) or $_SESSION['userid'] == '') || (!isset ($_SESSION['groupid']) or $_SESSION['groupid'] == '')) {
			$_SESSION['userid'] = 0;
			$_SESSION['groupid'] = 0;
		}

		// check for first run in this session and do expand all or not
		if ((!isset ($_SESSION['firstrun']) or $_SESSION['firstrun'] == 0) && $this->CLASS['config']->tree->expandall) {
			$this->CLASS['vars']['menu']['doexpand'] = 1;
		} else {
			$this->CLASS['vars']['menu']['doexpand'] = 0;
		}

		// load default tree layout
		$this->loadDefaultTreeLayout();

		// check if content should be shown directly
		$this->getIDFromContent();

		// get id from alias
		$this->getIDFromAlias();

		// if GET[id] not set use POST[id]
		if (isset ($_GET['id']) and $_GET['id'] != '') {
			$id = $_GET['id'];
		} else if (isset ($_POST['id']) and $_POST['id'] != '') {
			$id = $_POST['id'];
		} else {
			$id = '';
		}

		// set siteid to session
		if($id != "") {
			$_SESSION['cid'] = $id;

			$hashkey = md5('pageavailable_'.$_SESSION['cid']);
			if(!($data = $this->CLASS['cache']->load($hashkey))) {
				// check if page is available
				$res = $this->CLASS['db']->query(sprintf("SELECT count(id) as anz FROM tree WHERE id=%d",$_SESSION['cid']));
				$data = $this->CLASS['db']->fetch_assoc($res);
				$this->CLASS['cache']->save($data, $hashkey, array('system', 'tree'));
			}

			// if page is not available set to defaultpage
			if($data['anz'] == 0) {
				$id = $this->CLASS['config']->misc->defaultpage;
				$_SESSION['cid'] = $this->CLASS['config']->misc->defaultpage;
			}
		} else {
			if (!isset ($_SESSION['cid']) or $_SESSION['cid'] == '') {
				$id = $this->CLASS['config']->misc->defaultpage;
				$_SESSION['cid'] = $this->CLASS['config']->misc->defaultpage;
			}
		}

		// open current menu item
		if(isset($_SESSION['cid']) && $_SESSION['cid'] != "") {
			$_SESSION['open'][$_SESSION['cid']] = 1;
		}

		// do logout
		if (isset ($_GET['action']) and $_GET['action'] == "logout") {
			$this->logout();
		}

		// check usersession
		$this->check_loged_in_userrights();

		// do login
		if (isset ($_POST['login']) and $_POST['login'] != "") {
			$this->login();
		}

		// save default tree layout
		if(isset($_GET['action']) && $_GET['action'] == "savedefaulttree") {
			$this->saveDefaultTreeLayout();
		}

		// edit content?
		if (isset ($_POST['submit']) && isset ($_POST['editid'])) {
			$this->edit_content();
		}

		// create new content?
		if (isset ($_POST['submit']) && isset ($_POST['neditid'])) {
			$this->new_content();
		}

		// create new page?
		if (isset($_POST['submit']) && isset ($_POST['newpage'])) {
			$this->new_page();
		}

		// file upload?
		if (isset ($_POST['submit']) && isset ($_POST['upload'])) {
			$this->upload_file();
		}

		// delete content?
		if (isset ($_GET['delid']) and $_GET['delid'] != "") {
			$this->delete_content();
		}

		// delete page?
		if (isset ($_GET['delpage']) and $_GET['delpage'] != "") {
			$this->delete_page();
		}

		// edit page?
		if (isset ($_POST['editpage']) and $_POST['editpage'] != "") {
			$this->edit_page();
		}

		// delete file?
		if (isset ($_GET['delfile']) and $_GET['delfile'] != "") {
			$this->delete_file();
		}

		// create root?
		if (isset ($_POST['action']) and $_POST['action'] == "createroot") {
			$this->create_root();
		}

		// add user?
		if (isset ($_POST['submit']) && isset ($_POST['action']) and $_POST['action'] == "adduser" && isset ($_POST['name']) and $_POST['name'] != "") {
			$this->add_user();
		}

		// edit user?
		if (isset ($_POST['submit']) && isset ($_POST['action']) and $_POST['action'] == "edituser" && isset ($_POST['name']) and $_POST['name'] != "") {
			$this->edit_user();
		}

		// add group?
		if (isset ($_POST['submit']) && isset ($_POST['action']) and $_POST['action'] == "addgroup" && isset ($_POST['name']) and $_POST['name'] != "") {
			$this->add_group();
		}

		// edit group?
		if (isset ($_POST['submit']) && isset ($_POST['action']) and $_POST['action'] == "editgroup" && isset ($_POST['name']) and $_POST['name'] != "") {
			$this->edit_group();
		}

		// delete user?
		if (isset ($_GET['action']) and $_GET['action'] == "deluser" && isset ($_GET['uid']) and $_GET['uid'] != "") {
			$this->delete_user();
		}

		// delete group?
		if (isset ($_GET['action']) and $_GET['action'] == "delgroup" && isset ($_GET['gid']) and $_GET['gid'] != "") {
			$this->delete_group();
		}

		// edit options?
		if (isset ($_POST['action']) and $_POST['action'] == "options") {
			$this->edit_options();
		}

		// hide menu
		if (isset ($_GET['action']) and $_GET['action'] == "togglemenu") {
			$this->hideShowMenu();
		}

		// change language
		if (isset ($_POST['action']) and $_POST['action'] == "change_language") {
			$this->change_language();
		}

		// move page?
		if (isset ($_POST['move']) and $_POST['move'] == "move" && isset ($_POST['to']) and $_POST['to'] != $_SESSION['cid']) {
			$this->move_page();
		}

		// move content?
		if (isset ($_POST['move']) and $_POST['move'] == "cmove" && isset ($_POST['to']) and $_POST['to'] != $_SESSION['cid'] && isset ($_POST['contentid']) and $_POST['contentid'] != "") {
			$this->move_content();
		}

		// will move content up on the page
		if (isset ($_GET['moveup']) and $_GET['moveup'] != "") {
			$this->move_content_up();
		}

		// will move content down on the page
		if (isset ($_GET['movedown']) and $_GET['movedown'] != "") {
			$this->move_content_down();
		}

		// will move content on the same page after/before another content
		if(isset($_POST['movecontent']) && $_POST['movecontent'] == 'movecontent' && isset($_POST['page']) && $_POST['page'] != '' && isset($_POST['contentid']) && $_POST['contentid'] != '' && isset($_POST['targetcontentid']) && $_POST['targetcontentid'] != '' && isset($_POST['position']) && $_POST['position'] != '') {
			$this->move_content_position();
		}

		// open tree element?
		if (isset ($_GET['openid']) and $_GET['openid'] != "") {
			$this->open_tree_element();
		}

		// create search
		if (isset($_POST['submit']) && isset ($_POST['search']) and $_POST['search'] != "") {
			$this->create_search();
		}

		// add title to htmlheader
		$this->addtitle();

		// set charset
		if($this->CLASS['config']->base->charset != "") {
			if($this->CLASS['config']->base->charset == "utf8") {
				$charset = "utf-8";
			} else {
				$charset = $this->CLASS['config']->base->charset;
			}

			$this->addheader("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=" . $charset . "\" />");
		}

		$this->CLASS['hooks']->setHook("kr_header","check_vars","end");

		return 0;
	}

	/**
	 * hide or show menu
	 */
	function hideShowMenu() {
		if(isset($_SESSION['_hide_menu_']) && $_SESSION['_hide_menu_'] != false) {
			$_SESSION['_hide_menu_'] = false;
		} else {
			$_SESSION['_hide_menu_'] = true;
		}
	}

	/**
	 *
	 */
	function saveDefaultTreeLayout() {
		if(isset($_SESSION['admin']) && $_SESSION['admin'] == 1 && isset($this->CLASS['config']->tree->defaultlayout) && $this->CLASS['config']->tree->defaultlayout == 1) {
			$layout = serialize($_SESSION['open']);
			$this->CLASS['db']->queryf("UPDATE settings SET value='%s' WHERE name='%s'", $layout, "menu.defaultlayoutarray");
		}
	}

	/**
	 * load default tree layout to session for new users
	 */
	function loadDefaultTreeLayout() {
		if(isset($this->CLASS['config']->tree->defaultlayout) && $this->CLASS['config']->tree->defaultlayout == 1 && isset($this->CLASS['config']->tree->defaultlayoutarray) && $this->CLASS['config']->tree->defaultlayoutarray != "" && (!isset($_SESSION['open']) || (isset($_SESSION['open']) && !is_array($_SESSION['open'])))) {
			$layout = @unserialize($this->CLASS['config']->tree->defaultlayoutarray);

			if(is_array($layout)) {
				$_SESSION['open'] = $layout;
				$this->CLASS['vars']['menu']['doexpand'] = 0;
				$this->CLASS['config']->tree->expandall = 0;
			}
		}
	}

	/**
	 * make login
	 */
	function login() {
		$this->CLASS['hooks']->setHook("kr_header","login","start");

		if ($this->CLASS['auth']->login()) {
			// clean treecache
			unset($this->CLASS['tree']);
			$this->CLASS['tree'] = new categoryTree();
			$this->CLASS['tree']->start($this->CLASS);

			$this->CLASS['hooks']->setHook("kr_header","login","success");

			// do a reload for a new clean initialisation after logout
			header("Location: index.php?".session_name(). "=" .session_id());
			exit();
		} else {
			if ($this->CLASS['auth']->wartezeit) {
				$this->addwarning(str_replace('#RELOGINDELAY#', $this->CLASS['config']->login->delay, $this->CLASS['translate']->_('You can login after #RELOGINDELAY# seconds!')));
			} else if ($this->CLASS['auth']->loginblock) {
				$this->addwarning($this->CLASS['translate']->_('This account is disabled!'));
			} else {
				// vermerke fehlgeschlagenen Loginversuch
				$this->CLASS['auth']->writeFalseLogin();
				$this->addwarning($this->CLASS['translate']->_('You have entered the wrong user or password!'));
			}

			$_SESSION['user'] = "guest";
			$_SESSION['password'] = "guest";

			$this->CLASS['hooks']->setHook("kr_header","login","failed");
		}

		return 0;
	}

	/**
	 * destroy session and make clean logout
	 */
	function logout() {
		$this->CLASS['hooks']->setHook("kr_header","logout","start");

		if(isset($_SESSION['open'])) $treecache = $_SESSION['open'];
		$_SESSION = array();
		$_SESSION['user'] = "guest";
		$_SESSION['password'] = "guest";
		$_SESSION['md5hash'] = "";
		$_SESSION['cid'] = "";
		$_SESSION['userid'] = "";
		$_SESSION['groupid'] = "";
		$_SESSION['admin'] = "0";
		//$_SESSION['theme'] = "";

		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time()-42000, '/');
		}

		session_destroy();

		// will not work perhaps session is destroyed
		if(isset($_SESSION['open'])) $_SESSION['open'] = $treecache;

		$this->CLASS['hooks']->setHook("kr_header","logout","end");

		// do a reload for a new clean initialisation after logout
		header("Location: index.php");
		exit();

		return 0;
	}

	/**
	 * check usersession
	 * if session wrong give guest rights
	 */
	function check_loged_in_userrights() {
		if(!isset($_SESSION['user'])) $_SESSION['user'] = "";
		if(!isset($_SESSION['password'])) $_SESSION['password'] = "";
		if(!isset($_SESSION['md5hash'])) $_SESSION['md5hash'] = "";

		if ($_SESSION['user'] != "guest" && $_SESSION['password'] != "guest" && $_SESSION['md5hash'] != md5($_SESSION['user'] . $_SESSION['password'])) {
			$_SESSION['user'] = "guest";
			$_SESSION['password'] = "guest";
			$_SESSION['md5hash'] = "";
			$_SESSION['admin'] = "0";
		}

		return 0;
	}

	/**
	 * download files from knowledgeroot
	 */
	function check_download() {
		if(preg_match("/download\/([0-9]+)\/.*/",$this->getIndpEnv("TYPO3_SITE_SCRIPT"),$found)) {
			if($_SERVER["REQUEST_METHOD"] == "GET") {
				$_GET['download'] = $found[1];
			}
		}

		if (isset ($_GET['download']) and $_GET['download'] != "") {
			$this->CLASS['hooks']->setHook("kr_header","check_download","start");

			$rs = $this->CLASS['db']->query(sprintf("select f.*, c.belongs_to as cid, f.counter as counter from files f, content c where f.belongs_to = c.id AND f.deleted=0 AND f.id =%d",$_GET['download']));
			$anz = $this->CLASS['db']->num_rows($rs);

			if($anz != 1) {
				$this->CLASS['error']->log($this->CLASS['translate']->_("WRONG DOWNLOADFILE!"));
				exit();
			}

			$row = $this->CLASS['db']->fetch_assoc($rs);

			if($this->CLASS['knowledgeroot']->checkRecursivPerm($row['cid'], $_SESSION['userid']) == 0) {
				$this->CLASS['error']->log($this->CLASS['translate']->_("No File for you!"));
				exit();
			}

			$row['counter']++;
			$res = $this->CLASS['db']->query(sprintf("UPDATE files SET counter=%d WHERE id=%d", $row['counter'], $_GET['download']));

			$filename = $row['filename'];

			header("Content-Type: " . $row['filetype'] . "; name=\"$filename\"");
			header("Content-Disposition: attachment; filename=\"$filename\";");
			header("Pragma: private");
			header("Expires: 0");
			header("Cache-Control: private, must-revalidate, post-check=0, pre-check=0");
			header("Content-Transfer-Encoding: binary");

			if($this->CLASS['db']->dbtype == "pgsql") {
				$this->CLASS['db']->query ("begin");
				$loid = $this->CLASS['db']->lo_open($row['object'], "r");
				$this->CLASS['db']->lo_read_all ($loid);
				$this->CLASS['db']->lo_close ($loid);
				$this->CLASS['db']->query ("commit");
				$this->CLASS['db']->close();
			}

			if($this->CLASS['db']->dbtype == "mysql" || $this->CLASS['db']->dbtype == "mysqli") {
				// check for new upload method that uses base64
				if(substr($row['file'],0,7) == 'base64:') {
					echo base64_decode(substr($row['file'],7));
				} else {
					echo unserialize($row['file']);
				}
			}

			if($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") {
				echo base64_decode($row['file']);
			}

			$this->CLASS['hooks']->setHook("kr_header","check_download","end");

			exit();
		}
	}

	/**
	 * this will edit a content
	 */
	function edit_content() {
		if ($this->CLASS['knowledgeroot']->checkRecursivPerm($_SESSION['cid'],$_SESSION['userid']) > 0 && $this->CLASS['knowledgeroot']->getContentRights($_POST['editid'],$_SESSION['userid']) == 2) {
			if (!isset ($_POST['close']) or $_POST['close'] == '') {
				$this->CLASS['hooks']->setHook("kr_header","edit_content","start");

				// check for inheritrights
				$inheritrights = $this->CLASS['knowledgeroot']->getInheritRights($_SESSION['cid']);

				$show_rights = 0;
				if((is_array($inheritrights) && $inheritrights['subinheritrightseditable'] == 1) || $inheritrights == false) {
					$show_rights = 1;
				}

				if ((isset ($_SESSION['rightedit']) and $_SESSION['rightedit'] == 1 && $show_rights == 1) || (isset ($_SESSION['admin']) and $_SESSION['admin'] == 1)) {
					// change content record
					$res = $this->CLASS['db']->query(sprintf("UPDATE content SET content='%s', title='%s', owner=%d, lastupdatedby=%d, lastupdated=".(($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") ? "datetime('now')" : "NOW()") .", ".$this->CLASS['db']->quoteIdentifier("group")."=%d, userrights=%d, grouprights=%d, otherrights=%d WHERE id=%d",$_POST['content'],$_POST['title'],$_POST['user'],$_SESSION['userid'],$_POST['group'],$_POST['userrights'],$_POST['grouprights'],$_POST['otherrights'],$_POST['editid']));
				} else {
					$res = $this->CLASS['db']->query(sprintf("UPDATE content SET content='%s',  title='%s', lastupdatedby=%d, lastupdated=".(($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") ? "datetime('now')" : "NOW()") ." WHERE id=%d",$_POST['content'],$_POST['title'],$_SESSION['userid'],$_POST['editid']));
				}

				// is content closed or should be shown again
				if (isset ($_POST['save']) and $_POST['save'] != "") {
					$_GET['eid'] = $_POST['editid'];
				} else {
					// close content
					$this->CLASS['knowledgeroot']->closeOpenContent($_POST['editid'],$_SESSION['userid']);
				}

				// save multiple rights
				if ((isset ($_SESSION['rightedit']) and $_SESSION['rightedit'] == 1 && $show_rights == 1) || (isset ($_SESSION['admin']) and $_SESSION['admin'] == 1)) {
					$this->CLASS['knowledgeroot']->saveMultipleRightsForm("content", $_POST['editid'], true);
				}

				// save position
				if(isset($_POST['position']) && $_POST['position'] != "") {
					if($_POST['position'] == "first") {
						$res = $this->CLASS['db']->squery("SELECT id FROM content WHERE belongs_to=%d AND deleted=0 ORDER BY sorting ASC LIMIT 1", $_POST['belongsto']);
						$cnt = $this->CLASS['db']->num_rows($res);
						if($cnt == 1) {
							$row = $this->CLASS['db']->fetch_assoc($res);
							$this->_move_content_position($_POST['editid'], $row['id'], $_POST['belongsto'], "before", false);
						}
					} else {
						$this->_move_content_position($_POST['editid'], $_POST['position'], $_POST['belongsto'], "after", false);
					}
				}

				// email notification
				$this->CLASS['notification']->send_email_notification($_SESSION['cid'],"content","edited",$_POST['title'],$_POST['editid']);

				$this->CLASS['hooks']->setHook("kr_header","edit_content","end");
			} else {
				// close content
				$this->CLASS['knowledgeroot']->closeOpenContent($_POST['editid'],$_SESSION['userid']);
			}
		}
	}

	/**
	 * this will create a new content
	 */
	function new_content() {
		if ($this->CLASS['knowledgeroot']->checkRecursivPerm($_POST['belongsto'],$_SESSION['userid']) > 0 && $this->CLASS['knowledgeroot']->getPageRights($_POST['belongsto'],$_SESSION['userid']) == 2) {
			if (!isset ($_POST['close']) or $_POST['close'] == '') {
				$this->CLASS['hooks']->setHook("kr_header","new_content","start");

				// get next sorting value
				$res = $this->CLASS['db']->query(sprintf("SELECT max(sorting) as sorting FROM content WHERE belongs_to=%d",$_POST['belongsto']));
				$sort = $this->CLASS['db']->fetch_assoc($res);

				$sorting = $sort['sorting'] + 1;

				// check for inheritrights
				$inheritrights = $this->CLASS['knowledgeroot']->getInheritRights($_SESSION['cid']);

				$show_rights = 0;
				if((is_array($inheritrights) && $inheritrights['subinheritrightseditable'] == 1) || $inheritrights == false) {
					$show_rights = 1;
				}

				if (isset ($_SESSION['rightedit']) and $_SESSION['rightedit'] == 1 && $show_rights == 1) {
					$res = $this->CLASS['db']->query(sprintf("INSERT INTO content (belongs_to, sorting, content, title, createdate, lastupdatedby, lastupdated, owner,".$this->CLASS['db']->quoteIdentifier("group").",userrights,grouprights,otherrights) VALUES(%d, %d, '%s', '%s', ".(($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") ? "datetime('now')" : "NOW()").", %d, ".(($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") ? "datetime('now')" : "NOW()") .", %d, %d, %d, %d, %d)",$_POST['belongsto'],$sorting,$_POST['content'],$_POST['title'],$_SESSION['userid'],$_POST['user'],$_POST['group'],$_POST['userrights'],$_POST['grouprights'],$_POST['otherrights']));
					$contentid = $this->CLASS['db']->last_id("seq_knowledge");
				} else {
					if(!empty($_SESSION['userid'])) {
						if(is_array($inheritrights) && $inheritrights['subinheritrights'] == 1) {
							$res = $this->CLASS['db']->query(sprintf("INSERT INTO content (belongs_to, sorting, content, title, createdate, lastupdatedby, lastupdated, owner,".$this->CLASS['db']->quoteIdentifier("group").",userrights,grouprights,otherrights) VALUES(%d, %d, '%s', '%s', ".(($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") ? "datetime('now')" : "NOW()").", %d, ".(($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") ? "datetime('now')" : "NOW()") .", %d, %d, %d, %d, %d)",$_POST['belongsto'],$sorting,$_POST['content'],$_POST['title'],$_SESSION['userid'],$inheritrights['subinheritowner'],$inheritrights['subinheritgroup'],$inheritrights['subinherituserrights'],$inheritrights['subinheritgrouprights'],$inheritrights['subinheritotherrights']));
							$contentid = $this->CLASS['db']->last_id("seq_knowledge");

							$this->CLASS['knowledgeroot']->saveSubInheritMultipleRightsFrom("content", $_POST['belongsto'], $contentid, true);
						} else {
							// user logged in but have no rightedit -> use defaultrights
							$res = $this->CLASS['db']->query(sprintf("SELECT id,defaultgroup,defaultrights FROM users WHERE id=%s",$_SESSION['userid']));
							$anz = $this->CLASS['db']->num_rows($res);

							if($anz == 1) {
								$row = $this->CLASS['db']->fetch_assoc($res);
								$res = $this->CLASS['db']->query(sprintf("INSERT INTO content (belongs_to, sorting, content, title, createdate, lastupdatedby, lastupdated, owner,".$this->CLASS['db']->quoteIdentifier("group").",userrights,grouprights,otherrights) VALUES(%d, %d, '%s', '%s', ".(($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") ? "datetime('now')" : "NOW()").", %d, ".(($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") ? "datetime('now')" : "NOW()") .", %d, %d, %d, %d, %d)",$_POST['belongsto'],$sorting,$_POST['content'],$_POST['title'],$_SESSION['userid'],$row['id'],$row['defaultgroup'],substr($row['defaultrights'],0,1),substr($row['defaultrights'],1,1),substr($row['defaultrights'],2,1)));
								$contentid = $this->CLASS['db']->last_id("seq_knowledge");
							} else {
								$res = $this->CLASS['db']->query(sprintf("INSERT INTO content (belongs_to, sorting, content, title, createdate, lastupdatedby, lastupdated, owner,".$this->CLASS['db']->quoteIdentifier("group").",userrights,grouprights,otherrights) VALUES(%d, %d, '%s', '%s', ".(($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") ? "datetime('now')" : "NOW()").", %d, ".(($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") ? "datetime('now')" : "NOW()") .", 0, 0, 2, 2, 2)",$_POST['belongsto'],$sorting,$_POST['content'],$_POST['title'],$_SESSION['userid']));
								$contentid = $this->CLASS['db']->last_id("seq_knowledge");
							}
						}
					} else {
						// no user logged in
						if(is_array($inheritrights) && $inheritrights['subinheritrights'] == 1) {
							$res = $this->CLASS['db']->query(sprintf("INSERT INTO content (belongs_to, sorting, content, title, createdate, lastupdatedby, lastupdated, owner,".$this->CLASS['db']->quoteIdentifier("group").",userrights,grouprights,otherrights) VALUES(%d, %d, '%s', '%s', ".(($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") ? "datetime('now')" : "NOW()").", %d, ".(($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") ? "datetime('now')" : "NOW()") .", %d, %d, %d, %d, %d)",$_POST['belongsto'],$sorting,$_POST['content'],$_POST['title'],$_SESSION['userid'],$inheritrights['subinheritowner'],$inheritrights['subinheritgroup'],$inheritrights['subinherituserrights'],$inheritrights['subinheritgrouprights'],$inheritrights['subinheritotherrights']));
							$contentid = $this->CLASS['db']->last_id("seq_knowledge");

							$this->CLASS['knowledgeroot']->saveSubInheritMultipleRightsFrom("content", $_POST['belongsto'], $contentid, true);
						} else {
							$res = $this->CLASS['db']->query(sprintf("INSERT INTO content (belongs_to, sorting, content, title, createdate, lastupdatedby, lastupdated, owner,".$this->CLASS['db']->quoteIdentifier("group").",userrights,grouprights,otherrights) VALUES(%d, %d, '%s', '%s', ".(($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") ? "datetime('now')" : "NOW()").", %d, ".(($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") ? "datetime('now')" : "NOW()") .", 0, 0, 2, 2, 2)",$_POST['belongsto'],$sorting,$_POST['content'],$_POST['title'],$_SESSION['userid']));
							$contentid = $this->CLASS['db']->last_id("seq_knowledge");

							$this->CLASS['knowledgeroot']->saveInheritMultipleRightsFrom("content", $_POST['belongsto'], $contentid);
						}
					}
				}

				//$contentid = $this->CLASS['db']->last_id("seq_knowledge");

				if (isset ($_POST['save']) and $_POST['save'] != "") {
					$_GET['eid'] = $contentid;
				} else {
					$_GET['eid'] = '';
				}

				// save multiple rights
				if (isset ($_SESSION['rightedit']) and $_SESSION['rightedit'] == 1 && $show_rights == 1) {
					$this->CLASS['knowledgeroot']->saveMultipleRightsForm("content", $contentid);
				}

				// save position
				if(isset($_POST['position']) && $_POST['position'] != "") {
					if($_POST['position'] == "first") {
						// $contentid, $targetcontentid, $pageid, $position
						$res = $this->CLASS['db']->squery("SELECT id FROM content WHERE belongs_to=%d AND deleted=0 ORDER BY sorting ASC LIMIT 1", $_POST['belongsto']);
						$cnt = $this->CLASS['db']->num_rows($res);
						if($cnt == 1) {
							$row = $this->CLASS['db']->fetch_assoc($res);
							$this->_move_content_position($contentid, $row['id'], $_POST['belongsto'], "before", false);
						}
					} else {
						$this->_move_content_position($contentid, $_POST['position'], $_POST['belongsto'], "after", false);
					}
				}

				//$pagename = $this->CLASS['path']->getTreePageTitle($_POST['belongsto']);

				// email notification
				$this->CLASS['notification']->send_email_notification($_POST['belongsto'],"content","created",$_POST['title'], $contentid);

				$this->CLASS['hooks']->setHook("kr_header","new_content","end");
			}
		}
	}

	/**
	 * this will create a new page
	 */
	function new_page() {
		if($this->CLASS['knowledgeroot']->checkRecursivPerm($_POST['belongsto'],$_SESSION['userid']) > 0 && $this->CLASS['knowledgeroot']->getPageRights($_POST['belongsto'],$_SESSION['userid']) == 2 && $_POST['title'] != "") {
			$this->CLASS['hooks']->setHook("kr_header","new_page","start");

			// check if alias already exists
			if (isset ($_POST['alias']) && $_POST['alias'] != "") {
				$_POST['alias'] = $this->CLASS['knowledgeroot']->checkAlias($_POST['alias']);
			}

			// check for inheritrights
			$inheritrights = $this->CLASS['knowledgeroot']->getInheritRights($_SESSION['cid']);

			$show_rights = 0;
			if((is_array($inheritrights) && $inheritrights['subinheritrightseditable'] == 1) || $inheritrights == false) {
				$show_rights = 1;
			}

			// set tooltip
			if($this->CLASS['config']->tree->edittooltiptext == 1 && isset($_POST['tooltip']) && $_POST['tooltip'] != "") {
				$tooltip = $_POST['tooltip'];
			} else {
				$tooltip = "";
			}

			// set symlink
			if(isset($this->CLASS['config']->tree->symlink) && $this->CLASS['config']->tree->symlink == 1 && isset($_POST['symlink']) && $_POST['symlink'] != "") {
				$symlink = (int)$_POST['symlink'];
			} else {
				$symlink = 0;
			}

			// set sorting
			if($this->CLASS['config']->tree->order == 1 && isset($_POST['sorting']) && $_POST['sorting'] != "") {
				$sorting = (int)$_POST['sorting'];
			} else {
				$sorting = "0";
			}

			if (isset ($_SESSION['rightedit']) and $_SESSION['rightedit'] == 1 && $show_rights == 1) {
				if($this->CLASS['config']->misc->pagealias->use == 1 && (($this->CLASS['config']->misc->pagealias->rights == 2 && isset ($_SESSION['admin']) and $_SESSION['admin'] == 1) || ($this->CLASS['config']->misc->pagealias->rights == 1 && $_SESSION['userid'] != 0) || ($this->CLASS['config']->misc->pagealias->rights == 0))) {
					$res = $this->CLASS['db']->query(sprintf("INSERT INTO tree (belongs_to,title,symlink,tooltip,sorting,defaultcontentposition,alias,icon,owner,".$this->CLASS['db']->quoteIdentifier("group").",userrights,grouprights,otherrights) VALUES (%d,'%s', %d, '%s', %d, %d, '%s', '%s', %d, %d, %d, %d, %d)",$_POST['belongsto'],$_POST['title'],$symlink,$tooltip,$sorting,$_POST['defaultcontentposition'],$_POST['alias'],$_POST['treeicon'],$_POST['user'],$_POST['group'],$_POST['userrights'],$_POST['grouprights'],$_POST['otherrights']));
				} else {
					$res = $this->CLASS['db']->query(sprintf("INSERT INTO tree (belongs_to,title,symlink,tooltip,sorting,defaultcontentposition,icon,owner,".$this->CLASS['db']->quoteIdentifier("group").",userrights,grouprights,otherrights) VALUES (%d,'%s', %d,'%s', %d, %d, '%s', %d, %d, %d, %d, %d)",$_POST['belongsto'],$_POST['title'],$symlink,$tooltip,$sorting,$_POST['defaultcontentposition'],$_POST['treeicon'],$_POST['user'],$_POST['group'],$_POST['userrights'],$_POST['grouprights'],$_POST['otherrights']));
				}

				$new_page_id = $this->CLASS['db']->last_id("seq_knowledge");
			} else {
				if(!empty($_SESSION['userid'])) {
					// user logged in but have no rightedit -> use defaultrights
					if(is_array($inheritrights) && $inheritrights['subinheritrights'] == 1) {
						if($this->CLASS['config']->misc->pagealias->use == 1 && (($this->CLASS['config']->misc->pagealias->rights == 2 && isset ($_SESSION['admin']) and $_SESSION['admin'] == 1) || ($this->CLASS['config']->misc->pagealias->rights == 1 && $_SESSION['userid'] != 0) || ($this->CLASS['config']->misc->pagealias->rights == 0))) {
							$res = $this->CLASS['db']->query(sprintf("INSERT INTO tree (belongs_to,title,symlink,tooltip,sorting,defaultcontentposition,alias,icon,owner,".$this->CLASS['db']->quoteIdentifier("group").",userrights,grouprights,otherrights) VALUES (%d, '%s', %d, '%s', %d, %d, '%s', '%s', %d, %d, %d, %d, %d)",$_POST['belongsto'],$_POST['title'],$symlink, $tooltip,$sorting,$_POST['defaultcontentposition'],$_POST['alias'],$_POST['treeicon'],$inheritrights['subinheritowner'],$inheritrights['subinheritgroup'],$inheritrights['subinherituserrights'],$inheritrights['subinheritgrouprights'],$inheritrights['subinheritotherrights']));
							$new_page_id = $this->CLASS['db']->last_id("seq_knowledge");
						} else {
							$res = $this->CLASS['db']->query(sprintf("INSERT INTO tree (belongs_to,title,symlink,tooltip,sorting,defaultcontentposition,icon,owner,".$this->CLASS['db']->quoteIdentifier("group").",userrights,grouprights,otherrights) VALUES (%d, '%s', %d, '%s', %d, %d, '%s', %d, %d, %d, %d, %d)",$_POST['belongsto'],$_POST['title'],$symlink, $tooltip,$sorting,$_POST['defaultcontentposition'],$_POST['treeicon'],$inheritrights['subinheritowner'],$inheritrights['subinheritgroup'],$inheritrights['subinherituserrights'],$inheritrights['subinheritgrouprights'],$inheritrights['subinheritotherrights']));
							$new_page_id = $this->CLASS['db']->last_id("seq_knowledge");
						}

						$this->CLASS['knowledgeroot']->saveSubInheritMultipleRightsFrom("tree", $_POST['belongsto'], $new_page_id, true);
					} else {
						$res = $this->CLASS['db']->query(sprintf("SELECT id,defaultgroup,defaultrights FROM users WHERE id=%d",$_SESSION['userid']));
						$anz = $this->CLASS['db']->num_rows($res);

						if($anz == 1) {
							$row = $this->CLASS['db']->fetch_assoc($res);
							if($this->CLASS['config']->misc->pagealias->use == 1 && (($this->CLASS['config']->misc->pagealias->rights == 2 && isset ($_SESSION['admin']) and $_SESSION['admin'] == 1) || ($this->CLASS['config']->misc->pagealias->rights == 1 && $_SESSION['userid'] != 0) || ($this->CLASS['config']->misc->pagealias->rights == 0))) {
								$res = $this->CLASS['db']->query(sprintf("INSERT INTO tree (belongs_to,title,symlink,tooltip,sorting,defaultcontentposition,alias,icon,owner,".$this->CLASS['db']->quoteIdentifier("group").",userrights,grouprights,otherrights) VALUES (%d, '%s', %d, '%s', %d, '%s', '%s', %d, %d, %d, %d, %d)",$_POST['belongsto'],$_POST['title'],$symlink,$tooltip,$sorting,$_POST['alias'],$_POST['treeicon'],$row['id'],$row['defaultgroup'],substr($row['defaultrights'],0,1),substr($row['defaultrights'],1,1),substr($row['defaultrights'],2,1)));
								$new_page_id = $this->CLASS['db']->last_id("seq_knowledge");
							} else {
								$res = $this->CLASS['db']->query(sprintf("INSERT INTO tree (belongs_to,title,symlink,tooltip,sorting,defaultcontentposition,icon,owner,".$this->CLASS['db']->quoteIdentifier("group").",userrights,grouprights,otherrights) VALUES (%d, '%s', %d, '%s', %d, '%s', %d, %d, %d, %d, %d)",$_POST['belongsto'],$_POST['title'],$symlink,$tooltip,$sorting,$_POST['treeicon'],$row['id'],$row['defaultgroup'],substr($row['defaultrights'],0,1),substr($row['defaultrights'],1,1),substr($row['defaultrights'],2,1)));
								$new_page_id = $this->CLASS['db']->last_id("seq_knowledge");
							}
						} else {
							if($this->CLASS['config']->misc->pagealias->use == 1 && (($this->CLASS['config']->misc->pagealias->rights == 2 && isset ($_SESSION['admin']) and $_SESSION['admin'] == 1) || ($this->CLASS['config']->misc->pagealias->rights == 1 && $_SESSION['userid'] != 0) || ($this->CLASS['config']->misc->pagealias->rights == 0))) {
								$res = $this->CLASS['db']->query(sprintf("INSERT INTO tree (belongs_to,title,symlink,tooltip,sorting,defaultcontentposition,alias,icon,owner,".$this->CLASS['db']->quoteIdentifier("group").",userrights,grouprights,otherrights) VALUES (%d, '%s', %d, '%s', %d, '%s', '%s', 0, 0, 2, 2, 2)",$_POST['belongsto'],$_POST['title'],$symlink,$tooltip,$sorting,$_POST['alias'],$_POST['treeicon']));
								$new_page_id = $this->CLASS['db']->last_id("seq_knowledge");
							} else {
								$res = $this->CLASS['db']->query(sprintf("INSERT INTO tree (belongs_to,title,symlink,tooltip,sorting,defaultcontentposition,icon,owner,".$this->CLASS['db']->quoteIdentifier("group").",userrights,grouprights,otherrights) VALUES (%d, '%s', %d, '%s', %d, '%s', 0, 0, 2, 2, 2)",$_POST['belongsto'],$_POST['title'], $symlink,$tooltip, $sorting, $_POST['treeicon']));
								$new_page_id = $this->CLASS['db']->last_id("seq_knowledge");
							}
						}
					}
				} else {
					// no user logged in
					if(is_array($inheritrights) && $inheritrights['subinheritrights'] == 1) {
						if($this->CLASS['config']->misc->pagealias->use == 1 && (($this->CLASS['config']->misc->pagealias->rights == 2 && isset ($_SESSION['admin']) and $_SESSION['admin'] == 1) || ($this->CLASS['config']->misc->pagealias->rights == 1 && $_SESSION['userid'] != 0) || ($this->CLASS['config']->misc->pagealias->rights == 0))) {
							$res = $this->CLASS['db']->query(sprintf("INSERT INTO tree (belongs_to,title,symlink,tooltip,sorting,defaultcontentposition,alias,icon,owner,".$this->CLASS['db']->quoteIdentifier("group").",userrights,grouprights,otherrights) VALUES (%d, '%s', %d, '%s', %d, %d, '%s', '%s', %d, %d, %d, %d, %d)",$_POST['belongsto'],$_POST['title'],$symlink,$tooltip,$sorting,$_POST['defaultcontentposition'],$_POST['alias'],$_POST['treeicon'],$inheritrights['subinheritowner'],$inheritrights['subinheritgroup'],$inheritrights['subinherituserrights'],$inheritrights['subinheritgrouprights'],$inheritrights['subinheritotherrights']));
							$new_page_id = $this->CLASS['db']->last_id("seq_knowledge");
						} else {
							$res = $this->CLASS['db']->query(sprintf("INSERT INTO tree (belongs_to,title,symlink,tooltip,sorting,defaultcontentposition,icon,owner,".$this->CLASS['db']->quoteIdentifier("group").",userrights,grouprights,otherrights) VALUES (%d, '%s', %d, '%s', %d, %d, '%s', %d, %d, %d, %d, %d)",$_POST['belongsto'],$_POST['title'],$symlink,$tooltip,$sorting,$_POST['defaultcontentposition'],$_POST['treeicon'],$inheritrights['subinheritowner'],$inheritrights['subinheritgroup'],$inheritrights['subinherituserrights'],$inheritrights['subinheritgrouprights'],$inheritrights['subinheritotherrights']));
							$new_page_id = $this->CLASS['db']->last_id("seq_knowledge");
						}

						$this->CLASS['knowledgeroot']->saveSubInheritMultipleRightsFrom("tree", $_POST['belongsto'], $new_page_id, true);
					} else {
						if($this->CLASS['config']->misc->pagealias->use == 1 && (($this->CLASS['config']->misc->pagealias->rights == 2 && isset ($_SESSION['admin']) and $_SESSION['admin'] == 1) || ($this->CLASS['config']->misc->pagealias->rights == 1 && $_SESSION['userid'] != 0) || ($this->CLASS['config']->misc->pagealias->rights == 0))) {
							$res = $this->CLASS['db']->query(sprintf("INSERT INTO tree (belongs_to,title,symlink,tooltip,sorting,defaultcontentposition,alias,icon,owner,".$this->CLASS['db']->quoteIdentifier("group").",userrights,grouprights,otherrights) VALUES (%d, '%s', %d, '%s', %d, %d, '%s', '%s', 0, 0, 2, 2, 2)",$_POST['belongsto'],$_POST['title'],$symlink,$tooltip,$sorting,$_POST['defaultcontentposition'],$_POST['alias'],$_POST['treeicon']));
							$new_page_id = $this->CLASS['db']->last_id("seq_knowledge");
						} else {
							$res = $this->CLASS['db']->query(sprintf("INSERT INTO tree (belongs_to,title,symlink,tooltip,sorting,defaultcontentposition,icon,owner,".$this->CLASS['db']->quoteIdentifier("group").",userrights,grouprights,otherrights) VALUES (%d, '%s', %d, '%s', %d, %d, '%s', 0, 0, 2, 2, 2)",$_POST['belongsto'],$_POST['title'],$symlink,$tooltip,$sorting,$_POST['defaultcontentposition'],$_POST['treeicon']));
							$new_page_id = $this->CLASS['db']->last_id("seq_knowledge");
						}

						$this->CLASS['knowledgeroot']->saveInheritMultipleRightsFrom("tree", $_POST['belongsto'], $new_page_id);
					}
				}
			}

			// get pageid of the created page
			//$new_page_id = $this->CLASS['db']->last_id("seq_knowledge");

			// check for subinheritsrights
			if(isset ($_SESSION['admin']) && $_SESSION['admin'] == 1 && ((isset($_POST['subinheritrights']) && $_POST['subinheritrights'] == 1) || (isset($_POST['subinheritrightsdisable']) && $_POST['subinheritrightsdisable'] == 1))) {
				if(!isset($_POST['subinheritrightseditable']) || $_POST['subinheritrightseditable'] != 1) {
					$_POST['subinheritrightseditable'] = 0;
				}

				if(!isset($_POST['subinheritrights']) || $_POST['subinheritrights'] != 1) {
					$_POST['subinheritrights'] = 0;
				}

				if(!isset($_POST['subinheritrightsdisable']) || $_POST['subinheritrightsdisable'] != 1) {
					$_POST['subinheritrightsdisable'] = 0;
				}

				$res = $this->CLASS['db']->query(sprintf("UPDATE tree SET subinheritrights=%d, subinheritrightseditable=%d, subinheritrightsdisable=%d, subinheritowner=%d, subinheritgroup=%d, subinherituserrights=%d, subinheritgrouprights=%d, subinheritotherrights=%d WHERE id=%d",$_POST['subinheritrights'],$_POST['subinheritrightseditable'],$_POST['subinheritrightsdisable'],$_POST['subinherituser'],$_POST['subinheritgroup'],$_POST['subinherituserrights'],$_POST['subinheritgrouprights'],$_POST['subinheritotherrights'],$new_page_id));

				// save multiple subinheritrights
				$this->CLASS['knowledgeroot']->saveSubinheritMultipleRightsForm("tree", $new_page_id);
			}

			// save multiple rights
			if ((isset ($_SESSION['rightedit']) and $_SESSION['rightedit'] == 1 && $show_rights == 1) || (isset ($_SESSION['admin']) and $_SESSION['admin'] == 1)) {
				$this->CLASS['knowledgeroot']->saveMultipleRightsForm("tree", $new_page_id);
			}

			// email notification
			$this->CLASS['notification']->send_email_notification($new_page_id,"page","created",$_POST['title'],$new_page_id);

			$_SESSION['open'][$_POST['belongsto']] = 1;

			// simply redirect if the user select "automatically open the created page"
			if (isset($_POST['auto_open']) && $_POST['auto_open'] == "true") {
				$_SESSION['auto_open'] = true;
				header("Location: index.php?id=$new_page_id");
				exit();
			} else {
				$_SESSION['auto_open'] = false;
			}

			$this->CLASS['hooks']->setHook("kr_header","new_page","end");
		}
	}

	/**
	 * make file upload
	 */
	function upload_file() {
		// check size of file
		if(isset($this->CLASS['config']->upload->maxfilesize) && $this->CLASS['config']->upload->maxfilesize != "" && $_FILES['datei']['size'] > $this->CLASS['config']->upload->maxfilesize) {
			$this->addwarning($this->CLASS['translate']->_('Cannot add file. File is to big!'));
			return "";
		}

		if($this->CLASS['knowledgeroot']->getContentRights($_POST['contentid'],$_SESSION['userid']) == 2) {
			$this->CLASS['hooks']->setHook("kr_header","upload_file","start");

			$uploaddir = $this->CLASS['config']->upload->path;

			if(!is_dir($uploaddir)) {
				$this->addwarning($this->CLASS['translate']->_('No upload folder found!'));
				return 0;
			}

			if($_FILES['datei']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['datei']['tmp_name']) && is_file($_FILES['datei']['tmp_name'])) {
				$fp = fopen($_FILES['datei']['tmp_name'],"r");
				$buffer = fread($fp,filesize($_FILES['datei']['tmp_name']));
				fclose($fp);

				//import for postgresql
				if($this->CLASS['db']->dbtype == "pgsql") {
					$this->CLASS['db']->query ("begin");
					$oid = $this->CLASS['db']->lo_create ();
					$rs = $this->CLASS['db']->query(sprintf("insert into files (belongs_to,object,filename,filesize,filetype,owner) values(%d, %d,'%s', %d, '%s',%d)",$_POST['contentid'],$oid,addslashes($_FILES['datei']['name']),$_FILES['datei']['size'],addslashes($_FILES['datei']['type']),$_SESSION['userid']));//object field type must be "oid"
					$handle = $this->CLASS['db']->lo_open ($oid, "w");
					$this->CLASS['db']->lo_write ($handle, $buffer);
					$this->CLASS['db']->lo_close ($handle);
					$this->CLASS['db']->query ("commit");
				}

				//import for mysql
				if($this->CLASS['db']->dbtype == "mysql" || $this->CLASS['db']->dbtype == "mysqli") {
					$res = $this->CLASS['db']->query(sprintf("INSERT INTO files(belongs_to,file,filename,filesize,filetype,owner) VALUES (%d,'%s','%s', %d,'%s',%d)",$_POST['contentid'],'base64:'.base64_encode($buffer),addslashes($_FILES['datei']['name']),$_FILES['datei']['size'],addslashes($_FILES['datei']['type']),$_SESSION['userid']));
				}

				//import for sqlite
				if($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") {
					$res = $this->CLASS['db']->query(sprintf("INSERT INTO files(belongs_to,file,filename,filesize,filetype,owner,date) VALUES (%d,'%s','%s', %d,'%s',%d,'%s')",$_POST['contentid'],base64_encode($buffer),addslashes($_FILES['datei']['name']),$_FILES['datei']['size'],addslashes($_FILES['datei']['type']),$_SESSION['userid'],date('Y-m-d H:i:s', time())));
				}

				// email notification
				$this->CLASS['notification']->send_email_notification($_SESSION['cid'],"file","created",addslashes($_FILES['datei']['name']));

				unlink($_FILES['datei']['tmp_name']);

				// should be translated in the future
				//$this->addwarning($this->CLASS['language']->get['fileuploadsuccess']);

				$this->CLASS['hooks']->setHook("kr_header","upload_file","uploaded");
			} else {
				// should be translated in the future
				//$this->addwarning($this->CLASS['language']->get['fileuploaderror']);
			}

			$this->CLASS['hooks']->setHook("kr_header","upload_file","end");
		}
	}

	/**
	 * delete content
	 */
	function delete_content() {
		// delete the content
		$this->_delete_content($_SESSION['cid'], $_GET['delid']);
	}

	/**
	 * delete content element
	 * for internal use
	 * @param integer $contentid
	 */
	function _delete_content($pageid, $contentid) {
		if($this->CLASS['knowledgeroot']->checkRecursivPerm($pageid,$_SESSION['userid']) > 0 && $this->CLASS['knowledgeroot']->getContentRights($contentid,$_SESSION['userid']) == 2) {
			$this->CLASS['hooks']->setHook("kr_header","delete_content","start");

			$title = "";

			$res = $this->CLASS['db']->query(sprintf("UPDATE content SET deleted=1 WHERE id=%d",$contentid));
			$res = $this->CLASS['db']->query(sprintf("UPDATE files SET deleted=1 WHERE belongs_to=%d",$contentid));

			// get id of page and title from content
			$res = $this->CLASS['db']->query(sprintf("SELECT belongs_to, title FROM content WHERE id=%d",$contentid));
			$anz = $this->CLASS['db']->num_rows($res);

			if($anz == 1) {
				$row = $this->CLASS['db']->fetch_assoc($res);
				$pagename = $this->CLASS['path']->getTreePageTitle($row['belongs_to']);
				$title = $row['title'];
				$id = $row['belongs_to'];
			} else {
				$pagename = $this->CLASS['path']->getTreePageTitle($pageid);
				$title = "";
				$id = $pageid;
			}

			// email notification
			$this->CLASS['notification']->send_email_notification($id,"content","deleted",$title,$contentid);

			$this->CLASS['hooks']->setHook("kr_header","delete_content","end");
		}
	}

	/**
	 * delete page
	 */
	function delete_page() {
		// make delete of page
		$this->_delete_page($_GET['delpage']);
	}

	/**
	 * make a delete of a page
	 * will also check for recursiv deletion
	 * for internal use
	 * @param integer $pageid
	 */
	function _delete_page($pageid) {
		if($this->CLASS['knowledgeroot']->checkRecursivPerm($pageid,$_SESSION['userid']) > 0 && $this->CLASS['knowledgeroot']->getPageRights($pageid,$_SESSION['userid']) == 2) {
			$this->CLASS['hooks']->setHook("kr_header","delete_page","start");

			// check for recusriv delete
			if(($this->CLASS['config']->misc->recursivdelete == 2 && $_SESSION['admin'] == 1) || ($this->CLASS['config']->misc->recursivdelete == 1 && !empty($userid)) || ($this->CLASS['config']->misc->recursivdelete == 0)) {
				// get all subpages for delete
				$res = $this->CLASS['db']->query(sprintf("SELECT id FROM tree WHERE belongs_to=%d and deleted=0",$pageid));

				while($row = $this->CLASS['db']->fetch_assoc($res)) {
					$this->_delete_page($row['id']);
				}

				// get all contents for delete
				$res = $this->CLASS['db']->query(sprintf("SELECT id FROM content WHERE belongs_to=%d and deleted=0",$pageid));
				while($row = $this->CLASS['db']->fetch_assoc($res)) {
					$this->_delete_content($pageid, $row['id']);
				}
			}

			$res = $this->CLASS['db']->query(sprintf("SELECT count(*) AS anz FROM content WHERE belongs_to=%d and deleted=0",$pageid));
			$row = $this->CLASS['db']->fetch_object($res);
			$anz = $row->anz;

			$res = $this->CLASS['db']->query(sprintf("SELECT count(*) AS anz FROM tree WHERE belongs_to=%d and deleted=0",$pageid));
			$row = $this->CLASS['db']->fetch_object($res);
			$anz = $anz + $row->anz;


			if($anz == "0") {
				$res = $this->CLASS['db']->query(sprintf("UPDATE tree SET deleted=1 WHERE id=%d",$pageid));
				$_SESSION['cid'] = $this->CLASS['path']->getParent($pageid);

				$pagename = $this->CLASS['path']->getTreePageTitle($pageid);

				$res = $this->CLASS['db']->query(sprintf("SELECT belongs_to FROM tree WHERE id=%d",$pageid));
				$anz = $this->CLASS['db']->num_rows($res);

				if($anz == 1) {
					$row = $this->CLASS['db']->fetch_assoc($res);
					$id = $row['belongs_to'];
				} else {
					$id = $pageid;
				}

				// email notification
				$this->CLASS['notification']->send_email_notification($pageid,"page","deleted",$pagename,$pageid);

				$this->CLASS['hooks']->setHook("kr_header","delete_page","success");
			} else {
				$this->addwarning($this->CLASS['translate']->_('Cannot delete page. Check if content is on the page!'));
				$this->CLASS['hooks']->setHook("kr_header","delete_page","failed");
			}

			$this->CLASS['hooks']->setHook("kr_header","delete_page","end");
		}
	}

	/**
	 * edit page
	 */
	function edit_page() {
		if($this->CLASS['knowledgeroot']->checkRecursivPerm($_SESSION['cid'],$_SESSION['userid']) > 0 && $this->CLASS['knowledgeroot']->getPageRights($_SESSION['cid'],$_SESSION['userid']) == 2 && $_POST['title'] != "") {
			$this->CLASS['hooks']->setHook("kr_header","edit_page","start");

			// check if alias is already used
			$res = $this->CLASS['db']->query(sprintf("SELECT alias FROM tree WHERE id=%d",$_SESSION['cid']));
			$anz = $this->CLASS['db']->num_rows($res);

			if (!isset ($_POST['contentcollapsed']) or $_POST['contentcollapsed'] == "") {
				$collapsed = 0;
			} else {
				$collapsed = 1;
			}

			if (!isset ($_POST['alias'])) {  $_POST['alias'] = ''; }

			if($anz == 1) {
				$row = $this->CLASS['db']->fetch_assoc($res);
				if ($row['alias'] != $_POST['alias'] && $_POST['alias'] != "") {
					$_POST['alias'] = $this->CLASS['knowledgeroot']->checkAlias($_POST['alias']);
				}
			} else {
				if($_POST['alias'] != "") {
					$_POST['alias'] = $this->CLASS['knowledgeroot']->checkAlias($_POST['alias']);
				}
			}

			// set tooltip
			if($this->CLASS['config']->tree->edittooltiptext == 1 && isset($_POST['tooltip']) && $_POST['tooltip'] != "") {
				$tooltip = $_POST['tooltip'];
			} else {
				$tooltip = "";
			}

			// set symlink
			if(isset($this->CLASS['config']->tree->symlink) && $this->CLASS['config']->tree->symlink == 1 && isset($_POST['symlink']) && $_POST['symlink'] != "") {
				$symlink = (int)$_POST['symlink'];
			} else {
				$symlink = 0;
			}

			// set sorting
			if($this->CLASS['config']->tree->order == 1 && isset($_POST['sorting']) && $_POST['sorting'] != "") {
				$sorting = (int)$_POST['sorting'];
			} else {
				$sorting = "0";
			}

			// check for inheritrights
			$inheritrights = $this->CLASS['knowledgeroot']->getInheritRights($_SESSION['cid']);

			$show_rights = 0;
			if((is_array($inheritrights) && $inheritrights['subinheritrightseditable'] == 1) || $inheritrights == false) {
				$show_rights = 1;
			}

			if ((isset ($_SESSION['rightedit']) and $_SESSION['rightedit'] == 1 && $show_rights == 1) || (isset ($_SESSION['admin']) and $_SESSION['admin'] == 1)) {
				if($this->CLASS['config']->misc->pagealias->use == 1 && (($this->CLASS['config']->misc->pagealias->rights == 2 && $_SESSION['admin'] == 1) || ($this->CLASS['config']->misc->pagealias->rights == 1 && $_SESSION['userid'] != 0) || ($this->CLASS['config']->misc->pagealias->rights == 0))) {
					$res = $this->CLASS['db']->query(sprintf("UPDATE tree SET title='%s', symlink=%d, tooltip='%s', sorting=%d, contentcollapsed=%d, defaultcontentposition=%d, alias='%s', icon='%s', owner=%d, ".$this->CLASS['db']->quoteIdentifier("group")."=%d, userrights=%d, grouprights=%d, otherrights=%d WHERE id=%d",$_POST['title'],$symlink,$tooltip,$sorting,$collapsed,$_POST['defaultcontentposition'],$_POST['alias'],$_POST['treeicon'],$_POST['user'],$_POST['group'],$_POST['userrights'],$_POST['grouprights'],$_POST['otherrights'],$_SESSION['cid']));
				} else {
					$res = $this->CLASS['db']->query(sprintf("UPDATE tree SET title='%s', symlink=%d,tooltip='%s', sorting=%d, contentcollapsed=%d, defaultcontentposition=%d, icon='%s', owner=%d, ".$this->CLASS['db']->quoteIdentifier("group")."=%d, userrights=%d, grouprights=%d, otherrights=%d WHERE id=%d",$_POST['title'],$symlink,$tooltip,$sorting,$collapsed,$_POST['defaultcontentposition'],$_POST['treeicon'],$_POST['user'],$_POST['group'],$_POST['userrights'],$_POST['grouprights'],$_POST['otherrights'],$_SESSION['cid']));
				}
			} else {
				if($this->CLASS['config']->misc->pagealias->use == 1 && (($this->CLASS['config']->misc->pagealias->rights == 2 && isset ($_SESSION['admin']) and $_SESSION['admin'] == 1) || ($this->CLASS['config']->misc->pagealias->rights == 1 && $_SESSION['userid'] != 0) || ($this->CLASS['config']->misc->pagealias->rights == 0))) {
					$res = $this->CLASS['db']->query(sprintf("UPDATE tree SET title='%s', symlink=%d, tooltip='%s', sorting=%d, contentcollapsed=%d, defaultcontentposition=%d, alias='%s', icon='%s' WHERE id=%d",$_POST['title'],$symlink,$tooltip,$sorting,$collapsed,$_POST['defaultcontentposition'],$_POST['alias'],$_POST['treeicon'],$_SESSION['cid']));
				} else {
					$res = $this->CLASS['db']->query(sprintf("UPDATE tree SET title='%s', symlink=%d, tooltip='%s', sorting=%d, contentcollapsed=%d, defaultcontentposition=%d, icon='%s' WHERE id=%d",$_POST['title'],$symlink,$tooltip,$sorting,$collapsed,$_POST['defaultcontentposition'],$_POST['treeicon'],$_SESSION['cid']));
				}
			}

			// used var for check if multiple rights in table access are already deleted
			$deleted = false;

			// check for subinheritsrights
			if(isset ($_SESSION['admin']) && $_SESSION['admin'] == 1) {
				if(!isset($_POST['subinheritrightseditable']) || $_POST['subinheritrightseditable'] != 1) {
					$_POST['subinheritrightseditable'] = 0;
				}

				if(!isset($_POST['subinheritrights']) || $_POST['subinheritrights'] != 1) {
					$_POST['subinheritrights'] = 0;
				}

				if(!isset($_POST['subinheritrightsdisable']) || $_POST['subinheritrightsdisable'] != 1) {
					$_POST['subinheritrightsdisable'] = 0;
				}

				$res = $this->CLASS['db']->query(sprintf("UPDATE tree SET subinheritrights=%d, subinheritrightseditable=%d, subinheritrightsdisable=%d, subinheritowner=%d, subinheritgroup=%d, subinherituserrights=%d, subinheritgrouprights=%d, subinheritotherrights=%d WHERE id=%d",$_POST['subinheritrights'],$_POST['subinheritrightseditable'],$_POST['subinheritrightsdisable'],$_POST['subinherituser'],$_POST['subinheritgroup'],$_POST['subinherituserrights'],$_POST['subinheritgrouprights'],$_POST['subinheritotherrights'],$_SESSION['cid']));

				// save multiple subinheritrights
				$this->CLASS['knowledgeroot']->saveSubinheritMultipleRightsForm("tree", $_SESSION['cid'], true);
				$deleted = true;
			}

			// save multiple rights
			if ((isset ($_SESSION['rightedit']) and $_SESSION['rightedit'] == 1 && $show_rights == 1) || (isset ($_SESSION['admin']) and $_SESSION['admin'] == 1)) {
				$this->CLASS['knowledgeroot']->saveMultipleRightsForm("tree", $_SESSION['cid'], (($deleted == true) ? false : true));
			}

			// set rights recursiv
			if (((isset ($_SESSION['rightedit']) and $_SESSION['rightedit'] == 1 && $show_rights == 1) || (isset ($_SESSION['admin']) and $_SESSION['admin'] == 1)) && $_POST['recursiv'] == 1) {
				$this->CLASS['knowledgeroot']->setRightsRecursiv($_SESSION['cid'],$_SESSION['userid'],$_POST['user'],$_POST['group'],$_POST['userrights'].$_POST['grouprights'].$_POST['otherrights']);
			}

			$pagename = $this->CLASS['path']->getTreePageTitle($_SESSION['cid']);

			// email notification
			$this->CLASS['notification']->send_email_notification($_SESSION['cid'],"page","edited",$pagename,$_SESSION['cid']);

			$this->CLASS['hooks']->setHook("kr_header","edit_page","end");
		}
	}

	/**
	 * delete file
	 */
	function delete_file() {
		$res = $this->CLASS['db']->query(sprintf("SELECT belongs_to FROM files WHERE id=%d AND deleted=0",$_GET['delfile']));
		$anz = $this->CLASS['db']->num_rows($res);

		if($anz == 1) {
			$row = $this->CLASS['db']->fetch_assoc($res);
			$belongs_to = $row['belongs_to'];

			if($this->CLASS['knowledgeroot']->checkRecursivPerm($_SESSION['cid'],$_SESSION['userid']) > 0 && $this->CLASS['knowledgeroot']->getContentRights($belongs_to,$_SESSION['userid']) == 2) {
				$this->CLASS['hooks']->setHook("kr_header","delete_file","start");

				if($this->CLASS['db']->dbtype == "pgsql") {
					$res = $this->CLASS['db']->query(sprintf("SELECT object FROM files WHERE id=%d",$_GET['delfile']));

					$anz = $this->CLASS['db']->num_rows($res);

					if($anz == 1) {
						$row = $this->CLASS['db']->fetch_assoc($res);
						$this->CLASS['db']->lo_unlink($row['object']);
					}
				}

				//$res = $this->CLASS['db']->query("DELETE FROM files WHERE id='".$_GET['delfile']."'");
				$res = $this->CLASS['db']->query(sprintf("UPDATE files SET deleted=1 WHERE id=%d",$_GET['delfile']));

				// get filename
				$res = $this->CLASS['db']->query(sprintf("SELECT filename FROM files WHERE id=%d",$_GET['delfile']));
				$anz = $this->CLASS['db']->num_rows($res);

				if($anz == 1) {
					$row = $this->CLASS['db']->fetch_assoc($res);
				}

				// email notification
				$this->CLASS['notification']->send_email_notification($_SESSION['cid'],"file","deleted",$row['filename'],$_GET['delfile']);

				$this->CLASS['hooks']->setHook("kr_header","delete_file","end");
			}
		}
	}

	/**
	 * create root
	 */
	function create_root() {
		// rechte checken -> adminrechte
		if($_SESSION['admin'] == 1) {
			$this->CLASS['hooks']->setHook("kr_header","create_root","start");

			// check if alias already exists
			if(isset($_POST['alias']) && $_POST['alias'] != "") {
				$_POST['alias'] = $this->CLASS['knowledgeroot']->checkAlias($_POST['alias']);
			}

			if($_POST['title'] == "") {
				$this->addwarning($this->CLASS['translate']->_('name for root cannot be empty'));
			} else {
				if($this->CLASS['config']->misc->pagealias->use == 1 && (($this->CLASS['config']->misc->pagealias->rights == 2 && $_SESSION['admin'] == 1) || ($this->CLASS['config']->misc->pagealias->rights == 1 && $_SESSION['userid'] != 0) || ($this->CLASS['config']->misc->pagealias->rights == 0))) {
					$val = array(
						"belongs_to" => array(
							"value" => 0,
							"type" => "integer",
						),
						"title" => array(
							"value" => $_POST['title'],
							"type" => "string",
						),
						"alias" => array(
							"value" => $_POST['alias'],
							"type" => "string",
						),
						"owner" => array(
							"value" => $_POST['user'],
							"type" => "integer",
						),
						"group" => array(
							"value" => $_POST['group'],
							"type" => "integer",
						),
						"userrights" => array(
							"value" => $_POST['userrights'],
							"type" => "integer",
						),
						"grouprights" => array(
							"value" => $_POST['grouprights'],
							"type" => "integer",
						),
						"otherrights" => array(
							"value" => $_POST['otherrights'],
							"type" => "integer",
						),
					);

					// check for tooltip
					if($this->CLASS['config']->tree->edittooltiptext == 1 && isset($_POST['tooltip']) && $_POST['tooltip'] != "") {
						$val['tooltip'] = array(
							"value" => $_POST['tooltip'],
							"type" => "string",
						);
					}

					// check for sorting
					if($this->CLASS['config']->tree->order == 1 && isset($_POST['sorting']) && $_POST['sorting'] != "") {
						$val['sorting'] = array(
							"value" => $_POST['sorting'],
							"type" => "integer",
						);
					}

					$res = $this->CLASS['db']->db_insert("tree",$val);
					//$res = $this->CLASS['db']->query("INSERT INTO tree (belongs_to, title, alias, owner, \"group\", userrights, grouprights, otherrights) VALUES (0, '".$_POST['title']."', '".$_POST['alias']."', '".$_POST['user']."', '".$_POST['group']."', '".$_POST['userrights']."', '".$_POST['grouprights']."', '".$_POST['otherrights']."')");
				} else {
					// check for tooltip
					if($this->CLASS['config']->tree->edittooltiptext == 1 && isset($_POST['tooltip']) && $_POST['tooltip'] != "") {
						$res = $this->CLASS['db']->query(sprintf("INSERT INTO tree (belongs_to, title, tooltip, owner, ".$this->CLASS['db']->quoteIdentifier("group").", userrights, grouprights, otherrights) VALUES (0, '%s', '%s', %d, %d, %d, %d, %d)",$_POST['title'],$_POST['tooltip'],$_POST['user'],$_POST['group'],$_POST['userrights'],$_POST['grouprights'],$_POST['otherrights']));
					} else {
						$res = $this->CLASS['db']->query(sprintf("INSERT INTO tree (belongs_to, title, owner, ".$this->CLASS['db']->quoteIdentifier("group").", userrights, grouprights, otherrights) VALUES (0, '%s', %d, %d, %d, %d, %d)",$_POST['title'],$_POST['user'],$_POST['group'],$_POST['userrights'],$_POST['grouprights'],$_POST['otherrights']));
					}
				}

				// get pageid of the created page
				$new_page_id = $this->CLASS['db']->last_id("seq_knowledge");

				// check for subinheritsrights
				if(isset($_POST['subinheritrights']) && $_POST['subinheritrights'] == 1) {
					if(!isset($_POST['subinheritrightseditable']) || $_POST['subinheritrightseditable'] != 1) {
						$_POST['subinheritrightseditable'] = 0;
					}

					$res = $this->CLASS['db']->query(sprintf("UPDATE tree SET subinheritrights=%d, subinheritrightseditable=%d, subinheritowner=%d, subinheritgroup=%d, subinherituserrights=%d, subinheritgrouprights=%d, subinheritotherrights=%d WHERE id=%d",$_POST['subinheritrights'],$_POST['subinheritrightseditable'],$_POST['subinherituser'],$_POST['subinheritgroup'],$_POST['subinherituserrights'],$_POST['subinheritgrouprights'],$_POST['subinheritotherrights'],$new_page_id));

					// save multiple subinheritrights
					$this->CLASS['knowledgeroot']->saveSubinheritMultipleRightsForm("tree", $new_page_id);
				}

				// save multiple rights
				$this->CLASS['knowledgeroot']->saveMultipleRightsForm("tree", $new_page_id);

				// email notification
				$this->CLASS['notification']->send_email_notification($this->CLASS['db']->last_id("seq_knowledge"),"page","created",$_POST['title'],$new_page_id);

				$this->CLASS['hooks']->setHook("kr_header","create_root","success");
			}

			$this->CLASS['hooks']->setHook("kr_header","create_root","end");
		}
	}

	/**
	 * add user
	 */
	function add_user() {
		if($_SESSION['admin'] == 1) {
			$this->CLASS['hooks']->setHook("kr_header","add_user","start");

			$res = $this->CLASS['db']->query(sprintf("INSERT INTO users (name, password, theme, enabled, defaultgroup, defaultrights, admin, rightedit, treecache) VALUES ('%s','%s','%s', %d, %d, '%d', %d, %d, '')",$_POST['name'],md5(addslashes($_POST['password'])),$_POST['theme'],$_POST['enabled'],$_POST['defaultgroup'],$_POST['userrights'].$_POST['grouprights'].$_POST['otherrights'],$_POST['admin'],$_POST['rightedit']));

			$res = $this->CLASS['db']->query(sprintf("SELECT id FROM users WHERE name='%s'",$_POST['name']));
			while($row = $this->CLASS['db']->fetch_assoc($res)) {
				if (isset ($_POST['groups']) and is_array($_POST['groups'])) {
					foreach ($_POST['groups'] as $key => $value) {
						$ressub = $this->CLASS['db']->query(sprintf("INSERT INTO user_group (userid, groupid) VALUES (%d,%d)",$row['id'],$value));
					}
				}
			}

			$_GET['action'] = "users";

			$this->CLASS['hooks']->setHook("kr_header","add_user","end");
		}
	}

	/**
	 * edit user
	 */
	function edit_user() {
		if($_SESSION['admin'] == 1) {
			$this->CLASS['hooks']->setHook("kr_header","edit_user","start");

			//print_r($_POST['groups']);
			if($_POST['password'] == "") {
				$res = $this->CLASS['db']->query(sprintf("UPDATE users SET name='%s', theme='%s', enabled=%d, defaultgroup=%d, defaultrights='%d', admin=%d, rightedit=%d WHERE id=%d",$_POST['name'],$_POST['theme'],$_POST['enabled'],$_POST['defaultgroup'],$_POST['userrights'].$_POST['grouprights'].$_POST['otherrights'],$_POST['admin'],$_POST['rightedit'],$_POST['uid']));
			} else {
				$res = $this->CLASS['db']->query(sprintf("UPDATE users SET name='%s', theme='%s', password='%s', enabled=%d, defaultgroup=%d, defaultrights='%d', admin=%d, rightedit=%d WHERE id=%d",$_POST['name'],$_POST['theme'],md5(addslashes($_POST['password'])),$_POST['enabled'],$_POST['defaultgroup'],$_POST['userrights'].$_POST['grouprights'].$_POST['otherrights'],$_POST['admin'],$_POST['rightedit'],$_POST['uid']));
			}

			if(!isset($_POST['groups']) || !is_array($_POST['groups'])) {
				$_POST['groups'] = array();
			}

			$res = $this->CLASS['db']->query(sprintf("DELETE FROM user_group WHERE userid=%d",$_POST['uid']));
			foreach($_POST['groups'] as $key => $value) {
				$res = $this->CLASS['db']->query(sprintf("INSERT INTO user_group (userid, groupid) VALUES (%d, %d)",$_POST['uid'],$value));
			}

			$_GET['action'] = "users";

			$this->CLASS['hooks']->setHook("kr_header","edit_user","end");
		}
	}

	/**
	 * add group
	 */
	function add_group() {
		if($_SESSION['admin'] == 1) {
			$this->CLASS['hooks']->setHook("kr_header","add_group","start");

			$res = $this->CLASS['db']->query(sprintf("INSERT INTO groups (name,enabled) VALUES ('%s', 1)",$_POST['name']));
			$_GET['action'] = "users";

			$this->CLASS['hooks']->setHook("kr_header","add_group","end");
		}
	}

	/**
	 * edit group
	 */
	function edit_group() {
		if($_SESSION['admin'] == 1) {
			$this->CLASS['hooks']->setHook("kr_header","edit_group","start");

			$res = $this->CLASS['db']->query(sprintf("UPDATE groups SET name='%s' WHERE id=%d",$_POST['name'],$_POST['gid']));
			$_GET['action'] = "users";

			$this->CLASS['hooks']->setHook("kr_header","edit_group","end");
		}
	}

	/**
	 * delete user
	 */
	function delete_user() {
		if($_SESSION['admin'] == 1) {
			$this->CLASS['hooks']->setHook("kr_header","delete_user","start");

			$res = $this->CLASS['db']->query(sprintf("DELETE FROM users WHERE id =%d",$_GET['uid']));
			$res = $this->CLASS['db']->query(sprintf("DELETE FROM user_group WHERE userid =%d",$_GET['uid']));
			$this->addmessage($this->CLASS['translate']->_('User was deleted!'));
			$_GET['action'] = "users";

			$this->CLASS['hooks']->setHook("kr_header","delete_user","end");
		}
	}

	/**
	 * delete group
	 */
	function delete_group() {
		if($_SESSION['admin'] == 1) {
			$this->CLASS['hooks']->setHook("kr_header","delete_group","start");

			// check only in user table for defaultgroup - if a user use this group as defaultgroup then delete fail
			$res = $this->CLASS['db']->query(sprintf("SELECT id FROM users WHERE defaultgroup=%d",$_GET['gid']));
			$anz = $this->CLASS['db']->num_rows($res);

			if($anz == 0) {
				$res = $this->CLASS['db']->query(sprintf("DELETE FROM groups WHERE id =%d",$_GET['gid']));
				$res = $this->CLASS['db']->query(sprintf("DELETE FROM user_group WHERE groupid =%d",$_GET['gid']));
				$this->addmessage($this->CLASS['translate']->_('Group was deleted!'));
				$this->CLASS['hooks']->setHook("kr_header","delete_group","success");
			} else {
				$this->addwarning($this->CLASS['translate']->_('Could not delete group. Group is in use as defaultgroup!'));
				$this->CLASS['hooks']->setHook("kr_header","delete_group","fail");
			}

			$_GET['action'] = "users";

			$this->CLASS['hooks']->setHook("kr_header","delete_group","end");
		}
	}

	/**
	 * edit options
	 */
	function edit_options() {
		$this->CLASS['hooks']->setHook("kr_header","edit_options","start");

		if($_POST['language'] != $_SESSION['language']) {
			$_SESSION['language'] = $_POST['language'];


			// gettext
			Zend_Translate::setCache($this->CLASS['cache']);
			$language = str_replace(".UTF8","", $_POST['language']);
			$this->CLASS['translate'] = new Zend_Translate('gettext', $this->CLASS['config']->base->base_path.'system/language/'.$language.'.UTF8/LC_MESSAGES/knowledgeroot.mo', $language);

			$res = $this->CLASS['db']->query(sprintf("UPDATE users SET language='%s' WHERE id=%d",$_POST['language'],$_SESSION['userid']));
			$this->addmessage($this->CLASS['translate']->_('Language changed.'));
		}

		if($_POST['password'] == $_POST['password1'] && $_POST['password'] != "" && $_SESSION['userid'] != 0) {
			$res = $this->CLASS['db']->query(sprintf("UPDATE users SET password='%s' WHERE id=%d",md5(addslashes($_POST['password'])),$_SESSION['userid']));
			$this->addmessage($this->CLASS['translate']->_('Password changed!'));
		} else {
			if($_POST['password'] != "") {
				$this->addwarning($this->CLASS['translate']->_('Failed to change password!'));
			}
		}

		if($_POST['theme'] != $_SESSION['theme']) {
			$_SESSION['theme'] = $_POST['theme'];
			$res = $this->CLASS['db']->query(sprintf("UPDATE users SET theme='%s' WHERE id=%d",$_POST['theme'],$_SESSION['userid']));
			$this->addmessage($this->CLASS['translate']->_('Theme was changed.'));
		} else {
			//$this->messages .= "<div class=\"redmsg\">".$this->CLASS['language']->get['optionform']['themefailed']."</div>";
		}

		$this->CLASS['hooks']->setHook("kr_header","edit_options","end");
	}

	/**
	 * change language for current session
	 */
	function change_language() {
		if(isset($_POST['language'])) {
			$_SESSION['language'] = $_POST['language'];
			header("Location: index.php");
			exit();
		}
	}

	/**
	 * move page
	 */
	function move_page() {
		if($this->CLASS['knowledgeroot']->getPageRights($_SESSION['cid'],$_SESSION['userid']) == 2 && $this->CLASS['knowledgeroot']->getPageRights($_POST['to'],$_SESSION['userid']) == 2) {
			$this->CLASS['hooks']->setHook("kr_header","move_page","start");

			// check if element is a kind of element to move -> if yes than abort
			if(!$this->CLASS['tree']->isParentelement($_POST['to'],$_SESSION['cid'])) {
				$res = $this->CLASS['db']->query(sprintf("UPDATE tree SET belongs_to=%d WHERE id=%d",$_POST['to'],$_SESSION['cid']));

				$pagename = $this->CLASS['path']->getTreePageTitle($_SESSION['cid']);

				// check for subinheritrights
				if($this->CLASS['config']->misc->subinheritrightsonmove) {
					$inheritrights = $this->CLASS['knowledgeroot']->getInheritRights($_SESSION['cid']);
					if(is_array($inheritrights) && $inheritrights['subinheritrights'] == 1) {
						// delete old multiple rights
						$res = $this->CLASS['db']->query(sprintf("DELETE FROM access WHERE table_name='tree' and belongs_to=%d",$_SESSION['cid']));
	
						// save new multiple rights
						$this->CLASS['knowledgeroot']->saveSubInheritMultipleRightsFrom("tree", $_POST['to'], $_SESSION['cid'], true);
	
						// set owner, group and rights to page
						$res = $this->CLASS['db']->query(sprintf("UPDATE tree SET owner=%d,".$this->CLASS['db']->quoteIdentifier("group")."=%d,userrights=%d,grouprights=%d,otherrights=%d WHERE id=%d",$inheritrights['subinheritowner'],$inheritrights['subinheritgroup'],$inheritrights['subinherituserrights'],$inheritrights['subinheritgrouprights'],$inheritrights['subinheritotherrights'],$_SESSION['cid']));
					}
				}

				// email notification
				$this->CLASS['notification']->send_email_notification($_SESSION['cid'],"page","moved",$pagename,$_SESSION['cid']);

				$this->CLASS['hooks']->setHook("kr_header","move_page","success");
			}

			$this->CLASS['hooks']->setHook("kr_header","move_page","end");
		}
	}

	/**
	 * move content
	 */
	function move_content() {
		if($this->CLASS['knowledgeroot']->getPageRights($_POST['to'],$_SESSION['userid']) == 2 && $this->CLASS['knowledgeroot']->getContentRights($_POST['contentid'],$_SESSION['userid']) == 2 && $_POST['to'] != 0) {
			$this->CLASS['hooks']->setHook("kr_header","move_content","start");

			$res = $this->CLASS['db']->query(sprintf("UPDATE content SET belongs_to=%d WHERE id=%d",$_POST['to'],$_POST['contentid']));

			$pagename = $this->CLASS['path']->getTreePageTitle($_SESSION['cid']);

			// check for subinheritrights
			if($this->CLASS['config']->misc->subinheritrightsonmove) {
				$inheritrights = $this->CLASS['knowledgeroot']->getInheritRights($_POST['to']);
				if(is_array($inheritrights) && $inheritrights['subinheritrights'] == 1) {
					// delete old multiple rights
					$res = $this->CLASS['db']->query(sprintf("DELETE FROM access WHERE table_name='content' and belongs_to=%d",$_POST['contentid']));

					// save new multiple rights
					$this->CLASS['knowledgeroot']->saveSubInheritMultipleRightsFrom("content", $_POST['to'], $_POST['contentid'], true);

					// set owner, group and rights to page
					$res = $this->CLASS['db']->query(sprintf("UPDATE content SET owner=%d,".$this->CLASS['db']->quoteIdentifier("group")."=%d,userrights=%d,grouprights=%d,otherrights=%d WHERE id=%d",$inheritrights['subinheritowner'],$inheritrights['subinheritgroup'],$inheritrights['subinherituserrights'],$inheritrights['subinheritgrouprights'],$inheritrights['subinheritotherrights'],$_SESSION['cid']));
				}
			}

			// email notification
			$this->CLASS['notification']->send_email_notification($_SESSION['cid'],"content","moved",$pagename,$_POST['contentid']);

			$this->CLASS['hooks']->setHook("kr_header","move_content","end");
		}
	}

	/**
	 * open tree element
	 */
	function open_tree_element() {
		$this->CLASS['hooks']->setHook("kr_header","open_tree_element","start");

		//echo "#$#".$_SESSION['open'][$openid]."#";
		if(isset($_SESSION['open'][$_GET['openid']]) && $_SESSION['open'][$_GET['openid']] == 0) {
			$_SESSION['open'][$_GET['openid']] = 1;
		} else {
			$_SESSION['open'][$_GET['openid']] = 0;
		}

		// save treecache
		if(!empty($_SESSION['userid'])) {
			$res = $this->CLASS['db']->query(sprintf("UPDATE users SET treecache='%s' WHERE id=%d",serialize($_SESSION['open']),$_SESSION['userid']));
		}

		$this->CLASS['hooks']->setHook("kr_header","open_tree_element","end");
	}

	/**
	 * show messages
	 */
	function show_messages() {
		$this->CLASS['hooks']->setHook("kr_header","show_messages","start");

		if($this->messages != "") {
			$code = '
			<div class="alert alert-'.$this->messagetype.'" role="alert">
			  '.$this->messages.'
			    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			  </button>
			</div>
			';

			echo $code;
		}

		$this->CLASS['hooks']->setHook("kr_header","show_messages","end");
	}

	/**
	 * addmessage to top
	 * @param string $msg messagetext
	 */
	function addmessage($msg) {
		$this->CLASS['hooks']->setHook("kr_header","addmessage","start");

		$this->messagetype = "success";
		$this->messages .= $msg . "&nbsp;";

		$this->CLASS['hooks']->setHook("kr_header","addmessage","end");
	}

	/**
	 * add warning to top
	 * @param string $msg message
	 */
	function addwarning($msg) {
		$this->CLASS['hooks']->setHook("kr_header","addwarning","start");

		$this->messagetype = "danger";
		$this->messages .= $msg . "&nbsp;";

		$this->CLASS['hooks']->setHook("kr_header","addwarning","end");
	}

	/**
	 * add text to htmlheader
	 * @param string $msg
	 * @param bool $first
	 */
	function addheader($msg, $first = false) {
		$this->CLASS['hooks']->setHook("kr_header","addheader","start");
		if($first)
			$this->htmlheader = $msg . "\n" . $this->htmlheader;
		else
			$this->htmlheader .= $msg . "\n";
		$this->CLASS['hooks']->setHook("kr_header","addheader","end");
	}

	/**
	 * show htmlheader
	 */
	function show_header() {
		$this->CLASS['hooks']->setHook("kr_header","show_header","start");
		echo $this->htmlheader;
		$this->CLASS['hooks']->setHook("kr_header","show_header","end");
	}

	/**
	 * add plain javascriptcode to header
	 * @param string $code
	 */
	function addjs($code="") {
		$this->CLASS['hooks']->setHook("kr_header","addjs","start");
		$this->addheader("\t<script language=\"javascript\" type=\"text/javascript\">\n".$code."\t</script>");
		$this->CLASS['hooks']->setHook("kr_header","addjs","end");
	}

	/**
	 * add source javascript sourcefile to header
	 * @param string $src
	 */
	function addjssrc($src) {
		$this->CLASS['hooks']->setHook("kr_header","addjssrc","start");
		$this->addheader("\t<script src=\"".$src."\" type=\"text/javascript\"></script>");
		$this->CLASS['hooks']->setHook("kr_header","addjssrc","end");
	}

	/**
	 * add css file to header
	 */
	function addcsssrc($src) {
		$this->CLASS['hooks']->setHook("kr_header","addcsssrc","start");
		$this->addheader("<link rel=\"stylesheet\" href=\"". $src ."\" type=\"text/css\" />");
		$this->CLASS['hooks']->setHook("kr_header","addcsssrc","end");
	}

	/**
	 * add title to header
	 */
	function addtitle() {
		$this->CLASS['hooks']->setHook("kr_header","addtitle","start");
		$this->addheader("<title>".$this->CLASS['config']->base->title."</title>");
		$this->CLASS['hooks']->setHook("kr_header","addtitle","end");
	}

	/**
	* moves content up
	*/
	function move_content_up() {
		$contentid = $_GET['moveup'];
		$pageid = $_SESSION['cid'];

		if($contentid == "") {
			return 0;
		}

		// user can only move if he have pagerights
		if($this->CLASS['knowledgeroot']->getPageRights($_SESSION['cid'],$_SESSION['userid']) == 2) {
			$this->CLASS['hooks']->setHook("kr_header","move_content_up","start");

			$res = $this->CLASS['db']->query(sprintf("SELECT id, sorting FROM content WHERE id=%d",$contentid));
			$count = $this->CLASS['db']->num_rows($res);

			if($count == 1) {
				$row = $this->CLASS['db']->fetch_assoc($res);

				if($row['sorting'] == 0 || $row['sorting'] == null) {
					//$res = $this->CLASS['db']->query("UPDATE content SET sorting=0 WHERE sorting IS NULL AND deleted=0");
					$res = $this->CLASS['db']->query(sprintf("UPDATE content SET sorting=sorting+1 WHERE belongs_to=%d AND id<>%d AND deleted=0",$pageid,$contentid));

					$this->addmessage($this->CLASS['translate']->_('content moved'));
				} else {
					$res = $this->CLASS['db']->query(sprintf("SELECT id, max(sorting) as sorting FROM content WHERE belongs_to=%d AND sorting<=%d AND id<>%d AND deleted=0 GROUP BY id ORDER BY sorting DESC LIMIT 1",$pageid,$row['sorting'],$contentid));
					$count = $this->CLASS['db']->num_rows($res);

					if($count == 1) {
						$rowelement = $this->CLASS['db']->fetch_assoc($res);

						if($row['sorting'] == $rowelement['sorting']) {
							//$res = $this->CLASS['db']->query("UPDATE content SET sorting=0 WHERE sorting IS NULL AND deleted=0");
							$res = $this->CLASS['db']->query(sprintf("UPDATE content SET sorting=sorting+1 WHERE belongs_to=%d AND id<>%d AND sorting>=%d AND deleted=0",$pageid,$contentid,$row['sorting']));
						} else {
							$res = $this->CLASS['db']->query(sprintf("UPDATE content SET sorting=%d WHERE id=%d",$row['sorting'],$rowelement['id']));
							$res = $this->CLASS['db']->query(sprintf("UPDATE content SET sorting=%d WHERE id=%d",$rowelement['sorting'],$row['id']));
						}

						$this->addmessage($this->CLASS['translate']->_('content moved'));
						$this->CLASS['hooks']->setHook("kr_header","move_content_up","success");
					} // else - do nothing
				}
			}

			$this->CLASS['hooks']->setHook("kr_header","move_content_up","end");
		}
	}

	/**
	 * move content down on page
	 */
	function move_content_down() {
		$contentid = $_GET['movedown'];
		$pageid = $_SESSION['cid'];

		if($contentid == "") {
			return 0;
		}

		// user can only move if he have pagerights
		if($this->CLASS['knowledgeroot']->getPageRights($_SESSION['cid'],$_SESSION['userid']) == 2) {
			$this->CLASS['hooks']->setHook("kr_header","move_content_down","start");

			$res = $this->CLASS['db']->query(sprintf("SELECT id, sorting FROM content WHERE id=%d",$contentid));
			$count = $this->CLASS['db']->num_rows($res);

			if($count == 1) {
				$row = $this->CLASS['db']->fetch_assoc($res);

				// needed if fields have content "null"
				//$res = $this->CLASS['db']->query("UPDATE content SET sorting=0 WHERE sorting IS NULL AND deleted=0");

				$res = $this->CLASS['db']->query(sprintf("SELECT id, min(sorting) as sorting FROM content WHERE belongs_to=%d AND sorting>=%d AND id<>%d AND deleted=0 GROUP BY id ORDER BY sorting ASC LIMIT 1",$pageid,$row['sorting'],$contentid));
				$count = $this->CLASS['db']->num_rows($res);

				if($count == 1) {
					$rowelement = $this->CLASS['db']->fetch_assoc($res);

					if($row['sorting'] == $rowelement['sorting']) {
						$res = $this->CLASS['db']->query(sprintf("UPDATE content SET sorting=sorting+1 WHERE belongs_to=%d AND id<>%d AND sorting>=%d AND deleted=0",$pageid,$contentid,$row['sorting']));
					} else {
						$res = $this->CLASS['db']->query(sprintf("UPDATE content SET sorting=%d WHERE id=%d",$row['sorting'],$rowelement['id']));
						$res = $this->CLASS['db']->query(sprintf("UPDATE content SET sorting=%d WHERE id=%d",$rowelement['sorting'],$row['id']));
					}

					$this->addmessage($this->CLASS['translate']->_('content moved'));
					$this->CLASS['hooks']->setHook("kr_header","move_content_down","success");
				}
			}

			$this->CLASS['hooks']->setHook("kr_header","move_content_down","end");
		}
	}

	/**
	 * move content position on the same page
	 */
	function move_content_position() {
		$contentid = $_POST['contentid'];
		$targetcontentid = $_POST['targetcontentid'];
		$pageid = $_POST['page'];
		$position = $_POST['position'];

		$this->_move_content_position($contentid, $targetcontentid, $pageid, $position);
	}

	/**
	 * move content position on the same page
	 */
	function _move_content_position($contentid, $targetcontentid, $pageid, $position, $showMessage = true) {
		if($contentid == "" || $targetcontentid == "" || $pageid == "" || $position == "") {
			return 0;
		}

		// user can only move if he have pagerights
		if($this->CLASS['knowledgeroot']->getPageRights($pageid,$_SESSION['userid']) == 2) {
			$this->CLASS['hooks']->setHook("kr_header","move_content_position","start");

			$res = $this->CLASS['db']->query(sprintf("SELECT id, sorting FROM content WHERE id=%d",$targetcontentid));
			$count = $this->CLASS['db']->num_rows($res);

			if($count == 1) {
				$row = $this->CLASS['db']->fetch_assoc($res);

				if($position == 'before') {
					$res = $this->CLASS['db']->squery("UPDATE content SET sorting=sorting+1 WHERE belongs_to=%d AND sorting>=%d AND deleted=0",$pageid, $row['sorting']);
					$res = $this->CLASS['db']->squery("UPDATE content SET sorting=%d WHERE id=%d", $row['sorting'], $contentid);
				} else {
					$res = $this->CLASS['db']->squery("UPDATE content SET sorting=sorting+2 WHERE belongs_to=%d AND sorting>%d AND deleted=0",$pageid, $row['sorting']);
					$res = $this->CLASS['db']->squery("UPDATE content SET sorting=%d WHERE id=%d", $row['sorting']+1, $contentid);
				}

				if($showMessage) $this->addmessage($this->CLASS['translate']->_('content moved'));
				$this->CLASS['hooks']->setHook("kr_header","move_content_position","success");
			}

			$this->CLASS['hooks']->setHook("kr_header","move_content_position","end");
		}
	}

	/**
	 * This function will add the searchwords to your session and will redirect you to the search
	 */
	function create_search() {
		$this->CLASS['hooks']->setHook("kr_content","create_search","start");

		// if user search for special content the searchword is #[0-9]+
		if(preg_match('/#([0-9]+)/', $_POST['search'], $match)) {
			$res = $this->CLASS['db']->query(sprintf('SELECT belongs_to FROM content WHERE id=%d',$match[1]));
			$cnt = $this->CLASS['db']->num_rows($res);

			if($cnt == 1) {
				$row = $this->CLASS['db']->fetch_assoc($res);
				header('Location: index.php?id='.$row['belongs_to'].'#'.$match[1]);
				exit();
			}
		}

		// generete uniqu key for search
		$sum = md5($_POST['search']);

		// save searchword to key in the session
		$_SESSION['search'][$sum] = $_POST['search'];

		$this->CLASS['hooks']->setHook("kr_content","create_search","end");

		// redirect to show search
		header("Location: index.php?action=showsearch&key=".$sum."");
		exit();
	}

	/**
	 * return baseurl
	 * @return string
	 */
	function get_base_url() {
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
			$scheme = "https://";
		} else {
			$scheme = "http://";
		}

		$baseURL = $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
                $pos = strpos( $baseURL, "index.php" );
                if($pos == 0) {
                        $pos = strpos( $baseURL, "ajax-xml.php" );
                }

                return substr( $baseURL, 0, $pos );
	}

	/**
	 * This functions checks if a content should be shown directly to display it!
	 */
	function getIDFromContent() {
		if(isset($_GET['contentid']) && $_GET['contentid'] != "") {
			$mycontentrights = $this->CLASS['knowledgeroot']->getContentRights($_GET['contentid'],$_SESSION['userid']);

			if($mycontentrights >= 1) {
				$res = $this->CLASS['db']->query(sprintf("SELECT id,belongs_to FROM content WHERE id=%d",$_GET['contentid']));
				$anz = $this->CLASS['db']->num_rows($res);

				if($anz == 1) {
					$row = $this->CLASS['db']->fetch_assoc($res);
					$_GET['id'] = $row['belongs_to'];
				}
			}
		}
	}

	/**
	 * get ID from the alias in url
	 */
	function getIDFromAlias() {
		if(preg_match("/.*\/([a-zA-Z0-9]+)\.html.*/",$this->getIndpEnv("TYPO3_SITE_SCRIPT"),$found)) {
			if($_SERVER["REQUEST_METHOD"] == "GET") {
				$_SESSION['cid'] = "";
				$_GET['id'] = $this->getAliasID($found[1]);
			}

			if($_SERVER["REQUEST_METHOD"] == "POST") {
				$_SESSION['cid'] = "";
				$_POST['id'] = $this->getAliasID($found[1]);
			}
		}
	}

	/**
	 * get id for alias
	 */
	function getAliasID($alias) {
		if($alias != "") {
			$res = $this->CLASS['db']->query(sprintf("SELECT id FROM tree WHERE alias='%s' AND deleted=0",$alias));
			$anz = $this->CLASS['db']->num_rows($res);

			if($anz == 1) {
				$row = $this->CLASS['db']->fetch_assoc($res);
				return $row['id'];
			}
		}

		return "";
	}

	/**
	 * FROM TYPO3 - class.tslib_fe.php
	 * Abstraction method which returns System Environment Variables regardless of server OS, CGI/MODULE version etc. Basically this is SERVER variables for most of them.
	 * This should be used instead of getEnv() and $_SERVER/ENV_VARS to get reliable values for all situations.
	 * Usage: 221
	 *
	 * @param  string    Name of the "environment variable"/"server variable" you wish to use. Valid values are SCRIPT_NAME, SCRIPT_FILENAME, REQUEST_URI, PATH_INFO, REMOTE_ADDR, REMOTE_HOST, HTTP_REFERER, HTTP_HOST, HTTP_USER_AGENT, HTTP_ACCEPT_LANGUAGE, QUERY_STRING, TYPO3_DOCUMENT_ROOT, TYPO3_HOST_ONLY, TYPO3_HOST_ONLY, TYPO3_REQUEST_HOST, TYPO3_REQUEST_URL, TYPO3_REQUEST_SCRIPT, TYPO3_REQUEST_DIR, TYPO3_SITE_URL, _ARRAY
	 * @return  string    Value based on the input key, independent of server/os environment.
	 */
	function getIndpEnv($getEnvName)  {
		/*
		Conventions:
		output from parse_url():
		URL:  http://username:password@192.168.1.4:8080/typo3/32/temp/phpcheck/index.php/arg1/arg2/arg3/?arg1,arg2,arg3&p1=parameter1&p2[key]=value#link1
			[scheme] => 'http'
			[user] => 'username'
			[pass] => 'password'
			[host] => '192.168.1.4'
			[port] => '8080'
			[path] => '/typo3/32/temp/phpcheck/index.php/arg1/arg2/arg3/'
			[query] => 'arg1,arg2,arg3&p1=parameter1&p2[key]=value'
			[fragment] => 'link1'

			Further definition: [path_script] = '/typo3/32/temp/phpcheck/index.php'
				[path_dir] = '/typo3/32/temp/phpcheck/'
				[path_info] = '/arg1/arg2/arg3/'
				[path] = [path_script/path_dir][path_info]


		Keys supported:

		URI______:
			REQUEST_URI    =  [path]?[query]    = /typo3/32/temp/phpcheck/index.php/arg1/arg2/arg3/?arg1,arg2,arg3&p1=parameter1&p2[key]=value
			HTTP_HOST    =  [host][:[port]]    = 192.168.1.4:8080
			SCRIPT_NAME    =  [path_script]++    = /typo3/32/temp/phpcheck/index.php    // NOTICE THAT SCRIPT_NAME will return the php-script name ALSO. [path_script] may not do that (eg. '/somedir/' may result in SCRIPT_NAME '/somedir/index.php')!
			PATH_INFO    =  [path_info]      = /arg1/arg2/arg3/
			QUERY_STRING  =  [query]        = arg1,arg2,arg3&p1=parameter1&p2[key]=value
			HTTP_REFERER  =  [scheme]://[host][:[port]][path]  = http://192.168.1.4:8080/typo3/32/temp/phpcheck/index.php/arg1/arg2/arg3/?arg1,arg2,arg3&p1=parameter1&p2[key]=value
				(Notice: NO username/password + NO fragment)

		CLIENT____:
			REMOTE_ADDR    =  (client IP)
			REMOTE_HOST    =  (client host)
			HTTP_USER_AGENT  =  (client user agent)
			HTTP_ACCEPT_LANGUAGE  = (client accept language)

		SERVER____:
			SCRIPT_FILENAME  =  Absolute filename of script    (Differs between windows/unix). On windows 'C:\\blabla\\blabl\\' will be converted to 'C:/blabla/blabl/'

		Special extras:
			TYPO3_HOST_ONLY  =    [host]      = 192.168.1.4
			TYPO3_PORT    =    [port]      = 8080 (blank if 80, taken from host value)
			TYPO3_REQUEST_HOST =   [scheme]://[host][:[port]]
			TYPO3_REQUEST_URL =    [scheme]://[host][:[port]][path]?[query]  (sheme will by default be 'http' until we can detect if it's https -
			TYPO3_REQUEST_SCRIPT =  [scheme]://[host][:[port]][path_script]
			TYPO3_REQUEST_DIR =    [scheme]://[host][:[port]][path_dir]
			TYPO3_SITE_URL =     [scheme]://[host][:[port]][path_dir] of the TYPO3 website frontend
			TYPO3_SITE_SCRIPT =   [script / Speaking URL] of the TYPO3 website
			TYPO3_DOCUMENT_ROOT  =  Absolute path of root of documents:  TYPO3_DOCUMENT_ROOT.SCRIPT_NAME = SCRIPT_FILENAME (typically)

		Notice: [fragment] is apparently NEVER available to the script!


		Testing suggestions:
		- Output all the values.
		- In the script, make a link to the script it self, maybe add some parameters and click the link a few times so HTTP_REFERER is seen
		- ALSO TRY the script from the ROOT of a site (like 'http://www.mytest.com/' and not 'http://www.mytest.com/test/' !!)

		*/

		//    if ($getEnvName=='HTTP_REFERER')  return '';
		switch((string)$getEnvName)  {
			case 'SCRIPT_NAME':
				return (php_sapi_name()=='cgi'||php_sapi_name()=='cgi-fcgi')&&($_SERVER['ORIG_PATH_INFO']?$_SERVER['ORIG_PATH_INFO']:$_SERVER['PATH_INFO']) ? ($_SERVER['ORIG_PATH_INFO']?$_SERVER['ORIG_PATH_INFO']:$_SERVER['PATH_INFO']) : ($_SERVER['ORIG_SCRIPT_NAME']?$_SERVER['ORIG_SCRIPT_NAME']:$_SERVER['SCRIPT_NAME']);
			break;
			case 'SCRIPT_FILENAME':
				return str_replace('//','/', str_replace('\\','/', (php_sapi_name()=='cgi'||php_sapi_name()=='isapi' ||php_sapi_name()=='cgi-fcgi')&&($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED'])? ($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED']):($_SERVER['ORIG_SCRIPT_FILENAME']?$_SERVER['ORIG_SCRIPT_FILENAME']:$_SERVER['SCRIPT_FILENAME'])));
			break;
			case 'REQUEST_URI':
				// Typical application of REQUEST_URI is return urls, forms submitting to itself etc. Example: returnUrl='.rawurlencode($this->getIndpEnv('REQUEST_URI'))
				if (!$_SERVER['REQUEST_URI'])  {  // This is for ISS/CGI which does not have the REQUEST_URI available.
					return '/'.ereg_replace('^/','',$this->getIndpEnv('SCRIPT_NAME')).
					($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:'');
				} else return $_SERVER['REQUEST_URI'];
			break;
			case 'PATH_INFO':
				// $_SERVER['PATH_INFO']!=$_SERVER['SCRIPT_NAME'] is necessary because some servers (Windows/CGI) are seen to set PATH_INFO equal to script_name
				// Further, there must be at least one '/' in the path - else the PATH_INFO value does not make sense.
				// IF 'PATH_INFO' never works for our purpose in TYPO3 with CGI-servers, then 'php_sapi_name()=='cgi'' might be a better check. Right now strcmp($_SERVER['PATH_INFO'],$this->getIndpEnv('SCRIPT_NAME')) will always return false for CGI-versions, but that is only as long as SCRIPT_NAME is set equal to PATH_INFO because of php_sapi_name()=='cgi' (see above)
			//        if (strcmp($_SERVER['PATH_INFO'],$this->getIndpEnv('SCRIPT_NAME')) && count(explode('/',$_SERVER['PATH_INFO']))>1)  {
				if (php_sapi_name()!='cgi'&&php_sapi_name()!='cgi-fcgi')  {
					return $_SERVER['PATH_INFO'];
				} else return '';
			break;
				// These are let through without modification
			case 'REMOTE_ADDR':
			case 'REMOTE_HOST':
			case 'HTTP_REFERER':
			case 'HTTP_HOST':
			case 'HTTP_USER_AGENT':
			case 'HTTP_ACCEPT_LANGUAGE':
			case 'QUERY_STRING':
				return $_SERVER[$getEnvName];
			break;
			case 'TYPO3_DOCUMENT_ROOT':
				// Some CGI-versions (LA13CGI) and mod-rewrite rules on MODULE versions will deliver a 'wrong' DOCUMENT_ROOT (according to our description). Further various aliases/mod_rewrite rules can disturb this as well.
				// Therefore the DOCUMENT_ROOT is now always calculated as the SCRIPT_FILENAME minus the end part shared with SCRIPT_NAME.
				$SFN = $this->getIndpEnv('SCRIPT_FILENAME');
				$SN_A = explode('/',strrev($this->getIndpEnv('SCRIPT_NAME')));
				$SFN_A = explode('/',strrev($SFN));
				$acc = array();
				while(list($kk,$vv)=each($SN_A))  {
					if (!strcmp($SFN_A[$kk],$vv))  {
						$acc[] = $vv;
					} else break;
				}
				$commonEnd=strrev(implode('/',$acc));
				if (strcmp($commonEnd,''))  { $DR = substr($SFN,0,-(strlen($commonEnd)+1)); }
				return $DR;
			break;
			case 'TYPO3_HOST_ONLY':
				$p = explode(':',$_SERVER['HTTP_HOST']);
				return $p[0];
			break;
			case 'TYPO3_PORT':
				$p = explode(':',$_SERVER['HTTP_HOST']);
				return $p[1];
			break;
			case 'TYPO3_REQUEST_HOST':
				return ($this->getIndpEnv('TYPO3_SSL') ? 'https://' : 'http://').
				$_SERVER['HTTP_HOST'];
			break;
			case 'TYPO3_REQUEST_URL':
				return $this->getIndpEnv('TYPO3_REQUEST_HOST').$this->getIndpEnv('REQUEST_URI');
			break;
			case 'TYPO3_REQUEST_SCRIPT':
				return $this->getIndpEnv('TYPO3_REQUEST_HOST').$this->getIndpEnv('SCRIPT_NAME');
			break;
			case 'TYPO3_REQUEST_DIR':
				return $this->getIndpEnv('TYPO3_REQUEST_HOST').$this->dirname($this->getIndpEnv('SCRIPT_NAME')).'/';
			break;
			case 'TYPO3_SITE_URL':
				if (defined('PATH_thisScript') && defined('PATH_site'))  {
					$lPath = substr(dirname(PATH_thisScript),strlen(PATH_site)).'/';
					$url = $this->getIndpEnv('TYPO3_REQUEST_DIR');
					$siteUrl = substr($url,0,-strlen($lPath));
					if (substr($siteUrl,-1)!='/')  $siteUrl.='/';
					return $siteUrl;
				} else return '';
			break;
			case 'TYPO3_SITE_SCRIPT':
				return substr($this->getIndpEnv('TYPO3_REQUEST_URL'),strlen($this->getIndpEnv('TYPO3_SITE_URL')));
			break;
			case 'TYPO3_SSL':
				return (isset ($_SERVER['SSL_SESSION_ID']) and $_SERVER['SSL_SESSION_ID']) || (isset ($_SERVER['HTTPS']) and !strcmp($_SERVER['HTTPS'],'on')) ? TRUE : FALSE;
			break;
			case '_ARRAY':
				$out = array();
				// Here, list ALL possible keys to this function for debug display.
				$envTestVars = explode(',','
				HTTP_HOST,
				TYPO3_HOST_ONLY,
				TYPO3_PORT,
				PATH_INFO,
				QUERY_STRING,
				REQUEST_URI,
				HTTP_REFERER,
				TYPO3_REQUEST_HOST,
				TYPO3_REQUEST_URL,
				TYPO3_REQUEST_SCRIPT,
				TYPO3_REQUEST_DIR,
				TYPO3_SITE_URL,
				TYPO3_SITE_SCRIPT,
				TYPO3_SSL,
				SCRIPT_NAME,
				TYPO3_DOCUMENT_ROOT,
				SCRIPT_FILENAME,
				REMOTE_ADDR,
				REMOTE_HOST,
				HTTP_USER_AGENT,
				HTTP_ACCEPT_LANGUAGE');
				reset($envTestVars);
				while(list(,$v)=each($envTestVars))  {
					$out[trim($v)]=$this->getIndpEnv(trim($v));
				}
				reset($out);
				return $out;
			break;
		}
	}

	/**
	 * FROM TYPO3
	 * Returns the directory part of a path without trailing slash
	 * If there is no dir-part, then an empty string is returned.
	 * Behaviour:
	 *
	 * '/dir1/dir2/script.php' => '/dir1/dir2'
	 * '/dir1/' => '/dir1'
	 * 'dir1/script.php' => 'dir1'
	 * 'd/script.php' => 'd'
	 * '/script.php' => ''
	 * '' => ''
	 * Usage: 5
	 *
	 * @param  string    Directory name / path
	 * @return  string    Processed input value. See function description.
	 */
	function dirname($path)  {
		$p=$this->revExplode('/',$path,2);
		return count($p)==2?$p[0]:'';
	}

	/**
	 * FROM TYPO3
	 * Reverse explode which explodes the string counting from behind.
	 * Thus t3lib_div::revExplode(':','my:words:here',2) will return array('my:words','here')
	 * Usage: 8
	 *
	 * @param  string    Delimiter string to explode with
	 * @param  string    The string to explode
	 * @param  integer    Number of array entries
	 * @return  array    Exploded values
	 */
	function revExplode($delim, $string, $count=0)  {
		$temp = explode($delim,strrev($string),$count);
		while(list($key,$val)=each($temp))  {
			$temp[$key]=strrev($val);
		}
		$temp=array_reverse($temp);
		reset($temp);
		return $temp;
	}

	/**
	 * FROM TYPO3
	 * Splits a reference to a file in 5 parts
	 * Usage: 43
	 *
	 * @param  string    Filename/filepath to be analysed
	 * @return  array    Contains keys [path], [file], [filebody], [fileext], [realFileext]
	 */
	function split_fileref($fileref)  {
		if (  ereg('(.*/)(.*)$',$fileref,$reg)  )  {
			$info['path'] = $reg[1];
			$info['file'] = $reg[2];
		} else {
			$info['path'] = '';
			$info['file'] = $fileref;
		}
		$reg='';
		if (  ereg('(.*)\.([^\.]*$)',$info['file'],$reg)  )  {
			$info['filebody'] = $reg[1];
			$info['fileext'] = strtolower($reg[2]);
			$info['realFileext'] = $reg[2];
		} else {
			$info['filebody'] = $info['file'];
			$info['fileext'] = '';
		}
		reset($info);
		return $info;
	}
}

?>
