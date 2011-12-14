<?php
switch($SUBPATH[0]) {
	default: case 1:
		echo $this->html->form("$URL/2", 'POST', array(
			array('margin','40','text',false,'autofocus','margin (%):'),
			array(false,'SET-MARGIN','submit')
		));
		break;
	case 2:
		if(isset($_POST['margin'])) {
			$margin = 1+($_POST['margin']/100);
			$this->db->safe_query("UPDATE model SET model_price_out = CEIL(model_price_in * $margin)");
			$this->post_redirect_get("$URL_INTERNAL/1", T('Margin set to').' '.($margin*100-100).'%');
		}
		$this->post_redirect_get("$URL_INTERNAL/1","Set-margin: No value passed!", true);
		break;
}
