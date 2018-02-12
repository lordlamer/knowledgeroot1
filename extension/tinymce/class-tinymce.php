<?php
/**
 * Tinymce Class
 * This class will include the tinymce to knowledgeroot
 * @author Frank Habermann
 * @package Knowledgeroot
 * @version $Id:
 */
class tinymce extends rte {
	function main() {
		// use this class for the rte
		$this->CLASS['rte'] =& $this;

		// check if page is a page that need editor
		if ((isset ($_GET['action']) and $_GET['action'] == "newcontent") || (isset ($_GET['eid']) and $_GET['eid'] != '')) {
			$this->load_editor();
		}
	}

	/**
	 * load all required things in the header
	 */
	function load_editor() {
		//$this->CLASS['kr_header']->addjssrc($this->myPath . "jscripts/tiny_mce/tiny_mce_gzip.js");
		$this->CLASS['kr_header']->addjssrc($this->myPath . "jscripts/tiny_mce/tiny_mce.js");

		$language = $this->CLASS['config']->base->locale;

		if(strlen($language) > 2) {
			$language = substr($language,0,2);
		}

		if($language == '') {
			$language = 'en';
		}

		$tinymcedata = '
		<script language="javascript" type="text/javascript">
			tinyMCE_GZ.init({
				theme : "advanced",
				plugins : "style,layer,table,save,advhr,krootlink,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
				languages : "' . $language . '",
				disk_cache : true,
				suffix : "",
				debug : true
			});
		</script>
		<script language="javascript" type="text/javascript">
			tinymce.PluginManager.load("krootlink", "../../tinymce-plugins/krootlink/editor_plugin.js");

			window.onload = function() {
				tinyMCE.init({
					mode : "textareas",
					theme : "advanced",
					plugins : "style,layer,table,save,advhr,krootlink,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
					theme_advanced_buttons1_add : "fontselect,fontsizeselect",
					theme_advanced_buttons2 : "cut,copy,paste,separator,search,replace,separator,bullist,numlist,separator,outdent,indent,separator,undo,redo,separator,krootlink,link,unlink,krootlink,anchor,ibrowser,cleanup,help,code,separator,insertdate,inserttime,preview,zoom,separator,forecolor,backcolor",
					theme_advanced_buttons3_add_before : "tablecontrols,separator",
					theme_advanced_buttons3_add : "emotions,iespell,flash,advhr,separator,print,fullscreen",
					theme_advanced_toolbar_location : "top",
					theme_advanced_toolbar_align : "left",
					theme_advanced_path_location : "bottom",
					plugin_insertdate_dateFormat : "%Y-%m-%d",
					plugin_insertdate_timeFormat : "%H:%M:%S",
					theme_advanced_resizing : true,
					content_css : "'.$this->myPath.'tinymce_editor.css",
					extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
					language : "' . $language . '"
				});
			}
		</script>
		';

		$this->CLASS['kr_header']->addheader($tinymcedata);
	}
}

?>
