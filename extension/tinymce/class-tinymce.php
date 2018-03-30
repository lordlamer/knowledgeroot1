<?php
/**
 * Tinymce Class
 * This class will include the tinymce to knowledgeroot
 * @author Frank Habermann
 * @package Knowledgeroot
 * @version $Id:
 */
class tinymce extends rte {
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
	function load_editor() {
		$this->CLASS['kr_header']->addjssrc($this->myPath . "tinymce/tinymce.min.js");
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
			tinymce.init({
			  selector: \'textarea\',
			  height: 500,
			  theme: \'modern\',
			  plugins: \'print preview fullpage searchreplace autolink directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists textcolor wordcount imagetools contextmenu colorpicker textpattern help\',
			  toolbar1: \'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat\',
			  image_advtab: true,
			  templates: [
				{ title: \'Test template 1\', content: \'Test 1\' },
				{ title: \'Test template 2\', content: \'Test 2\' }
			  ]
			 });
		</script>
		';

        return $Html;
    }
}

?>
