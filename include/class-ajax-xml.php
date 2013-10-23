<?php
/**
 * class for ajax functions that return xml
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-ajax-xml.php 903 2009-12-13 12:21:29Z lordlamer $
 */
class ajax_xml {
	/**
	 * array $CLASS global array with classes
	 */
	var $CLASS = array();

	/**
	 * string $xmlcode var with xmlcode to return
	 */
	var $xmlcode = "";

	/**
	 * init/start class
	 * @param array &$CLASS reference to global array with classes
	 */
	function start(&$CLASS) {
		$this->CLASS =& $CLASS;
	}

	/**
	 * check what to do
	 */
	function check_vars() {
		//check if userid and groupid is set, if not set to 0
		if($_SESSION['userid'] == "" || $_SESSION['groupid'] == "") {
			$_SESSION['userid'] = 0;
			$_SESSION['groupid'] = 0;
		}

		// try to work only with POST
		if (isset ($_POST['ajaxopen']) and $_POST['ajaxopen'] != "") {
			$this->tree_open($_POST['ajaxopen']);
		} elseif (isset ($_POST['ajaxclose']) and $_POST['ajaxclose'] != "") {
			$this->tree_close($_POST['ajaxclose']);
		} elseif (isset ($_POST['reloadtree']) and $_POST['reloadtree'] != "") {
			$this->tree_reload();
		} elseif (isset ($_POST['expandtree']) and $_POST['expandtree'] != "") {
			$this->tree_expand();
		} elseif (isset ($_POST['collapsetree']) and $_POST['collapsetree'] != "") {
			$this->tree_collapse();
		} elseif (isset ($_POST['ajaxmenu']) and isset ($_POST['id']) and $_POST['ajaxmenu'] != "" and $_POST['id'] != "") {
			if(isset($_POST['contentid']) && $_POST['contentid'] != "") {
				$this->tree_contextmenu($_POST['ajaxmenu'], $_POST['id'], $_POST['contentid']);
			} else {
				$this->tree_contextmenu($_POST['ajaxmenu'], $_POST['id']);
			}
		} elseif (isset($_POST['action']) && $_POST['action'] == "ajaxmove" && isset($_POST['source']) && $_POST['source'] != "" && isset($_POST['destination']) && $_POST['destination'] != "") {
			$this->tree_move($_POST['source'], $_POST['destination']);
		} elseif (isset($_POST['action']) && $_POST['action'] == "ajaxmovecontent" && isset($_POST['source']) && $_POST['source'] != "" && isset($_POST['destination']) && $_POST['destination'] != "") {
			$this->tree_move_content($_POST['source'], $_POST['destination']);
		}
	}

	/**
	 * generate xml to open tree part
	 * @param integer $id id of element to open
	 */
	function tree_open($id) {
		// get all elements to open
		$elements = $this->getOpenTreeElements($id);

		// generate xmloutput
		$this->xmlcode = '<?xml version="1.0" ?>' . "\n";
		$this->xmlcode .= "<root>\n";
		$this->xmlcode .= "\t<parentid>".$id."</parentid>\n";
		$this->xmlcode .= "\t<html>\n";
		$this->xmlcode .= "<![CDATA[\n";
		//$this->xmlcode .= htmlentities($elements);
		$this->xmlcode .= $elements;
		$this->xmlcode .= "]]>\n";
		$this->xmlcode .= "\t</html>\n";
		$this->xmlcode .= "</root>\n";

		// save tree status to session and to db
		$_SESSION['open'][$id] = 1;

		if(!empty($_SESSION['userid'])) {
			$res = $this->CLASS['db']->query(sprintf("UPDATE users SET treecache='%s' WHERE id=%d",serialize($_SESSION['open']),$_SESSION['userid']));
		}
	}

	/**
	 * generate xml to close treepart
	 * @param integer $id id of element to close
	 */
	function tree_close($id) {
		// get all elements to close
		$elements = $this->getCloseTreeElements($id);

		// generate xmloutput
		$this->xmlcode = '<?xml version="1.0" ?>' . "\n";
		$this->xmlcode .= "<root>\n";
		$this->xmlcode .= $elements;
		$this->xmlcode .= "</root>\n";

		// save tree status to session and to db
		$_SESSION['open'][$id] = 0;

		if(!empty($_SESSION['userid'])) {
			$res = $this->CLASS['db']->query(sprintf("UPDATE users SET treecache='%s' WHERE id=%d",serialize($_SESSION['open']),$_SESSION['userid']));
		}
	}

	/**
	 * returns elements to close of an treeparentelement
	 * @param integer $id id of element
	 * @return string return part of xml string
	 */
	function getCloseTreeElements($id) {
		$line = "";

		// check if the user have permissions
		if($this->CLASS['knowledgeroot']->checkRecursivPerm($id, $_SESSION['userid']) != 0) {
			//if(isset($_SESSION['admin']) and $_SESSION['admin'] == 1) {
			// enable all because of multiple rights
			if(1) {
				$query = sprintf("SELECT id FROM tree WHERE belongs_to=%d AND deleted=0 ORDER BY title ASC",$id);
			} else {
				// get groups from user
				$res = $this->CLASS['db']->query(sprintf("SELECT groupid FROM user_group WHERE userid=%d",$_SESSION['userid']));
				$orclause = "";
				while($rowuser = $this->CLASS['db']->fetch_assoc($res)) {
					$orclause .= sprintf("OR (".$this->CLASS['db']->quoteIdentifier("group")."=%d  AND grouprights > 0) ",$rowuser['groupid']);
				}

				$query = sprintf("SELECT id FROM tree WHERE belongs_to=%d AND deleted=0 AND ((otherrights > 0) OR (".$this->CLASS['db']->quoteIdentifier("group")."=%d AND grouprights > 0) %sOR (owner=%d AND userrights>0)) ORDER BY title ASC",$id,$_SESSION['groupid'],$orclause,$_SESSION['userid']);
			}

			$line = "";
			$line .= $this->getCloseContentElements($id);

			// get all elements to close in tree
			$res = $this->CLASS['db']->query($query);
			while($row = $this->CLASS['db']->fetch_assoc($res)) {
				if($this->CLASS['knowledgeroot']->checkRecursivPerm($row['id'],$_SESSION['userid']) > 0) {
					$line .= "<element>".$row['id']."</element>\n";

					// get content elements to close
					$line .= $this->getCloseContentElements($row['id']);

					// try to get childs of this element
					if(isset($_SESSION['open'][$row['id']]) && $_SESSION['open'][$row['id']] == 1) {
						$line .= $this->getCloseTreeElements($row['id']);
					}
				}
			}
		}

		return $line;
	}

	/**
	 * return elements that should be closed
	 * @param integer $pageid id of element
	 * @return string return part of xml
	 */
	function getCloseContentElements($pageid) {
		$out = "";

		// select the content in table content with userrights
		if (isset ($_SESSION['admin']) and $_SESSION['admin'] == 1) {
			if($this->CLASS['db']->dbtype == "pgsql") {
				$query = sprintf("SELECT ct.id as id, ct.content as content, ct.title as title, ct.type as type, u.name as lastupdatedby, to_char(ct.lastupdated,'DD. Mon YYYY HH24:MI:SS') as lastupdated FROM content ct left outer join users u ON ct.lastupdatedby = u.id WHERE belongs_to=%d AND ct.deleted=0 ORDER BY ct.sorting ASC",$pageid);
			} elseif($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") {
				$query = sprintf("SELECT ct.id as id, ct.content as content, ct.title as title, ct.type as type, u.name as lastupdatedby, strftime('%%d.%%m.%%Y %%H:%%M:%%S',ct.lastupdated) as lastupdated FROM content ct left outer join users u ON ct.lastupdatedby = u.id WHERE belongs_to=%d AND ct.deleted=0 ORDER BY ct.sorting ASC",$pageid);
			} else {
				$query = "SELECT ct.id AS id, ct.content AS content, ct.title AS title, ct.type AS type, u.name AS lastupdatedby, DATE_FORMAT(ct.lastupdated,'%d. %M %Y %H:%i:%s') as lastupdated FROM content ct";
				$query .= sprintf(" LEFT OUTER JOIN users u ON ct.lastupdatedby = u.id WHERE belongs_to=%d AND ct.deleted=0 ORDER BY ct.sorting ASC", $pageid);
			}
		} else {
			// get groups from user
			$res = $this->CLASS['db']->query(sprintf("SELECT groupid FROM user_group WHERE userid=%d",$_SESSION['userid']));
			$orclause = "";
			while($rowuser = $this->CLASS['db']->fetch_assoc($res)) {
				$orclause .= sprintf("OR (ct.".$this->CLASS['db']->quoteIdentifier("group")."=%d  AND ct.grouprights > 0) ",$rowuser['groupid']);
			}

			if($this->CLASS['db']->dbtype == "pgsql") {
				$query = sprintf("SELECT ct.id as id, ct.content as content, ct.title as title, ct.type as type, u.name as lastupdatedby, to_char(ct.lastupdated,'DD. Mon YYYY HH24:MI:SS') as lastupdated FROM content ct left outer join users u ON ct.lastupdatedby = u.id WHERE belongs_to=%d AND ct.deleted=0 AND ((ct.otherrights > 0) OR (ct.".$this->CLASS['db']->quoteIdentifier("group")."=%d AND ct.grouprights > 0) %sOR (ct.owner=%d AND ct.userrights>0)) ORDER BY ct.sorting ASC",$pageid,$_SESSION['groupid'],$orclause,$_SESSION['userid']);
			} elseif($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") {
				$query = sprintf("SELECT ct.id as id, ct.content as content, ct.title as title, ct.type as type, u.name as lastupdatedby, strftime('%%d.%%m.%%Y %%H:%%M:%%S',ct.lastupdated) as lastupdated FROM content ct left outer join users u ON ct.lastupdatedby = u.id WHERE belongs_to=%d AND ct.deleted=0 AND ((ct.otherrights > 0) OR (ct.".$this->CLASS['db']->quoteIdentifier("group")."=%d AND ct.grouprights > 0) %sOR (ct.owner=%d AND ct.userrights>0)) ORDER BY ct.sorting ASC",$pageid,$_SESSION['groupid'],$orclause,$_SESSION['userid']);
			} else {
				$query = "SELECT ct.id AS id, ct.content AS content, ct.title AS title, ct.type AS type, u.name AS lastupdatedby, DATE_FORMAT(ct.lastupdated,'%d. %M %Y %H:%i:%s') AS lastupdated FROM content ct";
				$query .= sprintf(" LEFT OUTER JOIN users u ON ct.lastupdatedby = u.id WHERE belongs_to=%d AND ct.deleted=0 AND ((ct.otherrights > 0) OR (ct.".$this->CLASS['db']->quoteIdentifier("group")."=%d AND ct.grouprights > 0) %s OR (ct.owner=%d AND ct.userrights>0)) ORDER BY ct.sorting ASC",
				$pageid, $_SESSION['groupid'], $orclause,$_SESSION['userid']);
			}
		}

		$res = $this->CLASS['db']->query($query);

		while($row = $this->CLASS['db']->fetch_assoc($res)) {
			$out .= "<content>".$row['id']."</content>\n";
		}

		return $out;
	}

	/**
	 * returns elements to open in tree
	 * @oaram integer $id id of element
	 * @return string return htmlpart of tree
	 */
	function getOpenTreeElements($id) {
		$this->CLASS['tree'] = new categoryTree();

		// check if page is movingpage
		if($_POST['move'] == "1") {
			$this->CLASS['tree']->start($this->CLASS,'move',"#");
		} else if (isset ($_POST['editor']) && $_POST['editor'] == "1") {
			$this->CLASS['tree']->start($this->CLASS,'editor',"#");
		} else {
			$this->CLASS['tree']->start($this->CLASS);
		}

		$lines = $this->CLASS['tree']->buildAjaxTreePart($id);

		return $lines;
	}

	/**
	 * return xmlcode
	 * @return string return the internal xmlcache of this class
	 */
	function get_xml() {
		return $this->xmlcode;
	}

	/**
	 * returns the hole tree for reloading
	 * @param string $expand
	 * @return string return xmlcode
	 */
	function tree_reload($expand = "") {
		$this->CLASS['tree'] = new categoryTree();

		// check if page is movingpage
		if(isset($_POST['move']) && $_POST['move'] == "1") {
			$this->CLASS['tree']->start($this->CLASS,'move',"#");
		} else if (isset ($_POST['editor']) && $_POST['editor'] == "1") {
			$this->CLASS['tree']->start($this->CLASS,'editor',"#");
		} else {
			$this->CLASS['tree']->start($this->CLASS);
		}

		if($expand != "") {
			$this->CLASS['tree']->doexpand = $expand;
			$this->CLASS['tree']->expand = $expand;
		}

		$lines = $this->CLASS['tree']->buildAjaxTreePart("0");

		// generate tree
		$tree = "<table id=\"treeelementtable\" class=\"treeelements\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
		$tree .= $lines;
		$tree .= "</table>\n";

		// generate xmloutput
		$this->xmlcode = '<?xml version="1.0" ?>' . "\n";
		$this->xmlcode .= "<root>\n";
		$this->xmlcode .= "\t<html>\n";
		$this->xmlcode .= "<![CDATA[\n";
		$this->xmlcode .= $tree;
		$this->xmlcode .= "]]>\n";
		$this->xmlcode .= "\t</html>\n";
		$this->xmlcode .= "</root>\n";
	}

	/**
	 * expand tree
	 */
	function tree_expand() {
		$_SESSION['firstrun'] = 0;

		if (isset($_SESSION['open']) && $_SESSION['open'] != null) {
			foreach($_SESSION['open'] as $key => $value) {
				$_SESSION['open'][$key] = 1;
			}
		}

		// save treecache
		if(!empty($_SESSION['userid'])) {
			$res = $this->CLASS['db']->query(sprintf("UPDATE users SET treecache='%s' WHERE id=%d",serialize($_SESSION['open']),$_SESSION['userid']));
		}

		$this->tree_reload("1");
	}

	/**
	 * collapse tree
	 */
	function tree_collapse() {
		$_SESSION['firstrun'] = 0;

		if (isset($_SESSION['open']) && $_SESSION['open'] != null) {
			foreach($_SESSION['open'] as $key => $value) {
				$_SESSION['open'][$key] = 0;
			}
		}

		// save treecache
		if(!empty($_SESSION['userid'])) {
			$res = $this->CLASS['db']->query(sprintf("UPDATE users SET treecache='%s' WHERE id=%d",serialize($_SESSION['open']),$_SESSION['userid']));
		}

		$this->tree_reload();
	}

	/**
	 * get contextmenu for tree elements
	 * @param string $menuname
	 * @param integer $pageid
	 * @param integer $contentid optional
	 */
	function tree_contextmenu($menuname, $pageid, $contentid = null) {
		if($contentid != null) {
			// fetch content type
			$res = $this->CLASS['db']->query(sprintf("SELECT type FROM content WHERE id=%d",$contentid));
			$cnt = $this->CLASS['db']->num_rows($res);
			$type = "";

			if($cnt == 1) {
				$row = $this->CLASS['db']->fetch_assoc($res);
				$type = $row['type'];
			}

			$menu = $this->CLASS['kr_extension']->show_menu($menuname, $contentid, $this->CLASS['knowledgeroot']->getPageRights($pageid,$_SESSION['userid']), $this->CLASS['knowledgeroot']->getContentRights($contentid,$_SESSION['userid']), $type);
		} else {
			$menu = $this->CLASS['kr_extension']->show_menu($menuname, $pageid, $this->CLASS['knowledgeroot']->getPageRights($pageid,$_SESSION['userid']));
		}

		// generate xmloutput
		$this->xmlcode = '<?xml version="1.0" ?>' . "\n";
		$this->xmlcode .= "<root>\n";
		//$this->xmlcode .= "\t<menuname>".$menuname."</menuname>\n";
		$this->xmlcode .= "\t<html>\n";
		$this->xmlcode .= "<![CDATA[\n";
		$this->xmlcode .= $menu;
		$this->xmlcode .= "]]>\n";
		$this->xmlcode .= "\t</html>\n";
		$this->xmlcode .= "</root>\n";
	}

	/**
	 * move tree element
	 * @param integer $source
	 * @param integer $destionation
	 */
	function tree_move($source, $destination) {
		if($this->CLASS['knowledgeroot']->getPageRights($source,$_SESSION['userid']) == 2 && $this->CLASS['knowledgeroot']->getPageRights($destination,$_SESSION['userid']) == 2) {
			$this->CLASS['hooks']->setHook("ajax","move_page","start");

			// check if element is a kind of element to move -> if yes than abort
			if(!$this->CLASS['tree']->isParentelement($destination,$source)) {
				$res = $this->CLASS['db']->query(sprintf("UPDATE tree SET belongs_to=%d WHERE id=%d",$destination,$source));

				$pagename = $this->CLASS['path']->getTreePageTitle($source);

				// email notification
				$this->CLASS['notification']->send_email_notification($source,"page","moved",$pagename,$source);

				$this->CLASS['hooks']->setHook("ajax","move_page","success");
			}

			$this->CLASS['hooks']->setHook("ajax","move_page","end");
		}

		// make reload tree
		$this->tree_reload();
	}

	/**
	 * move content element
	 * @param integer $source
	 * @param integer $destionation
	 */
	function tree_move_content($source, $destination) {
		if($this->CLASS['knowledgeroot']->getContentRights($source,$_SESSION['userid']) == 2 && $this->CLASS['knowledgeroot']->getPageRights($destination,$_SESSION['userid']) == 2) {
			$this->CLASS['hooks']->setHook("ajax","move_content","start");

			$res = $this->CLASS['db']->query(sprintf("UPDATE content SET belongs_to=%d WHERE id=%d",$destination,$source));

			$pagename = $this->CLASS['path']->getTreePageTitle($source);

			// email notification
			$this->CLASS['notification']->send_email_notification($source,"content","moved",$pagename,$source);

			$this->CLASS['hooks']->setHook("ajax","move_page","success");

			$this->CLASS['hooks']->setHook("ajax","move_content","end");
		}

		// generate xmloutput
		$this->xmlcode = '<?xml version="1.0" ?>' . "\n";
		$this->xmlcode .= "<root>\n";
		$this->xmlcode .= "\t<action>contentremove</action>\n";
		$this->xmlcode .= "\t<element>".$source."</element>\n";
		$this->xmlcode .= "</root>\n";
	}
}
?>
