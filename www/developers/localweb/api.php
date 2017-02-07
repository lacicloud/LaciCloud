<?php
require("../functions.php");

$lacicloud_api = new LaciCloud();
$lacicloud_errors_api = new Errors();
$lacicloud_ftp_api = new FTPActions();
$lacicloud_payments_api = new Payments();
$lacicloud_api_api = new API();

//Simple POST API for LaciCloud

$dbc = $lacicloud_api -> getMysqlConn();
$dbc_ftp = $lacicloud_api -> getFtpMysqlConn();

$api_key = @$_POST["api_key"];

//check if key is from the help PDF 
if ($api_key == $lacicloud_api->grabSecret("api_key_tutorial")) {
	header("Location: https://media.makeameme.org/created/NICE-TRY.jpg");
	die(1);
}
	
$result = $lacicloud_api_api -> verifyAPIKey($api_key, $dbc);

if (!is_array($result)) {
	echo $lacicloud_api_api -> returnJSONObject($result, ($lacicloud_errors_api->getSuccessOrErrorFromID($result) == "success") ? true: false );
	die(1);
}

$id = $result["id"];
//API 

//success msg ID values are hardcoded!

if(isset($_POST["action"]) and $_POST["action"] == "addftpuser" and isset($_POST["ftp_username"]) and isset($_POST["ftp_password"]) and isset($_POST["ftp_space"]) and isset($_POST["ftp_space_currency"]) and isset($_POST["starting_directory"])) {

    $result = $lacicloud_ftp_api -> addFTPUser($_POST["ftp_username"], $_POST["ftp_password"], $_POST["ftp_space"], $_POST["starting_directory"] ,$_POST["ftp_space_currency"] , $id, $dbc, $dbc_ftp); 

    echo $lacicloud_api_api -> returnJSONObject($result, ($lacicloud_errors_api->getSuccessOrErrorFromID($result) == "success") ? true: false );

} elseif (isset($_POST["action"]) and $_POST["action"] == "removeftpuser" and isset($_POST["ftp_username"])) {

    $result = $lacicloud_ftp_api -> removeFTPUser($_POST["ftp_username"], $id, $dbc, $dbc_ftp);

    echo $lacicloud_api_api -> returnJSONObject($result, ($lacicloud_errors_api->getSuccessOrErrorFromID($result) == "success") ? true: false );

} elseif (isset($_POST["action"]) and $_POST["action"] == "getuservalues") {

    $result = $lacicloud_ftp_api -> getUserValues($id, $dbc);

    if (is_array($result)) {
        echo json_encode($result);
    } else {
        echo $lacicloud_api_api -> returnJSONObject($result, ($lacicloud_errors_api->getSuccessOrErrorFromID($result) == "success") ? true: false );
    }


} elseif (isset($_POST["action"]) and $_POST["action"] == "getftpusersvalues") {

    $result = $lacicloud_ftp_api -> getFTPUsersValues($id, $dbc_ftp);

    if (is_array($result)) {
        echo json_encode($result);
    } else {
        echo $lacicloud_api_api -> returnJSONObject($result, ($lacicloud_errors_api->getSuccessOrErrorFromID($result) == "success") ? true: false );
    }

    
} elseif (isset($_POST["action"]) and $_POST["action"] == "getftpuserslist") {
    $result = $lacicloud_ftp_api -> getFTPUsersList($id, $dbc_ftp);

    if (is_array($result)) {
        echo json_encode($result);
    } else {
        echo $lacicloud_api_api -> returnJSONObject($result, ($lacicloud_errors_api->getSuccessOrErrorFromID($result) == "success") ? true: false );
    }

} elseif (isset($_POST["action"]) and $_POST["action"] == "getftpusersusedspace") {
    $result = $lacicloud_ftp_api -> getFTPUsersUsedSpace($id, $dbc);

    if (is_int($result)) {
        echo json_encode($result);
    } else {
        echo $lacicloud_api_api -> returnJSONObject($result, ($lacicloud_errors_api->getSuccessOrErrorFromID($result) == "success") ? true: false );
    }

} elseif (isset($_POST["action"]) and $_POST["action"] == "getftpusersvirtuallyusedspace") {
    $result = $lacicloud_ftp_api -> getFTPUsersVirtuallyUsedSpace($id, $dbc);

    if (is_int($result)) {
        echo json_encode($result);
    } else {
        echo $lacicloud_api_api -> returnJSONObject($result, ($lacicloud_errors_api->getSuccessOrErrorFromID($result) == "success") ? true: false );
    }

} elseif (isset($_POST["action"]) and $_POST["action"] == "canchangetotier" and isset($_POST["tier"])) {
    $result = $lacicloud_api -> canChangeToTier($_POST["tier"], $id, $dbc, $dbc_ftp);

    echo $lacicloud_api_api -> returnJSONObject($result, ($lacicloud_errors_api->getSuccessOrErrorFromID($result) == "success") ? true: false );

} elseif (isset($_POST["action"]) and $_POST["action"] == "getierinfo" and isset($_POST["tier"])) {
    echo json_encode($lacicloud_api -> getTierData($_POST["tier"])); //just return array of current LaciCloud tiers
} else {
    //a very useful function
    $result = $lacicloud_api_api -> getNotEnoughParametersSuppliedErrorID(); 
    
    echo $lacicloud_api_api -> returnJSONObject($result, ($lacicloud_errors_api->getSuccessOrErrorFromID($result) == "success") ? true: false );

}

@$lacicloud_api -> blowUpMysql($dbc, $dbc_ftp); 
?>