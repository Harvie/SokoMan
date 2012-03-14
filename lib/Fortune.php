<?php
/*
 * Harvie's PHP Fortunes
 * Copyright (C) 2o11  Thomas Mudrunka
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


function fortune() {
	$file = DIR_LOCALE.'/'.LOCALE_LANG.'/'.'fortunes.txt';
	if(!is_file($file)) return false;
	$fortunes = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	return $fortunes[rand(0,sizeof($fortunes)-1)];
}
