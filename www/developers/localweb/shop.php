<?php
require("../functions.php");

$lacicloud_api = new LaciCloud();
$lacicloud_ftp_api = new FTPActions();
$lacicloud_payments_api = new Payments();
$lacicloud_errors_api = new Errors();

$session = $lacicloud_api -> startSession();
if ($lacicloud_errors_api -> getSuccessOrErrorFromID($session) !== "success") {
  $result = $session;
}

$dbc = $lacicloud_api -> getMysqlConn();
$dbc_ftp = $lacicloud_api -> getFtpMysqlConn();

//nifty PHP 7 feature
$id = $_SESSION["id"] ?? null;

//another nifty (just standard) PHP feature
$tier = isset($id) ? $lacicloud_ftp_api -> getUserValues($id, $dbc)["tier"] : null;


if (!isset($tier) or !isset($id) and !isset($result)) {
    $result = $lacicloud_payments_api->getNotLoggedInErrorID();
}

//redirect to account page if 'Sign up now' button is clicked
if (!isset($tier) and !isset($id) and isset($_GET["action"])) {
    header("Location: /account");
}

?>
<html>
<head>
    <title>LaciCloud - Shop</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" href="/resources/favicon-32x32.png">
    <!--scripts-->
    <script src="/js/main.js"></script>
    <!--styles-->
    <link href="/css/style.css" rel="stylesheet">
</head>
<?php 

 if (isset($_GET["action"]) and isset($_GET["tier"]) and !isset($result))  {

    $tier = $_GET["tier"][0];

    echo "<body style='text-align: center;'>";

    $result = $lacicloud_payments_api -> payWithGoUrl($tier, $id, $dbc, $dbc_ftp); 

    echo '<div class="success"></div>
          <div class="error"></div>
          <div class="warning"></div>
          <div class="info"></div>';

    //note to user saying that payment is possible via shapeshifter.io as well
    echo "<p>Want to pay using other cryptocurrency not listed here? Try <a target='_blank' href='https://shapeshift.io/'>shapeshift.io</a>! Supports Zcash as well!</p>";

    echo "<script>";

    echo 'var success = document.getElementsByClassName("success")[0];
          var error = document.getElementsByClassName("error")[0];
          var info = document.getElementsByClassName("info")[0];
          var warning = document.getElementsByClassName("warning")[0];
';

    if (isset($result)) {
        $message = $lacicloud_errors_api -> getErrorMsgFromID($result);
        $result =  $lacicloud_errors_api -> getSuccessOrErrorFromID($result);

        //if payment is successful, allow user to return to the shop page
        $message = str_replace("xXxherexXx",'<a href="/shop">here</a>',$message);
        
        echo "".$result.".innerHTML='".$message."';";
        echo "\n";
        echo "".$result.".style.display = 'block';";

    }


    echo "</script>";

    echo "</body></html>";
    //don't display the rest of the page
    die(0);
}

?>
<body class="body-shop">
    <!--start - main menu-->
    <nav>
        <ul class="topnav" id="mainNav">
            <li><a class="menu-link" href="/">Home</a><span class="menu-dot">.</span></li>
            <li><a class="menu-link" href="/about_us">About Us</a><span class="menu-dot">.</span></li>
            <li><a class="menu-link" href="/docs">Help &amp; Others</a></li>
            <li class="logo-li">
                <img class="logo-img" src="/resources/laci-logo.png">
            </li>
            <li><a class="menu-link active" href="/shop">Shop</a><span class="menu-dot">.</span></li>
            <li><a class="menu-link" href="/contact">Contact</a><span class="menu-dot">.</span></li>
            <li><a class="menu-link" href="/account">Log in/Sign up</a></li>
            <li class="icon">
                <a href="javascript:void(0);" onclick="navFunction()">☰</a>
            </li>
        </ul>
    </nav>
    <!--end - main menu-->
    <!--start - header-->
    <section class="h-header">
        <div class="col-3 topheader">
        </div>
    </section>

    <!--start - about-->
    <section class="row h-shop shop" id="shop" style="max-height: 300px">
        <div class="col-3 text-center">
            <div class="section-icon"><img src="/resources/icon-whylaci.png"></div>
            <div class="section-heading"><h1>We believe in choice.</h1></div>
            <div class="section-text about-text">
                We think it's always better to have something that suits you and just you.
                That's why we bring you a range of choice. Whoever you are, whatever your needs are, we have something for you!
                If you don't feel that the plans we offer suit you, contact us and we'll try to tailor something to you. See below for options!
            </div>

            <div style="width: 500px; margin:0 auto; padding-bottom: 10%">
            <div class="success"></div>
            <div class="error"></div>
            <div class="warning"></div>
            <div class="info"></div>
            </div>

        </div>
    </section>

    <div class="clear"></div>

    <!--start - meet the shop-->
    <section class="row h-features meettheshop">
        <div class="shop-wrapper">
            <div class="shop-row shop-row-1">
                <div class="shop-image"><img style="height:100%; width:100%" src="/resources/lacicloud_1_cat.jpg"></div>
                <div class="shop-user shop-user-1">
                    <div class="shop-name">Free - For First Timers</div>
                    <span class="shop-end">.</span>
                    <div class="shop-description">
                        <ul>
                            <li>75 GB of storage</li>
                            <li>25 FTP users</li>
                            <li>Free technical support</li>
                            <li>Limited bandwidth</li>
                            <li>Host static websites</li>
                        </ul>
                    </div>
                    <div class="shop-page-button"><a href="/shop.php?action=buy&tier=1" <?php if (isset($id) and $tier == "1") { echo 'class="disabled"'; } ?> ><span> <?php if (isset($id) and $tier == "1") { echo "Current Tier"; } elseif (isset($id)) { echo "Select Tier 1 >&nbsp;"; } else { echo "Sign up now! >&nbsp;"; } ?></span></a></div>
                </div>
            </div>
            <div class="shop-row">
                <div class="shop-user">
                    <div class="shop-name">10€/Month - For regulars</div>
                    <span class="shop-end">.</span>
                    <div class="shop-description">
                        <ul>
                            <li>250 GB of storage</li>
                            <li>125 FTP users</li>
                            <li>Free technical support</li>
                            <li>Bit limited bandwidth</li>
                            <li>Host static websites</li>
                        </ul>
                    </div>
                     <div class="shop-page-button"><a href="/shop.php?action=buy&tier=2" <?php if (isset($id) and $tier == "2") { echo 'class="disabled"'; } ?>  > <span><?php if ($tier == "2") { echo "Current Tier"; } else { echo "Select Tier 2 >&nbsp;"; } ?></span></a></div>
                </div>
                <div class="shop-image"><img style="height:100%; width:100%" src="/resources/lacicloud_2_cats.jpg"></div>
            </div>
            <div class="shop-row shop-row-3">
                <div class="shop-image"><img style="height:100%; width:100%" src="/resources/lacicloud_3_cats.jpg"></div>
                <div class="shop-user shop-user-3">
                    <div class="shop-name">20€/Month - For experts</div>
                    <span class="shop-end">.</span>
                    <div class="shop-description">
                        <ul>
                            <li>525 GB of storage</li>
                            <li>125 FTP users</li>
                            <li>Free technical support</li>
                            <li>Just a lil' bit limited bandwidth</li>
                            <li>Host static websites</li>
                        </ul>
                    </div>
                    <div class="shop-page-button"><a href="/shop.php?action=buy&tier=3" <?php if (isset($id) and $tier == "3") { echo 'class="disabled"'; } ?>  > <span><?php if ($tier == "3") { echo "Current Tier"; } else { echo "Select Tier 3 >&nbsp;"; } ?></span></a></div>
                </div>
            </div>
            <div class="shop-row shop-row-4">   
                <div class="shop-user shop-user-4">
                    <div class="shop-name">Customise it</div>
                    <span class="shop-end">.</span>
                    <div class="shop-description">
                        <ul>
                            <li>Up to 1Tb+ of storage</li>
                            <li>Up to 1000+ FTP users</li>
                            <li>Free technical support</li>
                            <li>Unlimited bandwidth</li>
                            <li>Host static/dynamic websites</li>
                        </ul>
                    </div>
                     <div class="shop-page-button"><a href="/contact.php"><span>Contact Us >&nbsp;</span></a></div>
                </div>
                <div class="shop-image"><img style="height:100%; width:100%" src="/resources/lacicloud_4_cats.jpg"></div>
            </div>
    </section>

    <div class="clear"></div>

    <!--start - footer-->
    <footer class="footer-wrapper">
        <div class="footer-leftbox">
            <span class="text-uppercase bold-font">LaciCloud</span><br>
            <span class="text-italic">Made with love</span><br>
            <span class="text-italic">Brussels, Belgium  - <a href="mailto:laci@lacicloud.net">laci@lacicloud.net</a></span>
        </div>
        <div class="footer-social">
            <a href="#" class="icon tw"><img src="/resources/social-twitter.png"></a>
            <a href="#" class="icon fb"><img src="/resources/social-facebook.png"></a>
            <a href="#" class="icon yt"><img src="/resources/social-youtube.png"></a>
        </div>
    </footer>

<script>
var success = document.getElementsByClassName("success")[0];
var error = document.getElementsByClassName("error")[0];
var info = document.getElementsByClassName("info")[0];
var warning = document.getElementsByClassName("warning")[0];

<?php 
if (isset($result)) {
    $message = $lacicloud_errors_api -> getErrorMsgFromID($result);
    $result =  $lacicloud_errors_api -> getSuccessOrErrorFromID($result);
    
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