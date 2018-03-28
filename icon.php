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
        <a class="navbar-brand" href="#"><?php echo $CLASS['translate']->_("select icon"); ?></a>
    </nav>
</header>

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
<input class="btn btn-secondary" onclick="window.opener.document.getElementById('selected-icon').src = ''; window.opener.document.getElementById('treeicon').value = ''; window.close();" type="button" name="submit" value="<?php echo $CLASS['translate']->_('no icon'); ?>">
</div>
<div id="toroot">
<input class="btn btn-primary" onclick="window.close();" type="button" name="submit" value="<?php echo $CLASS['translate']->_('close window'); ?>">
</div>
</body>
</html>
<?php
	$CLASS['db']->close();
?>
