<?php
$database = array(
	'db_host' => 'localhost',
	'db_port' => 3306,
	'db_user' => 'project',
	'db_pass' => 'project.true',
	'db_name' => 'tdpid_mutasi_core',
);
$db_connect = new mysqli($database['db_host'], $database['db_user'], $database['db_pass'], $database['db_name']);
$sql = "SELECT * FROM mutasi_bank_accounts WHERE account_is_active = 'Y'";
try {
	$sql_query = $db_connect->query($sql);
} catch (Exception $ex) {
	exit("Cannot query SQL: {$ex->getMessage()}");
}
while ($row = $sql_query->fetch_object()) {
	try {
		$get_contents = @file_get_contents('http://mutasi.myarena.id/mutasi/mutasi/update-transaction-daily/' . $row->seq);
	} catch (Exception $ex) {
		throw $ex;
		return FALSE;
	}
}
