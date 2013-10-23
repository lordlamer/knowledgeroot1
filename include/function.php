<?php
/**
 *
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: function.php 860 2009-09-25 12:15:19Z lordlamer $
 */

/**
 *
 */
function getfilesize($byte,$precision = 2) {
  $a = 0;

  while($byte > 1024) {
    $byte = round($byte / 1024,$precision);
    $a++;
  }

	if($a == 0) $size = "B";
  elseif($a == 1) $size = "KB";
  elseif($a == 2) $size = "MB";
  elseif($a == 3) $size = "GB";
  else return "Wrong Size";

	$bsize = "$byte $size";

	return $bsize;
}

/**
 * Liefert eine Ziffer mit angehangener Groessenangabe zurueck
 *
 * @access  public
 * @param int $size
 * @return  mix Ziffer+Groessenangabe
 */
function getFormattedSize($size) {
  // Setup some common file size measurements.
  $kb = 1024; // Kilobyte
  $mb = 1024 * $kb; // Megabyte
  $gb = 1024 * $mb; // Gigabyte
  $tb = 1024 * $gb; // Terabyte
  // Get the file size in bytes.

  // If it's less than a kb we just return the size, otherwise we keep going until
  // the size is in the appropriate measurement range.
  switch ($size) {
    case $size < $mb: return round($size / $kb, 2)." KBytes"; break;
    case $size < $gb: return round($size / $mb, 2)." MBytes"; break;
    case $size < $tb: return round($size / $gb, 2)." GBytes"; break;
    case $size >= $tb: return round($size / $tb, 2)." TBytes"; break;
    default:
    case $size < $kb: return $size." Bytes"; break;
  }
}

?>