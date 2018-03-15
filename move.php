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
}
?>
<!doctype html>
<html lang="en">
<head>
<?php
	$CLASS['kr_header']->show_header();
?>
    <style>
        html {
            position: relative;
            min-height: 100%;
        }
        body {
            /* Margin bottom by footer height */
            margin-top: 60px;
        }
    </style>
</head>
<body>

<div style="display: none;" id="messagebox">
	<div id="msg" class="loading"><?php echo $CLASS['translate']->_('loading...'); ?></div>
</div>

<header>
    <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark" style="border-bottom: 3px solid #F88529;">
        <a class="navbar-brand" href="#"><?php echo $CLASS['translate']->_("move to"); ?></a>
    </nav>
</header>

<?php
if(isset($_SESSION['admin']) && $_SESSION['admin'] == 1 && isset($_GET['type']) && $_GET['type'] == "page") {
?>
<div id="toroot">
<input class="btn btn-primary" onclick="window.opener.document.forms.move.to.value = '0'; window.opener.document.forms.move.submit(); window.close();" type="button" name="submit" value="<?php echo $CLASS['translate']->_('move to root'); ?>">
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
				echo "<a class=\"btn btn-secondary\" href=\"javascript:;\" onclick=\"moveContentOnPage('".$row['id']."', 'before');\"><i class=\"fa fa-arrow-left\" aria-hidden=\"true\"></i></a><br>\n";

			if($row['title'] != "") {
				echo $row['title'] . "<br/>\n";
			} else {
				echo "Id: " . $row['id'] . "<br/>\n";
			}

			echo "<a class=\"btn btn-secondary\" href=\"javascript:;\" onclick=\"moveContentOnPage('".$row['id']."', 'after');\"><i class=\"fa fa-arrow-left\" aria-hidden=\"true\"></i></a><br>\n";

			$first = false;
		}
	}
?>
</div>
<div id="toroot">
<input class="btn btn-primary" onclick="window.close();" type="button" name="submit" value="<?php echo $CLASS['translate']->_('close window'); ?>">
</div>
</body>
</html>
<?php
	$CLASS['db']->close();
?>
