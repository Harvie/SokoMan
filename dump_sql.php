#!/usr/bin/php
<?php
require_once('sklad.conf.php');
system('mysqldump -u'.DB_USER.' -p'.DB_PASS.' '.DB_NAME);
