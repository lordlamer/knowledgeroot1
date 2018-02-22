<?php
/**
 * This class set the default menu in knowledgeroot
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-default-menu.php 1159 2011-07-20 20:47:07Z lordlamer $
 */
class default_menu {

	var $CLASS;
	var $defaultmenu = array();

	/**
	 * init/start class
	 */
	function start(&$CLASS) {
		$this->CLASS =& $CLASS;
		$this->set_default_menu();
		$this->load_default_menu();
	}

	/**
	 *
	 */
	function load_default_menu() {
		$this->CLASS['kr_extension']->menu = array_merge_recursive($this->CLASS['kr_extension']->menu, $this->defaultmenu);
	}

	/**
	 *
	 */
	function set_default_menu() {
		// BEGIN TOP MENU
		$this->defaultmenu['top']['config']['wrap'] = '<ul class="navbar-nav mr-auto">|</ul>';
		$this->defaultmenu['top']['config']['defaultelementwrap'] = '<li class="nav-item">|</li>';
		$this->defaultmenu['top']['config']['donothide'] = '1';
		$this->defaultmenu['top']['config']['css_class'] = 'nav-link';

		// login
		$this->defaultmenu['top']['login']['name'] = $this->CLASS['translate']->_('login');
		$this->defaultmenu['top']['login']['link'] = "index.php?action=login";
		$this->defaultmenu['top']['login']['tooltip'] = "login";
		$this->defaultmenu['top']['login']['logout'] = "1";
		$this->defaultmenu['top']['login']['priority'] = "10";
        $this->defaultmenu['top']['login']['atagparams'] = "class=\"nav-link\"";

		// logout
		$this->defaultmenu['top']['logout']['name'] = $this->CLASS['translate']->_('logout');
		$this->defaultmenu['top']['logout']['link'] = "index.php?action=logout";
		$this->defaultmenu['top']['logout']['tooltip'] = "logout";
		$this->defaultmenu['top']['logout']['login'] = "1";
		$this->defaultmenu['top']['logout']['priority'] = "90";
        $this->defaultmenu['top']['logout']['atagparams'] = "class=\"nav-link\"";

		// roots
		$this->defaultmenu['top']['roots']['name'] = $this->CLASS['translate']->_('roots');
		$this->defaultmenu['top']['roots']['link'] = "index.php?action=createroot";
		$this->defaultmenu['top']['roots']['tooltip'] = "create root";
		$this->defaultmenu['top']['roots']['login'] = "1";
		$this->defaultmenu['top']['roots']['priority'] = "10";
		$this->defaultmenu['top']['roots']['admin'] = "1";
        $this->defaultmenu['top']['roots']['atagparams'] = "class=\"nav-link\"";

		// user
		$this->defaultmenu['top']['user']['name'] = $this->CLASS['translate']->_('user');
		$this->defaultmenu['top']['user']['link'] = "index.php?action=users";
		$this->defaultmenu['top']['user']['tooltip'] = "create users";
		$this->defaultmenu['top']['user']['login'] = "1";
		$this->defaultmenu['top']['user']['priority'] = "20";
		$this->defaultmenu['top']['user']['admin'] = "1";
        $this->defaultmenu['top']['user']['atagparams'] = "class=\"nav-link\"";

		// options
		$this->defaultmenu['top']['options']['name'] = $this->CLASS['translate']->_('options');
		$this->defaultmenu['top']['options']['link'] = "index.php?action=options";
		$this->defaultmenu['top']['options']['tooltip'] = "edit options";
		$this->defaultmenu['top']['options']['login'] = "1";
		$this->defaultmenu['top']['options']['priority'] = "30";
        $this->defaultmenu['top']['options']['atagparams'] = "class=\"nav-link\"";
		// END TOP MENU

		// BEGIN TREE NAVI
        $this->defaultmenu['tree']['config']['wrap'] = '<div class="btn-group" role="group" style="display: flex;">|</div>';
        $this->defaultmenu['tree']['config']['defaultelementwrap'] = '|';
        $this->defaultmenu['tree']['config']['donothide'] = '1';
        $this->defaultmenu['tree']['config']['css_class'] = 'nav-link';

		// expand all
        $this->defaultmenu['tree']['expand']['name'] = "<i class=\"fa fa-plus\" aria-hidden=\"true\"></i>";
		$this->defaultmenu['tree']['expand']['tooltip'] = $this->CLASS['translate']->_('expand menu');
		$this->defaultmenu['tree']['expand']['link'] = "javascript:;";
		$this->defaultmenu['tree']['expand']['atagparams'] = "onclick=\"TreeExpand(".'{$ID}'.");\" class=\"btn btn-secondary\" style=\"flex: 1;\"";
		$this->defaultmenu['tree']['expand']['priority'] = "20";

		// reload all
        $this->defaultmenu['tree']['reload']['name'] = "<i class=\"fa fa-retweet\" aria-hidden=\"true\"></i>";
		$this->defaultmenu['tree']['reload']['tooltip'] = $this->CLASS['translate']->_('reload menu');
		$this->defaultmenu['tree']['reload']['link'] = "javascript:;";
		$this->defaultmenu['tree']['reload']['atagparams'] = "onclick=\"TreeReload(".'{$ID}'.");\" class=\"btn btn-secondary\" style=\"flex: 1;\"";
		$this->defaultmenu['tree']['reload']['priority'] = "30";

		// collapse all
        $this->defaultmenu['tree']['collapse']['name'] = "<i class=\"fa fa-minus\" aria-hidden=\"true\"></i>";
		$this->defaultmenu['tree']['collapse']['tooltip'] = $this->CLASS['translate']->_('collapse menu');
		$this->defaultmenu['tree']['collapse']['link'] = "javascript:;";
		$this->defaultmenu['tree']['collapse']['atagparams'] = "onclick=\"TreeCollapse(".'{$ID}'.");\" class=\"btn btn-secondary\" style=\"flex: 1;\"";
		$this->defaultmenu['tree']['collapse']['priority'] = "40";
		// END TREE NAVI

		// BEGIN PAGE NAVI
		$this->defaultmenu['page']['config']['wrap'] = '<div class="btn-group">|</div>';

		// new page
		$this->defaultmenu['page']['newpage']['name'] = $this->CLASS['translate']->_('add new page');
		$this->defaultmenu['page']['newpage']['nolink'] = '1';
		$this->defaultmenu['page']['newpage']['pagerights'] = "2";
		$this->defaultmenu['page']['newpage']['priority'] = "10";
		$this->defaultmenu['page']['newpage']['wrap'] = "<button type=\"button\" class=\"btn btn-sm btn-outline-secondary\" onclick=\"location.href='index.php?id={\$PAGEID}&amp;action=newpage'\">|</button>";

		// edit page
		$this->defaultmenu['page']['editpage']['name'] = $this->CLASS['translate']->_('edit page');
		$this->defaultmenu['page']['editpage']['nolink'] = "1";
		$this->defaultmenu['page']['editpage']['pagerights'] = "2";
		$this->defaultmenu['page']['editpage']['priority'] = "20";
		$this->defaultmenu['page']['editpage']['wrap'] = "<button type=\"button\" class=\"btn btn-sm btn-outline-secondary\" onclick=\"location.href='index.php?id={\$PAGEID}&amp;action=editpage'\">|</button>";

		// move page
		$this->defaultmenu['page']['movepage']['name'] = $this->CLASS['translate']->_('move page');
		$this->defaultmenu['page']['movepage']['nolink'] = "1";
		$this->defaultmenu['page']['movepage']['atagparams'] = "onclick=\"window.document.forms.move.move.value='move'; window.open('move.php?type=page','Knowledgeroot','width=310,height=400,menubar=yes,resizable=yes,scrollbars=yes');\"";
		$this->defaultmenu['page']['movepage']['pagerights'] = "2";
		$this->defaultmenu['page']['movepage']['priority'] = "30";
		$this->defaultmenu['page']['movepage']['wrap'] = "<button type=\"button\" class=\"btn btn-sm btn-outline-secondary\" onclick=\"window.document.forms.move.move.value='move'; window.open('move.php?type=page','Knowledgeroot','width=310,height=400,menubar=yes,resizable=yes,scrollbars=yes');\">|</button>";

		// delete page
		$this->defaultmenu['page']['deletepage']['name'] = $this->CLASS['translate']->_('delete page');
		$this->defaultmenu['page']['deletepage']['nolink'] = "1";
		$this->defaultmenu['page']['deletepage']['atagparams'] = "onclick=\"if(confirm('" . $this->CLASS['translate']->_('Do you really want to delete this page?') . "')) { location.href='index.php?delpage={\$PAGEID}'; } else { return false; }\"";
		$this->defaultmenu['page']['deletepage']['pagerights'] = "2";
		$this->defaultmenu['page']['deletepage']['priority'] = "40";
		$this->defaultmenu['page']['deletepage']['wrap'] = "<button type=\"button\" class=\"btn btn-sm btn-outline-secondary\" onclick=\"if(confirm('" . $this->CLASS['translate']->_('Do you really want to delete this page?') . "')) { location.href='index.php?delpage={\$PAGEID}'; } else { return false; }\">|</button>";

		// create new content
		$this->defaultmenu['page']['createcontent']['name'] = $this->CLASS['translate']->_('add new content');
		$this->defaultmenu['page']['createcontent']['nolink'] = '1';
		$this->defaultmenu['page']['createcontent']['pagerights'] = "2";
		$this->defaultmenu['page']['createcontent']['priority'] = "50";
		$this->defaultmenu['page']['createcontent']['wrap'] = "<button type=\"button\" class=\"btn btn-sm btn-outline-secondary\" onclick=\"location.href='index.php?id={\$PAGEID}&amp;action=newcontent'\">|</button>";

		// toggle menu
		$this->defaultmenu['page']['togglemenu']['name'] = ((isset($_SESSION['_hide_menu_']) && $_SESSION['_hide_menu_'] != false) ? $this->CLASS['translate']->_('show menu') : $this->CLASS['translate']->_('hide menu'));
		$this->defaultmenu['page']['togglemenu']['nolink'] = '1';
		$this->defaultmenu['page']['togglemenu']['priority'] = "60";
		$this->defaultmenu['page']['togglemenu']['donothide'] = "1";
		$this->defaultmenu['page']['togglemenu']['wrap'] = "<button type=\"button\" class=\"btn btn-sm btn-outline-secondary\" onclick=\"location.href='index.php?id={\$PAGEID}&amp;action=togglemenu'\">|</button>";

		// END PAGE NAVI

		// BEGIN CONTENT NAVI
		$this->defaultmenu['content']['config']['wrap'] = '<div class="btn-group" role="group" style="margin-bottom: 1rem;">|</div><p/>';

		// edit content
		$this->defaultmenu['content']['editcontent']['name'] = $this->CLASS['translate']->_('edit');
		$this->defaultmenu['content']['editcontent']['contentrights'] = "2";
		$this->defaultmenu['content']['editcontent']['priority'] = "10";
		$this->defaultmenu['content']['editcontent']['contenttype'] = "text";
		$this->defaultmenu['content']['editcontent']['nolink'] = "1";
		$this->defaultmenu['content']['editcontent']['wrap'] = "<div type=\"button\" class=\"btn btn-sm btn-outline-secondary\" onclick=\"location.href='index.php?id={\$PAGEID}&amp;eid={\$ID}'\">|</div>";

		// delete content
		$this->defaultmenu['content']['deletecontent']['name'] = $this->CLASS['translate']->_('delete');
		$this->defaultmenu['content']['deletecontent']['nolink'] = '1';
		$this->defaultmenu['content']['deletecontent']['contentrights'] = "2";
		$this->defaultmenu['content']['deletecontent']['priority'] = "20";
		$this->defaultmenu['content']['deletecontent']['contenttype'] = "text";
		$this->defaultmenu['content']['deletecontent']['wrap'] = "<div type=\"button\" class=\"btn btn-sm btn-outline-secondary\" onclick=\"if(confirm('" . $this->CLASS['translate']->_('Do you really want to delete?') . "')) { location.href='index.php?id={\$PAGEID}&amp;delid={\$ID}'; } else { return false; }\">|</div>";

		// move content
		$this->defaultmenu['content']['movecontent']['name'] = $this->CLASS['translate']->_('move');
		$this->defaultmenu['content']['movecontent']['nolink'] = "1";
		$this->defaultmenu['content']['movecontent']['contentrights'] = "2";
		$this->defaultmenu['content']['movecontent']['pagerights'] = "2";
		$this->defaultmenu['content']['movecontent']['priority'] = "30";
		$this->defaultmenu['content']['movecontent']['contenttype'] = "text";
		$this->defaultmenu['content']['movecontent']['wrap'] = "<div type=\"button\" class=\"btn btn-sm btn-outline-secondary\" onclick=\"window.document.forms.move.contentid.value='".'{$ID}'."'; window.document.forms.move.move.value='cmove'; window.document.forms.movecontent.contentid.value='".'{$ID}'."'; window.open('move.php','Knowledgeroot','width=310,height=400,menubar=yes,resizable=yes,scrollbars=yes');\">|</div>";

		// print content
		$this->defaultmenu['content']['printcontent']['name'] = $this->CLASS['translate']->_('print');
		$this->defaultmenu['content']['printcontent']['nolink'] = "1";
		$this->defaultmenu['content']['printcontent']['contentrights'] = "1";
		$this->defaultmenu['content']['printcontent']['pagerights'] = "1";
		$this->defaultmenu['content']['printcontent']['priority'] = "40";
		$this->defaultmenu['content']['printcontent']['contenttype'] = "text";
		$this->defaultmenu['content']['printcontent']['wrap'] = "<div type=\"button\" class=\"btn btn-sm btn-outline-secondary\" onclick=\"window.open('print.php?contentid={\$ID}','Knowledgeroot','width=640,height=480,menubar=yes,resizable=yes,scrollbars=yes');\">|</div>";

		// add file
		$this->defaultmenu['content']['addfile']['name'] = $this->CLASS['translate']->_('add file');
		$this->defaultmenu['content']['addfile']['nolink'] = "1";
		$this->defaultmenu['content']['addfile']['contentrights'] = "2";
		$this->defaultmenu['content']['addfile']['priority'] = "50";
		$this->defaultmenu['content']['addfile']['contenttype'] = "text";
		$this->defaultmenu['content']['addfile']['wrap'] = "<div type=\"button\" class=\"btn btn-sm btn-outline-secondary\" onclick=\"$('#fileform_".'{$ID}'."').show();\">|</div>";

		// totop
		/*
		$this->defaultmenu['content']['top']['name'] = $this->CLASS['translate']->_('Top');
		$this->defaultmenu['content']['top']['nolink'] = "1";
		$this->defaultmenu['content']['top']['contentrights'] = "1";
		$this->defaultmenu['content']['top']['priority'] = "60";
		$this->defaultmenu['content']['top']['wrap'] = "<div style=\"float: right;\" type=\"button\" class=\"btn btn-sm btn-outline-secondary\" onclick=\"location.href='#top';\">|</div>";
		*/

		// END CONTENT NAVI

		// BEGIN TREEBOTTOM MENU
		$this->defaultmenu['treebottom']['config']['wrap'] = "<ul id=\"treebottom\">|</ul>";
		$this->defaultmenu['treebottom']['config']['admin'] = "1";
		$this->defaultmenu['treebottom']['config']['donothide'] = "1";

		// save default tree layout
		if($this->CLASS['config']->tree->defaultlayout) {
			$this->defaultmenu['treebottom']['defaulttree']['name'] = $this->CLASS['translate']->_('save as default tree');
			$this->defaultmenu['treebottom']['defaulttree']['link'] = 'index.php?action=savedefaulttree';
			$this->defaultmenu['treebottom']['defaulttree']['wrap'] = "<li>&raquo;&nbsp;|</li>";
			$this->defaultmenu['treebottom']['defaulttree']['priority'] = "20";
			$this->defaultmenu['treebottom']['defaulttree']['admin'] = "1";
		}

		if($this->CLASS['config']->menu->showsourceforgelogo) {
			$this->defaultmenu['treebottom']['sourceforge']['name'] = $this->CLASS['translate']->_('Sourceforge');
			$this->defaultmenu['treebottom']['sourceforge']['link'] = 'http://sourceforge.net';
			$this->defaultmenu['treebottom']['sourceforge']['wrap'] = "<li style=\"text-align: center;\">|</li>";
			$this->defaultmenu['treebottom']['sourceforge']['image'] = "http://sflogo.sourceforge.net/sflogo.php?group_id=157374&amp;type=1";
			$this->defaultmenu['treebottom']['sourceforge']['priority'] = "90";
		}

		// END TREEBOTTOM MENU
	}
}

?>
