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
if (isset($_POST["email"]) and isset($_POST["password"]) and isset($_POST["password_retyped"]) and isset($_POST["captcha_code"]) and isset($_POST["checkbox"])) {

   
    if ($_POST["beta_code"] !== "hunter2") {
      echo "<p style='text-align:center'>Beta code incorrect!</p>";
      die(0);
    }

    $dbc = $lacicloud_api -> getMysqlConn();
    $result = $lacicloud_api -> registerUser($_POST["email"], $_POST["password"], $_POST["password_retyped"], $_POST["captcha_code"], $dbc);

 
    $link = $lacicloud_utils_api->getEmailProvider($_POST["email"])[1]; //email link for message


}


//login
if (isset($_POST["email"]) and isset($_POST["password"]) and !isset($_POST["password_retyped"])) {
 
  $dbc = $lacicloud_api -> getMysqlConn();

  $result = $lacicloud_api -> loginUser($_POST["email"], $_POST["password"], $_POST["captcha_code"], $dbc);
  
} elseif (isset($_GET["unique_key"])) {
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
  background: #76b852; /* fallback for old browsers */
  background: -webkit-linear-gradient(right, #76b852, #8DC26F);
  background: -moz-linear-gradient(right, #76b852, #8DC26F);
  background: -o-linear-gradient(right, #76b852, #8DC26F);
  background: linear-gradient(to left, #76b852, #8DC26F);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;  ">

<div class="login-page">
  <div class="form">
    <img src="/resources/logo.png"></img>
    <br><br>
    <div class="success"></div>
    <div class="warning"></div>
    <div class="error"></div>
    <div class="info"></div>
    <form class="register-form" action="/account/" onsubmit="return ValidateRegister(this);" method="POST" accept-charset="UTF-8">
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
      <p class="message">Already registered? <a href="#">Sign In</a></p>
    </form>
    <form class="login-form" action="/account/" onsubmit="return ValidateLogin(this);" method="POST" accept-charset="UTF-8">
      <input required type="text" name="email" placeholder="email"/>
      <input required type="password" name="password" placeholder="password"/>
      <img src="/securimage_captcha/securimage_show.php?no_cache=" alt="CAPTCHA Image"/>
      <br><br>
      <input required type="text"  autocomplete="off" name="captcha_code" size="10" maxlength="6" placeholder="captcha"/>
      <button>login</button>
      <p class="message">Not registered? <a href="#">Create an account!</a></p>
      <p class="message">Forgot login? <a href="#">Reset password</a></p>
    </form>
  </div>
</div>

<script>

var success = document.getElementsByClassName("success")[0];
var error = document.getElementsByClassName("error")[0];
var info = document.getElementsByClassName("info")[0];
var warning = document.getElementsByClassName("warning")[0];

$('.message a').click(function(){
   $('form').animate({height: "toggle", opacity: "toggle"}, "slow");
   /* reset messages on form change */
   success.style.display = 'none';
   error.style.display = 'none';
   warning.style.display = 'none';
   info.style.display = 'none';
});

<?php 

if (isset($result)) {
    $message = $lacicloud_errors_api -> getErrorMsgFromID($result);
    $result =  $lacicloud_errors_api -> getSuccessOrErrorFromID($result);

    if ($result == "login") {
    	header("Location: /interface");
    } 

    //for create
    $message = str_replace("xXxemailxXx",'<a href="'.$link.'">email</a>',$message);
    
    echo "".$result.".innerHTML='".$message."'";
    echo "\n";
    echo "".$result.".style.display = 'block';";

}

//reset post array to prevent multiple submissions
$_POST[] = array();

?>

</script>




</body>

</html>