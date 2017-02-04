<!DOCTYPE html>
<html>
<head>
    <title>LaciCloud</title>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
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
                <img class="logo-img" src="resources/laci-logo.png" />
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
            <div class="section-icon"><img src="resources/about-icon.png" /></div>
            <div class="section-heading"><h1>An ftp-based privacy-centric cloud storage solution</h1></div>
            <div class="section-text about-text">
                LaciCloud is your private FTP(s)-cloud storage with many features, including a flexible Bitcoin payment method, 
                user-data encryption, first-class security, compatibility with virtually all computing devices around the world 
                and extreme versitality with an API and an HTTPS files feature. Read below.
            </div>
        </div>
    </section>
    <div class="clear"></div>
    <!--start - privacy-->
    <section class="row h-body privacy" id="privacy">
        <div class="col-2 f-left left-img-box"><img class="h-600" src="resources/about-1.jpg" /></div>
        <div class="col-1 text-center content-h-400 content-box">
            <div class="section-icon"><img src="resources/privacy-icon.png" /></div>
            <div class="section-heading"><h1>Privacy</h1></div>
            <div class="section-text privacy-text">
                Privacy and security is one of LaciCloud's most important feature. We do not, in any way or form,
                collect PII, or sell your data to adverisers. Payments are done via Bitcoin,
                and user data can be possibly encrypted. Of course, you can also get quick temporary FTP accounts without registering,
                much like temporary email addresses.
                Additionally, the code behind LaciCloud has been tested for security issues and privilige seperation is used between
                the webserver and the FTP(s) server.
                <span class="section-end">.</span>
            </div>
        </div>
        <div class="section-footer-image"><img class="h-200" src="resources/about-1a.jpg" /></div>
    </section>
    <div class="clear"></div>
    <!--start - versatility-->
    <section class="row h-body compatibility" id="compatibility">
        <div class="col-1 text-center content-h-400 content-box">
            <div class="section-icon"><img src="resources/compatibility-icon.png" /></div>
            <div class="section-heading"><h1>Versatility</h1></div>
            <div class="section-text compatibility-text">
                Versitality is one of of LaciCloud's other most important feature. Our service offers you ways to 
                display messages to your users, have hierchical user structures, and a way to host your files on a 
                public HTTPS server. Our API allows you to write your own applications on top of LaciCloud's, whatever 
                it may be. Do not forget the many syncronization, backup and encryption programs that exist for FTP(s).
                <span class="section-end">.</span>
            </div>
        </div>
        <div class="col-2 f-right right-img-box"><img class="h-600" src="resources/about-2.jpg" /></div>
        <div class="section-footer-image"><img class="h-200" src="resources/about-2a.jpg" /></div>
    </section>
    <div class="clear"></div>
    <!--start - open-source-->
    <section class="row h-bodylast versatility" id="versatility">
        <div class="col-1 f-left left-img-box">
            <img class="img-100 h-600" src="resources/about-3.jpg" />
        </div>
        <div class="bodylast-content-wrapper content-box">
            <div class="col-1 text-center content-h-400">
                <div class="section-icon"><img src="resources/open_source-icon.png" /></div>
                <div class="section-heading"><h1>Open-Source</h1></div>
                <div class="section-text pad-lr versatility-text">
                    Open-sourcing LaciCloud allows anyone access to view and enhance the source code. What this means is bug's gets fixed faster, 
                    and anyone can verify the codebase to make sure there are no back-doors and that nothing strange is going on.
                    <span class="section-end">.</span>
                </div>

            </div>
            <div class="section-footer-image"><img class="h-200" src="resources/about-3b.jpg" /></div>
        </div>
        <div class="col-1 f-right right-img-box">
            <img class="img-100 h-600" src="resources/about-3a.jpg" />
        </div>
    </section>
    <div class="clear"></div>
    <!--start - personal cloud-->
    <section class="row mailing-wrapper" id="mailing">
        <div class="col-3">
            <div class="margin-center mailing-logo">
                <img src="resources/laci-logo.png" />
            </div>
            <div class="section-text margin-center">
                Get started with LaciCloud for free!
            </div>
            <div class="mailing-button margin-center"><a href="/account">Get your personal cloud now!</a></div>
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
            <a href="https://twitter.com/lacicloud" class="icon tw"><img src="resources/social-twitter.png" /></a>
            <a href="#" class="icon fb"><img src="resources/social-facebook.png" /></a>
            <a href="https://www.youtube.com/channel/UC6cwh-kIj7aq4XoiRkzVSug" class="icon yt"><img src="resources/social-youtube.png" /></a>
        </div>
    </footer>
    <!--scripts-->
    <script src="js/main.js"></script>
</body>

</html>
