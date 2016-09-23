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

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Knowledgeroot</title>
        
	<link href="admin.css" rel="stylesheet">

        <link href="../system/javascript/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
        <link rel="stylesheet" href="../system/javascript/fontawesome/css/font-awesome.min.css">
        <script src="../system/javascript/jquery/jquery.min.js"></script>
        <script src="../system/javascript/jquery-ui/jquery-ui.min.js"></script>
        <script src="../system/javascript/bootstrap/js/bootstrap.min.js"></script>
        <script src="../system/javascript/jquery-layout/jquery.layout-latest.min.js"></script>
        <link type="text/css" rel="stylesheet" href="../system/javascript/jquery-layout/layout-default-latest.css" />

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
<body>
<?php
if (isset ($_POST['login']) and $_POST['login'] == "true" && $_POST['user'] != "" && $_POST['pass'] != "") {
	echo "<div id=\"well\">loginhash: ".md5($_POST['user'] . $_POST['pass'])."</div>\n";
}
?>

<div class="well">
    <script>
        $(".alert").alert()
    </script>
    <div class="page-header">
        <h2>Knowledgeroot Admin</h2>
    </div>
    <form role="form" class="form-horizontal" action="./login" method="post" name="loginformular">
        <input type="hidden" name="" value="" />
        <input type="hidden" name="login" value="true" />

        <div class="form-group">
            <label class="col-sm-1 control-label" for="user">User</label>

            <div class="input-group col-sm-3">
                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                <input type="text" class="form-control" id="user" name="user" placeholder="User" required="required" autofocus="autofocus">
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-1 control-label" for="password">Password</label>

            <div class="input-group col-sm-3">
                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                <input type="password" class="form-control" id="password" name="pass" placeholder="Password" required="required" autofocus="autofocus">
            </div>
        </div>

        <div class="col-sm-3 col-sm-offset-1">
            <input class="btn btn-primary" type="submit" name="submit" value="<?php echo $CLASS['translate']->_('login'); ?>" />
        </div>
    </form>

    <script type="text/javascript">
        <!--
        document.loginformular.user.focus();
        //-->
    </script>
</div>

<?php
} else {
?>
<body>
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

<nav class="navbar navbar-default navbar-fixed-top navbar-inverse">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">&fork;Knowledgeroot</a>
    </div>
  </div><!-- /.container-fluid -->
</nav>
    
<div style="display: none;" id="messagebox">
  <div id="msg" class="loading">lade...</div>
</div>

<div id="sidebar-layout">
	    <div class="ui-layout-west" style="background-color: #f5f5f5;">
<?php
$CLASS['kr_extension']->show_admin_menu("admin");
?>
	    </div>

	    <div class="ui-layout-center">  
<?php
$CLASS['kr_extension']->show_ext_content();
?>
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
