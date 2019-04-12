<?php 
require("../functions.php");

$lacicloud_api = new LaciCloud();

$lacicloud_api -> isPageCached();
?>
<html>
<head>
    <title>LaciCloud - About Us</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    

    <meta name="description" content="About LaciCloud, the FTP(s)-based cloud storage that is very customizable and privacy-centric" />
    <meta name="keywords" content="bitcoin, FTP, FTPS, cloud, cloud-storage, backup, privacy, private, encryption, security, customizable, secure, LaciCloud, history, about" />
    <meta name="author" content="Laci, Tristan, Fabio">
    <meta name="language" content="english"> 

    <link rel="author" href="https://plus.google.com/115512170582216368374"/>
    <link rel="help" href="/resources/lacicloud_help.pdf">

    <meta property="og:title" content="LaciCloud - Secure FTP(s) Cloud Storage - Our History"/>
    <meta property="og:url" content="https://lacicloud.net"/>
    <meta property="og:image" content="https://lacicloud.net/resources/logo.png"/>
    <meta property="og:image:type" content="image/png">
    <meta property="og:description" content="About LaciCloud, the FTP-based cloud storage that is very customizable and privacy-centric"/>
    <meta property="og:locale" content="en_US" />

    <meta property="twitter:title" content="LaciCloud - Secure FTP(s) Cloud Storage - Our History"/>
    <meta property="twitter:url" content="https://lacicloud.net"/>
    <meta property="twitter:image" content="https://lacicloud.net/resources/logo.png"/>
    <meta property="twitter:description" content="About LaciCloud, FTP(s)-based cloud storage that is very customizable and privacy-centric"/>

    <link rel="image_src" href="/resources/logo.png"/>
    

    <link rel="icon" type="image/png" href="/resources/favicon-32x32.png">
    <!--styles-->
    <link href="/css/style.css" rel="stylesheet">
</head>
<body class="body-about_us">
    <!--start - main menu-->
    <nav>
        <ul class="topnav" id="mainNav">
            <li><a class="menu-link" href="/">Home</a><span class="menu-dot">.</span></li>
            <li><a class="menu-link active" href="/about_us">About Us</a><span class="menu-dot">.</span></li>
            <li><a class="menu-link" href="/docs">Help &amp; Others</a></li>
            <li class="logo-li">
                <img class="logo-img" src="/resources/laci-logo.png" alt="LaciCloud rocket logo">
            </li>
            <li><a class="menu-link" href="/shop">Shop</a><span class="menu-dot">.</span></li>
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
    <section class="row h-about about" id="about">
        <div class="col-3 text-center">
            <div class="section-icon"><img src="/resources/icon-whylaci.png" alt="An icon image of a gear"></div>
            <div class="section-heading"><h1>Why LaciCloud?</h1></div>
            <div class="section-text about-text">
                With three years of experience as a cloud-storage service, webhost and six years of expertise in backup methods, we can meet your needs. We created LaciCloud, because the backup solutions currently on the market use proprietary software, and as a result are not flexible, and do not adhere to today's digital values like privacy, security and confidentiality. Most do not care for the business as individuum, and do not offer custom-made solutions.  We offer you choice and individual evaluations all fit to your budget to create an integral and foolproof backup plan. 
                <!-- Quick, simple, flexible => IT REALLY ISN'T ROCKET SCIENCE! -->
            </div>
        </div>
    </section>

    <div class="clear"></div>

    <!--start - features-->
    <section class="row h-features features">
        <div class="feature-block">
            <div class="text-center content-h-400">
                <div class="section-icon"><img src="/resources/versatility-icon.png" alt="An icon of a swiss knife set"></div>
                <div class="section-heading"><h1>CUSTOMIZABILITY</h1></div>
                <div class="section-text pad-lr versatility-text">
                    <ul>
                        <li>Individual solutions</li>
                        <li>API with per-user subdomains</li>
                        <li>Budget-based payments</li>
                        <li>FTP(s) compatbility and webhosting</li>
                    </ul>
                    <span class="section-end">.</span>
                </div>

            </div>
        </div>
        <div class="feature-block">
            <div class="text-center content-h-400">
                <!--
                <div class="section-icon"><img src="/resources/icon-features.png" /></div>
                <div class="section-heading"><h1>Features</h1></div>
                 -->
                <div class="section-icon"><img src="/resources/privacy-icon.png" alt="An icon of a lock"></div>
                <div class="section-heading"><h1>Privacy</h1></div>
                <div class="section-text pad-lr versatility-text">
                    <ul>
                        <li>Cryptocurrency payments</li>
                        <li>Encryption</li>
                        <li>Minimal log and PII policy</li>
                        <li>Europe-based</li>
                    </ul>
                    <span class="section-end">.</span>
                </div>

            </div>
        </div>
        <div class="feature-block">
            <div class="text-center content-h-400">
                <div class="section-icon"><img src="/resources/security-icon.png" alt="An icon of a shield with a check mark"></div>
                <div class="section-heading"><h1>Security</h1></div>
                <div class="section-text pad-lr versatility-text">
                    <ul>
                        <li>Partly open-source</li>
                        <li>Privilege separation</li>
                        <li>Secure coding practices</li>
                        <li>Pentested</li>
                    </ul>
                    <span class="section-end">.</span>
                </div>

            </div>
        </div>
    </section>

    <div class="clear"></div>

    <!--start - meet the team-->
    <section class="row h-features meettheteam">
        <div class="meet-the-team">
            <span>MEET OUR BACKUP SOLUTION</span>
        </div>
        <div class="team-wrapper">
            <div class="team-row team-row-1">
                <div class="team-user user-1">
                    <div class="team-name">THE EVALUATION</div>
                    <span class="team-end">.</span>
                    <div class="team-description">
                        <strong>We will evaluate your data situation to find the best adapted solution</strong><br />
                        We will, through consultation with you, gather all the data we need to make an informed decision on how to proceed. This consists of budget constraints, your infrastructure (ex. family home, WordPress site, business location, etc), special instructions, scheduling of the backups and more. After having done this, we will create a plan that fits your needs. 
                    </div>
                </div>
                <div class="team-image"><img style="height:100%; width:100%" src="/resources/consultation.jpg" alt="An image of five people sharing a fist bump"></div>
            </div>
            <div class="team-row team-row-2">
                <div class="team-image"><img style="height:100%; width:100%" src="/resources/customization.jpg" alt="An image of ethernet cables being plugged into networking equipment"></div>
                <div class="team-user user-2">
                    <div class="team-name">THE CUSTOMIZATION</div>
                    <span class="team-end">.</span>
                    <div class="team-description">
                        <strong>We will apply our solutions to your website, business, or home</strong><br />
                        We install the carefully selected programs and custom-made scripts onto your environnment. We fine-tune it to your environnment and then launch the scripts, and finally we set up your dashboard with the progress tracking software. The selected services can be LaciCloud's FTP, a third-party cloud storage, and the programs can range anywhere from backup/synchronization to virtual drive programs to your needs, all duly encrypted.
                    </div>
                </div>
            </div>
            <div class="team-row team-row-3">
                <div class="team-user user-3">
                    <div class="team-name">THE BACKUP</div>
                    <span class="team-end">.</span>
                    <div class="team-description">
                        <strong>We will offer you a way to keep track of your data</strong><br />
                        After setting up the environnment, you can watch your backups arrive through the LaciCloud dashboard or email alerts, divided into each of your devices and schedules. We offer integration for all of our custom solutions so that you can keep track of your backups on LaciCloud. If you should have any questions or changes in your environnment, you only have to contact us and we will get back to you quickly!
                    </div>
                </div>
                <div class="team-image"><img style="height:100%; width:100%" src="/resources/backup.jpg" alt="An image of a laptop running a dashboard software with graphs and statistics"></div>
            </div>
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
    <!--scripts-->
    <script src="/js/main.js"></script>


</body>
</html>