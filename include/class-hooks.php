<?php
/**
 * This Class inerhits functions that are used to handle hooks
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-hooks.php 860 2009-09-25 12:15:19Z lordlamer $
 */
class hooks {
	/**
	 * array $CLASS reference to global $CLASS variable
	 */
	var $CLASS;

	/**
	 * array $hooks array that is used to save the hooks
	 */
	var $hooks = array();
	
	/**
	 * init/start class
	 * @param array &$CLASS reference to global $CLASS variable
	 */
	function start(&$CLASS) {
		$this->CLASS =& $CLASS;
	}
	
	/**
	 * set hook in a function and will run functions that are registered for this hook
	 * @param string $classname class name for hook
	 * @param string $functionname function name for hook
	 * @param string $position position in this function for hook
	 */
	function setHook($classname,$functionname,$position) {
		if(isset($this->hooks[$classname][$functionname][$position]) && is_array($this->hooks[$classname][$functionname][$position])) {
			foreach($this->hooks[$classname][$functionname][$position] as $key => $value) {
				if(isset($this->hooks[$classname][$functionname][$position][$key]) && $this->hooks[$classname][$functionname][$position][$key] != "") {
					if(isset($this->CLASS[$key]) && is_object($this->CLASS[$key]) && $value != "") {
						if(method_exists($this->CLASS[$key],$value)) {
							$this->CLASS[$key]->$value();
						}
					} elseif(isset($this->CLASS['extension'][$key]) && is_object($this->CLASS['extension'][$key]['class']) && $value != "") {
						if(method_exists($this->CLASS['extension'][$key]['class'],$value)) {
							$this->CLASS['extension'][$key]['class']->$value();
						}
					}
				}
			}
		}
	}
	
	/**
	 * register a specific function in a class at a hook
	 * @param string $classname class name for hook
	 * @param string $functionname function name for hook
	 * @param string $position position in this function for hook
	 * @param string $myclass class that should be run if hook is called
	 * @param string $myfunction function that will be started if hook is called
	 */
	function execAtHook($classname,$functionname,$position,$myclass,$myfunction) {
		$this->hooks[$classname][$functionname][$position][$myclass] = $myfunction;
	}
}
?>
