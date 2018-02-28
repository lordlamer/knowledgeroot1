<?php
/**
 * This Class inherits functions for easier work
 *
 * @package Knowledgeroot
 * @author Frank Habermann
 * @version $Id: class-knowledgeroot.php 1061 2011-04-30 19:06:35Z lordlamer $
 */
class knowledgeroot {
	/**
	 * @param array $CLASS reference to global CLASS var
	 */
	var $CLASS;

	/**
	 * @param array $tree_right_cache cache for rights in tree
	 */
	var $tree_right_cache = array();

	/**
	 * @param array $content_right_cache cache for rigths in content
	 */
	var $content_right_cache = array();

	/**
	 * @param array $user_right_cache cache for rights in user
	 */
	var $user_right_cache = array();

	/**
	 * @param array $user_group_cache cache for rights in group
	 */
	var $user_group_cache = array();

	/**
	 * @param array $group_cache cache for group
	 */
	var $group_cache = array();

	/**
	 * @param array $groups_cache cache for groups
	 */
	var $groups_cache = array();

	/**
	 * @param array $recursiv_page_perm_cache cache for recursiv page rights
	 */
	var $recursiv_page_perm_cache = array();

	/**
	 * @param array $inerhitpagerights_cache cache for interhitpagerights
	 */
	var $inerhitpagerights_cache = array();

	/**
	 * @param array $userdropdowncache cache for userdropdown
	 */
	var $userdropdowncache = null;

	/**
	 * @param array $groupdropdowncache cache for groupdropdown
	 */
	var $groupdropdowncache = null;

	/**
	 * init/start class
	 * @param array &$CLASS reference to global CLASS var
	 */
	function start(&$CLASS) {
		$this->CLASS =& $CLASS;
	}

	/**
	 * FROM Typo3
	 * AddSlash array
	 * This function traverses a multidimentional array and adds slashes to the values.
	 * NOTE that the input array is and argument by reference.!!
	 * Twin-function to stripSlashesOnArray
	 * Usage: 8
	 *
	 * @param	array		Multidimensional input array, (REFERENCE!)
	 * @return	array
	 */
	function addSlashesOnArray(&$theArray)	{
		if(get_magic_quotes_gpc() == 0) {
			if (is_array($theArray))	{
				reset($theArray);
				while(list($Akey,$AVal)=each($theArray))	{
					if (is_array($AVal))	{
						$this->addSlashesOnArray($theArray[$Akey]);
					} else {
						$theArray[$Akey] = addslashes($AVal);
					}
				}
				reset($theArray);
			}
		}
	}

	/**
	 * return rightpanel for new content or page
	 * @param integer $userid
	 * @return string return rightpanel as html
	 */
	function rightpanel($userid = "") {
		// check if user have rights to set rights
		if(empty($userid)) {
			// user have no rights to see the panel
			return "";
		}

		$res = $this->CLASS['db']->squery("SELECT u.id as uid, g.id as gid, u.name as user,g.name as ".$this->CLASS['db']->quoteIdentifier("group").", u.defaultrights as rights, u.rightedit as rightedit FROM users u, groups g WHERE g.id=u.defaultgroup AND u.id=%d",$userid);
		$anz = $this->CLASS['db']->num_rows($res);

		if($anz == 1) {
			$row = $this->CLASS['db']->fetch_assoc($res);
			if($row['rightedit'] == 0) {
				return "";
			}
		}

		// get user dropdowns
		$users = $this->userSelectDropDown("user",$row['uid']);
		$musers = $this->userSelectDropDown("users[{ID}]");

		// get group dropdowns
		$groups = $this->groupSelectDropDown("group",$row['gid']);
		$mgroups = $this->groupSelectDropDown("groups[{ID}]");

		if(strlen($row['rights']) == 3) {
			$userright = substr($row['rights'],0,1);
			$groupright = substr($row['rights'],1,1);
			$otherright = substr($row['rights'],2,1);

			// Userrechte Dropdown
			$userrights = $this->rightDropDown("userrights",$userright);
			$muserrights = $this->rightDropDown("muserrights[{ID}]");

			// Gruppenrechte Dropdown
			$grouprights = $this->rightDropDown("grouprights",$groupright);
			$mgrouprights = $this->rightDropDown("mgrouprights[{ID}]");

			// Otherrechte Dropdown
			$otherrights = $this->rightDropDown("otherrights",$otherright);
		}

		$userrightsline = "<tr id=\"multiplerights_{ID}\"><td><button type=\"button\" class=\"btn btn-danger\" onclick=\"rights.delRightLine({ID})\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></button>&nbsp;".$this->CLASS['translate']->_('user')."</td><td style=\"padding:2px 2px 2px 2px;\">".$musers."</td><td>".$this->CLASS['translate']->_('userrights')."</td><td>".$muserrights."</td></tr>";
		$grouprightsline = "<tr id=\"multiplerights_{ID}\"><td><button type=\"button\" class=\"btn btn-danger\" onclick=\"rights.delRightLine({ID})\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></button>&nbsp;".$this->CLASS['translate']->_('group')."</td><td style=\"padding:2px 2px 2px 2px;\">".$mgroups."</td><td>".$this->CLASS['translate']->_('grouprights')."</td><td>".$mgrouprights."</td></tr>";

		$userrightsline = str_replace("\n","", $userrightsline);
		$grouprightsline = str_replace("\n","", $grouprightsline);

		$out = "";
		$out .= '
			<script language="javascript" type="text/javascript">
				var rights = {
					counter:  0,

					addUserRightLine: function () {
						var line = '."'".$userrightsline."'".';
						line = line.replace(/\{ID\}/g, this.counter);
						elementId = "multiplerights_" + this.counter;
						this.counter++;
						$("#rightpaneladdmultiplerights").before(line);
					},

					addGroupRightLine: function () {
						var line = '."'".$grouprightsline."'".';
						line = line.replace(/\{ID\}/g, this.counter);
						elementId = "multiplerights_" + this.counter;
						this.counter++;
						$("#rightpaneladdmultiplerights").before(line);
					},

					delRightLine: function(id) {
						$("#multiplerights_"+id).remove();
					}
				}
			</script>
			';
		$out .= "<div class=\"rightpanel\" style=\"padding:10px 0px 0px 0px;\">\n";
		$out .= '
			<div class="card"><div class="card-body">
			<table class="table table-hover table-sm">
				<tr><td>'.$this->CLASS['translate']->_('user').':</td><td style="padding:2px 2px 2px 2px;">' . $users . '</td><td>'.$this->CLASS['translate']->_('userrights').':</td><td>' . $userrights . '</td></tr>
				<tr><td>'.$this->CLASS['translate']->_('group').':</td><td>' . $groups . '</td><td>'.$this->CLASS['translate']->_('grouprights').':</td><td>' . $grouprights . '</td></tr>
				<tr><td colspan="2">&nbsp;</td><td>'.$this->CLASS['translate']->_('otherrights').':</td><td>' . $otherrights . '</td></tr>
				<tr id="rightpaneladdmultiplerights">
					<td colspan="4" align="center">
						<button class="btn btn-outline-secondary btn-sm" onclick="rights.addUserRightLine();" type="button" name="adduserrights">'.$this->CLASS['translate']->_('Add Userrights').'</button>
						&nbsp;
						<button class="btn btn-outline-secondary btn-sm" onclick="rights.addGroupRightLine();" type="button" name="addgrouprights">'.$this->CLASS['translate']->_('Add Grouprights').'</button>
					</td>
				</tr>
			</table>
			</div></div>
			';
		$out .= "</div>\n";
		return $out;
	}

	/**
	 * show rightpanel with subinherit rights
	 * @param integer $userid id of user
	 * @return string return html
	 */
	function rightpanelsubinherit($userid = "") {
		// check if user have rights to set rights
		if(empty($userid)) {
			// user have no rights to see the panel
			return "";
		} else {
			$res = $this->CLASS['db']->squery("SELECT u.id as uid, g.id as gid, u.name as user,g.name as ".$this->CLASS['db']->quoteIdentifier("group").", u.defaultrights as rights, u.rightedit as rightedit FROM users u, groups g WHERE g.id=u.defaultgroup AND u.id=%d",$userid);
			$anz = $this->CLASS['db']->num_rows($res);

			if($anz == 1) {
				$row = $this->CLASS['db']->fetch_assoc($res);
				if($row['rightedit'] == 0) {
					return "";
				}
			}
		}

		// get user dropdowns
		$users = $this->userSelectDropDown("subinherituser",$row['uid']);
		$musers = $this->userSelectDropDown("subinheritusers[{ID}]");

		// get group dropdowns
		$groups = $this->groupSelectDropDown("subinheritgroup",$row['gid']);
		$mgroups = $this->groupSelectDropDown("subinheritgroups[{ID}]");

		if(strlen($row['rights']) == 3) {
			$userright = substr($row['rights'],0,1);
			$groupright = substr($row['rights'],1,1);
			$otherright = substr($row['rights'],2,1);

			// Userrechte Dropdown
			$userrights = $this->rightDropDown("subinherituserrights",$userright);
			$muserrights = $this->rightDropDown("subinheritmuserrights[{ID}]");

			// Gruppenrechte Dropdown
			$grouprights = $this->rightDropDown("subinheritgrouprights",$groupright);
			$mgrouprights = $this->rightDropDown("subinheritmgrouprights[{ID}]");

			// Otherrechte Dropdown
			$otherrights = $this->rightDropDown("subinheritotherrights",$otherright);
		}

		$userrightsline = "<tr id=\"subinheritmultiplerights_{ID}\"><td><button type=\"button\" class=\"btn btn-danger\" onclick=\"subinheritrightsjs.delRightLine({ID})\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></button>&nbsp;".$this->CLASS['translate']->_('user')."</td><td style=\"padding:2px 2px 2px 2px;\">".$musers."</td><td>".$this->CLASS['translate']->_('userrights')."</td><td>".$muserrights."</td></tr>";
		$grouprightsline = "<tr id=\"subinheritmultiplerights_{ID}\"><td><button type=\"button\" class=\"btn btn-danger\" onclick=\"subinheritrightsjs.delRightLine({ID})\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></button>&nbsp;".$this->CLASS['translate']->_('group')."</td><td style=\"padding:2px 2px 2px 2px;\">".$mgroups."</td><td>".$this->CLASS['translate']->_('grouprights')."</td><td>".$mgrouprights."</td></tr>";

		$userrightsline = str_replace("\n","", $userrightsline);
		$grouprightsline = str_replace("\n","", $grouprightsline);

		$out = "";
		$out .= '
			<script language="javascript" type="text/javascript">
				var subinheritrightsjs = {
					counter:  0,

					addUserRightLine: function () {
						var line = '."'".$userrightsline."'".';
						line = line.replace(/\{ID\}/g, this.counter);
						elementId = "subinheritmultiplerights_" + this.counter;
						this.counter++;
						$("#subinheritrightpaneladdmultiplerights").before(line);
					},

					addGroupRightLine: function () {
						var line = '."'".$grouprightsline."'".';
						line = line.replace(/\{ID\}/g, this.counter);
						elementId = "subinheritmultiplerights_" + this.counter;
						this.counter++;
						$("#subinheritrightpaneladdmultiplerights").before(line);
					},

					delRightLine: function(id) {
						$("#subinheritmultiplerights_"+id).remove();
					}
				}
			</script>
			';

		$out .= "<input class=\"form-check-input\" type=\"checkbox\" name=\"subinheritrights\" value=\"1\" />".$this->CLASS['translate']->_('enable inherit rights for subpages and contents')."<br />\n";
		$out .= "<input class=\"form-check-input\" type=\"checkbox\" name=\"subinheritrightseditable\" value=\"1\" />".$this->CLASS['translate']->_('enable edit of rights for these pages and contents')."<br />\n";
		$out .= "<input class=\"form-check-input\" type=\"checkbox\" name=\"subinheritrightsdisable\" value=\"1\" />".$this->CLASS['translate']->_('disable inherit rights for subpages and contents')."<br />\n";

		$out .= "<div class=\"rightpanel\" style=\"padding:10px 0px 0px 0px;\">\n";
		$out .= '
			<div class="card"><div class="card-body">
			<table class="table table-hover table-sm">
				<tr><td>'.$this->CLASS['translate']->_('user').':</td><td style="padding:2px 2px 2px 2px;">' . $users . '</td><td>'.$this->CLASS['translate']->_('userrights').':</td><td>' . $userrights . '</td></tr>
				<tr><td>'.$this->CLASS['translate']->_('group').':</td><td>' . $groups . '</td><td>'.$this->CLASS['translate']->_('grouprights').':</td><td>' . $grouprights . '</td></tr>
				<tr><td colspan="2">&nbsp;</td><td>'.$this->CLASS['translate']->_('otherrights').':</td><td>' . $otherrights . '</td></tr>
				<tr id="subinheritrightpaneladdmultiplerights">
					<td colspan="4" align="center">
						<button class="btn btn-outline-secondary btn-sm" onclick="subinheritrightsjs.addUserRightLine();" type="button" name="subinheritadduserrights">'.$this->CLASS['translate']->_('Add Userrights').'</button>
						&nbsp;
						<button class="btn btn-outline-secondary btn-sm" onclick="subinheritrightsjs.addGroupRightLine();" type="button" name="subinheritaddgrouprights">'.$this->CLASS['translate']->_('Add Grouprights').'</button>
					</td>
				</tr>
			</table>
			</div></div>
			';
		$out .= "</div>\n";
		return $out;
	}

	/**
	 * return panel with dropdowns to set rights
	 * @param string $table tablename
	 * @param integer $belongsto
	 * @param integer $userid
	 * @param integer $groupid
	 * @param integer $rights
	 * @param integer $recursiv
	 * @return string return rightpanel as html
	 */
	function editRightPanel($table,$belongsto,$userid,$groupid,$rights,$recursiv = 0) {
		//if(empty($userid)) {
		if($userid == "") {
			// you have no rights
			return "";
		}

		$res = $this->CLASS['db']->squery("SELECT rightedit FROM users WHERE id=%d",$userid);
		$anz = $this->CLASS['db']->num_rows($res);

		if($anz == 1) {
			$row = $this->CLASS['db']->fetch_assoc($res);
		}

		if($_SESSION['rightedit'] != 1 && $_SESSION['admin'] == 0) {
			//you have no rights
			return "";
		}

		//user
		$users = $this->userSelectDropDown("user",$userid);
		$musers = $this->userSelectDropDown("users[{ID}]");

		//group
		$groups = $this->groupSelectDropDown("group",$groupid);
		$mgroups = $this->groupSelectDropDown("groups[{ID}]");

		//userrights
		$userrights = $this->rightDropDown("userrights",substr($rights,0,1));
		//grouprights
		$grouprights = $this->rightDropDown("grouprights",substr($rights,1,1));
		//otherrights
		$otherrights = $this->rightDropDown("otherrights",substr($rights,2,1));

		$muserrights = $this->rightDropDown("muserrights[{ID}]");
		$mgrouprights = $this->rightDropDown("mgrouprights[{ID}]");

		$recursivhtml = '';
		if($recursiv == 1) {
			$recursivhtml = "<tr><td>".$this->CLASS['translate']->_('set recursiv')."?&nbsp;\n";
            $recursivhtml .= "</td><td colspan=\"3\">\n";
			$recursivhtml .= "<select name=\"recursiv\" class=\"form-control form-control-sm\">\n";
			$recursivhtml .= "\t<option value=\"\">".$this->CLASS['translate']->_('no')."</option>\n";
			$recursivhtml .= "\t<option value=\"1\">".$this->CLASS['translate']->_('yes')."</option>\n";
			$recursivhtml .= "</select>\n";
			$recursivhtml .= "</td></tr>\n";
		}

		$multiplerights = "";
		$multiplerightsid = 0;

		$userrightsline = "<tr id=\"multiplerights_{ID}\"><td><button type=\"button\" class=\"btn btn-danger\" onclick=\"rights.delRightLine({ID})\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></button>&nbsp;".$this->CLASS['translate']->_('user')."</td><td style=\"padding:2px 2px 2px 2px;\">".$musers."</td><td>".$this->CLASS['translate']->_('userrights')."</td><td>".$muserrights."</td></tr>";
		$grouprightsline = "<tr id=\"multiplerights_{ID}\"><td><button type=\"button\" class=\"btn btn-danger\" onclick=\"rights.delRightLine({ID})\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></button>&nbsp;".$this->CLASS['translate']->_('group')."</td><td style=\"padding:2px 2px 2px 2px;\">".$mgroups."</td><td>".$this->CLASS['translate']->_('grouprights')."</td><td>".$mgrouprights."</td></tr>";

		$userrightsline = str_replace("\n","", $userrightsline);
		$grouprightsline = str_replace("\n","", $grouprightsline);

		// get multiple rights
		$mrightsout = "";
		$counterid = 0;
		$mrights = $this->getMultipleRights($table,$belongsto);
		if(is_array($mrights)) {
			foreach($mrights as $key => $value) {
				// check if right is for user
				if($value['owner_group'] == "o") {
					$mrightsout .= "<tr id=\"multiplerights_".$counterid."\"><td>".$this->CLASS['translate']->_('user')."</td><td style=\"padding:2px 2px 2px 2px;\">".$this->userSelectDropDown("users[".$counterid."]",$value['owner_group_id'])."</td><td>".$this->CLASS['translate']->_('userrights')."</td><td>".$this->rightDropDown("muserrights[".$counterid."]",$value['rights'])."&nbsp;<img style=\"cursor:pointer\" src=\"images/delete.gif\" alt=\"\" onclick=\"rights.delRightLine(".$counterid.")\" /></td></tr>\n";
				}

				// check if right is for group
				if($value['owner_group'] == "g") {
					$mrightsout .= "<tr id=\"multiplerights_".$counterid."\"><td>".$this->CLASS['translate']->_('group')."</td><td style=\"padding:2px 2px 2px 2px;\">".$this->groupSelectDropDown("groups[".$counterid."]",$value['owner_group_id'])."</td><td>".$this->CLASS['translate']->_('grouprights')."</td><td>".$this->rightDropDown("mgrouprights[".$counterid."]",$value['rights'])."&nbsp;<img style=\"cursor:pointer\" src=\"images/delete.gif\" alt=\"\" onclick=\"rights.delRightLine(".$counterid.")\" /></td></tr>\n";
				}

				// inc counterid
				$counterid++;
			}
		}

		$out = "";
		$out .= '
			<script language="javascript" type="text/javascript">
				var rights = {
					counter:  '.$counterid.',

					addUserRightLine: function () {
						var line = '."'".$userrightsline."'".';
						line = line.replace(/\{ID\}/g, this.counter);
						elementId = "multiplerights_" + this.counter;
						this.counter++;
						$("#rightpaneladdmultiplerights").before(line);
					},

					addGroupRightLine: function () {
						var line = '."'".$grouprightsline."'".';
						line = line.replace(/\{ID\}/g, this.counter);
						elementId = "multiplerights_" + this.counter;
						this.counter++;
						$("#rightpaneladdmultiplerights").before(line);
					},

					delRightLine: function(id) {
						$("#multiplerights_"+id).remove();
					}
				}
			</script>
			';
		$out .= "<div class=\"rightpanel\" style=\"padding:10px 0px 0px 0px;\">\n";
		$out .= '
			<div class="card"><div class="card-body">
			<table class="table table-hover table-sm">
				<tr><td>'.$this->CLASS['translate']->_('user').':</td><td style="padding:2px 2px 2px 2px;">' . $users . '</td><td>'.$this->CLASS['translate']->_('userrights').':</td><td>' . $userrights . '</td></tr>
				<tr><td>'.$this->CLASS['translate']->_('group').':</td><td>' . $groups . '</td><td>'.$this->CLASS['translate']->_('grouprights').':</td><td>' . $grouprights . '</td></tr>
				<tr><td colspan="2">&nbsp;</td><td>'.$this->CLASS['translate']->_('otherrights').':</td><td>' . $otherrights . '</td></tr>
				'.$mrightsout.'
				<tr id="rightpaneladdmultiplerights">
					<td colspan="4" align="center">
						<button class="btn btn-outline-secondary btn-sm" onclick="rights.addUserRightLine();" type="button" name="adduserrights">'.$this->CLASS['translate']->_('Add Userrights').'</button>
						&nbsp;
						<button class="btn btn-outline-secondary btn-sm" onclick="rights.addGroupRightLine();" type="button" name="addgrouprights">'.$this->CLASS['translate']->_('Add Grouprights').'</button>
					</td>
				</tr>
				'.$recursivhtml.'
			</table>
			</div></div>
			';
		$out .= "</div>\n";

		return $out;
	}

	/**
	 * return panel with dropdowns to set rights
	 * @param string $table
	 * @param integer $belongsto
	 * @param integer $subinheritrights
	 * @param integer $subinheritsrightsenable
	 * @param integer $subinheritsrightsdisable
	 * @param integer $userid
	 * @param integer $groupid
	 * @param integer $subuserrights
	 * @param integer $subgrouprights
	 * @param integer $subotherrights
	 * @return string return rightpanel as html
	 */
	function editRightPanelSubInherit($table,$belongsto,$subinheritrights,$subinheritsrightsenable,$subinheritsrightsdisable,$userid,$groupid,$subuserrights,$subgrouprights,$subotherrights) {
		//if(empty($userid)) {
		if((isset($_SESSION['admin']) && $_SESSION['admin'] != 1) || $userid == "") {
			// you have no rights
			return "";
		}

		$res = $this->CLASS['db']->squery("SELECT rightedit FROM users WHERE id=%d",$userid);
		$anz = $this->CLASS['db']->num_rows($res);

		if($anz == 1) {
			$row = $this->CLASS['db']->fetch_assoc($res);
		}

		if($_SESSION['rightedit'] != 1 && $_SESSION['admin'] == 0) {
			//you have no rights
			return "";
		}

		//user
		$users = $this->userSelectDropDown("subinherituser",$userid);
		$musers = $this->userSelectDropDown("subinheritusers[{ID}]");

		//group
		$groups = $this->groupSelectDropDown("subinheritgroup",$groupid);
		$mgroups = $this->groupSelectDropDown("subinheritgroups[{ID}]");

		//userrights
		$userrights = $this->rightDropDown("subinherituserrights",$subuserrights);
		//grouprights
		$grouprights = $this->rightDropDown("subinheritgrouprights",$subgrouprights);
		//otherrights
		$otherrights = $this->rightDropDown("subinheritotherrights",$subotherrights);

		$muserrights = $this->rightDropDown("subinheritmuserrights[{ID}]");
		$mgrouprights = $this->rightDropDown("subinheritmgrouprights[{ID}]");

		if($subinheritrights == 1) $subinheritscheck = "checked=\"checked\" ";
		else $subinheritscheck = "";

		if($subinheritsrightsenable == 1) $subinheritsenablecheck = "checked=\"checked\" ";
		else $subinheritsenablecheck = "";

		if($subinheritsrightsdisable == 1) $subinheritsdisablecheck = "checked=\"checked\" ";
		else $subinheritsdisablecheck = "";

		$multiplerights = "";
		$multiplerightsid = 0;

		$userrightsline = "<tr id=\"subinheritmultiplerights_{ID}\"><td><button type=\"button\" class=\"btn btn-danger\" onclick=\"subinheritrightsjs.delRightLine({ID})\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></button>&nbsp;".$this->CLASS['translate']->_('user')."</td><td style=\"padding:2px 2px 2px 2px;\">".$musers."</td><td>".$this->CLASS['translate']->_('userrights')."</td><td>".$muserrights."</td></tr>";
		$grouprightsline = "<tr id=\"subinheritmultiplerights_{ID}\"><td><button type=\"button\" class=\"btn btn-danger\" onclick=\"subinheritrightsjs.delRightLine({ID})\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></button>&nbsp;".$this->CLASS['translate']->_('group')."</td><td style=\"padding:2px 2px 2px 2px;\">".$mgroups."</td><td>".$this->CLASS['translate']->_('grouprights')."</td><td>".$mgrouprights."</td></tr>";

		$userrightsline = str_replace("\n","", $userrightsline);
		$grouprightsline = str_replace("\n","", $grouprightsline);

		// get multiple rights
		$mrightsout = "";
		$counterid = 0;
		$mrights = $this->getMultipleRights($table,$belongsto);
		if(is_array($mrights)) {
			foreach($mrights as $key => $value) {
				// check if right is for user
				if($value['owner_group'] == "so") {
					$mrightsout .= "<tr id=\"subinheritmultiplerights_".$counterid."\"><td>".$this->CLASS['translate']->_('user')."</td><td style=\"padding:2px 2px 2px 2px;\">".$this->userSelectDropDown("subinheritusers[".$counterid."]",$value['owner_group_id'])."</td><td>".$this->CLASS['translate']->_('userrights')."</td><td>".$this->rightDropDown("subinheritmuserrights[".$counterid."]",$value['rights'])."&nbsp;<img style=\"cursor:pointer\" src=\"images/delete.gif\" alt=\"\" onclick=\"subinheritrightsjs.delRightLine(".$counterid.")\" /></td></tr>\n";
				}

				// check if right is for group
				if($value['owner_group'] == "sg") {
					$mrightsout .= "<tr id=\"subinheritmultiplerights_".$counterid."\"><td>".$this->CLASS['translate']->_('group')."</td><td style=\"padding:2px 2px 2px 2px;\">".$this->groupSelectDropDown("subinheritgroups[".$counterid."]",$value['owner_group_id'])."</td><td>".$this->CLASS['translate']->_('grouprights')."</td><td>".$this->rightDropDown("subinheritmgrouprights[".$counterid."]",$value['rights'])."&nbsp;<img style=\"cursor:pointer\" src=\"images/delete.gif\" alt=\"\" onclick=\"subinheritrightsjs.delRightLine(".$counterid.")\" /></td></tr>\n";
				}

				// inc counterid
				$counterid++;
			}
		}

		$out = "";
		$out .= '
			<script language="javascript" type="text/javascript">
				var subinheritrightsjs = {
					counter:  '.$counterid.',

					addUserRightLine: function () {
						var line = '."'".$userrightsline."'".';
						line = line.replace(/\{ID\}/g, this.counter);
						elementId = "subinheritmultiplerights_" + this.counter;
						this.counter++;
						$("#subinheritrightpaneladdmultiplerights").before(line);
					},

					addGroupRightLine: function () {
						var line = '."'".$grouprightsline."'".';
						line = line.replace(/\{ID\}/g, this.counter);
						elementId = "subinheritmultiplerights_" + this.counter;
						this.counter++;
						$("#subinheritrightpaneladdmultiplerights").before(line);
					},

					delRightLine: function(id) {
						$("#subinheritmultiplerights_"+id).remove();
					}
				}
			</script>
			';
		$out .= "<input class=\"form-check-input\" type=\"checkbox\" name=\"subinheritrights\" value=\"1\" ".$subinheritscheck."/>".$this->CLASS['translate']->_('enable inherit rights for subpages and contents')."<br />\n";
		$out .= "<input class=\"form-check-input\" type=\"checkbox\" name=\"subinheritrightseditable\" value=\"1\" ".$subinheritsenablecheck."/>".$this->CLASS['translate']->_('enable edit of rights for these pages and contents')."<br />\n";
		$out .= "<input class=\"form-check-input\" type=\"checkbox\" name=\"subinheritrightsdisable\" value=\"1\" ".$subinheritsdisablecheck."/>".$this->CLASS['translate']->_('disable inherit rights for subpages and contents')."<br />\n";

		$out .= "<div class=\"rightpanel\" style=\"padding:10px 0px 0px 0px;\">\n";
		$out .= '
			<div class="card"><div class="card-body">
			<table class="table table-hover table-sm">
				<tr><td>'.$this->CLASS['translate']->_('user').':</td><td style="padding:2px 2px 2px 2px;">' . $users . '</td><td>'.$this->CLASS['translate']->_('userrights').':</td><td>' . $userrights . '</td></tr>
				<tr><td>'.$this->CLASS['translate']->_('group').':</td><td>' . $groups . '</td><td>'.$this->CLASS['translate']->_('grouprights').':</td><td>' . $grouprights . '</td></tr>
				<tr><td colspan="2">&nbsp;</td><td>'.$this->CLASS['translate']->_('otherrights').':</td><td>' . $otherrights . '</td></tr>
				'.$mrightsout.'
				<tr id="subinheritrightpaneladdmultiplerights">
					<td colspan="4" align="center">
						<button class="btn btn-outline-secondary btn-sm" onclick="subinheritrightsjs.addUserRightLine();" type="button" name="subinheritadduserrights">'.$this->CLASS['translate']->_('Add Userrights').'</button>
						&nbsp;
						<button class="btn btn-outline-secondary btn-sm" onclick="subinheritrightsjs.addGroupRightLine();" type="button" name="subinheritaddgrouprights">'.$this->CLASS['translate']->_('Add Grouprights').'</button>
					</td>
				</tr>
			</table>
			</div></div>
			';
		$out .= "</div>\n";

		return $out;
	}

	/**
	 * new function that check page rights with multiple rights
	 * @param integer $pageid
	 * @param integer $userid
	 * @return integer return rights of object
	 */
	function getPageRights($pageid, $userid) {
		$hashkey = md5('getpagerights'.$pageid.$userid);
		if(!($this->CLASS['cache']->test($hashkey))) {
			$pagerights = $this->_getPageRights($pageid, $userid);

			$this->CLASS['cache']->save($pagerights, $hashkey, array('system', 'page', 'rights'));
		} else {
			$pagerights = $this->CLASS['cache']->load($hashkey);
		}

		// check if pagerights are already full then return
		if($pagerights == 2) return 2;

		$mrights = $this->checkMultipleRights($userid, "tree", $pageid);

		if($mrights > $pagerights) {
			return $mrights;
		} else {
			return $pagerights;
		}
	}

	/**
	 * return rights on a page by userid
	 * @param integer $pageid
	 * @param integer $userid
	 * @return integer return rights of object
	 */
	function _getPageRights($pageid, $userid) {
		if(!isset($pageid) || !isset($userid)) {
			return 0;
		}

		// get userrights
		if(!isset($this->user_cache_rights[$userid])) {
			$res = $this->CLASS['db']->squery("SELECT * FROM users WHERE id=%d AND deleted=0",$userid);
			$anz = $this->CLASS['db']->num_rows($res);

			// check if user exists, if not - no rights
			if($anz == 0 && $userid != 0) {
				return 0;
			}

			// userrow
			$rowuser = $this->CLASS['db']->fetch_assoc($res);
			// set cache
			$this->user_cache_rights[$userid] = $rowuser;
		} else {
			// get from cache
			$rowuser = $this->user_cache_rights[$userid];
		}

		// check if user is admin, if yes then give full rights
		if($rowuser['admin'] == 1) {
			return 2;
		}

		// get pagerights
		if(!isset($this->tree_cache_rights[$pageid])) {
			$res = $this->CLASS['db']->squery("SELECT * FROM tree WHERE id=%d AND deleted=0",$pageid);
			$anz = $this->CLASS['db']->num_rows($res);

			// check if page exists, if not - no rights
			if($anz == 0) {
				return 0;
			}

			// pagerow
			$rowpage = $this->CLASS['db']->fetch_assoc($res);
			// set cache
			$this->tree_cache_rights[$pageid] = $rowpage;
		} else {
			// get from cache
			$rowpage = $this->tree_cache_rights[$pageid];
		}

		if($rowpage['owner'] == $rowuser['id']) {
			return $rowpage['userrights'];
		}

		if($rowpage['group'] == $rowuser['defaultgroup']) {
			return $rowpage['grouprights'];
		}

		// check for caching
		if(!isset($this->user_group_cache[$userid])) {
			$this->user_group_cache[$userid] = array();

			$found = 0;

			$res = $this->CLASS['db']->squery("SELECT groupid FROM user_group WHERE userid=%d",$userid);
			while($row = $this->CLASS['db']->fetch_assoc($res)) {
				if($row['groupid'] == $rowpage['group']) {
					$found = 1;
				}

				// set cache
				$this->user_group_cache[$userid][] = $row;
			}

			if($found == 1) return $rowpage['grouprights'];
		} else {
			if(is_array($this->user_group_cache[$userid])) {
				// get from cache
				foreach($this->user_group_cache[$userid] as $key => $value) {
					if($this->user_group_cache[$userid][$key]['groupid'] == $rowpage['group']) {
						return $rowpage['grouprights'];
					}
				}
			}
		}

		return $rowpage['otherrights'];
	}

	/**
	 * new function that check content rights with multiple rights
	 * @param integer $contentid
	 * @param integer $userid
	 * @return integer return rights of object
	 */
	function getContentRights($contentid, $userid) {
		$hashkey = md5('getcontentrights'.$contentid.$userid);
		if(!($this->CLASS['cache']->test($hashkey))) {
			$contentrights = $this->_getContentRights($contentid, $userid);
			$this->CLASS['cache']->save($contentrights, $hashkey, array('system', 'content', 'rights'));
		} else {
			$contentrights = $this->CLASS['cache']->load($hashkey);
		}

		// check if pagerights are already full then return
		if($contentrights == 2) return 2;

		$mrights = $this->checkMultipleRights($userid, "content", $contentid);

		if($mrights > $contentrights) {
			return $mrights;
		} else {
			return $contentrights;
		}
	}

	/**
	 * return rights on a content by userid
	 * @param integer $contentid
	 * @param integer $userid
	 * @return integer return rights of object
	 */
	function _getContentRights($contentid, $userid) {
		if(!isset($contentid) || !isset($userid)) {
			return 0;
		}

		// get userrights
		if(!isset($this->user_cache_rights[$userid])) {
			$res = $this->CLASS['db']->squery("SELECT * FROM users WHERE id=%d AND deleted=0",$userid);
			$anz = $this->CLASS['db']->num_rows($res);

			// check if user exists, if not - no rights
			if($anz == 0 && $userid != 0) {
				return 0;
			}

			// userrow
			$rowuser = $this->CLASS['db']->fetch_assoc($res);
			// set cache
			$this->user_cache_rights[$userid] = $rowuser;
		} else {
			// get from cache
			$rowuser = $this->user_cache_rights[$userid];
		}

		// check if user is admin, if yes then give full rights
		if($rowuser['admin'] == 1) {
			return 2;
		}

		// get contentrights
		if(!isset($this->content_cache_rights[$contentid])) {
			$res = $this->CLASS['db']->squery("SELECT * FROM content WHERE id=%d AND deleted=0",$contentid);
			$anz = $this->CLASS['db']->num_rows($res);

			// check if content exists, if not - no rights
			if($anz == 0) {
				return 0;
			}

			// pagerow
			$rowcontent = $this->CLASS['db']->fetch_assoc($res);
			// set cache
			$this->content_cache_rights[$contentid] = $rowcontent;
		} else {
			// get from cache
			$rowcontent = $this->content_cache_rights[$contentid];
		}

		if($rowcontent['owner'] == $rowuser['id']) {
			return $rowcontent['userrights'];
		}

		if($rowcontent['group'] == $rowuser['defaultgroup']) {
			return $rowcontent['grouprights'];
		}

		// check for caching
		if(!isset($this->user_group_cache[$userid])) {
			$this->user_group_cache[$userid] = array();

			$found = 0;

			$res = $this->CLASS['db']->squery("SELECT groupid FROM user_group WHERE userid=%d",$userid);
			while($row = $this->CLASS['db']->fetch_assoc($res)) {
				if($row['groupid'] == $rowcontent['group']) {
					$found = 1;
				}

				// set cache
				$this->user_group_cache[$userid][] = $row;
			}

			if($found == 1) return $rowcontent['grouprights'];
		} else {
			if(is_array($this->user_group_cache[$userid])) {
				// get from cache
				foreach($this->user_group_cache[$userid] as $key => $value) {
					if($this->user_group_cache[$userid][$key]['groupid'] == $rowcontent['group']) {
						return $rowcontent['grouprights'];
					}
				}
			}
		}

		return $rowcontent['otherrights'];
	}

	/**
	 * return yes or no
	 * @param integer $value
	 * @return string return yes or no
	 */
	function yesno($value) {
		if($value == "1" || $value == true) {
			return $this->CLASS['translate']->_('yes');
		}

		return $this->CLASS['translate']->_('no');
	}

	/**
	 * return a yes/no dropdown
	 * @param string $name name of dropdown
	 * @param integer $default if yes or no is selected by default
	 * @return string return html dropdown
	 */
	function yesnodropdown($name, $default = 0) {
		$defaultno = ($default==0) ? " selected=\"selected\"" : "";
		$defaultyes = ($default==1) ? " selected=\"selected\"" : "";

		$out = "<select name=\"".$name."\" class=\"form-control form-control-sm\">\n";
		$out .= "\t<option value=\"0\"" . $defaultno . ">".$this->CLASS['translate']->_('no')."</option>\n";
		$out .= "\t<option value=\"1\"" . $defaultyes . ">".$this->CLASS['translate']->_('yes')."</option>\n";
		$out .= "</select>\n";

		return $out;
	}

	/**
	 * return groupname
	 * @param integer $id id of group
	 * @return string return name of group
	 */
	function getGroup($id) {
		if($id == "") {
			return "";
		}

		if(!isset($this->group_cache[$id])) {
			$res = $this->CLASS['db']->squery("SELECT name FROM groups WHERE id=%d",$id);
			$anz = $this->CLASS['db']->num_rows($res);

			if($anz == 1) {
				$row = $this->CLASS['db']->fetch_assoc($res);
				$this->group_cache[$id] = $row;
			}
		} else {
			$row = $this->group_cache[$id];
		}

		if(!isset($row['name'])) $row['name'] = "";

		return $row['name'];
	}

	/**
	 * return id of groupname
	 * @param string $name name of group
	 * @return integer return id of groupname
	 */
	function getGroupID($name) {
		if($name == "") {
			return 0;
		}

		if(!isset($this->groups_cache[$name])) {
			$res = $this->CLASS['db']->squery("SELECT id FROM groups WHERE name='%s'",$name);
			$anz = $this->CLASS['db']->num_rows($res);

			if($anz == 1) {
				$row = $this->CLASS['db']->fetch_assoc($res);
				$this->groups_cache[$name] = $row;
				return $row['id'];
			}
		} else {
			return $this->groups_cache[$name]['id'];
		}

		return 0;
	}

	/**
	 * returns name of userid
	 * @param integer $id id of user
	 * @return string return name of user
	 */
	function getOwner($id) {
		if($id == "") {
			return "";
		}

		$row = null;

		$hashkey = md5('getowner'.$id);
		if(!($this->CLASS['cache']->test($hashkey))) {
			$res = $this->CLASS['db']->squery("SELECT name FROM users WHERE id=%d",$id);
			$anz = $this->CLASS['db']->num_rows($res);

			if($anz == 1) {
				$row = $this->CLASS['db']->fetch_assoc($res);
				$this->CLASS['cache']->save($row, $hashkey, array('system', 'user'));
			}
		} else {
			$row = $this->CLASS['cache']->load($hashkey);
		}

		if(isset($row['name'])) {
			return $row['name'];
		}

		return "";
	}

	/**
	 * returns a dropdown of grouprights
	 * @param string $name name of dropdown
	 * @param integer $default id of default group
	 * @param bool $multiple dropdown is multiple
	 * @param array $defaultgroups array with defaultgroups that are selected
	 * @return string return html dropdown
	 */
	function groupDropDown($name, $default = "", $multiple = false, $defaultgroups = array()) {
		if($multiple == true) {
			$out = "<select name=\"".$name."\" multiple=\"multiple\" size=\"5\" class=\"form-control form-control-sm\">\"";
		} else {
			$out = "<select name=\"".$name."\" class=\"form-control form-control-sm\">\"";
		}

		$res = $this->CLASS['db']->query("SELECT id,name FROM groups");
		while($row = $this->CLASS['db']->fetch_assoc($res)) {
			if($default == $row['id'] || $this->foundIDinArray($row['id'],$defaultgroups)) {
				$out .= "<option value=\"".$row['id']."\" selected=\"selected\">".$row['name']."</option>";
			} else {
				$out .= "<option value=\"".$row['id']."\">".$row['name']."</option>";
			}
		}

		$out .= "</select>\n";

		return $out;
	}

	/**
	 * search for id in array
	 * @param integer $id id that should be found
	 * @param array $arr array with elements
	 * @return bool
	 */
	function foundIDinArray($id,$arr) {
		if(!is_array($arr)) {
			$arr = array();
		}

		foreach($arr as $key => $value) {
			if($value == $id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * returns a dropdown with rights
	 * @param string $name name of dropdown
	 * @param integer $default rights that are selected
	 * @return string return html dropdown with rights
	 */
	function rightDropDown($name,$default = "") {
		$out = "<select name=\"".$name."\" class=\"form-control form-control-sm\">\n";

		$selected1 = $default == 2 ? "selected=\"selected\"" : "";
		$selected2 = $default == 1 ? "selected=\"selected\"" : "";
		$selected3 = $default == 0 ? "selected=\"selected\"" : "";

		$out .= "\t<option value=\"2\" ".$selected1.">".$this->CLASS['translate']->_('read+write')."</option>\n";
		$out .= "\t<option value=\"1\" ".$selected2.">".$this->CLASS['translate']->_('read')."</option>\n";
		$out .= "\t<option value=\"0\" ".$selected3.">".$this->CLASS['translate']->_('no rights')."</option>\n";

		$out .= "</select>\n";
		return $out;
	}

	/**
	 * check if a user have recursiv permissions on pageid
	 * @param integer $pageid id of page
	 * @param integer $userid id of user
	 * @return integer return rights (0,1,2)
	 */
	function checkRecursivPerm($pageid, $userid) {
		$hashkey = md5('checkrecursivperm_'.$pageid.$userid);
		if(!($this->CLASS['cache']->test($hashkey))) {
			$data = $this->_checkRecursivPerm($pageid, $userid);
			$this->CLASS['cache']->save($data, $hashkey, array('system','rights'));
		} else {
			$data = $this->CLASS['cache']->load($hashkey);
		}

		return $data;
	}

	/**
	 * check if a user have recursiv permissions on pageid
	 * @param integer $pageid id of page
	 * @param integer $userid id of user
	 * @return integer return rights (0,1,2)
	 */
	function _checkRecursivPerm($pageid, $userid) {
		if($userid == "") {
			$userid = 0;
		}

		if(strlen($pageid) > 0 && strlen($userid) > 0) {
			if(!isset($this->recursiv_page_perm_cache[$pageid])) {
				$res = $this->CLASS['db']->squery("SELECT id,belongs_to FROM tree WHERE id=%d and deleted=0",$pageid);
				$anz = $this->CLASS['db']->num_rows($res);

				if($anz == 1) {
					$row = $this->CLASS['db']->fetch_assoc($res);
					$this->recursiv_page_perm_cache[$pageid] = $row;
				} else {
					return 0;
				}
			} else {
				$row = $this->recursiv_page_perm_cache[$pageid];
			}

			if($row['belongs_to'] == 0) {
				return $this->getPageRights($row['id'],$userid);
			} else {
				$rights = $this->getPageRights($row['id'],$userid);

				if($rights == 0) {
					return 0;
				} else {
					return $this->checkRecursivPerm($row['belongs_to'],$userid);
				}
			}
		}

		return 0;
	}

	/**
	 * set rights recursiv on pages and content
	 * @param integer $pageid id of page
	 * @param integer $userid id of user
	 * @param integer $user id of user that will be set
	 * @param integer $group id of group that will be set
	 * @param integer $rights rights
	 * @param array $mrights array with multiple rights
	 * @return bool
	 */
	function setRightsRecursiv($pageid,$userid,$user,$group,$rights,$mrights = null) {
		$userrights = substr($rights,0,1);
		$grouprights = substr($rights,1,1);
		$otherrights = substr($rights,2,1);

		// get multiplerights
		if(!is_array($mrights)) {
			$mrights = $this->getMultipleRights("tree", $pageid);
		}

		// set rights for contents
		$res = $this->CLASS['db']->squery("SELECT id FROM content WHERE belongs_to=%d AND deleted=0",$pageid);
		while($row = $this->CLASS['db']->fetch_assoc($res)) {
			if($this->getContentRights($row['id'],$userid) == 2) {
				$ressub = $this->CLASS['db']->squery("UPDATE content SET owner=%d, ".$this->CLASS['db']->quoteIdentifier("group")."=%d, userrights=%d, grouprights=%d, otherrights=%d WHERE id=%d",$user,$group,$userrights,$grouprights,$otherrights,$row['id']);

				// delete all existing multiple rights on this element
				$this->deleteMultipleRightsAll("content", $row['id']);

				// set multiple rights on content
				foreach($mrights as $key => $value) {
					if($value['owner_group'] == "o" || $value['owner_group'] == "g") {
						$this->saveMultipleRights("content", $row['id'], $value['owner_group'], $value['owner_group_id'], $value['rights']);
					}
				}
			}
		}

		// set rights for subpages
		$res = $this->CLASS['db']->squery("SELECT id FROM tree WHERE belongs_to=%d AND deleted=0",$pageid);
		while($row = $this->CLASS['db']->fetch_assoc($res)) {
			if($this->getPageRights($row['id'],$userid) == 2) {
				$ressub = $this->CLASS['db']->squery("UPDATE tree SET owner=%d, ".$this->CLASS['db']->quoteIdentifier("group")."=%d, userrights=%d, grouprights=%d, otherrights=%d WHERE id=%d",$user,$group,$userrights,$grouprights,$otherrights,$row['id']);

				// delete all existing multiple rights on this element
				$this->deleteMultipleRightsAll("tree", $row['id']);

				// set multiple rights on content
				foreach($mrights as $key => $value) {
					$this->saveMultipleRights("tree", $row['id'], $value['owner_group'], $value['owner_group_id'], $value['rights']);
				}

				$this->setRightsRecursiv($row['id'],$userid,$user,$group,$rights, $mrights);
			}
		}

		return true;
	}

	/**
	 * replace values from $arr1 with the values in arr2
	 * @param array $arr1
	 * @param array $arr2
	 * @return array
	 */
	function replace_array($arr1, $arr2) {
		foreach($arr2 as $key => $value) {
			if(is_array($arr2[$key])) {
				if(isset($arr1[$key])) $arr1[$key] = $this->replace_array($arr1[$key],$arr2[$key]);
				else $arr1[$key] = $this->replace_array("",$arr2[$key]);
			} else {
				$arr1[$key] = $value;
			}
		}

		return $arr1;
	}

	/**
	 * IMPORTET FROM TYPO3
	 * Wrapping a string.
	 * Implements the TypoScript "wrap" property.
	 * Example: $content = "HELLO WORLD" and $wrap = "<b> | </b>", result: "<b>HELLO WORLD</b>"
	 *
	 * @param	string		The content to wrap
	 * @param	string		The wrap value, eg. "<b> | </b>"
	 * @param	string		The char used to split the wrapping value, default is "|"
	 * @return	string		Wrapped input string
	 * @see noTrimWrap()
	 */
	function setWrap($content = "",$wrap = "",$char = "|") {
		if ($wrap)	{
			// fix for || replacings
			$wrap = str_replace('||', '[NOT_TO_BE_REPLACED]', $wrap);
			$wrapArr = explode($char, $wrap);

			if(count($wrapArr) > 1) {
				$content = $wrapArr[0].$content.$wrapArr[1];
			} else {
				$content = $wrap;
			}

			$content = str_replace('[NOT_TO_BE_REPLACED]', '||', $content);
			return $content;
		} else return $content;
	}

	/**
	 * check for alias and returns a good alias
	 * @param string $alias
	 * @return string
	 */
	function checkAlias($alias) {
		// replace all chars that are not a-z A-Z 0-9
		$alias = preg_replace("/[^a-zA-Z0-9]*/s","",$alias);

		$res = $this->CLASS['db']->squery("SELECT count(*) AS anz FROM tree WHERE alias='%s' AND deleted=0",$alias);
		$row = $this->CLASS['db']->fetch_assoc($res);

		$x = 0;

		while($row['anz'] >= 1) {
			$x++;
			$alias_new = $alias . $x;

			$res = $this->CLASS['db']->squery("SELECT count(*) AS anz FROM tree WHERE alias='%s' AND deleted=0",$alias_new);
			$row = $this->CLASS['db']->fetch_assoc($res);
		}

		if($x == 0) {
			return $alias;
		} else {
			return $alias_new;
		}
	}

	/**
	 * create array from path and set value
	 * @param array &$arr
	 * @param string $path
	 * @param string $value
	 */
	function createArr(&$arr, $path, $value) {
		$p =& $arr;

		$path_arr = explode(".",$path);
		foreach($path_arr as $key) {
			$key = trim($key);

			if(!isset($p[$key])) {
				$p[$key] = array();
			}

			$p =& $p[$key];
		}

		$p = trim($value);
		unset($p);
	}

	/**
	 * save config value with a path
	 * @param string $path
	 * @param string $value
	 */
	function setConfig($path,$value) {
		$res = false;
		$path_arr = explode(".", $path);

		if($path_arr[0] == "db") {
			return "";
		}

		// get existing config as array and set our new config value
		$configOri= $this->CLASS['config']->toArray();
		$config =& $configOri;
		foreach($path_arr as $val) {
			if(!isset($config[$val])) {
				$config[$val] = array();
			}

			$config =& $config[$val];
		}
		$config = $value;

		try {
			// write new config
			$writer = new Zend_Config_Writer_Ini(array('config' => new Zend_Config($configOri), 'filename' => $this->CLASS['config']->base->base_path.'config/app.ini'));
			$writer->write();

			// create new config instance
			$this->CLASS['config'] = new Zend_Config($configOri);

			$res = true;
		} catch(Zend_Config_Exception $e) {

		}

		return $res;
	}

	/**
	 * get configvalue from a path
	 * @param string $path
	 * @return string
	 */
	function getConfig($path) {
		// explode our path
		$path_arr = explode(".", $path);

		// get config as array
		$config= $this->CLASS['config']->toArray();

		// foreach path element
		foreach($path_arr as $val) {
			if(!isset($config[$val])) {
				return "";
			}

			$config = $config[$val];
		}

		// return our value
		return $config;
	}

	/**
	 * save array to config
	 * @param array $arr
	 * @param string $path
	 */
	function setConfigArr($arr, $path = "") {
		$path = $path == "" ? "" : $path . ".";

		foreach($arr as $key => $value) {
			// db should not be converted
			if($key == "db" && $path == "") {
				continue;
			}

			if(is_array($arr[$key])) {
				$this->setConfigArr($arr[$key], $path.$key);
			} else {
				$name = $path . $key;
				$this->setConfig($name, $value);
			}
		}
	}

	/**
	 * get config as array with path
	 * @param string $path
	 * @return array
	 */
	function getConfigArr($path = "") {
		$CONFIG = array();

		$CONFIG = $this->CLASS['config']->toArray();

		if($path == "") {
			$data = $CONFIG;
			return $CONFIG;
		}

		$p =& $CONFIG;
		$path_arr = explode(".", $path);

		foreach($path_arr as $key) {
			$key = trim($key);

			if(!isset($p[$key])) {
				$p[$key] = array();
			}

			$p =& $p[$key];
		}

		$data = $p;

		return $data;
	}

	/**
	 * delete a config value with a path
	 * @param string $path
	 * @param bool $recursiv
	 */
	function delConfig($path) {
		$path_arr = explode(".", $path);

		if($path_arr[0] == "db") {
			return "";
		}

		// get existing config as array and set our new config value
		$configOri= $this->CLASS['config']->toArray();
		$config =& $configOri;
		foreach($path_arr as $val) {
			if(!isset($config[$val])) {
				$config[$val] = array();
			}

			$config =& $config[$val];
		}

		// clear our path with an empty array
		$config = array();

		// write new config
		$writer = new Zend_Config_Writer_Ini(array('config' => new Zend_Config($configOri), 'filename' => $this->CLASS['config']->base->base_path.'config/app.ini'));
		$writer->write();

		// create new config instance
		$this->CLASS['config'] = new Zend_Config($configOri);
	}

	/**
	 * mark content as opened
	 * @param integer $contentid
	 * @param integer $userid
	 */
	function openContent($contentid, $userid) {
		$this->clearOpenContent();

		if(strlen($contentid) > 0 && strlen($userid) > 0) {
			$time = time();

			if(sprintf("%d",$userid) == "0") {
				$userid = session_id();
			} else {
				//echo "##";
			}

			$res = $this->CLASS['db']->squery("SELECT id FROM content_open WHERE contentid=%d AND userid='%s'",$contentid,$userid);
			$anz = $this->CLASS['db']->num_rows($res);

			if($anz != 0) {
				$res = $this->CLASS['db']->squery("UPDATE content_open SET opened=%d WHERE contentid=%d and userid='%s'",$time,$contentid,$userid);
			} else {
				$res = $this->CLASS['db']->squery("INSERT INTO content_open (contentid, userid, opened) VALUES (%d, '%s', %d)",$_GET['eid'],$userid,$time);
			}
		}
	}

	/**
	 * close opened contents
	 * @param integer $contentid
	 * @param integer $userid
	 */
	function closeOpenContent($contentid, $userid) {
		$this->clearOpenContent();

		if(sprintf("%d",$userid) == "0") {
			$userid = session_id();
		}

		if(strlen($contentid) > 0 && strlen($userid) > 0) {
			$res = $this->CLASS['db']->squery("DELETE FROM content_open WHERE contentid=%d AND userid='%s'",$contentid,$userid);
		}
	}

	/**
	 * clear old opened contents
	 */
	function clearOpenContent() {
		$timeout = 1800;
		$time = time() - $timeout;

		$res = $this->CLASS['db']->squery("DELETE FROM content_open WHERE opened <= %d",$time);
	}

	/**
	 * show if content is already opened by other user
	 * @param integer $contentid
	 * @param integer $userid
	 * @return bool
	 */
	function isOpenContent($contentid, $userid) {
		$this->clearOpenContent();

		if(strlen($contentid) > 0 && strlen($userid) > 0) {
			if(sprintf("%d",$userid) == "0") {
				$userid = session_id();
			}

			$res = $this->CLASS['db']->squery("SELECT id FROM content_open WHERE contentid=%d AND userid!='%s'",$contentid,$userid);
			$anz = $this->CLASS['db']->num_rows($res);

			if($anz > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * returns an array with rights if the pageid should inherit rights - if not it returns false
	 * @param integer $pageid
	 * @return array will return false if no rights are found
	 */
	function getInheritRights($pageid) {
		if($this->isInheritRights($pageid)) {
			// check internal cache that was build by function isInheritRights
			if(isset($this->inerhitpagerights_cache[$pageid]) && is_array($this->inerhitpagerights_cache[$pageid])) {
				$row = $this->inerhitpagerights_cache[$pageid];

				if($row['subinheritrights'] == 1) {
					return $row;
				} elseif($row['subinheritrightsdisable'] == 1) {
					return $row;
				} else {
					return $this->getInheritRights($row['belongs_to']);
				}
			}
		}

		return false;
	}

	/**
	 * return true or false if the pageid should inherit rights
	 * @param integer $pageid
	 * @return bool
	 */
	function isInheritRights($pageid) {
		if(isset($this->inerhitpagerights_cache[$pageid]) && is_array($this->inerhitpagerights_cache[$pageid])) {
			$row = $this->inerhitpagerights_cache[$pageid];

			if($row['subinheritrights'] == 1) {
				return true;
			} elseif($row['subinheritrightsdisable'] == 1) {
				return false;
			} else {
				if($pageid != 0) {
					return $this->isInheritRights($row['belongs_to']);
				}
			}
		} else {
			$res = $this->CLASS['db']->squery("SELECT id, belongs_to, subinheritrights, subinheritrightseditable, subinheritrightsdisable, subinheritowner, subinheritgroup, subinherituserrights, subinheritgrouprights, subinheritotherrights FROM tree WHERE id=%d AND deleted=0",$pageid);
			$anz = $this->CLASS['db']->num_rows($res);

			if($anz == 1) {
				$row = $this->CLASS['db']->fetch_assoc($res);
				$this->inerhitpagerights_cache[$pageid]  = $row;

				if($row['subinheritrights'] == 1) {
					return true;
				} elseif($row['subinheritrightsdisable'] == 1) {
					return false;
				} else {
					if($pageid != 0) {
						return $this->isInheritRights($row['belongs_to']);
					}
				}
			}
		}

		return false;
	}

	/**
	 * This function will return environment variables
	 *
	 * This function was used from typo3 and was modified
	 *
	 * @param string $name name of the environment
	 * @return string value of this environment
	 */
	function getEnv($name) {
		switch((string)$name)	{
			case 'SCRIPT_NAME':
				return (php_sapi_name()=='cgi'||php_sapi_name()=='cgi-fcgi')&&($_SERVER['ORIG_PATH_INFO']?$_SERVER['ORIG_PATH_INFO']:$_SERVER['PATH_INFO']) ? ($_SERVER['ORIG_PATH_INFO']?$_SERVER['ORIG_PATH_INFO']:$_SERVER['PATH_INFO']) : ((isset($_SERVER['ORIG_SCRIPT_NAME']) && $_SERVER['ORIG_SCRIPT_NAME'])?$_SERVER['ORIG_SCRIPT_NAME']:$_SERVER['SCRIPT_NAME']);
			break;
			case 'SCRIPT_FILENAME':
				return str_replace('//','/', str_replace('\\','/', (php_sapi_name()=='cgi'||php_sapi_name()=='isapi' ||php_sapi_name()=='cgi-fcgi')&&($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED'])? ($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED']):($_SERVER['ORIG_SCRIPT_FILENAME']?$_SERVER['ORIG_SCRIPT_FILENAME']:$_SERVER['SCRIPT_FILENAME'])));
			break;
			case 'REQUEST_URI':
					// Typical application of REQUEST_URI is return urls, forms submitting to itself etc. Example: returnUrl='.rawurlencode($this->getIndpEnv('REQUEST_URI'))
				if (!$_SERVER['REQUEST_URI'])	{	// This is for ISS/CGI which does not have the REQUEST_URI available.
					return '/'.ereg_replace('^/','',$this->getEnv('SCRIPT_NAME')).
						($_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:'');
				} else return $_SERVER['REQUEST_URI'];
			break;
			case 'PATH_INFO':
					// $_SERVER['PATH_INFO']!=$_SERVER['SCRIPT_NAME'] is necessary because some servers (Windows/CGI) are seen to set PATH_INFO equal to script_name
					// Further, there must be at least one '/' in the path - else the PATH_INFO value does not make sense.
					// IF 'PATH_INFO' never works for our purpose in TYPO3 with CGI-servers, then 'php_sapi_name()=='cgi'' might be a better check. Right now strcmp($_SERVER['PATH_INFO'],$this->getIndpEnv('SCRIPT_NAME')) will always return false for CGI-versions, but that is only as long as SCRIPT_NAME is set equal to PATH_INFO because of php_sapi_name()=='cgi' (see above)
//				if (strcmp($_SERVER['PATH_INFO'],$this->getIndpEnv('SCRIPT_NAME')) && count(explode('/',$_SERVER['PATH_INFO']))>1)	{
				if (php_sapi_name()!='cgi'&&php_sapi_name()!='cgi-fcgi')	{
					return $_SERVER['PATH_INFO'];
				} else return '';
			break;
				// These are let through without modification
			case 'REMOTE_ADDR':
			case 'REMOTE_HOST':
			case 'HTTP_REFERER':
			case 'HTTP_HOST':
			case 'HTTP_USER_AGENT':
			case 'HTTP_ACCEPT_LANGUAGE':
			case 'QUERY_STRING':
				return $_SERVER[$getEnvName];
			break;
			case 'DOCUMENT_ROOT':
				// Some CGI-versions (LA13CGI) and mod-rewrite rules on MODULE versions will deliver a 'wrong' DOCUMENT_ROOT (according to our description). Further various aliases/mod_rewrite rules can disturb this as well.
				// Therefore the DOCUMENT_ROOT is now always calculated as the SCRIPT_FILENAME minus the end part shared with SCRIPT_NAME.
				$SFN = $this->getEnv('SCRIPT_FILENAME');
				$SN_A = explode('/',strrev($this->getEnv('SCRIPT_NAME')));
				$SFN_A = explode('/',strrev($SFN));
				$acc = array();
				while(list($kk,$vv)=each($SN_A))	{
					if (!strcmp($SFN_A[$kk],$vv))	{
						$acc[] = $vv;
					} else break;
				}
				$commonEnd=strrev(implode('/',$acc));
				if (strcmp($commonEnd,''))	{ $DR = substr($SFN,0,-(strlen($commonEnd)+1)); }
				return $DR;
			break;
			case 'HOST_ONLY':
				$p = explode(':',$_SERVER['HTTP_HOST']);
				return $p[0];
			break;
			case 'PORT':
				$p = explode(':',$_SERVER['HTTP_HOST']);
				return $p[1];
			break;
			case 'REQUEST_HOST':
				return ($this->getEnv('SSL') ? 'https://' : 'http://').
					$_SERVER['HTTP_HOST'];
			break;
			case 'REQUEST_URL':
				return $this->getEnv('REQUEST_HOST').$this->getEnv('REQUEST_URI');
			break;
			case 'REQUEST_SCRIPT':
				return $this->getEnv('REQUEST_HOST').$this->getEnv('SCRIPT_NAME');
			break;
			case 'REQUEST_DIR':
				return $this->getEnv('REQUEST_HOST').$this->dirname($this->getEnv('SCRIPT_NAME')).'/';
			break;
			case 'SITE_URL':
				if (defined('PATH_thisScript') && defined('PATH_site'))	{
					$lPath = substr(dirname(PATH_thisScript),strlen(PATH_site)).'/';
					$url = $this->getEnv('REQUEST_DIR');
					$siteUrl = substr($url,0,-strlen($lPath));
					if (substr($siteUrl,-1)!='/')	$siteUrl.='/';
					return $siteUrl;
				} else return '';
			break;
			case 'SITE_SCRIPT':
				return substr($this->getEnv('REQUEST_URL'),strlen($this->getEnv('SITE_URL')));
			break;
			case 'SSL':
				return (isset($_SERVER['SSL_SESSION_ID']) && $_SERVER['SSL_SESSION_ID']) || (isset($_SERVER['HTTPS']) && !strcmp($_SERVER['HTTPS'],'on')) ? TRUE : FALSE;
			break;
			case '_ALL_':
				$out = array();
					// Here, list ALL possible keys to this function for debug display.
				$envTestVars = explode(',','HTTP_HOST,HOST_ONLY,PORT,PATH_INFO,QUERY_STRING,REQUEST_URI,HTTP_REFERER,REQUEST_HOST,REQUEST_URL,REQUEST_SCRIPT,REQUEST_DIR,SITE_URL,SITE_SCRIPT,SSL,SCRIPT_NAME,DOCUMENT_ROOT,SCRIPT_FILENAME,REMOTE_ADDR,REMOTE_HOST,HTTP_USER_AGENT,HTTP_ACCEPT_LANGUAGE');

				foreach($envTestVars as $key => $v) {
					$out[trim($v)]=$this->getEnv(trim($v));
				}
				reset($out);
				return $out;
			break;
		}
	}

	/**
	 * FROM TYPO3
	 * Returns the directory part of a path without trailing slash
	 * If there is no dir-part, then an empty string is returned.
	 * Behaviour:
	 *
	 * '/dir1/dir2/script.php' => '/dir1/dir2'
	 * '/dir1/' => '/dir1'
	 * 'dir1/script.php' => 'dir1'
	 * 'd/script.php' => 'd'
	 * '/script.php' => ''
	 * '' => ''
	 * Usage: 5
	 *
	 * @param	string		Directory name / path
	 * @return	string		Processed input value. See function description.
	 */
	function dirname($path)	{
		$p=$this->revExplode('/',$path,2);
		return count($p)==2?$p[0]:'';
	}

	/**
	 * FROM TYPO3
	 * Reverse explode which explodes the string counting from behind.
	 * Thus t3lib_div::revExplode(':','my:words:here',2) will return array('my:words','here')
	 * Usage: 8
	 *
	 * @param	string		Delimiter string to explode with
	 * @param	string		The string to explode
	 * @param	integer		Number of array entries
	 * @return	array		Exploded values
	 */
	function revExplode($delim, $string, $count=0)	{
		$temp = explode($delim,strrev($string),$count);
		while(list($key,$val)=each($temp))	{
			$temp[$key]=strrev($val);
		}
		$temp=array_reverse($temp);
		reset($temp);
		return $temp;
	}

	/**
	 * save multiple rights to table access
	 * @param string $table tablename
	 * @param integer $belongs_to id to that the rights belongs
	 * @param string $owner_group owner or group as o or g
	 * @param integer $owner_group_id id of owner or group
	 * @param integer $rights rights for that id - could be 0, 1, 2
	 * @return boolean return bool or resultset of sqlquery
	 */
	function saveMultipleRights($table_name, $belongs_to, $owner_group, $owner_group_id, $rights) {
		// first check if rights for this user/group exists on this id on this table
		$res = $this->CLASS['db']->squery("SELECT id,rights FROM access WHERE table_name='%s' AND belongs_to=%d AND owner_group='%s' AND owner_group_id=%d",$table_name, $belongs_to, $owner_group, $owner_group_id);
		$anz = $this->CLASS['db']->num_rows($res);

		if($anz == 1) {
			$row = $this->CLASS['db']->fetch_assoc($res);
			// check if rights are different - if not no update is needed
			if($row['rights'] != $rights) {
				$res = $this->CLASS['db']->squery("UPDATE access SET rights=%d WHERE table_name='%s' AND belongs_to=%d AND owner_group='%s' AND owner_group_id=%d",$rights,$table_name, $belongs_to, $owner_group, $owner_group_id);
				return $res;
			} else {
				return true;
			}
		} else {
			$res = $this->CLASS['db']->squery("INSERT INTO access (table_name, belongs_to, owner_group, owner_group_id, rights) VALUES ('%s', %d, '%s', %d, %d)",$table_name, $belongs_to, $owner_group, $owner_group_id, $rights);
			return $res;
		}

		return false;
	}

	/**
	 * save multiple rights to table access with array
	 *
	 * array must contain the following elements
	 * $arr[1] = array(
	 * 	'table_name' => 'name',
	 * 	'belongs_to' => 'id',
	 * 	'owner_group' => 'o',
	 * 	'owner_group_id' => 'id',
	 * 	'rights' => '2',
	 * );
	 *
	 * @param array $arr multiple rights as array
	 * @return array return bool or result in each array element
	 */
	function saveMultipleRightsArr($arr) {
		if(is_array($arr)) {
			$arr_out = array();

			foreach($arr as $key => $value) {
				if(isset($value['table_name']) && isset($value['belongs_to']) && isset($value['owner_group']) && isset($value['owner_group_id']) && isset($value['rights'])) {
					$arr_out[$key] = $this->saveMultipleRights($value['table_name'],$value['belongs_to'],$value['owner_group'],$value['owner_group_id'],$value['rights']);
				} else {
					$arr_out[$key] = false;
				}
			}

			return $arr_out;
		} else {
			return false;
		}
	}

	/**
	 * delete all existing multiple rights for an object
	 * @param string $table_name name of table
	 * @param integer $belongsto id of object
	 * @return resource return resultset of query
	 */
	function deleteMultipleRightsAll($table_name, $belongs_to) {
		// clear old rights
		$res = $this->CLASS['db']->squery("DELETE FROM access WHERE table_name='%s' AND belongs_to=%d",$table_name, $belongs_to);
		return $res;
	}

	/**
	 * delete multiple rights from table access
	 */
	function deleteMultipleRights($table_name, $belongs_to, $owner_group, $owner_group_id) {
		$res = $this->CLASS['db']->squery("DELETE FROM access WHERE table_name='%s' AND belongs_to=%d AND owner_group='%s' AND owner_group_id=%d",$table_name, $belongs_to, $owner_group, $owner_group_id);
		return $res;
	}

	/**
	 * get all rights for an element
	 * @param string $table tablename
	 * @param integer $belongsto id of element
	 * @return array array with all rights
	 */
	function getMultipleRights($table, $belongsto) {
		$out = array();
		$res = $this->CLASS['db']->squery("SELECT * FROM access WHERE table_name='%s' AND belongs_to=%d",$table,$belongsto);
		while($row = $this->CLASS['db']->fetch_assoc($res)) {
			$out[] = $row;
		}

		return $out;
	}

	/**
	 * check rights for an user on element
	 * @param integer $userid id of user
	 * @param string $table tablename
	 * @param integer $belongsto id of element
	 * @return integer will return rights on element (0,1,2)
	 */
	function checkMultipleRights($userid, $table, $belongsto) {
		// default rights for user - no rights
		$rights = 0;

		// get rights from element
		$element = $this->getMultipleRights($table,$belongsto);

		// array with id of groups
		$groupids = array();

		// get default groupid from user
		$res = $this->CLASS['db']->squery("SELECT admin,defaultgroup FROM users WHERE id=%d AND deleted=0",$userid);
		$anz = $this->CLASS['db']->num_rows($res);
		if($anz == 1) {
			$row = $this->CLASS['db']->fetch_assoc($res);
			// check if admin then return 2
			if($row['admin'] == 1) return 2;

			$groupids[$row['defaultgroup']] = $row['defaultgroup'];
		}

		// get group ids
		$res = $this->CLASS['db']->squery("SELECT groupid FROM user_group WHERE userid=%d",$userid);
		while($row = $this->CLASS['db']->fetch_assoc($res)) {
			$groupids[$row['groupid']] = $row['groupid'];
		}

		// now check the rights
		foreach($element as $key => $value) {
			// check if rights are for owner or for group
			if($value['owner_group'] == "o") {
				// check if id of element is id of user
				if($value['owner_group_id'] == $userid) {
					// rights are for this user
					// now set rights if they are not high enough
					if($value['rights'] > $rights) {
						// set new rights
						$rights = $value['rights'];
					}
				}
			} elseif($value['owner_group'] == "g") {
				// now we must run for every groupid for this user to check the rights
				foreach($groupids as $gkey => $gvalue) {
					// check if groupid is the same as the rightid
					if($gvalue == $value['owner_group_id']) {
						// now set rights if they are not high enough
						if($value['rights'] > $rights) {
							// set new rights
							$rights = $value['rights'];
						}
					}
				}
			}
		}

		return $rights;
	}

	/**
	 * return dropdown for users
	 * @param string $name name of dropdown
	 * @param integer $userid optional userid that would be selected
	 * @return string return html dropdown
	 */
	function userSelectDropDown($name, $userid = "") {
		$cache = array();

		if(is_array($this->userdropdowncache)) {
			$cache = $this->userdropdowncache;
		} else {
			$res = $this->CLASS['db']->query("SELECT id,name FROM users WHERE deleted=0 ORDER BY name ASC");
			while($row = $this->CLASS['db']->fetch_assoc($res)) {
				$cache[$row['id']] = $row['name'];
			}

			$this->userdropdowncache = $cache;
		}

		$users = "\n<select name=\"".$name."\" class=\"form-control form-control-sm\">\n";
		foreach($cache as $key => $value) {
			// clean value
			$value = str_replace("\r\n", "", $value);
			$value = str_replace("\n", "", $value);
			$value = str_replace("\r", "", $value);

			if($userid != "" && $key == $userid) {
				$users .= "\t<option value=\"".$key."\" selected=\"selected\">".htmlspecialchars($value)."</option>\n";
			} else {
				$users .= "\t<option value=\"".$key."\">".htmlspecialchars($value)."</option>\n";
			}
		}
		$users .= "</select>\n";

		return $users;
	}

	/**
	 * return dropdown for groups
	 * @param string $name name of dropdown
	 * @param integer $groupid optional groupid that would be selected
	 * @return string return html dropdown
	 */
	function groupSelectDropDown($name, $groupid = "") {
		$cache = array();

		if(is_array($this->groupdropdowncache)) {
			$cache = $this->groupdropdowncache;
		} else {
			$res = $this->CLASS['db']->query("SELECT id,name FROM groups WHERE deleted=0 ORDER BY name ASC");
			while($row = $this->CLASS['db']->fetch_assoc($res)) {
				$cache[$row['id']] = $row['name'];
			}

			$this->groupdropdowncache = $cache;
		}

		$groups = "\n<select name=\"".$name."\" class=\"form-control form-control-sm\">\n";
		foreach($cache as $key => $value) {
			if($groupid != "" && $key == $groupid) {
				$groups .= "\t<option value=\"".$key."\" selected=\"selected\">".$value."</option>\n";
			} else {
				$groups .= "\t<option value=\"".$key."\">".$value."</option>\n";
			}
		}
		$groups .= "</select>\n";

		return $groups;
	}

	/**
	 * save a form with multiple rights that are sended
	 * @param string $table table name
	 * @param integer $belongsto id of object
	 * @param bool $deleteExisting should exisitings rights are deleted
	 */
	function saveMultipleRightsForm($table, $belongsto, $deleteExisting = false) {
		if (isset ($_SESSION['rightedit']) and $_SESSION['rightedit'] == 1) {
			// delete existing rights
			if($deleteExisting == true) $this->CLASS['knowledgeroot']->deleteMultipleRightsAll($table,$belongsto);

			if(isset($_POST['users']) && isset($_POST['muserrights']) && is_array($_POST['users']) && is_array($_POST['muserrights'])) {
				$rights = array();

				foreach($_POST['users'] as $key => $value) {
					$rights[] = array(
						'table_name' => $table,
						'belongs_to' => $belongsto,
						'owner_group' => 'o',
						'owner_group_id' => $value,
						'rights' => $_POST['muserrights'][$key],
					);
				}

				$res = $this->CLASS['knowledgeroot']->saveMultipleRightsArr($rights);
			}

			if(isset($_POST['groups']) && isset($_POST['mgrouprights']) && is_array($_POST['groups']) && is_array($_POST['mgrouprights'])) {
				$rights = array();

				foreach($_POST['groups'] as $key => $value) {
				$rights[] = array(
						'table_name' => $table,
						'belongs_to' => $belongsto,
						'owner_group' => 'g',
						'owner_group_id' => $value,
						'rights' => $_POST['mgrouprights'][$key],
					);
				}

				$res = $this->CLASS['knowledgeroot']->saveMultipleRightsArr($rights);
			}
		}
	}

	/**
	 * save a form with multiple subinheritrights that are sended
	 * @param string $table table name
	 * @param integer $belongsto id of object
	 * @param bool $deleteExisting should exisitings rights are deleted
	 */
	function saveSubinheritMultipleRightsForm($table, $belongsto, $deleteExisting = false) {
		if (isset ($_SESSION['admin']) and $_SESSION['admin'] == 1) {
			// delete existing rights
			if($deleteExisting == true) $this->CLASS['knowledgeroot']->deleteMultipleRightsAll($table,$belongsto);

			if(isset($_POST['subinheritusers']) && isset($_POST['subinheritmuserrights']) && is_array($_POST['subinheritusers']) && is_array($_POST['subinheritmuserrights'])) {
				$rights = array();

				foreach($_POST['subinheritusers'] as $key => $value) {
					$rights[] = array(
						'table_name' => $table,
						'belongs_to' => $belongsto,
						'owner_group' => 'so',
						'owner_group_id' => $value,
						'rights' => $_POST['subinheritmuserrights'][$key],
					);
				}

				$res = $this->CLASS['knowledgeroot']->saveMultipleRightsArr($rights);
			}

			if(isset($_POST['subinheritgroups']) && isset($_POST['subinheritmgrouprights']) && is_array($_POST['subinheritgroups']) && is_array($_POST['subinheritmgrouprights'])) {
				$rights = array();

				foreach($_POST['subinheritgroups'] as $key => $value) {
				$rights[] = array(
						'table_name' => $table,
						'belongs_to' => $belongsto,
						'owner_group' => 'sg',
						'owner_group_id' => $value,
						'rights' => $_POST['subinheritmgrouprights'][$key],
					);
				}

				$res = $this->CLASS['knowledgeroot']->saveMultipleRightsArr($rights);
			}
		}
	}

	/**
	 * get and save multiplerights from a page to another
	 * @param string $table
	 * @param integer $sourceId
	 * @param integer $destinationId
	 * @param bool $subinherit should also inherit subinheritrights?
	 */
	function saveInheritMultipleRightsFrom($table, $sourceId, $destinationId, $subinherit = false) {
		$sourceArr = array();

		// get rights from source
		$res = $this->CLASS['db']->squery("SELECT * FROM access WHERE belongs_to=%d", $sourceId);
		while($row = $this->CLASS['db']->fetch_assoc($res)) {
			$sourceArr[] = $row;
		}

		// make addslashes
		$this->addSlashesOnArray($sourceArr);

		foreach($sourceArr as $key => $value) {
			if($subinherit == true) {
				if($value['owner_group'] == "so") {
					$res = $this->CLASS['db']->squery("INSERT INTO access (table_name, belongs_to, owner_group, owner_group_id, rights) VALUES ('%s', %d, '%s', %d, %d)", $table, $destinationId, "o", $value['owner_group_id'], $value['rights']);
				} elseif($value['owner_group'] == "sg") {
					$res = $this->CLASS['db']->squery("INSERT INTO access (table_name, belongs_to, owner_group, owner_group_id, rights) VALUES ('%s', %d, '%s', %d, %d)", $table, $destinationId, "g", $value['owner_group_id'], $value['rights']);
				}
			} else {
				if($value['owner_group'] == "o" || $value['owner_group'] == "g") {
					$res = $this->CLASS['db']->squery("INSERT INTO access (table_name, belongs_to, owner_group, owner_group_id, rights) VALUES ('%s', %d, '%s', %d, %d)", $table, $destinationId, $value['owner_group'], $value['owner_group_id'], $value['rights']);
				}
			}
		}
	}

	/**
	 * get and save subinherit multiple rights from the inheritpage to another page
	 * @param string $table
	 * @param integer $sourceId
	 * @param integer $destinationId
	 * @param bool $subinherit should also inherit subinheritrights?
	 */
	function saveSubInheritMultipleRightsFrom($table, $sourceId, $destinationId, $subinherit = false) {
		$iRights = $this->getInheritRights($sourceId);

		if(is_array($iRights) && isset($iRights['id']) && $iRights['id'] != "") {
			return $this->saveInheritMultipleRightsFrom($table, $iRights['id'], $destinationId, $subinherit);
		} else {
			return $this->saveInheritMultipleRightsFrom($table, $sourceId, $destinationId, $subinherit);
		}
	}

	/**
	 * get pageid from contentid
	 * @param integer $contentId
	 * @return integer pageid
	 */
	function getPageIdFromContentId($contentId) {
		$ret = null;

		$res = $this->CLASS['db']->squery("SELECT belongs_to FROM content WHERE id=%d", (int)$contentId);
		if($row = $this->CLASS['db']->fetch_assoc($res)) {
			$ret = $row['belongs_to'];
		}

		return $ret;
	}
}

?>
