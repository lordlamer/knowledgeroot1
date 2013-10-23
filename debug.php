<?php
// is not enabled at default
// to collect debug informations comment the next line
die();

$CLASS = array();

require_once("include/version.php");

?>
<html>
<body>
<h2>Knowledgeroot debugcollector</h2>
<?php
echo "knowledgeroot version: " . $version . "<br>\n";
echo "config available:" . (is_file("config/app.ini")? "yes" : "no") . "<br>\n";

if(is_file("config/app.ini")) {
	include('lib/Zend/Config/Ini.php');

	// init config
	$CLASS['config'] = new Zend_Config_Ini('config/app.ini');

	echo "database type: " . $CLASS['config']->db->adapter . "<br>\n";
}

if(isset($_SERVER["SERVER_SOFTWARE"])) {
	echo "server software:" . $_SERVER["SERVER_SOFTWARE"] . "<br>\n";
}

if(isset($_SERVER["HTTP_USER_AGENT"])) {
	echo "user agent:" . $_SERVER["HTTP_USER_AGENT"] . "<br>\n";
}

echo "php version: " . phpversion() . "<br>\n";
echo "os: " . php_uname() . "<br>\n";

?>
</body>
</html>
