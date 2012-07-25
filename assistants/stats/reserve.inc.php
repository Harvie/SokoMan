<?php
$queries += array(
	"Nakoupit: Došlo úplně (Urgent WishList)"
		=> 'SELECT model_id,model_name,barcode_name,model_reserve,model_reserve as item_quantity_to_buy'.
		' FROM model LEFT JOIN barcode USING(model_id)'.
		' WHERE model_reserve>0 AND NOT EXISTS'.
		' (SELECT item_id FROM item WHERE barcode.barcode_id=item.barcode_id AND status_id=1 AND item_quantity>0 AND item_valid_till=0)',
	"Nakoupit: Dochází (WishList)"
		=> 'SELECT model_id,model_name,barcode_name,model_reserve,'.
		' COUNT(item_id),SUM(item_quantity),model_reserve-SUM(item_quantity) as item_quantity_to_buy'.
		' FROM item LEFT JOIN barcode USING(barcode_id) LEFT JOIN model USING(model_id)'.
		' WHERE item_valid_till=0 AND status_id=1'.
		' GROUP BY model_id'.
		' HAVING SUM(item_quantity)<model_reserve'.
		' ORDER BY model_id;',
);
