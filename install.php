<?php
header("Content-Type: text/html; charset=UTF-8");
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title>Knowledgeroot Installation</title>
		<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

		<!-- load the dojo toolkit base -->
		<script type="text/javascript" src="system/javascript/dojo/dojo/dojo.js"
			djConfig="parseOnLoad:true, isDebug:false">
		</script>

		<style type="text/css">
			@import "system/javascript/dojo/dijit/themes/claro/claro.css";
		</style>

		<script type="text/javascript">
			dojo.require("dijit.form.Button");
			dojo.require("dijit.form.Form");
			dojo.require("dijit.form.TextBox");
			dojo.require("dijit.form.ValidationTextBox");
			dojo.require("dijit.form.Select");
		</script>
	</head>
<body class="claro">
<?php
include_once("include/class-knowledgeroot-installer.php");

$install = new knowledgeroot_installer();
echo $install->mainInstall();

?>
</body>
</html>
