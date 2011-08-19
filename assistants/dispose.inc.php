<?php
$script = $_SERVER['SCRIPT_NAME'].'/assistant/dispose';
switch($step) {
	default: case 1:
?>
<form action="<?=$script?>/2" method="GET">
	item_serial: <input type="text" name="serial" autofocus />
	<input type="submit" value="DISPOSE" />
</form>
<?php
		break;
	case 2:
		$item_id = $this->db->map_unique('item_serial', $_GET['serial'], 'item_id', 'item');
		$columns = $this->db->get_columns('item');
    $selectbox = $this->db->columns_get_selectbox($columns, 'item');

		$current = $this->db->get_listing('item', $item_id, 1);
		$current[$item_id]['status_id'] = 2;

		$action = $_SERVER['SCRIPT_NAME']."/item/$item_id/edit";
    echo $this->html->render_insert_form('item', $columns, $selectbox, $current, false, $action);
		break;
}
