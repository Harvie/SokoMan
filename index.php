<?php
/*
 * SkladovySystem - Storage management system compatible with LMS
 * Copyright (C) 2011  Thomas Mudrunka
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
require_once('Sklad_LMS-fake.class.php');
require_once('HTTP_Auth.class.php');

class Sklad_HTML {
	function header_print($title='') {
		$home = URL_HOME;
		$script = $_SERVER['SCRIPT_NAME'];
		$search = @trim($_GET['q']);
		echo <<<EOF
<h1><a href="$script/">SystémSklad</a><small>$title</small></h1>
<div>
	<menu>
		<li><a href="?logout">Logout</a></li>
		<li><a href="$script/">Home</a></li>
	</menu>
	<form action="?" method="GET">
		<input type="text" name="q" placeholder="regexp..." value="$search" />
		<input type="submit" value="filter" />
	</form>
	<!-- form action="$script/" method="GET">
		<input type="text" name="q" placeholder="regexp..." value="$search" />
		<input type="submit" value="search items" />
	</form -->
</div>
EOF;
	}

	function row_print($row) {
		echo('<tr>');
		foreach($row as $var) {
			if(trim($var) == '') $var = '&nbsp;';
			echo("<td>$var</td>");
		}
		echo('</tr>');
	}

	function table_print(&$table, $params='border=1') {
		echo("<table $params>");
		$header=true;
		foreach($table as $row) {
			if($header) {
				$this->row_print(array_keys($row));
				$header=false;
			}
			$this->row_print($row);
		}
		echo('</table>');
	}

	function link($title='n/a', $link='#void', $internal=true) {
		if($internal) $link = $_SERVER['SCRIPT_NAME'].'/'.$link;
		return "<a href='$link'>$title</a>";
	}

	function img($src='#void', $title='img') {
		return "<img src='$src' alt='$title' title='$title' width=64 />";
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
				if(isset($table[$id][$link])) {
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
				$table_sorted[$id][$column]=$table[$id][$column];
				unset($table[$id][$column]);
			}
			$table_sorted[$id]=array_merge($table_sorted[$id],$table[$id]);
		}
		$table = $table_sorted;
	}

	function print_item_table($table) {
		$this->table_add_images($table);
		$this->table_collapse($table);
		$this->table_sort($table);
		return $this->table_print($table);
	}

	function input($name=false, $value=false, $type='text', $placeholder=false, $options=false) {
		$html = "<input type='$type' ";
		if($name) $html.= "name='$name' ";
		if(!is_bool($value)) $html.= "value='$value' ";
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

	function print_insert_form($class, $columns, $selectbox=array(), $current=false, $multi_insert=true) {
		//echo('<pre>'); print_r($selectbox);
		//echo('<pre>'); print_r($current);
		$update = false;
		if(is_array($current)) {
			$update = true;
			$current = array_shift($current);
		}

		echo('<form method="POST">');
		if($multi_insert) echo('<div name="input_set" style="float:left; border:1px solid grey;">');
		echo $this->input('table', $class, 'hidden');
		foreach($columns as $column)	{
			echo($column['Field'].': ');
			$name='value:'.$column['Field'].'[]';
			switch(true) {
				case preg_match('/auto_increment/', $column['Extra']):
					$val = $update ? $current[$column['Field']] : ''; //opakuje se (skoro) zbytecne
					echo $this->input($name, $val, 'hidden');
					echo($val.'(AUTO)');
					break;
				case isset($selectbox[$column['Field']]):
					$val = $update ? $current[$column['Field']] : false;
					echo $this->select($name,$selectbox[$column['Field']],$val); //opakuje se
					break;
				default:
					$val = $update ? $current[$column['Field']] : false; //opakuje se
					echo $this->input($name, $val);
					break;
			}
			echo('<br />');
		}

		if($multi_insert) {
			//TODO, move to separate JS file
			echo <<<EOF
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

		$btn = is_array($current) ? 'UPDATE' : 'INSERT';
		echo($this->input(false, $btn, 'submit'));
		echo('</form>');
	}
}

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

	function build_query_select($class, $id=false, $limit=false, $offset=0, $search=false, $id_suffix='_id') {
		$class = $this->escape($class);
		$join = array(
			'item'	=> array('model', 'category', 'producer', 'vendor', 'room', 'status'),
			'model'	=> array('category', 'producer')
		);
		$search_fields = array(
			'item'	=> array('item_id','model_name','model_barcode','model_descript','producer_name','vendor_name')
		);
		$sql="SELECT * FROM $class\n";
		if(isset($join[$class])) foreach($join[$class] as $j) $sql .= "LEFT JOIN $j USING($j$id_suffix)\n";
		if($search) {
			$search = $this->quote($search);
			if(!isset($search_fields[$class])) {
				trigger_error("Ve tride $class zatim vyhledavat nemozno :-(");
				die();
			}
			$sql .= 'WHERE FALSE ';
			foreach($search_fields[$class] as $column) $sql .= "OR $column REGEXP $search ";
		}	elseif($id) $sql .= "WHERE $class$id_suffix = $id\n";
		if($limit) {
			$limit = $this->escape((int)$limit);
			$offset = $this->escape((int)$offset);
			$sql .= "LIMIT $offset,$limit\n";
		}
		return $sql;
	}

	function safe_query($sql) {
		$result = $this->query($sql);
		if(!$result) {
			trigger_error('<font color=red><b>QUERY FAILED:</b><pre>'.$sql.'</pre></font>');
			die();
		}
		return $result;
	}

	function get_listing($class, $id=false, $limit=false, $offset=0, $search=false, $indexed=array(), $suffix_id='_id') {
		$sql = $this->build_query_select($class, $id, $limit, $offset, $search);
		$result = $this->safe_query($sql)->fetchAll(PDO::FETCH_ASSOC);
		if(!$result || !is_array($indexed)) return $result;

		foreach($result as $key => $row) $indexed[$row[$class.$suffix_id]]=$row;
		return $indexed;
	}

	function get_columns($class) {
		$class = $this->escape($class);
		$sql = "SHOW COLUMNS FROM $class;";
		return $this->safe_query($sql)->fetchAll(PDO::FETCH_ASSOC);
	}

	function columns_get_selectbox($columns, $class=false, $suffix_id='_id', $suffix_name='_name') {
		$selectbox=array();
		foreach($columns as $column) {
			if($class && $column['Field'] == $class.$suffix_id) continue;
			if(!preg_match('/'.$suffix_id.'$/', $column['Field'])) continue;
			$table=preg_replace('/'.$suffix_id.'$/','',$column['Field']);
			$sql = "SELECT $table$suffix_id, $table$suffix_name FROM $table;";
			$result=$this->safe_query($sql)->fetchAll(PDO::FETCH_ASSOC);
			foreach($result as $row) $selectbox[$table.$suffix_id][$row[$table.$suffix_id]]=$row[$table.$suffix_name];
		}
		//echo('<pre>'); print_r($selectbox);
		return $selectbox;
	}

	function build_query_insert($table, $values, $replace=true, $suffix_id='_id') {
		$table = $this->escape($table);

		//Get list of POSTed columns
		$columns = implode(',',array_map(array($this,'escape'), array_keys($values[0])));

		//Insert into table (columns)
		$sql = 'INSERT';
		if($replace) $sql = 'REPLACE';
		$sql .= " INTO $table ($columns) VALUES ";

		//Values (a,b,c),(d,e,f)
		$comma='';
		foreach($values as $row) {
			$sql .= $comma.'('.implode(',',array_map(array($this,'quote'), $row)).')';
			$comma = ',';
		}

		//Terminate
		$sql .= ';';
		return $sql;
	}

	function insert_or_update($table, $values) {
		$sql = $this->build_query_insert($table, $values);
		$this->safe_query($sql);
		return $this->lastInsertId();
	}

	function delete($table, $id, $suffix_id='_id') {
		$key = $this->escape($table.$suffix_id);
		$table = $this->escape($table);
		$id = $this->quote($id);
		return $this->safe_query("DELETE FROM $table WHERE $key = $id LIMIT 1;");
	}
}

class Sklad_UI {
	function __construct() {
		$this->db = new Sklad_DB();
		$this->html = new Sklad_HTML();
	}

	function show_items($class, $id=false, $limit=false, $offset=0, $search=false) {
		$this->html->print_item_table($this->db->get_listing($class, $id, $limit, $offset, $search));
	}

	function show_form_add($class) {
		$columns = $this->db->get_columns($class);
		$selectbox = $this->db->columns_get_selectbox($columns, $class);
		$this->html->print_insert_form($class, $columns, $selectbox);
	}

	function show_form_edit($class, $id) {
		$columns = $this->db->get_columns($class);
		$selectbox = $this->db->columns_get_selectbox($columns, $class);
		$current = $this->db->get_listing($class, $id);
		$this->html->print_insert_form($class, $columns, $selectbox, $current);
	}

	function show_single_record_details($class, $id) {
		$id_next = $id + 1;
		$id_prev = $id - 1 > 0 ? $id - 1 : 0;
		$get = $_SERVER['QUERY_STRING'] != '' ? '?'.$_SERVER['QUERY_STRING'] : '';
		echo $this->html->link('<<', "$class/$id_prev/");
		echo '-';
		echo $this->html->link('>>', "$class/$id_next/");
		echo ('<br />');
		echo $this->html->link('edit', "$class/$id/edit/");
	}

	function show_listing_navigation($class, $id, $limit, $offset) {
		$offset_next = $offset + $limit;
		$offset_prev = $offset - $limit > 0 ? $offset - $limit : 0;
		$get = $_SERVER['QUERY_STRING'] != '' ? '?'.$_SERVER['QUERY_STRING'] : '';
		echo $this->html->link('<<', "$class/$id/$limit/$offset_prev/$get");
		echo '-';
		echo $this->html->link('>>', "$class/$id/$limit/$offset_next/$get");
		echo ('<br />');
		echo $this->html->link('new', "$class/new/$get");
	}

	function show_listing_extensions($class, $id, $limit, $offset, $edit=false) {
		if(is_numeric($id)) {
			$this->show_single_record_details($class, $id);
		} else {
			$this->show_listing_navigation($class, '*', $limit, $offset);
		}
		if($edit)	{
			echo('<br />TODO UPDATE FORM!<br />');
			$this->show_form_edit($class, $id);
			$action = $_SERVER['SCRIPT_NAME']."/$class/$id/delete";
	    echo("<form action='$action' method='POST'>");
			echo $this->html->input(false, 'DELETE', 'submit');
			echo 'sure?'.$this->html->input('sure', false, 'checkbox');
			echo('</form>');
			$action = $_SERVER['SCRIPT_NAME']."/$class/$id/image";
	    echo("<form action='$action' method='POST' enctype='multipart/form-data'>");
			echo $this->html->input('image', false, 'file', false, 'size="30"');
			echo $this->html->input(false, 'IMAGE', 'submit');
			echo('</form>');
		}
	}

	function check_auth() {
		new HTTP_Auth('SkladovejSystem', true, array($this->db->lms,'check_auth'));
	}

	function post_redirect_get($last, $next) { //TODO prepracovat, tohle je uplna picovina...
		//header('Location: '.$_SERVER['REQUEST_URI']); //TODO redirect (need templating system or ob_start() first!!!)
		echo 'Hotovo. Poslední vložený záznam naleznete '.$this->html->link('zde', $last).'.<br />'.
		'Další záznam přidáte '.$this->html->link('zde', $next).'.';
		die();
	}

	function process_http_request_post($action=false, $class=false, $id=false) {
		if($_SERVER['REQUEST_METHOD'] != 'POST') return;
		echo('<pre>'); //DEBUG (maybe todo remove)

		//SephirPOST:
		$values=array();
		foreach($_POST as $key => $value) {
			$name = preg_split('/:/',$key);
			if(isset($name[0])) switch($name[0]) {
				case 'value':
					foreach($value as $id => $val) $values[$id][$name[1]]=$value[$id];
					break;
				default:
					break;
			}
		}

		if($action) switch($action) {
			case 'new':
			case 'edit':
				if(!isset($_POST['table'])) die(trigger_error("Jest nutno specifikovat tabulku voe!"));
				$table=$_POST['table'];
				//print_r($values); //debug
				$last = $this->db->insert_or_update($table, $values);
				$this->post_redirect_get("$table/$last/", "$table/new/");
				break;
			case 'delete':
				if(!isset($_POST['sure']) || !$_POST['sure']) die(trigger_error('Sure user expected :-)'));
				//$this->db->delete($class, $id);
				die('Neco asi bylo smazano. Fnuk :\'-('); //TODO REDIRECT
				break;
			case 'image':
				$image_classes = array('model'); //TODO, use this more widely across the code
				if(!in_array($class, $image_classes)) die(trigger_error("Nekdo nechce k DB Tride '$class' prirazovat obrazky!"));
				$image_destination = DIR_IMAGES."/$class/$id.jpg";
				if($_FILES['image']['name'] == '') die(trigger_error('Kazde neco se musi nejak jmenovat!'));
				if(move_uploaded_file($_FILES['image']['tmp_name'], $image_destination)) {
	        chmod ($image_destination, 0664);
  	      die('Obrazek se naladoval :)'); //TODO REDIRECT
        } else die(trigger_error('Soubor se nenahral :('));
				break;
			default:
				trigger_error('Nothin\' to do here my cutie :-*');
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
		$this->html->header_print($PATH_INFO);


		//Sephirot:
		$PATH_CHUNKS = preg_split('/\//', $PATH_INFO);
		if(!isset($PATH_CHUNKS[1])) $PATH_CHUNKS[1]='';
		switch($PATH_CHUNKS[1]) {
			case 'test':	//test
				die('Tell me why you cry');
				break;
			default:	//?
				$search	= (isset($_GET['q']) && trim($_GET['q']) != '') ? trim($_GET['q']) : false;
				$class	= (isset($PATH_CHUNKS[1]) && $PATH_CHUNKS[1] != '') ? $PATH_CHUNKS[1] : 'item';
				if(!isset($PATH_CHUNKS[2])) $PATH_CHUNKS[2]='';
				switch($PATH_CHUNKS[2]) {
					case 'new':	//?/new
						$this->process_http_request_post($PATH_CHUNKS[2], $class);
						$this->show_form_add($class);
						break;
					default:	//?/?
						$id	= (isset($PATH_CHUNKS[2]) && is_numeric($PATH_CHUNKS[2]) ? (int) $PATH_CHUNKS[2] : false);
						if(!isset($PATH_CHUNKS[3])) $PATH_CHUNKS[3]='';
						$edit=false;
						switch($PATH_CHUNKS[3]) {
							case 'edit':	//?/?/edit
							case 'image':	//?/image
							case 'delete':	//?/delete
								$this->process_http_request_post($PATH_CHUNKS[3], $class, $id);
								$edit=true;
							default:	//?/?/?
								$limit	= (int) (isset($PATH_CHUNKS[3]) ? $PATH_CHUNKS[3] : '0');
								$offset	= (int) (isset($PATH_CHUNKS[4]) ? $PATH_CHUNKS[4] : '0');
								$this->show_items($class, $id, $limit, $offset, $search);
								$this->show_listing_extensions($class, $id, $limit, $offset, $edit);
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
