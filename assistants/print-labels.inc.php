<?php
	if(isset($_POST['print'])) {
		$prefix = time().'SPOJE';
		$enctype = 'code128b';
		$geometry = '';
		$count = $_POST['cols']*$_POST['rows']*$_POST['pages'];

		$table = '-p A4 -t '.escapeshellarg(
			$_POST['cols'].'x'.$_POST['rows'].
			'+'.$_POST['left'].'+'.$_POST['bottom'].'-'.$_POST['right'].'-'.$_POST['top']
		);

		$barcodes = '';
		for($i=0;$i<$count;$i++) $barcodes.=' -b '.escapeshellarg($prefix.$i);

		switch(strtolower($_POST['print'])) {
			case 'debug': case 'dbg':
				break;
			case 'pdf':
				$convert='| ps2pdf -dCompatibility=1.2 - -';
				header('Content-Type: application/pdf');
				break;
			default: case 'ps':
				$convert='';
				header('Content-Type: application/postscript');
				header("Content-Disposition: attachment; filename=labels.ps");
				break;
		}
		error_reporting(0);
		$cmd="barcode -e $enctype $geometry $table $barcodes $convert";
		if($_POST['print']=='Debug') die($cmd);
		system($cmd);
		die();
	}
?>
<form action="?" method="POST">
	<input type="number" name="cols" value="4" /> &harr; Sloupců<br />
	<input type="number" name="rows" value="13" /> &varr; Řádků<br />
	<input type="number" name="pages" value="1" /> &crarr; Stran<br />
	<table>
		<tr><td></td><td><input type="number" name="top" value="25" /></td><td></td></tr>
		<tr><td><input type="number" name="left" value="2" /></td><td>Okraje</td><td><input type="number" name="right" value="1" /></td></tr>
		<tr><td></td><td><input type="number" name="bottom" value="20" /></td><td></td></tr>
	</table>
	<input type="submit" name="print" value="Debug" />
	<input type="submit" name="print" value="PS" />
	<input type="submit" name="print" value="PDF" />
</form>
Pozor! Každý arch vytiskni jen jednou a radši ho hned po vytištění smaž!
