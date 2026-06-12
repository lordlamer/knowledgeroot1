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

require_once("../include/init_admin.php");

if ($CLASS['config']->base->charset != '') {
	header("Content-Type: text/html; charset=".$CLASS['config']->base->charset);
}

if (isset($_POST['login']) && $_POST['login'] == "true") {
	if(md5($_POST['user'] . $_POST['pass']) == $CLASS['config']->admin->loginhash) {
		$_SESSION['passhash'] = md5($_POST['user'] . $_POST['pass']);
	}
}

$head = kr_capture(function () use (&$CLASS) {
	$CLASS['kr_header']->show_header();
});

$needsLogin = (
	$CLASS['config']->admin->loginhash == '' ||
	!isset($_SESSION['passhash']) ||
	$_SESSION['passhash'] == "" ||
	$_SESSION['passhash'] != $CLASS['config']->admin->loginhash
);

if($needsLogin) {
	$loginError = '';
	if(isset($_POST['login']) && $_POST['login'] == 'true' && isset($_POST['user']) && $_POST['user'] != '' && isset($_POST['pass']) && $_POST['pass'] != '') {
		$loginError = md5($_POST['user'] . $_POST['pass']);
	}

	echo kr_render($CLASS, 'admin-login.html', array(
		'head' => $head,
		'back_url' => $CLASS['config']->base->base_url,
		'back_text' => $CLASS['translate']->_("Back to Knowledgeroot"),
		'username_label' => $CLASS['translate']->_('Username'),
		'password_label' => $CLASS['translate']->_('Password'),
		'login_text' => $CLASS['translate']->_('login'),
		'forgot_title' => $CLASS['translate']->_('Forgot password?'),
		'forgot_body' => $CLASS['translate']->_('To reset the admin password you need to make a new login.<br /> After that you see a loginhash at the bottom. Copy this hash value to your app.ini in section admin.'),
		'login_hash' => $loginError,
	));
	exit();
}

$adminMenu = kr_capture(function () use (&$CLASS) {
	$CLASS['kr_extension']->show_admin_menu("admin");
});

$adminContent = kr_capture(function () use (&$CLASS) {
	$CLASS['kr_extension']->show_ext_content();
});

$debug = '';

echo kr_render($CLASS, 'admin.html', array(
	'head' => $head,
	'admin_menu' => $adminMenu,
	'admin_content' => $adminContent,
	'debug' => $debug,
));
