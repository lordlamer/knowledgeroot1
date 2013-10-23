<?php
/**
 * This Class is for error handling and debug output
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-error.php 941 2010-06-07 20:57:58Z lordlamer $
 */
class knowledgeroot_error {
	/**
	 * array $CLASS global array to access all classes
	 */
	var $CLASS = array();

	/**
	 * array $CONF config array
	 */
	var $CONF = array();

	/**
	 * bool $showBrOnScreen enable/disable output of br tags for screen messages
	 */
	var $showBrOnScreen = true;

	/**
	 * bool $setPhpError enable/disable changing php error configuration
	 */
	var $setPhpError = false;

	/**
	 * string $setPhpErrorReporting set the php error reporting
	 * for developement use E_ALL and for stable production use E_ALL & ~E_NOTICE 
	 */
	var $setPhpErrorReporting = E_ALL;

	/**
	 * init/start class
	 */
	function start(&$CLASS) {
		$this->CLASS =& $CLASS;
		if(isset($this->CLASS['config']->log)) $this->CONF = $this->CLASS['config']->log->toArray();

		if(isset($this->CONF['setPhpError']) && is_bool($this->CONF['setPhpError'])) {
			$this->setPhpError = $this->CONF['setPhpError'];
		}

		if(isset($this->CONF['setPhpErrorReporting']) && $this->CONF['setPhpErrorReporting'] != "") {
			$this->setPhpErrorReporting = $this->CONF['setPhpErrorReporting'];
		}

		if($this->setPhpError == true) {
			// set error reporting
			ini_set('error_reporting', $this->setPhpErrorReporting);

			if(isset($this->CONF['type']) && $this->CONF['type'] != "" && $this->CONF['type'] != "none") {
                                $type = explode(",", $this->CONF['type']);

				$found_screen = false;
				$found_apache = false;
				$found_logfile = false;

				foreach($type as $key => $value) {
					if(trim($value) == "screen") {
						ini_set('display_errors', 1);
						$found_screen = true;
					}

					if(trim($value) == "apache") {
						ini_set('error_log', 'syslog');
						$found_apache = true;
					}

					if(trim($value) == "logfile") {
						if(isset($this->CONF['logfile']) && $this->CONF['logfile'] != "") {
							ini_set('error_log', $this->CONF['logfile']);
						} else {
							ini_set('error_log', 'syslog');
						}

						$found_logfile = true;
					}
				}

				// at default disable error msg to screen
				if($found_screen == false) {
					ini_set('display_errors', 0);
				}

				// at default disable error msg to syslog or logfile
				if($found_apache == false && $found_logfile == false) {
					ini_set('error_log', '');
				}
			}
		}
	}

	/**
	* Debug_Out gibt Variableninfos aus
	*
	* Aufrufbeispiel:
	* $a = array(1, 2, array("a", "b", "c"));
	* Debug_Out($a);
	*
	* Aufruf in Knowledgeroot:
	* $this->CLASS['error']->DebugOut($foo)
	*
	* @param  mixed auszugebene Variablendaten
	* @param mixed Anweisungen fuer die switch-Abfrage
	*        'sql' - speziell formatierte Ausgabe
	* @param bool  0 (default): Ausgabe erfolgt per var_export()
	*        1: Ausgabe erfolgt per var_dump()
	*/
	function Debug_Out($input, $spezial = '', $dumpexport = 0) {
		//echo '<br />';
		$return = '';
		switch ($spezial) {
		case 'sql':
			if ($spezial == 'sql') {
				$return .= "\n".nl2br($input)."<br /><br />\n";
			}
		break;
		default:
			if (is_array($input) or is_object($input)) {
				ob_start();
				if ($dumpexport) { var_dump ($input); } else { var_export ($input); }
				$return .= "\n<pre>".ob_get_contents().'</pre><br />'."\n";
				ob_end_clean();
			}
			if (!is_array($input)) {
				$return .= $input.'<br />'."\n";
			}
			break;
		} // switch ($spezial)
		flush();

		// setze vor die Ausgabe einen deutlichen Hinweis auf die Debug-Ausgabe
		$return = '<span style="font-weight: bold;">DEBUGOUT: </span>'.
		$return."\n";
		// gib die Debuginfos aus
		echo $return;

		return true;
	}


	/**
	* DebugOut
	*
	* @see Debug_Out
	*/
	function DebugOut($input, $spezial = '', $dumpexport = 0) {
		return $this->Debug_Out($input, $spezial, $dumpexport);
	}

	/**
	 * Returns HTML-code, which is a visual representation of a multidimensional array
	 * use $this->print_array() in order to print an array
	 * Returns false if $array_in is not an array
	 * Usage:
	 * echo $this->CLASS['error']->view_array($foo)
	 *
	 * @param	array		Array to view
	 * @return	string		HTML output
	 */
	function view_array($array_in) {
		if (is_array($array_in)) {
			$result='<table border="1" cellpadding="1" cellspacing="0" bgcolor="white">';
			if (!count($array_in))	{$result.= '<tr><td><font face="Verdana,Arial" size="1"><b>'.htmlspecialchars("EMPTY!").'</b></font></td></tr>';}
			while (list($key,$val)=each($array_in))	{
				$result.= '<tr><td><font face="Verdana,Arial" size="1">'.htmlspecialchars((string)$key).'</font></td><td>';
				if (is_array($array_in[$key]))	{
					$result.=$this->view_array($array_in[$key]);
				} else
					$result.= '<font face="Verdana,Arial" size="1" color="red">'.nl2br(htmlspecialchars((string)$val)).'<br /></font>';
				$result.= '</td></tr>';
			}
			$result.= '</table>';
		} else	{
			echo "noarray";
			$result  = false;
		}
		return $result;
	}
	
	/**
	 * save message to logfile
	 * Usage: $this-CLASS['error']->log("mymessage");
	 *
	 * @param	string		string to log
	 * @param	string		sourcefile
	 */
	function log($msg,$level = 1,$backtrackarr = "") {
		if($level > 0 || (isset($this->CONF['level']) && $this->CONF['level'] > 0)) {
			if(isset($this->CONF['type']) && $this->CONF['type'] != "" && $this->CONF['type'] != "none") {
				$type = explode(",", $this->CONF['type']);
				$time = date("Y-m-d H:i:s", time());

				// build message
				$out = "[" . $time . "] [ERROR]: " . $msg . "\n";
				$backtrack = "";

				if(is_array($backtrackarr)) {
					foreach($backtrackarr as $keyb => $valueb) {
						if(!is_array($valueb)) {
							$backtrack .= "[" . $time . "] [BACKTRACK]: " . $backtrackarr[$keyb] . "\n";
						}
					}
				} elseif($backtrackarr != "") {
					$backtrack .= "[" . $time . "] [BACKTRACK]: " . $backtrackarr . "\n";
				}

				foreach($type as $key => $value) {
					$value = trim($value);
					if($value == "screen") {
						echo $out . (($this->showBrOnScreen == true) ? "<br />" : "")  . "\n";
						if($backtrack != "") echo $backtrack . (($this->showBrOnScreen == true) ? "<br />" : "")  . "\n";
					}
					if($value == "apache") {
						error_log($out . $backtrack);
					}
					if($value == "mail") {
						if(isset($this->CONF['mail']) && $this->CONF['mail'] != "" && $this->CONF['mail_extra_header'] != "") {
							error_log($out . $backtrack,1,$this->CONF['mail'],$this->CONF['mail_extra_header']);
						} elseif(isset($this->CONF['mail']) && $this->CONF['mail'] != "") {
							error_log($out . $backtrack,1,$this->CONF['mail']);
						}
					}
					if($value == "logfile") {
						if(isset($this->CONF['logfile']) && $this->CONF['logfile'] != "") {
							error_log($out . $backtrack,3,$this->CONF['logfile']);
						} else {
							error_log($out . $backtrack);
						}
					}
				}
			}
		}
	}
}

?>
