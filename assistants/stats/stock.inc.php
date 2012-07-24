<?php
$queries += array(
	"Ceník + Počet kusů skladem (PriceList + Stock)"
		=> 'SELECT room_id,room_name,model_id,model_name,model_barcode,model_price_out,COUNT(item_id),SUM(item_quantity)'.
		' FROM item LEFT JOIN barcode USING(barcode_id) LEFT JOIN model USING(model_id) LEFT JOIN room USING(room_id)'.
		' WHERE item_valid_till=0 AND status_id=1'.
		' GROUP BY model_id,room_id'.
		' ORDER BY room_id,model_id;'
);
