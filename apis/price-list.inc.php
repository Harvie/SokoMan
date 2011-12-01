<?php
$sql = 'SELECT model_name,model_price_out,producer_name'.
	' FROM model LEFT JOIN producer USING(producer_id)'.
	' WHERE model_price_out > 0 AND category_id > 0';
$result = $this->db->safe_query_fetch($sql);
die(json_encode($result));
