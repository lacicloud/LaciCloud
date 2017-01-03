<?php
/**
 *  ... Please MODIFY this file ... 
 *
 *
 *  YOUR MYSQL DATABASE DETAILS
 *
 */
$secretsfile = "/var/ftp/www/developers/secrets.ini";

define("DB_HOST", 	"localhost");				// hostname
define("DB_USER", 	parse_ini_file($secretsfile)["db_user_gourl"]);		// database username
define("DB_PASSWORD", 	parse_ini_file($secretsfile)["db_password_gourl"]);		// database password
define("DB_NAME", 	"laci_corporations_users");	// database name




/**
 *  ARRAY OF ALL YOUR CRYPTOBOX PRIVATE KEYS
 *  Place values from your gourl.io signup page
 *  array("your_privatekey_for_box1", "your_privatekey_for_box2 (otional), etc...");
 */
 
 $cryptobox_private_keys = array(parse_ini_file($secretsfile)["gourl_private_key_bitcoin"], parse_ini_file($secretsfile)["gourl_private_key_litecoin"], parse_ini_file($secretsfile)["gourl_private_key_dogecoin"], parse_ini_file($secretsfile)["gourl_private_key_potcoin"], parse_ini_file($secretsfile)["gourl_private_key_dashcoin"], parse_ini_file($secretsfile)["gourl_private_key_speedcoin"],);




 define("CRYPTOBOX_PRIVATE_KEYS", implode("^", $cryptobox_private_keys));
 unset($cryptobox_private_keys); 

?>