<?php
$queries += array(
	"Inventurni soupiska věcí se seriáky (Warehouse roster countable)"=>
		"select model_id, model_name, model_barcode, count(item_serial), group_concat(item_serial separator ', ') from item LEFT JOIN model USING(model_id) WHERE item_valid_till=0 AND status_id=1 AND model_countable=1 GROUP BY model_id ORDER BY model_name",
	"Inventurni soupiska věcí na počet (Warehouse roster uncountable)"=>
		"select model_id, model_name, model_barcode, sum(item_quantity) from item LEFT JOIN model USING(model_id) WHERE item_valid_till=0 AND status_id=1 AND model_countable=0 GROUP BY model_id ORDER BY model_name"
);
