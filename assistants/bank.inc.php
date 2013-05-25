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

	$comment=trim($comment);
	if(strlen($comment)<4) die("Komentář musí mít alespoň 4 znaky!");
	$comment=$ctx->db->quote($comment);

	$sql="INSERT INTO `bank` (`bank_time`, `bank_from`, `bank_to`, `bank_amount`, `bank_author`, `bank_comment`) VALUES (now(), $from, $to, $amount, $author, $comment);";
	$ctx->db->safe_query($sql);
}

function bank_get_accounts($ctx) {
	$fetch = $ctx->db->safe_query_fetch('SELECT DISTINCT bank_to FROM bank ORDER BY bank_to;');
	foreach($fetch as $account) $accounts[]=$account['bank_to'];
	return $accounts;
}

function bank_add_account($ctx, $name) {
	bank_transaction($ctx, $name, $name, "Created account \"$name\"");
}

if(isset($_POST['create_account'])) {
	bank_add_account($this, $_POST['account_name']);
	$this->post_redirect_get("$URL_INTERNAL","Účet byl vytvořen");
}
if(isset($_POST['transaction'])) {
	if(!is_numeric($_POST['amount']) || $_POST['amount'] < 0) $this->post_redirect_get("$URL_INTERNAL","Lze převádět jen kladné částky", true);
	bank_transaction($this, $_POST['account_from'], $_POST['account_to'], $_POST['comment'], $_POST['amount']);
	$this->post_redirect_get("$URL_INTERNAL","Transakce byla provedena"); //TODO redirect na account_from
}

//bank_add_account($this, 'material');
		echo("<a href='$URL/'>Banka</a> - ");
		echo("<a href='$URL/admin'>Správa účtů</a> - ");
		echo("Účty: ");
		$accounts = bank_get_accounts($this);
		foreach($accounts as $account) echo("<a href='$URL?account=$account'>$account</a>, ");

switch($SUBPATH[0]) {
	default:

		if(!isset($_GET['account'])) {
			echo("<h1>Banka</h1>");
	    $result = $this->db->safe_query_fetch("SELECT SUM(bank_amount) as troughput FROM bank;");
			echo("Obrat: ".$result[0]['troughput'].' '.$bank_currency);
	    $result = $this->db->safe_query_fetch("SELECT * FROM `bank` ORDER BY bank_time DESC;");
		} else {
			$account=bank_name($_GET['account']);
			$account_sql=$this->db->quote($account);
	    $result = $this->db->safe_query_fetch("SELECT SUM(bank_amount) FROM `bank` WHERE `bank_to`=$account_sql;");
			$deposits = $result[0]['SUM(bank_amount)'];
	    $result = $this->db->safe_query_fetch("SELECT SUM(bank_amount) FROM `bank` WHERE `bank_from`=$account_sql;");
			$withdrawals = $result[0]['SUM(bank_amount)'];
			echo("<h1>Účet: ".$_GET['account']." (".($deposits-$withdrawals).$bank_currency.")</h1>");

			?>
			<form action="?" method="POST">
				Převést <input type="number" name="amount" value="" /> <?php echo $bank_currency; ?>
				z účtu <?php echo $account; ?> <input type="hidden" name="account_from" value="<?php echo $account; ?>" />
				na účet <select name='account_to'>
					<?php foreach($accounts as $acc) echo("<option value='$acc'>$acc</option>"); ?>
				</select> (pozor, dluhy se převádí opačným směrem než peníze!)<br />
				Důvod: <input type="text" name="comment" style="width:800px;" />
				<input type="submit" name="transaction" value="Převést" />
			</form>
			<?php

			echo("$deposits-$withdrawals $bank_currency");
	    $result = $this->db->safe_query_fetch("SELECT * FROM `bank` WHERE `bank_to`=$account_sql OR `bank_from`=$account_sql ORDER BY bank_time DESC;");
		}
		$this->html->table_hide_columns($result, 'bank');
 	  echo $this->html->render_item_table($result);


		break;
	case 'admin':
?>
	<form action="<?php echo $ASSISTANT; ?>" method="POST" >
		Account name:
		<input type="text" name="account_name" />
		<input type="submit" name="create_account" value="Create account" />
	</form>
<?php
		break;
}
