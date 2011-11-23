<?php
$queries = array(); //TODO: use build_query_select()!!!
foreach($stats as $stat) include("assistants/stats/$stat.inc.php");
foreach($queries as $description => $sql) {
	echo "<h2>$description</h2>";
	echo $this->html->render_item_table($result = $this->db->safe_query_fetch($sql));
}
