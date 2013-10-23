<?php

if (!is_file("config/app.ini")) {
    echo "<html><body>No configuration file found! Please make a <a href=\"install.php\">install</a>!</body></html>";
    exit();
}

// load requiered files
require_once("include/init.php");
$pageid = $CLASS['knowledgeroot']->getPageIdFromContentId($_GET['contentid']);

if (isset($_GET['contentid']) && $_GET['contentid'] != '' && $CLASS['knowledgeroot']->checkRecursivPerm($pageid, $_SESSION['userid']) > 0 && $CLASS['knowledgeroot']->getContentRights($_GET['contentid'], $_SESSION['userid']) >= 1) {

    // get my rights - possible is 0,1,2
    $mypagerights = $CLASS['knowledgeroot']->getPageRights($pageid, $_SESSION['userid']);

    //check if userid and groupid is set, if not set to 0
    if ($_SESSION['userid'] == "" || $_SESSION['groupid'] == "") {
	$_SESSION['userid'] = 0;
	$_SESSION['groupid'] = 0;
    }

    // get page content collapse info
    $hashkey = md5('contentcollapsed_' . $_SESSION['cid']);
    if (!($treedata = $CLASS['cache']->load($hashkey))) {
	$res = $CLASS['db']->query(sprintf("SELECT contentcollapsed FROM tree WHERE id=%d", $pageid));
	$anz = $CLASS['db']->num_rows($res);
	if ($anz == 1) {
	    $treedata = $CLASS['db']->fetch_assoc($res);
	    $CLASS['cache']->save($treedata, $hashkey, array('system', 'contentcollapsed'));
	}
    }

    if ($CLASS['db']->dbtype == "pgsql") {
	$query = sprintf("SELECT ct.id as id, ct.content as content, ct.title as title, ct.type as type, u.name as lastupdatedby, to_char(ct.lastupdated,'DD.MM.YYYY HH24:MI:SS') as lastupdated, to_char(ct.createdate,'DD.MM.YYYY HH24:MI:SS') as createdate FROM content ct left outer join users u ON ct.lastupdatedby = u.id WHERE ct.belongs_to=%d AND ct.deleted=0 ORDER BY ct.sorting ASC", $pageid);
    } elseif ($CLASS['db']->dbtype == "sqlite" || $CLASS['db']->dbtype == "sqlite3") {
	$query = sprintf("SELECT ct.id as id, ct.content as content, ct.title as title, ct.type as type, u.name as lastupdatedby, strftime('%%d.%%m.%%Y %%H:%%M:%%S',ct.lastupdated) as lastupdated, strftime('%%d.%%m.%%Y %%H:%%M:%%S',createdate) as createdate FROM content ct left outer join users u ON ct.lastupdatedby = u.id WHERE ct.belongs_to=%d AND ct.deleted=0 ORDER BY ct.sorting ASC", $pageid);
    } else {
	$query = "SELECT ct.id AS id, ct.content AS content, ct.title AS title, ct.type AS type,
					u.name AS lastupdatedby, DATE_FORMAT(ct.lastupdated,'%d.%m.%Y %H:%i:%s') as lastupdated,
					DATE_FORMAT(ct.createdate,'%d.%m.%Y %H:%i:%s') as createdate
					FROM content ct";
	$query .= sprintf(" LEFT OUTER JOIN users u ON ct.lastupdatedby = u.id
					WHERE ct.belongs_to=%d AND ct.deleted=0
					ORDER BY ct.sorting ASC", $pageid);
    }

    $hashkey = md5('content_' . $query);
    if (!($CLASS['cache']->test($hashkey))) {
	$res = $CLASS['db']->query($query);

	$data = null;
	while ($row = $CLASS['db']->fetch_assoc($res)) {
	    $data[] = $row;
	}

	$CLASS['cache']->save($data, $hashkey, array('system', 'content'));
    } else {
	$data = $CLASS['cache']->load($hashkey);
    }

    $anz = count($data);

    // needed for up and down arrows
    $firstcontent = 1;
    $maxcontent = $anz;
    $contentcounter = 0;

    // check if some table is on the page
    if ($anz != 0) {
	foreach ($data as $row) {
	    // get content rights
	    $mycontentrights = $CLASS['knowledgeroot']->getContentRights($row['id'], $_SESSION['userid']);

	    // check if it is an extension and if it is enabled
	    if ($mycontentrights == 0 || (($row['type'] != "" && $row['type'] != "text") && (!isset($CLASS['extension'][$row['type']]) || (isset($CLASS['extension'][$row['type']]) && $CLASS['extension'][$row['type']]['init'] == FALSE)))) {
		$maxcontent--;
		continue;
	    }

	    $contentcounter++;

            // check if this is the content we want to show
            if($row['id'] != $_GET['contentid']) {
		$firstcontent = 0;
                continue;
            }

	    if ($row['title'] == null || $row['title'] == "") {
		if ($row['type'] == "" || $row['type'] == "text") {
		    //$contentType = "HTML/text";
		    $contentType = '';
		} else {
		    $contentType = $row['type'] . " " . "content";
		}
		$titleText = $contentType;
	    } else {
		$titleText = $row['title'];
	    }

	    // init booleans
	    $show_files = 1;
	    $showtitle = $CLASS['config']->content->showtitle;
	    if ($CLASS['config']->content->collapsecontent)
		$collapse = $treedata['contentcollapsed'];
	    else
		$collapse = false;

	    // check if content should be opened
	    if (isset($_GET['oc']) && $_GET['oc'] == $row['id']) {
		$collapse = false;
	    }

	    $mousecontext = "";

	    // check if contextmenu are enabled
	    if ($CLASS['config']->menu->context == 1) {
		// set mousecontext for rightclick menu
		$mousecontext = " oncontextmenu=\"KnowledgerootMenu.show('mousemenu','contentcontext', " . $pageid . ", " . $row['id'] . "); return false;\"";
	    }

	    $lastUpdated = $CLASS['translate']->_('Last modified by') . " " . ($row['lastupdatedby'] != null ? $row['lastupdatedby'] : $CLASS['translate']->_('guest')) . " " . $CLASS['translate']->_('on') . " " . $row['lastupdated'];
	    $created = $CLASS['translate']->_('created') . ": " . $row['createdate'];

	    // add up and down arrows
	    if ($mycontentrights == 2 && $mypagerights == 2) {
		if ($maxcontent != $contentcounter) {
		    $CLASS['kr_extension']->menu['content']['movedown']['name'] = 'movedown';
		    $CLASS['kr_extension']->menu['content']['movedown']['nolink'] = '1';
		    $CLASS['kr_extension']->menu['content']['movedown']['priority'] = '60';
		    $CLASS['kr_extension']->menu['content']['movedown']['wrap'] = "<div style=\"height: 13px; float: right;\" dojoType=\"dijit.MenuBarItem\" onclick=\"location.href='index.php?movedown=" . $row['id'] . "';\"><img src=\"images/down_arrow.gif\" /></div>";
		} else {
		    unset($CLASS['kr_extension']->menu['content']['movedown']);
		}

		if ($firstcontent != 1) {
		    $CLASS['kr_extension']->menu['content']['moveup']['name'] = 'moveup';
		    $CLASS['kr_extension']->menu['content']['moveup']['nolink'] = '1';
		    $CLASS['kr_extension']->menu['content']['moveup']['priority'] = '70';
		    $CLASS['kr_extension']->menu['content']['moveup']['wrap'] = "<div style=\"height: 13px; float: right;\" dojoType=\"dijit.MenuBarItem\" onclick=\"location.href='index.php?moveup=" . $row['id'] . "';\"><img src=\"images/up_arrow.gif\" /></div>";
		} else {
		    unset($CLASS['kr_extension']->menu['content']['moveup']);
		}
	    }

	    $CLASS['hooks']->setHook("kr_content", "show_tree_content", "show_content_menu_start");

	    // show content menu
	    echo $CLASS['kr_extension']->show_menu("content", $row['id'], $mypagerights, $mycontentrights, $row['type']);

	    $CLASS['hooks']->setHook("kr_content", "show_tree_content", "show_content_menu_end");
	    echo "
						<div class=\"ContentBodyWrapper\" id=\"ContentCollapseWrapper" . $row['id'] . "\">
						<span class=\"ContentBody\">\n";

	    // check if content is an extension
	    if ($row['type'] == "" || $row['type'] == "text") {
		// show content
		$CLASS['hooks']->setHook("kr_content", "show_tree_content", "show_content_start");

		// adding colored highlighting
		if (isset($_GET['highlight']) && $_GET['highlight'] != "") {
		    $highlight = str_replace('&quot;', '\"', $_GET['highlight']);
		    $highlight = explode(",", $highlight);

		    foreach ($highlight as $hkey => $hvalue) {
			$hvalue = preg_replace('/[^a-zA-Z0-9 \-_]/mu', '', $hvalue);
			$row['content'] = $CLASS['highlight']->str_highlight($row['content'], $hvalue, $CLASS['highlight']->STR_HIGHLIGHT_STRIPLINKS, '<span class="highlightword">\1</span>');
		    }
		}

		echo $row['content'];

		$CLASS['hooks']->setHook("kr_content", "show_tree_content", "show_content_end");
	    } else {
		// check if extension is loaded
		if (isset($CLASS['extension'][$row['type']]) and $CLASS['extension'][$row['type']]['init'] == TRUE) {
		    $CLASS['hooks']->setHook("kr_content", "show_tree_content", "show_extension_start");

		    // run extension
		    echo $CLASS['extension'][$row['type']]['class']->show_content($row['id']);

		    $CLASS['hooks']->setHook("kr_content", "show_tree_content", "show_extension_end");

		    // check if added files should be shown
		    if ($CLASS['extension'][$row['type']]['class']->show_addedfiles != 1) {
			$show_files = 0;
		    }
		} else {
		    $CLASS['hooks']->setHook("kr_content", "show_tree_content", "show_extension_not_loaded");
		    continue;
		}
	    }

	    echo "</span>
					</div>
					\n";

	    if ($show_files == 1) {
		$CLASS['hooks']->setHook("kr_content", "show_tree_content", "show_content_files_start");

		// select added files
		$hashkey = md5('files' . $row['id']);
		if (!($CLASS['cache']->test($hashkey))) {
		    if ($CLASS['db']->dbtype == "pgsql") {
			$result = $CLASS['db']->query(sprintf("SELECT id,filename,filesize,owner, to_char(date,'DD. Mon YYYY HH24:MI:SS') AS dateform FROM files WHERE belongs_to=%d AND deleted=0 ORDER BY id ASC", $row['id']));
		    } elseif ($CLASS['db']->dbtype == "sqlite" || $CLASS['db']->dbtype == "sqlite3") {
			$result = $CLASS['db']->query(sprintf("SELECT id,filename,filesize,owner, strftime('%%d.%%m.%%Y %%H:%%M:%%S',date) AS dateform FROM files WHERE belongs_to=%d AND deleted=0 ORDER BY id ASC", $row['id']));
		    } else {
			$query = "SELECT id,filename,filesize,owner, DATE_FORMAT(date,'%d. %M %Y %H:%i:%s') AS dateform
										FROM files";
			$query .= sprintf(" WHERE belongs_to=%d AND deleted=0
										ORDER BY id ASC", $row['id']);
			$result = $CLASS['db']->query($query);
		    }

		    $rows = null;
		    while ($zeile = $CLASS['db']->fetch_assoc($result)) {
			$rows[] = $zeile;
		    }

		    $CLASS['cache']->save($rows, $hashkey, array('system', 'files'));
		} else {
		    $rows = $CLASS['cache']->load($hashkey);
		}

		echo '<div dojoType="dijit.layout.ContentPane">';

		// read all select files
		if (is_array($rows)) {
		    foreach ($rows as $zeile) {
			$title = "";
			if ($zeile['owner'] == NULL || $zeile['owner'] == 0 || $zeile['owner'] == "") {
			    $title = $zeile['dateform'];
			} else {
			    $title = $CLASS['knowledgeroot']->getOwner($zeile['owner']) . " - " . $zeile['dateform'];
			}

			// check for static file download
			if (isset($CLASS['config']->misc->download->static) && $CLASS['config']->misc->download->static == 1) {
			    $downloadlink = "download/" . $zeile['id'] . "/" . $zeile['filename'];
			} else {
			    $downloadlink = "index.php?download=" . $zeile['id'];
			}

			if ($mycontentrights == 2) {
			    echo "<a href=\"index.php?delfile=" . $zeile['id'] . "\" onclick=\"return confirm('" . $CLASS['translate']->_('Do you really want to delete?') . "');\"><img src=\"images/delete.gif\" title=\"" . $CLASS['translate']->_('delete') . "\" class=\"upload\" /></a>&nbsp;<a href=\"" . $downloadlink . "\" title=\"" . $title . "\"><img src=\"images/file.gif\" class=\"upload\" /> " . $zeile['filename'] . "</a>&nbsp;<font class=\"text\">[" . getfilesize($zeile['filesize']) . "]&nbsp;[" . $title . "]</font><br />\n";
			} else {
			    echo "<a href=\"" . $downloadlink . "\" title=\"" . $title . "\"><img src=\"images/file.gif\" class=\"upload\" /> " . $zeile['filename'] . "</a>&nbsp;<font class=\"text\">[" . getfilesize($zeile['filesize']) . "]&nbsp;[" . $title . "]</font><br />\n";
			}
		    }
		}

		echo '</div>';

		$CLASS['hooks']->setHook("kr_content", "show_tree_content", "show_content_files_end");
	    }

	    $includeFileLink = false;

	    if ($show_files == 1) {
		// show form for adding new files
		if ($mycontentrights == 2) {
		    $includeFileLink = true;
		    echo "<div id=\"fileform_" . $row['id'] . "\" style=\"display:none\">
								<form class=\"AddFileForm\" action=\"index.php\" method=\"post\" enctype=\"multipart/form-data\">\n
									<b>" . $CLASS['translate']->_('add file') . "</b>\n
									<input type=\"hidden\" name=\"upload\" value=\"yes\" />
									<input type=\"hidden\" name=\"contentid\" value=\"" . $row['id'] . "\" />
									<input type=\"file\" name=\"datei\" />&nbsp;\n
									<input class=\"button\" type=\"submit\" name=\"submit\" value=\"" . $CLASS['translate']->_('add') . "\" />
								</form></div>";
		}
	    }

	    // show content status bar
	    if ($CLASS['config']->content->statusbar)
		echo "<div class=\"content_statusbar\">" . $lastUpdated . "&nbsp;|&nbsp;" . $created . "</div>\n";
	} // end of while
    }
}
?>