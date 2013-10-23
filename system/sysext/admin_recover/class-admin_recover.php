<?php
/**
 * Knowledgeroot password recover extension
 *
 * @author Frank Habermann <lordlamer@lordlamer.de>
 * @date 20100815
 */

class admin_recover extends extension_base {

	function main() {
		$content = "";

		// add menu item to admin navi
		$this->menu['admin']['recover']['name'] = $this->_('users');
		$this->menu['admin']['recover']['link'] = "index.php?action=show_recover";
		$this->menu['admin']['recover']['priority'] = "10";

		// check if informations should be shown
		if(isset($_GET['action']) and $_GET['action'] == "show_recover") {
			$content = $this->show_info();
		}

		// check if password should be reset
		if(isset($_POST['action']) and $_POST['action'] == "admin_recover_reset_password") {
			if(isset($_POST['userid']) && $_POST['userid'] && isset($_POST['password']) && $_POST['password']) {
				$content = $this->resetPassword($_POST['userid'], $_POST['password']);
			} else {
				$content .= $this->_('Could not reset password');
			}
		}

		// check if user should be created
		if(isset($_POST['action']) and $_POST['action'] == "admin_recover_create_user") {
			if(isset($_POST['username']) && $_POST['username'] != '' && isset($_POST['password']) && $_POST['password'] != '') {
				$content .= $this->createUser($_POST['username'], $_POST['password']);
			} else {
				$content .= $this->_('Could not create user');
			}
		}

		return $content;
	}

	// show informations
	function show_info() {
		$out = '
<script type="text/javascript">
	dojo.require("dijit.form.Select");
	dojo.require("dijit.form.Button");
	dojo.require("dijit.TooltipDialog");
	dojo.require("dijit.form.TextBox");
	dojo.require("dijit.TitlePane");
</script>
<div style="font-size: 14px; font-weight: bold;">
'.$this->_('Add new users or edit existing users').'
</div>
<div style="margin-top:10px;" dojoType="dijit.TitlePane" title="'.$this->_('Edit existing user').'">
	<form action="index.php" method="post" id="reset_password">
	<input type="hidden" name="action" value="admin_recover_reset_password" />
	<select name="userid" dojoType="dijit.form.Select">
';
		$res = $this->CLASS['db']->query("SELECT id,name FROM users WHERE deleted=0");
		while($row = $this->CLASS['db']->fetch_assoc($res)) {
			$out .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
		}

		$out .= '
	</select>
	<input dojoType=dijit.form.TextBox type="password" id="password" name="password">
	<button dojoType="dijit.form.Button" onclick="document.getElementById(\'reset_password\').submit();">
	'.$this->_('set new password').'
	</button>
	</form>
</div>
<div style="margin-top:10px;" dojoType="dijit.TitlePane" title="'.$this->_('Create new user').'">
	<form action="index.php" method="post" id="create_user">
	<input type="hidden" name="action" value="admin_recover_create_user" />
	<input type="hidden" id="create_username" name="username" value="" />
	<input type="hidden" id="create_password" name="password" value="" />
<button dojoType="dijit.form.DropDownButton">
	<span>'.$this->_('Add new admin user').'</span>
	<div dojoType="dijit.TooltipDialog" id="tooltipDlg" title="'.$this->_('Enter Login information').'" execute="dojo.byId(\'create_username\').value = dojo.byId(\'user\').value; dojo.byId(\'create_password\').value = dojo.byId(\'pwd\').value; dojo.byId(\'create_user\').submit();">
		<table>
			<tr>
				<td><label for="user">'.$this->_('User').':</label></td>

				<td><input dojoType="dijit.form.TextBox" type="text" id="user" name="user" value=""></td>
			</tr>
			<tr>
				<td><label for="pwd">'.$this->_('Password').':</label></td>
				<td><input dojoType="dijit.form.TextBox" type="password" id="pwd" name="pwd" value=""></td>
			</tr>
			<tr>
				<td colspan="2" align="center">
				<button dojoType=dijit.form.Button type="submit" name="submit">'.$this->_('create').'</button>
			</tr>
		</table>
	</div>

</button>

	</form>
</div>
		';

		return $out;
	}

	function resetPassword($userid, $password) {
		$out = "";

		if($this->updateUser($userid, $password)) {
			$out .= $this->_('Password was set');
		} else {
			$out .= $this->_('Could not set password');
		}

		return $out;
	}

	function createUser($name, $password) {
		$out = "";

		if($this->createAdminUser($name, $password)) {
			$out .= $this->_('Created user');
		} else {
			$out .= $this->_('Could not create user');
		}

		return $out;
	}

	function createAdminUser($name, $password) {
		$res = $this->CLASS['db']->query(sprintf("SELECT * FROM users WHERE name='%s'", $name));
		if($res->num_rows() != 0) {
			return false;
		}

		$res = $this->CLASS['db']->query(sprintf("INSERT INTO users (name, password, theme, enabled, admin, defaultgroup, defaultrights, rightedit, deleted, treecache) VALUES ('%s', '%s', 'green', 1, 1, 1, '220', 1, 0, '')", $name, md5(addslashes($password))));

		if($res->getResult()) return true;
		else return false;
	}

	function updateUser($userid, $password) {
		$res = $this->CLASS['db']->query(sprintf("UPDATE users SET password='%s' WHERE id=%d",md5(addslashes($password)), $userid));

		if($res->getResult()) return true;
		else return false;
	}
}

?>
