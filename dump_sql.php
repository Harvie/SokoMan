#!/usr/bin/php
<?php
if(isset($_SERVER['REQUEST_METHOD'])) die('Not for you');
require_once('sklad.conf.php');
system('mysqldump -u'.DB_USER.' -p'.DB_PASS.' '.DB_NAME);
