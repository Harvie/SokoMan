<?php
$script = $_SERVER['SCRIPT_NAME'].'/assistant/store-single';
$this->process_http_request_post('new', false, false, 'assistant/store-single/2');
switch($step) {
	default: case 1:
		echo $this->render_form_add('model');
		break;
	case 2:
		$model_id = trim($_GET['last']);
		$columns = $this->db->get_columns('item');
    $selectbox = $this->db->columns_get_selectbox($columns, 'item');

		$current = array(array(
			'model_id' => $model_id,
			'item_quantity' => 1,
			'status_id' => 1
		));

		$action = $_SERVER['SCRIPT_NAME'].'/item/new';
    echo $this->html->render_insert_form('item', $columns, $selectbox, $current, false, $action);
		break;
}
