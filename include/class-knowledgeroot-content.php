<?php
/**
 * This Class inerhits functions that show content in knowledgeroot
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-knowledgeroot-content.php 1343 2013-10-07 21:04:19Z lordlamer $
 */
class knowledgeroot_content {
	/**
	 * @param array $CLASS reference to global $CLASS var
	 */
	var $CLASS;

	/**
	 * init/start class
	 * @param array &$CLASS reference to global $CLASS var
	 */
	function start(&$CLASS) {
		$this->CLASS =& $CLASS;
	}

	/**
	 * this function show the content
	 */
	function show_content() {
		$this->CLASS['hooks']->setHook("kr_content","show_content","start");

		if(isset ($_GET['action']) and $_GET['action'] == "newcontent") {
			$this->new_content();
		} elseif(isset ($_GET['action']) and $_GET['action'] == "newpage") {
			$this->new_page();
		} elseif(isset ($_GET['action']) and $_GET['action'] == "editpage") {
			$this->edit_page();
		} elseif(isset ($_GET['eid']) and $_GET['eid'] != "") {
			$this->edit_content();
		} elseif (isset($_GET['action']) and $_GET['action'] == "showsearch" && isset($_GET['key']) and $_GET['key'] != "") {
			$this->show_search();
		} elseif(isset ($_GET['action']) and $_GET['action'] == "login") {
			$this->show_login();
		} elseif(isset ($_GET['action']) and $_GET['action'] == "createroot" && isset ($_SESSION['admin']) and $_SESSION['admin']) {
			$this->create_root();
		} elseif(((isset ($_GET['action']) and $_GET['action'] == "options") || (isset ($_POST['action']) and $_POST['action'] == "options")) && !empty($_SESSION['userid'])) {
			$this->show_options();
		} elseif(isset ($_GET['action']) and $_GET['action'] == "users" && isset ($_SESSION['admin']) and $_SESSION['admin'] == 1) {
			$this->list_users();
		} elseif(isset ($_GET['action']) and $_GET['action'] == "adduser" && isset ($_SESSION['admin']) and $_SESSION['admin'] == 1) {
			$this->add_user();
		} elseif(isset ($_GET['action']) and $_GET['action'] == "edituser" && isset ($_SESSION['admin']) and $_SESSION['admin'] == 1) {
			$this->edit_user();
		} elseif(isset ($_GET['action']) and $_GET['action'] == "addgroup" && isset ($_SESSION['admin']) and $_SESSION['admin'] == 1) {
			$this->add_group();
		} elseif(isset ($_GET['action']) and $_GET['action'] == "editgroup" && isset ($_SESSION['admin']) and $_SESSION['admin'] == 1) {
			$this->edit_group();
		} elseif(isset ($_GET['action']) and $_GET['action'] == "error") {
			$this->show_error();
		} elseif($this->CLASS['kr_extension']->content != "") {
			$this->CLASS['kr_extension']->show_ext_content();
		} else {
			$this->show_tree_content();
		}

		$this->CLASS['hooks']->setHook("kr_content","show_content","end");
	}

	/**
	 * create form for new content
	 */
	function new_content() {
		if($this->CLASS['knowledgeroot']->getPageRights($_SESSION['cid'],$_SESSION['userid']) == 2) {
			$this->CLASS['hooks']->setHook("kr_content","new_content","start");

			echo "<form action=\"index.php\" method=\"post\">";

			echo "<input type=\"hidden\" name=\"neditid\" value=\"new\" />";
			echo "<input type=\"hidden\" name=\"belongsto\" value=\"".$_SESSION['cid']."\" />";
			echo "<input type=\"hidden\" name=\"submit\" value=\"submit\" />";

			echo "<button class=\"btn btn-primary\" type=\"submit\" name=\"save\" value=\"save\">".$this->CLASS['translate']->_('save')."</button>\n";
			echo "<button class=\"btn btn-primary\" type=\"submit\" name=\"saveandclose\" value=\"saveandclose\">".$this->CLASS['translate']->_('save and close')."</button>\n";
			echo "<button class=\"btn btn-secondary\" type=\"submit\" name=\"close\" value=\"close\">".$this->CLASS['translate']->_('close')."</button>\n";

			echo "<p />";

			echo '<div class="card">';
			echo '
			  <div class="card-header">
				<ul class="nav nav-tabs card-header-tabs" role="tablist">
				  <li class="nav-item">
					<a class="nav-link active" id="content-tab" data-toggle="tab" href="#content" role="tab" aria-controls="content" aria-selected="true">'.$this->CLASS['translate']->_('content').'</a>
				  </li>
				  <li class="nav-item">
					<a class="nav-link" id="permissions-tab" data-toggle="tab" href="#permissions" role="tab" aria-controls="permissions" aria-selected="false">'.$this->CLASS['translate']->_('permissions').'</a>
				  </li>
				</ul>
			  </div>
			  <div class="card-body">
			  	<div class="tab-content">
			  		<div class="tab-pane fade show active" id="content" role="tabpanel" aria-labelledby="content-tab">
			';
			if ($this->CLASS['config']->content->showtitle) {
                echo "
                  <div class=\"form-group\">
					<label for=\"content_title\">" . $this->CLASS['translate']->_('Title') . "</label>
					<input type=\"text\" class=\"form-control\" id=\"content_title\" aria-describedby=\"title\" name=\"title\" value=\"\">
				  </div>
                ";
            }

			// position
			$defaultPos = '0';
			$resp = $this->CLASS['db']->squery("SELECT defaultcontentposition FROM tree WHERE id=%d", $_SESSION['cid']);
			if($rowp = $this->CLASS['db']->fetch_assoc($resp)) $defaultPos = $rowp['defaultcontentposition'];

			$res = $this->CLASS['db']->squery("SELECT id, title FROM content WHERE belongs_to=%d AND deleted=0 ORDER BY sorting ASC", $_SESSION['cid']);
			$cnt = $this->CLASS['db']->num_rows($res);

			$y = 0;
			if($cnt > 0) {
				echo "
				  <div class=\"form-group\">
					<label for=\"content_position\">" . $this->CLASS['translate']->_('Position') . "</label>
				";
				echo '<select name="position" class="form-control form-control-sm" id="content_position">';
				echo "<option value=\"first\">&larr; ".$this->CLASS['translate']->_('first')."</option>";
				while($row = $this->CLASS['db']->fetch_assoc($res)) {
					$y++;
					$title = "";
					if($row['title'] != "") {
						$title = $row['title'];
					} else {
						$title = "Id: " . $row['id'];
					}

					echo "<option value=\"".$row['id']."\"".(($defaultPos == 1 && $cnt == $y) ? " selected=\"selected\"": "").">&larr; ".sprintf($this->CLASS['translate']->_('after %s'), $title)."</option>";
				}
				echo '</select></div>';
			}

            echo "
				  <div class=\"form-group\">
					<label>" . $this->CLASS['translate']->_('Content') . "</label>
				";
			$this->CLASS['hooks']->setHook("kr_content","new_content","show");

			// show empty content in rte editor
			echo $this->CLASS['rte']->show("");

            echo '</div>';

			echo '</div>';
			echo '<div class="tab-pane fade" id="permissions" role="tabpanel" aria-labelledby="permissions-tab">';

			// check for inheritrights
			$inheritrights = $this->CLASS['knowledgeroot']->getInheritRights($_SESSION['cid']);

			$show_rights = 0;
			if((is_array($inheritrights) && $inheritrights['subinheritrightseditable'] == 1) || $inheritrights == false) {
				$show_rights = 1;
			}

			//check rights!!!
			if((!empty($_SESSION['userid']) && $show_rights == 1) || (isset($_SESSION['admin']) && $_SESSION['admin'] == 1)) {
				echo $this->CLASS['knowledgeroot']->rightpanel($_SESSION['userid']);
			}
			echo '</div>';
            echo '</div>';
			echo '</div>';

			$this->CLASS['hooks']->setHook("kr_content","new_content","show_tab");

			echo '</div>';

			echo "<p />\n";
			echo "<p />\n";

			echo "<button class=\"btn btn-primary\" type=\"submit\" name=\"save\" value=\"save\">".$this->CLASS['translate']->_('save')."</button>\n";
			echo "<button class=\"btn btn-primary\" type=\"submit\" name=\"saveandclose\" value=\"saveandclose\">".$this->CLASS['translate']->_('save and close')."</button>\n";
			echo "<button class=\"btn btn-secondary\" type=\"submit\" name=\"close\" value=\"close\">".$this->CLASS['translate']->_('close')."</button>\n";
			echo "</form>";

            echo "<p />\n";

			$this->CLASS['hooks']->setHook("kr_content","new_content","end");
		}
	}

	/**
	 * this function create form for newpage
	 */
	function new_page() {
		if($this->CLASS['knowledgeroot']->getPageRights($_SESSION['cid'],$_SESSION['userid']) == 2) {
			$this->CLASS['hooks']->setHook("kr_content","new_page","start");

			echo "<form action=\"index.php\" method=\"post\">";
			echo "<button class=\"btn btn-primary\" type=\"submit\" name=\"submit\" value=\"submit\">".$this->CLASS['translate']->_('create page')."</button>";
			echo "<p />";
			echo "<input type=\"hidden\" name=\"belongsto\" value=\"".$_SESSION['cid']."\" />";

			echo '
					<div class="card">

		  <div class="card-header">
			<ul class="nav nav-tabs card-header-tabs" role="tablist">
			  <li class="nav-item">
				<a class="nav-link active" id="content-tab" data-toggle="tab" href="#content" role="tab" aria-controls="content" aria-selected="true">'.$this->CLASS['translate']->_('site').'</a>
			  </li>
			  <li class="nav-item">
				<a class="nav-link" id="permissions-tab" data-toggle="tab" href="#permissions" role="tab" aria-controls="permissions" aria-selected="false">'.$this->CLASS['translate']->_('permissions').'</a>
			  </li>
			  <li class="nav-item">
				<a class="nav-link" id="inherit-permissions-tab" data-toggle="tab" href="#inherit-permissions" role="tab" aria-controls="inherit-permissions" aria-selected="false">'.$this->CLASS['translate']->_('inherit permissions').'</a>
			  </li>
			</ul>
		  </div>
		  <div class="card-body">
			<div class="tab-content">
				<div class="tab-pane fade show active" id="content" role="tabpanel" aria-labelledby="content-tab">
			';

            echo "
                  <div class=\"form-group\">
					<label for=\"page_title\">" . $this->CLASS['translate']->_('page name') . "</label>
					<input type=\"hidden\" name=\"newpage\" value=\"new\" />
					<input type=\"text\" class=\"form-control\" id=\"page_title\" aria-describedby=\"page_title\" name=\"title\" value=\"\">
				  </div>
                ";

			// automatically open the created page
			$auto_open = isset($_SESSION['auto_open']) && $_SESSION['auto_open'] == true ? "checked='checked'" : "";

            echo "
				  <div class=\"form-check\">
					<input type=\"checkbox\" class=\"form-check-input\" name=\"auto_open\" id=\"auto_open\" $auto_open value=\"true\">
					<label class=\"form-check-label\" for=\"auto_open\">".$this->CLASS['translate']->_('automatically open the created page') . "</label>
				  </div>
                ";

			// check for rights to show/edit alias
			if($this->CLASS['config']->misc->pagealias->use == 1 && (($this->CLASS['config']->misc->pagealias->rights == 2 && isset ($_SESSION['admin']) and $_SESSION['admin'] == 1) || ($this->CLASS['config']->misc->pagealias->rights == 1 && $_SESSION['userid'] != 0) || ($this->CLASS['config']->misc->pagealias->rights == 0))) {
                echo "
                  <div class=\"form-group\">
					<label for=\"page_alias\">" . $this->CLASS['translate']->_('page alias') . "</label>
					<input type=\"text\" class=\"form-control\" id=\"page_alias\" aria-describedby=\"page_alias\" name=\"alias\" value=\"\">
				  </div>
                ";
			}

			// default content position
            echo "
                  <div class=\"form-group\">
					<label for=\"defaultcontentposition\">" . $this->CLASS['translate']->_('default position for content') . "</label>
					<select class=\"form-control form-control-sm\" name=\"defaultcontentposition\">
						<option value=\"0\">".$this->CLASS['translate']->_('at beginning')."</option>
						<option value=\"1\">".$this->CLASS['translate']->_('at the end')."</option>
					</select>
				  </div>
                ";

			// icon
            echo "
                  <div class=\"form-group\">
					<label for=\"icon\">" . $this->CLASS['translate']->_('icon') . "</label>
					<input type=\"hidden\" id=\"treeicon\" name=\"treeicon\" value=\"\" />
					<button class=\"btn btn-secondary\" onclick=\"window.open('icon.php','Knowledgeroot','width=310,height=400,menubar=yes,resizable=yes,scrollbars=yes');\" type=\"button\" name=\"icon\">".$this->CLASS['translate']->_('select icon')."</button>
				  </div>
                ";

			// symlink
			if(isset($this->CLASS['config']->tree->symlink) && $this->CLASS['config']->tree->symlink == 1) {
                echo "
                  <div class=\"form-group\">
					<label for=\"symlink\">" . $this->CLASS['translate']->_('Symlink') . "</label>
					<input type=\"text\" class=\"form-control\" id=\"symlink\" aria-describedby=\"symlink\" name=\"symlink\" value=\"\">
				  </div>
                ";
			}

			// check for tooltip
			if($this->CLASS['config']->tree->edittooltiptext == 1) {
                echo "
                  <div class=\"form-group\">
					<label for=\"tooltip\">" . $this->CLASS['translate']->_('tooltip') . "</label>
					<input type=\"text\" class=\"form-control\" id=\"tooltip\" aria-describedby=\"tooltip\" name=\"tooltip\" value=\"\">
				  </div>
                ";
			}

			// check for order
			if($this->CLASS['config']->tree->order == 1) {
                echo "
                  <div class=\"form-group\">
					<label for=\"priority\">" . $this->CLASS['translate']->_('priority') . "</label>
					<input type=\"text\" class=\"form-control\" id=\"sorting\" aria-describedby=\"priority\" name=\"alias\" value=\"0\">
				  </div>
                ";
			}

			$this->CLASS['hooks']->setHook("kr_content","new_page","show");

			echo '</div>';
			echo '<div class="tab-pane fade" id="permissions" role="tabpanel" aria-labelledby="permissions-tab">';
			// check for inheritrights
			$inheritrights = $this->CLASS['knowledgeroot']->getInheritRights($_SESSION['cid']);

			$show_rights = 0;
			if((is_array($inheritrights) && $inheritrights['subinheritrightseditable'] == 1) || $inheritrights == false) {
				$show_rights = 1;
			}

			//check rights!!!
			if((!empty($_SESSION['userid']) && $show_rights == 1) || (isset($_SESSION['admin']) && $_SESSION['admin'] == 1)) {
				echo $this->CLASS['knowledgeroot']->rightpanel($_SESSION['userid']);
			}

			echo '</div>';
			echo '<div class="tab-pane fade" id="inherit-permissions" role="tabpanel" aria-labelledby="inherit-permissions-tab">';


			if(isset ($_SESSION['admin']) and $_SESSION['admin'] == 1) {
				echo $this->CLASS['knowledgeroot']->rightpanelsubinherit($_SESSION['userid']);
			}
			echo '</div>';
			$this->CLASS['hooks']->setHook("kr_content","new_page","show_tab");
			echo '</div>';
			echo '</div>';
            echo '</div>';

			echo '<p /><p />';
			echo "<button class=\"btn btn-primary\" type=\"submit\" name=\"submit\" value=\"submit\">".$this->CLASS['translate']->_('create page')."</button>";
			echo "</form>";

			$this->CLASS['hooks']->setHook("kr_content","new_page","end");
		}
	}

	/**
	 * this function create form for edit page
	 */
	function edit_page() {
		if($this->CLASS['knowledgeroot']->getPageRights($_SESSION['cid'],$_SESSION['userid']) == 2) {
			$this->CLASS['hooks']->setHook("kr_content","edit_page","start");

			$res = $this->CLASS['db']->query(sprintf("SELECT * FROM tree WHERE id=%d",$_SESSION['cid']));
			$anz = $this->CLASS['db']->num_rows($res);

			if($anz == 1) {
				$row = $this->CLASS['db']->fetch_assoc($res);

				echo "<form action=\"index.php\" method=\"post\">";
				echo "<button class=\"btn btn-primary\" type=\"submit\" name=\"submit\" value=\"submit\">".$this->CLASS['translate']->_('save changes')."</button>\n";
                echo "<p />";
				echo "<input type=\"hidden\" name=\"editpage\" value=\"".$_SESSION['cid']."\" />\n";

				echo '
					<div class="card">
		  <div class="card-header">
			<ul class="nav nav-tabs card-header-tabs" role="tablist">
			  <li class="nav-item">
				<a class="nav-link active" id="content-tab" data-toggle="tab" href="#content" role="tab" aria-controls="content" aria-selected="true">'.$this->CLASS['translate']->_('site').'</a>
			  </li>
			  <li class="nav-item">
				<a class="nav-link" id="permissions-tab" data-toggle="tab" href="#permissions" role="tab" aria-controls="permissions" aria-selected="false">'.$this->CLASS['translate']->_('permissions').'</a>
			  </li>
			  <li class="nav-item">
				<a class="nav-link" id="inherit-permissions-tab" data-toggle="tab" href="#inherit-permissions" role="tab" aria-controls="inherit-permissions" aria-selected="false">'.$this->CLASS['translate']->_('inherit permissions').'</a>
			  </li>
			</ul>
		  </div>
		  <div class="card-body">
			<div class="tab-content">
				<div class="tab-pane fade show active" id="content" role="tabpanel" aria-labelledby="content-tab">
				';

                echo "
                  <div class=\"form-group\">
					<label for=\"page_title\">" . $this->CLASS['translate']->_('page name') . "</label>
					<input type=\"text\" class=\"form-control\" id=\"title\" aria-describedby=\"page_title\" name=\"title\" value=\"".$row['title']."\">
				  </div>
                ";

				// check for rights to show/edit alias
				if($this->CLASS['config']->misc->pagealias->use == 1 && (($this->CLASS['config']->misc->pagealias->rights == 2 && isset ($_SESSION['admin']) and $_SESSION['admin'] == 1) || ($this->CLASS['config']->misc->pagealias->rights == 1 && $_SESSION['userid'] != 0) || ($this->CLASS['config']->misc->pagealias->rights == 0))) {
                    echo "
                  <div class=\"form-group\">
					<label for=\"page_alias\">" . $this->CLASS['translate']->_('page alias') . "</label>
					<input type=\"text\" class=\"form-control\" id=\"page_alias\" aria-describedby=\"page_alias\" name=\"alias\" value=\"".$row['alias']."\">
				  </div>
                ";
				}

				// default content position
                echo "
                  <div class=\"form-group\">
					<label for=\"defaultcontentposition\">" . $this->CLASS['translate']->_('default position for content') . "</label>
					<select class=\"form-control form-control-sm\" name=\"defaultcontentposition\">
						<option value=\"0\">".$this->CLASS['translate']->_('at beginning')."</option>
						<option value=\"1\"".(($row['defaultcontentposition']==1) ? " selected=\"selected\"" : "").">".$this->CLASS['translate']->_('at the end')."</option>
					</select>
				  </div>
                ";
                
				// icon
                echo "
                  <div class=\"form-group\">
					<label for=\"icon\">" . $this->CLASS['translate']->_('icon') . "</label>
					<img id=\"selected-icon\" src=\"".$row['icon']."\">
					<input type=\"hidden\" id=\"treeicon\" name=\"treeicon\" value=\"".$row['icon']."\" />
					<button class=\"btn btn-secondary\" onclick=\"window.open('icon.php','Knowledgeroot','width=310,height=400,menubar=yes,resizable=yes,scrollbars=yes');\" type=\"button\" name=\"icon\">".$this->CLASS['translate']->_('select icon')."</button>
				  </div>
                ";

				// symlink
				if(isset($this->CLASS['config']->tree->symlink) && $this->CLASS['config']->tree->symlink == 1) {
                    echo "
                  <div class=\"form-group\">
					<label for=\"symlink\">" . $this->CLASS['translate']->_('Symlink') . "</label>
					<input type=\"text\" class=\"form-control\" id=\"symlink\" aria-describedby=\"symlink\" name=\"symlink\" value=\"".$row['symlink']."\">
				  </div>
                ";
				}

				// check for tooltip
				if($this->CLASS['config']->tree->edittooltiptext == 1) {
                    echo "
                  <div class=\"form-group\">
					<label for=\"tooltip\">" . $this->CLASS['translate']->_('tooltip') . "</label>
					<input type=\"text\" class=\"form-control\" id=\"tooltip\" aria-describedby=\"tooltip\" name=\"tooltip\" value=\"".$row['tooltip']."\">
				  </div>
                ";
				}

				// check for order
				if($this->CLASS['config']->tree->order == 1) {
                    echo "
                  <div class=\"form-group\">
					<label for=\"priority\">" . $this->CLASS['translate']->_('priority') . "</label>
					<input type=\"text\" class=\"form-control\" id=\"sorting\" aria-describedby=\"priority\" name=\"alias\" value=\"".$row['sorting']."\">
				  </div>
                ";
				}

				if ($this->CLASS['config']->content->collapsecontent) {
                    echo "
				  <div class=\"form-check\">
					<input class=\"form-check-input\" type=\"checkbox\" id=\"contentcollapsed\" name=\"contentcollapsed\" ". ($row['contentcollapsed'] == 1 ? "checked" : "").">
					<label for=\"contentcollapsed\">&nbsp;&nbsp;".$this->CLASS['translate']->_('Initially show all content on this page collapsed?')."</label>
				  </div>
                ";
				}

				$this->CLASS['hooks']->setHook("kr_content","edit_page","show");

				echo '</div>';
                echo '<div class="tab-pane fade" id="permissions" role="tabpanel" aria-labelledby="permissions-tab">';

				// check for inheritrights
				$inheritrights = $this->CLASS['knowledgeroot']->getInheritRights($_SESSION['cid']);

				$show_rights = 0;
				if((is_array($inheritrights) && $inheritrights['subinheritrightseditable'] == 1) || $inheritrights == false) {
					$show_rights = 1;
				}

				//check rights
				if((!empty($_SESSION['userid']) && $show_rights == 1) || (isset($_SESSION['admin']) && $_SESSION['admin'] == 1)) {
					echo $this->CLASS['knowledgeroot']->editRightPanel("tree",$row['id'],$row['owner'],$row['group'],$row['userrights'].$row['grouprights'].$row['otherrights'],1);
				}

				echo '</div>';
                echo '<div class="tab-pane fade" id="inherit-permissions" role="tabpanel" aria-labelledby="inherit-permissions-tab">';

				if(isset ($_SESSION['admin']) and $_SESSION['admin'] == 1) {
					echo $this->CLASS['knowledgeroot']->editRightPanelSubInherit("tree",$row['id'],$row['subinheritrights'],$row['subinheritrightseditable'],$row['subinheritrightsdisable'],$row['subinheritowner'],$row['subinheritgroup'],$row['subinherituserrights'],$row['subinheritgrouprights'],$row['subinheritotherrights']);
				}

				echo '</div>';
				$this->CLASS['hooks']->setHook("kr_content","edit_page","show_tab");
                echo '</div>';
                echo '</div>';
                echo '</div>';

                echo '<p /><p />';
				echo "<button class=\"btn btn-primary\" type=\"submit\" name=\"submit\" value=\"submit\">".$this->CLASS['translate']->_('save changes')."</button>\n";
				echo "</form>";
			}

			$this->CLASS['hooks']->setHook("kr_content","edit_page","end");
		}
	}

	/**
	 * this function create form for edit content
	 */
	function edit_content() {
		if($this->CLASS['knowledgeroot']->getContentRights($_GET['eid'],$_SESSION['userid']) == 2) {
			$this->CLASS['hooks']->setHook("kr_content","edit_content","start");

			// save that this content is edited
			$this->CLASS['knowledgeroot']->openContent($_GET['eid'],$_SESSION['userid']);

			// show warning if another user is editing this content
			if($this->CLASS['knowledgeroot']->isOpenContent($_GET['eid'],$_SESSION['userid'])) {
				echo '
				<div class="alert alert-danger" role="alert">
				'.$this->CLASS['translate']->_('Another user is already editing this content!').'
				</div>
				';
			}

			// show content
			echo "<form action=\"index.php\" method=\"post\">";

			echo "<input type=\"hidden\" name=\"editid\" value=\"".$_GET['eid']."\" />\n";
			echo "<input type=\"hidden\" name=\"submit\" value=\"submit\" />\n";

			echo "<button class=\"btn btn-primary\" type=\"submit\" name=\"save\" value=\"save\">".$this->CLASS['translate']->_('save')."</button>\n";
			echo "<button class=\"btn btn-primary\" type=\"submit\" name=\"saveandclose\" value=\"saveandclose\">".$this->CLASS['translate']->_('save and close')."</button>\n";
			echo "<button class=\"btn btn-secondary\" type=\"submit\" name=\"close\" value=\"close\">".$this->CLASS['translate']->_('close')."</button>\n";

            echo "<p />";

			$res = $this->CLASS['db']->query(sprintf("SELECT * FROM content WHERE id=%d ORDER BY id ASC",$_GET['eid']));

			while($row = $this->CLASS['db']->fetch_assoc($res)) {
				echo "<input type=\"hidden\" name=\"belongsto\" value=\"".$row['belongs_to']."\" />\n";

                echo '<div class="card">';
                echo '
			  <div class="card-header">
				<ul class="nav nav-tabs card-header-tabs" role="tablist">
				  <li class="nav-item">
					<a class="nav-link active" id="content-tab" data-toggle="tab" href="#content" role="tab" aria-controls="content" aria-selected="true">'.$this->CLASS['translate']->_('content').'</a>
				  </li>
				  <li class="nav-item">
					<a class="nav-link" id="permissions-tab" data-toggle="tab" href="#permissions" role="tab" aria-controls="permissions" aria-selected="false">'.$this->CLASS['translate']->_('permissions').'</a>
				  </li>
				  <li class="nav-item">
					<a class="nav-link" id="informations-tab" data-toggle="tab" href="#informations" role="tab" aria-controls="informations" aria-selected="false">'.$this->CLASS['translate']->_('informations').'</a>
				  </li>
				</ul>
			  </div>
			  <div class="card-body">
			  	<div class="tab-content">
			  		<div class="tab-pane fade show active" id="content" role="tabpanel" aria-labelledby="content-tab">
			';

				if ($this->CLASS['config']->content->showtitle) {
                    echo "
                  <div class=\"form-group\">
					<label for=\"content_title\">" . $this->CLASS['translate']->_('Title') . "</label>
					<input type=\"text\" class=\"form-control\" id=\"content_title\" aria-describedby=\"title\" name=\"title\" value=\"" . $row['title'] . "\">
				  </div>
                ";
                }

				// position
				$resp = $this->CLASS['db']->squery("SELECT id, title FROM content WHERE belongs_to=%d AND deleted=0 ORDER BY sorting ASC", $_SESSION['cid']);
				$cnt = $this->CLASS['db']->num_rows($resp);

				if($cnt > 0) {
					echo '<div class="form-group">';
					echo '<label for="content_position">'.$this->CLASS['translate']->_('Position').'</label>';
					echo '<select name="position" id="content_position" class="form-control form-control-sm">';
					echo "<option value=\"\">" . $this->CLASS['translate']->_('do not change')."</option>";
					echo "<option value=\"first\">&larr; ".$this->CLASS['translate']->_('first')."</option>";
					while($rowp = $this->CLASS['db']->fetch_assoc($resp)) {
						$title = "";
						if($rowp['title'] != "") {
							$title = $rowp['title'];
						} else {
							$title = "Id: " . $rowp['id'];
						}
						//echo '<option value="" disabled="disabled">' . $title . "</option>\n";

						echo "<option value=\"".$rowp['id']."\">&larr; ".sprintf($this->CLASS['translate']->_('after %s'), $title)."</option>";
					}
					echo '</select></div>';
				}

				$this->CLASS['hooks']->setHook("kr_content","edit_content","show");

				// show content in rte editor
				echo $this->CLASS['rte']->show($row['content']);
				echo '</div>';
				echo '<div class="tab-pane fade" id="permissions" role="tabpanel" aria-labelledby="permissions-tab">';
				// check for inheritrights
				$inheritrights = $this->CLASS['knowledgeroot']->getInheritRights($_SESSION['cid']);

				$show_rights = 0;
				if((is_array($inheritrights) && $inheritrights['subinheritrightseditable'] == 1) || $inheritrights == false) {
					$show_rights = 1;
				}

				//check rights
				if((!empty($_SESSION['userid']) && $show_rights == 1) || (isset($_SESSION['admin']) && $_SESSION['admin'] == 1)) {
					echo $this->CLASS['knowledgeroot']->editRightPanel("content",$_GET['eid'],$row['owner'],$row['group'],$row['userrights'].$row['grouprights'].$row['otherrights']);
				}
				echo '</div>';
				echo '<div class="tab-pane fade" id="informations" role="tabpanel" aria-labelledby="informations-tab">';
				$lastChangedBy = $this->CLASS['knowledgeroot']->getOwner($row['lastupdatedby']);
				echo $this->CLASS['translate']->_('created at') . ': ' . $row['createdate'] . '<br />';
				echo $this->CLASS['translate']->_('last changed by') . ': ' . (($lastChangedBy != '') ? $lastChangedBy : $this->CLASS['translate']->_('guest')) . '<br />';
				echo $this->CLASS['translate']->_('last changed at') . ': ' . $row['lastupdated'] . '<br />';
				echo '</div>';
				echo '</div>';
				echo '</div>';
				$this->CLASS['hooks']->setHook("kr_content","edit_content","show_tab");
				echo '</div>';
			}

			$this->CLASS['hooks']->setHook("kr_content","edit_content","show_after");

            echo "<p />";
            echo "<p />";

			echo "<button class=\"btn btn-primary\" type=\"submit\" name=\"save\" value=\"save\">".$this->CLASS['translate']->_('save')."</button>\n";
			echo "<button class=\"btn btn-primary\" type=\"submit\" name=\"saveandclose\" value=\"saveandclose\">".$this->CLASS['translate']->_('save and close')."</button>\n";
			echo "<button class=\"btn btn-secondary\" type=\"submit\" name=\"close\" value=\"close\">".$this->CLASS['translate']->_('close')."</button>\n";

			echo "</form>\n";
            echo "<p />";

			$this->CLASS['hooks']->setHook("kr_content","edit_content","end");
		}
	}

	/**
	 * this function shows search
	 */
	function show_search() {
		$this->CLASS['hooks']->setHook("kr_content","show_search","start");

		if(!isset($_SESSION['search'][$_GET['key']])) $searchword = "";
		else $searchword = trim($_SESSION['search'][$_GET['key']]);

		if($this->CLASS['config']->base->charset == "utf8") {
			$charset = "UTF-8";
		} else {
			$charset = $this->CLASS['config']->base->charset;
		}

		// get searchwords
                $searchword = str_replace('&quot;','\"',$searchword);
                $originalsearchword = $searchword;

		// init search string parser
		$searchParser = new Search_String_Parser();
		$searchParser->parse($searchword);

                preg_match_all('/"(.*)"/U', stripslashes($searchword), $arrSearchGroup);

		echo $this->CLASS['translate']->_('Search for') . " <b>'" . stripslashes($searchword) . "'</b><br /><br />";

		// contentsearch
		echo "<h3>" . $this->CLASS['translate']->_('Content') . ":</h3>\n";

		$this->CLASS['hooks']->setHook("kr_content","show_search","content_search_start");

		$_SESSION['userid'] = $_SESSION['userid'] == "" ? 0 : $_SESSION['userid'];
		$_SESSION['groupid'] = $_SESSION['userid'] == "" ? 0 : $_SESSION['groupid'];

		// create whereclause for search of content
		$where = "";

		// and search condition
		foreach($searchParser->getSearchAnd() as $key => $value) {
			$valueHtml = @htmlentities($value,ENT_NOQUOTES,$charset);
			$like = 'like';

			if($this->CLASS['db']->dbtype == "pgsql") {
				$like = 'ilike';
			}

			$where .= sprintf("(content %s '%%%s%%' OR content %s '%%%s%%' OR title %s '%%%s%%' OR title %s '%%%s%%') AND ", $like, $value, $like, $valueHtml, $like, $value, $like, $valueHtml);
		}

		// and not search condition
		foreach($searchParser->getSearchNot() as $key => $value) {
			$valueHtml = @htmlentities($value,ENT_NOQUOTES,$charset);
			$like = 'not like';

			if($this->CLASS['db']->dbtype == "pgsql") {
				$like = 'not ilike';
			}

			$where .= sprintf("(content %s '%%%s%%' AND content %s '%%%s%%' AND title %s '%%%s%%' AND title %s '%%%s%%') AND ", $like, $value, $like, $valueHtml, $like, $value, $like, $valueHtml);
		}

		// or search condition
		foreach($searchParser->getSearchOr() as $part) {
			$where .= "(";
			foreach($part as $key => $value) {
				$valueHtml = @htmlentities($value,ENT_NOQUOTES,$charset);
				$like = 'like';

				if($this->CLASS['db']->dbtype == "pgsql") {
					$like = 'ilike';
				}

				$where .= sprintf("(content %s '%%%s%%' or content %s '%%%s%%' or title %s '%%%s%%' or title %s '%%%s%%') OR ", $like, $value, $like, $valueHtml, $like, $value, $like, $valueHtml);
			}

			$where = substr($where, 0, strlen($where)-3);
			$where .= ") AND ";
		}

		$where = substr($where, 0, strlen($where)-4);

		if(isset($_SESSION['admin']) && $_SESSION['admin'] == 1) {
			if($this->CLASS['db']->dbtype == "pgsql") {
				$sql = sprintf("SELECT id,belongs_to,content,title FROM content WHERE (%s) AND deleted=0",$where);
			} else {
				$sql = sprintf("SELECT id,belongs_to,content,title FROM content WHERE (%s) AND deleted=0",$where);
			}
		} else {
			// get groups from user
			$res = $this->CLASS['db']->query(sprintf("SELECT groupid FROM user_group WHERE userid=%d",$_SESSION['userid']));
			$orclause = "";
			while($rowuser = $this->CLASS['db']->fetch_assoc($res)) {
				$orclause .= sprintf("OR (c.".$this->CLASS['db']->quoteIdentifier("group")."=%d AND c.grouprights > 0) ",$rowuser['groupid']);
				$orclause .= sprintf("OR (a.owner_group_id=%d AND a.owner_group='g' AND a.rights > 0) ",$rowuser['groupid']);

			}

			// add orclause for userright
                        $orclause .= sprintf("OR (a.owner_group_id=%d  AND owner_group='o' AND a.rights > 0) ",$_SESSION['userid']);

			$sql = sprintf("SELECT c.id,c.belongs_to,c.content,c.title FROM content c LEFT JOIN access a ON a.belongs_to=c.id AND a.table_name='content' WHERE (%s) AND c.deleted=0 AND ((c.otherrights > 0) OR (c.".$this->CLASS['db']->quoteIdentifier("group")."=%d AND c.grouprights > 0) %sOR (c.owner=%d AND c.userrights>0))",$where,$_SESSION['groupid'],$orclause,$_SESSION['userid']);
		}

		$res = $this->CLASS['db']->query($sql);
		$anz = $this->CLASS['db']->num_rows($res);

		if($anz != 0) {
			$x = 0;
			while($row = $this->CLASS['db']->fetch_assoc($res)) {
				if($this->CLASS['knowledgeroot']->checkRecursivPerm($row['belongs_to'],$_SESSION['userid']) != 0) {
					echo $this->CLASS['path']->getPath($row['belongs_to']) . "&nbsp;/&nbsp;". (($row['title'] != "") ? "\"<a href=\"index.php?highlight=".urlencode(str_replace(" ",",",$originalsearchword))."&amp;id=" . $row['belongs_to'] . "#" . $row['id'] . "\">".$row['title']."</a>\"&nbsp;" : "") ."[<a href=\"index.php?highlight=".urlencode(str_replace(" ",",",$originalsearchword))."&amp;id=" . $row['belongs_to'] . "&amp;oc=".$row['id']."#" . $row['id'] . "\">" . $this->CLASS['translate']->_('show') . "</a>]<br />\n";
					$x++;
				}
			}

			if($x == 0) {
				echo $this->CLASS['translate']->_('Nothing found') . "<br />\n";
			}
		} else {
			echo $this->CLASS['translate']->_('Nothing found');
		}

		$this->CLASS['hooks']->setHook("kr_content","show_search","content_search_end");

		echo "<hr>\n";

		// tree search
		echo "<h3>" . $this->CLASS['translate']->_('Menu') . ":</h3>\n";

		$this->CLASS['hooks']->setHook("kr_content","show_search","tree_search_start");

		// create whereclause for search of tree
		$where = "";

		// and search condition
		foreach($searchParser->getSearchAnd() as $key => $value) {
			$like = 'like';

			if($this->CLASS['db']->dbtype == "pgsql") {
				$like = 'ilike';
			}

			$where .= sprintf("(title %s '%%%s%%') AND ", $like, $value);
		}

		// and not search condition
		foreach($searchParser->getSearchNot() as $key => $value) {
			$like = 'not like';

			if($this->CLASS['db']->dbtype == "pgsql") {
				$like = 'not ilike';
			}

			$where .= sprintf("(title %s '%%%s%%') AND ", $like, $value);
		}

		// or search condition
		foreach($searchParser->getSearchOr() as $part) {
			$where .= "(";
			foreach($part as $key => $value) {
				$like = 'like';

				if($this->CLASS['db']->dbtype == "pgsql") {
					$like = 'ilike';
				}

				$where .= sprintf("(title %s '%%%s%%') OR ", $like, $value);
			}

			$where = substr($where, 0, strlen($where)-3);
			$where .= ") AND ";
		}

		$where = substr($where, 0, strlen($where)-4);

		if($this->CLASS['db']->dbtype == "pgsql") {
			$sql = sprintf("SELECT id,belongs_to,title FROM tree WHERE %s",$where);
		} else {
			$sql = sprintf("SELECT id,belongs_to,title FROM tree WHERE %s",$where);
		}

		$res = $this->CLASS['db']->query($sql);
		$anz = $this->CLASS['db']->num_rows($res);

		if($anz != 0) {
			$x = 0;
			while($row = $this->CLASS['db']->fetch_assoc($res)) {
				if($this->CLASS['knowledgeroot']->checkRecursivPerm($row['id'], $_SESSION['userid']) != 0) {
				echo $this->CLASS['path']->getPath($row['id']) . "<br />\n";
				$x++;
				}
			}

			if($x == 0) {
				echo $this->CLASS['translate']->_('Nothing found') . "<br />\n";
			}
		} else {
			echo $this->CLASS['translate']->_('Nothing found') . "<br />\n";
		}

		$this->CLASS['hooks']->setHook("kr_content","show_search","tree_search_end");

		echo "<hr>\n";

		// file search
		echo "<h3>" . $this->CLASS['translate']->_('Files') . ":</h3>\n";

		$this->CLASS['hooks']->setHook("kr_content","show_search","file_search_start");

		// create whereclause for search of tree
		$where = "";

		// and search condition
		foreach($searchParser->getSearchAnd() as $key => $value) {
			$like = 'like';

			if($this->CLASS['db']->dbtype == "pgsql") {
				$like = 'ilike';
			}

			$where .= sprintf("(filename %s '%%%s%%') AND ", $like, $value);
		}

		// and not search condition
		foreach($searchParser->getSearchNot() as $key => $value) {
			$like = 'not like';

			if($this->CLASS['db']->dbtype == "pgsql") {
				$like = 'not ilike';
			}

			$where .= sprintf("(filename %s '%%%s%%') AND ", $like, $value);
		}

		// or search condition
		foreach($searchParser->getSearchOr() as $part) {
			$where .= "(";
			foreach($part as $key => $value) {
				$like = 'like';

				if($this->CLASS['db']->dbtype == "pgsql") {
					$like = 'ilike';
				}

				$where .= sprintf("(filename %s '%%%s%%') OR ", $like, $value);
			}

			$where = substr($where, 0, strlen($where)-3);
			$where .= ") AND ";
		}

		$where = substr($where, 0, strlen($where)-4);

		if($this->CLASS['db']->dbtype == "pgsql") {
			$sql = sprintf("SELECT f.id as id,t.id as tid,f.filename,f.filesize, to_char(f.date,'DD.MM.YYYY HH24:MI:SS') AS dateform, f.owner AS owner FROM files f, tree t, content c WHERE f.belongs_to = c.id AND c.belongs_to = t.id AND f.deleted=0 AND %s",$where);
		} elseif($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") {
			$sql = sprintf("SELECT f.id as id,t.id as tid,f.filename,f.filesize, strftime('%%d.%%m.%%Y %%H:%%M:%%S',f.date) AS dateform, f.owner AS owner FROM files f, tree t, content c WHERE f.belongs_to = c.id AND c.belongs_to = t.id AND f.deleted=0 AND %s",$where);
		} else {
			$query = "SELECT f.id AS id, t.id AS tid, f.filename, f.filesize,
				DATE_FORMAT(f.date,'%d.%m.%Y %H:%i:%s') AS dateform, f.owner AS owner
				FROM (files f, tree t, content c)";
			$sql = $query.sprintf(" WHERE f.belongs_to = c.id AND c.belongs_to = t.id AND f.deleted=0 AND %s", $where);
		}

		$res = $this->CLASS['db']->query($sql);
		$anz = $this->CLASS['db']->num_rows($res);

		if($anz != 0) {
			$x = 0;
			while($row = $this->CLASS['db']->fetch_assoc($res)) {
				// get file information
				$title = "";
				if($row['owner'] == NULL || $row['owner'] == 0 || $row['owner'] == "") {
					$title = $row['dateform'];
				} else {
					$title = $this->CLASS['knowledgeroot']->getOwner($row['owner']) . " - " . $row['dateform'];
				}

				// show file
				if($this->CLASS['knowledgeroot']->checkRecursivPerm($row['tid'], $_SESSION['userid']) != 0) {
					echo $this->CLASS['path']->getPath($row['tid']) . "&nbsp;/\n";
					echo "<a href=\"index.php?download=".$row['id']."\" title=\"".$title."\"><img src=\"images/file.gif\" class=\"upload\" /> ".$row['filename']."</a>&nbsp;<font class=\"text\">[".getfilesize($row['filesize'])."]&nbsp;[".$title."]</font><br />\n";
					$x++;
				}
			}

			if($x == 0) {
				echo $this->CLASS['translate']->_('Nothing found') . "<br />\n";
			}
		} else {
			echo $this->CLASS['translate']->_('Nothing found') . "<br />\n";
		}

		$this->CLASS['hooks']->setHook("kr_content","show_search","file_search_end");

		$this->CLASS['hooks']->setHook("kr_content","show_search","end");
	}

	/**
	 * form for login
	 */
	function show_login() {
		$this->CLASS['hooks']->setHook("kr_content","show_login","start");

		echo '
<div class="card">
  <div class="card-header">
    '.$this->CLASS['translate']->_('login').'
  </div>
  <div class="card-body">

		<form action="index.php" method="post" name="loginformular">
		<input type="hidden" name="'.session_name().'" value="'.session_id().'" />
		<input type="hidden" name="login" value="login" />
		
		  <div class="form-group">
			<label for="user">'.$this->CLASS['translate']->_('user').'</label>
			<input type="text" class="form-control" name="user" id="user" aria-describedby="emailHelp" placeholder="'.$this->CLASS['translate']->_('user').'">
		  </div>
		  <div class="form-group">
			<label for="password">'.$this->CLASS['translate']->_('password').'</label>
			<input type="password" class="form-control" name="password" id="password" placeholder="'.$this->CLASS['translate']->_('password').'">
		  </div>
		  ';

		$this->CLASS['hooks']->setHook("kr_content","show_login","before_submit");

		echo '<button class="btn btn-primary" type="submit" name="loginbutton">'.$this->CLASS['translate']->_('login').'</button>';

		$this->CLASS['hooks']->setHook("kr_content","show_login","after_submit");

		echo '
		</form>

  </div>
</div>
		';

		echo '<script type="text/javascript">
		<!--
		document.loginformular.user.focus();
		//-->
		</script>'."\n";

		$this->CLASS['hooks']->setHook("kr_content","show_login","end");
	}

	/**
	 * form for create root
	 */
	function create_root() {
		$this->CLASS['hooks']->setHook("kr_content","create_root","start");

		// rechte checken -> adminrechte
		echo '
		<form action="index.php" method="post">
		<input type="hidden" name="action" value="createroot" />
		<input type="hidden" name="'.session_name().'" value="'.session_id().'" />

		<button class="btn btn-primary" type="submit" name="submit">'.$this->CLASS['translate']->_('create').'</button>

		<p />

		<div class="card">

		  <div class="card-header">
			<ul class="nav nav-tabs card-header-tabs" role="tablist">
			  <li class="nav-item">
				<a class="nav-link active" id="content-tab" data-toggle="tab" href="#content" role="tab" aria-controls="content" aria-selected="true">'.$this->CLASS['translate']->_('site').'</a>
			  </li>
			  <li class="nav-item">
				<a class="nav-link" id="permissions-tab" data-toggle="tab" href="#permissions" role="tab" aria-controls="permissions" aria-selected="false">'.$this->CLASS['translate']->_('permissions').'</a>
			  </li>
			  <li class="nav-item">
				<a class="nav-link" id="inherit-permissions-tab" data-toggle="tab" href="#inherit-permissions" role="tab" aria-controls="inherit-permissions" aria-selected="false">'.$this->CLASS['translate']->_('inherit permissions').'</a>
			  </li>
			</ul>
		  </div>
		  <div class="card-body">
			<div class="tab-content">
			<div class="tab-pane fade show active" id="content" role="tabpanel" aria-labelledby="content-tab">
			
			  <div class="form-group">
				<label for="titleText">'.$this->CLASS['translate']->_('name').'</label>
				<input type="text" class="form-control" id="titleText" aria-describedby="titleText" name="title">
			  </div>
			  
			  <div class="form-group">
				<label for="aliasText">'.$this->CLASS['translate']->_('alias').'</label>
				<input type="text" class="form-control" id="aliasText" aria-describedby="aliasText" name="alias">
			  </div>
			  ';

        // check for tooltip
		if($this->CLASS['config']->tree->edittooltiptext == 1) {
            echo '
			  <div class="form-group">
				<label for="tooltipText">' . $this->CLASS['translate']->_('tooltip') . '</label>
				<input type="text" class="form-control" id="tooltipText" aria-describedby="tooltipText" name="tooltip">
			  </div>
			';
        }

		// check for order
		if($this->CLASS['config']->tree->order == 1) {
			echo '
			  <div class="form-group">
				<label for="sortingText">' . $this->CLASS['translate']->_('priority') . '</label>
				<input type="text" class="form-control" id="sortingText" aria-describedby="sortingText" name="sorting" value="0">
			  </div>
			';
		}

		echo '</div>';

		if(!empty($_SESSION['userid'])) {
			echo '<div class="tab-pane fade" id="permissions" role="tabpanel" aria-labelledby="permissions-tab">';
			echo $this->CLASS['knowledgeroot']->rightpanel($_SESSION['userid']);
			echo "</div>\n";
			echo '<div class="tab-pane fade" id="inherit-permissions" role="tabpanel" aria-labelledby="inherit-permissions-tab">';
			echo $this->CLASS['knowledgeroot']->rightpanelsubinherit($_SESSION['userid']);
			echo "</div>\n";
		}

		echo '</div></div></div></form>
		';

		$this->CLASS['hooks']->setHook("kr_content","create_root","end");
	}

	/**
	 * form for options
	 */
	function show_options() {
		$this->CLASS['hooks']->setHook("kr_content","show_options","start");

		echo '
<div class="card">
  <div class="card-header">
    '.$this->CLASS['translate']->_('change options').'
  </div>
  <div class="card-body">
		<form action="index.php" method="post">
		<input type="hidden" name="action" value="options" />

		  <div class="form-group">
			<label for="password">'.$this->CLASS['translate']->_('new password').'</label>
			<input type="password" class="form-control" name="password" id="password" placeholder="'.$this->CLASS['translate']->_('new password').'">
		  </div>
		  <div class="form-group">
			<label for="password1">'.$this->CLASS['translate']->_('confirm password').'</label>
			<input type="password" class="form-control" name="password1" id="password1" placeholder="'.$this->CLASS['translate']->_('confirm password').'">
		  </div>
		  <div class="form-group">
			<label for="theme">'.$this->CLASS['translate']->_('theme').'</label>
			'.$this->CLASS['themes']->theme_dropdown($_SESSION['theme']).'
		  </div>
		  <div class="form-group">
			<label for="language">'.$this->CLASS['translate']->_('language').'</label>
			'.$this->CLASS['language']->lang_dropdown("language",$_SESSION['language'],"", false).'
		  </div>

		<button class="btn btn-primary" type="submit" name="submit">'.$this->CLASS['translate']->_('save').'</button>

		</form>
	</div>
</div>
		';

		$this->CLASS['hooks']->setHook("kr_content","show_options","end");
	}

	/**
	 * list users
	 */
	function list_users() {
		$this->CLASS['hooks']->setHook("kr_content","list_users","start");

		echo '
		<div class="card">
		  <div class="card-header">
			'.$this->CLASS['translate']->_('user').'
		  </div>
		  <div class="card-body">
		  <a class="btn btn-primary" href="index.php?action=adduser">'.$this->CLASS['translate']->_('add user').'</a>
		  <p />
		<table id="userList" class="table table-striped table-hover table-sm">
			<thead>
			<tr>
					<th>'.$this->CLASS['translate']->_('id').'</th>
					<th>'.$this->CLASS['translate']->_('name').'</th>
					<th>'.$this->CLASS['translate']->_('default group').'</th>
					<th>'.$this->CLASS['translate']->_('default rights').'</th>
					<th>'.$this->CLASS['translate']->_('admin').'</th>
					<th>'.$this->CLASS['translate']->_('edit rights').'</th>
					<th>'.$this->CLASS['translate']->_('enabled').'</th>
					<th></th>
			</tr>
			</thead>
			<tbody>
		';

		$res = $this->CLASS['db']->query("SELECT * FROM users ORDER BY name");
		while($row = $this->CLASS['db']->fetch_assoc($res)) {
			echo "
			<tr>
				<td>".$row['id']."</td>
				<td>".$row['name']."</td>
				<td>".$this->CLASS['knowledgeroot']->getGroup($row['defaultgroup'])."</td>
				<td>".$row['defaultrights']."</td>
				<td>".$this->CLASS['knowledgeroot']->yesno($row['admin'])."</td>
				<td>".$this->CLASS['knowledgeroot']->yesno($row['rightedit'])."</td>
				<td>".$this->CLASS['knowledgeroot']->yesno($row['enabled'])."</td>
				<td>
					<a class=\"btn btn-secondary\" href=\"index.php?action=edituser&amp;uid=".$row['id']."\">".$this->CLASS['translate']->_('edit')."</a>
					<a class=\"btn btn-danger\" href=\"index.php?action=deluser&amp;uid=".$row['id']."\" onclick=\"return confirm('" . $this->CLASS['translate']->_('Do you really want to delete this user?') . "');\">".$this->CLASS['translate']->_('delete')."</a>
				</td>
			</tr>\n";
		}

		echo '
		</tbody>
		</table>
			</div>
		</div>
		';

		echo '<p />';

        echo '
		<div class="card">
		  <div class="card-header">
			'.$this->CLASS['translate']->_('groups').'
		  </div>
		  <div class="card-body">
		  <a class="btn btn-primary" href="index.php?action=addgroup">'.$this->CLASS['translate']->_('add group').'</a>
		  <p />
		<table id="userList" class="table table-striped table-hover table-sm">
			<thead>
			<tr>
					<th>'.$this->CLASS['translate']->_('id').'</th>
					<th>'.$this->CLASS['translate']->_('name').'</th>
					<th></th>
			</tr>
			</thead>
			<tbody>
		';

        $res = $this->CLASS['db']->query("SELECT * FROM groups ORDER BY name");
        while($row = $this->CLASS['db']->fetch_assoc($res)) {
            echo "
			<tr>
				<td>".$row['id']."</td>
				<td>".$row['name']."</td>
				<td>
					<a class=\"btn btn-secondary\" href=\"index.php?action=editgroup&amp;gid=".$row['id']."\">".$this->CLASS['translate']->_('edit')."</a>
					<a class=\"btn btn-danger\" href=\"index.php?action=delgroup&amp;gid=".$row['id']."\" onclick=\"return confirm('" . $this->CLASS['translate']->_('Do you really want to delete this group?') . "');\">".$this->CLASS['translate']->_('delete')."</a>
				</td>
			</tr>\n";
        }

        echo '
		</tbody>
		</table>
			</div>
		</div>
		';

		$this->CLASS['hooks']->setHook("kr_content","list_users","end");
	}

	/**
	 * add user
	 */
	function add_user() {
		$this->CLASS['hooks']->setHook("kr_content","add_user","start");

		echo '
		<div class="card">
		  <div class="card-header">
			'.$this->CLASS['translate']->_('add user').'
		  </div>
		  <div class="card-body">
		
		<form action="index.php" method="post" name="adduserformular">
		<input type="hidden" name="action" value="adduser">

		  <div class="form-group">
			<label for="name">' . $this->CLASS['translate']->_('name') . '</label>
			<input type="text" class="form-control" aria-describedby="name" name="name" value="">
		  </div>
		  
		  <div class="form-group">
			<label for="password">' . $this->CLASS['translate']->_('password') . '</label>
			<input type="password" class="form-control" aria-describedby="password" name="password" value="">
		  </div>
		  
		  <div class="form-group">
			<label for="theme">' . $this->CLASS['translate']->_('theme') . '</label>
			' . $this->CLASS['themes']->theme_dropdown() . '
		  </div>
		  
		  <div class="form-group">
			<label for="default group">' . $this->CLASS['translate']->_('default group') . '</label>
			' . $this->CLASS['knowledgeroot']->groupdropdown("defaultgroup") . '
		  </div>
		  
		  <div class="form-group">
			<label for="default group">' . $this->CLASS['translate']->_('admin') . '</label>
			' . $this->CLASS['knowledgeroot']->yesnodropdown("admin") . '
		  </div>
		  
		  <div class="form-group">
			<label for="edit rights">' . $this->CLASS['translate']->_('edit rights') . '</label>
			' . $this->CLASS['knowledgeroot']->yesnodropdown("enabled") . '
		  </div>
		  
		  <div class="form-group">
			<label for="enabled">' . $this->CLASS['translate']->_('enabled') . '</label>
			' . $this->CLASS['knowledgeroot']->yesnodropdown("enabled") . '
		  </div>
		  
		  <div class="form-group">
			<label for="groups">' . $this->CLASS['translate']->_('groups') . '</label>
			' . $this->CLASS['knowledgeroot']->groupDropDown("groups[]","",true) . '
		  </div>
		  
		  <h3>'.$this->CLASS['translate']->_('default rights').'</h3>
		  
		  <div class="form-group">
			<label for="user">' . $this->CLASS['translate']->_('user') . '</label>
			'.$this->CLASS['knowledgeroot']->rightDropDown("userrights",2).'
		  </div>
		  
		  <div class="form-group">
			<label for="group">' . $this->CLASS['translate']->_('group') . '</label>
			'.$this->CLASS['knowledgeroot']->rightDropDown("grouprights",1).'
		  </div>
		  
		  <div class="form-group">
			<label for="others">' . $this->CLASS['translate']->_('others') . '</label>
			'.$this->CLASS['knowledgeroot']->rightDropDown("otherrights",1).'
		  </div>
		  
		  <div class="form-group">
		  	<button class="btn btn-primary" name="submit" type="submit">'.$this->CLASS['translate']->_('save').'</button>
		  </div>
		';

		$this->CLASS['hooks']->setHook("kr_content","add_user","show");

		echo '
			</div>
		</div>
		';

		$this->CLASS['hooks']->setHook("kr_content","add_user","end");
	}

	/**
	* edit user
	*/
	function edit_user() {
		$this->CLASS['hooks']->setHook("kr_content","edit_user","start");

		$res = $this->CLASS['db']->query(sprintf("SELECT * FROM users WHERE id=%d",$_GET['uid']));
		$anz = $this->CLASS['db']->num_rows($res);

		if($anz == 1) {
			$row = $this->CLASS['db']->fetch_assoc($res);

			//fetch groups
			$res = $this->CLASS['db']->query(sprintf("SELECT * FROM user_group WHERE userid=%d",$row['id']));
			$x = 0;
			$grouparr = array();
			while($rowgroup = $this->CLASS['db']->fetch_assoc($res)) {
				$grouparr[$x] = $rowgroup['groupid'];
				$x++;
			}

			echo '
				<div dojoType="dijit.TitlePane" title="<b>'.$this->CLASS['translate']->_('edit user').'</b>">

				<form action="index.php" method="post">
				<input type="hidden" name="action" value="edituser" />
				<input type="hidden" name="uid" value="'.$row['id'].'" />

				<table border="0" cellpadding="1" cellspacing="3">
				<tr><td>'.$this->CLASS['translate']->_('name').': </td><td><input dojoType="dijit.form.TextBox" type="text" name="name" value="'.$row['name'].'" /></td></tr>
				<tr><td>'.$this->CLASS['translate']->_('password').': </td><td><input dojoType="dijit.form.TextBox" type="password" name="password" value="" /></td></tr>
				<tr><td>'.$this->CLASS['translate']->_('theme').': </td><td>' . $this->CLASS['themes']->theme_dropdown($row['theme']) . '</td></tr>
				<tr><td>'.$this->CLASS['translate']->_('default group').': </td><td>' . $this->CLASS['knowledgeroot']->groupdropdown("defaultgroup",$row['defaultgroup']) . '</td></tr>
				<tr><td>'.$this->CLASS['translate']->_('admin').': </td><td>' . $this->CLASS['knowledgeroot']->yesnodropdown("admin", $row['admin']) . '</td></tr>
				<tr><td>'.$this->CLASS['translate']->_('edit rights').': </td><td>' . $this->CLASS['knowledgeroot']->yesnodropdown("rightedit",$row['rightedit']) . '</td></tr>
				<tr><td>'.$this->CLASS['translate']->_('enabled').': </td><td>' . $this->CLASS['knowledgeroot']->yesnodropdown("enabled",$row['enabled']) . '</td></tr>
				<tr><td valign="top">'.$this->CLASS['translate']->_('groups').': </td><td>' . $this->CLASS['knowledgeroot']->groupDropDown("groups[]","",true,$grouparr) . ' </td></tr>
				<tr><td>'.$this->CLASS['translate']->_('default rights').': </td><td></td></tr>
				<tr><td>&nbsp; '.$this->CLASS['translate']->_('user').': </td><td>'.$this->CLASS['knowledgeroot']->rightDropDown("userrights",substr($row['defaultrights'],0,1)).'</td></tr>
				<tr><td>&nbsp; '.$this->CLASS['translate']->_('group').': </td><td>'.$this->CLASS['knowledgeroot']->rightDropDown("grouprights",substr($row['defaultrights'],1,1)).'</td></tr>
				<tr><td>&nbsp; '.$this->CLASS['translate']->_('others').': </td><td>'.$this->CLASS['knowledgeroot']->rightDropDown("otherrights",substr($row['defaultrights'],2,1)).'</td></tr>
			';

			$this->CLASS['hooks']->setHook("kr_content","edit_user","show");

			echo '
				<tr><td></td><td><button dojoType="dijit.form.Button" type="submit" name="submit">'.$this->CLASS['translate']->_('save').'</button></td></tr>
				</table>
				</form>
				</div>
			';
		}

		$this->CLASS['hooks']->setHook("kr_content","edit_user","end");
	}

	/**
	 * add group
	 */
	function add_group() {
		$this->CLASS['hooks']->setHook("kr_content","add_group","start");

		echo '
		<div class="card">
		  <div class="card-header">
			'.$this->CLASS['translate']->_('add group').'
		  </div>
		  <div class="card-body">
		
		<form action="index.php" method="post" name="addgroupformular">
			<input type="hidden" name="action" value="addgroup" />

		  <div class="form-group">
			<label for="name">' . $this->CLASS['translate']->_('name') . '</label>
			<input type="text" class="form-control" aria-describedby="name" name="name" value="">
		  </div>
		';

		$this->CLASS['hooks']->setHook("kr_content","add_group","show");

		echo '
		  <div class="form-group">
		  	<button class="btn btn-primary" name="submit" type="submit">'.$this->CLASS['translate']->_('save').'</button>
		  </div>
			
				</form>
		  </div>
		 </div>
		';

		$this->CLASS['hooks']->setHook("kr_content","add_group","end");
	}

	/**
	 * edit group
	 */
	function edit_group() {
		$this->CLASS['hooks']->setHook("kr_content","edit_group","start");

		$res = $this->CLASS['db']->query(sprintf("SELECT * FROM groups WHERE id=%d",$_GET['gid']));
		$anz = $this->CLASS['db']->num_rows($res);

		if($anz == 1) {
			$row = $this->CLASS['db']->fetch_assoc($res);
			echo '
		<div class="card">
		  <div class="card-header">
			'.$this->CLASS['translate']->_('add group').'
		  </div>
		  <div class="card-body">
		
			<form action="index.php" method="post">
			<input type="hidden" name="action" value="editgroup" />
			<input type="hidden" name="gid" value="'.$row['id'].'" />

		  <div class="form-group">
			<label for="name">' . $this->CLASS['translate']->_('name') . '</label>
			<input type="text" class="form-control" aria-describedby="name" name="name" value="'.$row['name'].'">
		  </div>
			';

			$this->CLASS['hooks']->setHook("kr_content","edit_group","show");

			echo '
		  <div class="form-group">
		  	<button class="btn btn-primary" name="submit" type="submit">'.$this->CLASS['translate']->_('save').'</button>
		  </div>
			
				</form>
		  </div>
		 </div>
			';
		}

		$this->CLASS['hooks']->setHook("kr_content","edit_group","end");
	}

	/**
	 * show tree content
	 */
	function show_tree_content($count = 0) {
		// check if page is a symlink
		/* is not a usable feature at the moment so its disabled
		$res = $this->CLASS['db']->query(sprintf("SELECT symlink FROM tree WHERE id=%d",$_SESSION['cid']));
		$anz = $this->CLASS['db']->num_rows($res);

		if($anz == 1) {
			$row = $this->CLASS['db']->fetch_assoc($res);

			if($row['symlink'] != "" && $row['symlink'] != 0) {
				$_SESSION['orginial_cid'] = $_SESSION['cid'];
				$_SESSION['cid'] = $row['symlink'];
			}
		}
		*/

		// isTree removed for symlink
		//if($_SESSION['cid'] != "" && $this->CLASS['path']->isTree($_SESSION['cid']) && $this->CLASS['knowledgeroot']->checkRecursivPerm($_SESSION['cid'], $_SESSION['userid']) != 0) {
		if($_SESSION['cid'] != "" && $this->CLASS['knowledgeroot']->checkRecursivPerm($_SESSION['cid'], $_SESSION['userid']) != 0) {
			$this->CLASS['hooks']->setHook("kr_content","show_tree_content","start");

			// get my rights - possible is 0,1,2
			$mypagerights = $this->CLASS['knowledgeroot']->getPageRights($_SESSION['cid'],$_SESSION['userid']);

			$this->CLASS['hooks']->setHook("kr_content","show_tree_content","show_page_menu_start");

			// show page menu
			echo $this->CLASS['kr_extension']->show_menu("page", $_SESSION['cid'], $mypagerights);

			$this->CLASS['hooks']->setHook("kr_content","show_tree_content","show_page_menu_end");

			//check if userid and groupid is set, if not set to 0
			if($_SESSION['userid'] == "" || $_SESSION['groupid'] == "") {
				$_SESSION['userid'] = 0;
				$_SESSION['groupid'] = 0;
			}

			// get page content collapse info
			$hashkey = md5('contentcollapsed_'.$_SESSION['cid']);
			if(!($treedata = $this->CLASS['cache']->load($hashkey))) {
				$res = $this->CLASS['db']->query(sprintf("SELECT contentcollapsed FROM tree WHERE id=%d",$_SESSION['cid']));
				$anz = $this->CLASS['db']->num_rows($res);
				if($anz == 1) {
					$treedata = $this->CLASS['db']->fetch_assoc($res);
					$this->CLASS['cache']->save($treedata, $hashkey, array('system','contentcollapsed'));
				}
			}

			if($this->CLASS['db']->dbtype == "pgsql") {
				$query = sprintf("SELECT ct.id as id, ct.content as content, ct.title as title, ct.type as type, u.name as lastupdatedby, to_char(ct.lastupdated,'DD.MM.YYYY HH24:MI:SS') as lastupdated, to_char(ct.createdate,'DD.MM.YYYY HH24:MI:SS') as createdate FROM content ct left outer join users u ON ct.lastupdatedby = u.id WHERE belongs_to=%d AND ct.deleted=0 ORDER BY ct.sorting ASC",$_SESSION['cid']);
			} elseif($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") {
				$query = sprintf("SELECT ct.id as id, ct.content as content, ct.title as title, ct.type as type, u.name as lastupdatedby, strftime('%%d.%%m.%%Y %%H:%%M:%%S',ct.lastupdated) as lastupdated, strftime('%%d.%%m.%%Y %%H:%%M:%%S',createdate) as createdate FROM content ct left outer join users u ON ct.lastupdatedby = u.id WHERE belongs_to=%d AND ct.deleted=0 ORDER BY ct.sorting ASC",$_SESSION['cid']);
			} else {
				$query = "SELECT ct.id AS id, ct.content AS content, ct.title AS title, ct.type AS type,
					u.name AS lastupdatedby, DATE_FORMAT(ct.lastupdated,'%d.%m.%Y %H:%i:%s') as lastupdated,
					DATE_FORMAT(ct.createdate,'%d.%m.%Y %H:%i:%s') as createdate
					FROM content ct";
				$query .= sprintf(" LEFT OUTER JOIN users u ON ct.lastupdatedby = u.id
					WHERE belongs_to=%d AND ct.deleted=0
					ORDER BY ct.sorting ASC",
				$_SESSION['cid']);
			}

			$hashkey = md5('content_'.$query);
			if(!($this->CLASS['cache']->test($hashkey))) {
				$res = $this->CLASS['db']->query($query);

				$data = array();
				while($row = $this->CLASS['db']->fetch_assoc($res)) {
					$data[] = $row;
				}

				$this->CLASS['cache']->save($data, $hashkey, array('system','content'));
			} else {
				$data = $this->CLASS['cache']->load($hashkey);
			}

			$anz = count($data);

			// needed for up and down arrows
			$firstcontent = 1;
			$maxcontent = $anz;
			$contentcounter = 0;

			// check if some table is on the page
			if($anz != 0) {
				foreach($data as $row) {
					// get content rights
					$mycontentrights = $this->CLASS['knowledgeroot']->getContentRights($row['id'],$_SESSION['userid']);

					// check if it is an extension and if it is enabled
					if($mycontentrights == 0 || (($row['type'] != "" && $row['type'] != "text") && (!isset($this->CLASS['extension'][$row['type']]) || (isset($this->CLASS['extension'][$row['type']]) && $this->CLASS['extension'][$row['type']]['init'] == FALSE)))) {
						$maxcontent--;
						continue;
					}

					$contentcounter++;
					if ($row['title'] == null || $row['title'] == "") {
						if($row['type'] == "" || $row['type'] == "text") {
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
					$showtitle = $this->CLASS['config']->content->showtitle;
					if($this->CLASS['config']->content->collapsecontent) $collapse = $treedata['contentcollapsed'];
					else $collapse = false;

					// check if content should be opened
					if(isset($_GET['oc']) && $_GET['oc'] == $row['id']) {
						$collapse = false;
					}

					$lastUpdated = $this->CLASS['translate']->_('Last modified by')." ". ($row['lastupdatedby'] != null ? $row['lastupdatedby'] : $this->CLASS['translate']->_('guest')) ." ".$this->CLASS['translate']->_('on')." ".$row['lastupdated'];
					$created = $this->CLASS['translate']->_('created').": ". $row['createdate'];

					echo "<div class=\"ContentItem\" id=\"contentid_".$row['id']."\">\n
					<!-- anchor --><a name=\"".$row['id']."\"></a>
<div class=\"card\">
    <h5 class=\"card-header\">
        <a data-toggle=\"collapse\" href=\"#content-".$row['id']."\" aria-expanded=\"".((!$collapse) ? "true":"false")."\" aria-controls=\"content-".$row['id']."\" id=\"content-head-".$row['id']."\" class=\"d-block ".(($collapse) ? "collapsed":"")."\">
            <i class=\"fa fa-chevron-down pull-right\"></i>
            ".$titleText."
        </a>
    </h5>
\n";

					// add up and down arrows
					if($mycontentrights == 2 && $mypagerights == 2) {
						if ($maxcontent != $contentcounter) {
							$this->CLASS['kr_extension']->menu['content']['movedown']['name'] = 'movedown';
							$this->CLASS['kr_extension']->menu['content']['movedown']['nolink'] = '1';
							$this->CLASS['kr_extension']->menu['content']['movedown']['priority'] = '60';
							$this->CLASS['kr_extension']->menu['content']['movedown']['wrap'] = "<div type=\"button\" class=\"btn btn-sm btn-outline-secondary\" onclick=\"location.href='index.php?movedown=".$row['id']."';\"><i class=\"fa fa-arrow-down\" aria-hidden=\"true\"></i></div>";
						} else {
							unset($this->CLASS['kr_extension']->menu['content']['movedown']);
						}

						if ($firstcontent != 1) {
							$this->CLASS['kr_extension']->menu['content']['moveup']['name'] = 'moveup';
							$this->CLASS['kr_extension']->menu['content']['moveup']['nolink'] = '1';
							$this->CLASS['kr_extension']->menu['content']['moveup']['priority'] = '70';
							$this->CLASS['kr_extension']->menu['content']['moveup']['wrap'] = "<div type=\"button\" class=\"btn btn-sm btn-outline-secondary\" onclick=\"location.href='index.php?moveup=".$row['id']."';\"><i class=\"fa fa-arrow-up\" aria-hidden=\"true\"></i></div>";
						} else {
							unset($this->CLASS['kr_extension']->menu['content']['moveup']);
						}
					}


					echo "
    <div id=\"content-".$row['id']."\" class=\"collapse ".((!$collapse) ? "show":"")."\" aria-labelledby=\"content-head-".$row['id']."\">
        <div class=\"card-body\">";

                        $this->CLASS['hooks']->setHook("kr_content","show_tree_content","show_content_menu_start");

                        // show content menu
                        echo $this->CLASS['kr_extension']->show_menu("content",$row['id'],$mypagerights,$mycontentrights,$row['type']);

                        $this->CLASS['hooks']->setHook("kr_content","show_tree_content","show_content_menu_end");

					// check if content is an extension
					if($row['type'] == "" || $row['type'] == "text") {
						// show content
						$this->CLASS['hooks']->setHook("kr_content","show_tree_content","show_content_start");

						// adding colored highlighting
						if(isset($_GET['highlight']) && $_GET['highlight'] != "") {
                                                        $highlight = str_replace('&quot;','\"',$_GET['highlight']);
                                                        $highlight = explode(",", $highlight);

							foreach($highlight as $hkey => $hvalue) {
								$hvalue = mb_ereg_replace('/[^a-zA-Z0-9 \-_]/mu', '', $hvalue);
								$row['content'] = $this->CLASS['highlight']->str_highlight($row['content'], $hvalue, $this->CLASS['highlight']->STR_HIGHLIGHT_STRIPLINKS, '<span class="highlightword">\1</span>');
							}
						}

						echo $row['content'];

						$this->CLASS['hooks']->setHook("kr_content","show_tree_content","show_content_end");
					} else {
						// check if extension is loaded
						if(isset($this->CLASS['extension'][$row['type']]) and $this->CLASS['extension'][$row['type']]['init'] == TRUE) {
							$this->CLASS['hooks']->setHook("kr_content","show_tree_content","show_extension_start");

							// run extension
							echo $this->CLASS['extension'][$row['type']]['class']->show_content($row['id']);

							$this->CLASS['hooks']->setHook("kr_content","show_tree_content","show_extension_end");

							// check if added files should be shown
							if($this->CLASS['extension'][$row['type']]['class']->show_addedfiles != 1) {
								$show_files = 0;
							}
						} else {
							$this->CLASS['hooks']->setHook("kr_content","show_tree_content","show_extension_not_loaded");
							continue;
						}
					}

					if($show_files == 1) {
						$this->CLASS['hooks']->setHook("kr_content","show_tree_content","show_content_files_start");

						// select added files
						$hashkey = md5('files'.$row['id']);
						if(!($this->CLASS['cache']->test($hashkey))) {
							if($this->CLASS['db']->dbtype == "pgsql") {
								$result = $this->CLASS['db']->query(sprintf("SELECT id,filename,filesize,owner, to_char(date,'DD. Mon YYYY HH24:MI:SS') AS dateform FROM files WHERE belongs_to=%d AND deleted=0 ORDER BY id ASC",$row['id']));
							} elseif($this->CLASS['db']->dbtype == "sqlite" || $this->CLASS['db']->dbtype == "sqlite3") {
								$result = $this->CLASS['db']->query(sprintf("SELECT id,filename,filesize,owner, strftime('%%d.%%m.%%Y %%H:%%M:%%S',date) AS dateform FROM files WHERE belongs_to=%d AND deleted=0 ORDER BY id ASC",$row['id']));
							} else {
								$query = "SELECT id,filename,filesize,owner, DATE_FORMAT(date,'%d. %M %Y %H:%i:%s') AS dateform
										FROM files";
								$query .= sprintf(" WHERE belongs_to=%d AND deleted=0
										ORDER BY id ASC",
										$row['id']);
								$result = $this->CLASS['db']->query($query);
							}

							$rows = null;
							while($zeile = $this->CLASS['db']->fetch_assoc($result)) {
								$rows[] = $zeile;
							}

							$this->CLASS['cache']->save($rows, $hashkey, array('system', 'files'));
						} else {
							$rows = $this->CLASS['cache']->load($hashkey);
						}

						// read all select files
						if(is_array($rows)) {
							echo '<div class="alert alert-secondary" style="margin-bottom: 0;">';

							foreach($rows as $zeile) {
								$title = "";
								if($zeile['owner'] == NULL || $zeile['owner'] == 0 || $zeile['owner'] == "") {
									$title = $zeile['dateform'];
								} else {
									$title = $this->CLASS['knowledgeroot']->getOwner($zeile['owner']) . " - " . $zeile['dateform'];
								}
	
								// check for static file download
								if(isset($this->CLASS['config']->misc->download->static) && $this->CLASS['config']->misc->download->static == 1) {
									$downloadlink = "download/".$zeile['id']."/".$zeile['filename'];
								} else {
									$downloadlink = "index.php?download=".$zeile['id'];
								}
	
								if($mycontentrights == 2) {
									echo "<a href=\"javascript:;\" onclick=\"if(confirm('" . $this->CLASS['translate']->_('Do you really want to delete?') . "')) { location.href='index.php?delfile=".$zeile['id']."'; } \"><i class=\"fa fa-trash\" aria-hidden=\"true\"></i></a>&nbsp;<a href=\"" . $downloadlink . "\" title=\"".$title."\"><i class=\"fa fa-cloud-download\" aria-hidden=\"true\"></i> ".$zeile['filename']."</a>&nbsp;<font class=\"text\">[".getfilesize($zeile['filesize'])."]&nbsp;[".$title."]</font><br />\n";
								} else {
									echo "<a href=\"".$downloadlink."\" title=\"".$title."\"><i class=\"fa fa-cloud-download\" aria-hidden=\"true\"></i> ".$zeile['filename']."</a>&nbsp;<font class=\"text\">[".getfilesize($zeile['filesize'])."]&nbsp;[".$title."]</font><br />\n";
								}
							}

                            echo '</div>';
						}

						$this->CLASS['hooks']->setHook("kr_content","show_tree_content","show_content_files_end");
					}

					if($show_files == 1) {
						// show form for adding new files
						if($mycontentrights == 2) {
							echo "<div id=\"fileform_".$row['id']."\" style=\"display:none\" class=\"alert alert-success\">
								<form class=\"AddFileForm\" action=\"index.php\" method=\"post\" enctype=\"multipart/form-data\">\n
									<b>".$this->CLASS['translate']->_('add file')."</b>\n
									<input type=\"hidden\" name=\"upload\" value=\"yes\" />
									<input type=\"hidden\" name=\"contentid\" value=\"".$row['id']."\" />
									<input type=\"file\" name=\"datei\" />&nbsp;\n
									<input class=\"btn btn-primary\" type=\"submit\" name=\"submit\" value=\"".$this->CLASS['translate']->_('add')."\" />
								</form></div>";
						}
					}

					echo '</div>';

					// show content status bar
					if($this->CLASS['config']->content->statusbar)
						echo "<div class=\"card-footer text-muted\">".$lastUpdated."&nbsp;|&nbsp;".$created."</div>\n";

					echo "</div>"; //content title pante end

					$this->CLASS['hooks']->setHook("kr_content","show_tree_content","show_contentline_menu_start");

					// show contentline menu
					echo $this->CLASS['kr_extension']->show_menu("contentline",$row['id'],$mypagerights,$mycontentrights,$row['type']);

					$this->CLASS['hooks']->setHook("kr_content","show_tree_content","show_contentline_menu_end");

					// set firstcontent
					if($mycontentrights == 2 && $mypagerights == 2) {
						$firstcontent = 0;
					}

					echo "</div>\n";
				} // end of while

				$this->CLASS['hooks']->setHook("kr_content","show_tree_content","show_page_menu_start");

				// show page menu
				if($this->CLASS['config']->misc->showpagebottomnavi && $contentcounter != 0) {
					echo "<div style=\"margin-top: 5px;\" />";
					echo $this->CLASS['kr_extension']->show_menu("page", $_SESSION['cid'], $mypagerights);
				}

				$this->CLASS['hooks']->setHook("kr_content","show_tree_content","show_page_menu_end");

				echo "</div><br /><br />";
			} else {
				$this->CLASS['hooks']->setHook("kr_content","show_tree_content","no_content_start");

				// show notification if no content is present
				echo "<h3>" . $this->CLASS['translate']->_('No content here.') . "</h3>\n";

				$this->CLASS['hooks']->setHook("kr_content","show_tree_content","no_content_end");
			}

			echo "
<script>
dojo.addOnLoad(function(){
  dojo.query(\".showMe\").forEach(function(node, index, arr){
	node.style.display = 'block';
  });
});
</script>
			";

			// form for move page
			echo "<form name=\"move\" action=\"index.php\" method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"move\" value=\"move\" />\n";
			echo "<input type=\"hidden\" name=\"to\" value=\"".$_SESSION['cid']."\" />\n";
			echo "<input type=\"hidden\" name=\"contentid\" value=\"\" />\n"; // use for move pagecontent
			echo "</form>\n";

			// form for move content on page
			echo "<form name=\"movecontent\" action=\"index.php\" method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"movecontent\" value=\"movecontent\" />\n";
			echo "<input type=\"hidden\" name=\"page\" value=\"".$_SESSION['cid']."\" />\n"; // id of page
			echo "<input type=\"hidden\" name=\"contentid\" value=\"\" />\n"; // id of source content
			echo "<input type=\"hidden\" name=\"targetcontentid\" value=\"\" />\n"; // id of target content
			echo "<input type=\"hidden\" name=\"position\" value=\"\" />\n"; // use before or after
			echo "</form>\n";
		} else {
			$this->CLASS['hooks']->setHook("kr_content","show_tree_content","welcome_msg_start");

			// show welcome message
			echo "<div class=\"jumbotron\"><h1>".$this->CLASS['translate']->_('Welcome to Knowledgeroot')."</h1></div>\n";

			$this->CLASS['hooks']->setHook("kr_content","show_tree_content","welcome_msg_end");
		}

		// check if symlink was set
		if(isset($_SESSION['original_cid']) && $_SESSION['original_cid'] != "") {
			$_SESSION['cid'] = $_SESSION['original_cid'];
			$_SESSION['original_cid'] = "";
		}

		$this->CLASS['hooks']->setHook("kr_content","show_tree_content","end");
	}
}
?>
