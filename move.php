<?php

if(!is_file("config/app.ini")) {
	echo "<html><body>No configuration file found! Please make a <a href=\"install.php\">install</a>!</body></html>";
	exit();
}

// load requiered files
require_once("include/init.php");

// init tree
$CLASS['tree'] = new categoryTree();
$CLASS['tree']->start($CLASS,'move',"#");

if($CLASS['config']->base->charset != '') {
	header("Content-Type: text/html; charset=".$CLASS['config']->base->charset);
	echo '<?xml version="1.0" encoding="'.$CLASS['config']->base->charset.'"?>';
} else {
	echo '<?xml version="1.0" encoding="iso-8859-1"?>';
}
?>
<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<?php
	$CLASS['kr_header']->show_header();
?>
</head>
<body>

<div style="display: none;" id="messagebox">
	<div id="msg" class="loading"><?php echo $CLASS['translate']->_('loading...'); ?></div>
</div>

<div class="movetitle">
<?php echo $CLASS['translate']->_('move to'); ?>
</div>
<?php
if(isset($_SESSION['admin']) && $_SESSION['admin'] == 1 && isset($_GET['type']) && $_GET['type'] == "page") {
?>
<div id="toroot">
<input class="button" onclick="window.opener.document.forms.move.to.value = '0'; window.opener.document.forms.move.submit(); window.close();" type="button" name="submit" value="<?php echo $CLASS['translate']->_('move to root'); ?>">
</div>
<?php
}
?>
<div style="padding: 5px 12px 0px 12px; font-size: 12px; font-weight: bold;">
<?php
echo $CLASS['translate']->_("Move content to other page:");
?>
</div>
<table><tr>
<td id="treecontainer">
 	<!-- <a href="#" onClick="ShowHide('tree');">#</a> -->
	<div id="treeopener" <?php if($CLASS['config']->tree->type == 'static') { echo "style=\"display:none;\""; } else { echo "style=\"display:block;\""; } ?>>
		<div id="treeshow">
			<a href="#" border="0" onclick="ShowTree();" alt="<?php echo $CLASS['translate']->_('show menu'); ?>"><img id="treeshowimg" src="images/right.gif" width="22" title="<?php echo $CLASS['translate']->_('show menu'); ?>" /></a>
		</div>
	</div>

 	<div id="tree" <?php if($CLASS['config']->tree->type == 'static') { echo "style=\"display:block;\""; } else { echo "style=\"display:none; position:absolute;\""; } ?>>
<?php
	// show tree
	$CLASS['tree']->open = $_SESSION['open'];
	$CLASS['tree']->buildTree(0);
?>
	</div>
 </td>
</tr></table>
<div style="padding: 5px 12px 12px 12px;">
<script>
	function moveContentOnPage(target, position) {
		window.opener.document.forms.movecontent.targetcontentid.value = target;
		window.opener.document.forms.movecontent.position.value = position;
		window.opener.document.forms.movecontent.submit();
		window.close();
	}
</script>
<?php
	echo "<div style=\"font-size: 12px; font-weight: bold;\">".$CLASS['translate']->_("Move content on the same page:") . "</div>";
	$first = true;

	$res = $CLASS['db']->squery("SELECT id, title FROM content WHERE belongs_to=%d AND deleted=0 ORDER BY sorting ASC", $_SESSION['cid']);
	while($row = $CLASS['db']->fetch_assoc($res)) {
		if($CLASS['knowledgeroot']->getContentRights($row['id'],$_SESSION['userid']) != 0) {
			if($first)
				echo "<a style=\"font-size: 16px; font-weight: bold;\" href=\"javascript:;\" onclick=\"moveContentOnPage('".$row['id']."', 'before');\">&larr;</a><br>\n";

			if($row['title'] != "") {
				echo $row['title'] . "<br/>\n";
			} else {
				echo "Id: " . $row['id'] . "<br/>\n";
			}

			echo "<a style=\"font-size: 16px; font-weight: bold;\" href=\"javascript:;\" onclick=\"moveContentOnPage('".$row['id']."', 'after');\">&larr;</a><br>\n";

			$first = false;
		}
	}
?>
</div>
<div id="toroot">
<input class="button" onclick="window.close();" type="button" name="submit" value="<?php echo $CLASS['translate']->_('close window'); ?>">
</div>
</body>
</html>
<?php
	$CLASS['db']->close();
?>
