<?php
$queries += array(
	"Nutno koupit"
		=> 'SELECT room_id,room_name,model_id,model_name,model_barcode,model_reserve,'.
		' COUNT(item_id),SUM(item_quantity),model_reserve-SUM(item_quantity) as item_quantity_to_buy'.
		' FROM item LEFT JOIN model USING(model_id) LEFT JOIN room USING(room_id)'.
		' WHERE item_valid_till=0 AND status_id=1'.
		' GROUP BY model_id,room_id'.
		' HAVING SUM(item_quantity)<model_reserve'.
		' ORDER BY room_id,model_id;',
);