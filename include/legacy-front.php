<?php
/**
 * Knowledgeroot is published under the GNU GPL! Read LICENSE
 *
 * Legacy front page flow. This file contains the former index.php and is
 * included by the Slim catch-all route (see index.php). It writes its
 * output with echo; the bridge captures it into the PSR-7 response.
 *
 * @package Knowledgeroot
 * @author Frank Habermann <lordlamer@lordlamer.de>
 * @author Robert Scholz <scholzrobert@web.de>
 */

// keep $CLASS in the global scope - parts of the legacy code
// (e.g. the gettext() fallback) access it via $GLOBALS['CLASS']
global $CLASS;

$timer = microtime();
$starttime = ((double)strstr($timer, ' ') + (double)substr($timer,0,strpos($timer,' ')));

if (!is_file("config/app.ini")) {
	echo "<html><body>No configuration file found! Please make a <a href=\"install.php\">install</a>!</body></html>";
	exit();
}

require_once ('include/init.php');

if ($CLASS['config']->base->charset != '') {
	header("Content-Type: text/html; charset=".$CLASS['config']->base->charset);
}

if(!isset($_SESSION['userid']) || $_SESSION['userid'] == '') {
	$_SESSION['userid'] = 0;
}

if(!isset($_SESSION['groupid']) || $_SESSION['groupid'] == '') {
	$_SESSION['groupid'] = 0;
}

if(!isset($_SESSION['user']) || $_SESSION['user'] == '') {
	$_SESSION['user'] = 'guest';
}

$head = kr_capture(function () use (&$CLASS) {
	$CLASS['kr_header']->show_header();
});

if (isset ($_SESSION['open'])) {
	$CLASS['tree']->open = $_SESSION['open'];
}

$tree = kr_capture(function () use (&$CLASS) {
	$CLASS['tree']->buildTree(0);
});

$messages = kr_capture(function () use (&$CLASS) {
	$CLASS['kr_header']->show_messages();
});

$content = kr_capture(function () use (&$CLASS) {
	$CLASS['kr_content']->show_content();
});

if($CLASS['config']->development->sqldebug) {
	$debug = "<span class=\"badge badge-primary\">Queries: " . $CLASS['db']->querys . "</span>";
	$debug .= $CLASS['error']->view_array($CLASS['db']->query_cache);
} else {
	$debug = '';
}

if($CLASS['config']->development->runtime) {
	$timer = microtime();
	$stoptime = ((double)strstr($timer, ' ') + (double)substr($timer,0,strpos($timer,' ')));
	$runtime = "<!-- runtime: ".sprintf('%2.3f', $stoptime - $starttime)." -->";
} else {
	$runtime = '';
}

$path = '';
if(isset($_SESSION['cid']) && $CLASS['knowledgeroot']->checkRecursivPerm($_SESSION['cid'], $_SESSION['userid']) != 0) {
	$path = $CLASS['path']->getPath($_SESSION['cid']);
}

$searchValue = '';
if(isset($_GET['action']) && $_GET['action'] == 'showsearch' && isset($_GET['key']) && $_GET['key'] != '' && isset($_SESSION['search'][$_GET['key']])) {
	$searchValue = str_replace('&amp;quot;', '&quot;', htmlspecialchars(stripslashes($_SESSION['search'][$_GET['key']])));
}

$_SESSION['firstrun'] = 0;
$CLASS['db']->close();

echo kr_render($CLASS, 'front.html', array(
	'head' => $head,
	'loading_text' => $CLASS['translate']->_('loading...'),
	'site_title' => $CLASS['config']->base->title,
	'version_label' => $CLASS['translate']->_('version'),
	'site_version' => $CLASS['config']->base->version,
	'user' => $_SESSION['user'],
	'top_menu' => $CLASS['kr_extension']->show_menu('top'),
	'language_dropdown' => $CLASS['language']->lang_dropdown('language', (isset($_SESSION['language']) ? $_SESSION['language'] : '')),
	'search_value' => $searchValue,
	'search_placeholder' => $CLASS['translate']->_('Search'),
	'search_action_text' => $CLASS['translate']->_('GO'),
	'user_label' => $CLASS['translate']->_('User'),
	'path' => $path,
	'tree' => $tree,
	'messages' => $messages,
	'content' => $content,
	'debug' => $debug,
	'runtime' => $runtime,
));
