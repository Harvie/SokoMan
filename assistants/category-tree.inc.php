<pre><?php
$result = $this->db->safe_query_fetch('SELECT * FROM category');

function addleaf(&$tree, $levels, $id, $category='') {
	if(!count($levels)) {
		$tree['__ID__'] = $id;
		return;
	}
	$current = array_shift($levels); //echo("$current ".count($levels)."");
	$category .= '/'.$current;
	if(!isset($tree[$current])) $tree[$current] = array();
	$tree[$current]['__PATH__'] = $category;
	//if(!count($levels)) $tree[$current]['__ID__'] = $id; //echo "($current $id)\n";
	addleaf($tree[$current], $levels, $id, $category);
}

$tree = array();
foreach($result as $row) {
	$row_parts = preg_split('/\//', $row['category_name']);
	//echo($row['category_name'].$row['category_id']."\n");
	addleaf($tree, $row_parts, $row['category_id']);
}

function render_tree($tree, $index_path='__PATH__', $index_id='__ID__') {
	if(!is_array($tree)) return '';
	$html='<menu>';
		foreach($tree as $name => $subtree) if($name != $index_path && $name != $index_id) {
			@$html.='<li><b>'.$name.'</b> <small>('.$subtree[$index_id].' => '.$subtree[$index_path].')</small>'.render_tree($subtree).'</li>';
		}
	$html.='</menu>';
	return $html;
}
echo render_tree($tree);
//print_r($tree);
