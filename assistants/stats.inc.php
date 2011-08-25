<?php
$month=date('Y-m');
$month_sql='';
if(isset($_GET['month'])) {
	$month = htmlspecialchars($_GET['month']);
	$month_sql = " AND DATE_FORMAT(item_valid_from, '%Y-%m') = ".$this->db->quote($_GET['month']);
}

echo $this->html->form($URL, 'GET', array(
	array('month',$month,'text',false,'autofocus','YYYY-MM:'),
	array(false,'SELECT BY MONTH','submit')
));

if($month_sql == '') $month='';
$queries = array( //TODO: use build_query_select()!!!
	"Nakoupeno celkem $month"
		=> 'SELECT COUNT(item_id),SUM(item_price_in) FROM item WHERE item_valid_till=0'.$month_sql,
	"Použito celkem $month"
		=> 'SELECT COUNT(item_id),SUM(item_price_in) FROM item WHERE item_valid_till=0 AND status_id = 2'.$month_sql,
	"Prodáno celkem $month"
		=> 'SELECT COUNT(item_id),SUM(item_price_out) FROM item WHERE item_valid_till=0 AND status_id = 3'.$month_sql,
	"Skladem celkem $month"
		=> 'SELECT COUNT(item_id),SUM(item_price_in) FROM item WHERE item_valid_till=0 AND status_id = 1'.$month_sql,
	"Bilance celkem $month"
		=> 'SELECT (
				SUM(item_price_out)
				-(SELECT SUM(item_price_in) FROM item WHERE item_valid_till=0)
			) FROM item WHERE item_valid_till=0 AND ( status_id = 3 )',
	"Počet kusů skladem"
		=> 'SELECT room_id,room_name,model_id,model_name,model_barcode,COUNT(item_id),SUM(item_quantity)'.
		' FROM item LEFT JOIN model USING(model_id) LEFT JOIN room USING(room_id)'.
		' WHERE item_valid_till=0 AND status_id=1'.
		' GROUP BY model_id,room_id'.
		' ORDER BY room_id,model_id;',
);

foreach($queries as $description => $sql) {
	echo "<h2>$description</h2>";
	echo $this->html->render_item_table($result = $this->db->safe_query_fetch($sql));
}
