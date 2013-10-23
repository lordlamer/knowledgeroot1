<?php
/**
 * This class is for getting run time values
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-runtime.php 860 2009-09-25 12:15:19Z lordlamer $
 *
 * @example $contenttime = $CLASS['runtime']->start();
 * @example echo $contenttime = $CLASS['runtime']->getTime('stop');
 * or
 * @example $contenttime = $CLASS['runtime']->stop();
 * @example echo $contenttime = $CLASS['runtime']->getTime();
 */
class runtime {
	var $starttime = 0;
	var $stoptime = 0;
	var $runtime = 0;

	var $startArr = array();
	var $stopArr = array();
	var $runArr = array();

	/**
	 * start timer
	 * @param string $name
	 */
	function start($name = "") {
		$this->reset($name);
		$timer = microtime();

		if($name != "") {
			$this->startArr[$name] = ((double)strstr($timer, ' ') + (double)substr($timer,0,strpos($timer,' ')));
		} else {
			$this->starttime = ((double)strstr($timer, ' ') + (double)substr($timer,0,strpos($timer,' ')));
		}
	}

	/**
	 * stop timer
	 * @param string $name
	 */
	function stop($name = "") {
		$timer = microtime();
		if($name != "") {
			$this->stopArr[$name] = ((double)strstr($timer, ' ') + (double)substr($timer,0,strpos($timer,' ')));
		} else {
			$this->stoptime = ((double)strstr($timer, ' ') + (double)substr($timer,0,strpos($timer,' ')));
		}
	}

	/**
	 * return runtime value
	 *
	 * @param string $name
	 * @param mixed	$stop - is this set, call stop()
	 * @return float
	 */
	function getTime($name = "", $stop = '') {
		if ($stop != '') {
			$this->stop($name);
		}

		if($name != "") {
			return sprintf('%2.3f', $this->stopArr[$name] - $this->startArr[$name]);
		} else {
			return sprintf('%2.3f', $this->stoptime - $this->starttime);
		}
	}

	/**
	 * reset all runtime vars
	 * @param string $name
	 * @param bool $clearAll
	 */
	function reset($name = "", $clearAll = false) {
		if($name != "" || $clearAll == true) {
			$this->startArr = array();
			$this->stopArr = array();
			$this->runArr = array();
		}

		if($name == "" || $clearAll == true) {
			$this->starttime = 0;
			$this->stoptime = 0;
			$this->runtime = 0;
		}
	}
}

?>
