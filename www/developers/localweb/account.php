<?php
require("../functions.php");


$lacicloud_api = new LaciCloud();
$lacicloud_errors_api = new Errors();
$lacicloud_utils_api = new Utils();

//supress errors
$link = "";
$ftp_username = "";
$ftp_password = "";

$session = $lacicloud_api -> startSession();
if ($lacicloud_errors_api -> getSuccessOrErrorFromID($session) !== "success") {
  $result = $session;
}


if (isset($_SESSION["logged_in"]) and $_SESSION["logged_in"] == 1) {
  header("Location: /interface");
}

//create
if (isset($_POST["email"]) and isset($_POST["password"]) and isset($_POST["password_retyped"]) and isset($_POST["captcha_code"]) and isset($_POST["checkbox"]) and !isset($result)) {

    $dbc = $lacicloud_api -> getMysqlConn();
    $result = $lacicloud_api -> registerUser($_POST["email"], $_POST["password"], $_POST["password_retyped"], $_POST["captcha_code"], $_POST["beta_code"], $dbc);

 
    $link = $lacicloud_utils_api->getEmailProvider($_POST["email"])[1]; //email link for message


}


//login
if (isset($_POST["email"]) and isset($_POST["password"]) and !isset($_POST["password_retyped"])  and !isset($result)) {
 
  $dbc = $lacicloud_api -> getMysqlConn();

  $result = $lacicloud_api -> loginUser($_POST["email"], $_POST["password"], $_POST["captcha_code"], $dbc);
} 

//forgot

if (isset($_POST["reset_email_address"]) and isset($_POST["captcha_code"]) and !isset($_POST["password_retyped"]) and !isset($_POST["password"]) and !isset($result)) {
 
  $dbc = $lacicloud_api -> getMysqlConn();

  $result = $lacicloud_api -> forgotLoginStep1($_POST["reset_email_address"], $_POST["captcha_code"], $dbc);

  $link = $lacicloud_utils_api->getEmailProvider($_POST["reset_email_address"])[1]; //email link for message

  $reset_step_1 = true;

} elseif (isset($_SESSION["reset"]) and isset($_POST["new_password"]) and isset($_POST["new_password_retyped"]) and isset($_POST["reset_key"]) and isset($_POST["captcha_code"])  and !isset($result)) {
  
  $dbc = $lacicloud_api -> getMysqlConn();

  $result = $lacicloud_api -> forgotLoginStep2($_SESSION["email"], $_POST["new_password"], $_POST["new_password_retyped"], $_POST["reset_key"], $_POST["captcha_code"], $dbc);

  $reset_step_2 = true;
   
}

//account confirm 
if (isset($_GET["unique_key"]) and isset($_GET["email"])) {
   $dbc = $lacicloud_api -> getMysqlConn();
   $dbc_ftp = $lacicloud_api -> getFtpMysqlConn();

   $ftp_username = $lacicloud_utils_api->getEmailUserName(strip_tags($_GET["email"]));
   $ftp_password = bin2hex(openssl_random_pseudo_bytes(8));
  
   $result = $lacicloud_api -> confirmAccount($_GET["unique_key"], $ftp_password, $dbc, $dbc_ftp);   
}

//if a login was succesful, redirect up here to avoid header already sent errors down there
if (isset($result)) {
    $result_login_check =  $lacicloud_errors_api -> getSuccessOrErrorFromID($result);

    if ($result_login_check == "login") {
        header("Location: /interface");
    } 
}


?>
<!DOCTYPE html>
<html>
<head>

<title>LaciCloud - Account</title>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1" />

  
<meta name="description" content="Log-in or sign up here to LaciCloud, the FTP(s)-based cloud storage that is very customizable and privacy-centric" />
<meta name="keywords" content="bitcoin, FTP, FTPS, cloud, cloud-storage, backup, privacy, private, encryption, security, customizable, secure, LaciCloud, signup, login, reset" />
<meta name="author" content="Laci, Tristan, Fabio">
<meta name="language" content="english"> 

<link rel="author" href="https://plus.google.com/115512170582216368374"/>
<link rel="help" href="/resources/lacicloud_help.pdf">

<meta property="og:title" content="LaciCloud - Secure FTP(s) Cloud Storage - Account"/>
<meta property="og:url" content="https://lacicloud.net"/>
<meta property="og:image" content="https://lacicloud.net/resources/logo.png"/>
<meta property="og:image:type" content="image/png">
<meta property="og:description" content="Log-in or sign up here to LaciCloud, the FTP-based cloud storage that is very customizable and privacy-centric"/>
<meta property="og:locale" content="en_US" />

<meta property="twitter:title" content="LaciCloud - Secure FTP(s) Cloud Storage - Account"/>
<meta property="twitter:url" content="https://lacicloud.net"/>
<meta property="twitter:image" content="https://lacicloud.net/resources/logo.png"/>
<meta property="twitter:description" content="Log-in or sign up here to LaciCloud, the FTP(s)-based cloud storage that is very customizable and privacy-centric"/>

<link rel="image_src" href="/resources/logo.png"/>
    
<link rel="icon" type="image/png" href="/resources/favicon-32x32.png">

<!-- stylesheet -->
<link href="/css/style.css" rel="stylesheet" />

</head>

<body style="
background-image:url('/resources/ui_bg.jpg');
background-repeat: no-repeat;
background-position: center center;
background-attachment: fixed;
background-size: cover;
filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='.myBackground.jpg', sizingMethod='scale');
-ms-filter: "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='myBackground.jpg', sizingMethod='scale')";
">

<div class="login-page">
  <div class="form">
    <img src="/resources/logo.png" alt="LaciCloud full logo with rocket"></img>
    <br><br>
    <div class="success"></div>
    <div class="warning"></div>
    <div class="error"></div>
    <div class="info"></div>
    <form class="register-form" action="/account/#create" onsubmit="return ValidateRegister(this);" method="POST" accept-charset="UTF-8">
      <input required type="text" name="email" placeholder="email address"/>
      <input required type="password" name="password" placeholder="password"/>
      <input required type="password" name="password_retyped" placeholder="confirm password"/>
      <input required type="text" name="beta_code" placeholder="beta code"/>
      <img src="/securimage_captcha/securimage_show.php?no_cache=" alt="CAPTCHA Image"/>
      <br><br>
      <input required type="text" autocomplete="off" name="captcha_code" size="10" maxlength="6" placeholder="captcha"/>
      <label>I agree to the <a href="/resources/lacicloud_legal.pdf">Terms and Conditions</a>:</label>
      <input required id="terms_and_conditions_checkbox" type="checkbox" name="checkbox" value="check"/>
      <button>create</button>
      <p class="message">Already registered? <a href="#login">Sign In</a></p>
    </form>
    <form class="login-form" action="/account/#login" onsubmit="return ValidateLogin(this);" method="POST" accept-charset="UTF-8">
      <input required type="text" name="email" placeholder="email"/>
      <input required type="password" name="password" placeholder="password"/>
      <img src="/securimage_captcha/securimage_show.php?no_cache=" alt="CAPTCHA Image"/>
      <br><br>
      <input required type="text"  autocomplete="off" name="captcha_code" size="10" maxlength="6" placeholder="captcha"/>
      <button>login</button>
      <p class="message">Not registered? <a href="#create">Create an account!</a></p>
      <p class="message">Forgot login? <a href="#forgot_step_1">Reset password!</a></p>
    </form>
    <form class="forgot-form" action="/account/#forgot_step_2" onsubmit="return ValidateForgotStep1(this);" method="POST" accept-charset="UTF-8">
      <input required type="text" name="reset_email_address" placeholder="email"/>
      <img src="/securimage_captcha/securimage_show.php?no_cache=" alt="CAPTCHA Image"/>
      <br><br>
      <input required type="text" autocomplete="off" name="captcha_code" size="10" maxlength="6" placeholder="captcha"/>
      <button>reset</button>
      <p class="message">Remembered it? <a href="#login">Sign In</a></p>
    </form>
    <form class="forgot-form-2" action="/account/#forgot_step_2" onsubmit="return ValidateForgotStep2(this);" method="POST" accept-charset="UTF-8">
      
      <input type="hidden" disabled="disabled" name="email" <?php echo (isset($_SESSION["email"]) == true) ? "value='".$_SESSION["email"]."'" : "" ?>>

      <input required type="password" name="new_password" placeholder="new password"/>
      <input required type="password" name="new_password_retyped" placeholder="retype new password"/>

      <input required type="text" <?php echo (isset($_GET["reset_key"]) == true) ? "value='".$_GET["reset_key"]."'" : "" ?> name="reset_key" placeholder="reset key"/>

      <img src="/securimage_captcha/securimage_show.php?no_cache=" alt="CAPTCHA Image"/>
      <br><br>
      <input required type="text" autocomplete="off" name="captcha_code" size="10" maxlength="6" placeholder="captcha"/>
      <button>reset</button>
      <p class="message">Account successfully reset? <a href="#login">Sign In</a></p>
    </form>
  </div>
</div>

<!-- scripts -->
<script src="/js/main.js"></script>

<script>

var success = document.getElementsByClassName("success")[0];
var error = document.getElementsByClassName("error")[0];
var info = document.getElementsByClassName("info")[0];
var warning = document.getElementsByClassName("warning")[0];

//by default, hide all
$('.forgot-form').toggle();
$('.forgot-form-2').toggle();
$('.register-form').toggle();
$('.login-form').toggle();

window.onload = AccountFunc(0);
window.onhashchange = locationHashChangedAccountEvent;

//IE placeholder 
$('input, textarea').placeholder();

<?php 
if (isset($result)) {
    $message = $lacicloud_errors_api -> getErrorMsgFromID($result);
    $result =  $lacicloud_errors_api -> getSuccessOrErrorFromID($result);

    //for create and reset
    $message = str_replace("xXxemailxXx",'<a href="'.$link.'" target="_blank">email</a>', $message);

    //for confirm
    $message = str_replace("xXxftp_usernamexXx", $ftp_username, $message);
    $message = str_replace("xXxftp_passwordxXx", $ftp_password, $message);
  
    //bugfix for reset (else the forgot-form-2 would show even on error)
    if (isset($reset_step_1) and $reset_step_1 and $result == "error") {
      echo "
      $('.forgot-form-2').toggle();
      $('.forgot-form').toggle();
      ";
    } elseif (isset($reset_step_2) and $reset_step_2 and $result == "success" or $result == "warning")  {
      //reset session, and show login form (another bugfix for the reset function)
      @$lacicloud_api -> blowUpSession();
      echo "
      $('.forgot-form-2').toggle();
      $('.login-form').toggle();
      ";
    }
    
    echo "".$result.".innerHTML='".$message."';";
    echo "\n";
    echo "".$result.".style.display = 'block';";

}
?>
</script>

</body>

</html>

<?php 

@$lacicloud_api -> blowUpMysql($dbc, $dbc_ftp); 

?>