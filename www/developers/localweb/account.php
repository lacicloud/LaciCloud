<?php
require("../functions.php");


$lacicloud_api = new LaciCloud();
$lacicloud_errors_api = new Errors();
$lacicloud_utils_api = new Utils();

$link = "";

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

} elseif (isset($_SESSION["reset"]) and isset($_POST["new_password"]) and isset($_POST["new_password_retyped"]) and isset($_POST["reset_key"]) and isset($_POST["captcha_code"])  and !isset($result)) {
  
  $dbc = $lacicloud_api -> getMysqlConn();

  $result = $lacicloud_api -> forgotLoginStep2($_SESSION["email"], $_POST["new_password"], $_POST["new_password_retyped"], $_POST["reset_key"], $_POST["captcha_code"], $dbc);

  //reset session
  $lacicloud_api -> blowUpSession();
   
}

//account confirm 
if (isset($_GET["unique_key"])) {
   $dbc = $lacicloud_api -> getMysqlConn();

   $result = $lacicloud_api -> confirmAccount($_GET["unique_key"], $dbc);

}

?>
<html>
<head>

<script src="/js/main.js"></script>
<link href="/css/style.css" rel="stylesheet" />

<title>LaciCloud Account</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />

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
    <img src="/resources/logo.png"></img>
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

    if ($result == "login") {
      //prevent header errors...
      echo 'window.location = "/interface";';
    } 

    //for create
    $message = str_replace("xXxemailxXx",'<a href="'.$link.'" target="_blank">email</a>',$message);
    
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