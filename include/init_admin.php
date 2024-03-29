<?php

use Pimple\Container;

/**
 *
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: init_admin.php 993 2010-12-23 23:11:54Z lordlamer $
 */

// get base url for session name
$baseurl = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
preg_match("/(.*\/).*/", $baseurl, $url_arr);

if($url_arr[1] == "") {
	$url_arr[1] = $_SERVER['HTTP_HOST'];
}

$base_path = realpath(dirname(__FILE__).'/../') . '/';

// load required files
require_once($base_path."vendor/autoload.php");
require_once($base_path."include/version.php");
require_once($base_path."include/class-session.php");
require_once($base_path."include/class-runtime.php");
require_once($base_path."include/function.php");
require_once($base_path."include/class-knowledgeroot.php");
require_once($base_path."include/class-knowledgeroot-header.php");
require_once($base_path."include/class-language.php");
require_once($base_path."include/class-knowledgeroot-extension.php");
require_once($base_path."include/class-extension-base.php");
require_once($base_path."include/class-error.php");
require_once($base_path."include/class-hooks.php");
require_once($base_path."include/class-db-result.php");
require_once($base_path."include/class-db-core.php");
require_once($base_path."include/class-db-dbal.php");

// this is the variable where all classes are in
$CLASS = array();

// check for app.ini and load config
if(!is_file($base_path.'config/app.ini')) {
	die('No app.ini found in config folder! Stop working here!');
}

// init config
$CLASS['config'] = new Zend_Config_Ini($base_path.'config/app.ini', null, array('allowModifications' => true));

// init session
$CLASS['session'] = new session();
$CLASS['session']->start($CLASS);
$CLASS['session']->startSession(md5($url_arr[1]));
if(!$CLASS['session']->checkSession()) {
        die("SESSION WAS WRONG!");
}

// define runtimer
$CLASS['runtime'] = new runtime();

// init error
$CLASS['error'] = new knowledgeroot_error();
$CLASS['error']->start($CLASS);

// set base paths
$CLASS['config']->admin->base_path = $base_path . "admin/";

// pimple di container
$CLASS['container'] = new Container();

// init twig
$loader = new Twig_Loader_Filesystem($base_path.'system/templates');
$CLASS['container']['twig'] = new Twig_Environment($loader, array(
    'cache' => $base_path.$CLASS['config']->cache->path,
));

// init hooks
$CLASS['hooks'] = new hooks();
$CLASS['hooks']->start($CLASS);

// init databaseclass
$CLASS['db'] = new db();
$CLASS['db']->start($CLASS);

// connect to database
$CLASS['db']->connect($CLASS['config']->db->adapter, $CLASS['config']->db->params->host,$CLASS['config']->db->params->username,$CLASS['config']->db->params->password,$CLASS['config']->db->params->dbname,$CLASS['config']->db->schema,$CLASS['config']->db->encoding);

// init cache
if(!is_dir($base_path . $CLASS['config']->cache->path)) {
	die('Cache path is not a directory:'.$base_path . $CLASS['config']->cache->path);
}
if(!is_writeable($base_path . $CLASS['config']->cache->path)) {
	die('Cache path is not writeable:'.$base_path . $CLASS['config']->cache->path);
}
$CLASS['cache'] = Zend_Cache::factory('Core', 'File', $CLASS['config']->cache->options->toArray(), array('cache_dir' => $base_path . $CLASS['config']->cache->path));

// init knowledgerootclass
$CLASS['knowledgeroot'] = new knowledgeroot();
$CLASS['knowledgeroot']->start($CLASS);

/**
 * PHP-Gettext
 */
// PHP-Gettext start
Zend_Translate::setCache($CLASS['cache']);
$CLASS['translate'] = new Zend_Translate('gettext', $base_path.'system/language/'.$CLASS['config']->base->locale .'.UTF8/LC_MESSAGES/knowledgeroot.mo', $CLASS['config']->base->locale);

// init language
$CLASS['language'] = new language();
$CLASS['language']->start($CLASS,$CLASS['config']->base->locale);

// addslashes on GET/POST
$CLASS['knowledgeroot']->addSlashesOnArray($_GET);
$CLASS['knowledgeroot']->addSlashesOnArray($_POST);
$CLASS['knowledgeroot']->addSlashesOnArray($_COOKIE);
$CLASS['knowledgeroot']->addSlashesOnArray($_REQUEST);
$CLASS['knowledgeroot']->addSlashesOnArray($_SERVER);

// init header
$CLASS['kr_header'] = new knowledgeroot_header();
$CLASS['kr_header']->start($CLASS);

// load extensions
$CLASS['kr_extension'] = new knowledgeroot_extension();
$CLASS['kr_extension']->start($CLASS,1);

// add favicon pointer
$CLASS['kr_header']->addheader("<link rel=\"shortcut icon\" href=\"../favicon.ico\" type=\"image/x-icon\" />");

// add generator
$CLASS['kr_header']->addheader("<meta name=\"generator\" content=\"Knowledgeroot - ".$version."\" />");

// set charset
if($CLASS['config']->base->charset != "") {
	if($CLASS['config']->base->charset == "utf8") {
		$charset = "utf-8";
	} else {
		$charset = $CLASS['config']->base->charset;
	}

	header("Content-Type: text/html; charset=".$charset."");
	$CLASS['kr_header']->addheader("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=" . $charset . "\" />");
}

// add javascript to htmlheader
$CLASS['kr_header']->addjssrc("../assets/jquery/jquery.min.js");
$CLASS['kr_header']->addjssrc("../assets/bootstrap/js/bootstrap.min.js");

$CLASS['kr_header']->addjssrc("../system/javascript/ajax-tree.js");
$CLASS['kr_header']->addjssrc("../system/javascript/messagebox.js");

// add bootstrap
$CLASS['kr_header']->addcsssrc("../assets/bootstrap/css/bootstrap.min.css");

// add fontawesome
$CLASS['kr_header']->addcsssrc("../assets/font-awesome/css/font-awesome.min.css");

// start all extensions
$CLASS['kr_extension']->start_extensions();

// add hook
$CLASS['hooks']->setHook("init_admin","init","end");
?>
