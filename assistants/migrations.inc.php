<pre>
<h1>-- populate columns item_date_bought and item_date_sold</h1><?php
$data = $this->db->safe_query_fetch('SELECT item_id,MIN(item_valid_from) AS min,MAX(item_valid_from) AS max FROM item GROUP BY item_id;');
//print_r($data);
echo("START TRANSACTION;\n");
foreach($data as $line) echo("UPDATE item SET item_date_bought='".$line['min']."',item_date_sold='".$line['max']."' WHERE item_id=".$line['item_id'].";\n");
echo("COMMIT;\n");
?>
</pre>
