<?php
$queries += array(
	"Skladová soupiska věcí se seriáky (Warehouse roster countable)"=>
		"select model_id, model_name, group_concat(item_serial separator ', ') from item LEFT JOIN model USING(model_id) WHERE item_valid_till=0 AND status_id=1 AND model_countable=1 GROUP BY model_id",
	"Skladová soupiska věcí na počet (Warehouse roster uncountable)"=>
		"select model_id, model_name, sum(item_quantity) from item LEFT JOIN model USING(model_id) WHERE item_valid_till=0 AND status_id=1 AND model_countable=0 GROUP BY model_id"
);
