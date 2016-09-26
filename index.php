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
<!DOCTYPE html>
<html lang="en">
<head>
<?php
  $CLASS['kr_header']->show_header();
?>
    
        <link href="system/javascript/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
        <link rel="stylesheet" href="system/javascript/fontawesome/css/font-awesome.min.css">
        <script src="system/javascript/jquery/jquery.min.js"></script>
        <script src="system/javascript/jquery-ui/jquery-ui.min.js"></script>
        <script src="system/javascript/bootstrap/js/bootstrap.min.js"></script>
        <script src="system/javascript/jquery-layout/jquery.layout-latest.min.js"></script>
        <link type="text/css" rel="stylesheet" href="system/javascript/jquery-layout/layout-default-latest.css" />
    	<script type="text/javascript">
	    $(document).ready(function() {
		    $('#sidebar-layout').layout({
			minSize: 300,
			west__size: 300,
			stateManagement__enabled: true,
			stateManagement__cookie__path: "/"
		    });
	    });
	</script>
</head>
<body class="claro" <?php if($CLASS['config']->menu->type == "slide") { echo "onload=\"Hide('tree');\""; } ?>>

<div style="display: none;" id="messagebox">
  <div id="msg" class="loading"><?php echo $CLASS['translate']->_('loading...'); ?></div>
</div>

<div id="mousemenu" style="display: none; position: absolute;">&nbsp;</div>
<div id="dragbox" style="display: none; position: absolute;">&nbsp;</div>

<nav class="navbar navbar-default navbar-fixed-top navbar-inverse" style="border-bottom: 3px solid #F88529;">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
        
<a class="navbar-brand" href="#" data-toggle="dropdown"><i class="fa fa-bars"></i></a>
<ul class="dropdown-menu">
    <li><a href="http://www.knowledgeroot.org" target="_blank"><i class="fa fa-rocket"></i>&nbsp;Knowledgeroot</a></li>
    <li><a href="http://docs.knowledgeroot.org" target="_blank"><i class="fa fa-book"></i>&nbsp;Docs</a></li>
    <li><a href="http://api.knowledgeroot.org" target="_blank"><i class="fa fa-puzzle-piece"></i>&nbsp;API</a></li>
</ul>

        
      <a class="navbar-brand" href="#">&fork;Knowledgeroot</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
      <form class="navbar-form navbar-left">
        <div class="form-group">
          <input type="text" class="form-control" placeholder="Search">
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
      </form>

    
        
    <?php
      // show top menu
      echo $CLASS['kr_extension']->show_menu("top");
    ?>
        
		<form id="change_language" action="index.php" method="post" class="navbar-form navbar-right">
			<input type="hidden" name="action" value="change_language" />
	<?php

	if (!isset ($_SESSION['language'])) { $_SESSION['language'] = ''; }

	echo $CLASS['language']->lang_dropdown("language", $_SESSION['language']);


	?>
		</form>
    
    <p class="navbar-text navbar-right"><?php echo $CLASS['translate']->_('User')  . ":&nbsp;" . $_SESSION['user']; ?></p>
    
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>

<a name="top"></a>

	<div id="sidebar-layout">
	    <div class="ui-layout-west" style="background-color: #f5f5f5;">
	<?php
	  // show tree
	  if (isset ($_SESSION['open'])) {
	    $CLASS['tree']->open = $_SESSION['open'];
	  }
	  $CLASS['tree']->buildTree(0);
	?>
	    </div>

	    <div class="ui-layout-center">
             
                                    <ol class="breadcrumb">
	<?php
	  // show path
	  if($CLASS['knowledgeroot']->checkRecursivPerm($_SESSION['cid'], $_SESSION['userid']) != 0) {
	    echo $CLASS['path']->getPath($_SESSION['cid']);
	  } else {
	    echo "<i class=\"fa fa-home fa-lg\"></i> /";
	  }

	if (!isset ($_SESSION['user'])) { $_SESSION['user'] = ''; }
	?>

			</ol>
             
	<?php
	  // show page content
	  $CLASS['kr_header']->show_messages();
	  $CLASS['kr_content']->show_content();
	?>
	    </div>
	</div>

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
