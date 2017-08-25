<?php 
require '../../functions.php';

$lacicloud_api = new LaciCloud();
$lacicloud_payments_api = new Payments();
$lacicloud_encryption_api = new Encryption();
$lacicloud_errors_api = new Errors();
$dbc = $lacicloud_api->getMysqlConn();
$dbc_ftp = $lacicloud_api->getFtpMysqlConn();
$CP = new \MineSQL\CoinPayments();

$CP->setMerchantId($lacicloud_api->grabSecret('coinpayments_merchant_id'));
$CP->setSecretKey($lacicloud_api->grabSecret('coinpayments_ipn_secret'));

try {
if($CP->listen($_POST, $_SERVER)) 
{
	// The payment is successful and passed all security measures
	// you can call the DB here if you want
	
	//ignore withdrawals
	if ($_POST["ipn_type"] == "withdrawal") {
		die(0);
	}

	//actual amount paid 
	$amount = floatval($_POST["amount1"]);
	$currency = $_POST["currency1"];
	
	$custom = $_POST["custom"];

	if (empty($custom)) {
		$lacicloud_errors_api -> msgLogger("SEVERE", "Coinpayments IPN error, custom is not set!", 53);
		die(1);
	}

	$custom_decrypted =  $lacicloud_encryption_api->decryptString($custom, $lacicloud_api->grabSecret("payments_encryption_secret"));

	//verify if decryption succeded
	if (!$custom_decrypted) {
		$lacicloud_errors_api -> msgLogger("SEVERE", "Coinpayments IPN error, encryption mismatch, custom is false!", 53);
		die(1);
	}

	$custom_parts = explode(":", $custom_decrypted);

	//simply check if element 5 is a valid email, thus verify if decryption succeeded (again)
	if (!strpos($custom_parts[5], "@") or empty($custom_parts)) {
		$lacicloud_errors_api -> msgLogger("SEVERE", "Coinpayments IPN error, encryption mismatch, custom_parts: ".print_r($custom_parts, true), 53);
		die(1);
	}

	$tier = $custom_parts[0];
	$id = $custom_parts[1];
	$order_currency = $custom_parts[2];
	$order_amount = $custom_parts[3];

	$paymentID = $_POST["txn_id"];


	//verify payment amount
	 if ($currency != $order_currency) { 
	     $lacicloud_errors_api -> msgLogger("SEVERE", "Coinpayments IPN error, currency mismatch for ID ".$id." currency: ".$currency." order_currency: ".$order_currency, 53);
	     die(1);
	 }     
	   
	  // Check amount against order total 
	 if ($amount < $order_amount) { 
	     $lacicloud_errors_api -> msgLogger("SEVERE", "Coinpayments IPN error, amount mismatch for ID ".$id." amount: ".$amount." order_amount: ".$order_amount, 53);
	     die(1);
	 } 

	 //upgrade tier
	 $lacicloud_api->upgradeToTier($tier, $custom, $id, $dbc, $dbc_ftp);

	 //send email, passes custom_parts array with: tier, id, email
	 //actual payed amount is passed, not requested
	 $lacicloud_payments_api->sendPaymentEmail($amount, $custom_parts, $custom, $paymentID);
	
} 
else 
{
	//the payment is pending. an exception is thrown for all other payment errors.
	//do nothing
}
}
catch(Exception $e) 
{
	$lacicloud_errors_api -> msgLogger("SEVERE", "Coinpayments IPN exceptioN: ".$e, 53);
}

?>
