<?php

if(!is_file("config/app.ini")) {
	echo "<html><body>No configuration file found! Please make a <a href=\"install.php\">install</a>!</body></html>";
	exit();
}

// load requiered files
require_once("include/init.php");

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
<body class="print" onload="window.print();">
<?php
	// check user rights
	if(isset($_GET['contentid']) && $_GET['contentid'] != '' && $CLASS['knowledgeroot']->checkRecursivPerm($CLASS['knowledgeroot']->getPageIdFromContentId($_GET['contentid']), $_SESSION['userid']) > 0 && $CLASS['knowledgeroot']->getContentRights($_GET['contentid'], $_SESSION['userid']) >= 1) {
		$res = $CLASS['db']->squery("SELECT * FROM content WHERE id=%d and deleted=0", $_GET['contentid']);
		if($row = $CLASS['db']->fetch_assoc($res)) {
			echo "<h2>".$row['title']."</h2>\n";
			echo "<div>" . $row['content'] . "</div>\n";
		}
	}
?>
</body>
</html>
<?php
	$CLASS['db']->close();
?>
