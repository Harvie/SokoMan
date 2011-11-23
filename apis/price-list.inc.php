<?php
$sql = 'SELECT model_name,model_price_out FROM model';
$result = $this->db->safe_query_fetch($sql);
foreach($result as $item) $items[$item['model_name']]=$item['model_price_out'];
die(json_encode($items));
