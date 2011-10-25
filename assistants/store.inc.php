<?php
switch($SUBPATH[0]) {
	default: case 1:
		$barcode = isset($_GET['barcode']) ? htmlspecialchars($_GET['barcode']) : ''; //TODO: XSS
		echo $this->html->form("$URL/2", 'GET', array(
			array('barcode',$barcode,'text',false,'autofocus','model_barcode:'),
			array('quantity','1','text',false,false,'quantity:'),
			array(false,'STORE','submit')
		));
		break;
	case 2:
		$model_id = $this->db->map_unique('model_barcode', $_GET['barcode'], 'model_id', 'model');
		$item_price_in = $this->db->map_unique('item_serial', $_GET['barcode'], 'item_price_in', 'item', false);
		$item_price_out = $this->db->map_unique('item_serial', $_GET['barcode'], 'item_price_out', 'item', false);
		$model_price_in = $this->db->map_unique('model_barcode', $_GET['barcode'], 'model_price_in', 'model');
		$model_price_out = $this->db->map_unique('model_barcode', $_GET['barcode'], 'model_price_out', 'model');

		$disable_cols = array('status_id','item_price_out','item_customer', 'model_id','item_quantity');
		if($this->db->map_unique('model_barcode', $_GET['barcode'], 'model_countable', 'model')) {
			$multi_insert = true;
			//$disable_cols[] = 'item_quantity';
			$item_serial = '';
			$item_quantity = $quantity_added = 1;
			$action = $_SERVER['SCRIPT_NAME'].'/item/new';
		} else {
			$multi_insert = false;
			$quantity_added = $_GET['quantity'];
			if($quantity_added <= 0) $this->post_redirect_get("$URL_INTERNAL/1","Can't store non-possitive amount of items!");
			if(!is_numeric($quantity_added)) $quantity_added = 1;
			$quantity_stored = $this->db->map_unique('item_serial', $_GET['barcode'], 'item_quantity', 'item', false);
			if(!is_numeric($quantity_stored)) $quantity_stored = 0;

			$disable_cols[] = 'item_serial';
			$item_serial = $_GET['barcode'];
			$item_quantity = $quantity_stored + $quantity_added;
			$action = $_SERVER['SCRIPT_NAME'].'/item/0/edit';

			echo('Stock: '.$quantity_stored.'<br />Storing: '.$quantity_added.'<br />Total: '.$item_quantity);
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
			'item_price_in' => $item_price_in + ($quantity_added * $model_price_in),
			'item_price_out' => $item_price_out + ($quantity_added * $model_price_out),
			'item_author' => $this->db->auth->get_user_id()
		));

    echo $this->html->render_insert_form('item', $columns, $selectbox, $current, $disable_cols, $action, $multi_insert);
		break;
}
