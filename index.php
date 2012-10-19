<?php
/*
 * SkladovySystem - Storage management system compatible with LMS
 * Copyright (C) 2011  Tomas Mudrunka
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once('sklad.conf.php');
set_include_path(DIR_LIB.PATH_SEPARATOR.get_include_path());

require_once('Sklad_Auth.class/common.php');
require_once('HTTP_Auth.class.php');
require_once('Locale.class.php');
require_once('Barcode.class.php');
require_once('Fortune.php');

/**
* Trida poskytuje vseobecne funkce pro generovani HTML kodu
*
* Tato trida by nemela sama nic vypisovat (vyjma chybovych a debugovacich hlasek)!
*
* @package  HTML
* @author   Tomas Mudrunka
*/
class HTML {
	function row($row,$type=false,$class=false,$parameters='',$colspan=array(),$rowspan=array(),$break_after=array()) {
		$html = '';
		$class_br = $class ? " class='$class' " : '';
		$class = $class ? " class='$class tr_nobr' " : '';
		if($type) $html.="<$type>";
		$html.="<tr$class$parameters>";
		$td = $type == 'thead' ? 'th' : 'td';
		foreach($row as $id => $var) {
			$tdclass = " class='cell_$id'";
			if(trim($var) == '') $var = '&nbsp;';
			$rs = isset($rowspan[$id]) ? " rowspan='$rowspan[$id]'" : '';
			$cs = isset($colspan[$id]) ? " colspan='$colspan[$id]'" : '';
			$html.="<$td$rs$cs$tdclass>$var</$td>";
			if(in_array($id,$break_after,true)) $html.='</tr>'."<tr$class_br$parameters>";
		}
		$html.='</tr>';
		if($type) $html.="</$type>";
		return $html;
	}

	function table(&$table,$colspan=array(),$rowspan=array(),$break_after=array(),$parity_class=array('tr_odd','tr_even'),$params='border=1',$row_classes_field='_row_classes') {
		$html="<table $params>";
		$header=true;
		$even=false;
		foreach($table as $row) {
			$params = isset($row[$row_classes_field]) ? $row[$row_classes_field] : '';
			unset($row[$row_classes_field]);
			if($header) {
				$keys = array(); foreach($row as $key => $val) $keys[$key]=$key;
				$html.=$this->row(T($keys),'thead',false,'',$colspan,$rowspan,$break_after);
				$header=false;
			}
			$class = $parity_class ? $parity_class[$even] : false;
			$html.=$this->row($row,false,$class.$params,'',$colspan,$rowspan,$break_after);
			$even = !$even;
		}
		$html.='</table>';
		return $html;
	}

	function link($title='n/a', $link='#void', $internal=true, $translate=true) {
		if($internal && (!isset($link[0]) || $link[0] != '#')) $link = $this->internal_url($link);
		if($translate) $title = T($title);
		return "<a href='$link'>".$title."</a>";
	}

	function img($src='#void', $title='img', $options='height=64') {
		if(isset($_GET['noimgs'])) return "<span title='$title'>".basename($src).'</span>';
		$options = $options ? " $options" : '';
		return "<img src='$src' alt='$title' title='$title'$options; />";
	}

	function img_link($src, $link='#void', $title='img_link', $internal=true, $translate=true, $options='height=64') {
		return $this->link($this->img($src,$title,$options),$link,$internal,$translate);
	}

	function textarea($name=false, $value='', $placeholder=false, $options=false, $prefix='') {
		$html = T($prefix)."<textarea";
		if($name) $html.= " name='$name'";
		if($options) $html.= " $options";
		if($placeholder) $html.= " placeholder='$placeholder'";
		$html .= ">$value</textarea>";
		return $html;
	}

	function input($name=false, $value=false, $type='text', $placeholder=false, $options=false, $prefix='') {
		if($type == 'textarea') return $this->textarea($name, $value, $placeholder, $options, $prefix);
		if($type == 'select') return $this->select($name, $placeholder, $value, $prefix);
		//select(vendor, $selectbox ,false, prefix:);
		$html = T($prefix)."<input type='$type' ";
		if($name) $html.= "name='$name' ";
		if(!is_bool($value)) {
			if($type == 'submit') $value = T($value);
			$html.= "value='$value' ";
		}
		if($options) $html.= "$options ";
		if($placeholder) $html.= "placeholder='$placeholder' ";
		$html .= '/>';
		return $html;
	}

	function form($action=false, $method=false, $inputs, $options=false) {
		$action = $action ? " action='$action'" : '';
		$method = $method ? " method='$method'" : '';
		$options = $options ? " $options" : '';
		$html = "<form$action$method$options>";
		foreach($inputs as $input) $html .= call_user_func_array(array($this,'input'), $input);
		$html .= "</form>";
		return $html;
	}

	function select($name, $selectbox, $default=false, $prefix='') {
		//echo('<pre>'); print_r($selectbox);
		$html = T($prefix)."<select name='$name'>";

		if(!is_bool($default)) {
			$value=$default; $title=$selectbox[$value];
			$html .= "<option value='$value'>$title (($value))</option>";
			unset($selectbox[$value]);
		}
		foreach($selectbox as $value => $title) {
			$html .= "<option value='$value'>$title (($value))</option>";
		}
		$html .= "</select>";
		return $html;
	}

	function ul($items,$tag=ul,$head='',$class=false) {
		$class = $class ? " class='$class'" : '';
		$html = "$head<$tag$class>";
		foreach($items as $key => $value) {
			$html .= '<li>';
			if(is_numeric($key)) {
				$html .= $value;
			} else {
				$html .= $this->link($key,$value);
			}
			$html .= '</li>';
		}
		$html .= "</$tag>";
		return $html;
	}

	function div($html, $options) {
		$options = $options ? " $options" : '';
		return "<div$options>$html</div>";
	}

	function favicon($url='/favicon.ico') {
		return '<link rel="shortcut icon" href="'.$url.'" /><link href="'.$url.'" rel="icon" type="image/gif" />';

	}

	function head($title=false,$charset='UTF-8',$more='') {
		$title = $title ? "\n<title>$title</title>" : '';
		$html= '<head>';
		$html.= '<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'" />'.$title.$more;
		$html.= $this->favicon(dirname($_SERVER['SCRIPT_NAME']).'/favicon.ico');
		$html.= '</head>';
		return $html;
	}
}

/**
* Trida poskytuje podpurne funkce pro generovani HTML kodu specificke pro sklad
*
* Tato trida by nemela sama nic vypisovat (vyjma chybovych a debugovacich hlasek)!
*
* @package  Sklad_HTML
* @author   Tomas Mudrunka
*/
class Sklad_HTML extends HTML { //TODO: Split into few more methods
	function header($title='', $user=array(), $headerhtml) {
		$home = URL_HOME;
		$script = $_SERVER['SCRIPT_NAME'];
		$search = htmlspecialchars(@trim($_GET['q']));
		$message = strip_tags(@trim($_GET['message']),'<a><b><u><i><br>');
		$fortune = fortune();
		$instance = INSTANCE_ID != '' ? '/'.INSTANCE_ID : '';
		$user_id = htmlspecialchars($user['id']);
		$user_gid = htmlspecialchars($user['gid']);
		$user_name = htmlspecialchars($user['name']);
		$time = date('r');
		//$title = T($title); //TODO

		$html = $this->head("SōkoMan$title");
		$html .= <<<EOF
<h1 style="display: inline;"><a href="$script/">SōkoMan</a><small>$instance$title</small></h1>
<div style="float:right; text-align:right;">
	Logged in as <b>$user_name</b> [UID: <b>$user_id</b>; GID: <b>$user_gid</b>]<br />
	Page loaded at $time
</div>

<style type="text/css">
* { font-family: arial; }
td,body { background-color: white; border: orange; }
.tr_nobr td { border-top: 3px solid orange; }
table { background-color: orange; border: orange; }
td textarea { width:100%; height:100%; }
a, a img { text-decoration:none; color: darkblue; border:none; }
li a, a:hover { text-decoration:underline; }
.tr_even td { background-color: lemonchiffon; }
.tr_nobr.tr_even td:first-child { border-left: 5px solid orange; }
.tr_nobr.tr_odd td:first-child { border-left: 5px solid white; }
.item_status_stored td { font-weight:bold; }
.item_status_deleted td { text-decoration:line-through; }
.item_status_destroyed td { font-style:italic; }
.floating_barcode { margin: 5px; }
.disabled { color: grey; }
/* table, table * { table-layout:fixed; width:100%; overflow:hidden; word-wrap:break-word; } */
/* td { position:absolute; } */
/* .cell_model_name { float:left; } */

.menu li {
	float: left;
	padding: 0.2em;
}

.menu * li {
	float: none;
}

.menu * menu {
	position: absolute;
	padding: 0.2em;
}

.menu, .menu * menu {
	list-style: none;
}

.menu * menu {
	border: 1px solid orange;
	display: none;
	margin: 0;
}

.menu li:hover menu, .menu li:hover {
	display: block;
	background-color: yellow;
}

</style>

<div>
EOF;

	$assistants=array();
	foreach(scandir(DIR_ASSISTANTS) as $item) {
		if($item == '.' || $item == '..') continue;
		$item = preg_replace('/\.inc\.php$/','',$item,-1,$count);
		if($count) $assistants[$item] = "assistant/$item";
	}

	$tables=array('item','barcode','model','category','producer','vendor','room','status','location');

	foreach($tables as $table) {
		$listable[$table] = $table;
		$insertable[$table] = "$table/new";
	}

	$html .= $this->ul(array(
		'Home' => '',
		'Logout' => '?logout',
		0 => $this->ul($assistants,'menu',$this->link('Assistants','#')),
		1 => $this->ul($insertable,'menu',$this->link('New','#')),
		2 => $this->ul($listable,'menu',$this->link('List','#'))
	),'menu', '', 'menu');

	$html .= '<div style="float: right;">';

	/*
	//TODO: Do we really need this?
	$html .= $this->form("$script/api/go", 'GET', array(
		array('q','','text','smart id...', 'autofocus'),
		array(false,'go','submit')
	), 'style="float: left;"');
	*/

	$html .= $this->form('?', 'GET', array(
		array('q',$search,'text','regexp...'),
		array(false,'filter','submit')
	), 'style="float: left;"');

	$html .= $this->form("$script/item", 'GET', array(
		array('q',$search,'text','regexp...','autofocus'),
		array(false,'search','submit')
	), 'style="float: left;"');

	$html .= '</div>';

	$html .= <<<EOF
</div>
<hr style="clear: both;" />
<div style="background-color:#FFDDDD;">
	<font color="red">$headerhtml $message</font>
</div>
<div style="text-align:right; color:darkgreen;">
$fortune
</div>
EOF;

	return $html;
	}

	function internal_url($link) {
		return $_SERVER['SCRIPT_NAME'].'/'.$link;
	}

	function table_add_images(&$table) {
		$image = array('model_id');
		foreach($table as $id => $row) {
			foreach($image as $column) if(isset($table[$id][$column])) {
				$type = @array_shift(preg_split('/_/', $column));
				$src=URL_IMAGES."/$type/".$table[$id][$column].'.jpg';
				$table[$id][$type.'_image']=$this->img_link($src, $src, $table[$id][$column], false, false);
			}
		}
	}

	function render_barcode($barcode,$opts=false) {
		return $this->img_link($this->internal_url("barcodeimg/$barcode"),$this->internal_url("barcodeimg/$barcode"),$barcode,false,false,$opts);
	}

	function table_add_barcodes(&$table) {
		$image = array('barcode_name', 'model_barcode', 'item_serial');
		foreach($table as $id => $row) {
			foreach($image as $column) if(isset($table[$id][$column])) {
				$table[$id][$column]=$this->render_barcode($table[$id][$column]);
			}
		}
	}

	function table_add_row_classes(&$table, $class_col='_row_classes') {
		$image = array('status_name' => ' item_status_');
		foreach($table as $id => $row) {
			foreach($image as $column => $param) if(isset($table[$id][$column])) {
				@$table[$id][$class_col] .= $param.$table[$id][$column];
			}
		}
	}

	function table_add_relations(&$table, $class, $suffix_relations='_relations') {
		$where_url = '%d/?where[%c]==%v';
		$insert_url = '%d/new?insert[%c]=%v';
		$relations = array( //TODO: Autodetect??? //TODO: Add [edit] link to all classes
			'model' => array(
				'model_id' => array(array('item',$where_url),array('barcode',$where_url),array('edit','model/%v/edit/'),array('barcode',$insert_url,false,'add barcode')),
				'model_barcode' => array(array('store','assistant/%d?barcode=%v')),
				'barcode_name' => array(array('store','assistant/%d?barcode=%v')),
				'model_name' => array(array('google','http://google.com/search?q=%v')) //TODO: add manufacturer to google query
			),
			'barcode' => array(
				'model_id' => array(array('model',$where_url)),
				'barcode_id' => array(array('item',$where_url),array('edit','barcode/%v/edit/')),
				'barcode_name' => array(array('store','assistant/%d?barcode=%v')),
			),
			'item' => array(
				'item_serial' => array(array('dispose','assistant/%d?serial=%v','in_stock'),array('sell','assistant/%d?serial=%v','in_stock')),
				'item_id' => array(array('edit','item/%v/edit/'))
			),
			'category' 	=> array('category_id' => array(array('item',$where_url), array('model',$where_url))),
			'producer' => array('producer_id' => array(array('item',$where_url), array('model',$where_url))),
			'vendor' => array('vendor_id' => array(array('item',$where_url))),
			'room' => array('room_id' => array(array('item',$where_url))),
			'status' => array('status_id' => array(array('item',$where_url))),
			'location' => array(
				'location_id' => array(array('item',$where_url),array('edit','location/%v/edit/')),
				'location_name' => array(array('smokeping','http://tartarus.brevnov.czf/cgi-bin/smokeping.cgi?filter=%v'))
			)
		);
		$relations_conditions=array(
			'in_stock' => 'return(@$table[$id]["status_name"] == "stored");',
			'not_sold' => 'return(@$table[$id]["status_name"] != "sold");',
			'not_sold_or_disposed' => 'return(@$table[$id]["status_name"] != "sold" && @$table[$id]["status_name"] != "disposed");'
		);
		foreach($table as $id => $row) {
			foreach($row as $column => $value) {
				if(isset($relations[$class][$column])) {
					foreach($relations[$class][$column] as $destination) {
						$destination_url = str_replace(
							array('%d','%c','%v'),
							array(urlencode($destination[0]),urlencode($column),urlencode($value)),
							$destination[1]
						);
						if(isset($destination[2]) && isset($relations_conditions[$destination[2]])) {
							//$condition = $relations_conditions[$destination[2]]($table,$id);
							if(!eval($relations_conditions[$destination[2]])) continue;
						}
						$destination_title = isset($destination[3]) ? $destination[3] : $destination[0];
						@$table[$id][$class.$suffix_relations] .= $this->link($destination_title, $destination_url, !preg_match('/http/', $destination_url) ).',';
					}
				}
			}
		}
	}

	function table_collapse(&$table) {
		$collapse = array(
			'item_id' => 'item_id',
			'model_id' => 'model_name',
			'category_id' => 'category_name',
			'producer_id' => 'producer_name',
			'vendor_id' => 'vendor_name',
			'room_id' => 'room_name',
			'location_id' => 'location_name',
			'status_id' => 'status_name',
			'item_author' => 'item_author_backend',
			'item_customer' => 'item_customer',
			'lock_id' => 'lock_name',
		);

		foreach($table as $id => $row) {
			foreach($collapse as $link => $title)
				if(isset($table[$id][$link]) && isset($row[$title])) {
					$type = @array_shift(preg_split('/_/', $link));
					if($link != $title) unset($table[$id][$link]);
					switch($link) { //TODO: Move to array for easy configuration
						case 'item_author':
						case 'item_customer':
							$table[$id][$title]=$this->link($row[$title], "?where[$link]==".$row[$link], false);
							break;
						default:
							$table[$id][$title]=$this->link($row[$title], $type.'/'.$row[$link].'/');
							break;
					}
				}
		}
	}

	function table_sort(&$table) {
		$precedence = array('item_id', 'model_image', 'model_name','model_descript','category_name','status_name','room_name','item_quantity','item_price_in','item_price_out','model_price_in','model_price_out','item_relations','model_relations');
		$table_sorted = array();
		foreach($table as $id => $row) {
			$table_sorted[$id] = array();
			foreach($precedence as $column) if(isset($table[$id][$column])) {
				$table_sorted[$id][$column]=$table[$id][$column];
				unset($table[$id][$column]);
			}
			$table_sorted[$id]=array_merge($table_sorted[$id],$table[$id]);
			//foreach($table[$id] as $key => $val) $table_sorted[$id][T($key)] = $val; //array_merge with T() translating
		}
		$table = $table_sorted;
	}

	function table_hide_columns(&$table, $class) { //TODO: Move to build_query_select() !!! :-)))
		$fields_hide = array(
			'model' => array('barcode_name'),
			'barcode' => array('model_price_in','model_price_out','model_reserve','producer_name','producer_note','model_eshop_hide','category_name','model_countable','model_descript'),
			'item' => array('model_descript','model_price_in','model_price_out','barcode_name','model_barcode','model_countable','model_reserve','model_eshop_hide','room_descript','room_author','producer_name','producer_note','vendor_note','location_author','location_gps','location_description')
		);
		//print_r($table); die();
		if(isset($fields_hide[$class])) foreach($table as $id => $row) {
			foreach($fields_hide[$class] as $field) unset($table[$id][$field]);
		}
	}

	function render_item_table($table,$class=false) {

		$cellspan = array(
			'break_after' => array(
				'item' => array('category_name'),
				'model'=> array('model_descript')
			),
			'rowspan' => array(
				'item' => array('model_image'=>2,'item_id'=>2),
				'model'=> array('model_image'=>2)
			),
			'colspan' => array(
				'item' => array('model_name'=>6,'category_name'=>'100%'),
				'model'=> array('model_name'=>4,'model_descript'=>'100%')
			)
		);

		foreach(array_keys($cellspan) as $vari)
			$$vari = isset($cellspan[$vari][$class]) ? $cellspan[$vari][$class] : array();

		if(empty($table)) return '<h3>'.T('holy primordial emptiness is all you can find here...').'</h3><br />';
		$this->table_add_row_classes($table);
		$this->table_add_images($table);
		if($class) $this->table_add_relations($table,$class);
		$this->table_add_barcodes($table);
		$this->table_collapse($table);
		if($class) $this->table_hide_columns($table,$class);
		$this->table_sort($table);
		return $this->table($table,$colspan,$rowspan,$break_after);
	}

	function render_insert_inputs($class,$columns,$selectbox,$current,$hidecols,$update) {
		$textarea = array(
			'item' => array('item_note'),
			'model' => array('model_descript')
		);
		$html = '<table>';
		$even=false;
		foreach($columns as $column)	{
			$html.='<tr class="'. ($even ? 'tr_even' : 'tr_odd') .'"><td>'.T($class).':<b>'.T($column['Field']).'</b>:&nbsp;</td><td>';
			$even = !$even;
			$name="values[$class][".$column['Field'].'][]';
			$val = $update && isset($current[$column['Field']]) ? $current[$column['Field']] : false;
			switch(true) {
				case (preg_match('/auto_increment/', $column['Extra']) || in_array($column['Field'], $hidecols)):
					if(is_bool($val) && !$val) $val = '';
					$html.=$this->input($name, $val, 'hidden');
					$html.='<span class="disabled"><i>[AUTO]</i> '.$val.'</span>';
					break;
				case isset($selectbox[$column['Field']]):
					$html.=$this->select($name,$selectbox[$column['Field']],$val);
					break;
				case isset($textarea[$class]) && in_array($column['Field'],$textarea[$class]):
					$html.=$this->input($name, $val, 'textarea');
					break;
				default:
					$html.=$this->input($name, $val);
					break;
			}
			$html.='</td></tr>';
		}
		$html.='</table>';
		return $html;
	}

	function render_insert_form_multi($array) {
		$html = '';
		$head=false;

		foreach($array as $key => $args) {
			$parts=array('inputs');
			if(!$head) { $head = true;
				$parts[]='head';
			}
			if(!isset($array[$key+1])) {
				$parts[]='foot';
				$hr = '';
			} else $hr = '<hr />';
			//$args[] = false;
			$args[] = $parts;

			$html .= call_user_func_array(array($this, 'render_insert_form'), $args);
			$html .= $hr;
		}
		return $html;
	}

	function render_insert_form($class, $columns, $selectbox=array(), $current=false, $hidecols=false, $action=false, $multi_insert=true, $parts=false) {
		$html = '';
		//print_r($parts);
		//echo('<pre>'); print_r($selectbox);
		//echo('<pre>'); print_r($current);
		$update = false;
		$current_new=array();
		if(is_array($current)) {
			$update = true;
			$current_new = array_merge($current_new,array_shift($current));
		}
		if(isset($_GET['insert']) && is_array($_GET['insert'])) {
			$update = true;
			$current_new = array_merge($current_new,$_GET['insert']);
		}

		if(!is_array($hidecols)) $hidecols = array();
		$hidecols = array_merge($hidecols, array('item_author', 'item_valid_from', 'item_valid_till')); //TODO Autodetect

		if(!is_array($parts) || in_array('head', $parts)) {
			$action = $action ? " action='$action'" : false;
			$html.="<form$action method='POST'>"; //TODO: use $this->form()
			$html.='<span><div name="input_set" style="float:left; border:1px solid grey; padding: 1px; margin: 1px;">';
		}

		if(!is_array($parts) || in_array('inputs', $parts))
			$html.=$this->render_insert_inputs($class,$columns,$selectbox,$current_new,$hidecols,$update);

		if(!is_array($parts) || in_array('foot', $parts)) {
			$html .= '</div></span><br style="clear:both" />';
			if($multi_insert) { //TODO, move to separate JS file
				$html.=<<<EOF
				<script>
					function duplicate_element(what, where) {
						var node = document.getElementsByName(what)[0];
						node.parentNode.appendChild(node.cloneNode(true));
					}
				</script>
				<a href='#' onClick="duplicate_element('input_set')">+</a>
EOF;
			}

			$btn = count($current_new)>0 ? 'UPDATE' : 'INSERT'; //TODO: $current may be set even when inserting...
			$html.=$this->input(false, $btn, 'submit');
			$html.='</form>';
		}
		return $html;
	}
}

/**
* Trida poskytuje rozhrani k databazi skladu
*
* @package  Sklad_DB
* @author   Tomas Mudrunka
*/
class Sklad_DB extends PDO {
	function __construct() {
		$this->auth = new Sklad_Auth();

		parent::__construct(
			DB_DSN, DB_USER, DB_PASS,
			array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") //Force UTF8 for MySQL
		);
	}

	function escape($str) {
		return preg_replace('(^.|.$)', '', $this->quote($str)); //TODO HACK
	}

	function quote_identifier($str) {
		return '`'.$this->escape($str).'`'; //TODO HACK
	}

	function build_query_select($class, $id=false, $limit=false, $offset=0, $where=false, $search=false, $history=false, $order=false, $suffix_id='_id') {
		//Configuration
		$join = array(
			'barcode'	=> array('model', 'category', 'producer'),
			'item'	=> array('barcode', 'model', 'category', 'producer', 'vendor', 'room', 'location', 'status'),
			'model'	=> array('category', 'producer')
		); //TODO Autodetect using foreign keys?
		$join2 = array(
			'model' => array('barcode'=>'model_id')
		);
		$fields_search = array(
			'item'	=> array('item_id','item_serial','model_name','barcode_name','model_descript','producer_name','vendor_name'),
			'model' => array('model_id','model_name','barcode_name','model_descript','producer_name')
		); //TODO Autodetect
		$group_concat = array(
			'model' => array('barcode_name'=>'model_id')
		);

		//Init
		if(is_array($where)) foreach($where as $key => $value) $where[$key] = $key.' '.$value; //TODO: escape SQLi!!!

		//Escaping
		$class = $this->escape($class);

		//GROUP_CONCAT
		$group_concat_query = '';
		$group_by = '';
		if(isset($group_concat[$class])) foreach($group_concat[$class] as $gc => $gb) {
			$group_concat_query .= ",group_concat($gc separator ', ')";
			$group_by .= "GROUP BY $gb\n";
		}

		//SELECT
		$sql="SELECT *$group_concat_query FROM `$class`\n";
		//JOIN
		if(isset($join[$class])) foreach($join[$class] as $j) $sql .= "LEFT JOIN `$j` USING($j$suffix_id)\n";
		if(isset($join2[$class])) foreach($join2[$class] as $j => $c) $sql .= "LEFT JOIN `$j` USING($c)\n";
		//WHERE/REGEXP
		if($search) {
			$search = $this->quote($search);
			if(!isset($fields_search[$class])) die(trigger_error(T("Can't search in $class table yet :-("))); //TODO: post_redirect_get
			$sql_search = '';
			foreach($fields_search[$class] as $column) $sql_search .= "OR $column REGEXP $search ";
			$where[] = "FALSE $sql_search";
		}	elseif($id) $where[] = "$class$suffix_id = $id";
		if(!$history && $this->contains_history($class)) $where[] = $class.'_valid_till=0';

		if($where) $sql .= 'WHERE ('.implode(') AND (', $where).")\n";

		//GROUP BY
		$sql.=$group_by;

		//ORDER
		if(!$order) $order = $class.$suffix_id.' DESC';
		if($this->contains_history($class)) $order .= ",${class}_valid_from DESC";
		$sql .= "ORDER BY $order\n";
		//LIMIT/OFFSET
		if($limit) {
			$limit = $this->escape((int)$limit);
			$offset = $this->escape((int)$offset);
			$sql .= "LIMIT $offset,$limit\n";
		}

		return $sql;
	}

	function safe_query($sql, $fatal=true) {
		$result = $this->query($sql);
		if(!$result) {
			$error = $this->errorInfo();
			trigger_error("<font color=red><b>QUERY FAILED ($error[0],$error[1]): </b>$error[2]<br /><br /><b>QUERY:</b>\n<pre>$sql</pre></font>");
			if($fatal) die();
		}
		return $result;
	}

	function translate_query_results(&$result) {
		$translate_cols = array('item_valid_till'); //TODO: Hardcoded
		foreach($result as $key => $row) {
			foreach($translate_cols as $col) if(isset($result[$key][$col])){
				$result[$key][$col] = T($result[$key][$col]);
			}
		}
	}

	function load_backend_data_to_query_results(&$result,$suffix_backend='_backend') {
		$translate_cols = array(
			'item_author' => 'return($this->auth->get_username_by_id($result[$key][$col]));'
		); //TODO: Hardcoded
		foreach($result as $key => $row) {
			foreach($translate_cols as $col => $backend) if(isset($result[$key][$col])){
				$result[$key][$col.$suffix_backend] = eval($backend);
			}
		}
	}

	function safe_query_fetch($sql, $fatal=true, $fetch_flags = PDO::FETCH_ASSOC, $translate=true) {
		$result = $this->safe_query($sql, $fatal);
		if(!$fatal && !$result) return $result;
		$result = $result->fetchAll($fetch_flags);
		$this->load_backend_data_to_query_results($result);
		if($translate) $this->translate_query_results($result);
		return $result;
	}

	function get_listing($class, $id=false, $limit=false, $offset=0, $where=false, $search=false, $history=false, $indexed=array(), $suffix_id='_id') {
		$sql = $this->build_query_select($class, $id, $limit, $offset, $where, $search, $history);
		$result = $this->safe_query_fetch($sql);
		if(!$result || !is_array($indexed)) return $result;

		foreach($result as $key => $row) $indexed[$row[$class.$suffix_id]]=$row;
		return $indexed;
	}

	function get_columns($class,$disable_cols=array()) { //TODO: Not sure if compatible with non-MySQL DBs
		$class = $this->escape($class);
		$sql = "SHOW COLUMNS FROM `$class`;";
		$columns = $this->safe_query_fetch($sql);
		/*foreach($columns as $colk => $col) foreach($col as $key => $val) {
			if(in_array($col['Field'],$disable_cols)) $columns[$colk]['Extra']='auto_increment';
		}*/
		return $columns;
	}

	function columns_get_selectbox($columns, $class=false, $suffix_id='_id', $suffix_name='_name') {
		$selectbox=array( //TODO: Hardcoded...
			'model_countable' => array(0 => 'no', 1 => 'yes'),
			'model_eshop_hide' => array(0 => 'no', 1 => 'yes'),
			'vendor_id' => array('COMPULSORY' => 'select...')
		);
		foreach($columns as $column) {
			if($column['Field'] == 'user_id') continue; //TODO HACK Blacklist: tabulka nemusi obsahovat *_name!!! momentalne se to tyka jen tabulky user (a item - u ty to nevadi)!
			if($class && $column['Field'] == $class.$suffix_id) continue;
			if(!preg_match('/'.$suffix_id.'$/', $column['Field'])) continue;
			$table=preg_replace('/'.$suffix_id.'$/','',$column['Field']);

			$history = $this->contains_history($table) ? " WHERE ${table}_valid_till=0" : '';
			$sql = "SELECT $table$suffix_id, $table$suffix_name FROM $table$history;"; //TODO use build_query_select()!!!
			$result = $this->safe_query_fetch($sql, false);
			if(!$result) continue;
			foreach($result as $row) $selectbox[$table.$suffix_id][$row[$table.$suffix_id]]=$row[$table.$suffix_name];
		}
		//echo('<pre>'); print_r($selectbox);
		//return $selectbox;
		//return array_filter($selectbox, 'ksort');
		return array_filter($selectbox, 'natcasesort');
		//array_multisort($selectbox); return $selectbox;
	}

	function map_unique($key, $value, $select, $table, $fatal=true) { //TODO: Guess $select and $table if not passed
		$history = $this->contains_history($table) ? " AND ${table}_valid_till=0" : '';
		$value=$this->quote($value);
		$sql = "SELECT $select FROM $table WHERE $key=$value$history LIMIT 1;"; //TODO use build_query_select()!!!
		$result = $this->safe_query_fetch($sql);
		if(isset($result[0][$select])) return $result[0][$select]; else if($fatal) die(trigger_error(T('Record not found!'))); //TODO post_redirect_get...
	}

	function contains_history($table) {
		$history_tables = array('item'); //TODO Autodetect
		return in_array($table, $history_tables);
	}

	function build_query_insert($table, $values, $replace=true, $suffix_id='_id') {
		//Init
		$history = $this->contains_history($table);

		//Escaping
		$table = $this->escape($table);

		//Get list of POSTed columns
		$columns_array = array_map(array($this,'escape'), array_keys($values[0]));
		$columns = implode(',',$columns_array);

		//Build query
		$sql = '';
		//echo('<pre>'); die(print_r($values));

		if($history) {
			$history_update=false;	foreach($values as $row) if(is_numeric($row[$table.'_id'])) $history_update=true;
			if($history_update) {
				$sql .= "UPDATE $table";
				$sql .= " SET ${table}_valid_till=NOW()";
				$sql .= " WHERE ${table}_valid_till=0 AND (";
				$or = '';
				foreach($values as $row) {
					$sql .= $or.' '.$table.'_id='.$this->quote($row[$table.'_id']);
					$or = ' OR';
				}
				$sql .= " );\n\n";
				$replace = false;
			}
		}

		//Insert into table (columns)
		$sql .= "INSERT INTO $table ($columns) VALUES ";

		//Values (a,b,c),(d,e,f)
		$comma='';
		foreach($values as $row) {
			$row_quoted = array_map(array($this,'quote'), $row); //Check
			if($history) {
				foreach($row as $column => $value) {
					switch($column) {
						case $table.'_valid_from':
							$row_quoted[$column] = 'NOW()';
							break;
						case $table.'_valid_till':
							$row_quoted[$column] = '0';
							break;
						case $table.'_author':
							$row_quoted[$column] = $this->auth->get_user_id();
							//die($this->auth->get_user_id().'=USER');
							break;
					}
				}
			}
			$sql .= $comma.'('.implode(',',$row_quoted).')';
			$comma = ',';
		}

		//On duplicate key
		if($replace) {
			foreach($columns_array as $col) {
				if($col == $table.'_id' || $col == $table.'_valid_till') continue;
				$on_duplicate[] = "$col=VALUES($col)";
			}
			$sql .= "\nON DUPLICATE KEY UPDATE ".implode(',', $on_duplicate);
		}

		//Terminate
		$sql .= ';';
		return $sql;
	}

	function insert_or_update($table, $values, $replace=true) {
		$sql = $this->build_query_insert($table, $values, $replace);
		$this->safe_query($sql);
		return $this->lastInsertId();
	}

	function insert_or_update_multitab($values, $replace=true) {
		$last=false;
		foreach($values as $table => $rows) $last = $this->insert_or_update($table, $rows, $replace);
		return $last;
	}

	function delete($table, $id, $suffix_id='_id') {
		if($this->contains_history($table)) return false;
		$key = $this->escape($table.$suffix_id);
		$table = $this->escape($table);
		$id = $this->quote($id);
		return $this->safe_query("DELETE FROM $table WHERE $key = $id LIMIT 1;");
	}
}

/**
* Trida poskytuje high-level rozhrani k databazi skladu
*
* @package  Sklad_DB_Abstract
* @author   Tomas Mudrunka
*/
class Sklad_DB_Abstract extends Sklad_DB {
	//TODO Code
}

/**
* Trida implementuje uzivatelske rozhrani skladu
*
* Example usage:
* $sklad = new Sklad_UI();
* $sklad->process_http_request();
*
* @package  Sklad_UI
* @author   Tomas Mudrunka
*/
class Sklad_UI {
	function __construct() {
		$this->db = new Sklad_DB();
		$this->html = new Sklad_HTML();
	}

	function render_items($class, $id=false, $limit=false, $offset=0, $where=false, $search=false, $history=false) {
		return $this->html->render_item_table($this->db->get_listing($class, $id, $limit, $offset, $where, $search, $history, false),$class);
	}

	function render_form_add($class) {
		$columns = $this->db->get_columns($class);
		$selectbox = $this->db->columns_get_selectbox($columns, $class);
		return $this->html->render_insert_form($class, $columns, $selectbox);
	}

	function render_form_edit($class, $id, $multi_insert) {
		$columns = $this->db->get_columns($class);
		$selectbox = $this->db->columns_get_selectbox($columns, $class);
		$current = $this->db->get_listing($class, $id, 1);
		return $this->html->render_insert_form($class, $columns, $selectbox, $current, false, false, $multi_insert);
	}

	function render_single_record_details($class, $id, $barcode=true) {
		$id_next = $id + 1;
		$id_prev = $id - 1 > 0 ? $id - 1 : 0;
		$get = $_SERVER['QUERY_STRING'] != '' ? '?'.$_SERVER['QUERY_STRING'] : '';
		$html='';
		$html.= $this->html->link('<<', "$class/$id_prev/");
		$html.= '-';
		$html.= $this->html->link('>>', "$class/$id_next/");
		$html.= '&nbsp;&nbsp;&nbsp;';
		$barcode && $html.='<span style="float:right;" class="floating_barcode">'.$this->html->render_barcode(BARCODE_PREFIX.strtoupper("$class/$id")).'</span>';
		$html.= $this->html->link('edit', "$class/$id/edit/");
		if($this->db->contains_history($class)) $html.= ' ][ '.$this->html->link('history', "$class/$id/history/");
		return $html;
	}

	function render_listing_navigation($class, $id, $limit, $offset) {
		$offset_next = $offset + $limit;
		$offset_prev = $offset - $limit > 0 ? $offset - $limit : 0;
		$get = $_SERVER['QUERY_STRING'] != '' ? '?'.$_SERVER['QUERY_STRING'] : '';
		$moreget = isset($get[0]) ? '&' : '?';
		$html='';
		$html.= $this->html->link('<<', "$class/$id/$limit/$offset_prev/$get");
		$html.= '-';
		$html.= $this->html->link('[*]', "$class/$id/0/0/$get$moreget".'noimgs');
		$html.= '-';
		$html.= $this->html->link('>>', "$class/$id/$limit/$offset_next/$get");
		$html.= '&nbsp;&nbsp;&nbsp;';
		$html.= $this->html->link('new', "$class/new/$get");
		return $html;
	}

	function render_listing_extensions($class, $id, $limit, $offset, $edit=false, $barcode=true) {
		$html='';
		if(is_numeric($id)) {
			$html.=$this->render_single_record_details($class, $id, $barcode);
		} else {
			$html.=$this->render_listing_navigation($class, '*', $limit, $offset);
		}
		if($edit && $barcode)	{
			$html.= $this->render_form_edit($class, $id, false);
			$action = $_SERVER['SCRIPT_NAME']."/$class/$id/delete";
			$html.=$this->html->form($action,'POST',array(
				array(false,'DELETE','submit'),
				array('sure', false, 'checkbox', false, false, 'sure?')
			));
			$action = $_SERVER['SCRIPT_NAME']."/$class/$id/image";
			$html.=$this->html->form($action,'POST',array(
				array('image', false, 'file', false, 'size="30"'),
				array(false, 'IMAGE', 'submit')
			), "enctype='multipart/form-data'");
		}
		return $html;
	}

	function check_auth() {
		new HTTP_Auth('WareHouse ['.BACKEND_AUTH.']', true, array($this->db->auth,'check_auth'));
	}

	function post_redirect_get($location, $message='', $error=false, $translate=true) {
		$messaget = $translate ? T($message) : $message;
		$url_args = $messaget != '' ? '?message='.urlencode($messaget) : '';
		$location = $this->html->internal_url($location).$url_args;
		header('Location: '.$location);
		if($error) trigger_error($message);
		$location=htmlspecialchars($location);
		die(
			"<meta http-equiv='refresh' content='0; url=$location'>".
			$messaget."<br />Location: <a href='$location'>$location</a>"
		);
	}

	function safe_include($dir,$name,$vars=array(),$ext='.inc.php') {
		if(preg_match('/[^a-zA-Z0-9-]/',$name)) $this->post_redirect_get('', 'SAFE INCLUDE: Securityfuck.', true);
		$filename="$dir/$name$ext";
		if(!is_file($filename)) $this->post_redirect_get('', 'SAFE INCLUDE: Fuckfound.', true);
		foreach($vars as $var => $val) $$var=$val;
		ob_start();
		include($filename);
		$out=ob_get_contents();
		ob_end_clean();
		return $out;
	}

	function check_input_validity($field, $value='', $ruleset=0) {
		$rules = array(0 => array(
			'model_barcode' => '/./',
			'barcode_name' => '/./',
			'item_serial' => '/./',
			'vendor_id' => '/^[0-9]*$/'
		));
		if(isset($rules[$ruleset][$field]) && !preg_match($rules[$ruleset][$field], trim($value))) return false;
		return true;
	}

	function check_locks() {
		$result = $this->db->safe_query_fetch("SELECT * FROM `lock`;");
		if(!empty($result)) {
			echo T('There are locks:').' '.$this->html->render_item_table($result);
			$this->post_redirect_get('', 'There are locks!', true);
		}
	}

	function process_http_request_post($action=false, $class=false, $id=false, $force_redirect=false) {
		if($_SERVER['REQUEST_METHOD'] != 'POST') return;
		//echo('<pre>'); //DEBUG (maybe todo remove), HEADERS ALREADY SENT!!!!

		//SephirPOST:

		/* Tenhle foreach() prekopiruje promenne
		 * z:		$_POST['values'][$table][$column][$id];
		 * do:	$values[$table][$id][$column]
		 */
		if(isset($_POST['values'])) {
			$this->check_locks();
			$values=array();
			foreach($_POST['values'] as $table => $columns) {
				foreach($columns as $column => $ids) {
					foreach($ids as $id => $val) {
						$values[$table][$id][$column] = trim($val);
						if(!$this->check_input_validity($column,$val)) {
							$message = "Spatny vstup: $column [$id] = \"$val\"; ". //XSS
								$this->html->link('GO BACK', 'javascript:history.back()', false, false);
			        $this->post_redirect_get('', $message, false, false);
						}
					}
				}
			}
			//die(print_r($values));
		}

		if($action) switch($action) {
			case 'new':
				$replace = false;
			case 'edit':
				$this->check_locks();
				if(!isset($replace)) $replace = true;
				$table = $class ? $class : 'item';
				//print_r($values); //debug
				$last = $this->db->insert_or_update_multitab($values, $replace);
				$last = $force_redirect ? $force_redirect."?last=$last" : "$table/$last/";
				$next = "$table/new/";
				$message = $force_redirect ? '' : 'Hotovo. Další záznam přidáte '.$this->html->link('zde', $next).'.';
				$this->post_redirect_get($last, $message);
				break;
			case 'delete':
				$this->check_locks();
				if(!isset($_POST['sure']) || !$_POST['sure']) $this->post_redirect_get("$class/$id/edit", 'Sure user expected :-)');
				$this->db->delete($class, $id) || $this->post_redirect_get("$class/$id/edit", "V tabulce $class jentak neco mazat nebudes chlapecku :-P");
				$this->post_redirect_get("$class", "Neco (pravdepodobne /$class/$id) bylo asi smazano. Fnuk :'-(");
				break;
			case 'image':
				$image_classes = array('model'); //TODO, use this more widely across the code
				if(!in_array($class, $image_classes)) $this->post_redirect_get("$class/$id/edit", "Nekdo nechce k DB Tride '$class' prirazovat obrazky!");
				$image_destination = DIR_IMAGES."/$class/$id.jpg";
				if($_FILES['image']['name'] == '') $this->post_redirect_get("$class/$id/edit", 'Everything has to be called somehow!', true);
				if(move_uploaded_file($_FILES['image']['tmp_name'], $image_destination)) {
					chmod ($image_destination, 0664);
					$this->post_redirect_get("$class/$id", 'Image has been upbloated successfully :)');
				} else $this->post_redirect_get("$class/$id/edit", 'File upload failed :(', true);
				break;
			default:
				$this->post_redirect_get('', 'Nothin\' to do here my cutie :-*');
				break;
		}

		die('POSTed pyčo!');
	}

	function process_http_request() {
		$listing_limit_classes = array('item','model');

		$this->check_auth();

		@ini_set('magic_quotes_gpc' , 'off');
		if(get_magic_quotes_gpc()) {
			die(trigger_error("Error: magic_quotes_gpc needs to be disabled! F00K!"));
		}

		$PATH_INFO=@trim($_SERVER[PATH_INFO]);
		if($PATH_INFO == '' || $PATH_INFO == '/') $PATH_INFO = FRONTEND_PAGE_WELCOME;
		$PATH_CHUNKS = preg_split('/\//', $PATH_INFO);
		//Sephirot:
		if(!isset($PATH_CHUNKS[1])) $PATH_CHUNKS[1]='';
		if($_SERVER['REQUEST_METHOD'] != 'POST' && $PATH_CHUNKS[1]!='barcodeimg' && $PATH_CHUNKS[1]!='api') { //TODO: tyhle podminky naznacujou, ze je v navrhu nejaka drobna nedomyslenost...
			$result = $this->db->safe_query_fetch("SELECT * FROM `lock`;");
			$headerhtml = !empty($result) ? T('There are locks:').' '.$this->html->render_item_table($result) : '';
			echo $this->html->header($PATH_INFO,$this->db->auth->get_user(),$headerhtml);
		}
		switch($PATH_CHUNKS[1]) { //TODO: Move some branches to plugins if possible
			case 'test':	//test
				die('Tell me why you cry');
				break;
			case 'assistant': case 'api': //assistant|api
				$incdirs = array(
					'assistant'	=> DIR_ASSISTANTS,
					'api'	=> DIR_APIS
				);
				$PATH_CHUNKS[3] = isset($PATH_CHUNKS[3]) ? trim($PATH_CHUNKS[3]) : false;
				$assistant_vars['SUBPATH'] = array_slice($PATH_CHUNKS, 3);
				$assistant_vars['URL_INTERNAL'] = 'assistant/'.$PATH_CHUNKS[2];
				$assistant_vars['URL'] = $_SERVER['SCRIPT_NAME'].'/'.$assistant_vars['URL_INTERNAL'];
				$assistant_vars['ASSISTANT'] = $PATH_CHUNKS[2];
				echo $this->safe_include($incdirs[$PATH_CHUNKS[1]],$PATH_CHUNKS[2],$assistant_vars);
				break;
			case 'barcodeimg': //barcode
				Barcode::download_barcode(implode('/',array_slice($PATH_CHUNKS, 2)));
				break;
			default:	//?
				$search	= (isset($_GET['q']) && trim($_GET['q']) != '') ? trim($_GET['q']) : false;
				$class	= (isset($PATH_CHUNKS[1]) && $PATH_CHUNKS[1] != '') ? $PATH_CHUNKS[1] : 'item';
				if(!isset($PATH_CHUNKS[2])) $PATH_CHUNKS[2]='';
				switch($PATH_CHUNKS[2]) {
					case 'new':	//?/new
						$this->process_http_request_post($PATH_CHUNKS[2], $class);
						echo $this->render_form_add($class);
						break;
					default:	//?/?
						$id	= (isset($PATH_CHUNKS[2]) && is_numeric($PATH_CHUNKS[2]) ? (int) $PATH_CHUNKS[2] : false);
						if(!isset($PATH_CHUNKS[3])) $PATH_CHUNKS[3]='';
						$edit=false;
						switch($PATH_CHUNKS[3]) {
							case 'edit':	//?/?/edit
							case 'image':	//?/?/image
							case 'delete':	//?/?/delete
								$this->process_http_request_post($PATH_CHUNKS[3], $class, $id);
								$edit=true;
							default:	//?/?/?
								$history = $PATH_CHUNKS[3] == 'history' ? true : false;
								$limit	= is_numeric($PATH_CHUNKS[3]) ? (int) $PATH_CHUNKS[3] : (in_array($class, $listing_limit_classes) ? FRONTEND_LISTING_LIMIT : 0);
								$offset	= isset($PATH_CHUNKS[4]) ? (int) $PATH_CHUNKS[4] : 0;
								$where = @is_array($_GET['where']) ? $_GET['where'] : false;
								echo $this->render_listing_extensions($class, $id, $limit, $offset, $edit, false);
								echo $this->render_items($class, $id, $limit, $offset, $where, $search, $history);
								echo $this->render_listing_extensions($class, $id, $limit, $offset, $edit);
								//print_r(array("<pre>",$_SERVER));
								break;
						}
						break;
				}
				break;
		}
	}
}

$sklad = new Sklad_UI();
$sklad->process_http_request();

echo('<br style="clear:both;" /><hr />');
