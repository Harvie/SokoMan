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

require_once('Sklad_LMS-fake.class.php');
require_once('HTTP_Auth.class.php');
require_once('Locale.class.php');

/**
* Trida poskytuje vseobecne funkce pro generovani HTML kodu
*
* Tato trida by nemela sama nic vypisovat (vyjma chybovych a debugovacich hlasek)!
*
* @package  HTML
* @author   Tomas Mudrunka
*/
class HTML {
	function row($row) {
		$html='<tr>';
		foreach($row as $var) {
			if(trim($var) == '') $var = '&nbsp;';
			$html.="<td>$var</td>";
		}
		$html.='</tr>';
		return $html;
	}

	function table(&$table, $params='border=1') {
		$html="<table $params>";
		$header=true;
		foreach($table as $row) {
			if($header) {
				$html.=$this->row(array_keys($row));
				$header=false;
			}
			$html.=$this->row($row);
		}
		$html.='</table>';
		return $html;
	}

	function link($title='n/a', $link='#void', $internal=true) {
		if($internal) $link = $this->internal_url($link);
		return "<a href='$link'>".T($title)."</a>";
	}

	function img($src='#void', $title='img') {
		return "<img src='$src' alt='$title' title='$title' width=64 />";
	}

	function input($name=false, $value=false, $type='text', $placeholder=false, $options=false) {
		$html = "<input type='$type' ";
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

	function select($name, $selectbox, $default=false) {
		//echo('<pre>'); print_r($selectbox);
		$html = "<select name='$name'>";

		if($default) {
			$value=$default; $title=$selectbox[$value];
			$html .= "<option value='$value'>$value :: $title</option>";
			unset($selectbox[$value]);
		}
		foreach($selectbox as $value => $title) {
			$html .= "<option value='$value'>$value :: $title</option>";
		}
		$html .= "</select>";
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
class Sklad_HTML extends HTML {
	function header($title='') {
		$home = URL_HOME;
		$script = $_SERVER['SCRIPT_NAME'];
		$search = htmlspecialchars(@trim($_GET['q']));
		$message = strip_tags(@trim($_GET['message']),'<a><b><u><i>');
		$instance = INSTANCE_ID != '' ? '/'.INSTANCE_ID : '';
		return <<<EOF
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>SōkoMan$title</title>
</head>
<h1><a href="$script/">SōkoMan</a><small>$instance$title</small></h1>

<style type="text/css">
* { font-family: arial; }

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
	<menu class="menu">
		<li><a href="?logout">Logout</a></li>
		<li><a href="$script/">Home</a></li>
		<li><a href="#">Assistants</a>
			<menu>
				<li><a href="$script/assistant/stats">stats</a></li>
				<li><a href="$script/assistant/store">store</a></li>
				<li><a href="$script/assistant/store-single">store-single</a></li>
				<li><a href="$script/assistant/dispose">dispose</a></li>
				<li><a href="$script/assistant/sell">sell</a></li>
				<li>&darr;&darr; BETA &darr;&darr;</li>
			</menu>
		</li>
		<li><a href="#">List</a>
			<menu>
				<li><a href="$script/item">item</a></li>
				<li><a href="$script/model">model</a></li>
				<li><a href="$script/category">category</a></li>
				<li><a href="$script/producer">producer</a></li>
				<li><a href="$script/vendor">vendor</a></li>
				<li><a href="$script/room">room</a></li>
				<li><a href="$script/status">status</a></li>
			</menu>
		</li>
		<li><a href="#">New</a>
			<menu>
				<li><a href="$script/item/new">item</a></li>
				<li><a href="$script/model/new">model</a></li>
				<li><a href="$script/category/new">category</a></li>
				<li><a href="$script/producer/new">producer</a></li>
				<li><a href="$script/vendor/new">vendor</a></li>
				<li><a href="$script/room/new">room</a></li>
				<li><a href="$script/status/new">status</a></li>
			</menu>
		</li>
	</menu>

	<div style="float: right;">
		<form action="$script/assistant/go" method="GET" style="float: left;"><!-- TODO: Display only when go plugin available -->
			<input type="text" name="q" placeholder="smart id..." />
			<input type="submit" value="go" />
		</form>
		<form action="?" method="GET" style="float: left;">
			<input type="text" name="q" placeholder="regexp..." value="$search" />
			<input type="submit" value="filter" />
		</form>
		<!-- form action="$script/" method="GET">
			<input type="text" name="q" placeholder="regexp..." value="$search" />
			<input type="submit" value="search items" />
		</form -->
	</div>
</div>
<hr style="clear: both;" />
<div style="background-color:#FFDDDD;">
	<font color="red">$message</font>
</div>
EOF;
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
				$table[$id][$type.'_image']=$this->img($src, $table[$id][$column]);
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
			'status_id' => 'status_name',
		);
		foreach($table as $id => $row) {
			foreach($collapse as $link => $title)
				if(isset($table[$id][$link]) && isset($row[$title])) {
					$type = @array_shift(preg_split('/_/', $link));
					if($link != $title) unset($table[$id][$link]);
					$table[$id][$title]=$this->link($row[$title], $type.'/'.$row[$link].'/');
				}
		}
	}

	function table_sort(&$table) {
		$precedence = array('item_id', 'model_image', 'model_name','model_descript','category_name','status_name','room_name');
		$table_sorted = array();
		foreach($table as $id => $row) {
			$table_sorted[$id] = array();
			foreach($precedence as $column) if(isset($table[$id][$column])) {
				$table_sorted[$id][T($column)]=$table[$id][$column];
				unset($table[$id][$column]);
			}
			//$table_sorted[$id]=array_merge($table_sorted[$id],$table[$id]);
			foreach($table[$id] as $key => $val) $table_sorted[$id][T($key)] = $val; //array_merge with T() translating
		}
		$table = $table_sorted;
	}

	function render_item_table($table) {
		$this->table_add_images($table);
		$this->table_collapse($table);
		$this->table_sort($table);
		return $this->table($table);
	}

	function render_insert_form($class, $columns, $selectbox=array(), $current=false, $hidecols=false, $action=false, $multi_insert=true) {
		//echo('<pre>'); print_r($selectbox);
		//echo('<pre>'); print_r($current);
		$update = false;
		if(is_array($current)) {
			$update = true;
			$current = array_shift($current);
		}

		if(!is_array($hidecols)) $hidecols = array('item_author', 'item_valid_from', 'item_valid_till'); //TODO Autodetect

		$action = $action ? " action='$action'" : false;
		$html="<form$action method='POST'>";
		if($multi_insert) $html.='<div name="input_set" style="float:left; border:1px solid grey;">';
		//$html.=$this->input('table', $class, 'hidden');
		foreach($columns as $column)	{
			$html.=T($class).':<b>'.T($column['Field']).'</b>: ';
			$name="values[$class][".$column['Field'].'][]';
			$val = $update && isset($current[$column['Field']]) ? $current[$column['Field']] : false;
			switch(true) {
				case (preg_match('/auto_increment/', $column['Extra']) || in_array($column['Field'], $hidecols)):
					if(!$val) $val = '';
					$html.=$this->input($name, $val, 'hidden');
					$html.=$val.'(AUTO)';
					break;
				case isset($selectbox[$column['Field']]):
					$html.=$this->select($name,$selectbox[$column['Field']],$val);
					break;
				default:
					$html.=$this->input($name, $val);
					break;
			}
			$html.='<br />';
		}

		if($multi_insert) {
			//TODO, move to separate JS file
			$html.=<<<EOF
			</div>
			<span name="input_set_next"></span><br style="clear:both" />
			<script>
				function duplicate_element(what, where) {
					document.getElementsByName(where)[0].outerHTML =
						document.getElementsByName(what)[0].outerHTML
						+ document.getElementsByName(where)[0].outerHTML;
				}
			</script>
			<a href='#' onClick="duplicate_element('input_set', 'input_set_next')">+</a>
EOF;
		}

		$btn = is_array($current) ? 'UPDATE' : 'INSERT'; //TODO: $current may be set even when inserting...
		$html.=$this->input(false, $btn, 'submit');
		$html.='</form>';
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
		$this->lms = new Sklad_LMS();

		parent::__construct(
			DB_DSN, DB_USER, DB_PASS,
			array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") //Force UTF8 for MySQL
		);
	}

	function escape($str) {
		return preg_replace('(^.|.$)', '', $this->quote($str)); //TODO HACK
	}

	function build_query_select($class, $id=false, $limit=false, $offset=0, $where=false, $search=false, $history=false, $order=false, $suffix_id='_id') {
		//Configuration
		$join = array(
			'item'	=> array('model', 'category', 'producer', 'vendor', 'room', 'status'),
			'model'	=> array('category', 'producer')
		); //TODO Autodetect using foreign keys?
		$search_fields = array(
			'item'	=> array('item_id','item_serial','model_name','model_barcode','model_descript','producer_name','vendor_name')
		); //TODO Autodetect

		//Escaping
		$class = $this->escape($class);

		//SELECT
		$sql="SELECT * FROM $class\n";
		//JOIN
		if(isset($join[$class])) foreach($join[$class] as $j) $sql .= "LEFT JOIN $j USING($j$suffix_id)\n";
		//WHERE/REGEXP
		if($search) {
			$search = $this->quote($search);
			if(!isset($search_fields[$class])) die(trigger_error(T("Can't search in $class table yet :-("))); //TODO: post_redirect_get
			$sql_search = '';
			foreach($search_fields[$class] as $column) $sql_search .= "OR $column REGEXP $search ";
			$where[] = "FALSE $sql_search";
		}	elseif($id) $where[] = "$class$suffix_id = $id";
		if(!$history && $this->contains_history($class)) $where[] = $class.'_valid_till=0';

		if($where) $sql .= 'WHERE ('.implode(') AND (', $where).")\n";
		//ORDER
		if(!$order) $order = $class.$suffix_id;
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

	function translate_query_results($result) {
		$translate_cols = array('status_name', 'item_valid_till'); //TODO: Hardcoded
		foreach($result as $key => $row) {
			foreach($translate_cols as $col) if(isset($result[$key][$col])){
				$result[$key][$col] = T($result[$key][$col]);
			}
		}
		return $result;
	}

	function safe_query_fetch($sql, $fatal=true, $fetch_flags = PDO::FETCH_ASSOC, $translate=true) {
		$result = $this->safe_query($sql, $fatal)->fetchAll($fetch_flags);
		if($translate) $result = $this->translate_query_results($result);
		return $result;
	}


	function get_listing($class, $id=false, $limit=false, $offset=0, $where=false, $search=false, $history=false, $indexed=array(), $suffix_id='_id') {
		$sql = $this->build_query_select($class, $id, $limit, $offset, $where, $search, $history);
		$result = $this->safe_query_fetch($sql);
		if(!$result || !is_array($indexed)) return $result;

		foreach($result as $key => $row) $indexed[$row[$class.$suffix_id]]=$row;
		return $indexed;
	}

	function get_columns($class) {
		$class = $this->escape($class);
		$sql = "SHOW COLUMNS FROM $class;";
		return $this->safe_query_fetch($sql);
	}

	function columns_get_selectbox($columns, $class=false, $suffix_id='_id', $suffix_name='_name') {
		$selectbox=array();
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
		return array_filter($selectbox, 'ksort');
	}

	function map_unique($key, $value, $select, $table) { //TODO: Guess $select and $table if not passed
		$history = $this->contains_history($table) ? " AND ${table}_valid_till=0" : '';
		$value=$this->quote($value);
		$sql = "SELECT $select FROM $table WHERE $key=$value$history LIMIT 1;"; //TODO use build_query_select()!!!
		$result = $this->safe_query_fetch($sql);
		if(isset($result[0][$select])) return $result[0][$select]; else die(trigger_error(T('Record not found!'))); //TODO post_redirect_get...
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
					$sql .= $or.' '.$table.'_id='.$row[$table.'_id'];
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
							$row_quoted[$column] = $this->lms->get_authorized_user_id();
							//die($this->lms->get_authorized_user_id().'=USER');
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
		return $this->html->render_item_table($this->db->get_listing($class, $id, $limit, $offset, $where, $search, $history, false));
	}

	function render_form_add($class) {
		$columns = $this->db->get_columns($class);
		$selectbox = $this->db->columns_get_selectbox($columns, $class);
		return $this->html->render_insert_form($class, $columns, $selectbox);
	}

	function render_form_edit($class, $id) {
		$columns = $this->db->get_columns($class);
		$selectbox = $this->db->columns_get_selectbox($columns, $class);
		$current = $this->db->get_listing($class, $id, 1);
		return $this->html->render_insert_form($class, $columns, $selectbox, $current);
	}

	function render_single_record_details($class, $id) {
		$id_next = $id + 1;
		$id_prev = $id - 1 > 0 ? $id - 1 : 0;
		$get = $_SERVER['QUERY_STRING'] != '' ? '?'.$_SERVER['QUERY_STRING'] : '';
		$html='';
		$html.= $this->html->link('<<', "$class/$id_prev/");
		$html.= '-';
		$html.= $this->html->link('>>', "$class/$id_next/");
		$html.= '<br />';
		$html.= $this->html->link('edit', "$class/$id/edit/");
		if($this->db->contains_history($class)) $html.= ' ][ '.$this->html->link('history', "$class/$id/history/");
		return $html;
	}

	function render_listing_navigation($class, $id, $limit, $offset) {
		$offset_next = $offset + $limit;
		$offset_prev = $offset - $limit > 0 ? $offset - $limit : 0;
		$get = $_SERVER['QUERY_STRING'] != '' ? '?'.$_SERVER['QUERY_STRING'] : '';
		$html='';
		$html.= $this->html->link('<<', "$class/$id/$limit/$offset_prev/$get");
		$html.= '-';
		$html.= $this->html->link('>>', "$class/$id/$limit/$offset_next/$get");
		$html.= '<br />';
		$html.= $this->html->link('new', "$class/new/$get");
		return $html;
	}

	function render_listing_extensions($class, $id, $limit, $offset, $edit=false) {
		$html='';
		if(is_numeric($id)) {
			$html.=$this->render_single_record_details($class, $id);
		} else {
			$html.=$this->render_listing_navigation($class, '*', $limit, $offset);
		}
		if($edit)	{
			$html.= $this->render_form_edit($class, $id);
			$action = $_SERVER['SCRIPT_NAME']."/$class/$id/delete";
	    $html.= "<form action='$action' method='POST'>";
			$html.= $this->html->input(false, 'DELETE', 'submit');
			$html.= T('sure?').$this->html->input('sure', false, 'checkbox');
			$html.= '</form>';
			$action = $_SERVER['SCRIPT_NAME']."/$class/$id/image";
	    $html.= "<form action='$action' method='POST' enctype='multipart/form-data'>";
			$html.= $this->html->input('image', false, 'file', false, 'size="30"');
			$html.= $this->html->input(false, 'IMAGE', 'submit');
			$html.='</form>';
		}
		return $html;
	}

	function check_auth() {
		new HTTP_Auth('SkladovejSystem', true, array($this->db->lms,'check_auth'));
	}

	function post_redirect_get($location, $message='', $error=false) {
		$url_args = $message != '' ? '?message='.urlencode(T($message)) : '';
		$location = $this->html->internal_url($location).$url_args;
		header('Location: '.$location);
		if($error) trigger_error($message);
		$location=htmlspecialchars($location);
		die(
			"<meta http-equiv='refresh' content='0; url=$location'>".
			T($message)."<br />Location: <a href='$location'>$location</a>"
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

	function process_http_request_post($action=false, $class=false, $id=false, $force_redirect=false) {
		if($_SERVER['REQUEST_METHOD'] != 'POST') return;
		//echo('<pre>'); //DEBUG (maybe todo remove), HEADERS ALREADY SENT!!!!

		//SephirPOST:

		/* Tenhle foreach() prekopiruje promenne
		 * z:		$_POST['values'][$table][$column][$id];
		 * do:	$values[$table][$id][$column]
		 */
		if(isset($_POST['values'])) {
			$values=array();
			foreach($_POST['values'] as $table => $columns) {
				foreach($columns as $column => $ids) {
					foreach($ids as $id => $val) $values[$table][$id][$column] = $val;
				}
			}
			//die(print_r($values));
		}

		if($action) switch($action) {
			case 'new':
				$replace = false;
			case 'edit':
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
		$this->check_auth();

		@ini_set('magic_quotes_gpc' , 'off');
		if(get_magic_quotes_gpc()) {
			die(trigger_error("Error: magic_quotes_gpc needs to be disabled! F00K!"));
		}

		$PATH_INFO=@trim($_SERVER[PATH_INFO]);
		if($_SERVER['REQUEST_METHOD'] != 'POST') echo $this->html->header($PATH_INFO); //TODO tahle podminka naznacuje ze je v navrhu nejaka drobna nedomyslenost...


		//Sephirot:
		$PATH_CHUNKS = preg_split('/\//', $PATH_INFO);
		if(!isset($PATH_CHUNKS[1])) $PATH_CHUNKS[1]='';
		switch($PATH_CHUNKS[1]) {
			case 'test':	//test
				die('Tell me why you cry');
				break;
			case 'assistant': //assistant
				$PATH_CHUNKS[3] = isset($PATH_CHUNKS[3]) ? trim($PATH_CHUNKS[3]) : false;
				$assistant_vars['SUBPATH'] = array_slice($PATH_CHUNKS, 3);
				$assistant_vars['URL_INTERNAL'] = 'assistant/'.$PATH_CHUNKS[2];
				$assistant_vars['URL'] = $_SERVER['SCRIPT_NAME'].'/'.$assistant_vars['URL_INTERNAL'];
				echo $this->safe_include(DIR_ASSISTANTS,$PATH_CHUNKS[2],$assistant_vars);
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
								$limit	= (int) (isset($PATH_CHUNKS[3]) ? $PATH_CHUNKS[3] : '0');
								$offset	= (int) (isset($PATH_CHUNKS[4]) ? $PATH_CHUNKS[4] : '0');
								$where = false; //TODO get from URL
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

echo("<hr/>");
