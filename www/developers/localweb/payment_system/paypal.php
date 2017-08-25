<?php 

require('../../functions.php');

$lacicloud_api = new LaciCloud();
$lacicloud_payments_api = new Payments();
$lacicloud_encryption_api = new Encryption();
$lacicloud_errors_api = new Errors();
$dbc = $lacicloud_api->getMysqlConn();
$dbc_ftp = $lacicloud_api->getFtpMysqlConn();

use overint\PaypalIPN;

$ipn = new PaypalIPN();

$verified = $ipn->verifyIPN();
if ($verified) {
    /*
     * Process IPN
     * A list of variables is available here:
     * https://developer.paypal.com/webapps/developer/docs/classic/ipn/integration-guide/IPNandPDTVariables/
     */

    //since the form was encrypted, we don't need many of the checks used with coinpayments.php
    $amount = $_POST["mc_gross"];
    $custom = $_POST["custom"];

    $custom_decrypted =  $lacicloud_encryption_api->decryptString($custom, $lacicloud_api->grabSecret("payments_encryption_secret"));
    $custom_parts = explode(":", $custom_decrypted);

    $tier = $custom_parts[0];
    $id = $custom_parts[1];
    $order_currency = $custom_parts[2];
    $order_amount = $custom_parts[3];

    $paymentID = $_POST["txn_id"];

    //upgrade tier
    $lacicloud_api->upgradeToTier($tier, $custom, $id, $dbc, $dbc_ftp);

    //send email, passes custom_parts array with: tier, id, email
    $lacicloud_payments_api->sendPaymentEmail($amount, $custom_parts, $custom, $paymentID);
    
}


?>
