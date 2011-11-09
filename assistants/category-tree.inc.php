<pre><?php
$result = $this->db->safe_query_fetch('SELECT * FROM category');

function addleaf(&$tree, $levels, $category='') {
	if(!count($levels)) return;
	$current = array_shift($levels);
	$category .= '/'.$current;
	if(!isset($tree[$current])) $tree[$current] = array();
	$tree[$current]['__PATH__'] = $category;
	addleaf($tree[$current], $levels, $category);
}

$tree = array();
foreach($result as $row) {
	$row_parts = preg_split('/\//', $row['category_name']);
	addleaf($tree, $row_parts);
}

print_r($tree);
