<?php
/**
 * This Class is a base class for extensions
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-extension-base.php 860 2009-09-25 12:15:19Z lordlamer $
 */
class extension_base {
	// global class array
	var $CLASS = array();

	// var for options from extension/config.php
	var $CONF = array();

	// show addfiles for this extension
	// default is disabled
	var $show_addedfiles = 0;

	// array for the menu items
	var $menu = array();

	// array with langtokens for this extension
	var $getLang = array();

	// array with post data for this extension - index.php?extname[varname]=test - so varname will be available in this GET
	var $GET = array();

	// array with post data for this extension - index.php?extname[varname]=test - so varname will be available in this POST
	var $POST = array();

	// Path to the extension (relativ)
	var $myPath = "";

	// Path in system for extension (absolute)
	var $myAbsolutePath = "";

	/**
	 * init/start class
	 */
	function __contruct(&$CLASS) {
		$this->CLASS =& $CLASS;

		// load GET and POST
		if(isset($_GET[get_class($this)])) $this->GET = $_GET[get_class($this)];
		if(isset($_POST[get_class($this)])) $this->GET = $_POST[get_class($this)];

		return 0;
	}

	/**
	 *
	 */
	function extension_base(&$CLASS) {
		$this->CLASS =& $CLASS;

		// load GET and POST
		if(isset($_GET[get_class($this)])) $this->GET = $_GET[get_class($this)];
		if(isset($_POST[get_class($this)])) $this->GET = $_POST[get_class($this)];

		return 0;
	}

	/**
	 * default functon for start
	 */
	function main() {
		return "";
	}

	/**
	 * translate msgid
	 * @param string $msgid
	 * @return string
	 */
	function _($msgid)
	{
		return $this->CLASS['translate']->_($msgid);
	}

	/**
	 * default function do display content
	 */
	function show_content($id = "") {
		return "";
	}

	/**
	 * display textbox and load rte if possible
	 */
	function getTextbox($value = "", $use_rte = TRUE) {
		$out = "";

		if ($use_rte == TRUE) {
			$out .= $this->CLASS['rte']->show($value);
		} else {
			$out .= "<textarea name=\"content\" cols=\"50\" rows=\"8\">\n";
			$out .= $value;
			$out .= "</textarea>\n";
		}

		return $out;
	}

	/**
	 * display rightpanel if possible
	 */
	function getRightpanel($user=0,$group=0,$userrights=0,$grouprights=0,$otherrights=0) {
		$panel = "";

		if(!empty($_SESSION['userid'])) {
			$panel = $this->CLASS['knowledgeroot']->editRightPanel($user,$group,$userrights,$grouprights,$otherrights);
		}

		return $panel;
	}

	/**
	 * get rightpanel by contentid
	 */
	function getRightpanelByID($contentid) {
		$panel = "";

		if(!empty($_SESSION['userid'])) {
			$res = $this->CLASS['db']->query(sprintf("SELECT * FROM content WHERE id=%d AND deleted=0",$contentid));
			$anz = $this->CLASS['db']->num_rows($res);

			if($anz == 1) {
				$row = $this->CLASS['db']->fetch_assoc($res);

				$panel = $this->CLASS['knowledgeroot']->editRightPanel($row['owner'],$row['group'],$row['userrights'],$row['grouprights'],$row['otherrights']);
			}
		}

		return $panel;
	}

	/**
	 * display users default right panel
	 * used for creation of new content
	 */
	function getDefaultRightpanel() {
		return $this->CLASS['knowledgeroot']->rightpanel($_SESSION['userid']);
	}
}
?>
