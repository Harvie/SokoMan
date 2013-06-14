<?php
/*
 * Barcode Class
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
* Trida implementuje funkce pro praci s carovymi kody
*
* @package  Barcode
* @author   Tomas Mudrunka
*/
class Barcode {
	static function test() {
		system('barcode -b test | convert - /dev/null', $ret);
		if($ret == 0) {
			return true;
		} else {
			trigger_error('Barcode-related features are disabled. Please install GNU Barcode and ImageMagick.');
			return false;
		}
	}

	static function generate_barcode($string='EXAMPLE', $convert='png', $enctype='code128b') {
		$string = escapeshellarg($string);
		$enctype = escapeshellarg($enctype);
		$convert = $convert ? " | convert - -crop 0x60+0+30\\! -background none -flatten $convert:-" : '';
		return shell_exec("barcode -e $enctype -E -b $string$convert");
	}

	static function cached_barcode($string='EXAMPLE', $convert='png', $enctype='code128b') {
		$ext = $convert ? $convert : 'ps';
		$filename=DIR_BARCODES."/$enctype-".urlencode($string).".$ext";
		if(is_file($filename)) return file_get_contents($filename);
		$barcode = self::generate_barcode($string,$convert,$enctype);
		file_put_contents($filename,$barcode);
		return $barcode;
	}

	static function download_barcode($string='EXAMPLE', $convert='png', $enctype='code128b') {
		if(self::test()) {
			header('Content-Type: image/png');
			header('Cache-Control: max-age=604800, public'); //1week caching
		}	else die();
		error_reporting(0); //TODO: enable errors again
		die(self::cached_barcode($string,$convert,$enctype));
	}
}
