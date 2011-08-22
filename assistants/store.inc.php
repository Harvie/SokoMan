<?php
switch($SUBPATH[0]) {
	default: case 1:
?>
<form action="<?=$URL?>/2" method="GET">
	model_barcode: <input type="text" name="barcode" autofocus />
	<input type="submit" value="STORE" />
</form>
<?php
		break;
	case 2:
		$model_id = $this->db->map_unique('model_barcode', $_GET['barcode'], 'model_id', 'model');
		$columns = $this->db->get_columns('item');
    $selectbox = $this->db->columns_get_selectbox($columns, 'item');

		//print_r(array('<pre>', $selectbox));
		//foreach($selectbox['model_id'] as $id => $name) if($id != $model_id) unset($selectbox['model_id'][$id]);
		$current = array(array(
			'model_id' => $model_id,
			'item_quantity' => 1,
			'status_id' => 1
		));

		$action = $_SERVER['SCRIPT_NAME'].'/item/new';
    echo $this->html->render_insert_form('item', $columns, $selectbox, $current, false, $action);
		break;
}
