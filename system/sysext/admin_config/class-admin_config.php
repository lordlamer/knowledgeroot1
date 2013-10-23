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

		// save configvalue
		if(isset($_POST['action']) and isset($_POST['ext']) and $_POST['ext'] == "admin_config" and $_POST['action'] == "save_config") {
			$this->save_config();
		}

		return $content;
	}

	// show informations
	function show_config() {
		// load js file
		$this->CLASS['kr_header']->addjssrc("../" . $this->myPath . $this->CONF['jsfile']);

		$out = "";
		$out .= '
    <script type="text/javascript">
        dojo.require("dijit.TitlePane");
	dojo.require("dijit.InlineEditBox");
    </script>
';

		$out .= "<h2>".$this->CLASS['translate']->_('change configuration')."</h2>";

		$config = $this->getConfigArray();

		foreach($config as $key => $value) {
			$out .= "<div dojoType=\"dijit.TitlePane\" title=\"".$key."\">";

			foreach($value as $ckey => $cvalue) {
				$out .= '<span>' . $ckey . " = <div dojoType=\"dijit.InlineEditBox\" onChange=\"saveConfig('".$key.".".$ckey."', arguments[0]);\" autoSave=\"true\" title=\"".$ckey."\" id=\"value_".$key.'.'.$ckey."\">" . $cvalue . "</div></span><br />\n";
			}

			$out .= "</div><br />\n";
			$out .= "\n";
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

	// save config value
	function save_config() {
		if(isset($_POST['config_path']) && $_POST['config_path'] != "") {
			$error = false;
			$error_msg = "";
			if(!$this->CLASS['knowledgeroot']->setConfig($_POST['config_path'],$_POST['config_value'])) {
				$error = true;
				$error_msg = $this->CLASS['translate']->_('Could not save config value!');
			}

			// set xml header
			header("Content-Type: text/xml");

			// generate xmloutput
			$xmlcode = '<?xml version="1.0" ?>' . "\n";
			$xmlcode .= "<root>\n";
			if($error) $xmlcode .= "\t<error>1</error>\n";
			if($error_msg != "") $xmlcode .= "\t<errormsg>".$error_msg."</errormsg>\n";
			$xmlcode .= "\t<name>".$_POST['config_path']."</name>\n";
			$xmlcode .= "\t<value>\n";
			$xmlcode .= "<![CDATA[\n";
			$xmlcode .= $this->CLASS['knowledgeroot']->getConfig($_POST['config_path']);
			$xmlcode .= "]]>\n";
			$xmlcode .= "\t</value>\n";
			$xmlcode .= "</root>\n";

			echo $xmlcode;
		}

		exit();
	}
}

?>
