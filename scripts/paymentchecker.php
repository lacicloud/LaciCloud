<?php
require("/var/ftp/www/developers/functions.php");

$lacicloud_api = new LaciCloud();
$dbc = $lacicloud_api->getMysqlConn();

//procedural-style code to check which users have their payments overdue
$query = "SELECT email, id, tier, lastpayment FROM users";
$stmt = mysqli_prepare($dbc, $query);
$result = mysqli_stmt_execute($stmt);

$stmt->bind_result($mysql_email,$mysql_id,$mysql_tier,$mysql_lastpayment);

$array = array();

while ($stmt->fetch()) {
	$array[$mysql_id]["mysql_email"] = $mysql_email;
	$array[$mysql_id]["mysql_tier"] = $mysql_tier;
	$array[$mysql_id]["mysql_lastpayment"] = $mysql_lastpayment;
}

foreach ($array as $i => $item) {
	$id = $i;
	$email = $item["mysql_email"];
	$tier = $item["mysql_tier"];
	$lastpayment = $item["mysql_lastpayment"];

	if ($tier == 1) {
		continue;
	}

        $current = time();
        $one_year =  $lacicloud_api->unix_time_1_year;
        $over_use = ($current - $lastpayment);
        $over_use = ($over_use - $one_year);
        $over_use = round(($over_use / 86400));

	if ($over_use > 0) {
		$lacicloud_api->sendOverUseEmail($id, $email, $tier, "paymentchecker", $over_use, date("Y-m-d", $lastpayment + $one_year));
	}
}

?>
