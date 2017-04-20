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

<title>LaciCloud - qFTP</title>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1" />


<meta name="description" content="qFTP is LaciCloud's special feature that allows you to create anonymous and temporary FTP(s) accounts without creating a LaciCloud account!" />
<meta name="keywords" content="bitcoin, FTP, FTPS, cloud, cloud-storage, backup, privacy, private, encryption, security, customizable, secure, LaciCloud, anonymous, temporary, qFTP" />
<meta name="author" content="Laci, Tristan, Fabio">
<meta name="language" content="english"> 

<link rel="author" href="https://plus.google.com/115512170582216368374"/>
<link rel="help" href="/resources/lacicloud_help.pdf">

<meta property="og:title" content="LaciCloud - Secure FTP(s) Cloud Storage - qFTP"/>
<meta property="og:url" content="https://lacicloud.net"/>
<meta property="og:image" content="https://lacicloud.net/resources/logo.png"/>
<meta property="og:image:type" content="image/png">
<meta property="og:description" content="qFTP is LaciCloud's special feature that allows you to create anonymous and temporary FTP(s) accounts without creating a LaciCloud account!"/>
<meta property="og:locale" content="en_US" />

<meta property="twitter:title" content="LaciCloud - Secure FTP(s) Cloud Storage - qFTP"/>
<meta property="twitter:url" content="https://lacicloud.net"/>
<meta property="twitter:image" content="https://lacicloud.net/resources/logo.png"/>
<meta property="twitter:description" content="qFTP is LaciCloud's special feature that allows you to create anonymous and temporary FTP(s) accounts without creating a LaciCloud account!"/>

<link rel="image_src" href="/resources/logo.png"/>

<link rel="icon" type="image/png" href="/resources/favicon-32x32.png">

<link href="/css/style.css" rel="stylesheet" />

</head>

<body style="
background-image:url('/resources/ui_bg.jpg');
background-repeat: no-repeat;
background-position: center center;
background-attachment: fixed;
background-size: cover;">

<div class="login-page">
  <div class="form">
    <img src="/resources/logo.png" alt="LaciCloud full logo with rocket"></img>
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

<!-- scripts -->
<script src="/js/main.js"></script>

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