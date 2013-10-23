<?php
/**
 * CKEditor Class
 * This class will include the ckeditor to knowledgeroot
 * @author Frank Habermann
 * @package Knowledgeroot
 * @version $Id:
 */
class dojorte extends rte {

	function main() {
		$this->CLASS['rte'] =& $this;
	}

	function show($text="") {
		$html = '
			<script type="text/javascript">
				dojo.require("dijit.Editor");
				dojo.require("dojox.editor.plugins.PrettyPrint");
				dojo.require("dojox.editor.plugins.PageBreak");
				dojo.require("dojox.editor.plugins.ShowBlockNodes");
				dojo.require("dojox.editor.plugins.Preview");
				dojo.require("dojox.editor.plugins.ToolbarLineBreak");
				dojo.require("dojox.editor.plugins.NormalizeIndentOutdent");
				dojo.require("dojox.editor.plugins.Breadcrumb");
				dojo.require("dojox.editor.plugins.FindReplace");
				dojo.require("dojox.editor.plugins.PasteFromWord");
				dojo.require("dojox.editor.plugins.InsertAnchor");
				dojo.require("dojox.editor.plugins.CollapsibleToolbar");
				dojo.require("dojox.editor.plugins.TextColor");
				dojo.require("dojox.editor.plugins.Blockquote");
				dojo.require("dojox.editor.plugins.Smiley");
			</script>

			<link rel="stylesheet" href="system/javascript/dojo/dojox/editor/plugins/resources/editorPlugins.css" />
			<link rel="stylesheet" href="system/javascript/dojo/dojox/editor/plugins/resources/css/PageBreak.css" />
			<link rel="stylesheet" href="system/javascript/dojo/dojox/editor/plugins/resources/css/ShowBlockNodes.css" />
			<link rel="stylesheet" href="system/javascript/dojo/dojox/editor/plugins/resources/css/Preview.css" />
			<link rel="stylesheet" href="system/javascript/dojo/dojox/editor/plugins/resources/css/Save.css" />
			<link rel="stylesheet" href="system/javascript/dojo/dojox/editor/plugins/resources/css/Breadcrumb.css" />
			<link rel="stylesheet" href="system/javascript/dojo/dojox/editor/plugins/resources/css/FindReplace.css" />
			<link rel="stylesheet" href="system/javascript/dojo/dojox/editor/plugins/resources/css/PasteFromWord.css" />
			<link rel="stylesheet" href="system/javascript/dojo/dojox/editor/plugins/resources/css/InsertAnchor.css" />
			<link rel="stylesheet" href="system/javascript/dojo/dojox/editor/plugins/resources/css/CollapsibleToolbar.css" />
			<link rel="stylesheet" href="system/javascript/dojo/dojox/editor/plugins/resources/css/Blockquote.css" />
			<link rel="stylesheet" href="system/javascript/dojo/dojox/editor/plugins/resources/css/Smiley.css" />
		';
		$html .= "<textarea data-dojo-type=\"dijit.Editor\" id=\"content\" name=\"content\" data-dojo-props=\"extraPlugins:['prettyprint','pagebreak','showblocknodes','preview','toolbarlinebreak','normalizeindentoutdent','breadcrumb','findreplace','pastefromword','insertanchor','collapsibletoolbar','foreColor', 'hiliteColor','blockquote','smiley']\">".$text."</textarea>\n";

		return $html;
	}

}

?>
