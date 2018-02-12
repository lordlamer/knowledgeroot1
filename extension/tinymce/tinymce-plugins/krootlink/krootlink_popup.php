<?php
define("KR_INCLUDE_PREFIX","../../../../");
require_once("../../../../include/init.php");
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>{#krootlink.desc}</title>
	<script language="javascript" type="text/javascript" src="../../jscripts/tiny_mce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="../../jscripts/tiny_mce/utils/mctabs.js"></script>
	<base target="_self" />
<?php
	$CLASS['kr_header']->show_header();
?>
<script language="javascript" type="text/javascript">
var msgboxloading = 'loading...';
</script>
<script language="javascript">

window.onload = function () //Runs when the toolbar button is clicked and this page is loaded
{
	FetchKrootTree();
}

function FetchKrootTree()
{
	var ajaxopen = new Ajax.Request(
		"../../../../ajax-xml.php",
		{
			method:'post',
			postBody: 'reloadtree=yes&editor=1',
			onComplete:afterTreeFetch,
			onFailure:myShowError
		}
	);
}
var afterTreeFetch = function(r) {
	try {
        var root = r.responseXML.getElementsByTagName("root");
		if (document.all) {
			var data = unescape(r.responseXML.getElementsByTagName("html")[0].firstChild.nodeValue);
		} else {
			var data = unescape(root[0].getElementsByTagName("html")[0].textContent);
		}
	    new Insertion.Top('treeelements', data);

	} catch (e) {
		//alert(e.message + "#" + e.name + "#" + e.number);
	}
}
var myShowError = function(r) {
    alert("Error: " + r.status + "/t" + r.statusText);
}

function editorSelect(url, caption) {

	var linkHtml = '<a href="' + url + '">' + caption + '</a>';

	tinyMCEPopup.execCommand("mceInsertContent", true, linkHtml);
	tinyMCEPopup.close();

	window.close();
}


</script>

</head>
<body>
<div style="display: none;" id="messagebox">
	<div id="msg" class="loading">loading...</div>
</div>
<form action="#">
	<div class="tabs">
		<ul>
			<li id="general_tab" class="current"><span><a href="javascript:mcTabs.displayTab('general_tab','general_panel');" onmousedown="return false;">{#krootlink.tabtext}</a></span></li>
		</ul>
	</div>

	<div class="panel_wrapper" style="height:380px">
		<div id="general_panel" class="panel current" style="height:380px">

{#krootlink.prompt}<br />
<table cellSpacing="0" cellPadding="0" align="center" border="0">
<tr>
  <td>
    <table><tr>
      <td id="treecontainer">
        <div id="tree" style="display:block;width:330px;height:330px;overflow:auto;background:white;border:1px solid black">
          <div id="treeelements"></div>
        </div>
      </td>
    </tr>
    </table>
  </td>
</tr>
</table>

	</div>
</div>
<div class="mceActionPanel">
		<div style="float: left">
			&nbsp;
		</div>

		<div style="float: right">
			<input type="button" id="cancel" name="cancel" value="{#cancel}" onclick="tinyMCEPopup.close();" />
		</div>
</div>
</form>
</body>
</html>
