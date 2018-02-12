<?php
define("KR_INCLUDE_PREFIX", "../../../../");
require_once("../../../../include/init.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Link Properties</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta content="noindex, nofollow" name="robots">
<?php
	$CLASS['kr_header']->show_header();
?>
<script language="javascript" type="text/javascript">
var msgboxloading = 'loading...';
</script>

<script language="javascript">

//Standard setup stuff (notice the command name used in the property names)
var oEditor = window.parent.InnerDialogLoaded() ;
var FCK = oEditor.FCK ;
var FCKLang = oEditor.FCKLang ;
var FCKKrootLink = oEditor.FCKKrootLink ;

window.onload = function () //Runs when the toolbar button is clicked and this page is loaded
{
	// First of all, translate the dialog box texts
	oEditor.FCKLanguageManager.TranslatePage( document ) ;

	LoadSelected() ; //see function below
	FetchKrootTree();
	window.parent.SetOkButton( false ) ; // Don't show the "Ok" button.
}

var eSelected = oEditor.FCKSelection.GetSelectedElement() ;

function LoadSelected()
{
	if ( !eSelected )
		return ;

	if ( eSelected.tagName == 'SPAN' && eSelected._fckplaceholder )
		document.getElementById('txtName').value = eSelected._fckplaceholder ;
	else
		eSelected == null ;
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
	//alert(r.responseText);
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
    document.getElementById('txtHref').value = url;
    document.getElementById('txtCaption').value = caption;

	if ( document.getElementById('txtHref').value.length > 0 )
		FCKKrootLink.Add( document.getElementById('txtHref').value, document.getElementById('txtCaption').value ) ;
	else
		alert(FCKLang.KrootLinkErrNoneSelected);

	window.parent.Cancel();
}


</script>
</head>

<body scroll="no" style="OVERFLOW: hidden">
<div style="display: none;" id="messagebox">
	<div id="msg" class="loading">loading...</div>
</div>

<table height="100%" cellSpacing="0" cellPadding="0" width="100%" border="0">
<tr>
<td><span fckLang="KrootLinkDlgInstructions">Select a knowledgeroot page to create the link:</span><br />
<table cellSpacing="0" cellPadding="0" align="center" border="0">
<tr>
  <td>
    <table><tr>
      <td id="treecontainer">
        <div id="tree" style="display:block;width:330px;height:300px;overflow:auto;background:white;border:1px solid black">
          <div id="treeelements"></div>
        </div>
      </td>
    </tr>
    </table>
  </td>
</tr>
<tr>
<td>
<input id="txtHref" type="hidden" style="width:160px"><br>
<input id="txtCaption" type="hidden" style="width:160px">
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>
