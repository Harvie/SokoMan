<?php
switch($SUBPATH[0]) {
	default: case 1:
		$barcode = isset($_GET['barcode']) ? htmlspecialchars($_GET['barcode']) : ''; //TODO: XSS
		echo $this->html->form("$URL/2", 'GET', array(
			array('barcode',$barcode,'text',false,'autofocus','model_barcode:'),
			array('quantity','1','text',false,false,'quantity:'),
			array('serials','','textarea',false,'autofocus','serial(s):'),
			array(false,'STORE','submit')
		));
		break;
	case 2:
		$barcode=$_GET['barcode'];

		$barcode_id = $this->db->map_unique('barcode_name', $barcode, 'barcode_id', 'barcode');
		$model_id = $this->db->map_unique('barcode_id', $barcode_id, 'model_id', 'barcode');
		$model_price_in = $this->db->map_unique('model_id', $model_id, 'model_price_in', 'model');
		$model_price_out = $this->db->map_unique('model_id', $model_id, 'model_price_out', 'model');
		$item_price_in = $this->db->map_unique('item_serial', $barcode, 'item_price_in', 'item', false);
		$item_price_out = $this->db->map_unique('item_serial', $barcode, 'item_price_out', 'item', false);

		$countable = $this->db->map_unique('model_id', $model_id, 'model_countable', 'model');

		$serials=explode("\n",trim($_GET['serials']));
		if(!$countable || trim($_GET['serials']) == '') $serials = array('');

		foreach($serials as $serial) {
			$serial=trim($serial);

			$disable_cols = array('status_id','item_price_out','item_customer', 'model_id','item_quantity','item_date_sold');
			if($countable) {
				$multi_insert = true;
				//$disable_cols[] = 'item_quantity';
				$item_serial = $serial;
				$item_quantity = $quantity_added = 1;
				$action = $_SERVER['SCRIPT_NAME'].'/item/new';
			} else {
				$multi_insert = false;
				$quantity_added = $_GET['quantity'];
				if($quantity_added <= 0) $this->post_redirect_get("$URL_INTERNAL/1","Can't store non-possitive amount of items!");
				if(!is_numeric($quantity_added)) $quantity_added = 1;
				$quantity_stored = $this->db->map_unique('item_serial', $barcode, 'item_quantity', 'item', false);
				if(!is_numeric($quantity_stored)) $quantity_stored = 0;

				$disable_cols[] = 'item_serial';
				$item_serial = $barcode;
				$item_quantity = $quantity_stored + $quantity_added;
				$action = $_SERVER['SCRIPT_NAME'].'/item/0/edit';

				echo('Stock: '.$quantity_stored.'<br />Storing: '.$quantity_added.'<br />Total: '.$item_quantity);
			}
			$columns = $this->db->get_columns('item');

	    $selectbox = $this->db->columns_get_selectbox($columns, 'item');
			//print_r(array('<pre>', $selectbox));
			//foreach($selectbox['model_id'] as $id => $name) if($id != $model_id) unset($selectbox['model_id'][$id]);
			$current = array(array(
				'barcode_id' => $barcode_id,
				'item_serial' => $item_serial,
				'item_quantity' => $item_quantity,
				'status_id' => 1,
				'item_price_in' => $item_price_in + ($quantity_added * $model_price_in),
				'item_price_out' => $item_price_out + ($quantity_added * $model_price_out),
				'item_author' => $this->db->auth->get_user_id(),
				'item_date_bought' => date('Y-m-d'),
				'location_id' => 0
			));

			$insert_form[]=array('item', $columns, $selectbox, $current, $disable_cols, $action, $multi_insert);
		}

		echo $this->html->render_insert_form_multi($insert_form);

		break;
}
