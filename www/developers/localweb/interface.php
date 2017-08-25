<?php 
require('../functions.php');

$lacicloud_api = new LaciCloud();
$lacicloud_ftp_api = new FTPActions();
$lacicloud_payments_api = new Payments();
$lacicloud_errors_api = new Errors();
$lacicloud_utils_api = new Utils();
$lacicloud_webhosting_api = new Webhosting;

//start gzipper
if(!$lacicloud_utils_api->getBrowserName() == 'Internet Explorer' and substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
    ob_start('ob_gzhandler');
}

if ($lacicloud_errors_api -> getSuccessOrErrorFromID($lacicloud_api -> startSession()) !== "success") {
  header("Location: /account");
  die(1);
}


//checks if user is logged in at all and then verifies UA, IP
if (!isset($_SESSION["logged_in"]) or $_SESSION["logged_in"] != 1) {
    $lacicloud_errors_api -> msgLogger("LOW", "Not-logged in access on /interface.php...", 2);
    header("Location: /account");
    die(1);
}

if ($lacicloud_errors_api -> getSuccessOrErrorFromID($lacicloud_api->verifyShit()) !== "success") {
    $lacicloud_api -> blowUpSession();
    header("Location: /account");
    die(1);
}

$dbc = $lacicloud_api -> getMysqlConn();
$dbc_ftp = $lacicloud_api -> getFtpMysqlConn();

$id = $_SESSION["id"];   

//check CSRF before executing action
if (isset($_POST["action"]) or isset($_GET["action"])) {

   $user_supplied_token = (isset($_GET["action"]) ? $_GET["csrf_token"] : $_POST["csrf_token"]);

    if ($lacicloud_errors_api -> getSuccessOrErrorFromID($lacicloud_api -> verifyCSRF($user_supplied_token)) !== "success") {
        $lacicloud_api -> blowUpSession();
        header("Location: /account");
        die(1);
    }
}

//execute actions
if(isset($_POST["action"]) and $_POST["action"] == "addftpuser" and isset($_POST["ftp_username"]) and isset($_POST["ftp_password"]) and isset($_POST["ftp_space"]) and isset($_POST["starting_directory"]) and isset($_POST["ftp_space_currency"])) {

    $result = $lacicloud_ftp_api -> addFTPUser($_POST["ftp_username"], $_POST["ftp_password"], $_POST["ftp_space"], $_POST["starting_directory"] ,$_POST["ftp_space_currency"] , $id, $dbc, $dbc_ftp); 

} elseif (isset($_GET["action"]) and $_GET["action"] == "removeftpuser" and isset($_GET["ftp_username"])) {

    $result = $lacicloud_ftp_api -> removeFTPUser($_GET["ftp_username"], $id, $dbc, $dbc_ftp);

} elseif (isset($_GET["action"]) and $_GET["action"] == "regenerateapikey") {

    $result = $lacicloud_ftp_api -> regenerateAPIKey($id, $dbc);

} elseif (isset($_POST["action"]) and $_POST["action"] == "addwebhostingenv") {

    $result = $lacicloud_webhosting_api->addWebhostingEnv($id, $_POST["sitename"], $lacicloud_webhosting_api->generateMysqlUsername(), $lacicloud_webhosting_api->generateMysqlPassword(), $dbc);

} elseif (isset($_GET["action"]) and $_GET["action"] == "resetmysql") {

    $result = $lacicloud_webhosting_api-> resetWebhostingEnvMysql($id, $lacicloud_webhosting_api->getWebhostingValues($id, $dbc)["sitename"], $lacicloud_webhosting_api->getWebhostingValues($id, $dbc)["mysql_username"], $dbc);

} elseif (isset($_GET["action"]) and $_GET["action"] == "resetperms") {

    $result = $lacicloud_webhosting_api-> resetWebhostingEnvPermissions($id, $lacicloud_webhosting_api->getWebhostingValues($id, $dbc)["sitename"], $dbc);
    
    
} elseif (isset($_GET["action"]) and $_GET["action"] == "logout") {
    $lacicloud_api -> blowUpSession();
    header("Location: /account");
    die(1);
}


//load vars into session array so that a mysql query doesn't have to be opened every time a user clicks a link
//if action done (result), update user values, or if the refresh GET parameter is set
if (@!$_SESSION["user_values_set"] or isset($result) or isset($_GET["refresh"])) {
    $user_values = $lacicloud_ftp_api->getUserValues($id, $dbc);
    $ftp_users_used_bandwidth = $lacicloud_ftp_api -> getUsedBandwidth($id, $dbc);
    $ftp_users_list = $lacicloud_ftp_api->getFTPUsersList($id, $dbc_ftp);
    $ftp_users_values = $lacicloud_ftp_api->getFTPUsersValues($id, $dbc_ftp);
    $ftp_users_used_space = $lacicloud_ftp_api -> getFTPUsersUsedSpace($id, $dbc);
    $ftp_users_used_space_virtual = $lacicloud_ftp_api -> getFTPUsersVirtuallyUsedSpace($id, $dbc_ftp);
    $webhosting_values = $lacicloud_webhosting_api->getWebhostingValues($id, $dbc);

    $_SESSION["ftp_users_list"] = $ftp_users_list;
    $_SESSION["ftp_users_values"] = $ftp_users_values;
    $_SESSION["ftp_users_used_space"] = $ftp_users_used_space;
    $_SESSION["ftp_users_used_bandwidth"] = $ftp_users_used_bandwidth;
    $_SESSION["ftp_users_used_space_virtual"] = $ftp_users_used_space_virtual;
    $_SESSION["webhosting_values"] = $webhosting_values;

    $_SESSION["tier"] = $user_values["tier"];
    $_SESSION["sitename"] = $user_values["sitename"];
    $_SESSION["lastpayment"] = $user_values["lastpayment"];
    $_SESSION["first_time_boolean"] = $user_values["first_time_boolean"];
    $_SESSION["api_key"] = $user_values["api_key"];
    $_SESSION["user_values_set"] = true;


} 

if ($_SESSION["user_values_set"]) {
    $tier = $_SESSION["tier"];
    $sitename = $_SESSION["sitename"];
    $lastpayment = $_SESSION["lastpayment"];
    $first_time_boolean = $_SESSION["first_time_boolean"];
    $api_key = $_SESSION["api_key"];

    $ftp_users_list = $_SESSION["ftp_users_list"];
    $ftp_users_values = $_SESSION["ftp_users_values"];

    //for logout link
    $csrf_token = $_SESSION["csrf_token"];  

    $ftp_space = $lacicloud_api -> getTierData($tier)[0];
    $limit = $lacicloud_api -> getTierData($tier)[1];

    $ftp_space_user_has = $lacicloud_api -> getTierData($tier)[0] -  $_SESSION["ftp_users_used_space"];
    $ftp_space_user_has_virtual = $lacicloud_api -> getTierData($tier)[0] -  $_SESSION["ftp_users_used_space_virtual"];

    $used_bandwidth = $_SESSION["ftp_users_used_bandwidth"];
    $total_bandwidth = $lacicloud_api -> getTierData($tier)[5];

    $webhosting_values = $_SESSION["webhosting_values"];
}


//checks if first time and if it is take action
if ($first_time_boolean == 0) {
    $lacicloud_api -> firstTimeSetup($id, $dbc); 
    $_SESSION["first_time_boolean"] = 1;       
}
        


//session expiration
if (isset($_SESSION['FIRST_ACTIVITY']) && (time() - $_SESSION['FIRST_ACTIVITY'] > 1800)) {
    $lacicloud_api -> blowUpSession();
    header("Location: /account");
    die(0);
} elseif (!isset($_SESSION["FIRST_ACTIVITY"])) {
    $_SESSION['FIRST_ACTIVITY'] = time(); //first activity timestamp
}

//if not a valid page display main screen
if (empty($_GET["id"]) or !in_array($_GET["id"], $lacicloud_api->valid_pages_array)) {
    $_GET["id"] = "0";
}


?>
<!doctype html>

<html>

<head>

<meta charset="utf-8">

<title>LaciCloud - Interface</title>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1" />

<!-- we don't need more meta tags here as this page will redirect to the account page in case it is linked -->

<link rel="icon" type="image/png" href="/resources/favicon-32x32.png">

<link rel="stylesheet" type="text/css" href="/css/interface.css">
</head>


<body>
<img src="/resources/ui_bg.jpg" id="ui_background" alt="Background image of the interface page, a famous building on Brussel's Grand Place, in blue/violet colors"> 
<div id="layout">
<!-- Menu toggle -->
    <a href="#menu" id="menuLink" class="menu-link">
        <span></span>
    </a>

    <div id="menu" class="">
        <div class="pure-menu">
            <a class="pure-menu-heading" href="#">LaciCloud UI</a>
            <ul class="pure-menu-list">
                <li class="pure-menu-item"><a rel="canonical" href="/interface" class="pure-menu-link">Main</a></li>
                <li class="pure-menu-item"><a rel="canonical" href="/interface?id=1" class="pure-menu-link">User Manager</a></li>
                <li class="pure-menu-item"><a rel="canonical" href="/interface?id=2" class="pure-menu-link">Payment Manager</a></li>
                <li class="pure-menu-item"><a rel="canonical" href="/interface?id=3" class="pure-menu-link">API Manager</a></li>
                <li class="pure-menu-item"><a rel="canonical" href="/ftp" target="_blank" class="pure-menu-link">Monsta FTP</a></li>
                <li class="pure-menu-item"><a rel="canonical" href="/interface?id=4" class="pure-menu-link">Webhosting</a></li>
                <li class="pure-menu-item"><a rel="canonical" href="https://stats.uptimerobot.com/r8N9QIrq1" class="pure-menu-link">Status Page</a></li>
                <li class="pure-menu-item"><a rel="canonical" href="/resources/lacicloud_help.pdf" target="_blank" class="pure-menu-link">Help</a></li>
                <li class="pure-menu-item"><a rel="canonical" href="#" class="pure-menu-link"></a></li>
                <li class="pure-menu-item"><a rel="canonical" href="#" class="pure-menu-link"></a></li>
                <li class="pure-menu-item"><a rel="canonical" href=<?php echo "/interface?action=logout&csrf_token=".$csrf_token;?> class="pure-menu-link">Logout</a></li>
            </ul>
        </div>

</div>

<div id="ui">
	

<?php if ($_GET["id"] == "0") { ?>
		<section id="ui_normal">
			<div class="panel panel-success">

	   		<div class="panel-heading">

	     	<h3 class="panel-title">LaciCloud Control Panel</h3>

	    	</div>

		    	<div class="panel-body"> 
			    	<h2>Main</h2>

                    <div style="display: block;" class="warning" id="noscript">For optimal experience please enable JavaScript! Disabled JS is not supported and may lead to bad experience!</div>

                    <?php if ($first_time_boolean == 0) {
                            echo "<div class='info' style='display: block;'>Hi! We have detected it's your first time logging in. It is <strong>REALLY</strong> recommended you read the <strong><a rel='canonical' target='_blank' href='/resources/lacicloud_help.pdf'>help</a></strong>!</div>";
                    } ?>
                   
			    
			        <p>Tier: <span class="green"><?php echo $tier;?></span> </p>

			        <p>FTP storage space: <span class="green"><?php echo $ftp_space; ?>MB</span></p>

                    <p>FTP Users: <span class="green"><?php echo $limit;?></span></p>


                    <?php if ($tier !== 1 and isset($sitename)) {  ?>
                        <p>Your subdomain: <span class="green"><?php echo $sitename.".lacicloud.net";?></span></p>
                    <?php } ?>
                    
                    <br>
                    <br>

			        <p>Connection details: <span class="green">Secure (HTTPS)</span></p>

			        <?php echo "<p>Your user ID is: <span class='green'>".$id."</span></p>" ?>

		    	</div>

			</div>

	    
        </section>


<?php } elseif ($_GET["id"] == "1") { ?>
		<section id="ui_normal">
			<div class="panel panel-success">

	   		<div class="panel-heading">

	     	<h3 class="panel-title">LaciCloud Control Panel</h3>

	    	</div>

		    	<div class="panel-body users_page"> 
		    		 <h2>User Manager</h2>

                    <div class="success"></div>
                    <div class="warning"></div>
                    <div class="error"></div>
                    <div class="info"></div>
		          
                     <p>Active Users: <span class="green"><?php echo count($ftp_users_list);?></span> Out of <span class="green"><?php echo $limit; ?></span></p>

                      <p>Space available: <span class="green"><?php echo $ftp_space_user_has;?>MB</span> Out of <span class="green"><?php echo $ftp_space ?>MB</span></p> </p>

                      <p>Virtual space available: <span class="green"><?php echo $ftp_space_user_has_virtual;?>MB</span> Out of <span class="green"><?php echo $ftp_space ?>MB</span></p>  

                      <p>Used bandwidth: <span class="green"><?php echo $used_bandwidth;?>MB</span> Out of <span class="green"><?php echo $total_bandwidth; ?>MB</span></p>

                      <p><a href="?id=1&refresh=">Refresh Variables</a> </p>  
                     
                     <div class="panel panel-default users_list">
                     <div class="panel-body">
                    <?php
                        //not too proud of this one, but it works
                        //do not touch please
                        $position = 0;
                        $not_set_position = 0;

                        if (isset($_GET["li"]) and (int)$_GET["li"] > 0.0 and (int)is_numeric($_GET["li"]) and (int)($_GET["li"]) % 5 == 0) {
                            $start_at = (int)$_GET["li"];
                        }
                        else {
                            $start_at = 0;
                        }

                        foreach($ftp_users_list as $key => $print_them_out) {
                            if ($key < $start_at) {
                                continue;
                            }

                            if ($position >= 5) {
                                break;
                            }

                            $position++;
                            echo "<p><span class='green'>" . $print_them_out . "</span><span> <a rel='canonical' onclick='return ValidateUserRemove()' href='/interface?id=1&action=removeftpuser&ftp_username=$print_them_out&csrf_token=".$csrf_token."'>Remove</a></span><span>&nbsp;<a href='/ftp/?ftp_username=$print_them_out' target='_blank'>Monsta FTP</a></span>";
                        }

                        $number_of_inactive_users = ($limit - $position);

                        while ($number_of_inactive_users != 0) {                
                              $not_set_position++;

                              if ($start_at + $not_set_position > $limit) {
                                echo "<br><a class='btn btn-default btn_previous_page' href='/interface?id=1&li=".($start_at - 5)."'>Previous Page</a>";
                                break;
                              }

                              if ($position - 1 + $not_set_position >= 5) {
                                echo "<br><a class='btn btn-default btn_next_page' href='/interface?id=1&li=".($start_at + 5)."'>Next Page</a>";
                                if ($start_at !== 0) {
                                     echo "<a class='btn btn-default btn_previous_page' href='/interface?id=1&li=".($start_at - 5)."'>Previous Page</a>";
                                }
                               
                                break;

                        }      


                              echo "<p><span class='red'>NOT SET</span>";

                              $number_of_inactive_users = $number_of_inactive_users - 1;  
                        }

                    ?>
                    </div>
                    </div>
         
                    <a class="btn btn-default" style="float: right;" rel="canonical" href="/interface?id=1_1">Add User</a>

		    	</div>

			</div>

	    
        </section>


<?php } elseif ($_GET["id"] == "1_1") { ?>

        <section id="ui_form">
            <div class="panel panel-success">

            <div class="panel-heading">

            <h3 class="panel-title">LaciCloud Control Panel</h3>

            </div>

                <div class="panel-body add_users_page"> 

                    <h2>Add FTP User</h2>

                    <div class="success"></div>
                    <div class="warning"></div>
                    <div class="error"></div>
                    <div class="info"></div>
                    
                    <form class="form-horizontal" action="/interface/?id=1_1" onsubmit='return ValidateAddFTPUser(this);' method="POST" accept-charset="UTF-8">

                    <div class="form-group">
                    <input type="hidden" name="action" value="addftpuser">

                    <input type="hidden" name="csrf_token" value=<?php  echo $csrf_token ?> >

                    <input type="hidden" disabled="disabled" name="limit" value=<?php echo $limit; ?> >
                    <input type="hidden" disabled="disabled" name="active_users" value=<?php echo count($ftp_users_list); ?> >
                    <input type="hidden" disabled="disabled" name="ftp_space_user_has" value=<?php echo $ftp_space_user_has; ?> >
                    </div>

                    <div class="form-group">
                    <label>FTP Username:</label>
                    <input type="text" name="ftp_username" class="form-control" required placeholder="Required">
                    </div>
                    <div class="form-group">
                    <label>FTP Password:</label>
                    <input type="password" autocomplete="off" name="ftp_password" class="form-control" placeholder="Only required if master account">
                    </div>
                    <div class="form-group">
                    <label>FTP Starting Directory:</label>
                    <input type="text" name="starting_directory" class="form-control" required placeholder="Required">
                    </div>
                    <div class="form-group">
                    <label>FTP Space:</label>
                    <input type="text" name="ftp_space" class="form-control" required placeholder="Required">
                    <select class="form-control" name="ftp_space_currency">
                                  <option value="mb">MB</option>
                                  <option value="gb">GB</option>
                                  <option value="tb">TB</option>
                    </select>
                    </div>

                  
                    <input class="btn btn-info" type="submit" name="submit" value="Go!">

                    </form>


                    <a class="btn btn-default" style="float: left;" rel="canonical" href="/interface?id=1">Back</a>

                </div>

            </div>

        </section>

<?php } elseif ($_GET["id"] == "2") { ?>
        <section id="ui_normal">
                <div class="panel panel-success">

                <div class="panel-heading">

                <h3 class="panel-title">LaciCloud Control Panel</h3>

                </div>

                    <div class="panel-body"> 

                        <h2>Payment Manager</h2>

                        <p>Tier Package: <span class="green"><?php echo $tier; echo $lacicloud_api -> getTierData($tier)[4]; ?></span></p>

                       
                        <p>Payment Status: 
                        <?php 
                        if ((time() - $lastpayment) > $lacicloud_api->unix_time_1_year and $tier != 1) {
                            echo "<span class='red'>Renew Plan</span>";
                        } else {
                            echo "<span class='green'>OK</span>";
                        }
                        ?></p>
  
                        <a class="btn btn-default" style="float: left;" rel="canonical" href="/shop">Change/Renew Plan</a>

                    </div>

                </div>

        </section>


<?php } elseif ($_GET["id"] == "3") { ?>
        <section id="ui_normal">
                <div class="panel panel-success">

                <div class="panel-heading">

                <h3 class="panel-title">LaciCloud Control Panel</h3>

                </div>

                    <div class="panel-body"> 

                        <h2>API Manager</h2>

                        <div class="success"></div>
                        <div class="warning"></div>
                        <div class="error"></div>
                        <div class="info"></div>

                        <?php 
                        echo "<br>";
                        echo "Your current API key is <span class='green' style='word-wrap: break-word;'>".$api_key."</span>"; 
                        echo "<br>";
                        echo "<br>";
                        echo "In order to regenerate your API key, please click <a rel='canonical' onclick='return ValidateAPIKeyRegenerate()' href='/interface?id=3&action=regenerateapikey&csrf_token=".$csrf_token."'>here</a>.";
                        ?>


                        

                    </div>

                </div>

        </section>
<?php } elseif ($_GET["id"] == "4") { ?>
        <section id="ui_normal">
                <div class="panel panel-success">

                <div class="panel-heading">

                <h3 class="panel-title">LaciCloud Control Panel</h3>

                </div>

                    <div class="panel-body"> 

                        <h2>Webhosting</h2>

                        <div class="success"></div>
                        <div class="warning"></div>
                        <div class="error"></div>
                        <div class="info"></div>

                        <?php 
                        if ($tier !== 1 and isset($sitename)) {
                            echo "Your subdomain: ".$webhosting_values["sitename"].".lacicloud.net";
                            echo "<br>";
                            echo "MySql host: localhost";
                            echo "<br>";
                            echo "MySql username: ".$webhosting_values["mysql_username"];
                            echo "<br>";
                            echo "MySql password: ".$webhosting_values["mysql_password"];
                            
                            echo "<br><br>";

                            echo "<a href='/interface?id=4&action=resetmysql&csrf_token=".$csrf_token."'>Reset MySql password</a>";
                            echo "<br>";
                            echo "<a href='/interface?id=4&action=resetperms&csrf_token=".$csrf_token."'>Reset permissions</a>";
                            echo "<br>";
                            echo "<a onClick='customDomainFunction();' href='#'>Have a domain?</a>";
                        } elseif ($tier == 2 or $tier == 3 and !isset($sitename))   {
                            echo "Thank you for upgrading a tier! Please fill out the form below to get your webhosting environment set-up.";
                            ?>
                            <form class="form-horizontal" action="/interface/?id=4" onsubmit='return ValidateSitename(this);' method="POST" accept-charset="UTF-8">

                            <div class="form-group">
                            <input type="hidden" name="action" value="addwebhostingenv">
                            <input type="hidden" name="csrf_token" value=<?php  echo $csrf_token ?> >
                            </div>

                            <div class="form-group">
                            <label>Sitename:</label>
                            <input type="text" name="sitename" class="form-control" required placeholder="Required">
                            </div>
                          
                            <input class="btn btn-info" type="submit" name="submit" value="Go!">

                            </form>

                            <?php 
                        } else {
                            echo "Please upgrade to tier 2 or 3 to activate webhosting functions. You won't regret it!";
                        }

                        ?>


                        

                    </div>

                </div>

        </section>

<?php }?>
</div>

<!-- scripts -->
<script type="text/javascript" src="/js/main.js"></script>

<script>


<?php 
//bugfix for landscape orientation on the User Manager and Add FTP user page 

if ($_GET["id"] == "1" or $_GET["id"] == "1_1") {
    echo '
//run once when site loads
if (/Mobi/.test(navigator.userAgent)) {
    if(window.innerWidth > window.innerHeight){
        alert("This site is best viewed in Portrait mode... Please turn your device!");
    }
}

//run when orientation changes
$(window).on("orientationchange",function(event){
  if(window.orientation !== 0) {
    alert("This site is best viewed in Portrait mode... Please turn your device!");
  }
});
';

}


?>

function ui_load() {
    HideNoScript();
    sidebar_active();

    var success = document.getElementsByClassName("success")[0];
    var error = document.getElementsByClassName("error")[0];
    var info = document.getElementsByClassName("info")[0];
    var warning = document.getElementsByClassName("warning")[0];

    $('input, textarea').placeholder(); //IE placeholder text 

    <?php 

    if (isset($result)) {
    $message = $lacicloud_errors_api -> getErrorMsgFromID($result);
    $result =  $lacicloud_errors_api -> getSuccessOrErrorFromID($result);
    
    echo "".$result.".innerHTML='".$message."';";
    echo "\n";
    echo "".$result.".style.display = 'block';";

    }

?>

}

window.onload = ui_load();

</script>

</body>


</html>

<?php 

@$lacicloud_api -> blowUpMysql($dbc, $dbc_ftp); 

?>
