<?php
$queries += array(
	"Inventurni soupiska (Warehouse roster)"=>
		"select model_id, model_name, barcode_name, COUNT(item_serial), GROUP_CONCAT(item_serial separator ', ') from item LEFT JOIN barcode USING(barcode_id) LEFT JOIN model USING(model_id) WHERE item_valid_till=0 AND status_id=1 GROUP BY model_id ORDER BY model_name",
);
