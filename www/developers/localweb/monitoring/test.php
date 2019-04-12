<?php 

//LaciCloud service-tests

//functions and secret values

$secrets = parse_ini_file("../../secrets.ini");
$username = $secrets["dummy_ftp_user"];
$password = $secrets["dummy_ftp_user_password"];
$api_key = $secrets["monitoring_app_api_key"];

function url_get_contents ($Url) {
    if (!function_exists('curl_init')){ 
        die('CURL is not installed!');
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $Url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function ping($host,$port,$timeout=6)
{
        $fsock = fsockopen($host, $port, $errno, $errstr, $timeout);
        if ( ! $fsock )
        {
                return FALSE;
        }
        else
        {
                return TRUE;
        }
}

function checkWebsite($url) {
     $result = url_get_contents($url);

     if (strpos($result, "<html>")) {
     	return TRUE;
     } else {
     	return FALSE;
     }

}

function testFTPConnection($host, $port, $username, $password) {
	// set up basic connection
	$conn_id = ftp_connect($host, $port);

	// login with username and password
	$login_result = ftp_login($conn_id, $username, $password);
	ftp_pasv($conn_id, true);

	// upload a file
	if (ftp_put($conn_id, "testfile.txt", "testfile.txt", FTP_ASCII)) {
	 ftp_delete($conn_id, "testfile.txt");
	 return TRUE;
	} else {
	 return FALSE;
	}

	// close the connection
	ftp_close($conn_id);

}

function testAPIPart1($url, $api_key, $username, $password) {

	$fields = array(
	'api_key' => urlencode($api_key),
	'action' => urlencode("addftpuser"),
	'ftp_username' => urlencode($username),
	'ftp_password' => urlencode($password),
	'ftp_space' => urlencode("5"),
	'ftp_space_currency' => urlencode("mb"),
	'starting_directory' => urlencode("/dummy")
);

	//url-ify the data for the POST
	$fields_string = http_build_query($fields);
	//open connection
	$ch = curl_init();

	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_POST, count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


	//execute post
	$result = curl_exec($ch);

	//close connection
	curl_close($ch);

	if (json_decode($result, true)['Success'] == false) {
		return FALSE;
	} else {
		return TRUE;
	}

}

function testAPIPart2($url, $api_key, $username) {

	$fields = array(
	'api_key' => urlencode($api_key),
	'action' => urlencode("removeftpuser"),
	'ftp_username' => urlencode($username),
);

	//url-ify the data for the POST
	$fields_string = http_build_query($fields);
	//open connection
	$ch = curl_init();

	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_POST, count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	//execute post
	$result = curl_exec($ch);

	//close connection
	curl_close($ch);

	if (json_decode($result, true)['Success'] == false) {
		return FALSE;
	} else {
		return TRUE;
	}
}

echo "IP: ".gethostbyname("lacicloud.net");
echo "<br>";
echo "IPV6: ".gethostbyname("lacicloud.net");
echo "<br>";
echo "Testing SSH port reachibility...".var_export(ping("lacicloud.net", 8337), true);
echo "<br>";
echo "Testing HTTP website port reachibility... ".var_export(ping("lacicloud.net", 80), true);
echo "<br>";
echo "Testing HTTPS website port reachibility... ".var_export(ping("lacicloud.net", 443), true);
echo "<br>";
echo "Testing FTP port reachibility... ".var_export(ping("lacicloud.net", 21), true);
echo "<br>";
echo "Testing website HTML content...".var_export(checkWebsite("https://lacicloud.net"), true);
echo "<br>";

echo "Testing API (addftpuser)...".var_export(testAPIPart1("https://lacicloud.net/api/",$api_key,$username,$password), true);
echo "<br>";
echo "Testing FTP connection...".var_export(testFTPConnection("lacicloud.net", 21, $username, $password), true);
echo "<br>";
echo "Testing API (removeftpuser)...".var_export(testAPIPart2("https://lacicloud.net/api/",$api_key,$username), true);



?>
