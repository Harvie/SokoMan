<?php
/*
 * Harvie's PHP Localization
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

require_once(DIR_LOCALE.'/'.LOCALE_LANG.'/messages.inc.php');

foreach($LOCALE_MESSAGES['regexp'] as $regexp => $replace) {
	$LOCALE_MESSAGES['regexp']['/'.$regexp.'/i'] = $LOCALE_MESSAGES['regexp'][$regexp];
	unset($LOCALE_MESSAGES['regexp'][$regexp]);
}

/**
 * !!! IMPORTANT NOTICE: This is ugly hack !!!
 * !!! You should rather use PHP's internal gettext support !!!
 */
function T($text) {
	if(is_array($text)) return array_map('T',$text);

	if(isset($GLOBALS['LOCALE_MESSAGES']['map'][$text])) return $GLOBALS['LOCALE_MESSAGES']['map'][$text];

	$t = strtolower($text);
	if(isset($GLOBALS['LOCALE_MESSAGES']['map'][$t])) return $GLOBALS['LOCALE_MESSAGES']['map'][$t];

	$t = trim($t);
	if(isset($GLOBALS['LOCALE_MESSAGES']['map'][$t])) return $GLOBALS['LOCALE_MESSAGES']['map'][$t];

	$text = str_ireplace(array_keys($GLOBALS['LOCALE_MESSAGES']['map']), $GLOBALS['LOCALE_MESSAGES']['map'], $text);

	return preg_replace(array_keys($GLOBALS['LOCALE_MESSAGES']['regexp']), $GLOBALS['LOCALE_MESSAGES']['regexp'], $text);
}
