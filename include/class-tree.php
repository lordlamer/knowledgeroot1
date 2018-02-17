<?php
/**
 *
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-tree.php 1039 2011-04-09 19:07:36Z lordlamer $
 */
class pathTree {
  var $CLASS;
  var $cache = array(); // cache of tree records indexed by tree id


  /**
   * init/start class
   */
  function start(&$CLASS) {
    $this->CLASS =& $CLASS;
  }

  /**
   * return path of a treeid with htmlcontent
   */
  function getPath($id = "0", $urlPrefix = '') {
    if($id == "") { $id = "0"; }
    $treeRecord = $this->getTreeRecord($id);

    if($treeRecord != null)  {
      if($treeRecord->belongs_to == "0") {
        return "<li class=\"breadcrumb-item\"><a href=\"" . $urlPrefix . "index.php?id=".$treeRecord->id."\">".$treeRecord->title."</a></li>";
      } else {
        $path = $this->getPath($treeRecord->belongs_to, $urlPrefix);
        return $path . "<li class=\"breadcrumb-item\"><a href=\"" . $urlPrefix . "index.php?id=".$treeRecord->id."\">" . $treeRecord->title."</a></li>";
      }
    } else {
      return "/ ";
    }
  }

  /**
   * return path of a treeid without htmlcontent
   */
  function getTextPath($id = "0", $urlPrefix = '') {
    if($id == "") { $id = "0"; }
    $treeRecord = $this->getTreeRecord($id);

    if($treeRecord != null)  {
      if($treeRecord->belongs_to == "0") {
        return "/ ".$treeRecord->title;
      } else {
        $path = $this->getTextPath($treeRecord->belongs_to, $urlPrefix);
        return $path . " / " . $treeRecord->title;
      }
    } else {
      return "/ ";
    }
  }

  /**
   *
   */
  function getUnlinkedPath($id = "0") {
    if($id == "") { $id = "0"; }
    $treeRecord = $this->getTreeRecord($id);

    if($treeRecord != null)  {
      if($treeRecord->belongs_to == "0") {

        return "/ ".$treeRecord->title;
      } else {
        $path = $this->getUnlinkedPath($treeRecord->belongs_to);
        return $path . " / " . $treeRecord->title;
      }
    } else {
      return "/ ";
    }
  }

  /**
   *
   */
  function getTreePageTitle($id) {

    $treeRecord = $this->getTreeRecord($id);

    if($treeRecord != null)  {
        return $treeRecord->title;
    } else {
      return "";
    }
  }

  /**
   *
   */
  function getTreeRecord($id = "0") {
    $hashkey = md5('treerecord_'.$id);
    if(!($data = $this->CLASS['cache']->load($hashkey))) {
      $data = $this->_getTreeRecord($id);
      $this->CLASS['cache']->save($data, $hashkey, array('system', 'tree'));
    }

    return $data;
  }

  /**
   *
   */
  function _getTreeRecord($id = "0") {
    $treeRecord = null;
    $no_rows = false;

    if(isset ($this->cache[$id]) and $this->cache[$id] != null) {
      $treeRecord = $this->cache[$id];
    } else {
      $res = $this->CLASS['db']->query(sprintf("SELECT * FROM tree WHERE id=%d",$id));
      $anz = $this->CLASS['db']->num_rows($res);
      if($anz == 1)  {
        $row = $this->CLASS['db']->fetch_object($res);
        $treeRecord = $row;
        $this->cache[$id] = $treeRecord;
      } else {
        return null;
      }
    }
    return $treeRecord;
  }

  /**
   *
   */
  function isTree($id) {
    if(isset($this->cache[$id]) and $this->cache[$id] != null) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * return parent of element in tree
   */
  function getParent($treeid) {
    $hashkey = md5('getparent_'.$treeid);
    if(!($this->CLASS['cache']->load($hashkey))) {
      $data = $this->_getParent($treeid);
      $this->CLASS['cache']->save($data, $hashkey, array('system', 'tree'));
    }

    return $data;
  }

  /**
   * return parent of element in tree
   */
  function _getParent($treeid) {
    $res = $this->CLASS['db']->query(sprintf("SELECT belongs_to FROM tree WHERE id=%d",$treeid));
    $anz = $this->CLASS['db']->num_rows($res);

    if($anz == 1) {
      $row = $this->CLASS['db']->fetch_assoc($res);
      return $row['belongs_to'];
    } else {
      return 0;
    }
  }
}


/**
 *
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-tree.php 1039 2011-04-09 19:07:36Z lordlamer $
 */
class categoryTree {
  var $out = array(); // elemts for the user
  var $allelements = array(); // all elements in the table
  var $myelements = array();

  var $open = array();

  var $firstrun = 0;
  var $expand = 0;
  var $doexpand = 0;
  var $targetfile = "index.php";
  var $move = FALSE;
  var $editor = FALSE;

  var $CLASS;
  var $category = array();

  var $userid = 0;
  var $groupid = 0;

  var $admin = 0;

  var $firstrootelement = 1; // used to check if the first rootelement is shown

  /**
   * init/start class
   */
  function start(&$CLASS,$specialCase='',$targetfile="index.php") {
    $this->CLASS =& $CLASS;

    if(!isset($_SESSION['firstrun']) || (isset($_SESSION['firstrun']) && $_SESSION['firstrun'] == 1)) {
      $this->doexpand = $this->CLASS['config']->tree->expandall;
    } else {
      $this->doexpand = 0;
    }

    $this->move = ($specialCase == 'move');
    $this->editor = ($specialCase == 'editor');
    $this->targetfile = $targetfile;

    if (isset ($_SESSION['admin']) and $_SESSION['admin'] != '') {
      $this->admin = $_SESSION['admin'];
    }

    $this->readAllCategories();
  }

  /**
   *
   */
  function readCategories() {
    if (isset ($this->category[0]) and $this->category[0] != "") {
      return $this->category;
    }

    // get order by
    if($this->CLASS['config']->tree->order == 'self') {
      $orderby = "sorting";
    } else {
      $orderby = "title";
    }

    $hashkey = md5('categorytree_'.$orderby);
    if(!($data = $this->CLASS['cache']->load($hashkey))) {
      $query = sprintf("SELECT * FROM tree WHERE deleted=0 ORDER BY %s ASC", $orderby);
      $res = $this->CLASS['db']->query($query);

      while ( $var = $this->CLASS['db']->fetch_assoc($res) ) {
        $data[] = $var;
      }

      $this->CLASS['cache']->save($data, $hashkey, array('system', 'tree'));
    }

    if(is_array($data)) {
      foreach($data as $var) {
        if($this->CLASS['knowledgeroot']->checkRecursivPerm($var['id'],$_SESSION['userid']) > 0) {
          $out[ $var['belongs_to'] ][ $var['id'] ]['title'] = $var['title'];
          $out[ $var['belongs_to'] ][ $var['id'] ]['tooltip'] = $var['tooltip'];
          $out[ $var['belongs_to'] ][ $var['id'] ]['alias'] = $var['alias'];
          $out[ $var['belongs_to'] ][ $var['id'] ]['id'] = $var['id'];
          $out[ $var['belongs_to'] ][ $var['id'] ]['belongs_to'] = $var['belongs_to'];
          $out[ $var['belongs_to'] ][ $var['id'] ]['symlink'] = $var['symlink'];
          $out[ $var['belongs_to'] ][ $var['id'] ]['icon'] = $var['icon'];
          $myout[ $var['id'] ]['id'] = $var['belongs_to'];
          $myout[ $var['id'] ]['symlink'] = $var['symlink'];

          if($this->firstrun == 1) {
            $this->open[$var['id'] ] = 0;
            $_SESSION['open'][$var['id']] = 0;
          }
        }
      }
    }

    $this->out = &$out;
    $this->myelements = &$myout;

    return $out;
  }

  /**
   *
   */
  function readAllCategories() {
    // get order by
    if($this->CLASS['config']->tree->order == 'self') {
      $orderby = "sorting";
    } else {
      $orderby = "title";
    }

    $hashkey = md5('categorytree_'.$orderby);
    if(!($data = $this->CLASS['cache']->load($hashkey))) {
      $query = sprintf("SELECT * FROM tree WHERE deleted=0 ORDER BY %s ASC",$orderby);
      $res = $this->CLASS['db']->query($query);

      while ( $var = $this->CLASS['db']->fetch_assoc($res) ) {
        $data[] = $var;
      }

      $this->CLASS['cache']->save($data, $hashkey, array('system', 'tree'));
    }

    if(is_array($data)) {
      foreach($data as $var) {
        $out[  $var['id'] ] = $var['belongs_to'];
      }
    }

    $this->allelements = &$out;
  }

  /**
   *
   */
  function isParentelement($element, $parent) {
    $query = sprintf("SELECT belongs_to FROM tree WHERE id='%s'",$element);
    $res = $this->CLASS['db']->query($query);
    $anz = $this->CLASS['db']->num_rows($res);
    if($anz != 1) {
      return FALSE;
    }

    $row = $this->CLASS['db']->fetch_assoc($res);

    if($row['belongs_to'] == $parent) {
      return TRUE;
    } else {
      return $this->isParentelement($row['belongs_to'],$parent);
    }
  }

  /**
   *
   */
  function buildTree( $rec_id ) {
    echo "<table class=\"card\" class=\"tree\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
    $cats = $this->readCategories();

    $moveparam = '';
    if($this->CLASS['config']->tree->ajax) {
      if($this->CLASS['config']->tree->type == "static") {
        $fixedstyle = "style=\"display:none;\"";
        $slidestyle = "style=\"display:block;\"";
      } else {
        $fixedstyle = "style=\"display:block;\"";
        $slidestyle = "style=\"display:none;\"";
      }

      if($this->move == TRUE) {
        $moveparam = "1";
      }

      echo '
        <tr><td class="treenavi">        
            ' . $this->CLASS['kr_extension']->show_menu("tree",$moveparam) . '            
        </td></tr>
      ';
      echo "<tr><td>\n";
      echo "<div class=\"\" id=\"treeelements\">\n";
      //echo "<div id=\"treeanchor\"></div>\n";
      echo "<table id=\"treeelementtable\" class=\"treeelements\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
      echo $this->showAjaxTreePart( $this->getTreePart(0) );
      echo "</table>\n";
      echo "</div>\n";
      echo "</td></tr>\n";
    } else {
      echo $this->showTreePart( $this->getTreePart(0) );
    }

    echo "</table>\n";

	// treebottom navi
	echo $this->CLASS['kr_extension']->show_menu("treebottom");
  }

	/**
	 *
	 */
	function buildAjaxTreePart($id) {
		if(isset($_SESSION['open'])) {
			$this->open = $_SESSION['open'];
		} else {
			$this->open = array();
		}

		$cats = $this->readCategories();

		if($id == "0") {
			$depth = 0;
		} else {
			$depth = 1;
		}

		$spaces = $this->getDeepPart($id) + $depth;

		$out = "";

		if($this->editor == TRUE) {
			$out .= $this->getContentTitleOfPage($id,$depth+1);
		}

		$out .= $this->showAjaxTreePart( $this->getTreePart($id), $spaces );

		return $out;

	}

  /**
   *
   */
  function showTreePart( $arr, $indent = 0 ) {
    $space = "";
    $open = array();

    for ( $i=0; $i<$indent; $i++ ) {
      $space .= "&nbsp;<img src=\"images/clear.gif\" width=\"9\" height=\"9\" alt=\"\" />&nbsp;";
    }

    $out = "";
    $itemcounter = 0;

    if( count( $arr ) > 0 ) {
            foreach( $arr as $id => $title ) {
              $itemcounter++;
        if($title == "") {
          $title = "[".$this->CLASS['translate']->_('Empty')."]";
        }
        //if($this->visible[$id] == 1) {
          if($this->move == TRUE) {
            $indexfile = "move.php";
          } else {
            $indexfile = "index.php";
          }

          if($this->lastPart($id) == 0) {
            if ((isset ($_SESSION['open'][$id]) and $_SESSION['open'][$id] == 1) || $this->expand == 1 || $this->doexpand == 1) {
              $spaces = $space . "&nbsp;<a class=\"image\" href=\"".$indexfile."?openid=$id\"><img border=\"0\" alt=\"\" width=\"9\" height=\"9\" src=\"images/minus.jpg\" /></a>&nbsp;".(($title['icon'] != '') ? '<img src="'.$title['icon'].'" />': '');
            } else {
              $spaces = $space . "&nbsp;<a class=\"image\" href=\"".$indexfile."?openid=$id\"><img border=\"0\" alt=\"\" width=\"9\" height=\"9\" src=\"images/plus.jpg\" /></a>&nbsp;".(($title['icon'] != '') ? '<img src="'.$title['icon'].'" />': '');
            }
          } else {
            if($title['icon'] != '')
              $spaces = $space . "&nbsp;<img class=\"bullet\" alt=\"\" src=\"".$title['icon']."\" />&nbsp;";
            else
              $spaces = $space . "&nbsp;<img class=\"bullet\" alt=\"\" width=\"7\" height=\"7\" src=\"images/black.gif\" />&nbsp;";
          }

          // check if link a actual element
          if($id == $_SESSION['cid']) {
            $class_act = "class=\"active_tree_item\" ";
          } else {
            $class_act = "";
          }

          if($this->move == TRUE) {
            $out .= "<tr><td class=\"tree\" nowrap=\"nowrap\">".$spaces . (($this->CLASS['config']->tree->showcounter) ? "<span class=\"treecounter\">" . $itemcounter . ".</span> " : "") . "<a ".$class_act."href=\"#\" onclick=\"window.opener.document.forms.move.to.value = '".$id."'; window.opener.document.forms.move.submit(); window.close();\">" . $title['title'] . "</a></td></tr>\n";
          } else {
            $out .= "<tr><td class=\"tree\" nowrap=\"nowrap\">".$spaces . (($this->CLASS['config']->tree->showcounter) ? "<span class=\"treecounter\">" . $itemcounter . ".</span> " : "") . "<a ".$class_act."href=\"" . $this->makeLink($title) . "\">" . (($this->isSymlink($id))? "&raquo;&nbsp;":"") . $title['title'] . "</a></td></tr>\n";
          }

          if ((isset ($this->open[$id]) and $this->open[$id] == 1) || $this->expand == 1 || $this->doexpand == 1) {
            $_SESSION['open'][$id] = 1;
            $this->open[$id] = 1;
            $out .= $this->showTreePart( $this->getTreePart( $id ), $indent+1 );
          }
        //}
      }
    }

    return $out;
  }

	/**
	 * return html that shows the part of a tree
	 */
	function showAjaxTreePart( $arr, $indent = 0 ) {
		$space = "";
		$open = array();
		$imagePrefix = "";
		$minusImage = "minus.jpg";
		$plusImage = "plus.jpg";

		$imagePrefix = $this->CLASS['knowledgeroot']->getEnv("REQUEST_DIR");

		for ( $i=0; $i<$indent; $i++ ) {
			$space .= "&nbsp;<img src=\"".$imagePrefix."images/clear.gif\" width=\"9\" height=\"9\" alt=\"\" />&nbsp;";
		}

		$out = "";
		$itemcounter = 0;

		if( count( $arr ) > 0 ) {
			foreach( $arr as $id => $title ) {
				$itemcounter++;
				if($title == "") {
					$title = "[".$this->CLASS['translate']->_('Empty')."]";
				}

				if($this->lastPart($id) == 0) {
					$moveparam = "";

					// check if page is movingpage
					if($this->move == TRUE) {
						$moveparam = "1";
					} else {
						$moveparam = "0";
					}

					$useeditor = "";
					if($this->editor == TRUE) $useeditor = 1;
					else $useeditor = 0;

					if ((isset ($_SESSION['open'][$id]) and $_SESSION['open'][$id] == 1) || $this->expand == 1 || $this->doexpand == 1) {
						$spaces = $space . "&nbsp;<a id=\"linkid_".$id."\" class=\"image\" href=\"javascript:;\" onclick=\"AjaxMenuClose(".$id.",".$moveparam.",'".$imagePrefix."',".$useeditor.");\"><img id=\"menuimg_".$id."\" border=\"0\" alt=\"\" width=\"9\" height=\"9\" src=\"".$imagePrefix."images/".$minusImage."\" /></a>&nbsp;" . (($title['icon'] != '') ? '<img src="'.$imagePrefix.$title['icon'].'" />' : '');
					} else {
						$spaces = $space . "&nbsp;<a id=\"linkid_".$id."\" class=\"image\" href=\"javascript:;\" onclick=\"AjaxMenuOpen(".$id.",".$moveparam.",'".$imagePrefix."',".$useeditor.");\"><img id=\"menuimg_".$id."\" border=\"0\" alt=\"\" width=\"9\" height=\"9\" src=\"".$imagePrefix."images/".$plusImage."\" /></a>&nbsp;" . (($title['icon'] != '') ? '<img src="'.$imagePrefix.$title['icon'].'" />' : '');
					}
				} else {
					if($title['icon'] != '')
						$spaces = $space . "&nbsp;<img class=\"bullet\" alt=\"\" src=\"".$title['icon'] ."\" />&nbsp;";
					else
						$spaces = $space . "&nbsp;<img class=\"bullet\" alt=\"\" width=\"7\" height=\"7\" src=\"".$imagePrefix."images/black.gif\" />&nbsp;";
				}

				// check if link a actual element
				if($id == $_SESSION['cid']) {
					$class_act = "class=\"active_tree_item\" ";
				} else {
					$class_act = "";
				}

				// check for rootelement
				if($this->CLASS['config']->tree->hrrootline && isset($title['belongs_to']) && $title['belongs_to'] == 0) {
					if($this->firstrootelement == 1) {
						$this->firstrootelement = 0;
						$class_root_item = "class=\"tree\"";
					} else {
						$class_root_item = "class=\"treeRootItem\"";
					}
				} else {
					$class_root_item = "class=\"tree\"";
				}

				$mousecontext = "";

				// check if contextmenu are enabled
				if($this->CLASS['config']->menu->context) {
					// set mousecontext for rightclick menu
					$mousecontext = " oncontextmenu=\"KnowledgerootMenu.show('mousemenu','pagecontext', ".$id."); return false;\"";
				}

				// set tooltip
				if($this->CLASS['config']->tree->edittooltiptext == 1 && $title['tooltip'] != "") {
					$tooltip = " title=\"".$title['tooltip']."\"";
				} else {
					$tooltip = " title=\"".$title['title']."\"";
				}

				if($this->move == TRUE) {
					$out .= "<tr id=\"menu_".$id."\"><td ".$class_root_item." nowrap=\"nowrap\">".$spaces . (($this->CLASS['config']->tree->showcounter) ? "<span class=\"treecounter\">" . $itemcounter . ".</span> " : "") . "<a ".$class_act."id=\"alink_".$id."\" href=\"#\" onclick=\"window.opener.document.forms.move.to.value = '".$id."'; window.opener.document.forms.move.submit(); window.close();\">" . $title['title'] . "</a></td></tr>\n";
				} else if ($this->editor == TRUE) {
					$out .= "<tr id=\"menu_".$id."\"><td ".$class_root_item." nowrap=\"nowrap\">".$spaces . (($this->CLASS['config']->tree->showcounter) ? "<span class=\"treecounter\">" . $itemcounter . ".</span> " : "") . "<a ".$class_act."id=\"alink_".$id."\" onclick=\"editorSelect('index.php?id=".$id."','".$title['title']."')\" href=\"#\">" .$title['title'] . "</a></td></tr>\n";
				} else {
					$out .= "<tr id=\"menu_".$id."\"><td ".$class_root_item." nowrap=\"nowrap\">".$spaces . (($this->CLASS['config']->tree->showcounter) ? "<span class=\"treecounter\">" . $itemcounter . ".</span> " : "") . "<a ".$class_act."id=\"alink_".$id."\" href=\"" . $this->makeLink($title) . "\"" . $mousecontext . $tooltip . '>' .(($this->isSymlink($id))? "&raquo;&nbsp;":"").$title['title'] . "</a></td></tr>\n";
				}

				// check if drag and drop is enabled
				if($this->CLASS['config']->menu->dragdrop && $this->move != TRUE && $this->editor != TRUE) {
					// add javascript for drag and drop
					$out .= '<script type="text/javascript" language="javascript">';
					$out .= 'var navig_agt=navigator.userAgent.toLowerCase();
var navig_ie=((navig_agt.indexOf("msie")!=-1) && (navig_agt.indexOf("opera")==-1));
var navig_ie8=(navig_ie && (navig_agt.indexOf("msie 8.")!=-1));';
					$out .= 'if((navigator.appName.toLowerCase() != "opera" && !document.all) || navig_ie8) {';
					$out .= "new Draggable('menu_".$id."', {handle: 'alink_".$id."', revert:true, ghosting: false, zindex: 900, starteffect:function() { Dragbox.show(\$('alink_'+".$id.").innerHTML); }, endeffect:function() { Dragbox.hide(); }  });";
					$out .= "Droppables.add('alink_".$id."', {hoverclass: 'menudraghover', onDrop: function(element) { var regdrag = /.*(menu)_[0-9]+.*/; regdrag.exec(element.id); if(RegExp.\$1 == 'menu') { var reg = /.*alink_([0-9]+).*/; reg.exec(element.innerHTML); AjaxMoveTree(RegExp.\$1, ".$id."); } else { var reg = /.*contentdragid_([0-9]+).*/; reg.exec(element.id); AjaxMoveContent(RegExp.\$1, ".$id."); } } });";
					$out .= '}';
					$out .= "</script>\n";
				}

				if ((isset ($this->open[$id]) && $this->open[$id] == 1) || $this->expand == 1 || $this->doexpand == 1) {
					if($this->editor == TRUE) {
						$out .= $this->getContentTitleOfPage($id,$indent+1,$imagePrefix);
					}

					$_SESSION['open'][$id] = 1;
					$this->open[$id] = 1;
					$out .= $this->showAjaxTreePart( $this->getTreePart( $id ), $indent+1 );
				}
			}
		}

		return $out;
	}

	/**
	 * create a link
	 * @param array $link
	 * @return string return url
	 */
	function makeLink($link = array()) {
		if(is_array($link)) {
			if($link['symlink'] != 0) {
				return "index.php?id=" . $link['symlink'];
			} elseif($link['alias'] != "" && $this->CLASS['config']->misc->pagealias->use && $this->CLASS['config']->misc->pagealias->static) {
				return $link['alias'] . ".html";
			} else {
				return "index.php?id=" . $link['id'];
			}
		}

		return "";
	}

	/**
	 * return number of parents to root
	 * needed for tree to show subelements
	 * @param integer $id
	 * @return integer
	 */
	function getDeepPart($id) {
		if (isset ($this->allelements[$id]) and $this->allelements[$id] != "" && isset ($this->allelements[$id]) and $this->allelements[$id] != 0) {
			return 1 + $this->getDeepPart($this->allelements[$id]);
		} else {
			return "0";
		}
	}

	/**
	 * get part of tree
	 * @param integer $belongs_to
	 * @return array
	 */
	function getTreePart( $belongs_to ) {
		// WAAH! ueberfluessig haeufiger Aufruf...mir jetzt egal :)
		$cats = $this->out;
		$out = array();

		if (isset ($cats[ $belongs_to ]) and count( $cats[ $belongs_to ] ) > 0 ) {
			foreach ( $cats[ $belongs_to ] as $id => $entry ) {
				$out[$id] = $entry;
			}
		}

		return $out;
	}

	/**
	 * check if the id is an symlink
	 * @param integer $id treeitemid
	 * @return integer
	 */
	function isSymlink($id) {
		if(isset($this->myelements[$id]['symlink']) && $this->myelements[$id]['symlink'] != "" && $this->myelements[$id]['symlink'] != "0") {
			return $this->myelements[$id]['symlink'];
		}

		return 0;
	}

	/**
	 * check if the id is the last part in tree
	 * @param integer $id
	 * @return bool
	 */
	function lastPart($id) {
		foreach($this->myelements as $key => $value) {
			if($this->myelements[$key]['id'] == $id) {
				return 0;
			}
		}

		return 1;
	}

	/**
	 * get all content titels of a tree element
	 * @param integer $pageid
	 * @param integer $indent
	 * @param string $imagePrefix
	 * @return string
	 */
	function getContentTitleOfPage($pageid, $indent = 0,$imagePrefix = "") {
		$imagePrefix = $this->CLASS['knowledgeroot']->getEnv("REQUEST_DIR");

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

		$space = "";
		for ( $i=0; $i<$indent; $i++ ) {
			$space .= "&nbsp;<img src=\"".$imagePrefix."images/clear.gif\" alt=\"\" width=\"9\" height=\"9\" />&nbsp;";
		}

		$spaces = $space . "&nbsp;<img class=\"bullet\" alt=\"\" width=\"16\" height=\"16\" src=\"".$imagePrefix."images/pages.gif\" />&nbsp;";

		while($row = $this->CLASS['db']->fetch_assoc($res)) {
			if($row['title'] == "") $row['title'] = $row['type'] . " (ID:".$row['id'].")";
			$out .= "<tr id=\"contenttreeid_".$row['id']."\"><td>" . $spaces . "<a href=\"javascript:;\" onclick=\"editorSelect('index.php?contentid=".$row['id']."#".$row['id']."', '".$row['title']."');\">" . $row['title']."</a></td></tr>\n";
		}

		return $out;
	}
}

?>
