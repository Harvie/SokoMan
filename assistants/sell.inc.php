<?php
//TODO: Highlight fields that should be filled (and maybe even check them when submited)
//TODO: Add support for selling/disposing multiple items at once...
//TODO: Reuse /item/$item_id/edit
//TODO: Stop using map_unique()!!!
//TODO: we can use empty selectbox[] if no selectboxes are shown

if(!isset($status_id)) $status_id = 3;
if(!isset($item_customer)) $item_customer = '';
if(!isset($hide_cols_additional)) $hide_cols_additional = array();
$button_label = strtoupper($ASSISTANT);

$hide_cols_common = array_merge($hide_cols_additional,array('status_id','item_price_in','item_serial','item_quantity','model_id','vendor_id','room_id'));

switch($SUBPATH[0]) {
	default: case 1:
		echo $this->html->form("$URL/2", 'GET', array(
			array('serial','','text',false,'autofocus','item_serial:'),
			array('quantity','1','text',false,false,'quantity:'),
			array(false,$button_label,'submit')
		));
		break;
	case 2:
		$item_serial = $_GET['serial'];
		$item_id = $this->db->map_unique('item_serial', $item_serial, 'item_id', 'item');

		$current = $this->db->get_listing('item', $item_id, 1);
		$current[$item_id]['item_author'] = $this->db->auth->get_user_id();
		$forked_item = $current;

		$model_id = $this->db->map_unique('item_serial', $item_serial, 'model_id', 'item');
		$model_price_in = $this->db->map_unique('model_id', $model_id, 'model_price_in', 'model');
		$model_price_out = $this->db->map_unique('model_id', $model_id, 'model_price_out', 'model');

		$model_countable = $this->db->map_unique('model_id', $model_id, 'model_countable', 'model');
		if($model_countable) {
			$current[$item_id]['status_id'] = $status_id;
			$current[$item_id]['item_customer'] = $item_customer;
			$item_quantity = 1;
			$current[$item_id]['item_price_out'] =  $model_price_out;
			$hide_cols = $hide_cols_common;
		} else {
			$hide_cols = array_merge($hide_cols_common,array('item_price_out','item_note','item_customer'));
			$quantity_removed = $_GET['quantity'];
			if($quantity_removed <= 0) $this->post_redirect_get("$URL_INTERNAL/1","Can't dispose non-possitive amount of items!");
			if(!is_numeric($quantity_removed)) $quantity_removed = 1;
			$quantity_stored = $this->db->map_unique('item_serial', $item_serial, 'item_quantity', 'item', false);
			if(!is_numeric($quantity_stored)) $quantity_stored = 0;
			$item_quantity = $quantity_stored - $quantity_removed;


			echo("Stock: ".$quantity_stored."<br />Disposing/Selling: ".$quantity_removed."<br />Keeping: ".$item_quantity);

			$current[$item_id]['item_quantity'] = $item_quantity;
			$current[$item_id]['item_price_in'] =  $item_quantity * $model_price_in;
			$current[$item_id]['item_price_out'] =  $item_quantity * $model_price_out;

			$forked_item[$item_id]['item_id'] = '';
			$forked_item[$item_id]['item_serial'] .= '@'.time();
			$forked_item[$item_id]['status_id'] = $status_id;
			$forked_item[$item_id]['item_quantity'] = $quantity_removed;
			$forked_item[$item_id]['item_price_in'] =  $quantity_removed * $model_price_in;
			$forked_item[$item_id]['item_price_out'] = $quantity_removed * $model_price_out;
			$forked_item[$item_id]['item_customer'] = $item_customer;

			$forked_hide_cols = array_merge($hide_cols_common,array('item_price_out'));
		}

		$columns = $this->db->get_columns('item');
    $selectbox = $this->db->columns_get_selectbox($columns, 'item');

		$action = $_SERVER['SCRIPT_NAME']."/item/$item_id/edit";
    //echo $this->html->render_insert_form('item', $columns, $selectbox, $current, $hide_cols, $action);

		$insert_form[]=array('item', $columns, $selectbox, $current, $hide_cols, $action, false);
		if(!$model_countable) $insert_form[]=array('item', $columns, $selectbox, $forked_item, $forked_hide_cols, $action, false);
    echo $this->html->render_insert_form_multi($insert_form);
		break;
}
