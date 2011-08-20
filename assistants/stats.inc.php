<?php
$queries = array( //TODO: use build_query_select()!!!
	'Nakoupeno celkem' => 'SELECT COUNT(item_id),SUM(item_price_in) FROM item WHERE item_valid_till=0',
	'Použito celkem' => 'SELECT COUNT(item_id),SUM(item_price_in) FROM item WHERE item_valid_till=0 AND status_id = 2',
	'Prodáno celkem' => 'SELECT COUNT(item_id),SUM(item_price_in) FROM item WHERE item_valid_till=0 AND status_id = 3',
	'Počet kusů skladem' => 'SELECT room_id,room_name,model_id,model_name,model_barcode,COUNT(item_id)'.
		' FROM item LEFT JOIN model USING(model_id) LEFT JOIN room USING(room_id)'.
		' WHERE item_valid_till=0 AND status_id=1'.
		' GROUP BY model_id,room_id'.
		' ORDER BY room_id,model_id;'
);

foreach($queries as $description => $sql) {
	echo "<h2>$description</h2>";
	echo $this->html->render_item_table($result = $this->db->safe_query($sql)->fetchAll(PDO::FETCH_ASSOC));
}
