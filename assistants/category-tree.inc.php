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

function render_tree($tree, $pathindex='__PATH__') {
	if(!is_array($tree)) return '';
	$html='<menu>';
		foreach($tree as $name => $subtree) if($name != $pathindex) {
			$html.='<li><b>'.$name.'</b> <small>('.$subtree[$pathindex].')</small>'.render_tree($subtree).'</li>';
		}
	$html.='</menu>';
	return $html;
}
echo render_tree($tree);
//print_r($tree);
