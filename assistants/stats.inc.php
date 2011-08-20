<?php
$script = $_SERVER['SCRIPT_NAME'].'/assistant/stats';
$month=date('Y-m');
$month_sql='';
if(isset($_GET['month'])) {
	$month = htmlspecialchars($_GET['month']);
	$month_sql = " AND DATE_FORMAT(item_valid_from, '%Y-%m') = ".$this->db->quote($_GET['month']);
}
?>

<form action="<?=$script?>/" method="GET">
	YYYY-MM: <input type="text" name="month" autofocus value="<?=$month?>" />
	<input type="submit" value="SELECT BY MONTH" />
</form>
<?php

if($month_sql == '') $month='';
$queries = array( //TODO: use build_query_select()!!!
	"Nakoupeno celkem $month"
		=> 'SELECT COUNT(item_id),SUM(item_price_in) FROM item WHERE item_valid_till=0'.$month_sql,
	"Použito celkem $month"
		=> 'SELECT COUNT(item_id),SUM(item_price_in) FROM item WHERE item_valid_till=0 AND status_id = 2'.$month_sql,
	"Prodáno celkem $month"
		=> 'SELECT COUNT(item_id),SUM(item_price_in) FROM item WHERE item_valid_till=0 AND status_id = 3'.$month_sql,
	"Počet kusů skladem"
		=> 'SELECT room_id,room_name,model_id,model_name,model_barcode,COUNT(item_id)'.
		' FROM item LEFT JOIN model USING(model_id) LEFT JOIN room USING(room_id)'.
		' WHERE item_valid_till=0 AND status_id=1'.
		' GROUP BY model_id,room_id'.
		' ORDER BY room_id,model_id;'
);

foreach($queries as $description => $sql) {
	echo "<h2>$description</h2>";
	echo $this->html->render_item_table($result = $this->db->safe_query($sql)->fetchAll(PDO::FETCH_ASSOC));
}