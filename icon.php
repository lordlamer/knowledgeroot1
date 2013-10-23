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
<body>

<div style="display: none;" id="messagebox">
	<div id="msg" class="loading"><?php echo $CLASS['translate']->_('loading...'); ?></div>
</div>

<div class="movetitle">
<?php echo $CLASS['translate']->_('select icon'); ?>
</div>
<div class="iconlist">
<?php

$CLASS['hooks']->setHook("icon_select","show","start");

foreach (glob("icons/*") as $filename) {
	echo "<a href=\"javascript:;\" ";
	echo "onclick=\"window.opener.document.getElementById('selected-icon').src = '".$filename."'; window.opener.document.getElementById('treeicon').value = '".$filename."'; window.close();\"";
	echo ">";
	echo "<img src=\"$filename\" />";
	echo "</a>" . "\n";
}

$CLASS['hooks']->setHook("icon_select","show","end");

?>
</div>
<div id="toroot">
<input class="button" onclick="window.opener.document.getElementById('selected-icon').src = ''; window.opener.document.getElementById('treeicon').value = ''; window.close();" type="button" name="submit" value="<?php echo $CLASS['translate']->_('no icon'); ?>">
</div>
<div id="toroot">
<input class="button" onclick="window.close();" type="button" name="submit" value="<?php echo $CLASS['translate']->_('close window'); ?>">
</div>
</body>
</html>
<?php
	$CLASS['db']->close();
?>
