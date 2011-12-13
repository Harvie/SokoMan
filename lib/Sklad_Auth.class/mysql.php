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

/**
* Trida zajistuje autorizaci vuci LMS
*
* @package  Sklad_Auth
* @author   Tomas Mudrunka
* @author   Martin Krusinsky
*/

class Sklad_Auth extends Sklad_Auth_common {
	function check_auth($user, $pass) {

		$LMS_CONFIG = (array)parse_ini_file('/etc/lms/lms.ini', true);
		define('BACKEND_AUTH_MYSQL_HOST', $LMS_CONFIG['database']['host']);
		define('BACKEND_AUTH_MYSQL_USER', $LMS_CONFIG['database']['user']);
		define('BACKEND_AUTH_MYSQL_PASS', $LMS_CONFIG['database']['password']);
		define('BACKEND_AUTH_MYSQL_DB', $LMS_CONFIG['database']['database']);

		$dblink = @mysql_connect(BACKEND_AUTH_MYSQL_HOST, BACKEND_AUTH_MYSQL_USER, BACKEND_AUTH_MYSQL_PASS);
		mysql_select_db(BACKEND_AUTH_MYSQL_DB, $dblink);

		mysql_query("SET NAMES utf8");

		$lQ = mysql_query("SELECT id, name, passwd, hosts, lastlogindate, lastloginip FROM users WHERE login='".$user."' AND deleted=0");
		$lA = mysql_fetch_array($lQ, MYSQL_ASSOC);
		@mysql_close($dblink);

		if(!is_array($lA)) return false;

		if(crypt($pass, $lA['passwd']) != $lA['passwd']) return false;

		$this->user['name'] = $lA['name'];
		$this->user['id'] = $lA['id'];
		$this->user['gid'] = 0; //TODO: rights
		return true;

	}
}
