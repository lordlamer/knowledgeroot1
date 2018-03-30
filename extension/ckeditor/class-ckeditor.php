<?php
/**
 * ckeditor Class
 * This class will include the ckeditor to knowledgeroot
 * @author Frank Habermann
 * @package Knowledgeroot
 * @version $Id:
 */
class ckeditor extends rte
{
    var $InstanceName = "content";
    var $Value = "";


    function main() {
        // use this class for the rte
        $this->CLASS['rte'] =& $this;

        // check if page is a page that need editor
        if (
            (isset ($_GET['action']) and $_GET['action'] == "newcontent") ||
            (isset ($_GET['eid']) and $_GET['eid'] != '') ||
            (isset ($_POST['editid']) and $_POST['editid'] != '') ||
            (isset ($_POST['neditid']) and $_POST['neditid'] != '')
        ) {
            $this->load_editor();
        }
    }

    /**
     * load all required things in the header
     */
    function load_editor()
    {
        $this->CLASS['kr_header']->addjssrc($this->myPath . "ckeditor/ckeditor.js");
    }

    function show($text = "")
    {
        $this->Value = $text;
        return $this->CreateHtml();
    }

    function CreateHtml()
    {
        $HtmlValue = htmlspecialchars($this->Value);

        $Html = '<textarea name="'.$this->InstanceName.'" id="editor">
		'.$HtmlValue.'
		</textarea>
		<script>
			CKEDITOR.replace( \'editor\' );
		</script>
		';

        return $Html;
    }
}
