<?php
$bank_currency='Kč';

function bank_name($name) {
	return strtolower(trim($name));
}

function bank_transaction($ctx, $from, $to, $comment, $amount=0) {
	$author=$ctx->db->quote($ctx->db->auth->get_user_id());
	$from=$ctx->db->quote(bank_name($from));
	$to=$ctx->db->quote(bank_name($to));
	$amount=$ctx->db->quote($amount);
	$comment=$ctx->db->quote(trim($comment));

	$sql="INSERT INTO `bank` (`bank_time`, `bank_from`, `bank_to`, `bank_amount`, `bank_author`, `bank_comment`) VALUES (now(), $from, $to, $amount, $author, $comment);";
	$ctx->db->safe_query($sql);
}

function bank_get_accounts($ctx, $all=false) {
	$fetch = $ctx->db->safe_query_fetch('SELECT DISTINCT bank_to FROM bank ORDER BY bank_to;');
	foreach($fetch as $account) if($all || $account['bank_to'][0]!='_') $accounts[]=$account['bank_to'];
	return $accounts;
}

function bank_add_account($ctx, $name) {
	bank_transaction($ctx, $name, $name, "Created account \"$name\"");
}

function bank_get_total($ctx, $account, $string=false) {
	$account_sql=$ctx->db->quote($account);
	$result = $ctx->db->safe_query_fetch("SELECT SUM(bank_amount) FROM `bank` WHERE `bank_to`=$account_sql;");
	$deposits = $result[0]['SUM(bank_amount)'];
	$result = $ctx->db->safe_query_fetch("SELECT SUM(bank_amount) FROM `bank` WHERE `bank_from`=$account_sql;");
	$withdrawals = $result[0]['SUM(bank_amount)'];
	if($string) return "$deposits-$withdrawals";
	return $deposits-$withdrawals;
}
function bank_rename_account($ctx, $old, $new) {
	if(in_array($new, bank_get_accounts($ctx, true))) return false;
	$old=$ctx->db->quote($old);
	$new=$ctx->db->quote($new);

	return $ctx->db->safe_query(
		"START TRANSACTION;".
		"UPDATE bank SET `bank_to`=$new WHERE `bank_to`=$old;".
		"UPDATE bank SET `bank_from`=$new WHERE `bank_from`=$old;".
		"COMMIT;"
	);
}

function bank_get_overview($ctx) {
	$accounts = bank_get_accounts($ctx);
	foreach($accounts as $acc) $overview[]=array("bank_account"=>$acc,"bank_total"=>bank_get_total($ctx, $acc));
	return $overview;
}

if(isset($_POST['create_account'])) {
	bank_add_account($this, $_POST['account_name']);
	$this->post_redirect_get("$URL_INTERNAL","Účet byl vytvořen");
}
if(isset($_POST['rename_account'])) {
	if(bank_rename_account($this, $_POST['account_old'], $_POST['account_new'])) {
		$this->post_redirect_get("$URL_INTERNAL","Účet byl upraven");
	} else {
		$this->post_redirect_get("$URL_INTERNAL","Takový účet již existuje!", false);
	}
}
if(isset($_POST['transaction'])) {
	if(!is_numeric($_POST['amount']) || $_POST['amount'] < 0) $this->post_redirect_get("$URL_INTERNAL?account=".$_POST['account_from'],"Lze převádět jen kladné částky", true);
	$comment=trim($_POST['comment']);
	if(strlen($comment)<4) $this->post_redirect_get("$URL_INTERNAL?account=".$_POST['account_from'],"Komentář musí mít alespoň 4 znaky!",true);
	bank_transaction($this, $_POST['account_from'], $_POST['account_to'], $_POST['comment'], $_POST['amount']);
	$this->post_redirect_get("$URL_INTERNAL?account=".$_POST['account_from'],"Transakce byla provedena");
}

//bank_add_account($this, 'material');
echo("<a href='$URL/'>Banka</a> - ");
echo("<a href='$URL/admin'>Správa účtů</a> - ");
echo("Účty: ");
$accounts = bank_get_accounts($this, $SUBPATH[0]=='admin');
foreach($accounts as $account) echo("<a href='$URL?account=$account'>$account</a>, ");

switch($SUBPATH[0]) {
	default:

		if(!isset($_GET['account'])) {
			echo("<h1>Banka</h1>");
			echo ("<h2>Stav</h2>");
	    $result = $this->db->safe_query_fetch("SELECT COUNT(bank_amount) as troughput FROM bank;");
			echo("Transakcí: ".$result[0]['troughput']."<br />");
	    $result = $this->db->safe_query_fetch("SELECT SUM(bank_amount) as troughput FROM bank;");
			echo("Obrat: ".$result[0]['troughput'].' '.$bank_currency);
	    $result = $this->db->safe_query_fetch("SELECT * FROM `bank` ORDER BY bank_time DESC;");
			echo $this->html->render_item_table(bank_get_overview($this),'bank');
			echo ("<h2>Přehled transakcí</h2>");
		} else {
			$account=bank_name($_GET['account']);
			$account_sql=$this->db->quote($account);
			echo("<h1>Účet: ".$account." (".bank_get_total($this,$account).$bank_currency.")</h1>");

			?>
			<form action="?" method="POST">
				Převést <input type="number" name="amount" value="" /> <?php echo $bank_currency; ?>
				z účtu <?php echo $account; ?> <input type="hidden" name="account_from" value="<?php echo $account; ?>" />
				na účet <select name='account_to'>
					<?php foreach($accounts as $acc) echo("<option value='$acc'>$acc</option>"); ?>
				</select> (pozor, dluhy se převádí opačným směrem než peníze!)<br /><br />
				Důvod: <input type="text" name="comment" style="width:64em;" />
				<input type="submit" name="transaction" value="Převést" />
			</form>
			<?php

			echo(bank_get_total($this,$account,true)." $bank_currency");
	    $result = $this->db->safe_query_fetch("SELECT * FROM `bank` WHERE `bank_to`=$account_sql OR `bank_from`=$account_sql ORDER BY bank_time DESC;");
		}
		echo $this->html->render_item_table($result,'bank');

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
