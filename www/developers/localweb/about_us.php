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
                Why did we create LaciCloud? Simply put, we have all tried to find a cloud storage service for
                personal use. What was available didn't suit us. They aren't flexible, they aren't open-source,
                they don't allow you to encrypt your information, and they most certainly breach your constitutional
                rights by keeping an eye on your data in a sense. Back in 2013, my Dropbox account was suspended
                due to allegedly uploading pirated software to the site; that's when I thought I had enough.
                By creating LaciCloud, I created a secure environment for myself and my friends where they could
                upload files in complete security and privacy to an FTP server. This quickly grew into LaciCloud, today a cloud storage and a webhost.
                <br><strong>LaciCloud is the hard work of multiple people;</strong>
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
                <div class="section-heading"><h1>Versatility</h1></div>
                <div class="section-text pad-lr versatility-text">
                    <ul>
                        <li>FTP device and programs compatibility</li>
                        <li>API</li>
                        <li>Per-user subdomains for PHP webhosting</li>
                        <li>Flexible payments</li>
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
                        <li>Bitcoin payments</li>
                        <li>Temporary FTP accounts</li>
                        <li>Minimal log keeping and data policy</li>
                        <li>Possible user data encryption</li>
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
                        <li>Open-source</li>
                        <li>Privilege separation</li>
                        <li>Secure coding practices (OWASP)</li>
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
            <span>Meet the team!</span>
        </div>
        <div class="team-wrapper">
            <div class="team-row team-row-1">
                <div class="team-user user-1">
                    <div class="team-name">Laszlo Molnarfi</div>
                    <span class="team-end">.</span>
                    <div class="team-description">
                        <strong>CEO of LaciCloud/Head of ICT</strong><br />
                        Laci is our excellent programmer. He is an aspiring young computer scientist with the
                        desire to create software that makes people happy. He has experience in web development,
                        web app and network security, computer forensics and system administration. He has been
                        passionate about computers since the age of 4, and probably will be forever.
                    </div>
                </div>
                <div class="team-image"><img style="height:100%; width:100%" src="/resources/laci.jpg" alt="An image of the CEO of LaciCloud, Laszlo Molnarfi; teen circa 16 years"></div>
            </div>
            <div class="team-row team-row-2">
                <div class="team-image"><img style="height:100%; width:100%" src="/resources/tristan.jpg" alt="An image of the designer of LaciCloud, Tristan Thomson; adult circa 18 years"></div>
                <div class="team-user user-2">
                    <div class="team-name">Tristan Thomson</div>
                    <span class="team-end">.</span>
                    <div class="team-description">
                        <strong>Head of Design</strong><br />
                        Tristan is our excellent designer. He has been in the design industry for quite some time.
                        He is highly skilled in graphic design, and an excellent, dedicated worker. He has completed
                        over a 100 graphic design projects to date all with great success...
                    </div>
                </div>
            </div>
            <div class="team-row team-row-3">
                <div class="team-user user-3">
                    <div class="team-name">Fabio Barbero</div>
                    <span class="team-end">.</span>
                    <div class="team-description">
                        <strong>Head of User Experience and Testing</strong><br /> Fabio is our excellent Beta tester.
                        Over the past few months, Fabio has been greatly dedicated to this project. Finding every
                        little bug, content problem, spelling mistake and always had great suggestions for the site.
                        He is also a young aspiring computer scientist like Laci. In addition to these team members,
                        I feel it is neccesary to mention some amazing people who have helped us on our journey to
                        create LaciCloud; <br>Click
                        <strong><a style="color:inherit;text-decoration:none;" href="/humans.txt">here</a></strong>
                        to read more about these other members.
                    </div>
                </div>
                <div class="team-image"><img style="height:100%; width:100%" src="/resources/fabio.jpg" alt="An image of the Beta Tester of LaciCloud, Fabio Barbero; teen circa 16 years"></div>
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