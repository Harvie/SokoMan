<?php
//TODO: Merge SELL and DISPOSE to single file with parameter!
//TODO: Highlight fields that should be filled (and maybe even check them when submited)
//TODO: Add support for selling/disposing multiple items at once...
//TODO: Reuse /item/$item_id/edit
switch($SUBPATH[0]) {
	default: case 1:
		echo $this->html->form("$URL/2", 'GET', array(
			array('serial','','text',false,'autofocus','item_serial:'),
			array(false,'SELL','submit')
		));
		break;
	case 2:
		$item_id = $this->db->map_unique('item_serial', $_GET['serial'], 'item_id', 'item');
		$columns = $this->db->get_columns('item');
    $selectbox = $this->db->columns_get_selectbox($columns, 'item');

		$current = $this->db->get_listing('item', $item_id, 1);
		$current[$item_id]['status_id'] = 3;

		$action = $_SERVER['SCRIPT_NAME']."/item/$item_id/edit";
    echo $this->html->render_insert_form('item', $columns, $selectbox, $current, false, $action);
		break;
}
