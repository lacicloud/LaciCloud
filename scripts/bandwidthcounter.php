<?php
require("/var/ftp/www/developers/functions.php");

$lacicloud_api = new LaciCloud();
$dbc = $lacicloud_api->getMysqlConn();

//procedural-style code to check which users are over their bandwidth limit
$query = "SELECT email, id, tier FROM users";
$stmt = mysqli_prepare($dbc, $query);
$result = mysqli_stmt_execute($stmt);

$stmt->bind_result($mysql_email,$mysql_id, $mysql_tier);

$array = array();

while ($stmt->fetch()) {
	$array[$mysql_id]["mysql_email"] = $mysql_email;
	$array[$mysql_id]["mysql_tier"] = $mysql_tier;
}

foreach ($array as $i => $item) {
	$id = $i;
	$email = $item["mysql_email"];
	$tier = $item["mysql_tier"];
	$query = "SELECT used_bandwidth FROM truebandwidthcounter WHERE id = ?";
	$stmt = mysqli_prepare($dbc, $query);
	mysqli_stmt_bind_param($stmt, "i", $id);
	$result = mysqli_stmt_execute($stmt);
 
	$stmt->bind_result($mysql_used_bandwidth);
	
	while ($stmt->fetch()) {
        	$used_bandwidth = $mysql_used_bandwidth;
	}
	
	if ($used_bandwidth >  $lacicloud_api->getTierData($tier)[5]) {
		$over_use = $used_bandwidth - $lacicloud_api->getTierData($tier)[5];
		$lacicloud_api->sendOverUseEmail($id, $email, $tier, "bandwidthcounter", $over_use, '');
	}


}

?>
