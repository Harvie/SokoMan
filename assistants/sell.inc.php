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

$hide_cols_common = array_merge($hide_cols_additional,array('status_id','item_price_in','item_serial','item_quantity','model_id','vendor_id','room_id','item_date_bought'));

switch($SUBPATH[0]) {
	default: case 1:
		$serial = isset($_GET['serial']) ? htmlspecialchars($_GET['serial']) : ''; //TODO: XSS
		echo $this->html->form("$URL/2", 'GET', array(
			array('serial',$serial,'text',false,'autofocus','item_serial:'),
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

		$barcode_id = $this->db->map_unique('item_id', $item_id, 'barcode_id', 'item');
		$model_id = $this->db->map_unique('barcode_id', $barcode_id, 'model_id', 'barcode');
		$model_price_in = $this->db->map_unique('model_id', $model_id, 'model_price_in', 'model');
		$model_price_out = $this->db->map_unique('model_id', $model_id, 'model_price_out', 'model');

		$current[$item_id]['status_id'] = $status_id;
		$current[$item_id]['item_customer'] = $item_customer;
		$item_quantity = 1;
		$current[$item_id]['item_price_out'] = $model_price_out;
		$current[$item_id]['item_date_sold'] = date('Y-m-d');
		$hide_cols = $hide_cols_common;

		$columns = $this->db->get_columns('item');
    $selectbox = $this->db->columns_get_selectbox($columns, 'item');

		$action = $_SERVER['SCRIPT_NAME']."/item/$item_id/edit";
    //echo $this->html->render_insert_form('item', $columns, $selectbox, $current, $hide_cols, $action);

		$insert_form[]=array('item', $columns, $selectbox, $current, $hide_cols, $action, false);
    echo $this->html->render_insert_form_multi($insert_form);
		break;
}
