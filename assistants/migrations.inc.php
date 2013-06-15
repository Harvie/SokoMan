<pre>
<h1>-- Remove all uncountable items</h1>
SELECT DISTINCT model_countable FROM model;
DELETE item FROM item LEFT JOIN barcode USING(barcode_id) LEFT JOIN model USING(model_id) WHERE model_countable=0;
UPDATE model SET model_countable=1;
<h1>-- populate barcode table</h1><?php
$data = $this->db->safe_query_fetch('SELECT model_id as id, model_barcode as bar FROM model;', false);
//print_r($data);
echo("UPDATE item SET barcode_id=model_id;\n");
echo("INSERT INTO barcode (barcode_id,model_id,barcode_name) VALUES\n");
foreach($data as $line) echo("('".$line['id']."','".$line['id']."',".$this->db->quote($line['bar'])."),\n");
?>
<h1>-- populate columns item_date_bought and item_date_sold</h1><?php
$data = $this->db->safe_query_fetch('SELECT item_id,MIN(item_valid_from) AS min,MAX(item_valid_from) AS max FROM item GROUP BY item_id;', false);
//print_r($data);
echo("START TRANSACTION;\n");
foreach($data as $line) echo("UPDATE item SET item_date_bought='".$line['min']."',item_date_sold='".$line['max']."' WHERE item_id=".$line['item_id'].";\n");
echo("COMMIT;\n");
?>
</pre>
