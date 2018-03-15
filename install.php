<?php
header("Content-Type: text/html; charset=UTF-8");
?>
<!doctype html>
<html lang="en">
    <head>
		<title>Knowledgeroot Installation</title>
		<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <script src="assets/jquery/jquery.min.js" type="text/javascript"></script>
        <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css" type="text/css" />
        <script src="assets/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>

        <style>
            html {
                position: relative;
                min-height: 100%;
            }
            body {
                /* Margin bottom by footer height */
                margin-top: 60px;
            }
        </style>
    </head>
<body>

    <header>
        <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark" style="border-bottom: 3px solid #F88529;">
            <a class="navbar-brand" href="#">Knowledgeroot Installer</a>
        </nav>
    </header>
<?php
include_once("include/class-knowledgeroot-installer.php");

$install = new knowledgeroot_installer();
echo $install->mainInstall();

?>
</body>
</html>
