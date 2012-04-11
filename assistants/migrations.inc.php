<pre><h1>populate column item_date_bought</h1><?php
$data = $this->db->safe_query_fetch('SELECT item_id,MIN(item_valid_from) AS item_valid_from FROM item GROUP BY item_id;');
//print_r($data);
echo("START TRANSACTION;\n");
foreach($data as $line) echo("UPDATE item SET item_date_bought='".$line['item_valid_from']."' WHERE item_id=".$line['item_id'].";\n");
echo("COMMIT;\n");
