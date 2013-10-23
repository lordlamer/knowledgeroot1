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
		$this->defaultmenu['top']['config']['wrap'] = '<div class="top-navigation"><ul>|</ul></div>';
		$this->defaultmenu['top']['config']['defaultelementwrap'] = '<li class="top-navigation-element {$CSS_CLASS}">|</li>';
		$this->defaultmenu['top']['config']['donothide'] = '1';
		$this->defaultmenu['top']['config']['css_class'] = 'top-navigation-element';

		// login
		$this->defaultmenu['top']['login']['name'] = $this->CLASS['translate']->_('login');
		$this->defaultmenu['top']['login']['link'] = "index.php?action=login";
		$this->defaultmenu['top']['login']['tooltip'] = "login";
		$this->defaultmenu['top']['login']['logout'] = "1";
		$this->defaultmenu['top']['login']['priority'] = "10";
		$this->defaultmenu['top']['login']['wrap'] = '<li class="top-navigation-element {$CSS_CLASS}">|</li>';

		// logout
		$this->defaultmenu['top']['logout']['name'] = $this->CLASS['translate']->_('logout');
		$this->defaultmenu['top']['logout']['link'] = "index.php?action=logout";
		$this->defaultmenu['top']['logout']['tooltip'] = "logout";
		$this->defaultmenu['top']['logout']['login'] = "1";
		$this->defaultmenu['top']['logout']['priority'] = "90";
		$this->defaultmenu['top']['logout']['wrap'] = '<li class="top-navigation-element {$CSS_CLASS}">|</li>';

		// roots
		$this->defaultmenu['top']['roots']['name'] = $this->CLASS['translate']->_('roots');
		$this->defaultmenu['top']['roots']['link'] = "index.php?action=createroot";
		$this->defaultmenu['top']['roots']['tooltip'] = "create root";
		$this->defaultmenu['top']['roots']['login'] = "1";
		$this->defaultmenu['top']['roots']['priority'] = "10";
		$this->defaultmenu['top']['roots']['admin'] = "1";
		$this->defaultmenu['top']['roots']['wrap'] = '<li class="top-navigation-element {$CSS_CLASS}">|</li>';

		// user
		$this->defaultmenu['top']['user']['name'] = $this->CLASS['translate']->_('user');
		$this->defaultmenu['top']['user']['link'] = "index.php?action=users";
		$this->defaultmenu['top']['user']['tooltip'] = "create users";
		$this->defaultmenu['top']['user']['login'] = "1";
		$this->defaultmenu['top']['user']['priority'] = "20";
		$this->defaultmenu['top']['user']['admin'] = "1";
		$this->defaultmenu['top']['user']['wrap'] = '<li class="top-navigation-element {$CSS_CLASS}">|</li>';

		// options
		$this->defaultmenu['top']['options']['name'] = $this->CLASS['translate']->_('options');
		$this->defaultmenu['top']['options']['link'] = "index.php?action=options";
		$this->defaultmenu['top']['options']['tooltip'] = "edit options";
		$this->defaultmenu['top']['options']['login'] = "1";
		$this->defaultmenu['top']['options']['priority'] = "30";
		$this->defaultmenu['top']['options']['wrap'] = '<li class="top-navigation-element {$CSS_CLASS}">|</li>';
		// END TOP MENU

		// BEGIN TREE NAVI
		$this->defaultmenu['tree']['config']['donothide'] = '1';

		// hide button
		$this->defaultmenu['tree']['hide']['name'] = $this->CLASS['translate']->_('hide menu');
		$this->defaultmenu['tree']['hide']['image'] = "images/left.gif";
		$this->defaultmenu['tree']['hide']['imagewidth'] = "22";
		$this->defaultmenu['tree']['hide']['link'] = "javascript:;";
		$this->defaultmenu['tree']['hide']['atagparams'] = "onclick=\"HideTree();\"";
		$this->defaultmenu['tree']['hide']['wrap'] = "<td>|</td>";
		$this->defaultmenu['tree']['hide']['priority'] = "10";

		// expand all
		$this->defaultmenu['tree']['expand']['name'] = $this->CLASS['translate']->_('expand menu');
		$this->defaultmenu['tree']['expand']['image'] = "images/plus.gif";
		$this->defaultmenu['tree']['expand']['imagewidth'] = "22";
		$this->defaultmenu['tree']['expand']['link'] = "javascript:;";
		$this->defaultmenu['tree']['expand']['atagparams'] = "onclick=\"TreeExpand(".'{$ID}'.");\"";
		$this->defaultmenu['tree']['expand']['wrap'] = "<td>|</td>";
		$this->defaultmenu['tree']['expand']['priority'] = "20";

		// reload all
		$this->defaultmenu['tree']['reload']['name'] = $this->CLASS['translate']->_('reload menu');
		$this->defaultmenu['tree']['reload']['image'] = "images/reload.gif";
		$this->defaultmenu['tree']['reload']['imagewidth'] = "22";
		$this->defaultmenu['tree']['reload']['link'] = "javascript:;";
		$this->defaultmenu['tree']['reload']['atagparams'] = "onclick=\"TreeReload(".'{$ID}'.");\"";
		$this->defaultmenu['tree']['reload']['wrap'] = "<td>|</td>";
		$this->defaultmenu['tree']['reload']['priority'] = "30";

		// collapse all
		$this->defaultmenu['tree']['collapse']['name'] = $this->CLASS['translate']->_('collapse menu');
		$this->defaultmenu['tree']['collapse']['image'] = "images/minus.gif";
		$this->defaultmenu['tree']['collapse']['imagewidth'] = "22";
		$this->defaultmenu['tree']['collapse']['link'] = "javascript:;";
		$this->defaultmenu['tree']['collapse']['atagparams'] = "onclick=\"TreeCollapse(".'{$ID}'.");\"";
		$this->defaultmenu['tree']['collapse']['wrap'] = "<td>|</td>";
		$this->defaultmenu['tree']['collapse']['priority'] = "40";

		// checking treestyle and slide
		if($this->CLASS['config']->tree->type == "static") {
			$fixedstyle = "style=\"display:none;\"";
			$slidestyle = "style=\"display:block;\"";
		} else {
			$fixedstyle = "style=\"display:block;\"";
			$slidestyle = "style=\"display:none;\"";
		}

		// tree fixed
		$this->defaultmenu['tree']['fixed']['name'] = $this->CLASS['translate']->_('show menu');
		$this->defaultmenu['tree']['fixed']['image'] = "images/fixed.gif";
		$this->defaultmenu['tree']['fixed']['imagewidth'] = "22";
		$this->defaultmenu['tree']['fixed']['link'] = "javascript:;";
		$this->defaultmenu['tree']['fixed']['atagparams'] = "onclick=\"TreeFixed();\"";
		$this->defaultmenu['tree']['fixed']['wrap'] = "<td><div id=\"treefixed\" ".$fixedstyle.">|</div>";
		$this->defaultmenu['tree']['fixed']['priority'] = "40";

		// tree slide
		$this->defaultmenu['tree']['slide']['name'] = $this->CLASS['translate']->_('close menu');
		$this->defaultmenu['tree']['slide']['image'] = "images/close.gif";
		$this->defaultmenu['tree']['slide']['imagewidth'] = "22";
		$this->defaultmenu['tree']['slide']['link'] = "javascript:;";
		$this->defaultmenu['tree']['slide']['atagparams'] = "onclick=\"TreeSlide();\"";
		$this->defaultmenu['tree']['slide']['wrap'] = "<div id=\"treeslide\" ".$slidestyle.">|</div></td>";
		$this->defaultmenu['tree']['slide']['priority'] = "41";

		// END TREE NAVI

		// BEGIN PAGE NAVI
		$this->defaultmenu['page']['config']['wrap'] = '<div dojoType="dijit.MenuBar" region="top">|</div>';

		// new page
		$this->defaultmenu['page']['newpage']['name'] = $this->CLASS['translate']->_('add new page');
		$this->defaultmenu['page']['newpage']['nolink'] = '1';
		$this->defaultmenu['page']['newpage']['pagerights'] = "2";
		$this->defaultmenu['page']['newpage']['priority'] = "10";
		$this->defaultmenu['page']['newpage']['wrap'] = "<div dojoType=\"dijit.MenuBarItem\" onclick=\"location.href='index.php?id={\$PAGEID}&amp;action=newpage'\">|</div>";

		// edit page
		$this->defaultmenu['page']['editpage']['name'] = $this->CLASS['translate']->_('edit page');
		$this->defaultmenu['page']['editpage']['nolink'] = "1";
		$this->defaultmenu['page']['editpage']['pagerights'] = "2";
		$this->defaultmenu['page']['editpage']['priority'] = "20";
		$this->defaultmenu['page']['editpage']['wrap'] = "<div dojoType=\"dijit.MenuBarItem\" onclick=\"location.href='index.php?id={\$PAGEID}&amp;action=editpage'\">|</div>";

		// move page
		$this->defaultmenu['page']['movepage']['name'] = $this->CLASS['translate']->_('move page');
		$this->defaultmenu['page']['movepage']['nolink'] = "1";
		$this->defaultmenu['page']['movepage']['atagparams'] = "onclick=\"window.document.forms.move.move.value='move'; window.open('move.php?type=page','Knowledgeroot','width=310,height=400,menubar=yes,resizable=yes,scrollbars=yes');\"";
		$this->defaultmenu['page']['movepage']['pagerights'] = "2";
		$this->defaultmenu['page']['movepage']['priority'] = "30";
		$this->defaultmenu['page']['movepage']['wrap'] = "<div dojoType=\"dijit.MenuBarItem\" onclick=\"window.document.forms.move.move.value='move'; window.open('move.php?type=page','Knowledgeroot','width=310,height=400,menubar=yes,resizable=yes,scrollbars=yes');\">|</div>";

		// delete page
		$this->defaultmenu['page']['deletepage']['name'] = $this->CLASS['translate']->_('delete page');
		$this->defaultmenu['page']['deletepage']['nolink'] = "1";
		$this->defaultmenu['page']['deletepage']['atagparams'] = "onclick=\"if(confirm('" . $this->CLASS['translate']->_('Do you really want to delete this page?') . "')) { location.href='index.php?delpage={\$PAGEID}'; } else { return false; }\"";
		$this->defaultmenu['page']['deletepage']['pagerights'] = "2";
		$this->defaultmenu['page']['deletepage']['priority'] = "40";
		$this->defaultmenu['page']['deletepage']['wrap'] = "<div dojoType=\"dijit.MenuBarItem\" onclick=\"if(confirm('" . $this->CLASS['translate']->_('Do you really want to delete this page?') . "')) { location.href='index.php?delpage={\$PAGEID}'; } else { return false; }\">|</div>";

		// create new content
		$this->defaultmenu['page']['createcontent']['name'] = $this->CLASS['translate']->_('add new content');
		$this->defaultmenu['page']['createcontent']['nolink'] = '1';
		$this->defaultmenu['page']['createcontent']['pagerights'] = "2";
		$this->defaultmenu['page']['createcontent']['priority'] = "50";
		$this->defaultmenu['page']['createcontent']['wrap'] = "<div dojoType=\"dijit.MenuBarItem\" onclick=\"location.href='index.php?id={\$PAGEID}&amp;action=newcontent'\">|</div>";

		// toggle menu
		$this->defaultmenu['page']['togglemenu']['name'] = ((isset($_SESSION['_hide_menu_']) && $_SESSION['_hide_menu_'] != false) ? $this->CLASS['translate']->_('show menu') : $this->CLASS['translate']->_('hide menu'));
		$this->defaultmenu['page']['togglemenu']['nolink'] = '1';
		$this->defaultmenu['page']['togglemenu']['priority'] = "60";
		$this->defaultmenu['page']['togglemenu']['donothide'] = "1";
		$this->defaultmenu['page']['togglemenu']['wrap'] = "<div dojoType=\"dijit.MenuBarItem\" onclick=\"location.href='index.php?id={\$PAGEID}&amp;action=togglemenu'\">|</div>";

		// END PAGE NAVI

		// BEGIN CONTENT NAVI
		$this->defaultmenu['content']['config']['wrap'] = '<div style="height: 24px; border-left:0px; border-right:0px;" dojoType="dijit.MenuBar" region="top">|</div>';

		// edit content
		$this->defaultmenu['content']['editcontent']['name'] = $this->CLASS['translate']->_('edit');
		$this->defaultmenu['content']['editcontent']['contentrights'] = "2";
		$this->defaultmenu['content']['editcontent']['priority'] = "10";
		$this->defaultmenu['content']['editcontent']['contenttype'] = "text";
		$this->defaultmenu['content']['editcontent']['nolink'] = "1";
		$this->defaultmenu['content']['editcontent']['wrap'] = "<div dojoType=\"dijit.MenuBarItem\" onclick=\"location.href='index.php?id={\$PAGEID}&amp;eid={\$ID}'\">|</div>";

		// delete content
		$this->defaultmenu['content']['deletecontent']['name'] = $this->CLASS['translate']->_('delete');
		$this->defaultmenu['content']['deletecontent']['nolink'] = '1';
		$this->defaultmenu['content']['deletecontent']['contentrights'] = "2";
		$this->defaultmenu['content']['deletecontent']['priority'] = "20";
		$this->defaultmenu['content']['deletecontent']['contenttype'] = "text";
		$this->defaultmenu['content']['deletecontent']['wrap'] = "<div dojoType=\"dijit.MenuBarItem\" onclick=\"if(confirm('" . $this->CLASS['translate']->_('Do you really want to delete?') . "')) { location.href='index.php?id={\$PAGEID}&amp;delid={\$ID}'; } else { return false; }\">|</div>";

		// move content
		$this->defaultmenu['content']['movecontent']['name'] = $this->CLASS['translate']->_('move');
		$this->defaultmenu['content']['movecontent']['nolink'] = "1";
		$this->defaultmenu['content']['movecontent']['contentrights'] = "2";
		$this->defaultmenu['content']['movecontent']['pagerights'] = "2";
		$this->defaultmenu['content']['movecontent']['priority'] = "30";
		$this->defaultmenu['content']['movecontent']['contenttype'] = "text";
		$this->defaultmenu['content']['movecontent']['wrap'] = "<div dojoType=\"dijit.MenuBarItem\" onclick=\"window.document.forms.move.contentid.value='".'{$ID}'."'; window.document.forms.move.move.value='cmove'; window.document.forms.movecontent.contentid.value='".'{$ID}'."'; window.open('move.php','Knowledgeroot','width=310,height=400,menubar=yes,resizable=yes,scrollbars=yes');\">|</div>";

		// print content
		$this->defaultmenu['content']['printcontent']['name'] = $this->CLASS['translate']->_('print');
		$this->defaultmenu['content']['printcontent']['nolink'] = "1";
		$this->defaultmenu['content']['printcontent']['contentrights'] = "1";
		$this->defaultmenu['content']['printcontent']['pagerights'] = "1";
		$this->defaultmenu['content']['printcontent']['priority'] = "40";
		$this->defaultmenu['content']['printcontent']['contenttype'] = "text";
		$this->defaultmenu['content']['printcontent']['wrap'] = "<div dojoType=\"dijit.MenuBarItem\" onclick=\"window.open('print.php?contentid={\$ID}','Knowledgeroot','width=640,height=480,menubar=yes,resizable=yes,scrollbars=yes');\">|</div>";

		// add file
		$this->defaultmenu['content']['addfile']['name'] = $this->CLASS['translate']->_('add file');
		$this->defaultmenu['content']['addfile']['nolink'] = "1";
		$this->defaultmenu['content']['addfile']['contentrights'] = "2";
		$this->defaultmenu['content']['addfile']['priority'] = "50";
		$this->defaultmenu['content']['addfile']['contenttype'] = "text";
		$this->defaultmenu['content']['addfile']['wrap'] = "<div dojoType=\"dijit.MenuBarItem\" onclick=\"return ShowById('fileform_".'{$ID}'."');\">|</div>";

		// totop
		$this->defaultmenu['content']['top']['name'] = $this->CLASS['translate']->_('Top');
		$this->defaultmenu['content']['top']['nolink'] = "1";
		$this->defaultmenu['content']['top']['contentrights'] = "1";
		$this->defaultmenu['content']['top']['priority'] = "60";
		$this->defaultmenu['content']['top']['wrap'] = "<div style=\"float: right;\" dojoType=\"dijit.MenuBarItem\" onclick=\"location.href='#top';\">|</div>";

		// END CONTENT NAVI

		// BEGIN CONTENT_LINE NAVI

		// add drag drop
		//$this->defaultmenu['contentline']['move']['name'] = $this->CLASS['translate']->_('move');
		//$this->defaultmenu['contentline']['move']['link'] = "javascript:;";
		//$this->defaultmenu['contentline']['move']['image'] = "images/drag.gif";
		//$this->defaultmenu['contentline']['move']['atagparams'] = "id=\"contentdragicon_{\$ID}\"";
		//$this->defaultmenu['contentline']['move']['wrap'] = "<div class=\"downarrow\">|<a id=\"contentdragid_{\$ID}\" href=\"#\"></a><script type=\"text/javascript\" language=\"javascript\">\nvar navig_agt=navigator.userAgent.toLowerCase();\nvar navig_ie=((navig_agt.indexOf(\"msie\")!=-1) && (navig_agt.indexOf(\"opera\")==-1));\nvar navig_ie8=(navig_ie && (navig_agt.indexOf(\"msie 8.\")!=-1));\nif((navigator.appName.toLowerCase() != \"opera\" && !document.all) || navig_ie8) {";
		//$this->defaultmenu['contentline']['move']['wrap'] .= "new Draggable('contentdragid_{\$ID}', {handle: 'contentdragicon_{\$ID}', revert:true, ghosting: false, zindex: 900, starteffect:function() { if(\$('contenttitleid_{\$ID}').innerHTML != \"\") { Dragbox.show(\$('contenttitleid_{\$ID}').innerHTML); } else { Dragbox.show('".$this->CLASS['translate']->_('content')."'); } }, endeffect:function() { Dragbox.hide(); } });";
		//$this->defaultmenu['contentline']['move']['wrap'] .= "} </script></div>";
		//$this->defaultmenu['contentline']['move']['tooltip'] = $this->CLASS['translate']->_('move content');
		//$this->defaultmenu['contentline']['move']['priority'] = "10";
		// END CONTEN$this->CLASS['translate']->_LINE NAVI

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

		// BEGIN TREE CONTEXT MENU

		// configure menu
		$this->defaultmenu['pagecontext']['config']['wrap'] = "<table id=\"pagecontext\" class=\"contextmenu\" cellpadding=\"0\" cellspacing=\"0\">|</table>";

		// new page
		$this->defaultmenu['pagecontext']['newpage']['name'] = $this->CLASS['translate']->_('add new page');
		$this->defaultmenu['pagecontext']['newpage']['link'] = 'index.php?id={$PAGEID}&action=newpage';
		$this->defaultmenu['pagecontext']['newpage']['pagerights'] = "2";
		$this->defaultmenu['pagecontext']['newpage']['priority'] = "10";
		$this->defaultmenu['pagecontext']['newpage']['wrap'] = "<tr class=\"contextmenu-item-row\"><td class=\"contextmenu-item\">|</td><td class=\"contextmenu-item-img\"><img src=\"images/pages.gif\" /></td></tr>";

		// edit page
		$this->defaultmenu['pagecontext']['editpage']['name'] = $this->CLASS['translate']->_('edit page');
		$this->defaultmenu['pagecontext']['editpage']['link'] = 'index.php?id={$PAGEID}&action=editpage';
		$this->defaultmenu['pagecontext']['editpage']['pagerights'] = "2";
		$this->defaultmenu['pagecontext']['editpage']['priority'] = "20";
		$this->defaultmenu['pagecontext']['editpage']['wrap'] = "<tr class=\"contextmenu-item-row\"><td class=\"contextmenu-item\">|</td><td class=\"contextmenu-item-img\"><img src=\"images/pages.gif\" /></td></tr>";

		// move page
		$this->defaultmenu['pagecontext']['movepage']['name'] = $this->CLASS['translate']->_('move page');
		$this->defaultmenu['pagecontext']['movepage']['link'] = "#";
		$this->defaultmenu['pagecontext']['movepage']['atagparams'] = "onclick=\"window.document.forms.move.move.value='move'; window.open('move.php?type=page','Knowledgeroot','width=310,height=400,menubar=yes,resizable=yes,scrollbars=yes');\"";
		$this->defaultmenu['pagecontext']['movepage']['pagerights'] = "2";
		$this->defaultmenu['pagecontext']['movepage']['priority'] = "30";
		$this->defaultmenu['pagecontext']['movepage']['wrap'] = "<tr class=\"contextmenu-item-row\"><td class=\"contextmenu-item\">|</td><td class=\"contextmenu-item-img\"><img src=\"images/pages.gif\" /></td></tr>";

		// delete page
		$this->defaultmenu['pagecontext']['deletepage']['name'] = $this->CLASS['translate']->_('delete page');
		$this->defaultmenu['pagecontext']['deletepage']['link'] = "javascript:;";
		$this->defaultmenu['pagecontext']['deletepage']['atagparams'] = "onclick=\"if(confirm('" . $this->CLASS['translate']->_('Do you really want to delete this page?') . "')) { location.href='index.php?delpage={\$PAGEID}'; } else { return false; }\"";
		$this->defaultmenu['pagecontext']['deletepage']['pagerights'] = "2";
		$this->defaultmenu['pagecontext']['deletepage']['priority'] = "40";
		$this->defaultmenu['pagecontext']['deletepage']['wrap'] = "<tr class=\"contextmenu-item-row\"><td class=\"contextmenu-item\">|</td><td class=\"contextmenu-item-img\"><img src=\"images/pages.gif\" /></td></tr>";

		// add empty line
		$this->defaultmenu['pagecontext']['line']['name'] = "";
		$this->defaultmenu['pagecontext']['line']['link'] = '';
		$this->defaultmenu['pagecontext']['line']['pagerights'] = "2";
		$this->defaultmenu['pagecontext']['line']['priority'] = "45";
		$this->defaultmenu['pagecontext']['line']['wrap'] = "<tr class=\"contextmenu-line-row\"><td colspan=\"2\"><img src=\"images/clear.gif\" width=\"1\" height=\"1\" />|</td></tr>";

		// create new content
		$this->defaultmenu['pagecontext']['createcontent']['name'] = $this->CLASS['translate']->_('add new content');
		$this->defaultmenu['pagecontext']['createcontent']['link'] = 'index.php?id={$PAGEID}&amp;action=newcontent';
		$this->defaultmenu['pagecontext']['createcontent']['pagerights'] = "2";
		$this->defaultmenu['pagecontext']['createcontent']['priority'] = "50";
		$this->defaultmenu['pagecontext']['createcontent']['wrap'] = "<tr class=\"contextmenu-item-row\"><td class=\"contextmenu-item\">|</td><td class=\"contextmenu-item-img\"><img src=\"images/pages.gif\" /></td></tr>";
		// END TREE CONTEXT MENU

		// BEGIN CONTENT CONTEXT MENU
		// configure menu
		$this->defaultmenu['contentcontext']['config']['wrap'] = "<table id=\"pagecontext\" class=\"contextmenu\" cellpadding=\"0\" cellspacing=\"0\">|</table>";

		// create new content
		$this->defaultmenu['contentcontext']['createcontent']['name'] = $this->CLASS['translate']->_('add new content');
		$this->defaultmenu['contentcontext']['createcontent']['link'] = 'index.php?id={$PAGEID}&amp;action=newcontent';
		$this->defaultmenu['contentcontext']['createcontent']['pagerights'] = "2";
		$this->defaultmenu['contentcontext']['createcontent']['priority'] = "10";
		$this->defaultmenu['contentcontext']['createcontent']['wrap'] = "<tr class=\"contextmenu-item-row\"><td class=\"contextmenu-item\">|</td><td class=\"contextmenu-item-img\"><img src=\"images/pages.gif\" /></td></tr>";

		// edit content
		$this->defaultmenu['contentcontext']['editcontent']['name'] = $this->CLASS['translate']->_('edit content');
		$this->defaultmenu['contentcontext']['editcontent']['link'] = 'index.php?id={$PAGEID}&amp;eid=';
		$this->defaultmenu['contentcontext']['editcontent']['addid'] = "1";
		$this->defaultmenu['contentcontext']['editcontent']['contentrights'] = "2";
		$this->defaultmenu['contentcontext']['editcontent']['priority'] = "20";
		$this->defaultmenu['contentcontext']['editcontent']['contenttype'] = "text";
		$this->defaultmenu['contentcontext']['editcontent']['wrap'] = "<tr class=\"contextmenu-item-row\"><td class=\"contextmenu-item\">|</td><td class=\"contextmenu-item-img\"><img src=\"images/pages.gif\" /></td></tr>";

		// delete content
		$this->defaultmenu['contentcontext']['deletecontent']['name'] = $this->CLASS['translate']->_('delete content');
		$this->defaultmenu['contentcontext']['deletecontent']['link'] = 'javascript:;';
		$this->defaultmenu['contentcontext']['deletecontent']['atagparams'] = "onclick=\"if(confirm('" . $this->CLASS['translate']->_('Do you really want to delete?') . "')) { location.href='index.php?id={\$PAGEID}&amp;delid={\$ID}'; } else { return false; }\"";
		$this->defaultmenu['contentcontext']['deletecontent']['contentrights'] = "2";
		$this->defaultmenu['contentcontext']['deletecontent']['priority'] = "30";
		$this->defaultmenu['contentcontext']['deletecontent']['contenttype'] = "text";
		$this->defaultmenu['contentcontext']['deletecontent']['wrap'] = "<tr class=\"contextmenu-item-row\"><td class=\"contextmenu-item\">|</td><td class=\"contextmenu-item-img\"><img src=\"images/pages.gif\" /></td></tr>";

		// move content
		$this->defaultmenu['contentcontext']['movecontent']['name'] = $this->CLASS['translate']->_('move content');
		$this->defaultmenu['contentcontext']['movecontent']['link'] = "#";
		$this->defaultmenu['contentcontext']['movecontent']['atagparams'] = "onclick=\"window.document.forms.move.contentid.value='".'{$ID}'."'; window.document.forms.move.move.value='cmove'; window.open('move.php','Knowledgeroot','width=310,height=400,menubar=yes,resizable=no');\"";
		$this->defaultmenu['contentcontext']['movecontent']['contentrights'] = "2";
		$this->defaultmenu['contentcontext']['movecontent']['pagerights'] = "2";
		$this->defaultmenu['contentcontext']['movecontent']['priority'] = "40";
		$this->defaultmenu['contentcontext']['movecontent']['contenttype'] = "text";
		$this->defaultmenu['contentcontext']['movecontent']['wrap'] = "<tr class=\"contextmenu-item-row\"><td class=\"contextmenu-item\">|</td><td class=\"contextmenu-item-img\"><img src=\"images/pages.gif\" /></td></tr>";

		// add file
		$this->defaultmenu['contentcontext']['addfile']['name'] = $this->CLASS['translate']->_('add file');
		$this->defaultmenu['contentcontext']['addfile']['link'] = "#";
		$this->defaultmenu['contentcontext']['addfile']['addid'] = "1";
		$this->defaultmenu['contentcontext']['addfile']['atagparams'] = "onclick=\"return ShowById('fileform_".'{$ID}'."');\"";
		$this->defaultmenu['contentcontext']['addfile']['contentrights'] = "2";
		$this->defaultmenu['contentcontext']['addfile']['priority'] = "40";
		$this->defaultmenu['contentcontext']['addfile']['contenttype'] = "text";
		$this->defaultmenu['contentcontext']['addfile']['wrap'] = "<tr class=\"contextmenu-item-row\"><td class=\"contextmenu-item\">|</td><td class=\"contextmenu-item-img\"><img src=\"images/pages.gif\" /></td></tr>";

		// END CONTENT CONTEXT MENU
	}
}

?>
