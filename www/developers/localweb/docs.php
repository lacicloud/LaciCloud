<?php 
require("../functions.php");

$lacicloud_api = new LaciCloud();

$lacicloud_api -> isPageCached();
?>
<!DOCTYPE html>
<html>
<head>
    <title>LaciCloud - Docs</title>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <meta name="description" content="LaciCloud's help, API, and legal documents. LaciCloud is the FTP(s)-based cloud storage that is very customizable and privacy-centric" />
    <meta name="keywords" content="bitcoin, FTP, FTPS, cloud, cloud-storage, backup, privacy, private, encryption, security, customizable, secure, LaciCloud, support, help, API, legal" />
    <meta name="author" content="Laci, Tristan, Fabio">
    <meta name="language" content="english"> 

    <link rel="author" href="https://plus.google.com/115512170582216368374"/>
    <link rel="help" href="/resources/lacicloud_help.pdf">

    <meta property="og:title" content="LaciCloud - Secure FTP(s) Cloud Storage - Documents"/>
    <meta property="og:url" content="https://lacicloud.net"/>
    <meta property="og:image" content="https://lacicloud.net/resources/logo.png"/>
    <meta property="og:image:type" content="image/png">
    <meta property="og:description" content="LaciCloud's help, API, and legal documents. LaciCloud is the FTP-based cloud storage that is very customizable and privacy-centric"/>
    <meta property="og:locale" content="en_US" />

    <meta property="twitter:title" content="LaciCloud - Secure FTP(s) Cloud Storage - Documents"/>
    <meta property="twitter:url" content="https://lacicloud.net"/>
    <meta property="twitter:image" content="https://lacicloud.net/resources/logo.png"/>
    <meta property="twitter:description" content="LaciCloud's help, API, and legal documents. LaciCloud is the FTP(s)-based cloud storage that is very customizable and privacy-centric"/>

    <link rel="image_src" href="/resources/logo.png"/>

    <link rel="icon" type="image/png" href="/resources/favicon-32x32.png">
    <!--styles-->
    <link href="/css/style.css" rel="stylesheet" />
</head>
<body class="body-docs">
    <!--start - main menu-->
    <nav>
        <ul class="topnav" id="mainNav">
            <li><a class="menu-link" href="/">Home</a><span class="menu-dot">.</span></li>
            <li><a class="menu-link" href="/about_us">About Us</a><span class="menu-dot">.</span></li>
            <li><a class="menu-link active" href="/docs">Help & Others</a></li>
            <li class="logo-li">
                <img class="logo-img" src="/resources/laci-logo.png" alt="LaciCloud rocket logo"/>
            </li>
            <li><a class="menu-link" href="/shop">Shop</a><span class="menu-dot">.</span></li>
            <li><a class="menu-link" href="/contact">Contact</a><span class="menu-dot">.</span></li>
            <li><a class="menu-link" href="/account">Log in/Sign up</a></li>
            <li class="icon">
                <a href="javascript:void(0);" onclick="navFunction()">&#9776;</a>
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
    <section class="row h-about about" id="about">
        <div class="col-3 text-center">
            <div class="section-icon"><img src="/resources/about-icon.png" alt="An icon of a cloud with a lock on it"/></div>
            <div class="section-heading"><h1>An ftp-based privacy-centric cloud storage solution</h1></div>
            <div class="section-text about-text">
                LaciCloud is your private FTP(s)-cloud storage and webhost with many features, including a flexible Bitcoin payment method,
                user-data encryption, first-class security, compatibility with virtually all computing devices around the world
                and extreme versitality with an API and an HTTPS files feature. Read below.
            </div>
        </div>
    </section>
    <div class="clear"></div>

    <!--start - links-->
    <section class="row mailing-wrapper" id="mailing">
        <div class="col-3 align-center">
            <div class="mailing-button margin-center"><a href="/resources/lacicloud_help.pdf">Help PDF</a></div>
            <div class="mailing-button margin-center"><a href="/resources/lacicloud_api_documentation.pdf">API Documentation</a></div>
            <div class="mailing-button margin-center"><a href="/resources/lacicloud_legal.pdf">Legal Policy</a></div>
        </div>
    </section>

    <!--start - footer-->
    <footer class="footer-wrapper">
        <div class="footer-leftbox">
            <span class="text-uppercase bold-font">LaciCloud</span><br />
            <span class="text-italic">Made with love</span><br />
            <span class="text-italic">Brussels, Belgium  - <a href="mailto:laci@lacicloud.net">laci@lacicloud.net</a></span>
        </div>
        <div class="footer-social">
            <a href="https://twitter.com/lacicloud" target="_blank" class="icon tw"><img src="/resources/social-twitter.png" alt="Twitter icon"/></a>
            <a href="https://www.facebook.com/lacicloudhosting/" target="_blank" class="icon fb"><img src="/resources/social-facebook.png" alt="FaceBook icon"/></a>
            <a href="https://www.youtube.com/channel/UC6cwh-kIj7aq4XoiRkzVSug" target="_blank" class="icon yt"><img src="/resources/social-youtube.png" alt="YouTube icon"/></a>
        </div>
    </footer>
    <!--scripts-->
    <script src="/js/main.js"></script>
</body>

</html>
