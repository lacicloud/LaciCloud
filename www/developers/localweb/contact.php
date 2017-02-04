<?php 
require("../functions.php");
if (isset($_POST["contact_reason"]) and isset($_POST["subject"]) and isset($_POST["message"]) and isset($_POST["reply_to_address"])) {
    $lacicloud_api = new LaciCloud();
    $lacicloud_api->sendContactEmail($_POST["contact_reason"], $_POST["subject"], $_POST["message"], $_POST["reply_to_address"]);
    $_POST[] = array();
}


?>
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
    <link href="/css/style.css" rel="stylesheet" />
</head>
<body class="body-contact">
    <!--start - main menu-->
    <nav>
        <ul class="topnav" id="mainNav">
            <li><a class="menu-link" href="/">Home</a><span class="menu-dot">.</span></li>
            <li><a class="menu-link" href="/about_us">About Us</a><span class="menu-dot">.</span></li>
            <li><a class="menu-link" href="/docs">Help & Others</a></li>
            <li class="logo-li">
                <img class="logo-img" src="/resources/laci-logo.png" />
            </li>
            <li><a class="menu-link" href="/shop">Shop</a><span class="menu-dot">.</span></li>
            <li><a class="menu-link active" href="/contact">Contact</a><span class="menu-dot">.</span></li>
            <li><a class="menu-link" href="/login">Log in/Sign up</a></li>
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

    <!--start - contact-->
    <section class="row h-contact contact" id="contact">
        <div class="col-half">
            <div class="section-heading"><h3>Having trouble? Send us an e-mail</h3></div>
            <form action="/contact/" method="POST" accept-charset="UTF-8">
                <div class="form-field select-wrapper">
                    <select required name="contact_reason">
                        <option selected="true" disabled="disabled">Contact Reason</option>
                        <option>Technical Support</option>
                        <option>Question</option>
                        <option>Sales</option>
                    </select>
                </div>
                <div class="form-field">
                    <input required type="text" name="subject" placeholder="Subject :" />
                </div>
                <div class="form-field">
                    <input required type="text" name="reply_to_address" placeholder="Your Email :" />
                </div>
                <div class="form-field">
                    <textarea required name="message" placeholder="Message :" cols="50" rows="15"></textarea>
                </div>
                <div class="form-button">
                    <input type="submit" value="Send >"/>
                </div>
            </form>

        </div>
        <div class="col-half">
            <div class="section-heading"><h3>Or follow us on:</h3></div>
            <div class="social-icons-large">
                <!-- base64: empty image, css fills it -->
                <img id="sprite-twitter" src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" /><br/><br />
                <img id="sprite-facebook" src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" /><br /><br />
                <img id="sprite-youtube" src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" /><br /><br />
            </div>
            <div class="youtube-embed-container">
                <div class="youtube-embed-inner">
                    <iframe width="488" height="397" src="https://www.youtube.com/embed/FrG4TEcSuRg" frameborder="0" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </section>
    <div class="clear"></div>

    <!--start - footer-->
    <footer class="footer-wrapper">
        <div class="footer-leftbox">
            <span class="text-uppercase bold-font">LaciCloud</span><br />
            <span class="text-italic">Made with love</span><br />
            <span class="text-italic">Brussels, Belgium  - <a href="mailto:laci@lacicloud.net">laci@lacicloud.net</a></span>
        </div>
        <div class="footer-social">
            <a href="#" class="icon tw"><img src="/resources/social-twitter.png" /></a>
            <a href="#" class="icon fb"><img src="/resources/social-facebook.png" /></a>
            <a href="#" class="icon yt"><img src="/resources/social-youtube.png" /></a>
        </div>
    </footer>
    <!--scripts-->
    <script src="/js/main.js"></script>
</body>

</html>