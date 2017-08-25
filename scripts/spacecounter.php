<?php
require("/var/ftp/www/developers/functions.php");

$lacicloud_api = new LaciCloud();
$dbc = $lacicloud_api->getMysqlConn();

//procedural-style code to check which users are over their space limit
$query = "SELECT email, id, tier FROM users";
$stmt = mysqli_prepare($dbc, $query);
$result = mysqli_stmt_execute($stmt);

$stmt->bind_result($mysql_email,$mysql_id,$mysql_tier);

$array = array();

while ($stmt->fetch()) {
	$array[$mysql_id]["mysql_email"] = $mysql_email;
	$array[$mysql_id]["mysql_tier"] = $mysql_tier;
}

foreach ($array as $i => $item) {
	$id = $i;
	$email = $item["mysql_email"];
	$tier = $item["mysql_tier"];
	$query = "SELECT used_space FROM truespacecounter WHERE id = ?";
	$stmt = mysqli_prepare($dbc, $query);
	mysqli_stmt_bind_param($stmt, "i", $id);
	$result = mysqli_stmt_execute($stmt);
 
	$stmt->bind_result($mysql_used_space);
	
	while ($stmt->fetch()) {
        	$used_space = $mysql_used_space;
	}

	if ($used_space >  $lacicloud_api->getTierData($tier)[0]) {
		$over_use = $used_space - $lacicloud_api->getTierData($tier)[0];
		$lacicloud_api->sendOverUseEmail($id, $email, $tier, "spacecounter", $over_use, '');
	}

}

?>
