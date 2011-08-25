<?php
switch($SUBPATH[0]) {
	default: case 1:
		echo $this->html->form("$URL/2", 'GET', array(
			array('barcode','','text',false,'autofocus','model_barcode:'),
			array('quantity','1','text',false,false,'quantity:'),
			array(false,'STORE','submit')
		));
		break;
	case 2:
		$model_id = $this->db->map_unique('model_barcode', $_GET['barcode'], 'model_id', 'model');

		$disable_cols = array('status_id','item_price_out','item_customer', 'model_id','item_quantity');
		if($this->db->map_unique('model_barcode', $_GET['barcode'], 'model_countable', 'model')) {
			//$disable_cols[] = 'item_quantity';
			$item_serial = '';
			$item_quantity = 1;
			$action = $_SERVER['SCRIPT_NAME'].'/item/new';
		} else {
			$quantity_added = $_GET['quantity'];
			if($quantity_added <= 0) $this->post_redirect_get("$URL_INTERNAL/1","Can't store non-possitive amount of items!");
			if(!is_numeric($quantity_added)) $quantity_added = 1;
			$quantity_stored = $this->db->map_unique('item_serial', $_GET['barcode'], 'item_quantity', 'item', false);
			if(!is_numeric($quantity_stored)) $quantity_stored = 0;
			echo("Quantity stored: ".$quantity_stored);

			$disable_cols[] = 'item_serial';
			$item_serial = $_GET['barcode'];
			$item_quantity = $quantity_stored + $quantity_added;
			$action = $_SERVER['SCRIPT_NAME'].'/item/0/edit';
		}
		$columns = $this->db->get_columns('item');

    $selectbox = $this->db->columns_get_selectbox($columns, 'item');
		//print_r(array('<pre>', $selectbox));
		//foreach($selectbox['model_id'] as $id => $name) if($id != $model_id) unset($selectbox['model_id'][$id]);
		$current = array(array(
			'model_id' => $model_id,
			'item_serial' => $item_serial,
			'item_quantity' => $item_quantity,
			'status_id' => 1,
			'item_price_in' => $this->db->map_unique('model_barcode', $_GET['barcode'], 'model_price_in', 'model'),
			'item_price_out' => $this->db->map_unique('model_barcode', $_GET['barcode'], 'model_price_out', 'model')
		));

    echo $this->html->render_insert_form('item', $columns, $selectbox, $current, $disable_cols, $action);
		break;
}
