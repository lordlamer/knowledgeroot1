<?php
/******************************
 * Knowledgeroot
 * Frank Habermann
 * 21.09.2006
 *
 * Version 0.1
 * This Class shows informations in the admin interface
 ******************************/

class admin_config extends extension_base {

	function main() {
		$content = "";

		// add menu item to admin navi
		$this->menu['admin']['adm_config']['name'] = $this->CLASS['translate']->_('configure');
		$this->menu['admin']['adm_config']['link'] = "index.php?action=show_config";
		$this->menu['admin']['adm_config']['priority'] = "20";

		// check if informations should be shown
		if(isset($_GET['action']) and $_GET['action'] == "show_config") {
			$content = $this->show_config();
		}

		return $content;
	}

	// show informations
	function show_config() {
		$out = "";

		$out .= "<h2>".$this->CLASS['translate']->_('current configuration')."</h2>";

		$config = $this->getConfigArray();

		foreach($config as $key => $value) {
			$out .= '
			<div class="card">
			  <div class="card-header">
				'.$key.'
			  </div>
			  <div class="card-body">
			';

			foreach($value as $ckey => $cvalue) {
				$out .= '<span>' . $ckey . " = ";
				$out .= $cvalue;
				$out .= "</span><br />";
			}

			$out .= "</div></div></p>";
		}

		return $out;
	}

	/**
	 * return complete config as array
	 *
	 * @return array
	 */
	function getConfigArray($config = null, $keyname = '', $level = 0) {
		$arr = array();
		$level++;

		if($config == null)
			$config = $this->CLASS['config']->toArray();
		foreach($config as $key => $value) {
			if(is_array($value)) {
				if($level == 1) {
					$arr[$key] = $this->getConfigArray($value, '', $level);
				} else {
					foreach($this->getConfigArray($value, (($keyname != '') ? $keyname . '.' : '') . $key) as $key2 => $value2) {
						$arr[$key2] = $value2;
					}
				}
			} else {
				$arr[(($keyname != '') ? $keyname . '.' : '') . $key] = $value;
			}
		}

		return $arr;
	}
}

?>
