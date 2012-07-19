<?php
$queries += array(
	"Skladová soupiska (Warehouse roster)"=>
		"select model_id, model_name, group_concat(item_serial separator ', ') from item LEFT JOIN model USING(model_id) WHERE item_valid_till=0 AND status_id=1 GROUP BY model_id",
);
