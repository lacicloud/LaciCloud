<!DOCTYPE html>
<html>
<head>
	<title>LaciCloud Payment Gateway</title>

	<!--scripts-->
    <script src="/js/main.js"></script>
    <!--styles-->
    <link href="/css/style.css" rel="stylesheet">

    <!--Stripe-->
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>

</head>
<body style="text-align: center;">

<h2>LaciCloud Secure Payment Gateway</h2>

<div id="payment_div">

<div class="success"></div>
<div class="warning"></div>
<div class="error"></div>
<div class="info"></div>


<?php 
require("../functions.php");

$lacicloud_api = new LaciCloud();
$lacicloud_errors_api = new Errors();
$lacicloud_ftp_api = new FTPActions();
$lacicloud_encryption_api = new Encryption();
$lacicloud_payments_api = new Payments();
$dbc = $lacicloud_api->getMysqlConn();
$dbc_ftp = $lacicloud_api->getFtpMysqlConn();

$CP = new \MineSQL\CoinPayments();

\Stripe\Stripe::setApiKey($lacicloud_api->grabSecret("stripe_secret_key"));

//check if logged in, and also redirect if wanted tier is 1
$session = $lacicloud_api -> startSession();
if (!isset($_SESSION["logged_in"]) or $_SESSION["logged_in"] !== 1 or isset($_GET["tier"]) and $_GET["tier"] == "1") {
  header("Location: /account");
  die(0);
}


//load variables
$id = $_SESSION["id"];  
$user_values = $lacicloud_ftp_api->getUserValues($id, $dbc);
$email = $user_values["email"];
$valid_tiers = $lacicloud_payments_api->valid_tiers;

if (isset($_GET["tier"]) and $_GET["tier"] == "2") {
		$tier = "2";
		$price = 15;
} elseif (isset($_GET["tier"]) and $_GET["tier"] == "3") {
		$tier = "3";
		$price = 25;
} 

if (isset($_GET["action"]) and $_GET["action"] == "buy" and isset($_GET["tier"]) and in_array((int)$_GET["tier"], $valid_tiers) and isset($_GET["type"]) and $_GET["type"] == "crypto" and !isset($_GET["result"])) {


	if ($lacicloud_errors_api -> getSuccessOrErrorFromID($lacicloud_api -> canChangeToTier($tier, $id, $dbc, $dbc_ftp)) !== "success") {
			$result = $lacicloud_payments_api->getChangeDisallowedCode();
	} else {
		$CP->setMerchantId($lacicloud_api->grabSecret('coinpayments_merchant_id'));
		$CP->setSecretKey($lacicloud_api->grabSecret('coinpayments_ipn_secret'));

		//REQUIRED
		$CP->setFormElement('currency', 'EUR');
		$CP->setFormElement('amountf', $price);
		$CP->setFormElement('item_name', 'LaciCloud tier '.$tier.' for 1 year');
		$CP->setFormElement('item_desc', 'LaciCloud tier '.$tier.' for 1 year');
		//OPTIONAL
		
		//this will be broken down to an array later: tier, id, currency, price, type (crypto, paypal, etc), email
		//to prevent tampering, it is encrypted and base64 encoded
		$CP->setFormElement('custom', $lacicloud_encryption_api->encryptString($tier.':'.$id.':EUR:'.$price.":crypto:".$email, $lacicloud_api->grabSecret("payments_encryption_secret")));

		$CP->setFormElement('success_url', 'https://lacicloud.net/pay/?action=buy&tier='.$tier.'&type=crypto&result=success');
		$CP->setFormElement('cancel_url', 'https://lacicloud.net/pay/?action=buy&tier='.$tier.'&type=crypto&result=cancel');
		$CP->setFormElement('want_shipping', 0);
		
		//set some PII
		$CP->setFormElement('first_name', "John");
		$CP->setFormElement('last_name', "Doe");
		$CP->setFormElement('email', $email);

		// After you have finished configuring all your form elements, 
		//you can call the CoinPayments::createForm method to invoke 
		// the creation of a usable html form.
		echo $CP->createForm();
	}

} elseif (isset($_GET["action"]) and $_GET["action"] == "buy" and isset($_GET["tier"]) and in_array((int)$_GET["tier"], $valid_tiers) and isset($_GET["type"]) and $_GET["type"] == "paypal" and !isset($_GET["result"])) {

	if ($lacicloud_errors_api -> getSuccessOrErrorFromID($lacicloud_api -> canChangeToTier($tier, $id, $dbc, $dbc_ftp)) !== "success") {
			$result = $lacicloud_payments_api->getChangeDisallowedCode();
	} else {

	
		$sendPayData = array(
		    "METHOD" => "BMCreateButton",
		    "VERSION" => "95.0",
		    "USER" => $lacicloud_api->grabSecret("paypal_api_user"),
		    "PWD" => $lacicloud_api->grabSecret("paypal_api_pwd"),
		    "SIGNATURE" => $lacicloud_api->grabSecret("paypal_api_signature"),
		    "BUTTONCODE" => "ENCRYPTED",
		    "BUTTONTYPE" => "BUYNOW",
		    "BUTTONSUBTYPE" => "SERVICES",
		    "L_BUTTONVAR1" => "item_number=".$tier,
		    "L_BUTTONVAR2" => "item_name=LaciCloud Tier ".$tier,
		    "L_BUTTONVAR3" => "amount=".$price,
		    "L_BUTTONVAR4" => "currency_code=EUR",
		    "L_BUTTONVAR5" => "no_shipping=1",
		    "L_BUTTONVAR6" => "no_note=1",
		    "L_BUTTONVAR7" => "notify_url=https://lacicloud.net/payment_system/paypal.php",
		    "L_BUTTONVAR8" => "cancel_return=https://lacicloud.net/pay/?action=buy&tier=".$tier."&type=paypal&result=cancel",
		    "L_BUTTONVAR9" => "return=https://lacicloud.net/pay/?action=buy&tier=".$tier."&type=paypal&result=success",
		    "L_BUTTONVAR10" => "subtotal=".$price,
		    "L_BUTTONVAR11" => "custom=".$lacicloud_encryption_api->encryptString($tier.':'.$id.':EUR:'.$price.":paypal:".$email, $lacicloud_api->grabSecret("payments_encryption_secret"))
		);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		
		$paydata = http_build_query($sendPayData);

		curl_setopt($curl,CURLOPT_POST, 1);
		curl_setopt($curl,CURLOPT_POSTFIELDS, $paydata);

		curl_setopt($curl, CURLOPT_URL, 'https://api-3t.paypal.com/nvp?');
		$nvpPayReturn = curl_exec($curl);

		parse_str($nvpPayReturn, $return);
		print($return["WEBSITECODE"]);
	}

} elseif (isset($_GET["action"]) and $_GET["action"] == "buy" and isset($_GET["tier"]) and isset($_GET["type"]) and isset($_GET["result"]) and $_GET["result"] == "success") {
	$result = $lacicloud_payments_api->getSuccessCode();
} elseif (isset($_GET["action"]) and $_GET["action"] == "buy" and isset($_GET["tier"]) and isset($_GET["type"]) and isset($_GET["result"]) and $_GET["result"] == "cancel") {
	$result = $lacicloud_payments_api->getCancelCode();
} elseif (isset($_GET["action"]) and $_GET["action"] == "buy" and isset($_GET["tier"]) and in_array((int)$_GET["tier"], $valid_tiers) and isset($_GET["type"]) and $_GET["type"] == "stripe" and !isset($_GET["result"])) {
	?>

	<?php  
		if ($lacicloud_errors_api -> getSuccessOrErrorFromID($lacicloud_api -> canChangeToTier($tier, $id, $dbc, $dbc_ftp)) !== "success") {
			$result = $lacicloud_payments_api->getChangeDisallowedCode();
		} else {
	?>
	<form <?php echo 'action="/pay/?action=buy&tier='.$tier.'&custom='.$lacicloud_encryption_api->encryptString($tier.':'.$id.':EUR:'.$price.":stripe:".$email, $lacicloud_api->grabSecret("payments_encryption_secret")).'"' ?> method="POST">
	  <script
	    src="https://checkout.stripe.com/checkout.js" class="stripe-button"
	    data-key=<?php echo '"'.$lacicloud_api->grabSecret("stripe_publishable_key").'"' ?>
	    data-amount=<?php echo '"'.($price * 100).'"' ?>
	    data-email=<?php echo '"'.$email.'"'?>
	    data-name="LaciCloud"
	    data-description=<?php echo '"LaciCloud Tier '.$tier.'"' ?>
	    data-image="https://lacicloud.net/resources/laci-logo.png"
	    data-locale="auto"
	    data-currency="eur">
	  </script>
	</form>
	<?php } ?>

	<?php 
} elseif (isset($_POST['stripeToken']) and isset($_GET["custom"])) {
	$token = $_POST["stripeToken"];
	$price_stripe = $price * 100;

	try {
		$charge = \Stripe\Charge::create(array(
		    'amount'   => $price_stripe,
		    'currency' => 'eur',
		    'description' => 'LaciCloud Tier '.$tier,
		    'source' => $token
		));
	} catch (Exception $e) {
		$lacicloud_errors_api -> msgLogger("SEVERE", 'Stripe API error: '.$e, 53);
	}

	$paymentID = $charge["balance_transaction"];

	if ($charge["status"] == "succeeded" and $charge->paid == true and $lacicloud_errors_api -> getSuccessOrErrorFromID($lacicloud_api -> canChangeToTier($tier, $id, $dbc, $dbc_ftp)) == "success") {
		 $custom = $_GET["custom"];
		 $custom_decrypted =  $lacicloud_encryption_api->decryptString($custom, $lacicloud_api->grabSecret("payments_encryption_secret"));
		 $custom_parts = explode(":", $custom_decrypted);

		 $tampering = false;
		 if (!strpos($custom_parts[5], "@") or empty($custom_parts)) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "Stripe payment error, encryption mismatch, custom_parts: ".print_r($custom_parts, true), 53);
			$tampering = true;
		}

		 if (!$tampering) {
		 	//upgrade tier
		 	$lacicloud_api->upgradeToTier($tier, $custom, $id, $dbc, $dbc_ftp);

		 	//send email
		 	$lacicloud_payments_api->sendPaymentEmail($price, $custom_parts, $custom, $paymentID);

		 	$result = $lacicloud_payments_api->getSuccessCode();
		 } else {
		 	$result = $lacicloud_payments_api->getCancelCode();
		 }

	} else {
		 $result = $lacicloud_payments_api->getCancelCode();
	}

} elseif (!isset($_GET["type"])) {
	if (isset($_GET["action"]) and isset($_GET["tier"])) {
		echo "<a href='/pay/?action=".$_GET["action"]."&tier=".$_GET["tier"]."&type=crypto'>Pay using Bitcoin (+70 Altcoins)<a/>";
		echo "<br><br>";
		echo "<a href='/pay/?action=".$_GET["action"]."&tier=".$_GET["tier"]."&type=paypal'>Pay using Paypal</a>";
		echo "<br><br>";
		echo "<a href='/pay/?action=".$_GET["action"]."&tier=".$_GET["tier"]."&type=stripe'>Pay using Stripe (Credit/Debit cards)</a>";
		echo "<br><br>";
		echo "<a href='/pay/?action=".$_GET["action"]."&tier=".$_GET["tier"]."&type=iban' onClick='alert(\"Please contact laci@lacicloud.net to pay using bank transfer manually!\"); return false;'>Pay using European Bank transfer (IBAN)</a>";
	} else {
		header("Location: /shop");
	}
	
}

?>
</div>
<script>
var success = document.getElementsByClassName("success")[0];
var error = document.getElementsByClassName("error")[0];
var info = document.getElementsByClassName("info")[0];
var warning = document.getElementsByClassName("warning")[0];

<?php 

if (isset($result)) {
    $message = $lacicloud_errors_api -> getErrorMsgFromID($result);
    $result =  $lacicloud_errors_api -> getSuccessOrErrorFromID($result);

    $message = str_replace("xXxherexXx",'<a href="/interface/?refresh=">here</a>',$message);

    echo "".$result.".innerHTML='".$message."';";
    echo "\n";
    echo "".$result.".style.display = 'block';";
}

?>

</script>

</body>
</html>