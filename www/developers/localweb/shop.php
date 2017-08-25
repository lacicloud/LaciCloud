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
$lastpayment = isset($id) ? $lacicloud_ftp_api -> getUserValues($id, $dbc)["lastpayment"] : null;

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

    <meta name="description" content="LaciCloud's Shop. LaciCloud is the FTP(s)-based cloud storage that is very customizable and privacy-centric" />
    <meta name="keywords" content="bitcoin, FTP, FTPS, cloud, cloud-storage, backup, privacy, private, encryption, security, customizable, secure, LaciCloud, shop, tiers" />
    <meta name="author" content="Laci, Tristan, Fabio">
    <meta name="language" content="english"> 

    <link rel="author" href="https://plus.google.com/115512170582216368374"/>
    <link rel="help" href="/resources/lacicloud_help.pdf">

    <meta property="og:title" content="LaciCloud - Secure FTP(s) Cloud Storage - Shop"/>
    <meta property="og:url" content="https://lacicloud.net"/>
    <meta property="og:image" content="https://lacicloud.net/resources/logo.png"/>
    <meta property="og:image:type" content="image/png">
    <meta property="og:description" content="LaciCloud's Shop. LaciCloud is the FTP-based cloud storage that is very customizable and privacy-centric"/>
    <meta property="og:locale" content="en_US" />

    <meta property="twitter:title" content="LaciCloud - Secure FTP(s) Cloud Storage - Shop"/>
    <meta property="twitter:url" content="https://lacicloud.net"/>
    <meta property="twitter:image" content="https://lacicloud.net/resources/logo.png"/>
    <meta property="twitter:description" content="LaciCloud's Shop. LaciCloud is the FTP(s)-based cloud storage that is very customizable and privacy-centric"/>

    <link rel="image_src" href="/resources/logo.png"/>

    <link rel="icon" type="image/png" href="/resources/favicon-32x32.png">
    <!--scripts-->
    <script src="/js/main.js"></script>
    <!--styles-->
    <link href="/css/style.css" rel="stylesheet">
</head>

<body class="body-shop">
    <!--start - main menu-->
    <nav>
        <ul class="topnav" id="mainNav">
            <li><a class="menu-link" href="/">Home</a><span class="menu-dot">.</span></li>
            <li><a class="menu-link" href="/about_us">About Us</a><span class="menu-dot">.</span></li>
            <li><a class="menu-link" href="/docs">Help &amp; Others</a></li>
            <li class="logo-li">
                <img class="logo-img" src="/resources/laci-logo.png" alt="LaciCloud rocket logo">
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
            <div class="section-icon"><img src="/resources/icon-whylaci.png" alt="An icon image of a gear"></div>
            <div class="section-heading"><h1>We believe in choice.</h1></div>
            <div class="section-text about-text">
                We think it's always better to have something that suits you and just you.
                That's why we bring you a range of choice. Whoever you are, whatever your needs are, we have something for you!
                If you don't feel that the plans we offer suit you, contact us and we'll try to tailor something to you. All our packages are DMCA-free, see below for options!
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
                <div class="shop-image"><img style="height:100%; width:100%" src="/resources/lacicloud_1_cat.jpg" alt="A picture of one cute cat"></div>
                <div class="shop-user shop-user-1">
                    <div class="shop-name">Free - For First Timers</div>
                    <span class="shop-end">.</span>
                    <div class="shop-description">
                        <ul>
                            <li>5 GB of storage</li>
                            <li>10 FTP users</li>
                            <li>Free technical support</li>
                            <li>Limited speed (8MBit up, 1MBit down)</li>
			                <li>Limited bandwidth (10GB/Month FTP)</li>
                            <li>No subdomain or webhosting</li>
                        </ul>
                    </div>
                 

                    <div class="shop-page-button"><a href="/pay/?action=buy&tier=1" <?php if (isset($id)) { echo 'class="disabled"'; } ?> ><span> 

                    <?php


                    if (isset($id)) { 
                        if ($tier == "1") {
                            echo "Current Tier";
                        } else {
                            echo "Unavailable";
                        }
                    } else { 
                        echo "Sign up now! >&nbsp;"; 
                    } 

                    ?>


                     </span></a></div>
                </div>
            </div>
            <div class="shop-row">
                <div class="shop-user">
                    <div class="shop-name">15€/Year - For regulars</div>
                    <span class="shop-end">.</span>
                    <div class="shop-description">
                        <ul>
                            <li>125 GB of storage</li>
                            <li>50 FTP users</li>
                            <li>Free technical support</li>
                            <li>Bit limited speed (16MBit up, 2MBit down)</li>
                            <li>Bit limited bandwidth (1TB/Month FTP and website)</li>
                            <li>Free subdomain, PHP/MySQL webhosting</li>
                        </ul>
                    </div>
                     <div class="shop-page-button"><a href="/pay/?action=buy&tier=2" <?php if (isset($id) and $tier == "2" and (time() - $lastpayment) < $lacicloud_api->unix_time_1_year or isset($id) and $tier == "3") { echo 'class="disabled"'; } ?>  > <span>


                     <?php 

                    if (isset($id)) {
                        if ($tier == "2"  and (time() - $lastpayment) > $lacicloud_api->unix_time_1_year) {
                            echo "Renew Tier 2 >&nbsp;";
                        } elseif ($tier == "2") {
                            echo "Current Tier";
                        } elseif ($tier == "1") {
                           echo "Select Tier 2 >&nbsp;";
                        } else {
                            echo "Unavailable";
                        }
                    } else {
                        echo "Select Tier 2 >&nbsp;";
                    }

                    ?>
                         

                     </span></a></div>
                

                </div>
                <div class="shop-image"><img style="height:100%; width:100%" src="/resources/lacicloud_2_cats.jpg" alt="A picture of two cute cats"></div>
            </div>
            <div class="shop-row shop-row-3">
                <div class="shop-image"><img style="height:100%; width:100%" src="/resources/lacicloud_3_cats.jpg" alt="A picture of three cute cats"></div>
                <div class="shop-user shop-user-3">
                    <div class="shop-name">25€/Year - For experts</div>
                    <span class="shop-end">.</span>
                    <div class="shop-description">
                        <ul>
                            <li>250 GB of storage</li>
                            <li>125 FTP users</li>
                            <li>Free technical support</li>
                            <li>Just a lil' bit limited speed (32MBit up, 4MBit down)</li>
                            <li>Just a lil' bit limited bandwidth (2TB/Month FTP and website)</li>
			                <li>Free subdomain, PHP/MySQL webhosting</li>
                        </ul>
                    </div>
                    <div class="shop-page-button"><a href="/pay/?action=buy&tier=3" <?php if (isset($id) and $tier == "3" and (time() - $lastpayment) < $lacicloud_api->unix_time_1_year) { echo 'class="disabled"'; } ?>  > <span>


                    <?php 

                    if (isset($id)) {
                        if ($tier == "3" and  (time() - $lastpayment) > $lacicloud_api->unix_time_1_year) {
                            echo "Renew Tier 3 >&nbsp;";
                        } elseif ($tier == "1" or $tier == "2") {
                            echo "Select Tier 3 >&nbsp;";
                        } else {
                            echo "Current Tier";
                        }
                    } else {
                        echo "Select Tier 3 >&nbsp;";
                    }   

                    ?>
                        

                    </span></a></div>
                </div>
            </div>
            <div class="shop-row shop-row-4">   
                <div class="shop-user shop-user-4">
                    <div class="shop-name">Customise it</div>
                    <span class="shop-end">.</span>
                    <div class="shop-description">
                        <ul>
                            <li>Up to 1TB+ of storage</li>
                            <li>Up to 1000+ FTP users</li>
                            <li>Free technical support</li>
                            <li>Unlimited speed/bandwidth</li>
                            <li>Free subdomain, PHP/MySQL webhosting</li>
                        </ul>
                    </div>
                     <div class="shop-page-button"><a href="/contact"><span>Contact Us >&nbsp;</span></a></div>
                </div>
                <div class="shop-image"><img style="height:100%; width:100%" src="/resources/lacicloud_4_cats.jpg" alt="A picture of four cute cats"></div>
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
            <a href="https://twitter.com/lacicloud" target="_blank" class="icon tw"><img src="/resources/social-twitter.png" alt="Twitter icon"></a>
            <a href="https://www.facebook.com/lacicloudhosting/" target="_blank" class="icon fb"><img src="/resources/social-facebook.png" alt="FaceBook icon"></a>
            <a href="https://www.youtube.com/channel/UC6cwh-kIj7aq4XoiRkzVSug" target="_blank" class="icon yt"><img src="/resources/social-youtube.png" alt="YouTube icon"></a>
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
