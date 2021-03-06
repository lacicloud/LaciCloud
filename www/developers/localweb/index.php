<?php
require("../functions.php");

$lacicloud_api = new LaciCloud();

$dbc = $lacicloud_api -> getMysqlConn();

$lacicloud_api -> increasePageVisitCounter($dbc);
$lacicloud_api -> isPageCached();

?>
<!DOCTYPE html>
<html>
<head>
    <title>LaciCloud - The open-source FTP(s)-based cloud storage and webhost</title>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

  
    <meta name="description" content="The open-source FTP(s)-based cloud storage and webhost that is very customizable and privacy-centric. Get 5GB free now!" />
    <meta name="keywords" content="bitcoin, FTP, FTPS, cloud, cloud-storage, webhost, webhosting, backup, privacy, private, encryption, security, customizable, secure, LaciCloud" />
    <meta name="author" content="Laci, Tristan, Fabio">
    <meta name="language" content="english"> 

    <meta name="norton-safeweb-site-verification" content="vm75mf5npwcd4k9ku90jt29d3df2ag5utrqzvll-2o5ytj7qvolcwdlx6ixgfwyq-jm7ce3yx0be6nx74f0soh0ozt18q10h085019weeko7m260ge-np1v-4-n6l4ox" />

    <link rel="author" href="https://plus.google.com/115512170582216368374"/>
    <link rel="help" href="/resources/lacicloud_help.pdf">

    <meta property="og:title" content="LaciCloud - Secure FTP(s) Cloud Storage, Webhost"/>
    <meta property="og:url" content="https://lacicloud.net"/>
    <meta property="og:image" content="https://lacicloud.net/resources/logo.png"/>
    <meta property="og:image:type" content="image/png">
    <meta property="og:description" content="The open-source FTP(s)-based cloud storage and webhost that is very customizable and privacy-centric. Get 5GB free now!"/>
    <meta property="og:locale" content="en_US" />

    <meta property="twitter:title" content="LaciCloud - Secure FTP(s) Cloud Storage, Webhost"/>
    <meta property="twitter:url" content="https://lacicloud.net"/>
    <meta property="twitter:image" content="https://lacicloud.net/resources/logo.png"/>
    <meta property="twitter:description" content="The open-source FTP(s)-based cloud storage and webhost that is very customizable and privacy-centric. Get 5GB free now!"/>

    <link rel="image_src" href="/resources/logo.png"/>
    
    <link rel="icon" type="image/png" href="/resources/favicon-32x32.png">
    <!--styles-->
    <link href="css/style.css" rel="stylesheet" />
</head>
<body class="body-index">
    <!--start - main menu-->
    <nav>
        <ul class="topnav" id="mainNav">
            <li><a class="menu-link active" href="/">Home</a><span class="menu-dot">.</span></li>
            <li><a class="menu-link" href="/about_us">About Us</a><span class="menu-dot">.</span></li>
            <li><a class="menu-link" href="/docs">Help & Others</a></li>
            <li class="logo-li">
                <img class="logo-img" src="resources/laci-logo.png" alt="LaciCloud rocket logo"/>
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
            <div class="section-icon"><img src="resources/about-icon.png" alt="An icon of a cloud with a lock on it"/></div>
            <div class="section-heading"><h1>A FIRM SPECIALIZING IN PROVIDING BACKUP SOLUTIONS FOR WEBSITES, (SMALL) BUSINESSES AND INDIVIDUALS</h1></div>
            <div class="section-text about-text">
                LaciCloud is your backup consultancy firm, with in-house cloud storage and PHP webhosting, comprising of many features including flexible payments, data encryption, high security, compatibility with a variety of devices/softwares and an API-based dashboard on your personal subdomain for our solutions. Read below.
            </div>
        </div>
    </section>
    <div class="clear"></div>
    <!--start - privacy-->
    <section class="row h-body privacy" id="privacy">
        <div class="col-2 f-left left-img-box"><img class="h-600" src="resources/about-1.jpg" alt="An image of a datacenter"/></div>
        <div class="col-1 text-center content-h-400 content-box">
            <div class="section-icon"><img src="resources/privacy-icon.png" alt="An icon of a lock"/></div>
            <div class="section-heading"><h1>PRIVACY AND SECURITY</h1></div>
            <div class="section-text privacy-text">
                One of LaciCloud’s most important features is its commitment to keeping your data safe and encrypted at all times. With our custom solutions, you can be sure that your data is in good hands wherever it is. Payments can be done via Bitcoins/Altcoins (including BIT-Z, IOTA, XRB, NYC, BTS and ARK) <span class="tooltip" title="PayPal, Stripe, and European IBAN">(<strong>and 3 other non-crypto payment methods!</strong>)</span>,
                and encryption is inbuilt. Additionally, the code behind LaciCloud has been tested for security issues and privilege seperation is used between the webserver and the FTP(s), or other server(s).
                <span class="section-end">.</span>
            </div>
        </div>
        <div class="section-footer-image"><img class="h-200" src="resources/about-1a.jpg" alt="An image of the Bitcoin logo"/></div>
    </section>
    <div class="clear"></div>
    <!--start - versatility-->
    <section class="row h-body compatibility" id="compatibility">
        <div class="col-1 text-center content-h-400 content-box">
            <div class="section-icon"><img src="resources/compatibility-icon.png" alt="An icon of two people shaking hands"/></div>
            <div class="section-heading"><h1>VERSATILITY AND CUSTOMIZABILITY</h1></div>
            <div class="section-text compatibility-text">
                Our backup consulting process is based on finding the right fit for you, keeping in mind the nature of your business and your budget. We will find the right solution, such as location, services (our FTP(s) server or other cloud storage services as per your organization's needs) and programs to use, in order to safely backup all your critical business data for the devices in your organization. All this is managed through our API, through which you can manage your backups, and write custom applications on top of our service. <span class="tooltip" title="TBA"><strong>There are many partners with who we work to achieve our, and your goals.</strong></span>
                <span class="section-end">.</span>
            </div>
        </div>
        <div class="col-2 f-right right-img-box"><img class="h-600" src="resources/about-2.jpg" alt="An image of a server rack with ethernet cables hanging outt of it"/></div>
        <div class="section-footer-image"><img class="h-200" src="resources/about-2a.jpg" alt="An image of a woman browsing through vinly records in a store"/></div>
    </section>
    <div class="clear"></div>
    <!--start - open-source-->
    <section class="row h-bodylast versatility" id="versatility">
        <div class="col-1 f-left left-img-box">
            <img class="img-100 h-600" src="resources/about-3.jpg" alt="An assortement of cameras, lenses, tripods"/>
        </div>
        <div class="bodylast-content-wrapper content-box">
            <div class="col-1 text-center content-h-400">
                <div class="section-icon"><img src="resources/open_source-icon.png" alt="The open-source logo" /></div>
                <div class="section-heading"><h1>CLOUD STORAGE & WEBHOSTING</h1></div>
                <div class="section-text pad-lr versatility-text">
                    Not only are we a consultancy firm, but we also offer FTP(s) storage/access and PHP-based webhosting to clients; we realize that some clients may be IT experts or developers for a company who can set up the backup procedures themselves, while others may need consultation. You can take advantage of the flexibility given by using non-proprietary FTP(s), such as having hierarchical user structures, a plethora of integrations, sending messages to your users, scripting and more. We are also partly open-source and transparent about our data processing, and we offer quick support.
                    <span class="section-end">.</span>
                </div>

            </div>
            <div class="section-footer-image"><img class="h-200" src="resources/about-3b.jpg" alt="An assortement of a camera, a smartphone, a headset, and a digital art-board with a monitor"/></div>
        </div>
        <div class="col-1 f-right right-img-box">
            <img class="img-100 h-600" src="resources/about-3a.jpg" alt="A laptop running a code editor, editing PHP"/>
        </div>
    </section>
    <div class="clear"></div>
    <!--start - personal cloud-->
    <section class="row mailing-wrapper" id="mailing">
        <div class="col-3">
            <div class="margin-center mailing-logo">
                <img src="resources/laci-logo.png" alt="LaciCloud rocket logo"/>
            </div>
            <div class="section-text margin-center">
                Get started with LaciCloud for free!
            </div>
            <div class="mailing-button margin-center"><a href="/account">Get 5GB free now!</a></div>
        </div>
    </section>

    <!--start - footer-->
    <footer class="footer-wrapper">
        <div class="footer-leftbox">
            <span class="text-uppercase bold-font">LaciCloud</span><br />
            <span class="text-italic">Made with love</span><br />
            <span class="text-italic">Brussels, Belgium  - <a href="mailto:laci@lacicloud.net">laci@lacicloud.net</a></span>
	    <span class="text-italic">Copyright &copy; LaciCloud 2018. All Rights Reserved.</span>
            <br><br><br>
            <span class="text-italic">Users: <?php echo $lacicloud_api -> getUserCount($dbc); ?> </span>
            <br>
            <span class="text-italic">Space used: 345GB out of 4096GB</span>
            <br>
            <span class="text-italic">Service status: <strong><a href="https://stats.uptimerobot.com/r8N9QIrq1">OK</a></strong></span>
        </div>
        <div class="footer-social">
            <a href="https://twitter.com/lacicloud" target="_blank" class="icon tw"><img src="resources/social-twitter.png" alt="Twitter icon"/></a>
            <a href="https://www.facebook.com/lacicloudhosting/" target="_blank" class="icon fb"><img src="resources/social-facebook.png" alt="FaceBook icon"/></a>
            <a href="https://www.youtube.com/channel/UC6cwh-kIj7aq4XoiRkzVSug" target="_blank" class="icon yt"><img src="resources/social-youtube.png" alt="YouTube icon" /></a>
        </div>
        <div class="footer-payments">
        <img src="resources/bitz.png" height="70" alt="BIT-Z accepted and recommended for trading!">
        <img src="resources/nyc.png" height="70" alt="NYC Accepted">
        <img src="resources/cloakcoin.png" height="70" alt="CloakCoin Accepted">
        </div>
    </footer>
    <!--scripts-->
    <script src="js/main.js"></script>

    <script>
        $(document).ready(function() {
            $('.tooltip').tooltipster();
        });
    </script>

</body>

</html>
