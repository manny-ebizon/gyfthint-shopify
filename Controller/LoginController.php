<?php 
include('Model/Model.php');
include('Model/UserModel.php');
require("vendor/autoload.php");
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
	
	function generate_string($strength = 6) {		
		$permitted_chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';		
	    $input_length = strlen($permitted_chars);
	    $random_string = '';
	    for($i = 0; $i < $strength; $i++) {
	        $random_character = $permitted_chars[mt_rand(0, $input_length - 1)];
	        $random_string .= $random_character;
	    }
	 
	    return $random_string;
	}

	function index() {
		if (isset($_GET['access_token'])) {
			$UM = new UserModel;

			$result = $UM->loginAccessToken($_GET['access_token']);						
			if($result!=NULL)
			{		
				unset($result['password']);
				unset($result['created_at']);
				$payload = $result;
				$payload['ip_address'] = $_SERVER['REMOTE_ADDR'];

				$jwt = JWT::encode($payload, KEY, 'HS256');				
				$user_id = $result['id'];

				if ($jwt!="") {					
					unset($result['id']);
					$data['token'] = $jwt;
					$data['userdata'] = json_encode($result);		
					$_SESSION['token'] = $jwt;
					$_SESSION['role'] = $payload['role'];
					$_SESSION['login_id'] = $payload['id'];
				}
			}
		}
		if (isset($_GET['redirect_url'])) {
			$data['path'] = "signup/customer-login";
		} else {
			$data['path'] = "signup/login";	
		}
		$data['title'] = "Sign In";
		
		$data['menu-link'] = "login";
		return $data;	
	}	

	function uuidv4() {
	    $data = random_bytes(16);
	    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
	    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

	    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}

	function google() {
		require_once __DIR__.'/../google-api-php-client/vendor/autoload.php';

		$CLIENT_ID = GOOGLE_CLIENT_ID;
		if (isset($_POST['credential'])) {
			$id_token = $_POST['credential'];
			$client = new Google_Client(['client_id' => $CLIENT_ID]);  // Specify the CLIENT_ID of the app that accesses the backend
			$payload = $client->verifyIdToken($id_token);
			if ($payload) {
			  $userid = $payload['sub'];			  
			  $UM = new UserModel;			  
			  $result = $UM->getUser($payload['email']);
			  
			  // user doesn't exist
			  if ($result==NULL) {
			  	// Register User
			  	$data = [];
			  	$data['uuid'] = uuidv4();
			  	$data['email'] = $payload['email'];
			  	$data['password'] = $payload['sub'];
			  	$data['first_name'] = $payload['given_name'];
			  	$data['last_name'] = $payload['family_name'];
			  	$data['role'] = "client";
			  	$UM->addGlobal('users',$data);
			  	$result = $UM->getUser($payload['email']);
			  	$data = $result;			  	
				
				unset($data['id']);
				unset($data['password']);
				unset($data['last_login']);
				unset($data['is_deleted']);
				unset($data['created_on']);				
				$payload = $data;
				$payload['ip_address'] = $_SERVER['REMOTE_ADDR'];
				$token = JWT::encode($payload, KEY, 'HS256');
				$_SESSION['userdata'] = $data;
				$_SESSION['token'] = $token;
				$_SESSION['role'] = $result['role'];

				header("Location: /login/?token=".$token);
				exit;
			  } else {
			  	// Login w/ session
			  	if($result['role']=="client") {										
			  		unset($result['id']);
					unset($result['password']);
					unset($result['last_login']);
					unset($result['is_deleted']);
					unset($result['created_on']);	
					$payload = $result;
					$payload['ip_address'] = $_SERVER['REMOTE_ADDR'];
					$token = JWT::encode($payload, KEY, 'HS256');					
					$_SESSION['userdata'] = $result;
					$_SESSION['token'] = $token;
					$_SESSION['role'] = $result['role'];					
					header("Location: /login/?token=".$token);
					exit;
				}
			  }			 
			} else {
			  header("Location: /login/?error=Invalid Token");
			}
		} else {
			header("Location: /");
		}		
		exit;
	}

	function token() {
		if(isset($_POST['token']))
		{		
			$jwt = $_POST['token'];
		    $payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
			if($payload)
			{						
				$return['status'] = true;
				$return['token'] = $jwt;
				$_SESSION['token'] = $jwt;
				$_SESSION['userdata'] = (array)$payload;
				$_SESSION['role'] = $payload->role;
			}
			else
			{
				$return['status'] = false;
				$return['message'] = __("Incorrect token.",true);
			}
			echo json_encode($return);
			exit;
		}
	}

	function sign_up() {
		session_destroy();		
		$data['path'] = "signup/register-customer";
		$data['title'] = "Customer Sign Up | Gyfthint";
		return $data;
	}

	function merchant_onboarding() {
		session_destroy();		
		$data['path'] = "signup/merchant-onboarding";
		$data['title'] = "Merchant Onboarding | Gyfthint";
		return $data;
	}

	function forgot_password() {
		session_destroy();
		$data['path'] = "signup/forgot";
		$data['title'] = "Forgot Password";
		return $data;
	}

	function reset_password() {		
		if (isset($_GET['t'])) {			
			$UM = new UserModel();
			$result = $UM->getGlobalbyId("reset_password_key",$_GET['t'],false);			
			
			if (count($result)==0 || $result==NULL) {
				header("Location: /forgot-password/");
				exit;
			} else {
				if (date("Y-m-d H:i:s") <= date("Y-m-d H:i:s",strtotime($result['expiry']))) {
					$data['path'] = "signup/reset";
					$data['title'] = "Reset Password";
					return $data;
				} else {
					$data['link_expired'] = true;
					$data['path'] = "signup/reset";
					$data['title'] = "Reset Password";
					return $data;
				}
				
			};
		} else {
			header("Location: /forgot_password/");
			exit;
		}		
		$data['path'] = "signup/reset";
		return $data;
	}

	function send_reset() {
		if(isset($_POST['reset'])) {
			$UM = new UserModel;		
			$result = passwordResetEmail($_POST['email']);
			if ($result) {
				curl_process_email_queue();
				$return['status'] = true;
			} else {
				$return['status'] = false;
			}
			echo json_encode($return);
		}
		exit;
	}

	function update_password() {
		if(isset($_POST['data']))
		{
			parse_str($_POST['data'],$postdata);
			$tokenvalue = JWT::decode($postdata['token'], new Key(KEY, 'HS256'));
			if ($tokenvalue) {
				$id = $postdata['uuid'];				
				$newdata = [];
				$newdata['password'] = md5($postdata['newpass']);
				$UM = new UserModel;
				$result = $UM->updateGlobal('users',$id,$newdata);				
				if($result)
				{					
					$return['status'] = true;
				}
				else
				{
					$return['message'] = __("Unable to updated password. Please try again or contact support.",true);
					$return['status'] = false;
				}
			} else {
				$return['status'] = false;	
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);			
			}
			
			echo json_encode($return);
			exit;
		}	
		exit;
	}

	function generatePassword($length = 12) {
		$str = "";
		$characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
		$max = count($characters) - 1;
		for ($i = 0; $i < $length; $i++) {
			$rand = mt_rand(0, $max);
			$str .= $characters[$rand];
		}
		return $str;
	}

	function logout() {						
		session_destroy();	
		header("Location: /login/");
		exit;
	}


?>