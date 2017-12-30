<?php 
require '../../functions.php';

$lacicloud_api = new LaciCloud();
$lacicloud_payments_api = new Payments();
$lacicloud_encryption_api = new Encryption();
$lacicloud_errors_api = new Errors();
$dbc = $lacicloud_api->getMysqlConn();
$dbc_ftp = $lacicloud_api->getFtpMysqlConn();

if (isset($_POST["address"]) and isset($_POST["custom"]) and isset($_POST["verification"]) and isset($_POST["paid_iota"]) and isset($_POST["price_iota"])) {
	$address = $_POST["address"];
	$custom = $_POST["custom"];
	$verification = $_POST["verification"];
	$paid_iota = $_POST["paid_iota"];
	$price_iota = $_POST["price_iota"];

	$custom_decrypted =  $lacicloud_encryption_api->decryptString($custom, $lacicloud_api->grabSecret("payments_encryption_secret"));
	$custom_parts = explode(":", $custom_decrypted);

	//simply check if element 5 is a valid email, thus verify if decryption succeeded
	if (!strpos($custom_parts[5], "@") or empty($custom_parts)) {
		$lacicloud_errors_api -> msgLogger("SEVERE", "PayIOTA IPN error, encryption mismatch, custom_parts: ".print_r($custom_parts, true), 53);
		die(1);
	}


	if ($verification !== $lacicloud_api->grabSecret("payiota_verification_string")) {
		$lacicloud_errors_api -> msgLogger("SEVERE", "PayIOTA IPN error, verification key mismatch, POST verification key: ".print_r($verification, true), 53);
		die(1);
	}

	$paymentID = $lacicloud_encryption_api->encryptString($paid_iota.":".$address.":".$verification.":".$price_iota, $lacicloud_api->grabSecret("payments_encryption_secret"));
	$tier = $custom_parts[0];
	$id = $custom_parts[1];
	
	$curl = curl_init();
	curl_setopt_array($curl, array(
	    CURLOPT_RETURNTRANSFER => 1,
	    CURLOPT_URL => "https://payiota.me/api.php?action=convert_to_usd&iota=".$paid_iota,
	));

	$paid = curl_exec($curl);
	curl_close($curl);

	//upgrade tier
	$lacicloud_api->upgradeToTier($tier, $custom, $id, $dbc, $dbc_ftp);

	//send email, passes custom_parts array with: tier, id, email
	//actual payed amount is passed, not requested
	$lacicloud_payments_api->sendPaymentEmail($paid, $custom_parts, $custom, $paymentID);
}

?>
