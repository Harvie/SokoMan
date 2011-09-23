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
* Trida implementuje podpurne funkce spolecne pro vsechny implementace tridy Sklad_Auth
*
* @package  Sklad_Auth_common
* @author   Tomas Mudrunka
*/
class Sklad_Auth_common {
	function get_authorized_user_id($die=true) {
		if(isset($this->authorized_user_id)) return $this->authorized_user_id;
		if($die) die('No user authorized!!!');
		return false;
	}
}

require_once(BACKEND_AUTH.'.php');
