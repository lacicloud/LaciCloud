<?php 
require("../functions.php");

$lacicloud_api = new LaciCloud();
$lacicloud_qftp_api = new qFTP();
$lacicloud_errors_api = new Errors();

$dbc = $lacicloud_api -> getMysqlConn();
$dbc_ftp = $lacicloud_api -> getFtpMysqlConn();

if (isset($_POST["captcha_code"]) and isset($_POST["beta_code"])) {
  
  $username = $lacicloud_qftp_api -> generateFTPUsername();
  $password = bin2hex(openssl_random_pseudo_bytes(8));

  $result = $lacicloud_qftp_api -> addQFTPUser($username, $password, $_POST["captcha_code"], $_POST["beta_code"], $dbc_ftp);

}   



?>

<html>
<head>

<script src="/js/main.js"></script>
<link href="/css/style.css" rel="stylesheet" />

<title>LaciCloud qFTP</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />

</head>

<body style="
background-image:url('/resources/ui_bg.jpg');
background-repeat: no-repeat;
background-position: center center;
background-attachment: fixed;
background-size: cover;">

<div class="login-page">
  <div class="form">
    <img src="/resources/logo.png"></img>
    <br><br>
    <div class="success"></div>
    <div class="warning"></div>
    <div class="error"></div>
    <div class="info"></div>
    
    <form class="qftp-form" action="/qftp/"  method="POST" accept-charset="UTF-8">
      <h4>Please enter the captcha and the beta code to generate your qFTP account!</h4>
      <br><br>
      <input required type="text" name="beta_code" placeholder="beta code"/>
      <img src="/securimage_captcha/securimage_show.php?no_cache=" alt="CAPTCHA Image"/>
      <br><br>
      <input required type="text" autocomplete="off" name="captcha_code" size="10" maxlength="6" placeholder="captcha"/>
      <button>generate</button>
      <p class="message">Have an LaciCloud account? <a href="/account/#login">Sign In</a></p>
    </form>
  </div>
</div>

<script>
var success = document.getElementsByClassName("success")[0];
var error = document.getElementsByClassName("error")[0];
var info = document.getElementsByClassName("info")[0];
var warning = document.getElementsByClassName("warning")[0];

//by default, hide all
$('.qftp-form').toggle();


window.onload = qFTPFunc;

//IE placeholder 
$('input, textarea').placeholder();

<?php 
if (isset($result)) {
    $message = $lacicloud_errors_api -> getErrorMsgFromID($result);
    $result =  $lacicloud_errors_api -> getSuccessOrErrorFromID($result);

    //for qftp
    $message = str_replace("xXxusernamexXx", $username, $message);
    $message = str_replace("xXxpasswordxXx", $password, $message);
    
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