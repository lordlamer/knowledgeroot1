<?php
/**
 * Ritch Text Editor Class
 * This class is the basic class for all rte editors
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-rte.php 860 2009-09-25 12:15:19Z lordlamer $
 */
class rte extends extension_base {
	/**
	 * cols for textarea
	 * @param integer $cols
	 */
	var $cols = 75;

	/**
	 * rows for textare
	 * @param integer $rows
	 */
	var $rows = 20;

	/**
	 * id for textarea
	 * @param string $id
	 */
	var $id = "";

	/**
	 * name for textarea
	 * @param string $name
	 */
	var $name = "content";

	/**
	 * Show Text in a textare
	 * @param string $text
	 * @return string
	 */
	function show($text = "") {
		$out = "";

		$out .= "<textarea class=\"form-control\" ".(($this->id != "") ? "id=\"".$this->id."\" ": "").(($this->name != "") ? "name=\"".$this->name."\" " : "")."rows=\"".$this->rows."\" cols=\"".$this->cols."\">\n";
		$out .= $text;
		$out .= "</textarea>";

		return $out;
	}
}

?>
