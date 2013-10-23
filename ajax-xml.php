<?php
/*
 * Knowledgeroot
 * return XML for ajax
 * Frank Habermann
 */

if(!is_file("config/app.ini")) {
	exit();
}

// load requiered files
require_once("include/init.php");
require_once("include/class-ajax-xml.php");

// init ajax class
$CLASS['ajax'] = new ajax_xml();
$CLASS['ajax']->start($CLASS);

// check what to do
$CLASS['ajax']->check_vars();

// set xml header
header("Content-Type: text/xml");

// return xmlcode
echo $CLASS['ajax']->get_xml();

?>
