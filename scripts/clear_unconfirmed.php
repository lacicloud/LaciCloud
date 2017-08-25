<?php
require("/var/ftp/www/developers/functions.php");

$lacicloud_api = new LaciCloud();
$dbc = $lacicloud_api->getMysqlConn();

//procedural-style code to check which users haven't confirmed their accounts
$query = "SELECT id, unique_id FROM users";
$stmt = mysqli_prepare($dbc, $query);
$result = mysqli_stmt_execute($stmt);

$stmt->bind_result($mysql_id, $mysql_unique_id);

$array = array();

while ($stmt->fetch()) {
	$array[$mysql_id]["mysql_unique_id"] = $mysql_unique_id;
}

foreach ($array as $i => $item) {
	$id = $i;
	$unique_id = $item["mysql_unique_id"];

	if ((int)$unique_id !== 1) {
		echo $id;
		$lacicloud_api->deleteUser($id, $dbc);
	}

}

?>
