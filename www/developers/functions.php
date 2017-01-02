<?php
//API & Functions

//~Be smart. Be clean. Be simple. Ship! And keep a small roll of duct tape at the ready, and don’t be afraid to use it. - Uncle Bob (From Robert Martin's blog)

//if method call is successfull returns true (or desired value), else returns error code id
//warning: You must check for success with === true, not just if($api -> method) way, as PHP regards numbers > 0 as true

//captcha
require_once('localweb/securimage_captcha/securimage.php');
//swiftmailer for sending emails
require_once('SwiftMailer/swift_required.php');

//for Login page
require_once('BruteForceBlock/BruteForceBlock.php');

//payments
require_once("GoUrl/cryptobox.class.php");

//LaciCloud core functions that directly interact with the database or the server in any way
class LaciCloud {
	
	private $bcrypt_options = [
  		  'cost' => 12, //should be good for a few more years
	];

	private $secrets_file = "secrets.ini";

	//default values to use when creating account

	private $first_time_boolean_default = 0;

	private	$reset_key_default = 0;

	protected $document_root = "/var/ftp";

	public $login_throttle_settings = [
			5 => 'captcha'	//captcha
	];

	public $unix_time_1_month = 2629743;

	protected $usedSpaceDefault = 0;
	
	public $valid_pages_array = ["0","1","1_1","1_2","2","2_1","2_2","3","4"];

	public function grabSecret($name) {
		$secrets = parse_ini_file($this->document_root."/www/developers/".$this->secrets_file);
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
			 $lacicloud_errors_api -> msgLogger("CRIT", 'Could not connect to FTP MySQL server... Connect error: '.mysqli_connect_error($dbc_ftp).' Error: '.mysqli_error($dbc_ftp), 2);
			 return 2;
		} else {
			return $dbc_ftp;
		}

	}

	public function startSession() {
		$lacicloud_errors_api = new Errors();

		session_name("secure_session");

		if(!session_start()) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "Could not start session... Session id: ".session_id(), 3);
			return 3;
		}

		session_regenerate_id(true);

		return true;
	}

	private function validateUserInfo($email, $password, $password_retyped) {
		$lacicloud_errors_api = new Errors();

		if (empty($email) or empty($password)) {
			$lacicloud_errors_api -> msgLogger("LOW", "Email/Password empty when logging in or creating account!", 5);
			return 5;

		}

		if (preg_match('/\s/',$email) or strlen($email) < 5 or strlen($email) > 320 or !strpos($email, "@") or !strpos($email, ".")) {
			$lacicloud_errors_api -> msgLogger("LOW", "Email is invalid... Email: ".$email, 5);
			return 5;
		}

		if ($email == $password) {
			$lacicloud_errors_api -> msgLogger("LOW", "Email same as password...", 5);
			return 5;
		}

		if (strlen($password) < 8 or !preg_match("#[0-9]+#", $password) or !preg_match("#[a-zA-Z]+#", $password)) {
			$lacicloud_errors_api -> msgLogger("LOW", "Password strenght too weak...", 5);
			return 5;
		}


		if ($password !== $password_retyped){
			$lacicloud_errors_api -> msgLogger("LOW", "Password not the same as retyped password", 5);
			return 5; 

		}

		return true;
	}

	//nice to have
	public function increasePageVisitCounter($dbc) {
			$lacicloud_errors_api = new Errors();

			$query = "UPDATE counter SET count = count + 1";
			$stmt = mysqli_prepare($dbc, $query);
			$result = mysqli_stmt_execute($stmt);

			if (!$result) {
				$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while incrementing index page counter... Error:".mysqli_error($dbc), 8);
			}

			return true;
	}

	//nice to have
	private function increaseUserLoginCounter($dbc) {
			$lacicloud_errors_api = new Errors();

			$query = "UPDATE counter SET logins = logins + 1";


			$stmt = mysqli_prepare($dbc, $query);

			       
			$result = mysqli_stmt_execute($stmt);

			//non-critical so error is only logged
			if (!$result) {
				$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while incrementing login counter... Error:".mysqli_error($dbc), 7);
			}

			return true;
	}

	public function loginUser($email, $password, $captcha, $dbc) {
		$lacicloud_errors_api = new Errors();

		$BFBresponse = ejfrancis\BruteForceBlock::getLoginStatus($this->login_throttle_settings, $_SERVER["REMOTE_ADDR"]);

		switch ($BFBresponse['status']){
		      case 'safe':
		          //login allowed, continue
		          break;
		      case 'captcha':
		          //captcha required	  	
				  if ($this -> checkCaptcha($captcha) !== true) {
						return 4;
				  } else {
				  	$BFBresponse = ejfrancis\BruteForceBlock::clearDatabase($_SERVER["REMOTE_ADDR"]);
				  }

		          break;
		  }


		//don't waste memory by quering email/password combinations that couldn't have existed in the first place
		if ($this -> validateUserInfo($email, $password, $password) !== true) {
			$lacicloud_errors_api -> msgLogger("LOW", "Email/Password not valid when logging in... Email: ".$email, 5);
			return 5;
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
			$lacicloud_errors_api -> msgLogger("LOW", "Wrong Email when logging in... Email: ".$email, 5);
			return 5;
		}


		if (!\password_verify($password, $database_password) or $email !== $database_email) {
			$lacicloud_errors_api -> msgLogger("LOW", "Wrong password when logging in... Id: ".$id, 5);
			$BFBresponse = ejfrancis\BruteForceBlock::addFailedLoginAttempt($id, $_SERVER['REMOTE_ADDR']);
			return 5;
		}

		if ((int)$user_unique_key !== 1) {
			$lacicloud_errors_api -> msgLogger("LOW", "Account not confirmed when logging in... Id: ".$id, 6);
			return 6;
		}

		$this -> increaseUserLoginCounter($dbc);

		//set login variables
		$_SESSION["logged_in"] = 1;
		$_SESSION["csrf_token"] = bin2hex(openssl_random_pseudo_bytes(16));
		$_SESSION["id"] = $id;

		return true;

	}

	public function confirmAccount($unique_key, $dbc) {
		$lacicloud_errors_api = new Errors();

		if (empty($unique_key)) {
			$lacicloud_errors_api -> msgLogger("LOW", "Unique key empty when confirming account...", 9);
			return 9; 
		}

		$unique_key = preg_replace('/\s+/', '', $unique_key); //strip whitespace


		$query = "SELECT unique_id FROM users WHERE unique_id=?";
        $stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "s", $unique_key);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
        	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while confirming account... Error: ".mysqli_error($dbc), 9);
        	return 9;
        }

        $stmt->bind_result($mysql_database_user_unique_key);

        while ($stmt->fetch()) {

        	$database_user_unique_key = $mysql_database_user_unique_key;

		}

		if (empty($database_user_unique_key)) {
			$lacicloud_errors_api -> msgLogger("LOW", "Unique key incorrect when confirming account... Unique key: ".$unique_key, 9);
			return 9;
		}


		$query = "UPDATE users SET unique_id='1' WHERE unique_id=?";
        $stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "s", $unique_key);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
        	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while confirming account... Error:".mysqli_error($dbc), 9);
        	return 9;
        }

        return true;


	}

	public function registerUser($email, $password, $password_retyped, $captcha, $dbc) {
		$lacicloud_errors_api = new Errors();
		
		if ($this -> checkCaptcha($captcha) !== true) {
			return 4;
		}


		if ($this -> validateUserInfo($email, $password, $password_retyped) !== true) {
			return 10;
		}

		//check terms & conditions checkbox
		if (count($_POST["checkbox"]) == 0) {
			$lacicloud_errors_api -> msgLogger("LOW", "Terms & Conditions checkbox not checked when creating account...", 10);
			return 10;  
		}

		//check wheter email already exists
		$query = "SELECT email FROM users WHERE email = ?";
		$stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "s", $email);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
        	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while creating account... Error: ".mysqli_error($dbc), 10);
        	return 10;
        }

        $stmt->bind_result($mysql_email);

        while ($stmt->fetch()) {

        	$email_in_database = $mysql_email;

		}

		if (!empty($email_in_database)) {
			$lacicloud_errors_api -> msgLogger("LOW", "Email already exists when creating account... Email: ".$email, 12);
			return 12;
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
        	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while creating user account... Error:".mysqli_error($dbc)." Affected rows: ".$affected_rows, 10);
        	return 10;
        }

        //check wheter email already exists
		$query = "SELECT id FROM users WHERE password = ?";
		$stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "s", $bcrypt_password);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
        	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while creating account... Error: ".mysqli_error($dbc), 10);
        	return 10;
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
        	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while creating account... Error: ".mysqli_error($dbc), 10);
        	return 10;
        } 

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
			$cid = $message->embed(Swift_Image::fromPath($this->document_root.'/www/developers/localweb/resources/logo.png'));
			$message 
				->setSubject("Confirm your account!")
				->setFrom(array("bot@lacicloud.net" => "LaciCloud"))
				->setTo(array("$email"))
				->setCharset('utf-8') 
				->setBody("<html><body><img src='".$cid."' alt='LaciCloud Logo'><br>Hi ".$email."!<br>Please confirm your account here: <br>"."<a href='https://lacicloud.net/login/?unique_key=".$user_unique_key."'>Click this link to confirm your account</a>"."<br><br>Have a great day, <br>The LaciCloud Team</body></html>",'text/html');
			$result = $mailer->send($message, $errors);
	    } catch(\Swift_TransportException $e){
	        $response = $e->getMessage();
	        $result = false;
	    } catch (Exception $e) {
	    	$response = $e->getMessage();
	    	$result = false; 
	    }

     
		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "Could not send email when creating account... Error:\n".$logger->dump()." Exception error:\n".$response,10);

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

			return 10;
		}

		return true;


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

		return true;
	}



	//generate key and send email
	public function forgotLoginStep1($email, $captcha, $dbc) {
		$lacicloud_errors_api = new Errors();

		if ($this -> checkCaptcha($captcha) !== true) {
			return 4;
		}

		$query = "SELECT email FROM users WHERE email=?";
		$stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "s", $email);
		$result = mysqli_stmt_execute($stmt);

		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while resetting account... Error: ".mysqli_error($dbc), 15);
			return 15;
		}

		$stmt->bind_result($mysql_email);
   	 	while ($stmt->fetch()) {
        	$email_in_database = $mysql_email;
		}

		if (empty($email_in_database)) {
			$lacicloud_errors_api -> msgLogger("LOW", "Email not in database when resetting account... Email: ".$email, 15);
			return 15;
		}

		$reset_key = bin2hex(openssl_random_pseudo_bytes(32));

		$query = "UPDATE users SET reset_key=? WHERE email=?";
		$stmt = mysqli_prepare($dbc, $query);	
		mysqli_stmt_bind_param($stmt, "ss", $reset_key, $email);
		$result = mysqli_stmt_execute($stmt);
		$affected_rows = mysqli_stmt_affected_rows($stmt);

		if (!$result or $affected_rows !== 1) {
				  $lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while resetting account... Error: ".mysqli_error($dbc), 15);
		      	 return 15;
		}

	


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
			$cid = $message->embed(Swift_Image::fromPath($this->document_root.'/www/developers/localweb/resources/logo.png'));
			$message 
				->setSubject("Reset Key")
				->setFrom(array("bot@lacicloud.net" => "LaciCloud"))
				->setTo(array("$email"))
				->setCharset('utf-8') 
				->setBody("<html><body><img src='".$cid."' alt='LaciCloud Logo'><br>Hi ".$email."! You have requested a login reset. Please click this link to proceed: <a href='https://lacicloud.net/forgot?reset_key=".$reset_key."'>Reset Login</a> or copy-paste your reset-key: ".$reset_key."<br>If you didn't request this, feel free to ignore this email but do report it by sending an email to laci@lacicloud.net!<br>Have a great day, <br>The LaciCloud Team</body></html>",'text/html');
			$result = $mailer->send($message, $errors);
	    } catch(\Swift_TransportException $e){
	        $response = $e->getMessage();
	        $result = false;
	    } catch (Exception $e) {
	    	$response = $e->getMessage();
	    	$result = false; 
	    }


		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "Could not send email when resetting account... Error:\n".$logger->dump()." Exception error:\n".$response,15);
			return 15;
		}

		$_SESSION["reset"] = true;
		$_SESSION["email"] = $email;

		//reset post array
		$_POST = array();

		return true;


	}

	//check key and update password - finish!!
	public function forgotLoginStep2($email, $password, $password_retyped, $reset_key, $dbc) {
		$lacicloud_errors_api = new Errors();

		if (empty($reset_key)) {
			$lacicloud_errors_api -> msgLogger("LOW", "Empty reset key when updating account details...", 13);
			return 13;
		}

		if ($this -> validateUserInfo($email, $password, $password_retyped) !== true) {
			return 13;
		}

		$reset_key = preg_replace('/\s+/', '', $reset_key);

		$query = "SELECT reset_key,password FROM users WHERE reset_key=?";
		$stmt = mysqli_prepare($dbc, $query);		
		mysqli_stmt_bind_param($stmt, "s", $reset_key);
		$result = mysqli_stmt_execute($stmt);
        
		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while resetting password... Error: ".mysqli_error($dbc), 13);
			return 13;
		}

        $stmt->bind_result($mysql_reset_key, $mysql_old_user_password);
   	 	while ($stmt->fetch()) {
        	$reset_key = $mysql_reset_key;
        	$old_user_password = $mysql_old_user_password;
		}

		if(empty($reset_key)) {
			$lacicloud_errors_api -> msgLogger("LOW", "User reset key incorrect when resetting password...", 13);
			return 13;
		}

		if (\password_verify($password, $old_user_password)) {
			$lacicloud_errors_api -> msgLogger("LOW", "Old password/new password same when resetting password...", 14);
			return 14; 
		}

		$password =  password_hash($password, PASSWORD_DEFAULT, $this->bcrypt_options);

		$query = "UPDATE users SET password=?,reset_key=? WHERE reset_key=?";
		$stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "sis", $password, $this->reset_key_default, $reset_key);
		$result = mysqli_stmt_execute($stmt);
		

		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while updating user information... Error:".mysqli_error($dbc), 13);
			return 13;
		}

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
			$cid = $message->embed(Swift_Image::fromPath($this->document_root.'/www/developers/localweb/resources/logo.png'));
			$message 
				->setSubject("Account information successfully reset!")
				->setFrom(array("bot@lacicloud.net" => "LaciCloud"))
				->setTo(array("$email"))
				->setCharset('utf-8') 
				->setBody("<html><body><img src='".$cid."' alt='LaciCloud Logo'><br>Hi ".$email."! Your login reset was successful. If you didn't request this, please send an email to laci@lacicloud.net!<br>IP of the person who requested this: ".$_SERVER["REMOTE_ADDR"]."<br>Have a great day, <br>The LaciCloud Team</body></html>",'text/html');
			$result = $mailer->send($message, $errors);
	    } catch(\Swift_TransportException $e){
	        $response = $e->getMessage();
	        $result = false;
	    } catch (Exception $e) {
	    	$response = $e->getMessage();
	    	$result = false; 
	    }


		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "Could not send email when resetting account (step 2)... Error:\n".$logger->dump()." Exception error:\n".$response,40);
		}

		$_POST = array();

		return true; 

	}

	public function verifyShit() {
		//should be 'nough
		//check UA
		if (isset($_SESSION['HTTP_USER_AGENT'])) {
		    if ($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['HTTP_USER_AGENT']))
		    {
		        return 16;
		    } 

		} else {
		    $_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);
		}

		//check IP
		if (isset($_SESSION["REMOTE_ADDR"])) {
			if ($_SESSION["REMOTE_ADDR"] != md5($_SERVER["REMOTE_ADDR"])) {
				return 16;
			}
		} else {
			$_SESSION['REMOTE_ADDR'] = md5($_SERVER['REMOTE_ADDR']);
		}

		return true;

	}

	public function verifyCSRF($token) {
		if ($_SESSION["csrf_token"] !== $token) {
			return 16;
		}

		return true;
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

	public function isIndexPageCached() {
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
		if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])==$lastModified || $etagHeader == $etagFile)
		{
		      header($_SERVER["SERVER_PROTOCOL"]." 304 Not Modified");
		      return true;
		} else {
			return false;
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

        return true;
	}

	public function checkCaptcha($captcha) {
		$lacicloud_errors_api = new Errors();

		$securimage = new Securimage();
		$securimage->database_user = $this -> grabSecret("db_user_captcha");
		$securimage->database_pass = $this -> grabSecret("db_password_captcha");
		$securimage->database_name = 'laci_corporations_users';
		$securimage->database_table = 'captcha_codes';

		$correct_code = $securimage->getCode(false, true);
	    if ($securimage->check($captcha) == false) {
	        $lacicloud_errors_api -> msgLogger("LOW", "Captcha code incorrect... User-inputted Captcha: ".$captcha." Correct captca: ".$correct_code, 4);
	        return 4;
	    }

	    return true;
	}

	public function canChangeToTier($tier, $id, $dbc, $dbc_ftp) {
		$lacicloud_errors_api = new Errors();
		$lacicloud_ftp_api = new FTPActions();

	    $ftp_space = $this -> getTierData($tier)[0] - $lacicloud_ftp_api -> getFTPUsersUsedSpace($id, $dbc);
		$ftp_space_virtual = $this -> getTierData($tier)[0] - $lacicloud_ftp_api -> getFTPUsersVirtuallyUsedSpace($id, $dbc_ftp);
		
		if ($ftp_space < 0.0 or $ftp_space_virtual < 0.0 or (int)$tier == (int)$lacicloud_ftp_api -> getUserValues($id, $dbc)["tier"]) {
				return 49;
		}

		return true;
	}

	public function upgradeToTier($tier, $orderID, $id, $dbc) {
		$lacicloud_errors_api = new Errors();

		$ftp_space = $this -> getTierData($tier)[0];
		$limit = $this -> getTierData($tier)[1];

		$time = time();
	
		$query = "UPDATE users SET tier=?,lastpayment=? WHERE id=?";
        $stmt = mysqli_prepare($dbc, $query);
        mysqli_stmt_bind_param($stmt, "iii", $tier, $time, $id);
        $result = mysqli_stmt_execute($stmt);

        if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while upgrading to tier ".$tier." for orderID ".$orderID."... Error:\n".mysqli_error($dbc), 51);
			return 51; 
		}

        return true;
	}

	public function getTierData($tier) {
		$data = array(1 => array(10000, 10, 256, 600, "- Free - For Occasional Users"), 2 => array(25000, 50, 512, 2000, " - 10€ / Month - For regular Users"), 3 => array(50000, 75, 1000, 5000, " - 20€ / Month - For advanced Users"));

		return $data[$tier];

	}


}

//things such as creating FTP user, deleting FTP user
class FTPActions extends LaciCloud {

	public function validateFTPUserInfo($ftp_username, $ftp_password, $ftp_space_currency, $ftp_space_specified, $starting_directory) {
		$lacicloud_errors_api = new Errors();

		if (empty($ftp_username) or empty($ftp_space_specified) or empty($starting_directory)) {
			$lacicloud_errors_api -> msgLogger("LOW", "Values empty when creating FTP user...", 21);
			return 21;
		}

		if (strlen($ftp_username) < 3 or !ctype_alnum($ftp_username)) {
			$lacicloud_errors_api -> msgLogger("LOW", "FTP username invalid when creating FTP user... Username: ".$ftp_username, 21);
			return 21;
		} 

		if (!is_numeric($ftp_space_specified) or (float)$ftp_space_specified <= 0) {
			$lacicloud_errors_api -> msgLogger("LOW", "FTP space specified invalid... Specified: ".$ftp_space_specified, 21);
			return 21;
		}

		//Directory traversal attack check 
		$starting_directory = rawurldecode($starting_directory);

		
		if (!preg_match('/^[\p{L}0-9\s-]+$/u', str_replace("/", "" ,$starting_directory)) and $starting_directory != "/") {
			$lacicloud_errors_api -> msgLogger("LOW", "Starting directory not in valid format... Specified: ".$starting_directory, 21);
			return 21;
		}
		

		if ($starting_directory[0] !== "/" or strpos($starting_directory, "../") !== FALSE or strpos($starting_directory, "..\\\\") !== FALSE) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "Directory traversal attempt... Specified: ".$starting_directory, 21);
			return 21;
		}

		if ($starting_directory == "/" and empty($ftp_password)) {
			$lacicloud_errors_api -> msgLogger("LOW", "Master account without FTP user password attempted...", 21);
			return 21;
		}

		if (!empty($ftp_password)) {
			if (strlen($ftp_password) < 8 or !preg_match("#[0-9]+#", $ftp_password) or !preg_match("#[a-zA-Z]+#", $ftp_password)) {
				$lacicloud_errors_api -> msgLogger("LOW", "FTP account password strenght too weak...", 21);
				return 21;
			}
		}

		return true; 
	}

	public function addFTPUser($ftp_username, $ftp_password, $ftp_space_specified, $starting_directory, $ftp_space_currency, $id, $dbc, $dbc_ftp) {
		$lacicloud_api = new LaciCloud();
		$lacicloud_errors_api = new Errors();
		
		if ($this->validateFTPUserInfo($ftp_username, $ftp_password, $ftp_space_currency, $ftp_space_specified, $starting_directory) !== true) {
			return 21;
		} 


		//convert to MB
		if ($ftp_space_currency == "gb") {
			$ftp_space_specified = ($ftp_space_specified * 1024);
		} elseif ($ftp_space_currency == "tb") {
			$ftp_space_specified = ($ftp_space_specified * 1024) * 1024;
		}
		
		$tier = $this->getUserValues($id, $dbc)["tier"];
		
		$ftp_space_user_has = $lacicloud_api -> getTierData($tier)[0] - $this -> getFTPUsersUsedSpace($id, $dbc);
		$ftp_space_user_has_virtual = $lacicloud_api -> getTierData($tier)[0] - $this -> getFTPUsersVirtuallyUsedSpace($id, $dbc_ftp);

		$users_limit = $lacicloud_api -> getTierData($tier)[1];


		if ((float)$ftp_space_specified > $ftp_space_user_has or (float)$ftp_space_specified > $ftp_space_user_has_virtual) {
			$lacicloud_errors_api -> msgLogger("LOW", "User doesn't have enough FTP space for FTP user: ".$ftp_username." with FTP space: ".$ftp_space_specified." User's max: ".$ftp_space_user_has, 21);
			return 21;
		}

		$users_array = $this->getFTPUsersList($id, $dbc_ftp);
		$active_users = count($users_array);

		if ($active_users == $users_limit) {
			$lacicloud_errors_api -> msgLogger("LOW", "User hit FTP user's limit '".$users_limit."' Active users: '".$active_users."'...", 21);
			return 21;
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
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while adding FTP user with info; FTP Username: '".$ftp_username."' with Starting directory '".$ftp_starting_directory."' and FTP space '".$ftp_space_specified."' for ID '".$id."'... Error: ".mysqli_error($dbc_ftp), 21);
			return 21;
		}

		return true;

	}

	public function removeFTPUser($ftp_username, $id, $dbc, $dbc_ftp) {
		$lacicloud_api = new LaciCloud();
		$lacicloud_errors_api = new Errors();

		//validate with dummy data, except for the username
		if ($this->validateFTPUserInfo($ftp_username, bin2hex(openssl_random_pseudo_bytes(16)), "mb", "100", "/dummy") !== true) {
			return 22;
		} 

		$query = "SELECT realID,home FROM ftp_users WHERE user = ?";
		$stmt = mysqli_prepare($dbc_ftp, $query);
		mysqli_stmt_bind_param($stmt, "s", $ftp_username);
		$result = mysqli_stmt_execute($stmt);

		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while removing FTP user '".$ftp_username."'... Error: ".mysqli_error($dbc_ftp), 22);
			return 22;
		}

		$stmt->bind_result($mysql_ftp_user_real_id,$mysql_ftp_starting_directory);
		while ($stmt->fetch()) {
			$ftp_user_real_id = $mysql_ftp_user_real_id;
			$ftp_starting_directory = $mysql_ftp_starting_directory;
		}

		

		if ($id !== $ftp_user_real_id) {
			$lacicloud_errors_api -> msgLogger("LOW", "FTP user '".$ftp_username."' not his for user ID: ".$id." Real ID: ".$ftp_user_real_id, 22);
			return 22;
		}

		$query = "DELETE FROM ftp_users WHERE user = ?";
		$stmt = mysqli_prepare($dbc_ftp, $query);
		mysqli_stmt_bind_param($stmt, "s", $ftp_username);
		$result = mysqli_stmt_execute($stmt);

		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while removing FTP user '".$ftp_username."'... Error: ".mysqli_error($dbc_ftp), 22);
			return 22;
		}

		$query = "INSERT INTO ftpactions (value, type) VALUES (?, ?)";
		$stmt = mysqli_prepare($dbc, $query);

		//see ftpactions.py: type 0 is make symlink from /users/$id/public_files to /public_files/$id, type 1 is remove .ftpquota file so that FTP user directory can be deleted
		$ftpactions_type = 1;
		$ftpactions_starting_directory = str_replace($lacicloud_api->document_root."/users/", "", $ftp_starting_directory);
		
		mysqli_stmt_bind_param($stmt,"si", $ftpactions_starting_directory, $ftpactions_type);
		$result = mysqli_stmt_execute($stmt);

		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while registering new ftpactions.py action; type: '".$type."' Starting directory: '".$ftpactions_starting_directory."'... Error: ".mysqli_error($dbc), 22);
			return 22;
		}

		return true;

	}

	//returns array of user values like ftp_space, bitcoin_paid, etc
	public function getUserValues($id, $dbc) {
		$lacicloud_errors_api = new Errors();

		$query = "SELECT tier,first_time_boolean,api_key,email,lastpayment,id FROM users WHERE id = ?";
	    $stmt = mysqli_prepare($dbc, $query);
	    mysqli_stmt_bind_param($stmt, "i", $id);
	    $result = mysqli_stmt_execute($stmt);

	    if (!$result) {
	    	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while getting User's values... Error: ".mysqli_error($dbc), 25);
	    	return 25;
	    }

	    $stmt->bind_result($mysql_tier, $mysql_first_time, $mysql_api_key,  $mysql_email, $mysql_lastpayment, $mysql_id);

	    $values_array = array();
	    while ($stmt->fetch()) {

	            $values_array["tier"] = (int)$mysql_tier;

	            $values_array["first_time_boolean"] = $mysql_first_time;

	            $values_array["api_key"] = $mysql_api_key; 

	            $values_array["email"] = $mysql_email;

	            $values_array["lastpayment"] = (int)$mysql_lastpayment;

	            $values_array["id"] = (int)$mysql_id;

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
	    	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while getting FTP user's values... Error: ".mysqli_error($dbc_ftp), 25);
	    	return 25;
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
	    	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while getting FTP user's list... Error: ".mysqli_error($dbc_ftp), 25);
	    	return 25;
	    }

	    $stmt->bind_result($mysql_ftp_users_list);


	    $users_array = array();

	    while ($stmt->fetch()) {
	            $users_array[] = $mysql_ftp_users_list;
	    }

	    return $users_array;

	}
	
	public function getFTPUsersUsedSpace($id, $dbc) { 
		$lacicloud_errors_api = new Errors();

		$query = "SELECT used_space FROM truespacecounter WHERE id = ?";
	    $stmt = mysqli_prepare($dbc, $query);
	    mysqli_stmt_bind_param($stmt, "i", $id);
	    $result = mysqli_stmt_execute($stmt);

	    if (!$result) {
	    	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while getting FTP user's real used space... Error: ".mysqli_error($dbc), 25);
	    	return 25;
	    }

	    $stmt->bind_result($mysql_ftp_used_space);

	    while ($stmt->fetch()) {
	            $ftp_used_space = $mysql_ftp_used_space;
	    }

	    if ($ftp_used_space == NULL) {
	    	$ftp_used_space = 0;
	    }

	    return $ftp_used_space;

	}
	
	public function getFTPUsersVirtuallyUsedSpace($id, $dbc_ftp) {
		$lacicloud_errors_api = new Errors();

		$query = "SELECT SUM(quota) FROM ftp_users WHERE realID = ?";
	    $stmt = mysqli_prepare($dbc_ftp, $query);
	    mysqli_stmt_bind_param($stmt, "i", $id);
	    $result = mysqli_stmt_execute($stmt);

	    if (!$result) {
	    	$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while getting FTP user's virtually used space... Error: ".mysqli_error($dbc_ftp), 25);
	    	return 25;
	    }

	    $stmt->bind_result($mysql_ftp_used_space);

	    while ($stmt->fetch()) {
	            $ftp_used_space = $mysql_ftp_used_space;
	    }

	    if ($ftp_used_space == NULL) {
	    	$ftp_used_space = 0;
	    }

	    return $ftp_used_space;
	}

	public function regenerateAPIKey($id, $dbc) {
		$lacicloud_errors_api = new Errors();

		$api_key = bin2hex(openssl_random_pseudo_bytes(32));

		$query = "UPDATE users SET api_key=? WHERE id=?";
		$stmt = mysqli_prepare($dbc, $query);
		mysqli_stmt_bind_param($stmt, "si", $api_key, $id);
		$result = mysqli_stmt_execute($stmt);

		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL Error regenerating API key... Error: ".mysqli_error($dbc), 26);
			return 26;
		}

		return true;
	}

}

//anything related to Bitcoin payments and normal payments, such as creating invoice, callback function 
class Payments extends LaciCloud {

	//GoUrl payment vars
	private $available_cryptocurrency_payments = array('bitcoin', 'litecoin', 'dogecoin', 'potcoin', 'dash', 'speedcoin');
	private $def_payment = "bitcoin";
	private $def_language = "en";
	private $period = "1 MONTH";

	//12 = 10 euro, 22 = 20 euro
	private $paymentPriceArray = array("1" => 0.0,"2" => 12, "3" => 22); 

	//Stripe payment vars
	//...

	public function payWithGoUrl($tier, $id, $dbc, $dbc_ftp) {
		$lacicloud_api = new LaciCloud();
		$lacicloud_ftp_api = new FTPActions();
		$lacicloud_errors_api = new Errors();

		$amountUSD = $this->paymentPriceArray[$tier];

		if (!isset($amountUSD)) {
			$lacicloud_errors_api -> msgLogger("LOW", "amountUSD is not set when paying... amountUSD: ".$amountUSD." Tier: ".$tier." orderID: ".$orderID, 24);
			return 24;
		}
		
		$orderID = "tier_".$tier."_".$id."_".date('n');

		//check if user can change to said tier
		if ($lacicloud_api -> canChangeToTier($tier, $id, $dbc, $dbc_ftp) !== true) {
			$lacicloud_errors_api -> msgLogger("LOW", "User is not allowed to upgrade/downgrade to tier: ".$tier." for orderID: ".$orderID, 51);
			return 49;
		}

		//no payment is required for tier 1
		if ($tier == "1") {
			if ($lacicloud_api->upgradeToTier($tier, $orderID, $id, $dbc) !== true) {
				return 51;
			}

			return true;
		}


		$all_keys = array(  
        "bitcoin"  => array("public_key" => $lacicloud_api->grabSecret("gourl_public_key_bitcoin"),  "private_key" => $lacicloud_api->grabSecret("gourl_private_key_bitcoin")),
        "litecoin"  => array("public_key" => $lacicloud_api->grabSecret("gourl_public_key_litecoin"),  "private_key" => $lacicloud_api->grabSecret("gourl_private_key_litecoin")),
        "dogecoin"  => array("public_key" => $lacicloud_api->grabSecret("gourl_public_key_dogecoin"),  "private_key" => $lacicloud_api->grabSecret("gourl_private_key_dogecoin")),
        "potcoin"  => array("public_key" => $lacicloud_api->grabSecret("gourl_public_key_potcoin"),  "private_key" => $lacicloud_api->grabSecret("gourl_private_key_potcoin")),  
        "dash"  => array("public_key" => $lacicloud_api->grabSecret("gourl_public_key_dashcoin"),  "private_key" => $lacicloud_api->grabSecret("gourl_private_key_dashcoin")),
        "speedcoin"  => array("public_key" => $lacicloud_api->grabSecret("gourl_public_key_speedcoin"),  "private_key" => $lacicloud_api->grabSecret("gourl_private_key_speedcoin")),
        // etc.
    	);   

    	// Optional - Coin selection list (html code)
    	$coins_list = display_currency_box($this->available_cryptocurrency_payments, $this->def_payment, $this->def_language, 70, "margin: 5px 0 0 20px", "/resources/gourl"); 

    	if (isset($_GET["gourlcryptocoin"])) {
       		$coinName = $_GET["gourlcryptocoin"];
    	} else {
    		$coinName = $this->def_payment;
    	}
    	
    	$public_key  = $all_keys[$coinName]["public_key"];
    	$private_key = $all_keys[$coinName]["private_key"];

    	    /** PAYMENT BOX **/
   		$options = array(
            "public_key"  => $public_key,   // your public key from gourl.io
            "private_key" => $private_key,  // your private key from gourl.io
            "webdev_key"  => "",            // optional, gourl affiliate key
            "orderID"     => $orderID,      // order id
            "userID"      => $id,       // unique identifier for every user
            "userFormat"  => "SESSION",   // save userID in COOKIE, IPADDRESS or SESSION
            "amountUSD"   => $amountUSD,    // we use price in USD
            "period"      => $this->period,       // payment valid period
            "language"    => "EN"  // text on EN - english, FR - french, etc
    	);

    	$box = new Cryptobox($options);

		if ($box->is_paid()) {

			if ($lacicloud_api->upgradeToTier($tier, $orderID, $id, $dbc) !== true) {
					return 51;
			}


			if (!$box->is_processed()) {

				$this->sendGoUrlPaymentEmail($orderID, $box->payment_id(), $amountUSD, $id, $tier, $lacicloud_ftp_api->getUserValues($id, $dbc)["email"]);
				// Set Payment Status to Processed

				$box->set_status_processed();
				return true;
			} else {
				return true;
			} 
		} else {
			echo "<br><br>";
			echo $coins_list;
			echo($box->display_cryptobox(true, 550, 250, "padding:3px 6px;margin:10px;border:10px solid #f7f5f2;"));
		}

	 return "inprogress"; //payment in progress

	}


	public function sendGoUrlPaymentEmail($orderID, $paymentID, $amountUSD, $id, $tier, $email) {
		$lacicloud_api = new LaciCloud();
		$lacicloud_errors_api = new Errors();
		
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
			$cid = $message->embed(Swift_Image::fromPath($lacicloud_api->document_root.'/www/developers/localweb/resources/logo.png'));
			$message 
				->setSubject("Payment received!")
				->setFrom(array("bot@lacicloud.net" => "LaciCloud"))
				->setTo(array("$email"))
				->setBcc(array("laci@lacicloud.net" => "Laci"))
				->setCharset('utf-8') 
				->setBody("<html><body><img src='".$cid."' alt='LaciCloud Logo'><br>Hi ".$email."!<br>A payment has been successfully received and your account upgraded to tier ".$tier." .<br>User ID: ".$id."<br>Value: ".$amountUSD."<br>Order ID: ".$orderID."<br>Payment ID: ".$paymentID."<br><br>Have a great day, <br>The LaciCloud Team</body></html>",'text/html');
			$result = $mailer->send($message, $errors);
	    } catch(\Swift_TransportException $e){
	        $response = $e->getMessage();
	        $result = false;
	    } catch (Exception $e) {
	    	$response = $e->getMessage();
	    	$result = false; 
	    }


		if (!$result) {
			@$lacicloud_errors_api -> msgLogger("SEVERE", "Could not send payment email for orderID ".$orderID."... Error:\n".$logger->dump()." Exception error:\n".$response, 37);
		}

		return true;

	}

	public function sendGoUrlConfirmationEmail($email, $orderID, $paymentID) {
		$lacicloud_api = new LaciCloud();
		$lacicloud_errors_api = new Errors();
		
		try {
			//sends email to user & me about payment
			$title = "LaciCloud_Payment_Confirmation_Email";
		    $transport = Swift_SmtpTransport::newInstance(gethostbyname("mail.gandi.net"), 465, "ssl") 
				->setUsername($lacicloud_api -> grabSecret("email"))
				->setPassword($lacicloud_api -> grabSecret("email_password"))
				->setSourceIp("0.0.0.0");
			$mailer = Swift_Mailer::newInstance($transport);
			$logger = new \Swift_Plugins_Loggers_ArrayLogger();
			$mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));
			$message = Swift_Message::newInstance("$title");
			$cid = $message->embed(Swift_Image::fromPath($lacicloud_api->document_root.'/www/developers/localweb/resources/logo.png'));
			$message 
				->setSubject("Payment confirmed!")
				->setFrom(array("bot@lacicloud.net" => "LaciCloud"))
				->setTo(array("$email"))
				->setBcc(array("laci@lacicloud.net" => "Laci"))
				->setCharset('utf-8') 
				->setBody("<html><body><img src='".$cid."' alt='LaciCloud Logo'><br>Hi ".$email."!<br>Payment for ID ".$paymentID.", orderID ".$orderID." has been successfully confirmed.<br> Have a great day, <br>The LaciCloud Team</body></html>",'text/html');
			$result = $mailer->send($message, $errors);
	    } catch(\Swift_TransportException $e){
	        $response = $e->getMessage();
	        $result = false;
	    } catch (Exception $e) {
	    	$response = $e->getMessage();
	    	$result = false; 
	    }


		if (!$result) {
			$lacicloud_errors_api -> msgLogger("SEVERE", "Could not send payment confirmation email for orderID: ".$orderID." (Callback IPN)... Error:\n".$logger->dump()." Exception error:\n".$response, 37);
		}

		return true;

	}

	//sort of 're-verifies' that the tier has been changed (changes it again)
	public function callbackIPN($paymentID, $payment_details, $box_status, $dbc, $dbc_ftp) {
		$lacicloud_api = new LaciCloud();
		$lacicloud_errors_api = new Errors();
		$lacicloud_ftp_api = new FTPActions();

		$orderID = $payment_details["order"];

		$tier = (int)$orderID[5];
		$id = (int)$payment_details["user"];
		$email = $lacicloud_ftp_api -> getUserValues($id, $dbc)["email"];

		$amountUSD = (int)$payment_details["amountusd"];


		if ((int)$payment_details["confirmed"] != 1) {
			return 52;
		}

		if ($tier == 1 and $amountUSD > 10 and $amountUSD > 13 or $tier == 2 and $amountUSD > 20 and $amountUSD < 25) {
			$this->sendGoUrlConfirmationEmail($email, $orderID, $paymentID);


			if ($lacicloud_api -> canChangeToTier($tier, $id, $dbc, $dbc_ftp) !== true) {
				$lacicloud_errors_api -> msgLogger("LOW", "User is not allowed to upgrade/downgrade to tier: ".$tier." orderID: ".$orderID." (Callback IPN, probably already on tier)", 49);
				return 49;
			}

			if ($lacicloud_api->upgradeToTier($tier, $payment_details["order"], $id, $dbc) !== true) {
				return 51;
			}

		} else {
			$lacicloud_errors_api -> msgLogger("LOW", "Incorrectly paid sum when upgrading to tier: ".$tier." orderID: ".$orderID." (Callback IPN)", 52);
			return 52;
		}

		return true; 		
	}


}

//anything related to error handling, or displaying errors
class Errors extends LaciCloud {

	//most of the error codes and their messages
	private $messages = array(
		true => "Everything okay!",
		1 => "Database connection error!",
		2 => "FTP Database connection error!",
		3 => "Could not start session!",
		4 => "Captcha code incorrect!",
		5 => "Email or Password is incorrect!", //also validation error 
		6 => "Login successfull, but account not confirmed! Please check your email!",
		7 => "Failed incrementing login counter!",
		8 => "Failed incrementing index page counter!",
		9 => "Couldn't confirm account with key!",
		10 => "Couldn't create account with specified information!",
		11 => "Couldn't send confirmation email during account creation!",
		12 => "Email already exists!",
		13 => "Couldn't update login details!",
		14 => "Old password can't be the same as the new one!", //this is seperate error as this one can't be validate using JS
		15 => "Couldn't find email in database! Are you sure you typed your email correctly?",
		16 => "Couldn't verify the authenticity of action!",
		//whoops forgot success messages
		17 => "Account successfully registered! Please check your inbox for confirmation!",
		18 => "Successfully sent reset email... Please check your inbox!",
		19 => "Account successfully reset!",
		//FTPActions class
		20 => "Unexpected error occured!", //nice to have
		21 => "Couldn't create FTP user with specified data, maybe FTP user already exists?",
		22 => "Couldn't remove FTP user! Are you sure you typed his name correctly?",
		24 => "Couldn't create invoice!",
		25 => "Error while getting values!", //for FTP users list, FTP users values, and user values
		26 => "Error while regenerating API key!",
		28 => "Please log-in to continue!",
		31 => "Successfully created FTP user!",
		32 => "Successfully removed FTP user!",
		35 => "Successfully regenerated API key!",
		37 => "Couldn't send payment email!",
		38 => "Couldn't update password!",
		39 => "Password successfully updated!",
		40 => "Error while sending step 2 reset email!",
		//API
		41 => "Error! No API key found!",
		42 => "Error! API key wrong!", //the answer to life the universe and everything
		43 => "Error! Not enough parameters!",
		44 => "Successfully confirmed account!",
		//qftp
		45 => "Couldn't create qFTP user!",
		46 => "Successfully created qFTP user!", //in js with username/password
		48 => "Successfully brought tier!",
		49 => "Error! You can't change to this tier because you have more FTP space used than this tier allows you to! Please delete a few FTP users before continuing...",
		50 => "Please create an account before continuing!",
		51 => "Internal server error while changing to this tier! We are working on it...",
		52 => "IPN Error!"
	);

	public function getErrorMSGFromID($id) {
		return $this->messages[$id];
	}

	public function msgLogger($severity, $msg, $id) {
		 $lacicloud_api = new LaciCloud();

		 $bt = debug_backtrace();
  		 $caller = array_shift($bt);

  		 if ($severity == "CRIT" or $severity == "SEVERE") {
  		 	$POST = print_r($_POST, true);
  			$GET = print_r($_GET, true);
  		 	$SERVER = print_r($_SERVER, true);

  		 }
  		 

  		 $message = "\n".date('l jS \of F Y h:i:s A').':'." Severity: ".$severity." Message: ".$msg." Error ID: ".$id." File: ".$caller['file']." Line: ".$caller['line']." User ID: ".$_SESSION["id"]." IP: ".$_SERVER["REMOTE_ADDR"]." UA: ".$_SERVER['HTTP_USER_AGENT']." Referer: ".$_SERVER["HTTP_REFERER"]." POST: ".$POST." GET: ".$GET." SERVER: ".$SERVER."\n\n";

		 error_log($message, 3, $lacicloud_api->document_root."/logs/website_custom.txt");
		 
	}


}

class API extends LaciCloud {

	public function verifyAPIKey($api_key, $dbc) {

		if (!isset($api_key)) {
			return 41;
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
			return 42;
		}

		return array("id" => $id, "api_key" => $api_key);

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
		$password='';
		srand ((double)microtime()*1000000);
		$max = $length/2;
		for($i=1; $i<=$max; $i++){
			$password.=$conso[rand(0,19)];
			$password.=$vocal[rand(0,4)];
		}
		$password.=rand(10,99);
		$newpass = $password;
		return $newpass;

	}

	public function addQFTPUser($username, $password, $dbc_ftp) {
		$lacicloud_api = new LaciCloud();
		$lacicloud_errors_api = new Errors();

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
			$lacicloud_errors_api -> msgLogger("SEVERE", "SQL error while adding QFTP user... Error: ".mysqli_error($dbc_ftp), 21);
			return 21;
		}


		return true;

	}

}

//anything that does not belong to the core of LaciCloud
class Utils extends LaciCloud {
	public function getEmailProvider($email) {

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
			$message = "";
			$link = "";
		}

		return array($message, $link);
					
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