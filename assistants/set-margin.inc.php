<?php
switch($SUBPATH[0]) {
	default: case 1:
		echo $this->html->form("$URL/2", 'GET', array(
			array('margin','40','text',false,'autofocus','margin (%):'),
			array(false,'SET-MARGIN','submit')
		));
		break;
	case 2:
		$margin = 1+($_GET['margin']/100);
		$this->db->safe_query("UPDATE model SET model_price_out = CEIL(model_price_in * $margin)");
		echo(T('Margin set to').' '.($margin*100-100).'%');
		break;
}
