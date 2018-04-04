<?php 
require("../functions.php");
$lacicloud_errors_api = new Errors();
$lacicloud_api = new LaciCloud();

if (isset($_POST["contact_reason"]) and isset($_POST["subject"]) and isset($_POST["message"]) and isset($_POST["reply_to_address"]) and isset($_POST["captcha_code"])) {
    $result = $lacicloud_api->sendContactEmail($_POST["contact_reason"], $_POST["subject"], $_POST["message"], $_POST["reply_to_address"], $_POST["captcha_code"]);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>LaciCloud - Contact</title>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <meta name="description" content="Contact LaciCloud, the FTP(s)-based cloud storage that is very customizable and privacy-centric" />
    <meta name="keywords" content="bitcoin, FTP, FTPS, cloud, cloud-storage, backup, privacy, private, encryption, security, customizable, secure, LaciCloud, contact, support" />
    <meta name="author" content="Laci, Tristan, Fabio">
    <meta name="language" content="english"> 

    <link rel="author" href="https://plus.google.com/115512170582216368374"/>
    <link rel="help" href="/resources/lacicloud_help.pdf">

    <meta property="og:title" content="LaciCloud - Secure FTP(s) Cloud Storage - Contact"/>
    <meta property="og:url" content="https://lacicloud.net"/>
    <meta property="og:image" content="https://lacicloud.net/resources/logo.png"/>
    <meta property="og:image:type" content="image/png">
    <meta property="og:description" content="Contact LaciCloud, the FTP-based cloud storage that is very customizable and privacy-centric"/>
    <meta property="og:locale" content="en_US" />

    <meta property="twitter:title" content="LaciCloud - Secure FTP(s) Cloud Storage - Contact"/>
    <meta property="twitter:url" content="https://lacicloud.net"/>
    <meta property="twitter:image" content="https://lacicloud.net/resources/logo.png"/>
    <meta property="twitter:description" content="Contact LaciCloud, FTP(s)-based cloud storage that is very customizable and privacy-centric"/>

    <link rel="image_src" href="/resources/logo.png"/>

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
                <img class="logo-img" src="/resources/laci-logo.png" alt="LaciCloud rocket logo"/>
            </li>
            <li><a class="menu-link" href="/shop">Shop</a><span class="menu-dot">.</span></li>
            <li><a class="menu-link active" href="/contact">Contact</a><span class="menu-dot">.</span></li>
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

    <!--start - contact-->
    <section class="row h-contact contact" id="contact">
        <div class="col-half">
            <div class="section-heading"><h1>Having trouble? Send us an e-mail</h1></div>

            <div class="success"></div>
            <div class="error"></div>
            <div class="warning"></div>
            <div class="info"></div>

            <form action="/contact/" method="POST" accept-charset="UTF-8" onsubmit="return validateContactEmail(this);">
                <div class="form-field select-wrapper">
                    <select required name="contact_reason">
                        <option selected="true" disabled="disabled">Contact Reason</option>
                        <option>Technical Support/API</option>
                        <option>Question</option>
                        <option>Sales</option>
                        <option>Feedback</option>
                        <option>Abuse</option>
                        <option>Discounts</option>
                        <option>Other</option>
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
                <div class="form-field">
                     <?php echo '<img src="/securimage_captcha/securimage_show.php?no_cache='.bin2hex(openssl_random_pseudo_bytes(4)).'"'.' alt="CAPTCHA Image"/>'; ?>
                     <input type="text" class="form-control" autocomplete="off" name="captcha_code" size="10" maxlength="6" placeholder="Captcha :" required/>
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
                <img onclick="window.open('https://twitter.com/lacicloud', '_blank');" id="sprite-twitter" src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" alt="Twitter icon"/><br/><br />
                <img onclick="window.open('https://www.facebook.com/lacicloudhosting/', '_blank');" id="sprite-facebook" src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" alt="Facebook icon"/><br /><br />
                <img onclick="window.open('https://www.youtube.com/channel/UC6cwh-kIj7aq4XoiRkzVSug', '_blank');" id="sprite-youtube" src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" alt="YouTube icon"/><br /><br />
            </div>
            <div class="youtube-embed-container">
                <div class="youtube-embed-inner">
                    <iframe width="535" height="397" src="https://www.youtube.com/embed/dtTJgc6Vroo" frameborder="0" allowfullscreen></iframe>
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
            <a href="https://twitter.com/lacicloud" target="_blank" class="icon tw"><img src="/resources/social-twitter.png" alt="Twitter icon"/></a>
            <a href="https://www.facebook.com/lacicloudhosting/" target="_blank" class="icon fb"><img src="/resources/social-facebook.png" alt="FaceBook icon"/></a>
            <a href="https://www.youtube.com/channel/UC6cwh-kIj7aq4XoiRkzVSug" target="_blank" class="icon yt"><img src="/resources/social-youtube.png" alt="FaceBook icon"/></a>
        </div>
    </footer>
    <!--scripts-->
    <script src="/js/main.js"></script>

<script>
var success = document.getElementsByClassName("success")[0];
var error = document.getElementsByClassName("error")[0];
var info = document.getElementsByClassName("info")[0];
var warning = document.getElementsByClassName("warning")[0];

<?php 

if (isset($result)) {
    $message = $lacicloud_errors_api -> getErrorMsgFromID($result);
    $result =  $lacicloud_errors_api -> getSuccessOrErrorFromID($result);
    echo "".$result.".innerHTML='".$message."'";
    echo "\n";
    echo "".$result.".style.display = 'block';";
}

//reset POST array to prevent duplicate email
$_POST[] = array();

?>

</script>

</body>

</html>
