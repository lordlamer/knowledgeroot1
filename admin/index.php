<?php
/**
 * Admin Interface for Knowledgeroot
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: index.php 1110 2011-06-05 19:36:52Z lordlamer $
 */

if(!is_file("../config/app.ini")) {
	echo "<html><body>No configuration file found! Please make a <a href=\"../install.php\">install</a>!</body></html>";
	exit();
}

// load requiered files
require_once("../include/init_admin.php");

echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<link rel="stylesheet" href="admin.css" type="text/css" />
<?php
	$CLASS['kr_header']->show_header();
?>
	<!-- load the dojo toolkit base -->
	<style type="text/css">
		@import "../system/javascript/dojo/dijit/themes/claro/claro.css";
		@import "../system/javascript/dojo/dojo/resources/dojo.css";
	</style>
	<script type="text/javascript">
		dojo.require("dijit.layout.SplitContainer");
		dojo.require("dijit.layout.LayoutContainer");
		dojo.require("dijit.layout.ContentPane");
		dojo.require("dijit.form.DropDownButton");
		dojo.require("dijit.TooltipDialog");
	</script>
</head>
<?php
// show login?
if (isset ($_POST['login']) and $_POST['login'] == "true") {
	if(md5($_POST['user'] . $_POST['pass']) == $CLASS['config']->admin->loginhash) {
		$_SESSION['passhash'] = md5($_POST['user'] . $_POST['pass']);
	}
}

if($CLASS['config']->admin->loginhash == '' || !isset ($_SESSION['passhash']) or $_SESSION['passhash'] == "" || $_SESSION['passhash'] != $CLASS['config']->admin->loginhash) {
?>
<body class="claro login">
<p id="toplogin"><a href="<?php echo $CLASS['config']->base->base_url; ?>">&larr; <?php echo $CLASS['translate']->_('Back to Knowledgeroot'); ?></a></p>

<!-- show login -->
<div id="login"><h1><a href="http://www.knowledgeroot.org" title="<?php echo $CLASS['translate']->_('Powered by Knowledgeroot'); ?>">Knowledgeroot</a></h1>
<form class="login" action="index.php" method="post" name="loginformular">
<input type="hidden" name="login" value="true" />

<div id="loginform">
	<div id="loginuser"><?php echo $CLASS['translate']->_('Username'); ?>:</div><div id="loginuserfield"><input class="input" type="text" name="user" value="" /></div>
	<div id="loginpass"><?php echo $CLASS['translate']->_('Password'); ?>:</div><div id="loginpassfield"><input class="input" type="password" name="pass" value="" /></div>
	<div id="loginsubmit"><input class="button" type="submit" name="submit" value="<?php echo $CLASS['translate']->_('login'); ?>" /></div>
<?php
if (isset ($_POST['login']) and $_POST['login'] == "true" && $_POST['user'] != "" && $_POST['pass'] != "") {
	echo "<div id=\"loginhash\">loginhash: ".md5($_POST['user'] . $_POST['pass'])."</div>\n";
}
?>
	<div data-dojo-type="dijit.form.DropDownButton">
		<span><?php echo $CLASS['translate']->_('Forgot password?'); ?></span>
		<div data-dojo-type="dijit.TooltipDialog" id="tooltipDlg" data-dojo-props='title:"Enter Login information"'>
			<?php echo $CLASS['translate']->_('To reset the admin password you need to make a new login.<br /> After that you see a loginhash at the bottom. Copy this hash value to your app.ini in section admin.'); ?>
		</div>
	</div>
</div>

</form>
</div>

<script type="text/javascript">
	<!--
	try{document.loginformular.user.focus();}catch(e){}

	//-->
</script>


<?php
} else {
?>
<body class="claro">

<div style="display: none;" id="messagebox">
  <div id="msg" class="loading">lade...</div>
</div>

<!-- show content -->
        <div dojoType="dijit.layout.LayoutContainer" layoutChildPriority="top-bottom" style="width: 100%; height: 100%;">
            <div dojoType="dijit.layout.ContentPane" layoutAlign="top" style="height:50px; border-bottom: 1px solid #000000;">
		<div id="logo"><img src="../images/knowledgeroot.jpg" /></div>
		<div id="title"></div>
            </div>
            <div dojoType="dijit.layout.SplitContainer" orientation="horizontal" sizerWidth="7" activeSizing="0"
                 isActiveResize="0" layoutAlign="client" >

                <div dojoType="dijit.layout.ContentPane" id="leftpane" sizeMin="230" sizeShare="1"
                     style="padding: 10px 10px 10px 10px; background-color: #A2AAB8;">

<?php
$CLASS['kr_extension']->show_admin_menu("admin");
?>
                </div>

                    <div dojoType="dijit.layout.ContentPane" id="downpane" sizeMin="20" sizeShare="30" style="padding: 10px 10px 10px 10px; background-color: #EFEFF4;">
<?php
$CLASS['kr_extension']->show_ext_content();
?>
                    </div>
                </div>
            </div>
        </div>
<?php
}
	// show querys - only for debug
	if($CLASS['config']->development->sqldebug && 1==0) {
		echo "querys: " . $CLASS['db']->querys;
		echo $CLASS['error']->view_array($CLASS['db']->query_cache);
	}
?>

</body>
</html>
