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

if ($CLASS['config']->base->charset != '') {
    header("Content-Type: text/html; charset=".$CLASS['config']->base->charset);
}

?>
<!doctype html>
<html lang="en">
<head>
	<link rel="stylesheet" href="admin.css" type="text/css" />
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

        .form-signin {
            width: 100%;
            max-width: 360px;
            padding: 15px;
            margin: 0 auto;
        }
    </style>
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
<body class="hold-transition login-page">
<header>
    <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark" style="border-bottom: 3px solid #F88529;">
        <a class="navbar-brand" href="<?php echo $CLASS['config']->base->base_url; ?>"><i class="fa fa-arrow-left" aria-hidden="true"></i> <?php echo $CLASS['translate']->_("Back to Knowledgeroot"); ?></a>
    </nav>
</header>

<div class="form-signin">
    <h1>Knowledgeroot</h1>
    <form class="login" action="index.php" method="post" name="loginformular">
        <input type="hidden" name="login" value="true" />
        <div class="form-group has-feedback">
            <input type="text" class="form-control" placeholder="<?php echo $CLASS['translate']->_('Username'); ?>" name="user" id="user">
            <span class="glyphicon glyphicon-user form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback">
            <input type="password" class="form-control" placeholder="<?php echo $CLASS['translate']->_('Password'); ?>" name="pass" id="pass">
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-flat"><?php echo $CLASS['translate']->_('login'); ?></button>
    </form>
    <p/>
    <?php
    if (isset ($_POST['login']) and $_POST['login'] == "true" && $_POST['user'] != "" && $_POST['pass'] != "") {
        echo "<div class=\"alert alert-warning\">loginhash: ".md5($_POST['user'] . $_POST['pass'])."</div>\n";
    }
    ?>
    <div class="alert alert-info" role="alert">
        <h4 class="alert-heading"><?php echo $CLASS['translate']->_('Forgot password?'); ?></h4>
        <p>
            <?php echo $CLASS['translate']->_('To reset the admin password you need to make a new login.<br /> After that you see a loginhash at the bottom. Copy this hash value to your app.ini in section admin.'); ?>
        </p>
    </div>
</div><!-- /.login-box-body -->
</div><!-- /.login-box -->

<?php
} else {
?>
<body>

<div style="display: none;" id="messagebox">
  <div id="msg" class="loading">lade...</div>
</div>

<header>
    <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark" style="border-bottom: 3px solid #F88529;">
        <a class="navbar-brand" href="javascript:;">Knowledgeroot</a>
    </nav>
</header>


<!-- show content -->
<div class="container-fluid">
    <div class="row">
        <ul class="list-group">
            <?php
            $CLASS['kr_extension']->show_admin_menu("admin");
            ?>
        </ul>
        <div class="col-10">
            <?php
            $CLASS['kr_extension']->show_ext_content();
            ?>
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
