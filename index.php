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
require_once('Sklad_LMS-fake.class.php');
require_once('HTTP_Auth.class.php');

/**
* Trida poskytuje podpurne funkce pro generovani HTML kodu specificke pro sklad
*
* Tato trida by nemela sama nic vypisovat (vyjma chybovych a debugovacich hlasek)!
*
* @package  Sklad_HTML
* @author   Tomas Mudrunka
*/
class Sklad_HTML {
	function header($title='') {
		$home = URL_HOME;
		$script = $_SERVER['SCRIPT_NAME'];
		$search = @trim($_GET['q']);
		return <<<EOF
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
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

	function render_item_table($table) {
		$this->table_add_images($table);
		$this->table_collapse($table);
		$this->table_sort($table);
		return $this->table($table);
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

	function render_insert_form($class, $columns, $selectbox=array(), $current=false, $multi_insert=true) {
		//echo('<pre>'); print_r($selectbox);
		//echo('<pre>'); print_r($current);
		$update = false;
		if(is_array($current)) {
			$update = true;
			$current = array_shift($current);
		}

		$html='<form method="POST">';
		if($multi_insert) $html.='<div name="input_set" style="float:left; border:1px solid grey;">';
		//$html.=$this->input('table', $class, 'hidden');
		foreach($columns as $column)	{
			$html.=$class.':<b>'.$column['Field'].'</b>: ';
			$name="values[$class][".$column['Field'].'][]';
			$val = $update ? $current[$column['Field']] : false;
			switch(true) {
				case preg_match('/auto_increment/', $column['Extra']):
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

		$btn = is_array($current) ? 'UPDATE' : 'INSERT';
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

	function insert_or_update_multitab($values) {
		$last=false;
		foreach($values as $table => $rows) $last = $this->insert_or_update($table, $rows);
		return $last;
	}

	function delete($table, $id, $suffix_id='_id') {
		$key = $this->escape($table.$suffix_id);
		$table = $this->escape($table);
		$id = $this->quote($id);
		return $this->safe_query("DELETE FROM $table WHERE $key = $id LIMIT 1;");
	}
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

	function render_items($class, $id=false, $limit=false, $offset=0, $search=false) {
		return $this->html->render_item_table($this->db->get_listing($class, $id, $limit, $offset, $search));
	}

	function render_form_add($class) {
		$columns = $this->db->get_columns($class);
		$selectbox = $this->db->columns_get_selectbox($columns, $class);
		return $this->html->render_insert_form($class, $columns, $selectbox);
	}

	function render_form_edit($class, $id) {
		$columns = $this->db->get_columns($class);
		$selectbox = $this->db->columns_get_selectbox($columns, $class);
		$current = $this->db->get_listing($class, $id);
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
			$html.='<br />TODO UPDATE FORM!<br />';
			$html.= $this->render_form_edit($class, $id);
			$action = $_SERVER['SCRIPT_NAME']."/$class/$id/delete";
	    $html.= "<form action='$action' method='POST'>";
			$html.= $this->html->input(false, 'DELETE', 'submit');
			$html.= 'sure?'.$this->html->input('sure', false, 'checkbox');
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

	function post_redirect_get($last, $next) { //TODO prepracovat, tohle je uplna picovina...
		//header('Location: '.$_SERVER['REQUEST_URI']); //TODO redirect (need templating system or ob_start() first!!!)
		echo 'Hotovo. Poslední vložený záznam naleznete '.$this->html->link('zde', $last).'.<br />'.
		'Další záznam přidáte '.$this->html->link('zde', $next).'.';
		die();
	}

	function safe_include($dir,$name,$ext='.inc.php') {
		if(preg_match('/[^a-zA-Z0-9-]/',$name)) die(trigger_error('SAFE INCLUDE: Securityfuck.'));
		$filename="$dir/$name$ext";
		if(!is_file($filename)) die(trigger_error('SAFE INCLUDE: Fuckfound.'));
		ob_start();
		include($filename);
		$out=ob_get_contents();
		ob_end_clean();
		return $out;
	}

	function process_http_request_post($action=false, $class=false, $id=false) {
		if($_SERVER['REQUEST_METHOD'] != 'POST') return;
		echo('<pre>'); //DEBUG (maybe todo remove)

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
			case 'edit':
				//if(!isset($_POST['table'])) die(trigger_error("Jest nutno specifikovat tabulku voe!"));
				//$table=$_POST['table'];
				$table='item';
				//print_r($values); //debug
				$last = $this->db->insert_or_update_multitab($values);
				$this->post_redirect_get("$table/$last/", "$table/new/");
				break;
			case 'delete':
				if(!isset($_POST['sure']) || !$_POST['sure']) die(trigger_error('Sure user expected :-)'));
				$this->db->delete($class, $id);
				die("Neco (pravdepodobne /$class/$id) bylo asi smazano. Fnuk :'-("); //TODO REDIRECT
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
		echo $this->html->header($PATH_INFO);


		//Sephirot:
		$PATH_CHUNKS = preg_split('/\//', $PATH_INFO);
		if(!isset($PATH_CHUNKS[1])) $PATH_CHUNKS[1]='';
		switch($PATH_CHUNKS[1]) {
			case 'test':	//test
				die('Tell me why you cry');
				break;
			case 'assistant': //assistant
				echo $this->safe_include(DIR_ASSISTANTS,$PATH_CHUNKS[2]);
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
							case 'image':	//?/image
							case 'delete':	//?/delete
								$this->process_http_request_post($PATH_CHUNKS[3], $class, $id);
								$edit=true;
							default:	//?/?/?
								$limit	= (int) (isset($PATH_CHUNKS[3]) ? $PATH_CHUNKS[3] : '0');
								$offset	= (int) (isset($PATH_CHUNKS[4]) ? $PATH_CHUNKS[4] : '0');
								echo $this->render_items($class, $id, $limit, $offset, $search);
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
