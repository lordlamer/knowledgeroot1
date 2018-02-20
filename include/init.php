<?php
/**
 *
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: init.php 1159 2011-07-20 20:47:07Z lordlamer $
 */

/**
 * autoloader function for classes
 * @param string $class name of class
 */
function __autoload($class) {
        Zend_Loader::loadClass($class);
}

// base path
$base_path = realpath(dirname(__FILE__).'/../') . '/';

// set include path
set_include_path($base_path . '/lib/' . PATH_SEPARATOR . get_include_path());

// load required files
require_once('Zend/Loader.php');
require_once($base_path."include/version.php");
require_once($base_path."include/class-session.php");
require_once($base_path."include/class-runtime.php");
require_once($base_path."include/class-tree.php");
require_once($base_path."include/function.php");
require_once($base_path."include/class-knowledgeroot.php");
require_once($base_path."include/class-knowledgeroot-header.php");
require_once($base_path."include/class-knowledgeroot-content.php");
require_once($base_path."include/class-knowledgeroot-themes.php");
require_once($base_path."include/class-language.php");
require_once($base_path."include/class-email-notification.php");
require_once($base_path."include/class-knowledgeroot-extension.php");
require_once($base_path."include/class-extension-base.php");
require_once($base_path."include/class-default-menu.php");
require_once($base_path."include/class-error.php");
require_once($base_path."include/class-knowledgeroot-auth.php");
require_once($base_path."include/class-hooks.php");
require_once($base_path."include/class-rte.php");
require_once($base_path."include/class-db-result.php");
require_once($base_path."include/class-db-core.php");
require_once($base_path."include/class-highlight.php");
require_once($base_path."include/class-search-string-parser.php");

// this is the variable where all classes are in
$CLASS = array();

// check for app.ini and load config
if(!is_file($base_path.'config/app.ini')) {
	die('No app.ini found in config folder! Stop working here!');
}

// init config
$CLASS['config'] = new Zend_Config_Ini($base_path.'config/app.ini', null, array('allowModifications' => true));

// init error
$CLASS['error'] = new knowledgeroot_error();
$CLASS['error']->start($CLASS);

// define runtimer
$CLASS['runtime'] = new runtime();

// load databaseclass
switch($CLASS['config']->db->adapter) {
	case 'mysql':
		require_once($base_path."include/class-mysql.php");
		break;
	case 'mysqli':
		require_once($base_path."include/class-mysqli.php");
		break;
	case 'pgsql':
		require_once($base_path."include/class-pgsql.php");
		break;
	case 'mdb2':
		require_once($base_path."include/class-mdb2.php");
		break;
	case 'sqlite':
		require_once($base_path."include/class-sqlite.php");
		break;
	case 'oracle':
		require_once($base_path."include/class-oracle.php");
		break;
}

// check if database class is loaded
if(!class_exists('db', false)) {
	echo "Could not load database class. Check your name for the database adapter!\n";
	exit();
}

// init hooks
$CLASS['hooks'] = new hooks();
$CLASS['hooks']->start($CLASS);

// init error
$CLASS['auth'] = new knowledgeroot_auth();
$CLASS['auth']->start($CLASS);

// init databaseclass
$CLASS['db'] = new db();
$CLASS['db']->start($CLASS);

// connect to database
$CLASS['db']->connect($CLASS['config']->db->params->host,$CLASS['config']->db->params->username,$CLASS['config']->db->params->password,$CLASS['config']->db->params->dbname,$CLASS['config']->db->schema,$CLASS['config']->db->encoding);

// init cache
if(!is_dir($base_path.$CLASS['config']->cache->path)) {
	die('Cache path is not a directory:'.$base_path.$CLASS['config']->cache->path);
}
if(!is_writeable($base_path.$CLASS['config']->cache->path)) {
	die('Cache path is not writeable:'.$base_path.$CLASS['config']->cache->path);
}
$CLASS['cache'] = Zend_Cache::factory('Core', 'File', $CLASS['config']->cache->options->toArray(), array('cache_dir' => $base_path.$CLASS['config']->cache->path));

// init knowledgerootclass
$CLASS['knowledgeroot'] = new knowledgeroot();
$CLASS['knowledgeroot']->start($CLASS);

// addslashes on GET/POST/COOKIE/REQUEST/SERVER
$CLASS['knowledgeroot']->addSlashesOnArray($_GET);
$CLASS['knowledgeroot']->addSlashesOnArray($_POST);
$CLASS['knowledgeroot']->addSlashesOnArray($_COOKIE);
$CLASS['knowledgeroot']->addSlashesOnArray($_REQUEST);
$CLASS['knowledgeroot']->addSlashesOnArray($_SERVER);

// init session
$CLASS['session'] = new session();
$CLASS['session']->start($CLASS);
$CLASS['session']->startSession(md5($CLASS['config']->base->base_url));
if(!$CLASS['session']->checkSession()) {
	die("Wrong session!");
}

// init gettext
Zend_Translate::setCache($CLASS['cache']);
if(isset($_SESSION['language']) && $_SESSION['language'] != '' && (is_file($base_path.'system/language/'.$_SESSION['language'].'.UTF8/LC_MESSAGES/knowledgeroot.mo') || is_file($base_path.'system/language/'.$_SESSION['language'].'/LC_MESSAGES/knowledgeroot.mo'))) {
	$language = str_replace(".UTF8","",$_SESSION['language']);
	$CLASS['translate'] = new Zend_Translate('gettext', $base_path.'system/language/'.$language.'.UTF8/LC_MESSAGES/knowledgeroot.mo', $language);
	if($language != $CLASS['config']->base->locale) {
		$CLASS['translate']->addTranslation($base_path.'system/language/'.$CLASS['config']->base->locale.'.UTF8/LC_MESSAGES/knowledgeroot.mo', $CLASS['config']->base->locale);
		$CLASS['translate']->setLocale($language);
	}
} elseif(is_file($base_path.'system/language/'.$CLASS['config']->base->locale .'.UTF8/LC_MESSAGES/knowledgeroot.mo')) {
	$CLASS['translate'] = new Zend_Translate('gettext', $base_path.'system/language/'.$CLASS['config']->base->locale .'.UTF8/LC_MESSAGES/knowledgeroot.mo', $CLASS['config']->base->locale);
} else {
	$CLASS['translate'] = new Zend_Translate('gettext', $base_path.'system/language/en_US.UTF8/LC_MESSAGES/knowledgeroot.mo', 'en_US');
}

// init language
$CLASS['language'] = new language();
$CLASS['language']->start($CLASS,$CLASS['config']->base->locale);

// init themes
$CLASS['themes'] = new knowledgeroot_themes();
$CLASS['themes']->start($CLASS);

// init email notification class
$CLASS['notification'] = new knowledgeroot_notification($CLASS);

// load rte editor class
$CLASS['rte'] = new rte($CLASS);

// load string-highlight class
$CLASS['highlight'] = new highlight();

// load extensions
$CLASS['kr_extension'] = new knowledgeroot_extension();
$CLASS['kr_extension']->start($CLASS);

// init tree
$CLASS['tree'] = new categoryTree();
$CLASS['tree']->start($CLASS);

// init tree_path
$CLASS['path'] = new pathTree();
$CLASS['path']->start($CLASS);

// init header
$CLASS['kr_header'] = new knowledgeroot_header();
$CLASS['kr_header']->start($CLASS);

// init content
$CLASS['kr_content'] = new knowledgeroot_content();
$CLASS['kr_content']->start($CLASS);

// start all extensions
$CLASS['kr_extension']->start_extensions();

// check header variables
$CLASS['kr_header']->check_vars();

// check if site is a download
$CLASS['kr_header']->check_download();

// load default menu
$CLASS['default_menu'] = new default_menu();
$CLASS['default_menu']->start($CLASS);

// add javascript to htmlheader
if(!defined("KR_INCLUDE_PREFIX")) {
	define("KR_INCLUDE_PREFIX", "");
}

$CLASS['kr_header']->addjssrc(KR_INCLUDE_PREFIX."assets/jquery/jquery.min.js");
$CLASS['kr_header']->addjssrc(KR_INCLUDE_PREFIX."assets/bootstrap/js/bootstrap.min.js");
$CLASS['kr_header']->addjssrc(KR_INCLUDE_PREFIX."system/javascript/prototype.js");
$CLASS['kr_header']->addjssrc(KR_INCLUDE_PREFIX."system/javascript/scriptaculous.js");
//$CLASS['kr_header']->addjssrc(KR_INCLUDE_PREFIX."system/javascript/effects.js");
//$CLASS['kr_header']->addjssrc(KR_INCLUDE_PREFIX."system/javascript/dragdrop.js");
$CLASS['kr_header']->addjssrc(KR_INCLUDE_PREFIX."system/javascript/showhide.js");
$CLASS['kr_header']->addjssrc(KR_INCLUDE_PREFIX."system/javascript/ajax-tree.js");
$CLASS['kr_header']->addjssrc(KR_INCLUDE_PREFIX."system/javascript/messagebox.js");

// add bootstrap
$CLASS['kr_header']->addcsssrc(KR_INCLUDE_PREFIX."assets/bootstrap/css/bootstrap.min.css");

// add fontawesome
$CLASS['kr_header']->addcsssrc(KR_INCLUDE_PREFIX."assets/font-awesome/css/font-awesome.min.css");

// check if contextmenus are enabled
if($CLASS['config']->menu->context) {
	// load js for contextmenus
	$CLASS['kr_header']->addjssrc(KR_INCLUDE_PREFIX."system/javascript/knowledgeroot-menu.js");
}

// add js for dragbox if dragdrop is enabled
if($CLASS['config']->menu->dragdrop) {
	$CLASS['kr_header']->addjssrc(KR_INCLUDE_PREFIX."system/javascript/dragbox.js");
}

// add loadingmessage for messagebox
$CLASS['kr_header']->addjs("var msgboxloading = '".$CLASS['translate']->_('loading...')."';");

// add theme
$CLASS['kr_header']->addcsssrc($CLASS['themes']->load_theme());

// add favicon pointer
$CLASS['kr_header']->addheader("<link rel=\"shortcut icon\" href=\"".KR_INCLUDE_PREFIX."favicon.ico\" type=\"image/x-icon\" />");

// add generator
if(isset($version))
	$CLASS['kr_header']->addheader("<meta name=\"generator\" content=\"Knowledgeroot - ".$version."\" />");

// add dojo to html header for loading
if($CLASS['config']->javascript->cdn) {
$CLASS['kr_header']->addheader('
<!-- load the dojo toolkit base -->
<script src="'.$CLASS['config']->javascript->cdn_url.'" type="text/javascript" djConfig="parseOnLoad:false, isDebug:false"></script>
', true);
} else {
$CLASS['kr_header']->addheader('
<!-- load the dojo toolkit base -->
<script type="text/javascript" src="system/javascript/dojo/dojo/dojo.js"
 djConfig="parseOnLoad:false, isDebug:false">
</script>
', true);
}

$CLASS['kr_header']->addheader('
<!-- load the dojo toolkit base -->
<script type="text/javascript">
	dojo.require("dojo.parser");
	dojo.require("dijit.MenuBar");
	dojo.require("dijit.MenuBarItem");
	dojo.require("dijit.MenuSeparator");
	dojo.require("dijit.form.Select");
	dojo.require("dijit.TitlePane");
	dojo.require("dijit.form.Button");
	dojo.require("dijit.form.TextBox");
	dojo.require("dijit.form.MultiSelect");
	dojo.require("dojox.data.HtmlStore");
	dojo.require("dojox.grid.DataGrid");
	dojo.require("dijit.form.CheckBox");
	dojo.require("dijit.layout.TabContainer");
	dojo.require("dijit.layout.ContentPane");

	dojo.addOnLoad(function(){
		dojo.parser.parse();
	});
</script>
');

if($CLASS['config']->javascript->cdn) {
	$CLASS['kr_header']->addheader('
<style type="text/css">
@import "'.$CLASS['config']->javascript->cdn_css_base_url.'/dijit/themes/claro/claro.css";
@import "'.$CLASS['config']->javascript->cdn_css_base_url.'/dojo/resources/dojo.css";
</style>
', true);
} else {
	$CLASS['kr_header']->addheader('
<style type="text/css">
@import "'.KR_INCLUDE_PREFIX.'system/javascript/dojo/dijit/themes/claro/claro.css";
@import "'.KR_INCLUDE_PREFIX.'system/javascript/dojo/dojo/resources/dojo.css";
</style>
', true);
}

// add hook
$CLASS['hooks']->setHook("init","init","end");
?>
