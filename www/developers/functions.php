<?php
//API & Functions

//captcha
require_once(__DIR__.'/localweb/securimage_captcha/securimage.php');

//swiftmailer for sending emails
require_once(__DIR__.'/SwiftMailer/lib/swift_required.php');

//payments
require_once(__DIR__."/MineSQL/CoinPayments.php");
require_once(__DIR__."/PayPal/PaypalIPN.php");
require_once(__DIR__."/Stripe/init.php");

//LaciCloud core functions that directly interact with the database or the server in any way
class LaciCloud {
	
	private $bcrypt_options = [
  		  'cost' => 12, //should be good for a few more years
	];

	private $secrets_file = __DIR__."/secrets.ini";

	//default values to use when creating account

	private $first_time_boolean_default = 0;

	private	$reset_key_default = 0;

	protected $document_root = "/var/ftp";

	public $unix_time_1_year = 31556916;

	protected $usedSpaceDefault = 0;

	protected $usedBandwidthDefault = 0.0;
	
	public $valid_pages_array = ["0","1","1_1","1_2","2","2_1","2_2","3","4"];

	public function grabSecret($name) {
		$secrets = parse_ini_file($this->secrets_file);
		return $secrets[$name];

	}

	public function getMysqlConn() {
		
		$lacicloud_errors_api = new Errors();

		$dbc = mysqli_connect($this->grabSecret("db_host"), $this->grabSecret("db_user"), $this->grabSecret("db_password"), $this->grabSecret("db_name"));
		mysqli_set_charset($dbc , "utf8mb4");


		if (is_null($dbc) or $dbc == false) {
			 $lacicloud_errors_api -> msgLogger("CRIT", 'Could not connect to MySQL server... Connect error: '.mysqli_connect_error($dbc).' Error: '.mysqli_error($dbc), 1);
			 return 1;
		} else {
			return $dbc;
		}

	}

	public function getFtpMysqlConn() {
		$lacicloud_errors_api = new Errors();

		$dbc_ftp = mysqli_connect($this->grabSecret("db_host_ftp"), $this->grabSecret("db_user_ftp"), $this->grabSecret("db_password_ftp"), $this->grabSecret("db_name_ftp"));
		mysqli_set_charset($dbc_ftp , "utf8mb4");


		if (is_null($dbc_ftp) or $dbc_ftp == false) {
			 $lacicloud_errors_api -> msgLogger("CRIT", 'Could not connect to FTP MySQL server... Connect error: '.mysqli_connect_error($dbc_ftp).' Error: '.mysqli_error($dbc_ftp), 1);
			 return 1;
		} else {
			return $dbc_ftp;
		}

	}

	public function startSession() {
		$lacicloud_errors_api = new Errors();

		session_name("secure_session");

		if(!session_start()) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "Could not start session... Session id: ".session_id(), 2);
			return 2;
		}

		session_regenerate_id(true);

		return 3;
	}

	private function validateUserInfo($email, $password, $password_retyped) {
		$lacicloud_errors_api = new Errors();

		if (empty($email) or empty($password)) {
			$lacicloud_errors_api -> msgLogger("LOW", "Email/Password empty when logging in or creating account!", 4);
			return 4;

		}

		if (preg_match('/\s/',$email) or strlen($email) < 5 or strlen($email) > 320 or !strpos($email, "@") or !strpos($email, ".")) {
			$lacicloud_errors_api -> msgLogger("LOW", "Email is invalid... Email: ".$email, 4);
			return 4;
		}

		if ($email == $password) {
			$lacicloud_errors_api -> msgLogger("LOW", "Email same as password...", 4);
			return 4;
		}

		if (strlen($password) < 8 or !preg_match("#[0-9]+#", $password) or !preg_match("#[a-zA-Z]+#", $password)) {
			$lacicloud_errors_api -> msgLogger("LOW", "Password strenght too weak...", 4);
			return 4;
		}


		if ($password !== $password_retyped){
			$lacicloud_errors_api -> msgLogger("LOW", "Password not the same as retyped password", 4);
			return 4; 

		}

		return 5;
	}

	//nice to have
	public function increasePageVisitCounter($dbc) {
			$lacicloud_errors_api = new Errors();

			$query = "UPDATE counter SET count = count + 1";
			$stmt = mysqli_prepare($dbc, $query);
			$result = mysqli_stmt_execute($stmt);

			if (!$result) {
				$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while incrementing index page counter... Error:".mysqli_error($dbc), 1);
			}

			return 7;
	}

	//nice to have
	private function increaseUserLoginCounter($dbc) {
			$lacicloud_errors_api = new Errors();

			$query = "UPDATE counter SET logins = logins + 1";


			$stmt = mysqli_prepare($dbc, $query);

			       
			$result = mysqli_stmt_execute($stmt);

			//non-critical so error is only logged
			if (!$result) {
				$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while incrementing login counter... Error:".mysqli_error($dbc), 1);
			}

			return 9;
	}

	public function loginUser($email, $password, $captcha, $dbc) {
		$lacicloud_errors_api = new Errors();

		//captcha required	  	
		if ($lacicloud_errors_api -> getSuccessOrErrorFromID($this -> checkCaptcha($captcha)) !== "success") {
						return 10;
		}

		//don't waste memory by quering email/password combinations that could not have existed in the first place
		if ($lacicloud_errors_api -> getSuccessOrErrorFromID($this -> validateUserInfo($email, $password, $password)) !== "success") {
			$lacicloud_errors_api -> msgLogger("LOW", "Email/Password not valid when logging in... Email: ".$email, 4);
			return 4;
		}

		$query = "SELECT unique_id,password,email,id FROM users WHERE email=?";
		$stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "s", $email);
		$result = mysqli_stmt_execute($stmt);

		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while retrieving user info during login... Errror: ".mysqli_error($dbc), 1);
		}

		$stmt->bind_result($mysql_unique_id,$mysql_database_password,$mysql_email,$mysql_id);


   	 	while ($stmt->fetch()) {

        	$database_password = $mysql_database_password;

			$user_unique_key = $mysql_unique_id;

			$database_email = $mysql_email;

			$id = $mysql_id;

		}

		if (empty($id)) {
			$lacicloud_errors_api -> msgLogger("LOW", "Wrong Email when logging in... Email: ".$email, 40);
			return 40;
		}


		if (!\password_verify($password, $database_password) or $email !== $database_email) {
			$lacicloud_errors_api -> msgLogger("LOW", "Wrong password when logging in... Id: ".$id, 40);
			return 40;
		}

		if ((int)$user_unique_key !== 1) {
			$lacicloud_errors_api -> msgLogger("LOW", "Account not confirmed when logging in... Id: ".$id, 12);
			return 12;
		}

		$this -> increaseUserLoginCounter($dbc);

		//set login variables
		$_SESSION["logged_in"] = 1;
		$_SESSION["csrf_token"] = bin2hex(openssl_random_pseudo_bytes(16));
		$_SESSION["id"] = $id;

		return 13;

	}

	public function getUserCount($dbc) {
		$query = "SELECT count(*) FROM users";
        $stmt = mysqli_prepare($dbc, $query);
        $result = mysqli_stmt_execute($stmt);

        $stmt->bind_result($mysql_count);

        while ($stmt->fetch()) {

        	$count = $mysql_count;

		}

		return $count;
	}

	public function confirmAccount($unique_key, $ftp_password, $dbc, $dbc_ftp) {
		$lacicloud_api = new LaciCloud();
		$lacicloud_errors_api = new Errors();
		$lacicloud_ftp_api = new FTPActions();
		$lacicloud_utils_api = new Utils();
		$lacicloud_webhosting_api = new Webhosting();

		if (empty($unique_key)) {
			$lacicloud_errors_api -> msgLogger("LOW", "Unique key empty when confirming account...", 14);
			return 14; 
		}

		$unique_key = preg_replace('/\s+/', '', $unique_key); //strip whitespace


		$query = "SELECT unique_id, id FROM users WHERE unique_id=?";
        $stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "s", $unique_key);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
        	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while confirming account... Error: ".mysqli_error($dbc), 1);
        	return 1;
        }

        $stmt->bind_result($mysql_database_user_unique_key, $mysql_user_id);

        while ($stmt->fetch()) {

        	$database_user_unique_key = $mysql_database_user_unique_key;
        	$id = $mysql_user_id;

		}

		if (empty($database_user_unique_key)) {
			$lacicloud_errors_api -> msgLogger("LOW", "Unique key incorrect when confirming account... Unique key: ".$unique_key, 14);
			return 14;
		}


		$query = "UPDATE users SET unique_id='1' WHERE unique_id=?";
        $stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "s", $unique_key);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
        	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while confirming account... Error:".mysqli_error($dbc), 1);
        	return 1;
        }

        /*
        //create user by default; get email address, don't trust user inputted one
        $email = $lacicloud_ftp_api->getUserValues($id, $dbc)["email"];
        
        //75% of tier space
        $ftp_space = 0.75 * $lacicloud_api->getTierData($lacicloud_ftp_api->getUserValues($id, $dbc)["tier"])[0];
        $ftp_username = $lacicloud_utils_api->getEmailUserName($email);
        
        //add default FTP user (master)
        $lacicloud_ftp_api->addFTPUser($ftp_username, $ftp_password, $ftp_space, "/", "mb", $id, $dbc, $dbc_ftp);
		*/

        return 15;
	}

	public function registerUser($email, $password, $password_retyped, $captcha, $dbc) {
		$lacicloud_errors_api = new Errors();
		$lacicloud_emails_api = New Emails();
		$lacicloud_webhosting_api = new Webhosting();
		
		//captcha required	  	
		if ($lacicloud_errors_api -> getSuccessOrErrorFromID($this -> checkCaptcha($captcha)) !== "success") {
			return 10;
		}

		if ($lacicloud_errors_api -> getSuccessOrErrorFromID($this -> validateUserInfo($email, $password, $password_retyped)) !== "success") {
			$lacicloud_errors_api -> msgLogger("LOW", "Email/Password not valid when creating acocunt... Email: ".$email, 4);
			return 4;
		}

		//check terms & conditions checkbox
		if (count($_POST["checkbox"]) == 0) {
			$lacicloud_errors_api -> msgLogger("LOW", "Terms & Conditions checkbox not checked when creating account...", 1);
			return 1;  
		}

		//check wheter email already exists
		$query = "SELECT email FROM users WHERE email = ?";
		$stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "s", $email);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
        	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while creating account... Error: ".mysqli_error($dbc), 1);
        	return 1;
        }

        $stmt->bind_result($mysql_email);

        while ($stmt->fetch()) {

        	$email_in_database = $mysql_email;

		}

		if (!empty($email_in_database)) {
			$lacicloud_errors_api -> msgLogger("LOW", "Email already exists when creating account... Email: ".$email, 17);
			return 17;
		}

		$api_key = bin2hex(openssl_random_pseudo_bytes(32));

		$bcrypt_password = password_hash($password, PASSWORD_DEFAULT ,$this->bcrypt_options);

		$user_unique_key = bin2hex(openssl_random_pseudo_bytes(32));

		$time = time();

		$query = "INSERT INTO users (password, email, unique_id, first_time_boolean, reset_key, api_key, lastpayment) VALUES (?, ?, ?, ?, ?, ?, ?)";
		$stmt = mysqli_prepare($dbc, $query);
        mysqli_stmt_bind_param($stmt,"sssissi", $bcrypt_password, $email, $user_unique_key, $this->first_time_boolean_default, $this->reset_key_default, $api_key, $time);
        $result = mysqli_stmt_execute($stmt);
        $affected_rows = mysqli_stmt_affected_rows($stmt);

        if (!$result or $affected_rows !== 1) {
        	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while creating user account... Error:".mysqli_error($dbc)." Affected rows: ".$affected_rows, 1);
        	return 1;
        }

        //fetch ID
		$query = "SELECT id FROM users WHERE password = ?";
		$stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "s", $bcrypt_password);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
        	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while creating account... Error: ".mysqli_error($dbc), 1);
        	return 1;
        }

        $stmt->bind_result($mysql_id);

        while ($stmt->fetch()) {

        	$id = $mysql_id;

		}

        //insert into truespacecounter
		$query = "INSERT INTO truespacecounter (used_space, id) VALUES (?, ?)";
		$stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "ii", $this->usedSpaceDefault, $id);
        $result = mysqli_stmt_execute($stmt);

        
        if (!$result) {
        	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while creating account... Error: ".mysqli_error($dbc), 1);
        	return 1;
        } 


        //and into truebandwidthcounter (which only really counts FTP uploads)
        $query = "INSERT INTO truebandwidthcounter (used_bandwidth, id) VALUES (?, ?)";
		$stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "di", $this->usedBandwidthDefault, $id);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
        	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while creating account... Error: ".mysqli_error($dbc), 1);
        	return 1;
        } 


        //construct email
        $body = str_replace(array("[TITLE_EMAIL]", "[PREHEADER_EMAIL]", "[FIRST_TEXT_EMAIL]", "[BUTTON_EMAIL]", "[END_TEXT_EMAIL]"), array("LaciCloud - Confirm your account!", "LaciCloud - Confirm your account!", "<p>To confirm your LaciCloud account, please click here:</p>", "<a href='https://lacicloud.net/account/?unique_key=".$user_unique_key."&email=".urlencode($email)."' target='_blank'>Confirm now!</a>", "<p>Enjoy LaciCloud! We hope it works for you.</p>"), $lacicloud_emails_api -> getEmailTemplate());

        //send email
        
     	try{
     		$title = "LaciCloud_Create_Email";
        	$transport = Swift_SmtpTransport::newInstance(gethostbyname("mail.gandi.net"), 465, "ssl") 
				->setUsername($this -> grabSecret("email"))
				->setPassword($this -> grabSecret("email_password"))
				->setSourceIp("0.0.0.0");
			$mailer = Swift_Mailer::newInstance($transport);
			$logger = new \Swift_Plugins_Loggers_ArrayLogger();
			$mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));
			$message = Swift_Message::newInstance("$title");
			$message 
				->setSubject("LaciCloud - Confirm your account!")
				->setFrom(array("bot@lacicloud.net" => "LaciCloud"))
				->setTo(array("$email"))
				->setCharset('utf-8') 
				->setBody($body, 'text/html');
			$result = $mailer->send($message, $errors);
	    } catch(\Swift_TransportException $e){
	        $response = $e->getMessage();
	        $result = false;
	    } catch (Exception $e) {
	    	$response = $e->getMessage();
	    	$result = false; 
	    }

     
		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "Could not send email when creating account... Error:\n".$logger->dump()." Exception error:\n".$response,18);

			//revert changes
			$query = "SELECT id FROM users WHERE password=?";
			$stmt = mysqli_prepare($dbc, $query);							
			mysqli_stmt_bind_param($stmt, "s", $bcrypt_password);
			mysqli_stmt_execute($stmt);
	        $stmt->bind_result($mysql_id);
	   	 	while ($stmt->fetch()) {
	        	$id = $mysql_id;
			}

			$this -> deleteUser($id, $dbc);
			
			return 18;
		}

		return 19;


	}

	//delete user with id - used when sending registration email fails
	public function deleteUser($id, $dbc) {
		$query = "DELETE FROM users WHERE id = ?";
		$stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "i", $id);
		mysqli_stmt_execute($stmt);

		$query = "DELETE FROM truespacecounter WHERE id = ?";
		$stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "i", $id);
		mysqli_stmt_execute($stmt);

		$query = "DELETE FROM truebandwidthcounter WHERE id = ?";
		$stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "i", $id);
		mysqli_stmt_execute($stmt);

		return 20;
	}



	//generate key and send email
	public function forgotLoginStep1($email, $captcha, $dbc) {
		$lacicloud_errors_api = new Errors();
		$lacicloud_emails_api = New Emails();

		//captcha required	  	
		if ($lacicloud_errors_api -> getSuccessOrErrorFromID($this -> checkCaptcha($captcha)) !== "success") {
						return 10;
		}


		$query = "SELECT email FROM users WHERE email=?";
		$stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "s", $email);
		$result = mysqli_stmt_execute($stmt);

		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while resetting account... Error: ".mysqli_error($dbc), 1);
			return 1;
		}

		$stmt->bind_result($mysql_email);
   	 	while ($stmt->fetch()) {
        	$email_in_database = $mysql_email;
		}

		if (empty($email_in_database)) {
			$lacicloud_errors_api -> msgLogger("LOW", "Email not in database when resetting account... Email: ".$email, 1);
			return 1;
		}

		$reset_key = bin2hex(openssl_random_pseudo_bytes(32));

		$query = "UPDATE users SET reset_key=? WHERE email=?";
		$stmt = mysqli_prepare($dbc, $query);	
		mysqli_stmt_bind_param($stmt, "ss", $reset_key, $email);
		$result = mysqli_stmt_execute($stmt);
		$affected_rows = mysqli_stmt_affected_rows($stmt);

		if (!$result or $affected_rows !== 1) {
				  $lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while resetting account... Error: ".mysqli_error($dbc), 1);
		      	 return 1;
		}

		 //construct email
        $body = str_replace(array("[TITLE_EMAIL]", "[PREHEADER_EMAIL]", "[FIRST_TEXT_EMAIL]", "[BUTTON_EMAIL]", "[END_TEXT_EMAIL]"), array("LaciCloud - Reset your account!", "LaciCloud - Reset your account!", "<p>To reset your LaciCloud account, please click here:</p>", "<a href='https://lacicloud.net/account/?reset_key=".$reset_key."#forgot_step_2' target='_blank'>Reset now!</a>", "Or copy paste your reset key: ".$reset_key."!"), $lacicloud_emails_api -> getEmailTemplate());


		try{
     		$title = "LaciCloud_Forgot_Email";
	        $transport = Swift_SmtpTransport::newInstance(gethostbyname("mail.gandi.net"), 465, "ssl") 
				->setUsername($this -> grabSecret("email"))
				->setPassword($this -> grabSecret("email_password"))
				->setSourceIp("0.0.0.0");
			$mailer = Swift_Mailer::newInstance($transport);
			$logger = new \Swift_Plugins_Loggers_ArrayLogger();
			$mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));
			$message = Swift_Message::newInstance("$title");
			$message 
				->setSubject("LaciCloud - Reset your account!")
				->setFrom(array("bot@lacicloud.net" => "LaciCloud"))
				->setTo(array("$email"))
				->setCharset('utf-8') 
				->setBody($body,'text/html');
			$result = $mailer->send($message, $errors);
	    } catch(\Swift_TransportException $e){
	        $response = $e->getMessage();
	        $result = false;
	    } catch (Exception $e) {
	    	$response = $e->getMessage();
	    	$result = false; 
	    }


		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "Could not send email when resetting account... Error:\n".$logger->dump()." Exception error:\n".$response,18);
			return 18;
		}

		$_SESSION["reset"] = true;
		$_SESSION["email"] = $email;

		//reset post array
		//$_POST = array();

		return 21;


	}

	//check key and update password - finish!!
	public function forgotLoginStep2($email, $password, $password_retyped, $reset_key, $captcha, $dbc) {
		$lacicloud_errors_api = new Errors();
		$lacicloud_emails_api = New Emails();

			//captcha required	  	
		if ($lacicloud_errors_api -> getSuccessOrErrorFromID($this -> checkCaptcha($captcha)) !== "success") {
						return 10;
		}


		if ($lacicloud_errors_api -> getSuccessOrErrorFromID($this -> validateUserInfo($email, $password, $password_retyped)) !== "success") {
			return 4;
		}

		$reset_key = preg_replace('/\s+/', '', $reset_key);

		$query = "SELECT reset_key,password FROM users WHERE reset_key=?";
		$stmt = mysqli_prepare($dbc, $query);		
		mysqli_stmt_bind_param($stmt, "s", $reset_key);
		$result = mysqli_stmt_execute($stmt);
        
		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while resetting password... Error: ".mysqli_error($dbc), 1);
			return 1;
		}

        $stmt->bind_result($mysql_reset_key, $mysql_old_user_password);
   	 	while ($stmt->fetch()) {
        	$reset_key = $mysql_reset_key;
        	$old_user_password = $mysql_old_user_password;
		}

		if(empty($reset_key)) {
			$lacicloud_errors_api -> msgLogger("LOW", "User reset key incorrect when resetting password...", 22);
			return 22;
		}

		if (\password_verify($password, $old_user_password)) {
			$lacicloud_errors_api -> msgLogger("LOW", "Old password/new password same when resetting password...", 23);
			return 23; 
		}

		$password =  password_hash($password, PASSWORD_DEFAULT, $this->bcrypt_options);

		$query = "UPDATE users SET password=?,reset_key=? WHERE reset_key=?";
		$stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "sis", $password, $this->reset_key_default, $reset_key);
		$result = mysqli_stmt_execute($stmt);
		

		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while updating user information... Error:".mysqli_error($dbc), 1);
			return 1;
		}

		 //construct email
        $body = str_replace(array("[TITLE_EMAIL]", "[PREHEADER_EMAIL]", "[FIRST_TEXT_EMAIL]", "[BUTTON_EMAIL]", "[END_TEXT_EMAIL]"), array("LaciCloud - Account has been reset!", "LaciCloud - Account has been reset!", " <p>You have successfully reset your account.</p>", "", "<p>The IP of the person who reset your account: ".$_SERVER["REMOTE_ADDR"]."! If this wasn't you, please email us using our contact page, or try resetting your account again.</p>"), $lacicloud_emails_api -> getEmailTemplate());


		try{
     		$title = "LaciCloud_Forgot2_Email";
	        $transport = Swift_SmtpTransport::newInstance(gethostbyname("mail.gandi.net"), 465, "ssl") 
				->setUsername($this -> grabSecret("email"))
				->setPassword($this -> grabSecret("email_password"))
				->setSourceIp("0.0.0.0");
			$mailer = Swift_Mailer::newInstance($transport);
			$logger = new \Swift_Plugins_Loggers_ArrayLogger();
			$mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));
			$message = Swift_Message::newInstance("$title");
			$message 
				->setSubject("LaciCloud - Account has been reset!")
				->setFrom(array("bot@lacicloud.net" => "LaciCloud"))
				->setTo(array("$email"))
				->setCharset('utf-8') 
				->setBody($body, 'text/html');
			$result = $mailer->send($message, $errors);
	    } catch(\Swift_TransportException $e){
	        $response = $e->getMessage();
	        $result = false;
	    } catch (Exception $e) {
	    	$response = $e->getMessage();
	    	$result = false; 
	    }


		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "Could not send email when resetting account (step 2)... Error:\n".$logger->dump()." Exception error:\n".$response,18);
			return 18;
		}

		return 24; 

	}

	public function verifyShit() {
		//should be 'nough
		//check UA
		if (isset($_SESSION['HTTP_USER_AGENT'])) {
		    if ($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['HTTP_USER_AGENT']))
		    {
		        return 25;
		    } 

		} else {
		    $_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);
		}

		//check IP
		if (isset($_SESSION["REMOTE_ADDR"])) {
			if ($_SESSION["REMOTE_ADDR"] != md5($_SERVER["REMOTE_ADDR"])) {
				return 25;
			}
		} else {
			$_SESSION['REMOTE_ADDR'] = md5($_SERVER['REMOTE_ADDR']);
		}

		return 3;

	}

	public function verifyCSRF($token) {
		if ($_SESSION["csrf_token"] !== $token) {
			return 25;
		}

		return 3;
	}

	public function blowUpSession() {
		session_unset();
		session_destroy();

		//unset cookies just to be sure, yes this was copied from stackoverflow
		if (isset($_SERVER['HTTP_COOKIE'])) {
		    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
		    foreach($cookies as $cookie) {
		        $parts = explode('=', $cookie);
		        $name = trim($parts[0]);
		        setcookie($name, '', time()-1000);
		        setcookie($name, '', time()-1000, '/');
		    }
		}
	}

	public function blowupMysql($dbc, $dbc_ftp) {
		mysqli_close($dbc);
		mysqli_close($dbc_ftp);
	}

	public function isPageCached() {
		//get the last-modified-date of this very file
		$lastModified=filemtime(__FILE__);
		//get a unique hash of this file (etag)
		$etagFile = md5_file(__FILE__);
		//get the HTTP_IF_MODIFIED_SINCE header if set
		$ifModifiedSince=(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false);
		//get the HTTP_IF_NONE_MATCH header if set (etag: unique file hash)
		$etagHeader=(isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);

		//set last-modified header
		header("Last-Modified: ".gmdate("D, d M Y H:i:s", $lastModified)." GMT");
		//set etag-header


		header("Etag: ".$etagFile);
		//make sure caching is turned on (2 hours)
		header('Cache-Control: public, max-age=7200');

		//check if page has changed. If not, send 304 and exit
		if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])==$lastModified || $etagHeader == $etagFile) {
		      header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
		      die(0);
		} 
	}

	public function getHitCount($dbc) {
		$query = "SELECT count FROM counter";
		$stmt = mysqli_prepare($dbc, $query);
		$result = mysqli_stmt_execute($stmt);


		$stmt->bind_result($mysql_counter);

	    /* fetch values */

		while ($stmt->fetch()) {

			$hit_count = $mysql_counter;
		}
		return $hit_count;
	}


	public function firstTimeSetUp($id, $dbc) {
		$lacicloud_ftp_api = new FTPActions();
		$lacicloud_webhosting_api = new Webhosting();

		$query = "UPDATE users SET first_time_boolean='1' WHERE id=?";
        $stmt = mysqli_prepare($dbc, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        $result = mysqli_stmt_execute($stmt);


        $query = "INSERT INTO ftpactions (value, type) VALUES (?, ?)";
        $stmt = mysqli_prepare($dbc, $query);

        //see ftpactions.py: type 0 is make symlink from /users/$id/public_files to /public_files/$id, type 1 is remove .ftpquota file so that FTP user directory can be deleted
        $ftpactions_type = 0; 
        mysqli_stmt_bind_param($stmt,"ii", $id, $ftpactions_type);
        $result = mysqli_stmt_execute($stmt);
		
        return 26;
	}

	public function checkCaptcha($captcha) {
		$lacicloud_errors_api = new Errors();

		$securimage = new Securimage();
		$securimage->database_user = $this -> grabSecret("db_user_captcha");
		$securimage->database_pass = $this -> grabSecret("db_password_captcha");
		$securimage->database_name = $this -> grabSecret("db_name");
		$securimage->database_host = $this->grabSecret("db_host");
		$securimage->database_table = $this -> grabSecret("db_table_captcha");

		$correct_code = $securimage->getCode(false, true);
	    if ($securimage->check($captcha) == false) {
	        $lacicloud_errors_api -> msgLogger("LOW", "Captcha code incorrect... User-inputted Captcha: ".$captcha." Correct captca: ".$correct_code, 10);
	        return 10;
	    }

	    return 11;
	}

	public function canChangeToTier($tier, $id, $dbc, $dbc_ftp) {
		$lacicloud_api = new LaciCloud();
		$lacicloud_errors_api = new Errors();
		$lacicloud_ftp_api = new FTPActions();

		//downgrade is tricky, so it will be done manually if needed
		$user_tier = (int)$lacicloud_ftp_api -> getUserValues($id, $dbc)["tier"];
		$lastpayment = (int)$lacicloud_ftp_api -> getUserValues($id, $dbc)["lastpayment"];

		//if equal tier and tier wanted or downgrade trip detector
		//if equal tier but payment is NOT OK for this year, don't trip detector as both tier equal and payment need to be true
		if ($user_tier == $tier and (time() - $lastpayment) < $lacicloud_api->unix_time_1_year or $user_tier > $tier) {
				return 27;
		}
		
		
		return 28;
	}

	public function upgradeToTier($tier, $orderID, $id, $dbc, $dbc_ftp) {
		$lacicloud_errors_api = new Errors();

		$upload_speed = $this -> getTierData($tier)[2];
		$download_speed = $this -> getTierData($tier)[3];

		$time = time();
	
		$query = "UPDATE users SET tier=?,lastpayment=? WHERE id=?";
        $stmt = mysqli_prepare($dbc, $query);
        mysqli_stmt_bind_param($stmt, "iii", $tier, $time, $id);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while upgrading to tier ".$tier." for orderID ".$orderID."... Error:\n".mysqli_error($dbc), 1);
			return 1; 
		}

		$query = "UPDATE ftp_users SET uploadspeed=?, downloadspeed=? WHERE realID=?";
		$stmt = mysqli_prepare($dbc_ftp, $query);
        mysqli_stmt_bind_param($stmt, "iii", $upload_speed, $download_speed, $id);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error (2) while upgrading to tier ".$tier." for orderID ".$orderID."... Error:\n".mysqli_error($dbc_ftp), 1);
			return 1; 
		}


        return 29;
	}

	public function getTierData($tier) {

		//first is space in MB, then FTP users, then upload speed in KBits, then download speed in KBits, then description, then bandwidth in MB, then webhosting functions
		//add custom tiers to end, like 4_1, 4_2 each with their own custom contracts
		$data = array(1 => array(5000, 10, 8192, 1024, "- Free - For Occasional Users", 10000, false), 2 => array(125000, 50, 16384, 2048, " - 15€ / Year - For Regular Users", 1000000, true), 3 => array(250000, 125, 32768, 4096, " - 25€ / Year - For Expert Users", 2000000, true), 4 => array(2000000, 999999, 65565, 8096, " - 250€ / Year - Custom Subscription #1", 999999, true));

		return $data[$tier];

	}	

	public function sendOverUseEmail($id, $email, $tier, $type, $over_use, $additional_information) {
		$lacicloud_errors_api = new Errors();
		$lacicloud_emails_api = new Emails();


		if ($type == "spacecounter") {
			$subject = "LaciCloud - Space overused!";
			$title_email = "LaciCloud - Space overused!";
			$preheader_email = "LaciCloud - Space overused!";
			$first_text_email = "<p>You are over-using your space for tier ".$tier." for user ID ".$id." by ".$over_use."MB. Maximum allowed  is ".$this->getTierData($tier)[0]." and you are using ".($over_use + $this->getTierData($tier)[0]).". Please delete some files to rectify the situation!</p>";
			$button_email = "<a href='https://lacicloud.net/ftp'>Rectify now!</a>";
			$end_text_email = "<p>Thank you.</p>";
		} elseif ($type == "bandwidthcounter") {
			$subject = "LaciCloud - Bandwidth overused!";
			$title_email = "LaciCloud - Bandwidth overused!";
			$preheader_email = "LaciCloud - Bandwidth overused!";
			$first_text_email = "<p>You are over-using your bandwidth for tier ".$tier." for user ID ".$id." by ".$over_use."MB. Maximum allowed  is ".$this->getTierData($tier)[5]."MB and you are using ".($over_use + $this->getTierData($tier)[5])."MB. Please slow your transfers or upgrade to a bigger tier!</p>";
			$button_email = "<a href='https://lacicloud.net/shop'>Upgrade now!</a>";
			$end_text_email = "<p>Thank you.</p>";
		} elseif ($type == "paymentchecker") {
			$subject = "LaciCloud - Payment overdue!";
			$title_email = "LaciCloud - Payment overdue!";
			$preheader_email = "LaciCloud - Payment overdue!";
			$first_text_email = "<p>Your payment for tier ".$tier." for user ID ".$id." is overdue by ".$over_use." days. You should have already paid by ".$additional_information.". Please log-in to the interface and make a payment!</p>";
			$button_email = "<a href='https://lacicloud.net/interface'>Pay now!</a>";
			$end_text_email = "<p>Thank you.</p>";
		}

		//construct email
        $body = str_replace(array("[TITLE_EMAIL]", "[PREHEADER_EMAIL]", "[FIRST_TEXT_EMAIL]", "[BUTTON_EMAIL]", "[END_TEXT_EMAIL]"), array($title_email, $preheader_email, $first_text_email, $button_email, $end_text_email), $lacicloud_emails_api -> getEmailTemplate());

		
		try {
			//sends email to user & me about overdue payment or overused space
			$title = "LaciCloud_OverUsed_Email";
		    $transport = Swift_SmtpTransport::newInstance(gethostbyname("mail.gandi.net"), 465, "ssl") 
				->setUsername($this -> grabSecret("email"))
				->setPassword($this -> grabSecret("email_password"))
				->setSourceIp("0.0.0.0");
			$mailer = Swift_Mailer::newInstance($transport);
			$logger = new \Swift_Plugins_Loggers_ArrayLogger();
			$mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));
			$message = Swift_Message::newInstance("$title");
			$message 
				->setSubject($subject)
				->setFrom(array("bot@lacicloud.net" => "LaciCloud"))
				->setTo(array("$email"))
				->setBcc(array("laci@lacicloud.net" => "Laci"))
				->setCharset('utf-8') 
				->setBody($body, 'text/html');
			$result = $mailer->send($message, $errors);
	    } catch(\Swift_TransportException $e){
	        $response = $e->getMessage();
	        $result = false;
	    } catch (Exception $e) {
	    	$response = $e->getMessage();
	    	$result = false; 
	    }


		if (!$result) {
			@$lacicloud_errors_api -> msgLogger("SEVERE", "Could not send overdue email for user ID ".$id."... Error:\n".$logger->dump()." Exception error:\n".$response, 18);
		}


	}

	public function sendContactEmail($contact_reason, $subject, $body, $reply_to_address, $captcha) {
		$lacicloud_errors_api = new Errors();

		//captcha required	  	
		if ($lacicloud_errors_api -> getSuccessOrErrorFromID($this -> checkCaptcha($captcha)) !== "success" or is_null($reply_to_address)) {
						return 10;
		}


		//spent 15 mins debugging why the object was sending its own headers, turns out i named the $body variable $message at first... :P
		try{
     		$title = "LaciCloud_Contact_Email";
	        $transport = Swift_SmtpTransport::newInstance(gethostbyname("mail.gandi.net"), 465, "ssl") 
				->setUsername($this -> grabSecret("email"))
				->setPassword($this -> grabSecret("email_password"))
				->setSourceIp("0.0.0.0");
			$mailer = Swift_Mailer::newInstance($transport);
			$logger = new \Swift_Plugins_Loggers_ArrayLogger();
			$mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));
			$message = Swift_Message::newInstance("$title");
			$message 
				->setSubject("[CONTACT] " . $contact_reason . " : " . $subject)
				->setFrom(array("bot@lacicloud.net" => "LaciCloud"))
				->setReplyTo(array($reply_to_address))
				->setTo(array("laci@lacicloud.net"))
				->setCharset('utf-8') 
				->setBody(strip_tags($body));
			$result = $mailer->send($message, $errors);	
	    } catch(\Swift_TransportException $e){
	        $response = $e->getMessage();
	        $result = false;
	    } catch (Exception $e) {
	    	$response = $e->getMessage();
	    	$result = false; 
	    }


		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "Could not send contact email... Error:\n".$logger->dump()." Exception error:\n".$response, 18);
			return 18;
		}

		return 30;

	}


}

//things such as creating FTP user, deleting FTP user
class FTPActions extends LaciCloud {

	public function validateFTPUserInfo($ftp_username, $ftp_password, $ftp_space_currency, $ftp_space_specified, $starting_directory) {
		$lacicloud_errors_api = new Errors();

		if (empty($ftp_username) or empty($ftp_space_specified) or empty($starting_directory)) {
			$lacicloud_errors_api -> msgLogger("LOW", "Values empty when creating FTP user...", 31);
			return 31;
		}

		//allow @, -, _, !, and alphanumeric characters in FTP usernames
		if (strlen($ftp_username) < 3 or !ctype_alnum(str_replace(array("@","-","_","!", "."), "", $ftp_username))) {
			$lacicloud_errors_api -> msgLogger("LOW", "FTP username invalid when creating FTP user... Username: ".$ftp_username, 31);
			return 31;
		} 

		if (!is_numeric($ftp_space_specified) or (float)$ftp_space_specified <= 1.0) {
			$lacicloud_errors_api -> msgLogger("LOW", "FTP space specified invalid... Specified: ".$ftp_space_specified, 31);
			return 31;
		}

		//Directory traversal attack check 
		$starting_directory = rawurldecode($starting_directory);

		
		if (!preg_match('/^[\p{L}0-9\s-]+$/u', str_replace("/", "" ,$starting_directory)) and $starting_directory != "/") {
			$lacicloud_errors_api -> msgLogger("LOW", "Starting directory not in valid format... Specified: ".$starting_directory, 31);
			return 31;
		}
		

		if ($starting_directory[0] !== "/" or strpos($starting_directory, "../") !== FALSE or strpos($starting_directory, "..\\\\") !== FALSE) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "Directory traversal attempt... Specified: ".$starting_directory, 31);
			return 31;
		}

		if ($starting_directory == "/" and empty($ftp_password)) {
			$lacicloud_errors_api -> msgLogger("LOW", "Master account without FTP user password attempted...", 31);
			return 31;
		}

		if (!empty($ftp_password)) {
			if (strlen($ftp_password) < 8 or !preg_match("#[0-9]+#", $ftp_password) or !preg_match("#[a-zA-Z]+#", $ftp_password) or $ftp_username == $ftp_password) {
				$lacicloud_errors_api -> msgLogger("LOW", "FTP account password strength too weak...", 31);
				return 31;
			}
		}

		return 32; 
	}

	public function addFTPUser($ftp_username, $ftp_password, $ftp_space_specified, $starting_directory, $ftp_space_currency, $id, $dbc, $dbc_ftp) {
		$lacicloud_api = new LaciCloud();
		$lacicloud_errors_api = new Errors();
		
		if ($lacicloud_errors_api -> getSuccessOrErrorFromID($this->validateFTPUserInfo($ftp_username, $ftp_password, $ftp_space_currency, $ftp_space_specified, $starting_directory)) !== "success") {
			return 31;
		} 


		//convert to MB
		if ($ftp_space_currency == "gb") {
			$ftp_space_specified = ($ftp_space_specified * 1000);
		} elseif ($ftp_space_currency == "tb") {
			$ftp_space_specified = ($ftp_space_specified * 1000) * 1000;
		}
		
		$tier = $this->getUserValues($id, $dbc)["tier"];
		
		$ftp_space_user_has = $lacicloud_api -> getTierData($tier)[0] - $this -> getFTPUsersUsedSpace($id, $dbc);
		$ftp_space_user_has_virtual = $lacicloud_api -> getTierData($tier)[0] - $this -> getFTPUsersVirtuallyUsedSpace($id, $dbc_ftp);

		$users_limit = $lacicloud_api -> getTierData($tier)[1];


		if ((float)$ftp_space_specified > $ftp_space_user_has or (float)$ftp_space_specified > $ftp_space_user_has_virtual) {
			$lacicloud_errors_api -> msgLogger("LOW", "User doesn't have enough FTP space for FTP user: ".$ftp_username." with FTP space: ".$ftp_space_specified." User's max: ".$ftp_space_user_has, 31);
			return 31;
		}

		$users_array = $this->getFTPUsersList($id, $dbc_ftp);
		$active_users = count($users_array);

		if ($active_users == $users_limit) {
			$lacicloud_errors_api -> msgLogger("LOW", "User hit FTP user's limit '".$users_limit."' Active users: '".$active_users."'...", 31);
			return 31;
		}

		//this also checks if FTP user already exists
		$query = "INSERT INTO ftp_users (user, password, home, quota, downloadspeed, uploadspeed ,realID) VALUES (?, ?, ?, ?, ?, ?, ?)";
		$stmt = mysqli_prepare($dbc_ftp, $query);

		$crypt_ftp_password = crypt($ftp_password, '$6$rounds=5000$'.bin2hex(openssl_random_pseudo_bytes(16)).'$');
		$ftp_starting_directory = $lacicloud_api->document_root."/users/".$id.rawurldecode($starting_directory);

		$downloadspeed = $lacicloud_api->getTierData($tier)[3];
		$uploadspeed = $lacicloud_api->getTierData($tier)[2];

		mysqli_stmt_bind_param($stmt, "sssiiii", $ftp_username, $crypt_ftp_password, $ftp_starting_directory, $ftp_space_specified, $downloadspeed, $uploadspeed, $id);
		$result = mysqli_stmt_execute($stmt);

		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while adding FTP user with info; FTP Username: '".$ftp_username."' with Starting directory '".$ftp_starting_directory."' and FTP space '".$ftp_space_specified."' for ID '".$id."'... Error: ".mysqli_error($dbc_ftp), 1);
			return 1;
		}

		return 32;

	}

	public function removeFTPUser($ftp_username, $id, $dbc, $dbc_ftp) {
		$lacicloud_api = new LaciCloud();
		$lacicloud_errors_api = new Errors();

		//validate FTP username
		if ($lacicloud_errors_api->getSuccessOrErrorFromID($this->validateFTPUserInfo($ftp_username, bin2hex(openssl_random_pseudo_bytes(16)), "mb", "100", "/dummy")) !== "success") {
			return 33;
		} 

		$query = "SELECT realID,home FROM ftp_users WHERE user = ?";
		$stmt = mysqli_prepare($dbc_ftp, $query);
		mysqli_stmt_bind_param($stmt, "s", $ftp_username);
		$result = mysqli_stmt_execute($stmt);

		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while removing FTP user '".$ftp_username."'... Error: ".mysqli_error($dbc_ftp), 1);
			return 1;
		}

		$stmt->bind_result($mysql_ftp_user_real_id,$mysql_ftp_starting_directory);
		while ($stmt->fetch()) {
			$ftp_user_real_id = $mysql_ftp_user_real_id;
			$ftp_starting_directory = $mysql_ftp_starting_directory;
		}

		

		if ($id !== $ftp_user_real_id) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "FTP user '".$ftp_username."' not his for user ID: ".$id.", FTP user belongs to user ID: ".$ftp_user_real_id, 33);
			return 33;
		}

		$query = "DELETE FROM ftp_users WHERE user = ?";
		$stmt = mysqli_prepare($dbc_ftp, $query);
		mysqli_stmt_bind_param($stmt, "s", $ftp_username);
		$result = mysqli_stmt_execute($stmt);

		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while removing FTP user '".$ftp_username."'... Error: ".mysqli_error($dbc_ftp), 1);
			return 1;
		}

		$query = "INSERT INTO ftpactions (value, type) VALUES (?, ?)";
		$stmt = mysqli_prepare($dbc, $query);

		//see ftpactions.py: type 0 is make symlink from /users/$id/public_files to /public_files/$id, type 1 is remove .ftpquota file so that FTP user directory can be deleted
		$ftpactions_type = 1;
		$ftpactions_starting_directory = str_replace($lacicloud_api->document_root."/users/", "", $ftp_starting_directory);
		
		mysqli_stmt_bind_param($stmt,"si", $ftpactions_starting_directory, $ftpactions_type);
		$result = mysqli_stmt_execute($stmt);

		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while registering new ftpactions.py action; type: '".$type."' Starting directory: '".$ftpactions_starting_directory."'... Error: ".mysqli_error($dbc), 1);
			return 1;
		}

		return 34;

	}

	//returns array of user values like ftp_space, bitcoin_paid, etc
	public function getUserValues($id, $dbc) {
		$lacicloud_errors_api = new Errors();

		$query = "SELECT tier,first_time_boolean,api_key,email,lastpayment,id,sitename FROM users WHERE id = ?";
	    $stmt = mysqli_prepare($dbc, $query);
	    mysqli_stmt_bind_param($stmt, "i", $id);
	    $result = mysqli_stmt_execute($stmt);

	    if (!$result) {
	    	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while getting User's values... Error: ".mysqli_error($dbc), 1);
	    	return 1;
	    }

	    $stmt->bind_result($mysql_tier, $mysql_first_time, $mysql_api_key,  $mysql_email, $mysql_lastpayment, $mysql_id, $mysql_sitename);

	    $values_array = array();
	    while ($stmt->fetch()) {

	            $values_array["tier"] = (int)$mysql_tier;

	            $values_array["first_time_boolean"] = $mysql_first_time;

	            $values_array["api_key"] = $mysql_api_key; 

	            $values_array["email"] = $mysql_email;

	            $values_array["lastpayment"] = (int)$mysql_lastpayment;

	            $values_array["id"] = (int)$mysql_id;

	            $values_array["sitename"] = $mysql_sitename;

	    }

	    return $values_array;


	}

	public function getFTPUsersValues($id, $dbc_ftp) {
		$lacicloud_errors_api = new Errors();

		$query = "SELECT user,home,quota FROM ftp_users WHERE realID = ?";
		$stmt = mysqli_prepare($dbc_ftp, $query);
	    mysqli_stmt_bind_param($stmt, "i", $id);
	    $result = mysqli_stmt_execute($stmt);
	    
	    if (!$result) {
	    	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while getting FTP user's values... Error: ".mysqli_error($dbc_ftp), 1);
	    	return 1;
	    }

	    //mysqli fetch into multi dimensional array
	    $result = $stmt->get_result();

	    $users_array_values = array();

	   	while ($row = $result->fetch_assoc()) {
	            $users_array_values[] = $row;
	    }


	    return $users_array_values;
	}

	//returns array of FTP usernames user has
	public function getFTPUsersList($id, $dbc_ftp) {
		$lacicloud_errors_api = new Errors();

		$query = "SELECT user FROM ftp_users WHERE realID = ?";
	    $stmt = mysqli_prepare($dbc_ftp, $query);
	    mysqli_stmt_bind_param($stmt, "i", $id);
	    $result = mysqli_stmt_execute($stmt);

	    if (!$result) {
	    	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while getting FTP user's list... Error: ".mysqli_error($dbc_ftp), 1);
	    	return 1;
	    }

	    $stmt->bind_result($mysql_ftp_users_list);


	    $users_array = array();

	    while ($stmt->fetch()) {
	            $users_array[] = $mysql_ftp_users_list;
	    }

	    return $users_array;

	}

	public function getUsedBandwidth($id, $dbc) {
			$lacicloud_errors_api = new Errors();

			$query = "SELECT used_bandwidth FROM truebandwidthcounter WHERE id = ?";
		    $stmt = mysqli_prepare($dbc, $query);
		    mysqli_stmt_bind_param($stmt, "i", $id);
		    $result = mysqli_stmt_execute($stmt);

		    if (!$result) {
		    	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while getting user's bandwidth... Error: ".mysqli_error($dbc), 1);
		    	return 1;
		    }

		    $stmt->bind_result($mysql_ftp_used_bandwidth);

		    while ($stmt->fetch()) {
		            $ftp_used_bandwidth = $mysql_ftp_used_bandwidth;
		    }

		    if ($ftp_used_bandwidth == NULL) {
		    	$ftp_used_bandwidth = 0;
		    }

		    return (int)$ftp_used_bandwidth;
	}

	public function getIndividualFTPUsersUsedSpaceFromFTP($ftp_username, $ftp_password, $id, $dbc, $dbc_ftp) {
		$lacicloud_errors_api = new Errors();
		$lacicloud_api = new LaciCloud();

		//validate FTP username and password
		if ($lacicloud_errors_api->getSuccessOrErrorFromID($this->validateFTPUserInfo($ftp_username, $ftp_password, "mb", "100", "/dummy")) !== "success") {
			return 33;
		} 

		
		//first see whether user owns FTP user
		$query = "SELECT realID FROM ftp_users WHERE user = ?";
		$stmt = mysqli_prepare($dbc_ftp, $query);
		mysqli_stmt_bind_param($stmt, "s", $ftp_username);
		$result = mysqli_stmt_execute($stmt);

		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while getting individual used FTP space for FTP user '".$ftp_username."'... Error: ".mysqli_error($dbc_ftp), 1);
			return 1;
		}

		$stmt->bind_result($mysql_ftp_user_real_id);
		while ($stmt->fetch()) {
			$ftp_user_real_id = $mysql_ftp_user_real_id;
		}

		if ($id !== $ftp_user_real_id) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "FTP user '".$ftp_username."' not his for user ID: ".$id.", FTP user belongs to user ID: ".$ftp_user_real_id, 33);
			return 33;
		}



		$fp = ftp_connect("lacicloud.net");
		ftp_raw($fp, "USER ".$ftp_username);
		$usage = ftp_raw($fp, "PASS ".$ftp_password);

		if ($usage[0] == "530 Login authentication failed") {
			$lacicloud_errors_api -> msgLogger("LOW", "Wrong password when getting used FTP space for FTP user ".$ftp_username."!", 40);
			return 40;
		}

		preg_match_all('!\d+!', $usage[2], $usage);
		return $usage[0];
	}
	
	public function getFTPUsersUsedSpace($id, $dbc) { 
		$lacicloud_errors_api = new Errors();

		$query = "SELECT used_space FROM truespacecounter WHERE id = ?";
	    $stmt = mysqli_prepare($dbc, $query);
	    mysqli_stmt_bind_param($stmt, "i", $id);
	    $result = mysqli_stmt_execute($stmt);

	    if (!$result) {
	    	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while getting FTP user's real used space... Error: ".mysqli_error($dbc), 1);
	    	return 1;
	    }

	    $stmt->bind_result($mysql_ftp_used_space);

	    while ($stmt->fetch()) {
	            $ftp_used_space = $mysql_ftp_used_space;
	    }

	    if ($ftp_used_space == NULL) {
	    	$ftp_used_space = 0;
	    }

	    return (int)$ftp_used_space;

	}
	
	public function getFTPUsersVirtuallyUsedSpace($id, $dbc_ftp) {
		$lacicloud_errors_api = new Errors();

		$query = "SELECT SUM(quota) FROM ftp_users WHERE realID = ?";
	    $stmt = mysqli_prepare($dbc_ftp, $query);
	    mysqli_stmt_bind_param($stmt, "i", $id);
	    $result = mysqli_stmt_execute($stmt);

	    if (!$result) {
	    	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while getting FTP user's virtually used space... Error: ".mysqli_error($dbc_ftp), 1);
	    	return 1;
	    }

	    $stmt->bind_result($mysql_ftp_used_space);

	    while ($stmt->fetch()) {
	            $ftp_used_space = $mysql_ftp_used_space;
	    }

	    if ($ftp_used_space == NULL) {
	    	$ftp_used_space = 0;
	    }

	    return (int)$ftp_used_space;
	}

	public function regenerateAPIKey($id, $dbc) {
		$lacicloud_errors_api = new Errors();

		$api_key = bin2hex(openssl_random_pseudo_bytes(32));

		$query = "UPDATE users SET api_key=? WHERE id=?";
		$stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "si", $api_key, $id);
		$result = mysqli_stmt_execute($stmt);

		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL Error regenerating API key... Error: ".mysqli_error($dbc), 1);
			return 1;
		}

		return 39;
	}

}


class Payments extends LaciCloud {

	//tiers that can be paid for
	public $valid_tiers = [2, 3];

	public function getChangeDisallowedCode() {
		return 27;
	}

	public function getSuccessCode() {
		return 36;
	}

	public function getCancelCode() {
		return 47;
	}

	public function getNotLoggedInErrorID() {
		return 46;
	}

	public function sendPaymentEmail($amount, $order_info, $orderID, $paymentID) {
		$lacicloud_api = new LaciCloud();
		$lacicloud_emails_api = New Emails();
		$lacicloud_errors_api = new Errors();

		$tier = $order_info[0];
		$id = $order_info[1];
		$email = $order_info[5];

		//construct email
        $body = str_replace(array("[TITLE_EMAIL]", "[PREHEADER_EMAIL]", "[FIRST_TEXT_EMAIL]", "[BUTTON_EMAIL]", "[END_TEXT_EMAIL]"), array("LaciCloud - Tier Upgraded!", "LaciCloud - Tier Upgrade!", "<p>Your account will be upgraded to tier ".$tier." momentarily! User ID: ".$id.", For value: ".$amount."€, Order ID: ".$orderID.", Payment ID: ".$paymentID."!</p>", "", "<p>We thank you for choosing LaciCloud!</p>"), $lacicloud_emails_api -> getEmailTemplate());
		
		try {
			//sends email to user & me about payment
			$title = "LaciCloud_Payment_Email";
		    $transport = Swift_SmtpTransport::newInstance(gethostbyname("mail.gandi.net"), 465, "ssl") 
				->setUsername($lacicloud_api -> grabSecret("email"))
				->setPassword($lacicloud_api -> grabSecret("email_password"))
				->setSourceIp("0.0.0.0");
			$mailer = Swift_Mailer::newInstance($transport);
			$logger = new \Swift_Plugins_Loggers_ArrayLogger();
			$mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));
			$message = Swift_Message::newInstance("$title");
			$message 
				->setSubject("LaciCloud - Tier upgraded!")
				->setFrom(array("bot@lacicloud.net" => "LaciCloud"))
				->setTo(array("$email"))
				->setBcc(array("laci@lacicloud.net" => "Laci"))
				->setCharset('utf-8') 
				->setBody($body, 'text/html');
			$result = $mailer->send($message, $errors);
	    } catch(\Swift_TransportException $e){
	        $response = $e->getMessage();
	        $result = false;
	    } catch (Exception $e) {
	    	$response = $e->getMessage();
	    	$result = false; 
	    }
		if (!$result) {
			@$lacicloud_errors_api -> msgLogger("SEVERE", "Could not send payment email for orderID ".$orderID."... Error:\n".$logger->dump()." Exception error:\n".$response, 18);
		}


	}


}

//used for coinpayments form encryption
class Encryption extends LaciCloud {

	public function encryptString($data, $key) {
		 $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length("AES-256-CBC"));
		 $encrypted = openssl_encrypt($data, "AES-256-CBC", $key, 0, $iv);
		 return base64_encode($encrypted . ':' . base64_encode($iv));
	}

	public function decryptString($data, $key) {
		$data = explode(':', base64_decode($data));

		$decrypted = openssl_decrypt($data[0], "AES-256-CBC", $key, 0, base64_decode($data[1]));

		return $decrypted;
	}

}

class Webhosting extends LaciCloud {

	//blacklisted subdomains that are used or may be
	public $blacklist = ["blog", "api", "ns", "mail", "www", "ftp", "ns1", "ns2", "web", "shop", "cdn", "dev", "test", "admin", "forum", "m"];
	
	public function validateSitename($sitename, $dbc) {
		
		//make it lowercase
		$sitename = strtolower($sitename);		

		//check if alphanumeric
		if (!ctype_alnum($sitename)) {
			return 48;
		} 

		//must be between 3 to 32 characters
		if (strlen($sitename) < 3 or strlen($sitename) > 32) {
 	   		return 48;
		}

		if (in_array($sitename, $this->blacklist)) {
			return 48;
		}

		//check whether subdomain already taken
		$query = "SELECT sitename FROM users WHERE sitename = ?";
		$stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "s", $sitename);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
        	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while checking sitename... Error: ".mysqli_error($dbc), 1);
        	return 1;
        }

        $stmt->bind_result($mysql_sitename);

        while ($stmt->fetch()) {
        	$sitename_in_database = $mysql_sitename;
		}

		if (!empty($sitename_in_database)) {
			return 48;
		}

		return 49;
		
	}

	public function addWebhostingEnv($id, $sitename, $mysql_username, $mysql_password, $dbc) {
		$lacicloud_errors_api = new Errors();
		$lacicloud_ftp_api = new FTPActions();

		if ($lacicloud_ftp_api->getUserValues($id, $dbc)["tier"] == 1 or isset($lacicloud_ftp_api->getUserValues($id, $dbc)["sitename"])) {
			return 54;
		}

		if ($lacicloud_errors_api -> getSuccessOrErrorFromID($this -> validateSitename($sitename, $dbc)) !== "success") {
			$lacicloud_errors_api -> msgLogger("LOW", "Sitename not valid when creating environment... Sitename: ".$sitename, 48);
			return 48;
		}

		$query = "UPDATE users SET sitename = ? WHERE id = ?";
		$stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "si", $sitename, $id);
		$result = mysqli_stmt_execute($stmt);

        $done = 0;
        $action = "addwebhostingenv";

        $query = "INSERT INTO webhosting (realID, action, sitename, mysql_username, mysql_password, done) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($dbc, $query);
        mysqli_stmt_bind_param($stmt, "issssi", $id, $action, $sitename, $mysql_username, $mysql_password, $done);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
        	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while adding webhosting environment for realID ".$id." and for sitename ".$sitename."... Error:".mysqli_error($dbc), 1);
        	return 1;
        }
 
        return 50;

	}

	public function resetWebhostingEnvPermissions($id, $sitename, $dbc) {
		$lacicloud_errors_api = new Errors();
		$lacicloud_ftp_api = new FTPActions();

		if ($lacicloud_ftp_api->getUserValues($id, $dbc)["tier"] == 1 or empty($sitename)) {
			return 54;
		}

        $done = 0;
        $action = "resetperms";

        $query = "INSERT INTO webhosting (realID, action, sitename, done) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($dbc, $query);
        mysqli_stmt_bind_param($stmt, "issi", $id, $action, $sitename, $done);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
        	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while adding reset permissions command to webhosting environment for realID ".$id." and for sitename ".$sitename."... Error:".mysqli_error($dbc), 1);
        	return 1;
        }
 
        return 51;
	}

	public function resetWebhostingEnvMysql($id, $sitename, $mysql_username, $dbc) {
		$lacicloud_errors_api = new Errors();
		$lacicloud_ftp_api = new FTPActions();

		if ($lacicloud_ftp_api->getUserValues($id, $dbc)["tier"] == 1 or empty($sitename)) {
			return 54;
		}

		$done = 0;
		$action = "resetmysql";
		$mysql_password = $this->generateMysqlPassword();

		//reset mysql password on normal
		$query = "UPDATE webhosting SET mysql_username = ?, mysql_password = ? WHERE (realID=? AND action='addwebhostingenv')";
		$stmt = mysqli_prepare($dbc, $query);
        mysqli_stmt_bind_param($stmt, "ssi", $mysql_username, $mysql_password, $id);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
        	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error (1) while adding reset mysql command to webhosting environment for realID ".$id." and for sitename ".$sitename."... Error:".mysqli_error($dbc), 1);
        	return 1;
        }

        //add reset command
        $query = "INSERT INTO webhosting (realID, action, sitename, mysql_username, mysql_password, done) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($dbc, $query);
        mysqli_stmt_bind_param($stmt, "issssi", $id, $action, $sitename, $mysql_username, $mysql_password, $done);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
        	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error (2) while adding reset mysql command to webhosting environment for realID ".$id." and for sitename ".$sitename."... Error:".mysqli_error($dbc), 1);
        	return 1;
        }

        return 52;


	}

	public function getWebhostingValues($id, $dbc) {
		$lacicloud_errors_api = new Errors();

		$query = "SELECT sitename, mysql_username, mysql_password FROM webhosting WHERE (realID = ? AND action='addwebhostingenv')";
	    $stmt = mysqli_prepare($dbc, $query);
	    mysqli_stmt_bind_param($stmt, "i", $id);
	    $result = mysqli_stmt_execute($stmt);

	    if (!$result) {
	    	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while getting webhosting user's values... Error: ".mysqli_error($dbc), 1);
	    	return 1;
	    }

	    $stmt->bind_result($mysql_sitename, $mysql_mysql_username, $mysql_mysql_password);

	    $values_array = array();
	    while ($stmt->fetch()) {

	            $values_array["sitename"] = $mysql_sitename;

	            $values_array["mysql_host"] = "localhost";

	            $values_array["mysql_username"] = $mysql_mysql_username;

	            $values_array["mysql_password"] = $mysql_mysql_password; 

	    }

	    if (!isset($values_array["sitename"])) {
	    	$values_array["sitename"] = "";
	    	$values_array["mysql_username"] = "";
	    }

	    return $values_array;
	}

	public function generateMysqlPassword() {
		return bin2hex(openssl_random_pseudo_bytes(16));
	}

	//mysql 5.6 limitation: username must be smaller than or equal to 16
	public function generateMysqlUsername() {
		return bin2hex(openssl_random_pseudo_bytes(8));
	}

}

//anything related to error handling, or displaying errors
class Errors extends LaciCloud {

	//most of the error codes and their messages
	private $messages = array(
		1 => "An internal error occured... Damn! If you are adding an FTP user, another user already has that name taken or you already have this FTP username!",
		2 => "Session could not be started.. Sorry!",
		3 => "Session started successfully... Yay!",
		4 => "An error occured while validating your user information... Please try again!",
		5 => "User info validated successfully... Yay!",
		6 => "An error occured while increasing page visit counter... Damn!",
		7 => "Page visit counter increased successfully... Yay!",
		8 => "",
		9 => "",
		10 => "The captcha was entered incorrectly... Please try again!",
		11 => "Captcha validated successfully... Yay!",
		12 => "Account has not been confirmed yet... Please confirm and try again!",
		13 => "Successfully logged in... Yay!",
		14 => "An error occured while confirming your account with key... Please try again!",
		15 => "Account confirmed successfully... Yay! Please log-in!",
		16 => "",
		17 => "Email already exists in database... Please try again or reset your password!",
		18 => "An unfortunate error occured while sending the email... Sorry!",
		19 => "Account has been successfully created... Please confirm now from your xXxemailxXx!",
		20 => "The user account has been successfully deleted... Yay, but sad to see you go!", //RIP user
		21 => "Email accepted... Please check your xXxemailxXx for further instructions!",
		22 => "Reset key could not be validated... Please try again!",
		23 => "Your new password can not be the same as the old one... Please try again!",
		24 => "Successfully reset account! You can xXxresetfixxXx now!",
		25 => "Session timed-out... Please log-in again if you wish to continue!",
		26 => "First-time setup completed successfully... Yay!",
		27 => "Sorry, you cannot downgrade a tier or upgrade to the same tier you are currently on!",
		28 => "You can change to this tier if you wish... Yay!",
		29 => "Successfully upgraded tier... Yay!", //function of upgrading tiers
		30 => "You\"r email has been successfully sent... Yay!",
		31 => "An error occured while validating FTP user information... Please try again!",
		32 => "FTP user successfully created... Yay!",
		33 => "FTP username incorrect; no such FTP user exists under your account... Please try again!",
		34 => "FTP user successfully removed... Yay!",
		35 => "Internal error occured while changing to this tier... Sorry!",
		36 => "Successfully accepted payment, tier will be upgraded shortly... Yay! Click xXxherexXx to return to the interface page!", //from payments
		37 => "Email successfully sent... Yay!",
		38 => "QFTP user successfully created with username xXxusernamexXx and password xXxpasswordxXx!",
		39 => "API key successfully regenerated!",
		40 => "Username or password incorrect... Please try again!",
		43 => "API key incorrect... Please try again!",
		44 => "Not enough parameters supplied for API... Please try again!",
		45 => "API call received OK... Yay!",
		46 => "You need to log-in or create an account to use the shop page... Please log-in!",
		47 => "Payment cancelled or an error occured! Please contact support if you are encountering issues.",
		48 => "Sitename already exists or is not alphanumeric!",
		49 => "Sitename validated!",
		50 => "Successfully added webhosting environment!",
		51 => "Successfully reset permissions on webhosting environment!",
		52 => "Successfully reset MySql password on webhosting environment!",
		53 => "IPN/Payment error!",
		54 => "Sorry, action is disallowed for your tier or your webhosting options!",
	);

	private $result_messages_map = array(
		1 => "error",
		2 => "error",
		3 => "success",
		4 => "error",
		5 => "success",
		6 => "error",
		7 => "success",
		8 => "",
		9 => "",
		10 => "error",
		11 => "success",
		12 => "warning",
		13 => "login",
		14 => "error",
		15 => "success",
		16 => "",
		17 => "warning",
		18 => "error",
		19 => "success",
		20 => "success",
		21 => "success",
		22 => "error",
		23 => "error",
		24 => "warning",
		25 => "warning",
		26 => "success",
		27 => "warning",
		28 => "success",
		29 => "success",
		30 => "success",
		31 => "error",
		32 => "success",
		33 => "error",
		34 => "success",
		35 => "error",
		36 => "success",
		37 => "success",
		38 => "success",
		39 => "success",
		40 => "error",
		41 => "error",
		42 => "success",
		43 => "error",
		44 => "error",
		45 => "success",
		46 => "warning",
		47 => "warning",
		48 => "error",
		49 => "success",
		50 => "success",
		51 => "success",
		52 => "success",
		53 => "error",
		54 => "error"
		);

	public function getSuccessOrErrorFromID($id) {
		return $this->result_messages_map[$id];
	}

	public function getErrorMSGFromID($id) {
		return $this->messages[$id];
	}

	public function msgLogger($severity, $msg, $id) {
		 $lacicloud_api = new LaciCloud();

		 $bt = debug_backtrace();
  		 $caller = array_shift($bt);

  		 if ($severity == "CRIT" or $severity == "SEVERE" or $severity == "API") {
  		 	@$POST = print_r($_POST, true);
  			@$GET = print_r($_GET, true);
  		 	@$SERVER = print_r($_SERVER, true);

  		 }
  		 

  		 @$message = "\n".date('l jS \of F Y h:i:s A').':'." Severity: ".$severity." Message: ".$msg." Error ID: ".$id." File: ".$caller['file']." Line: ".$caller['line']." User ID: ".$_SESSION["id"]." IP: "."0.0.0.0"." UA: ".$_SERVER['HTTP_USER_AGENT']." Referer: ".$_SERVER["HTTP_REFERER"]." POST: ".$POST." GET: ".$GET." SERVER: ".$SERVER."\n\n";

		 error_log($message, 3, $lacicloud_api->document_root."/logs/website_custom.txt");
		 
	}


}

class API extends LaciCloud {

	public function verifyAPIKey($api_key, $dbc) {

		if (!isset($api_key)) {
			return 43;
		}

		$query = "SELECT api_key,id FROM users WHERE api_key = ?";
		$stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "s", $_POST["api_key"]);
		$result = mysqli_stmt_execute($stmt);


		$stmt->bind_result($mysql_api, $mysql_id);


		    /* fetch values */

		while ($stmt->fetch()) {

			$api_key = $mysql_api;
			$id = $mysql_id;

		}

		if (!$result or empty($id)) {
			return 43;
		}

		return array("id" => $id, "api_key" => $api_key);

	}

	//two very useful functions
	public function getNotEnoughParametersSuppliedErrorID() {
		return 44;
	}

	public function getAPIRequestOKSuccessID() {
		return 45;
	}

	//id as in error id
	public function returnJSONObject($id, $success) {
		$lacicloud_errors_api = new Errors();

		return json_encode(array("ID" => $id, "MSG" => $lacicloud_errors_api -> getErrorMSGFromID($id) ,"Success" => $success));

	}


}

//aynthing related to qFTP
class qFTP extends LaciCloud {

	private $qFTPUserId = 0; 

	private $qFTPUserSpace = 2048;
	
	private $qFTPUserULSpeed = 128;
	
	private $qFTPUserDLSpeed = 256;


	public function generateFTPUsername($len = 8) {
		/* Programmed by Christian Haensel
		** christian@chftp.com
		** http://www.chftp.com
		**
		** Exclusively published on weberdev.com.
		** If you like my scripts, please let me know or link to me.
		** You may copy, redistribute, change and alter my scripts as
		** long as this information remains intact.
		**
		** Modified by Josh Hartman on 12/30/2010.
		*/
		if(($len%2)!==0){ // Length paramenter must be a multiple of 2
			$len=8;
		}

		$length=$len-2; // Makes room for the two-digit number on the end
		$conso=array('b','c','d','f','g','h','j','k','l','m','n','p','r','s','t','v','w','x','y','z');
		$vocal=array('a','e','i','o','u');
		$username='';
		srand ((double)microtime()*1000000);
		$max = $length/2;
		for($i=1; $i<=$max; $i++){
			$username.=$conso[rand(0,19)];
			$username.=$vocal[rand(0,4)];
		}
		$username.=rand(10,99);
		return $username;

	}

	public function addQFTPUser($username, $password, $captcha, $dbc_ftp) {
		$lacicloud_api = new LaciCloud();
		$lacicloud_errors_api = new Errors();

		if ($lacicloud_errors_api->getSuccessOrErrorFromID($lacicloud_api -> checkCaptcha($captcha)) !== "success") {
    		return 10;
  		}

		$ftp_starting_directory = $lacicloud_api->document_root."/users/qftp/".$username;
		$expiration = strtotime('+1 day', time());
		//this also checks if FTP user already exists
		$query = "INSERT INTO ftp_users (user, password, home, quota, uploadspeed, downloadspeed, realID, expiration) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = mysqli_prepare($dbc_ftp, $query);
		//you can never be sure, so lets go with 16bytes for salt
		$crypt_ftp_password = crypt($password, '$6$rounds=5000$'.bin2hex(openssl_random_pseudo_bytes(16)).'$');

		mysqli_stmt_bind_param($stmt, "sssiiiii", $username, $crypt_ftp_password, $ftp_starting_directory, $this->qFTPUserSpace, $this->qFTPUserULSpeed, $this->qFTPUserDLSpeed, $this->qFTPUserId, $expiration);
		$result = mysqli_stmt_execute($stmt);

		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while adding QFTP user... Error: ".mysqli_error($dbc_ftp), 1);
			return 1;
		}


		return 38;

	}

}

//email templating system
class Emails extends LaciCloud {
		public function getEmailTemplate() {

		$lacicloud_api = new LaciCloud();

		$template_html = file_get_contents(__DIR__."/SwiftMailer/email_template.html");
		return $template_html;
		}

}

//anything that does not belong to the core of LaciCloud
class Utils extends LaciCloud {
	public function getEmailProvider($email) {

		//strrpos is not misspelled
		$email_provider = substr($email, strrpos($email, '@') + 1);
					
		if ($email_provider == "gmail.com") {
				$message = "Open Gmail";
				$link = "https://mail.google.com/";
		} elseif ($email_provider == "yahoo.com" or $email_provider == "ymail.com") {
				$message = "Open Yahoo Mail";
				$link = "https://login.yahoo.com/";
		} elseif ($email_provider == "msn.com" or $email_provider == "hotmail.com" or $email_provider == "live.com" or $email_provider == "MSN.com" ) {
				$message = "Open Outlook Mail";
				$link = "https://login.live.com";
		} elseif ($email_provider == "yandex.com" or $email_provider == "yandex.ru" or $email_provider == "yandex.by" or $email_provider == "yandex.kz" or $email_provider == "yandex.ua") {
				$message = "Open Yandex Mail";
				$link = "https://mail.yandex.com/";
		} elseif ($email_provider == "mt2015.com") {
				$message = "Open MyTrashMail";
				$link = "http://www.mytrashmail.com/";
		} elseif ($email_provider == "sharklasers.com" or $email_provider == "guerillamail.com" or $email_provider == "guerillamail.net" or $email_provider == "guerillamail.org" or $email_provider == "guerillamail.de") {
				$message = "Open GuerillaMail";
				$link = "https://www.guerrillamail.com/";
		} elseif ($email_provider == "tutanota.com" or $email_provider == "tutanota.de" or $email_provider == "tutamail.com" or $email_provider == "tuta.io" or $email_provider == "keemail.me") {
				$message = "Open Tutanota Mail";
				$link = "https://app.tutanota.de/#login";
		} elseif ($email_provider == "protonmail.com" or $email_provider == "protonmail.ch") {
				$message = "Open ProtonMail";
				$link = "https://mail.protonmail.com/login";
		} else {
			$message = "Open Google";
			$link = "https://google.com";
		}

		return array($message, $link);
					
	}

	public function getEmailUserName($email) {
		$email_array = explode("@", $email);
		$username = $email_array[0];

		//just to be safe, remove all non-alphanum, including spaces
		$username = preg_replace("/[^A-Za-z0-9 ]/", '', $username);
		$username = str_replace(" ", "", $username);

		return $username;
	}

	public function getBrowserName() {
		@$user_agent = $_SERVER['HTTP_USER_AGENT'];

	    if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) return 'Opera';
	    elseif (strpos($user_agent, 'Edge')) return 'Edge';
	    elseif (strpos($user_agent, 'Chrome')) return 'Chrome';
	    elseif (strpos($user_agent, 'Safari')) return 'Safari';
	    elseif (strpos($user_agent, 'Firefox')) return 'Firefox';
	    elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) return 'Internet Explorer';
	    	
	    return 'Other';
	}

}
?>
