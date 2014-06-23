<?php
$bank_currency='Kč';
global $bank_table;
$bank_table='transaction';
$recursive=true; //USE RECURSIVE QUERIES???
$limit=23;

function bank_name($name) {
	return strtolower(trim($name));
}

function bank_transaction($ctx, $from, $to, $comment, $amount=0) {
	global $bank_table;
	$author=$ctx->db->quote($ctx->db->auth->get_user_id());
	$from=$ctx->db->quote(bank_name($from));
	$to=$ctx->db->quote(bank_name($to));
	$amount=$ctx->db->quote($amount);
	$comment=$ctx->db->quote(trim($comment));

	$sql="INSERT INTO `${bank_table}` (`${bank_table}_time`, `${bank_table}_from`, `${bank_table}_to`, `${bank_table}_amount`, `${bank_table}_author`, `${bank_table}_comment`) VALUES (now(), $from, $to, $amount, $author, $comment);";
	$ctx->db->safe_query($sql);
}

function bank_get_accounts($ctx, $all=false) {
	global $bank_table;
	$fetch = $ctx->db->safe_query_fetch("SELECT DISTINCT ${bank_table}_to FROM ${bank_table} ORDER BY ${bank_table}_to;");
	foreach($fetch as $account) if($all || $account[$bank_table.'_to'][0]!='_') $accounts[]=$account[$bank_table.'_to'];
	return $accounts;
}

function bank_get_last_to($ctx, $account) {
	global $bank_table;
	$account=$ctx->db->quote(bank_name($account));
	$fetch = $ctx->db->safe_query_fetch("SELECT ${bank_table}_to FROM ${bank_table} WHERE ${bank_table}_from=$account ORDER BY ${bank_table}_time DESC LIMIT 1;");
	return $fetch[0][$bank_table.'_to'];
}

function bank_add_account($ctx, $name) {
	bank_transaction($ctx, $name, $name, "Created account \"$name\"");
}

function bank_month_sql($ctx, $month=false) {
	global $bank_table;
	$month_sql = 'TRUE';
	if(!is_bool($month)) {
		$month_q = $ctx->db->quote($month);
		$month_sql .= " AND DATE_FORMAT(${bank_table}_time, '%Y-%m') = ".$month_q;
	}
	return $month_sql;
}

function bank_get_total($ctx, $account, $month, $string=false) {
	global $bank_table;
	$account_sql=$ctx->db->quote($account);
	$result = $ctx->db->safe_query_fetch("SELECT SUM(${bank_table}_amount) FROM `${bank_table}` WHERE `${bank_table}_to`=$account_sql AND ".bank_month_sql($ctx,$month).';');
	$deposits = $result[0]["SUM(${bank_table}_amount)"];
	$result = $ctx->db->safe_query_fetch("SELECT SUM(${bank_table}_amount) FROM `${bank_table}` WHERE `${bank_table}_from`=$account_sql AND (".bank_month_sql($ctx,$month).');');
	$withdrawals = $result[0]["SUM(${bank_table}_amount)"];
	if($string) return "$deposits-$withdrawals";
	return $deposits-$withdrawals;
}
function bank_rename_account($ctx, $old, $new) {
	global $bank_table;
	if(in_array($new, bank_get_accounts($ctx, true))) return false;
	$old=$ctx->db->quote($old);
	$new=$ctx->db->quote($new);

	return $ctx->db->safe_query(
		"START TRANSACTION;".
		"UPDATE ${bank_table} SET `${bank_table}_to`=$new WHERE `${bank_table}_to`=$old;".
		"UPDATE ${bank_table} SET `${bank_table}_from`=$new WHERE `${bank_table}_from`=$old;".
		"COMMIT;"
	);
}

function bank_get_overview($ctx,$prefix='',$month=false) {
	global $bank_table;
	$accounts = bank_get_accounts($ctx);
	foreach($accounts as $acc) {
		$total=bank_get_total($ctx, $acc, $month);
		$overview['table'][]=array("${prefix}account"=>$acc,"${prefix}total"=>$total);
		$overview['array'][$acc]=$total;
	}
	return $overview;
}

if(isset($bank_json_only) && $bank_json_only) {
	$overview=bank_get_overview($this,'');
	die(json_encode(array(
		'overview'=>$overview['array']
	)));
}

if(isset($_POST['create_account'])) {
	$new_account=$_POST['account_name'];
	bank_add_account($this, $new_account);
	$this->post_redirect_get("$URL_INTERNAL/admin","Účet <b>$new_account</b> byl vytvořen!");
}
if(isset($_POST['rename_account'])) {
	$new_account=$_POST['account_new'];
	$old_account=$_POST['account_old'];
	if(bank_rename_account($this, $_POST['account_old'], $_POST['account_new'])) {
		$this->post_redirect_get("$URL_INTERNAL/admin","Účet <b>$old_account</b> byl přejmenován na <b>$new_account</b>!");
	} else {
		$this->post_redirect_get("$URL_INTERNAL/admin","Účet <b>$new_account</b> již existuje!", false);
	}
}
if(isset($_POST['transaction'])) {
	$account_from=$_POST['account_from'];
	$account_to=$_POST['account_to'];
	$amount=$_POST['amount'];
	$comment=trim($_POST['comment']);
	$account_redirect=$account_from;
	if(!is_numeric($amount)) $this->post_redirect_get("$URL_INTERNAL?account=".urlencode($account_from),"Převáděnou částkou musí být celé číslo.", true);
	if($amount < 0) {
		$amount=abs($amount);
		list($account_from,$account_to)=array($account_to,$account_from); //swap from/to
	}
	if(strlen($comment)<4) $this->post_redirect_get("$URL_INTERNAL?account=".urlencode($account_from),"Komentář musí mít alespoň 4 znaky!",true);
	bank_transaction($this, $account_from, $account_to, $comment, $amount);
	$this->post_redirect_get("$URL_INTERNAL?account=".urlencode($account_redirect),"Transakce byla provedena:<br />Převod <b>$amount $bank_currency</b> z účtu <b>$account_from</b> na účet <b>$account_to</b>.<br />($comment)");
}

$month = isset($_GET['month']) ? $_GET['month'] : false;

//bank_add_account($this, 'material');
echo("<a href='$URL/'>Banka</a> - ");
echo("<a href='$URL/admin'>Správa účtů</a> - ");
echo('<span style="float:right;">');
echo $this->html->form($URL, 'GET', array(
  array('month',$month,'text',false,'','YYYY-MM:'),
  array(false,'SELECT BY MONTH','submit')
));
echo('</span>');
echo("Účty: <br />");
$accounts = bank_get_accounts($this, $SUBPATH[0]=='admin');
$lastaccount=false;
foreach($accounts as $account) {
	if($lastaccount && $lastaccount[0]!=$account[0] && !preg_match('/[a-zA-Z0-9]/', $lastaccount[0])) echo('<br />');
	echo("<a href='$URL?account=".urlencode($account)."'>$account</a>, ");
	$lastaccount=$account;
}



switch($SUBPATH[0]) {
	default:

		if(isset($_GET['limit'])) $limit = intval($_GET['limit']);
		$limit_sql = $limit==0 ? '' : ' LIMIT '.intval($limit);

		if(!isset($_GET['account'])) {
			echo("<h1>Banka $month</h1>");
			echo ("<h2>Stav $month</h2>");
			$result = $this->db->safe_query_fetch("SELECT COUNT(${bank_table}_amount) as troughput FROM ${bank_table} WHERE ".bank_month_sql($this,$month).';');
			echo("Transakcí $month: ".$result[0]['troughput']."<br />");
			$result = $this->db->safe_query_fetch("SELECT SUM(${bank_table}_amount) as troughput FROM ${bank_table} WHERE ".bank_month_sql($this,$month).';');
			echo("Obrat $month: ".$result[0]['troughput'].' '.$bank_currency);
			$result = $this->db->safe_query_fetch("SELECT * FROM `${bank_table}` WHERE ".bank_month_sql($this,$month)." ORDER BY ${bank_table}_time DESC".$limit_sql.";");
			$overview=bank_get_overview($this,$bank_table.'_',$month);
			echo $this->html->render_item_table($overview['table'],'bank');
		} else {
			$account=bank_name($_GET['account']);
			$account_sql=$this->db->quote($account);
			echo("<h1>Účet: ".$account." $month (".bank_get_total($this,$account,$month).$bank_currency.")</h1>");

			?>
			<form action="?" method="POST">
				Převést <!-- &plusmn; --><input type="number" name="amount" value="" /> <?php echo $bank_currency; ?>
				z účtu <b><?php echo $account; ?></b> <input type="hidden" name="account_from" value="<?php echo $account; ?>" />
				na účet <select name='account_to'>
					<?php
						//Ziskat posledni cilovy ucet a presunout na zacatek $accounts
						$last=bank_get_last_to($this,$account);
						unset($accounts[array_search($last,$accounts)]);
						array_unshift($accounts,$last);

						foreach($accounts as $acc) echo("<option value='$acc'>$acc</option>");
					?>
				</select> (pozor! zamysli se! převádíš peníze nebo dluhy?! záporná částka = převod v opačném směru.)<br /><br />
				Důvod: <input type="text" name="comment" maxlength="128" style="width:64em;" />
				<input type="submit" name="transaction" value="Převést" />
			</form>
			<?php

			echo(bank_get_total($this,$account,$month,true)." $bank_currency");
			$subtotal=$recursive?",(
				(SELECT SUM(${bank_table}_amount) FROM ${bank_table} x WHERE ${bank_table}_to=$account_sql AND x.${bank_table}_id<=${bank_table}.${bank_table}_id)
				-(SELECT SUM(${bank_table}_amount) FROM ${bank_table} x WHERE ${bank_table}_from=$account_sql AND x.${bank_table}_id<=${bank_table}.${bank_table}_id)
				) as ${bank_table}_subtotal":'';
			//(@flux := IF(transaction_to='harvie',IF(transaction_from='harvie',0,1),IF(transaction_from='harvie',-1,0))) as flux
			$result = $this->db->safe_query_fetch("SELECT *${subtotal} FROM `${bank_table}` WHERE (`${bank_table}_to`=$account_sql OR `${bank_table}_from`=$account_sql) AND (".bank_month_sql($this,$month).") ORDER BY ${bank_table}_time DESC".$limit_sql.";");
		}
		echo ("<h2>Přehled transakcí $month</h2>");
		echo $this->html->render_item_table($result,$bank_table);

		if(!isset($_GET['limit']))
			echo("<a href='?".$_SERVER['QUERY_STRING']."&limit=0'>zobrazit vše...</a>");
		if($limit == 0)
			echo('to je vše.');

		break;
	case 'admin':
?>
	</p>
	<form action="<?php echo $ASSISTANT; ?>" method="POST" >
		Account:
		<input type="text" name="account_name" />
		<input type="submit" name="create_account" value="Create account" />
	</form>
	<form action="<?php echo $ASSISTANT; ?>" method="POST" >
		Account: <select name='account_old'>
			<?php foreach($accounts as $acc) echo("<option value='$acc'>$acc</option>"); ?>
		</select>
		<input type="text" name="account_new" />
		<input type="submit" name="rename_account" value="Rename account" /> (účty začínající podtržítkem nebudou běžně viditelné)
	</form>
	</p>
<?php
		break;
}
