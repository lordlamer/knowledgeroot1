<?php
/**
 * Knowledgeroot is published under the GNU GPL! Read LICENSE
 *
 * @package Knowledgeroot
 * @author Frank Habermann <lordlamer@lordlamer.de>
 * @author Robert Scholz <scholzrobert@web.de>
 * @version $Id: index.php 1071 2011-05-08 20:28:39Z lordlamer $
 */

// timer
$timer = microtime();
$starttime = ((double)strstr($timer, ' ') + (double)substr($timer,0,strpos($timer,' ')));

if (!is_file("config/app.ini")) {
	echo "<html><body>No configuration file found! Please make a <a href=\"install.php\">install</a>!</body></html>";
	exit();
}

// load requiered files
require_once ('include/init.php');


/********************
 * This is the end of initialisation
 * Now do header work
 ********************/

if ($CLASS['config']->base->charset != '') {
  header("Content-Type: text/html; charset=".$CLASS['config']->base->charset);
}

?>
<!doctype html>
<html lang="en">
<head>
<?php
  $CLASS['kr_header']->show_header();
?>
</head>
<body class="claro" <?php if($CLASS['config']->menu->type == "slide") { echo "onload=\"Hide('tree');\""; } ?>>

<div style="display: none;" id="messagebox">
  <div id="msg" class="loading"><?php echo $CLASS['translate']->_('loading...'); ?></div>
</div>

<div id="mousemenu" style="display: none; position: absolute;">&nbsp;</div>
<div id="dragbox" style="display: none; position: absolute;">&nbsp;</div>

<a name="top"></a>

<nav class="navbar navbar-expand-md fixed-top navbar-dark bg-dark" style="border-bottom: 3px solid #F88529;">
    <a class="navbar-brand" href="#"><?php echo $CLASS['config']->base->title; ?></a>
    <button class="navbar-toggler p-0 border-0" type="button" data-toggle="offcanvas">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="navbar-collapse offcanvas-collapse nav-justified" id="navbarsExampleDefault">
        <?php
        // show top menu
        echo $CLASS['kr_extension']->show_menu("top");
        ?>

        <form class="form-inline my-2 my-lg-0" id="change_language" action="index.php" method="post" style="margin-right: 5px;">
            <input type="hidden" name="action" value="change_language" />
            <?php

            if (!isset ($_SESSION['language'])) { $_SESSION['language'] = ''; }

            echo $CLASS['language']->lang_dropdown("language", $_SESSION['language']);

            if (!$CLASS['config']->tree->ajax) {
                echo '<input class="button" type="submit" name="submit1" value="'.$CLASS['translate']->_('change').'" />'."\n";
            }
            ?>
        </form>

        <form class="form-inline my-2 my-lg-0" action="index.php" method="post" style="margin-right: 5px;">
            <input class="form-control mr-sm-2" type="text" name="search" placeholder="<?php echo $CLASS['translate']->_('Search'); ?>" aria-label="Search" value="<?php if(isset ($_GET['action']) && $_GET['action'] == "showsearch" && isset ($_GET['key']) && $_GET['key'] != "" && isset($_SESSION['search'][$_GET['key']])) { echo str_replace('&amp;quot;','&quot;',htmlspecialchars(stripslashes($_SESSION['search'][$_GET['key']]))); } ?>">
            <input type="hidden" name="submit" value="GO" />
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit" name="submit"><?php echo $CLASS['translate']->_('GO'); ?></button>
        </form>

        <span class="navbar-text">
            <?php echo $CLASS['translate']->_('User')  . ":&nbsp;" . $_SESSION['user']; ?>
        </span>
    </div>
</nav>

<nav class="navbar fixed-bottom navbar-light bg-light justify-content-end">
        <span class="navbar-text">
            <a href="http://www.knowledgeroot.org">Knowledgeroot</a> - <?php echo $CLASS['translate']->_('version') . ":&nbsp;" . $CLASS['config']->base->version; ?>
        </span>
</nav>

<nav aria-label="breadcrumb" style="margin-top:55px;">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><i class="fa fa-home fa-lg"></i> </li>
    <?php
	  // show path
	  if($CLASS['knowledgeroot']->checkRecursivPerm($_SESSION['cid'], $_SESSION['userid']) != 0) {
	    echo $CLASS['path']->getPath($_SESSION['cid']);
	  }
	  ?>
    </ol>
</nav>

<table border="0" cellpadding="0" cellspacing="0" width="100%">

	<tr>
	 <td id="treecontainer">

	   <div id="tree" <?php if($CLASS['config']->menu->type == "static") { echo "style=\"display:block;\""; } else { echo "style=\"display:none; position:absolute;\""; } ?>>
	<?php
	  // show tree
	  if (isset ($_SESSION['open'])) {
	    $CLASS['tree']->open = $_SESSION['open'];
	  }
	  $CLASS['tree']->buildTree(0);
	?>
	  </div>
	 </td>
	 <td id="contentcontainer">
	<?php
	  // show page content
	  $CLASS['kr_header']->show_messages();
	  $CLASS['kr_content']->show_content();
	?>
	 </td>
	</tr>
</table>

<?php
  // show developer toolbar
  if($CLASS['config']->development->toolbar) {
    echo "<div id=\"footer\">\n";
    echo "<p>"."\n";
    echo "developer toolbar: <a href=\"http://forum.linuxdelta.de\" target=\"_blank\">forum</a>"."\n";
    echo "<a href=\"http://lists.knowledgeroot.org/cgi-bin/mailman/listinfo\" target=\"_blank\">mailinglist</a>"."\n";
    echo "<a href=\"http://www.knowledgeroot.org\" target=\"_blank\">project page</a>"."\n";
    echo "<a href=\"http://trac.knowledgeroot.org\" target=\"_blank\">bug tracker</a>"."\n";
    echo $CLASS['kr_extension']->show_menu("toolbar");
    echo "</p>"."\n";
    echo "</div>\n";
  }

  // do last cleanups
  $_SESSION['firstrun'] = 0;

  // show querys - only for debug
  if($CLASS['config']->development->sqldebug) {
    echo "querys: " . $CLASS['db']->querys;
    echo $CLASS['error']->view_array($CLASS['db']->query_cache);
  }

  // close db connection
  $CLASS['db']->close();

  if($CLASS['config']->development->runtime) {
    $timer = microtime();
    $stoptime = ((double)strstr($timer, ' ') + (double)substr($timer,0,strpos($timer,' ')));
    echo "<!-- runtime: ".sprintf('%2.3f', $stoptime - $starttime)." -->";
  }
?>
</body>
</html>
