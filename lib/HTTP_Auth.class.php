<?php
/*
 * Harvie's PHP HTTP-Auth script
 * Copyright (C) 2oo7-2o11  Thomas Mudrunka
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

///SETTINGS//////////////////////////////////////////////////////////////////////////////////////////////////////
//Login
$require_login = false; //Require login? (if false, no login needed) - WARNING!!!
$realm = 'music'; //This is used by browser to identify protected area and saving passwords (one_site+one_realm==one_user+one_password)
$users = array( //You can specify multiple users in this array
	'music' => 'passw'
);
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//MANUAL/////////////////////////////////////////////////////////////////////////////////////////////////////////
/* HOWTO
 * To each file, you want to lock add this line (at begin of first line - Header-safe):
 * <?php require_once('http_auth.php'); ?> //Password Protection 8')
 * Protected file have to be php script (if it's html, simply rename it to .php)
 * Server needs to have PHP as module (not CGI).
 * You need HTTP Basic auth enabled on server and php.
 */
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////CODE/////////////////////////////////////////////////////////////////////////////////////////////////////////
class HTTP_Auth {

	function send_auth_headers($realm='') {
		Header('WWW-Authenticate: Basic realm="'.$realm.'"');
		Header('HTTP/1.0 401 Unauthorized');
	}

	function get_current_url($login='logout@') {
		$proto = empty($_SERVER['HTTPS']) ? $proto = 'http' : $proto = 'https';
		return $proto.'://'.$login.$_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'].$_SERVER['PHP_SELF'];
	}

	static function check_auth_internal($user, $pass) { //Check if login is succesfull
		//(U can modify this to use DB, or anything else)
		return (isset($GLOBALS['users'][$user]) && ($GLOBALS['users'][$user] == $pass));
	}

	function check_auth($user, $pass) {
		return call_user_func($this->auth_function, $user, $pass);
	}

	function unauthorized() { //Do this when login fails
		//Show warning and die
		die("$this->cbanner<title>401 - Forbidden</title>\n<h1>401 - Forbidden</h1>\n<a href=\"?\">Login...</a>\n$this->hbanner");
		die(); //Don't forget!!!
	}


	function auth($realm) {
		//Backward compatibility
		if(isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_PW'] != '') $PHP_AUTH_USER = $_SERVER['PHP_AUTH_USER'];
		if(isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_PW'] != '') $PHP_AUTH_PW = $_SERVER['PHP_AUTH_PW'];

		//Logout
		if(isset($_GET['logout'])) { //script.php?logout
			Header('HTTP/1.0 302 Found');
			Header('Location: '.$this->get_current_url());
		}

		if(!isset($PHP_AUTH_USER)) {
			//Storno or first visit of page
			$this->send_auth_headers($realm);
			$this->unauthorized();
		} else {
			//Login sent
			if($this->check_auth($PHP_AUTH_USER, $PHP_AUTH_PW)) {
				//Login succesfull - probably do nothing here
			} else {
				//Bad login
				$this->send_auth_headers($realm);
				$this->unauthorized();
			}
		}
		//Rest of file will be displayed only if login is correct
	}

	function __construct($realm='private', $require_login=true, $auth_function=false) {
		//CopyLeft
		$ver = '2o11-5.0';
		$link = '<a href="https://blog.harvie.cz/">blog.harvie.cz</a>';
		$banner = "Harvie's PHP HTTP-Auth script (v$ver)";
		$this->hbanner = "<hr /><i>$banner\n-\n$link</i>\n";
		$this->cbanner = "<!-- $banner -->\n";

		$this->auth_function=array($this,'check_auth_internal');
		if($auth_function) $this->auth_function=$auth_function;

		if($require_login) {
			$this->auth($realm);
		}
	}

}

if($require_login) new HTTP_Auth($realm);
