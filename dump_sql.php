#!/usr/bin/php
<?php
if(isset($_SERVER['REQUEST_METHOD'])) die('Not for you');
$nodump = array('item','model','barcode','lock','bank');
require_once('sklad.conf.php');

$ignore = '';
$nodata = '';
foreach($nodump as $t) {
	$ignore .= ' --ignore-table='.DB_NAME.'.'.$t;
	$nodata .= ' '.$t;
}

system('mysqldump -u'.DB_USER.' -p'.DB_PASS.' '.DB_NAME.$ignore);
if($nodata != '') system('mysqldump -u'.DB_USER.' -p'.DB_PASS.' '.DB_NAME.' --skip-comments --no-data'.$nodata);
