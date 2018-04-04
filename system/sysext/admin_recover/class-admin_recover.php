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
				$content .= '<div class="alert alert-danger" role="alert">'.$this->_('Could not create user').'</div>';
			}
		}

		return $content;
	}

	// show informations
	function show_info() {
		$out = '
<h2>
'.$this->_('Add new users or edit existing users').'
</h2>
<div class="card">
  <div class="card-header">
    '.$this->_('Edit existing user').'
  </div>
  <div class="card-body">
	<form action="index.php" method="post" id="reset_password">
	<input type="hidden" name="action" value="admin_recover_reset_password" />
	<div class="form-group">
		<label for="userid">'.$this->_('User').'</label>
		<select name="userid" class="form-control">
	';
			$res = $this->CLASS['db']->query("SELECT id,name FROM users WHERE deleted=0");
			while($row = $this->CLASS['db']->fetch_assoc($res)) {
				$out .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
			}

			$out .= '
		</select>
	</div>	
	<div class="form-group">
		<label for="password">'.$this->_('Password').'</label>
		<input class="form-control" type="password" id="password" name="password" placeholder="password">	
	</div>
	<div class="form-group">
	<button class="btn btn-outline-secondary" onclick="document.getElementById(\'reset_password\').submit();">
	'.$this->_('set new password').'
	</button>
	</div>
	</form>
  </div>
</div>

</p>

<div class="card">
  <div class="card-header">
    '.$this->_('Create new user').'
  </div>
  <div class="card-body">
	<form action="index.php" method="post" id="create_user">
	<input type="hidden" name="action" value="admin_recover_create_user" />

	<div class="form-group">
	    <label for="username">'.$this->_('User').'</label>
    	<input type="text" class="form-control" id="username" name="username" value="" placeholder="user">
	</div>
	
	<div class="form-group">
	    <label for="password">'.$this->_('Password').'</label>
    	<input type="password" class="form-control" id="password" name="password" placeholder="password">
	</div>
	
	<div class="form-group">
		<button class="btn btn-outline-secondary" type="submit" name="submit">'.$this->_('create').'</button>
	</div>
	
	</form>
  </div>
</div>
';

		return $out;
	}

	function resetPassword($userid, $password) {
		$out = "";

		if($this->updateUser($userid, $password)) {
			$out .= '<div class="alert alert-success" role="alert">'.$this->_('Password was set').'</div>';
		} else {
			$out .= '<div class="alert alert-danger" role="alert">'.$this->_('Could not set password').'</div>';
		}

		return $out;
	}

	function createUser($name, $password) {
		$out = "";

		if($this->createAdminUser($name, $password)) {
			$out .= '<div class="alert alert-success" role="alert">'.$this->_('Created user').'</div>';
		} else {
			$out .= '<div class="alert alert-danger" role="alert">'.$this->_('Could not create user').'</div>';
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
