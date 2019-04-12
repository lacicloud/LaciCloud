<?php
require("../functions.php");

$lacicloud_api = new LaciCloud();
$lacicloud_api -> isPageCached();
?>
<!--
Author: W3layouts
Author URL: http://w3layouts.com
License: Creative Commons Attribution 3.0 Unported
License URL: http://creativecommons.org/licenses/by/3.0/
-->
<!DOCTYPE HTML>
<html>
<head>
<title>LaciCloud - 404 - That page cannot be found!</title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta charset="utf-8">
<link rel="stylesheet" type="text/css" href="/css/error.css">
</head>
<body>
<div class="wrap">
	<img style="padding-top: 3%; padding-left: 2%; -webkit-transform: scale(1.5); -moz-transform: scale(1.5); -o-transform: scale(1.5);" src="/resources/logo.png"  alt="LaciCloud full logo with rocket" />
	<div class="banner">
		<img src="/resources/404_banner.png" alt="404 - Not Found image of a man holding up a sign that says 404" />
	</div>
	<div class="page">
		<h2>We cannot find that page... Damn!</h2>
		<h3>Press any key to continue...</h3>
		<h4>Or click <a style="text-decoration: underline;" onClick="history.go(-1);" href="#">here</a> to go back!</h4>
	</div>
	<div class="footer">
		<p>Design by <a href="http://w3layouts.com">w3layouts</a></p>
	</div>
</div>

<script>

document.onkeypress = function (e) {
    e = e || window.event;
    	window.history.go(-1);
};

</script>

</body>
</html>
