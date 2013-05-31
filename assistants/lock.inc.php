<?php
switch($SUBPATH[0]) {
	default: case 1:
		$result = $this->db->safe_query_fetch("SELECT * FROM `lock`;");
		if(empty($result)) {
			echo 'Not locked...';
			echo $this->html->form("$URL/2", 'POST', array(
				array('reason','','textarea',false,'autofocus','reason:'),
				array('lock','lock','submit')
			));
		} else {
			echo $this->html->render_item_table($result, 'lock');
			echo $this->html->form("$URL/2", 'POST', array(
				array('unlock','unlock','submit')
			));
		}

		break;
	case 2:
		if(isset($_POST['lock'])) {
			$user=$this->db->auth->get_user_id();
			$username=$this->db->auth->get_username_by_id($user);
			$lock = $this->db->quote($username.': '.$_POST['reason']);
			$this->db->safe_query("INSERT INTO `lock` (lock_name) VALUES ($lock);");
			$this->post_redirect_get("$URL_INTERNAL/1", T('Lock set'));
		}
		if(isset($_POST['unlock'])) {
			$this->db->safe_query("TRUNCATE TABLE `lock`;");
			$this->post_redirect_get("$URL_INTERNAL/1", T('Lock unset'));
		}
		$this->post_redirect_get("$URL_INTERNAL/1","Lock: No value passed!", true);
		break;
}
