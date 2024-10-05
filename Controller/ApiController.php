<?php 
include('Model/Model.php');
include('Model/UserModel.php');
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;
use chillerlan\QRCode\QRCode;	
use Razorpay\Api\Api;
	
	function neiltest(){
		echo uuidv4();
		exit;
	}

	function auth_login() {
		if (isset($_POST['phone']) || isset($_GET['phone']) ) {
			$UM = new UserModel;
			if (isset($_GET['phone'])) {
				$phone = $_GET['phone'];
			}
			if (isset($_POST['phone'])) {
				$phone = $_POST['phone'];
			}
			$login = $UM->loginPhone($phone);
			var_dump($login);exit;
			printr($login);
			
			$return['status'] = true;
			$return['licenses'] = $returnData;
		} else {
			$return['status'] = false;
			$return['message'] = "Invalid token";
		}
		echo json_encode($return);
		exit;
	}

	function update_suggested_hints() {
		if (isset($_POST['data']) ) {
			$UM = new UserModel;			
			parse_str($_POST['data'],$postdata);
			$UM = new UserModel;
			foreach ($postdata['suggested_hints'] as $key => $value) {
				$res = $UM->updateSuggestedHints($key,$value);
			}
			if ($res===true) {
				$return['status'] = true;
				$return['message'] = "Successfully updated suggested hints.";
			} else {
				$return['status'] = false;
				$return['message'] = "Invalid OTP/Phone Number.";
			}
			
		} else {
			$return['status'] = false;
			$return['message'] = "Invalid token";
		}
		echo json_encode($return);
		exit;
	}

	function login_customer() {
		if (isset($_POST['phone']) ) {
			$UM = new UserModel;			
			$login = $UM->loginPhone($_POST['phone']);			
			if ($login!=NULL) {
				$return['status'] = true;
				$return['id'] = $login['uuid'];
				if (strpos($_POST['redirect_url'], '?') !== false) {
				    $return['redirect_url'] = $_POST['redirect_url']."&token=".$login['uuid'];
				} else {
				    $return['redirect_url'] = $_POST['redirect_url']."?token=".$login['uuid'];
				}

				
			} else {
				$return['status'] = false;
				$return['message'] = "Invalid OTP/Phone Number.";
			}
			
		} else {
			$return['status'] = false;
			$return['message'] = "Invalid token";
		}
		echo json_encode($return);
		exit;
	}


	function licenses() {
		if (true) {
			$UM = new UserModel;
			$licenses = $UM->fetchLicenses();
			$returnData = [];
			foreach ($licenses as $key => $value) {
				$col = [];
				$col['label'] = $value['label'];
				$col['description'] = $value['description'];
				$col['price'] = $value['price'];
				$col['is_enabled'] = $value['is_enabled'];
				$returnData[] = $col;
			}
			$return['status'] = true;
			$return['licenses'] = $returnData;
		} else {
			$return['status'] = false;
			$return['message'] = "Invalid token";
		}
		echo json_encode($return);
		exit;
	}

	function register_customer() {
		if (isset($_POST['data'])) {
			parse_str($_POST['data'],$postdata);
			$UM = new UserModel;


			$url = $postdata['redirect_url'];
			$parsedUrl = parse_url($url);
			$domain = $parsedUrl['host'];
			
			$store = $UM->fetchStoreByDomain($domain);

			$newData = [];						
			$newData['uuid'] = uuidv4();
			$newData['first_name'] = ucwords($postdata['first_name']);
			$newData['last_name'] = ucwords($postdata['last_name']);
			$newData['email'] = $postdata['email'];
			$newData['phone'] = $postdata['phone'];
			$newData['source'] = "shopify button";
			$newData['store_id'] = $store['id'];
			$res = $UM->addGlobal("customers",$newData);
			if ($res===true) {
				$return['status'] = true;
				if (strpos($postdata['redirect_url'], '?') !== false) {
				    $return['redirect_url'] = $postdata['redirect_url']."&token=".$newData['uuid'];
				} else {
				    $return['redirect_url'] = $postdata['redirect_url']."?token=".$newData['uuid'];
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = "Invalid data. Please try again";
			}

		} else {
			$return['status'] = false;
			$return['message'] = "Invalid data. Please try again";
		}
		echo json_encode($return);
		exit;
	}

	function fetchSheetData($sheet_url) {
		$parts = explode('/d/', $sheet_url);
		$subparts = explode('/', $parts[1]);
		$sheetId = $subparts[0];				
		
		$apiKey = GOOGLE_SHEET_API_KEY;
		$range = 'A1:Z';

		//fetch SHEET RANGES
		$url = "https://sheets.googleapis.com/v4/spreadsheets/$sheetId/?key=$apiKey";

		// Initialize cURL session
		$ch = curl_init($url);

		// Set cURL options
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    'Accept: application/json'
		));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_ENCODING, '');

		// Execute cURL session and get the response
		$response = curl_exec($ch);
		$sheetData = json_decode($response,true);
		
		$sheetRanges = [];

		if (isset($sheetData['error'])) {
			$return['status'] = false;
			if ($sheetData['error']['message']=="The caller does not have permission") {
				$return['message'] = "Please make sure the sheet permission is allowed to be accessed.";
			} else {
				$return['message'] = $sheetData['error']['message'];	
			}
			echo json_encode($return);
			exit;
		}
		foreach ($sheetData['sheets'] as $sheetDataVal) {			
			$sheetRanges[] = $sheetDataVal['properties']['title'];			
		}

		// Check for cURL errors
		if(curl_errno($ch)) {
		    echo 'cURL error: ' . curl_error($ch);
		    exit;
		}

		if (count($sheetRanges)>0) {
			$transformedData = [];
			foreach ($sheetRanges as $firstKey => $sheetRange) {				
				$range = str_replace(" ","%20",$sheetRange)."!A1:Z";
				$url = "https://sheets.googleapis.com/v4/spreadsheets/$sheetId/values/$range?key=$apiKey";

				// Initialize cURL session
				$ch = curl_init($url);

				// Set cURL options
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				    'Accept: application/json'
				));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_ENCODING, '');

				// Execute cURL session and get the response
				$response = curl_exec($ch);
				$sheetValue = json_decode($response,true);

				// Check for cURL errors
				if(curl_errno($ch)) {
				    echo 'cURL error: ' . curl_error($ch);
				}						
				foreach ($sheetValue['values'] as $key => $value) {
					if ($key!=0) {
						$rowIndex = '';
						$col = [];
						foreach ($value as $k => $val) {
							if ($k==0) {
								$col['dr'] = $val;
							} else if($k==1) {
								$col['traffic'] = $val;
							} else if($k==2) {
								$col['prospect_url'] = $val;
							} else if($k==3) {
								$col['anchor_text'] = $val;
							} else if($k==4) {
								$col['target_url'] = $val;
							} else if($k==5) {
								$col['published_link'] = $val;
							}
						}
						$transformedData[$sheetRange][] = $col;						
					}
				}				
			}			
		}
		return $transformedData;
	}

	function sync_sheet_data() {
		if (isset($_POST['token'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel;

				$order = $UM->fetchOrderById($_POST['order_id']);

				$transformedData = fetchSheetData($order['doc_link']);
				$newData = [];
				$newData['order_details'] = json_encode($transformedData);				

				$res = $UM->updateGlobal("orders",$_POST['order_id'],$newData);
				if ($res===true) {
					$return['status'] = true;
				} else {
					$return['status'] = false;
					$return['message'] = "Unable to fetch data. Please contact support or try again later.";
				}
				
			} catch (Exception $e) {
				$return['status'] = false;
				$return['message'] = $e->getMessage();
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("Missing Token",true);
		}

		echo json_encode($return);
		exit;			
	}

	function stripe() {
		if (getUriSegment(3)=="checkout_link" && isset($_POST['token'])) {
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			$UM = new UserModel;				
						
			if (isset($_POST['data']) && !isset($_POST['order_id'])) {
				parse_str($_POST['data'],$postdata);

				$newData = [];
				$newData['uuid'] = uuidv4();
				$last_order = $UM->fetchLastOrder($tokenvalue->uuid);
				if ($last_order===false) {
					$last_order['order_id'] = 0;
				}
				$newData['order_id'] = (int)$last_order['order_id']+1;	
				$newData['user_id'] = $tokenvalue->uuid;
				$newData['service'] = $postdata['service'];
				if ($postdata['service']=="blogger outreach") {
					$newData['line_items'] = json_encode($postdata['package']);
					$newData['amount'] = calculate_subtotal($postdata['service'],$postdata['package']);
				} else {				
					$newData['amount'] = calculate_subtotal($postdata['service'],$postdata['package_value']);
				}
				
				
				$newData['doc_link'] = $postdata['doc_link'];
				if ($postdata['doc_link']!="") {
					$transformedData = fetchSheetData($postdata['doc_link']);
					$newData['order_details'] = json_encode($transformedData);
				}
				$newData['comments'] = $postdata['comments'];

				if (isset($postdata['package_value'])) {
					$newData['package'] = $postdata['package_value'];
				}			

				if (isset($postdata['pre_approved_websites'])) {
					$newData['pre_approved_websites'] = 1;
				}
				if (isset($postdata['anchor_landing_pages'])) {
					$newData['anchor_landing_pages'] = 1;
				}
				if (isset($postdata['website'])) {
					$newData['website'] = $postdata['website'];
				}
				if (isset($postdata['no_pages'])) {
					$newData['no_pages'] = $postdata['no_pages'];
				}

				$newData['payment_method'] = "pay_later";		
				if ($postdata['coupon_code']!="") {
					$newData['coupon'] = $postdata['coupon_code'];
				}
				//ADD ORDER INITIALLY					
				$result = $UM->addGlobal("orders",$newData);
				$order_id = $newData['uuid'];
			} else if (isset($_POST['order_id']) && !isset($_POST['data'])) {
				$order_id = $_POST['order_id'];
			}
									
			$stripe = new \Stripe\StripeClient(STRIPE_CLIENT_SECRET);
			$amount = $_POST['amount']*100;
			$name = str_replace("seo","SEO",$_POST['service']);
			$name = str_replace("pdp","PDP",$name);
			$name = ucwords($name);
			//CREATE PRICE			
			$response = $stripe->prices->create([
			  'currency' => 'usd',
			  'unit_amount' => $amount,
			  'product_data' => ['name' => $name]
			]);

			\Stripe\Stripe::setApiKey(STRIPE_CLIENT_SECRET);
			header('Content-Type: application/json');

			try {
				$checkout_session = \Stripe\Checkout\Session::create([
				  'line_items' => [[
				    # Provide the exact Price ID (e.g. pr_1234) of the product you want to sell
				    'price' => $response->id,
				    'quantity' => 1,
				  ]],
				  'mode' => 'payment',
				  'success_url' => SITE_URL . '/api/stripe/success?order_id='.$order_id,
				  'cancel_url' => $_POST['cancelURL'],
				]);
				$return['status'] = true;
				$return['checkout_link'] = $checkout_session->url;
			} catch (Exception $e) {
				$return['status'] = false;
				$return['message'] = $e->getMessage();
			}	
			echo json_encode($return);
			exit;
		} else if (getUriSegment(3)=="success") {
			$UM = new UserModel;
			$newData = [];
			$status = $newData['status'] = "Paid";
			$newData['payment_id'] = uuidv4();
			$newData['paid_with'] = "stripe";
			$newData['payment_date'] = date("Y-m-d");
			$order = $UM->fetchOrderById($_GET['order_id']);						
			$result = $UM->updateGlobal("orders",$_GET['order_id'],$newData);
			if ($result===true) {				
				$user = $UM->getGlobalbyId("users",$order['user_id']);
				send_order_status_update_email($user,$order,$status);
				send_order_status_update_email_admin($user,$order,$status);				
			}
			header("Location: ".SITE_URL."/order/".$_GET['order_id']);
			exit;

		} else {
			$return['status'] = false;
			$return['message'] = "Missing token";
		}	
		echo json_encode($return);
		exit;
	}

	function razorpay_checkout() {
		if (isset($_POST['token'])) {
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			$UM = new UserModel;				
						
			if (isset($_POST['data']) && !isset($_POST['order_id'])) {
				parse_str($_POST['data'],$postdata);

				$newData = [];
				$newData['uuid'] = uuidv4();
				$last_order = $UM->fetchLastOrder($tokenvalue->uuid);
				if ($last_order===false) {
					$last_order['order_id'] = 0;
				}
				$newData['order_id'] = (int)$last_order['order_id']+1;	
				$newData['user_id'] = $tokenvalue->uuid;
				$newData['service'] = $postdata['service'];
				if ($postdata['service']=="blogger outreach") {
					$newData['line_items'] = json_encode($postdata['package']);
					$newData['amount'] = calculate_subtotal($postdata['service'],$postdata['package']);
				} else {				
					$newData['amount'] = calculate_subtotal($postdata['service'],$postdata['package_value']);
				}
				
				
				$newData['doc_link'] = $postdata['doc_link'];
				if ($postdata['doc_link']!="") {
					$transformedData = fetchSheetData($postdata['doc_link']);
					$newData['order_details'] = json_encode($transformedData);
				}
				$newData['comments'] = $postdata['comments'];

				if (isset($postdata['package_value'])) {
					$newData['package'] = $postdata['package_value'];
				}			

				if (isset($postdata['pre_approved_websites'])) {
					$newData['pre_approved_websites'] = 1;
				}
				if (isset($postdata['anchor_landing_pages'])) {
					$newData['anchor_landing_pages'] = 1;
				}
				if (isset($postdata['website'])) {
					$newData['website'] = $postdata['website'];
				}
				if (isset($postdata['no_pages'])) {
					$newData['no_pages'] = $postdata['no_pages'];
				}

				$newData['payment_method'] = "pay_later";		
				if ($postdata['coupon_code']!="") {
					$newData['coupon'] = $postdata['coupon_code'];
				}
				//ADD ORDER INITIALLY					
				$result = $UM->addGlobal("orders",$newData);
				$order_id = $newData['uuid'];
			} else if (isset($_POST['order_id']) && !isset($_POST['data'])) {
				$order_id = $_POST['order_id'];
			}
									
			$stripe = new \Stripe\StripeClient(STRIPE_CLIENT_SECRET);
			$amount = $_POST['amount']*100;
			$name = str_replace("seo","SEO",$_POST['service']);
			$name = str_replace("pdp","PDP",$name);
			$name = ucwords($name);
			//CREATE PRICE			
			$response = $stripe->prices->create([
			  'currency' => 'usd',
			  'unit_amount' => $amount,
			  'product_data' => ['name' => $name]
			]);

			\Stripe\Stripe::setApiKey(STRIPE_CLIENT_SECRET);
			header('Content-Type: application/json');

			try {
				$checkout_session = \Stripe\Checkout\Session::create([
				  'line_items' => [[
				    # Provide the exact Price ID (e.g. pr_1234) of the product you want to sell
				    'price' => $response->id,
				    'quantity' => 1,
				  ]],
				  'mode' => 'payment',
				  'success_url' => SITE_URL . '/api/stripe/success?order_id='.$order_id,
				  'cancel_url' => $_POST['cancelURL'],
				]);
				$return['status'] = true;
				$return['checkout_link'] = $checkout_session->url;
			} catch (Exception $e) {
				$return['status'] = false;
				$return['message'] = $e->getMessage();
			}	
			echo json_encode($return);
			exit;
		} else if (getUriSegment(3)=="success") {
			$UM = new UserModel;
			$newData = [];
			$status = $newData['status'] = "Paid";
			$newData['payment_id'] = uuidv4();
			$newData['paid_with'] = "stripe";
			$newData['payment_date'] = date("Y-m-d");
			$order = $UM->fetchOrderById($_GET['order_id']);						
			$result = $UM->updateGlobal("orders",$_GET['order_id'],$newData);
			if ($result===true) {				
				$user = $UM->getGlobalbyId("users",$order['user_id']);
				send_order_status_update_email($user,$order,$status);
				send_order_status_update_email_admin($user,$order,$status);				
			}
			header("Location: ".SITE_URL."/order/".$_GET['order_id']);
			exit;

		} else {
			$return['status'] = false;
			$return['message'] = "Missing token";
		}	
		echo json_encode($return);
		exit;
	}

	function uuidv4() {
	    $data = random_bytes(16);
	    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
	    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

	    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}

	function printr($data){
		print("<pre>".print_r($data,true)."</pre>");

		exit;
		return true;
	}
		
	function confirm() {
		if(isset($_POST['data']))
		{		
			parse_str($_POST['data'],$postdata);			
			$UM = new UserModel;	

			$result = $UM->loginUser($postdata);			
			
			if($result!=NULL)
			{		
				unset($result['password']);
				unset($result['created_at']);
				$payload = $result;
				$payload['ip_address'] = $_SERVER['REMOTE_ADDR'];

				$jwt = JWT::encode($payload, KEY, 'HS256');				
				$user_id = $result['id'];
				if ($result['role']!="admin") {
					$stores = $UM->fetchStoresByUserId($user_id);
					$result['store_id'] = $stores[0]['id'];
					$license = $UM->fetchStoresLicenseByStoreId($stores[0]['id']);					
					if ($license!==false) {
						$result['license'] = $license['label'];
					} else {
						$result['license'] = "FREE";
					}
					
				}
				
				if ($jwt!="") {
					$return['status'] = true;
					$return['token'] = $jwt;
					unset($result['id']);
					$return['userdata'] = $result;
					$_SESSION['token'] = $jwt;
					$_SESSION['role'] = $payload['role'];
					$_SESSION['login_id'] = $payload['id'];
					if ($result['role']!="admin") {
						$_SESSION['store_id'] = $stores[0]['id'];
						$_SESSION['license'] = $result['license'];
					}
				} else {
					$return['status'] = false;
					$return['message'] = __("Incorrect username/password. Please try again.",true);
				}
			}
			else
			{
				$return['status'] = false;
				$return['message'] = __("Incorrect username/password. Please try again.",true);
			}
			echo json_encode($return);
			exit;
		}
	}

	function forgot_password() {
		if(isset($_POST['email']))
		{					
			$UM = new UserModel;			
			$email = $_POST['email'];
			$user = $UM->getUserbyEmail($email);			
			if($user!=NULL)
			{						
				$newData = [];
				$newData['uuid'] = $key = uuidv4().'-'.uuidv4();
				$newData['user_id'] = $user['uuid'];
				$newData['expiry'] = date("Y-m-d H:i:s",strtotime("+1 day"));
				$res = $UM->addGlobal("reset_password_key",$newData);
				if ($res===true) {
					//SEND EMAIL LINK to reset password
					send_reset_email($newData['uuid']);
					$return['status'] = true;
					$return['message'] = "Reset password link is sent on your email.";
				} else {
					$return['status'] = false;
					$return['message'] = "Unable to reset password";
				}
			}
			else
			{
				$return['status'] = false;
				$return['message'] = "Please provide valid email";
			}			
		} else {
				$return['status'] = false;
				$return['message'] = "Please provide valid email";
		}
		echo json_encode($return);
		exit;
	}

	function send_reset_email($reset_id=null) {
		if ($reset_id!=null) {
			$UM = new UserModel();
			$resetDetails = $UM->getGlobalbyId("reset_password_key",$reset_id,false);			
			$user = $UM->getGlobalbyId("users",$resetDetails['user_id']);
			
			// CUSTOMIZE SUBJECT & BODY
			$body = '<table class="body-sub" role="presentation"><tr><td><p class="f-fallback sub">Hi '.ucwords($user['first_name']." ".$user['last_name']).',';

			$subject = "Forgot Password Request";
        	$body .= '<br><br>We&apos;ve received a password request from your email. <br><br>Please click the link below: <br><a href="https://app.creativebraindesign.com/reset-password?t='.$resetDetails['uuid'].'">https://app.creativebraindesign.com/reset-password?t='.$resetDetails['uuid'].'</a>';
        	$body .= '<br><br>If this is not requested by you, please ignore this email';

	    	$body .= '</p></td></tr></table>';
			//--- CUSTOMIZE SUBJECT & BODY
			
			$supplierAddress = 'app.creativebraindesign.com';
			$supplierName = 'Creative Brains for Design and Marketing';
			$supplierLogo = '<img src="https://app.creativebraindesign.com/assets/img/logo.png" alt="Creative Brains for Design and Marketing Logo" style="max-height:100%;max-width:100%;object-fit: contain;"/>';				

			$html = '<table id="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="padding-top:20px !important;background-color: #f2f4f6;">
			  <tr>
			    <td align="center">
			      <table class="email-content" width="100%" cellpadding="0" cellspacing="0" role="presentation">          
			        <tr>
			          <td class="email-body" style="max-width: 570px;" cellpadding="0" cellspacing="0">
			            <table class="email-body_inner" align="center" style="max-width: 570px;padding:10px 0;background-color:#fff;" cellpadding="0" cellspacing="0" role="presentation">
			              <tr>
			                <td>
			                  <div style="padding: 10px 30px 20px;">
			                  <div style="display: flex;justify-content: center;align-items: center;max-height: 125px;max-width: 200px;text-align: center;margin:20px auto;">'.$supplierLogo.'</div>
			                    '.$body.'<br>';
			                    $html .= '
			                  </div>
			                </td>
			              </tr>
			            </table>
			          </td>
			        </tr>
			        <tr>
			          <td>
			            <table style="margin: 20px auto;" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
			              <tr>
			                <td class="content-cell" align="center">
			                  <p style="color: #a8aaaf;font-size: 13px;max-width:300px;">
			                    '.$supplierName.'
			                    <br>'.$supplierAddress.'
			                  </p>
			                </td>
			              </tr>
			            </table>
			          </td>
			        </tr>
			      </table>
			    </td>
			  </tr>
			</table>';
			
			$col = [];
		    $col['address']['email'] = $user['email'];
		    $recipients[] = $col;		
		
			$css_styles = '<style type="text/css">@import url("https://fonts.googleapis.com/css?family=Nunito+Sans:400,700&display=swap"); body { margin: 0; } #email-wrapper { font-family: "Nunito Sans", Helvetica, Arial, sans-serif !important; }</style>';
			
		    $attachments = [];	        		    
			$emailArr = [];
			$emailArr['campaign_id'] = $resetDetails['id']."-".$resetDetails['user_id'];
			$emailArr['recipients'] = $recipients;								
			
			$emailArr['content']['from']['email'] = "no-reply@mailer.creativebraindesign.com";			
			$emailArr['content']['from']['name'] = $supplierName;
			$emailArr['content']['subject'] = $subject;
			$emailArr['content']['html'] = $css_styles.$html;
			$emailArr['content']['text'] = strip_tags($html);					
						
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, 'https://api.sparkpost.com/api/v1/transmissions?num_rcpt_errors=3');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailArr));
			
			$headers = array();
			$headers[] = 'Content-Type: application/json';
			$headers[] = 'Accept: application/json';
			$headers[] = 'Authorization: '.SPARKPOST_API_KEY;
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

			$result = json_decode(curl_exec($ch));					
			if (curl_errno($ch)) {
			      echo 'Error:' . curl_error($ch);
			      return false;
			}
			
			if ($result) {
			    if (isset($result->errors)) {
			      	$return['status'] = false;
					$return['message'] = "Failed: ".$result->errors[0]->message;   					     
					return false;
			    } else {
			    	$return['status'] = true;
					$return['message'] = __("Email sent!",true);
					$return['sent_at'] = date(DATEFORMAT." H:i A");
					return true;
			    }    
			} else {
			    $return['status'] = false;
				$return['message'] = __("Failed to send email. Please contact support.",true);
				return false;
			}
			curl_close($ch);
		} else {
			return false;
		}
		exit;
	}

	function reset_password() {
		if(isset($_POST['data']))
		{					
			$UM = new UserModel;			
			parse_str($_POST['data'],$postdata);
			
			$user = $UM->getGlobalbyId("reset_password_key",$postdata['uuid'],false);
			if($user!=NULL)
			{						
				$newData = [];
				$newData['password'] = sha1($postdata['new_pass']);
				$res = $UM->updateGlobal("users",$user['user_id'],$newData);
				if ($res===true) {
					$UM->deleteGlobalById("reset_password_key",$postdata['uuid']);
					//SEND EMAIL LINK to reset password
					$return['status'] = true;
					$return['message'] = "Password reset successfully. Please proceed to login page.";
				} else {
					$return['status'] = false;
					$return['message'] = "Unable to reset password";
				}
			}
			else
			{
				$return['status'] = false;
				$return['message'] = "Please provide valid email";
			}			
		} else {
				$return['status'] = false;
				$return['message'] = "Please provide valid email";
		}
		echo json_encode($return);
		exit;
	}	

	function register() {
		if(isset($_POST['data']))
		{		
			parse_str($_POST['data'],$postdata);			
			$UM = new UserModel;
			$res = $UM->getEmailUnique($postdata['email']);
			if ($res===false) {
				$return['status'] = false;
				$return['message'] = "Email address already used. Please try again with another email.";
				echo json_encode($return);
				exit;
			}
			$newData = [];
			$newData['uuid'] = uuidv4();
			$newData['role'] = "client";
			$newData['role_id'] = 4; //client
			$newData['first_name'] = ucwords($postdata['first_name']);
			$newData['last_name'] = ucwords($postdata['last_name']);
			$newData['company'] = ucfirst($postdata['company']);
			$newData['email'] = $postdata['email'];
			$newData['password'] = sha1($postdata['password']);						
			$result = $UM->addGlobal("users",$newData);

			if($result!=NULL)
			{	
				$filter = [];
				$filter['company_domain'] = $_SERVER['SERVER_NAME'];
				$siteDetails = $UM->getGlobal('sites',null,null,$filter);

				//save to site users
				if($siteDetails!=NULL){
					$siteUsersData['uuid'] = uuidv4();
					$siteUsersData['user_id'] = (int)$result['id'];
					$siteUsersData['site_id'] = (int)$siteDetails[0]['id'];
					$siteUsersData['created_by'] = (int)$result['id'];
					$result = $UM->addGlobal('site_users', $siteUsersData);
				}
				$return['siteDetails'] = $siteDetails;

				unset($newData['password']);	
				$payload = $newData;
				$payload['ip_address'] = $_SERVER['REMOTE_ADDR'];
				$jwt = JWT::encode($payload, KEY, 'HS256');
				$user_id = $newData['uuid'];
				if ($jwt!="") {
					$return['status'] = true;
					$return['token'] = $jwt;			
					unset($newData['uuid']);
					$return['userdata'] = $newData;		
					$_SESSION['token'] = $jwt;
					$_SESSION['role'] = $payload['role'];
					$updateData = [];
					$updateData['last_login'] = date("Y-m-d H:i:s");
					$UM->updateGlobal('users',$user_id,$updateData);
				} else {
					$return['status'] = false;
					$return['message'] = __("Unable to sign up account. Please try again.",true);
				}
			}
			else
			{
				$return['status'] = false;
				$return['message'] = __("Unable to sign up account. Please try again.",true);
			}
			echo json_encode($return);
			exit;
		}
	}

	function clean($string) {   
        $string = str_replace('"', "&quot;", $string);
        $string = str_replace("'", "&apos;", $string);
        return $string;
    }

    function cleanHard($string) {   
        $string = str_replace('"', "&quot;", $string);
        $string = str_replace("'", "&apos;", $string);
        $string = str_replace("(", "", $string);
        $string = str_replace(")", "", $string);
        $string = str_replace("%", "", $string);
        $string = str_replace("^", "", $string);
        $string = str_replace(">", "", $string);
        $string = str_replace("<", "", $string);
        $string = str_replace("*", "", $string);
        return trim($string);
    }    

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

	function encode_str_asterisk($string) {		
		// Get the first 3 characters of the string
		$first_three = substr($string, 0, 3);

		// Get the last 3 characters of the string
		$last_three = substr($string, -3);

		// Get the characters between the first 3 and last 3 characters
		$middle = substr($string, 3, -3);

		// Replace the characters in the middle with asterisks
		$middle = str_repeat("*", strlen($middle));

		// Concatenate the first 3 characters, middle with asterisks, and last 3 characters
		$new_string = $first_three . $middle . $last_three;

		return $new_string;
	}


	function trim_emails($emails) {
		$emails = str_replace(";",",",$emails);
		$emailArr = explode(",",$emails);
		if (count($emailArr)>1) {
			$newTrim = [];
			foreach ($emailArr as $key => $value) {
				$newTrim[] = trim($value);
			}
			return implode(",",$newTrim);
		} else {
			return trim($emails);
		}
	}

	function dir_exist($path) {
		if (is_dir('./client/'.$path)) {
			return true;
		} else {
			return false;
		}
		exit;
	}

	function upfile() {		
		if (isset($_POST['token'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));

				/* Getting file name */
				$filename = $_FILES['file']['name'];	
				/* Location */
				$userdir = $_SESSION['userdata']['uuid'];
				if (!dir_exist($userdir)) {
					mkdir('./client/'.$userdir);
				}
				$directory = "./client/".$userdir;
				$location = $directory."/".$filename;

				$uploadOk = 1;
				$fileType = pathinfo($location,PATHINFO_EXTENSION);	

				$fname = str_replace(".".$fileType, "", $filename);
				while(file_exists($location)) {
					$filename = cleanHard($fname."-".generate_string(4).".".$fileType);
					$location = $directory."/".$filename;
				}
				/* Valid Extensions */
				if (isset($_POST['upload_type']) && $_POST['upload_type']=="logo") {
					$valid_extensions = array("png","jpg","jpeg");
				} else {
					$valid_extensions = array("pdf");
				}
				/* Check file extension */
				if ( !in_array(strtolower($fileType),$valid_extensions) ) {
				   $uploadOk = 0;
				   $return['status']=false;
				   $return['message'] = __("Invalid file type / extension",true).".";				   
				   echo json_encode($return);
					exit;
				}

				if ($uploadOk != 0) {
				   /* Upload file */
				   $newfilename = $filename;		   		   			

				   	if(move_uploaded_file($_FILES['file']['tmp_name'],$directory."/".$newfilename)){		      
				      	$return['status']=1;
					    $file_results_col['file'] = SITE_URL.str_replace("./","/",$directory."/".$newfilename);
					    $file_results_col['file_name'] = $newfilename;
					    $file_results = $file_results_col;
						
				   	} else {
				   		$return['status']=false;
						$return['message'] = __("Not uploaded due to an error #",true).$_FILES["file"]["error"];
						echo json_encode($return);
						exit;
				   	}
				}
				$return['status']=1;
				$return['data'] = $file_results;

				echo json_encode($return);
				exit;	
			} catch (Exception $e) {
				$return['status'] = false;
				$return['message'] = $e->getMessage();
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("Missing Token",true);
		}

		echo json_encode($return);
		exit;	
	}

	function file_upload($filename,$filetmp,$upload_type="logo") {		
		/* Getting file name */
		$filename = $filename;
		/* Location */
		$userdir = $_SESSION['userdata']['uuid'];
		if (!dir_exist($userdir)) {
			mkdir('./client/'.$userdir);
		}
		$directory = "./client/".$userdir;
		$location = $directory."/".$filename;

		$uploadOk = 1;
		$fileType = pathinfo($location,PATHINFO_EXTENSION);	

		$fname = str_replace(".".$fileType, "", $filename);
		while(file_exists($location)) {
			$filename = cleanHard($fname."-".generate_string(4).".".$fileType);
			$location = $directory."/".$filename;
		}
		/* Valid Extensions */
		if ($upload_type=="logo") {
			$valid_extensions = array("png","jpg","jpeg");
		} else {
			$valid_extensions = array("pdf");
		}
		/* Check file extension */
		if ( !in_array(strtolower($fileType),$valid_extensions) ) {
		   $uploadOk = 0;
		   $return['status']=false;
		   $return['message'] = __("Invalid file type / extension",true).".";
		}

		if ($uploadOk != 0) {
		   /* Upload file */
		   $newfilename = $filename;		   		   			

		   	if(move_uploaded_file($filetmp,$directory."/".$newfilename)){		      
		      	$return['status']=1;
			    $file_results_col['file'] = SITE_URL.str_replace("./","/",$directory."/".$newfilename);
			    $file_results_col['file_name'] = $newfilename;
			    $file_results = $file_results_col;
				$return['data'] = $file_results;
		   	} else {
		   		$return['status']=false;
				$return['message'] = __("Not uploaded due to an error #",true);			
		   	}
		}

		return $return;
	}


	// --- SITE SETTINGS --- //
	function fetch_site_settings() {
		if (isset($_POST['token'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				
				if ($payload->role=="admin") {
					$UM = new UserModel();
					$result = $UM->getGlobal('site_settings');
					if (count($result)>0) {
						$return['status'] = true;
						$return['data'] = $result;
					} else {
						$return['status'] = true;
					}
				} else {
					$return['status'] = false;
					$return['message'] = __("Access Denied",true);
				}
				
			} catch (Exception $e) {
				$return['status'] = false;
				$return['message'] = $e->getMessage();
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("Missing Token",true);
		}

		echo json_encode($return);
		exit;
	}

	function update_site_settings() {
		if(isset($_POST['data']) && isset($_POST['token']))
		{
			parse_str($_POST['data'],$postdata);
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue->role=="admin") {
				$UM = new UserModel;				
				$updateData = [];

				foreach ($postdata as $key => $value) {
					$check = $UM->checkSettingsExist($key);
					if($check) {
						$result = $UM->updateSiteSettings($key,$value);
					} else {	
						$newData = [];					
						$newData['name'] = $key;
						$newData['value'] = $value;
						$newData['updated_by'] = $_SESSION['userdata']['uuid'];
						$result = $UM->addGlobal('site_settings',$newData);
					}
				}

				$return['status'] = true;
				$return['message'] = __("Successfully updated platform settings",true);
				
			} else {
				$return['status'] = false;
				$return['message'] = "Invalid Token. Please re-log in or contact support.";
			}
			
			echo json_encode($return);
		}
		exit;
	}




	//----- PROFILE ------//
	function fetch_user() {
		if(isset($_POST['token']))
		{			
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));						
			
			if ($tokenvalue) {
				$UM = new UserModel;				
				$newdata = $UM->getGlobalbyId("users",$tokenvalue->id,false);				
				unset($newdata['password']);
				unset($newdata['created_on']);
				$return['userdata'] = $newdata;
				$return['status'] = true;
				
			} else {
				$return['status'] = false;
				$return['message'] = "Invalid Token. Please re-log in or contact support.";				
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function fetch_profile() {
		if(isset($_POST['token']))
		{			
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));						
			if ($tokenvalue) {
				$UM = new UserModel;
				$newdata = [];
				if (isset($postdata['new_password'])) {
					$postdata['password'] = sha1($postdata['new_password']);
					unset($postdata['current_password']);
					unset($postdata['new_password']);
					unset($postdata['confirm_new_password']);					
				}
				$newdata = $postdata;
				$id = $tokenvalue->uuid;
				$result = $UM->updateUser($id,$newdata);	
				if($result)
				{
					if (isset($newdata['password'])) {
						$return['status'] = true;
						$return['message'] = __("Successfully updated password",true);
					} else {
						$newdata = $UM->getGlobalbyId("users",$id);						
						unset($newdata['password']);
						unset($newdata['created_on']);
						unset($newdata['is_deleted']);
						$payload = $newdata;
						$jwt = JWT::encode($payload, KEY, 'HS256');											
						$_SESSION['token'] = $jwt;
						$_SESSION['userdata'] = (array)$payload;
						$_SESSION['role'] = $payload['role'];
						$return['token'] = $jwt;
						$return['userdata'] = (array)$payload;
						$return['status'] = true;
						$return['message'] = __("Successfully updated profile",true);
					}					
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to update profile. Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = "Invalid Token. Please re-log in or contact support.";				
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function update_profile() {
		if(isset($_POST['data']) && isset($_POST['token']))
		{
			parse_str($_POST['data'],$postdata);		
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;
				$newdata = [];				
				$newdata = $postdata;
				$id = $tokenvalue->id;
				$result = $UM->updateGlobal("users",$id,$newdata);	
				if($result)
				{
					$newdata = $UM->getGlobalbyId("users",$id,false);					
					unset($newdata['password']);
					$payload = $newdata;					
					$jwt = JWT::encode($payload, KEY, 'HS256');											
					$_SESSION['token'] = $jwt;
					$_SESSION['userdata'] = (array)$payload;
					$_SESSION['role'] = $payload['role'];
					$return['token'] = $jwt;
					$return['userdata'] = (array)$payload;
					$return['status'] = true;
					$return['message'] = __("Successfully updated profile",true);
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to update profile. Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = "Invalid Token. Please re-log in or contact support.";				
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function update_password() {
		if(isset($_POST['data']) && isset($_POST['token']))
		{
			parse_str($_POST['data'],$postdata);
			$UM = new UserModel;
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
						
			if ($tokenvalue) {
				if (isset($postdata['uuid'])) {
					$user = $UM->getGlobalbyId("users",$postdata['uuid']);
					$email = $user['email'];
					$id = $postdata['uuid'];
				} else {
					$email = $tokenvalue->email;
					$id = $tokenvalue->uuid;
				}
				
				$check = $UM->checkPassword($email,$postdata['old_pass']);
				if ($check===NULL) {
					$return['status'] = false;
					$return['message'] = __("Old password is incorrect. Please try again.",true);
					echo json_encode($return);
					exit;
				}
				
				$newdata = [];
				if (isset($postdata['new_pass'])) {
					$newdata['password'] = sha1($postdata['new_pass']);
				}				
				
				$result = $UM->updateGlobal("users",$id,$newdata);	
				if($result)
				{
					if (isset($newdata['password'])) {
						$return['status'] = true;
						$return['message'] = __("Successfully updated password",true);
					} else {
						$newdata = $UM->getGlobalbyId("users",$id);
						unset($newdata['id']);
						unset($newdata['password']);
						unset($newdata['created_on']);
						unset($newdata['is_deleted']);
						$payload = $newdata;
						$jwt = JWT::encode($payload, KEY, 'HS256');											
						$_SESSION['token'] = $jwt;
						$_SESSION['userdata'] = (array)$payload;
						$_SESSION['role'] = $payload['role'];
						$return['token'] = $jwt;
						$return['userdata'] = (array)$payload;
						$return['status'] = true;
						$return['message'] = __("Successfully updated profile",true);
					}					
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Unable to update password. Please try again.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = "Invalid Token. Please re-log in or contact support.";				
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function check_password() {
		if(isset($_POST['password']) && isset($_POST['token']))
		{
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;			
				$newdata['email'] = $tokenvalue->email;
				$newdata['password'] = $_POST['password'];
				$result = $UM->loginUser($newdata);
				if($result==NULL)
				{
					$return['status'] = false;
				}
				else
				{
					$return['status'] = true;
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = "Invalid Token. Please re-log in or contact support.";				
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function fetch_table() {		
		if(isset($_POST['token']) && isset($_POST['t']))
		{
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if (true) {
				$UM = new UserModel;				
				$datatable = [];

				if (isset($_POST['filter']) && $_POST['filter']!="All") {
					$filter = [];
					$filter['district'] = $_POST['filter'];
					$result = $UM->getGlobal($_POST['t'],null,null,$filter);
				} else {

					if ($_POST['t']=="clients" && $tokenvalue->role=="territory manager") {
						$result = $UM->fetchClientsByUserId($tokenvalue->uuid);
					} else if ($_POST['t']=="products" && $tokenvalue->role=="territory manager") { 
						$result = $UM->fetchProductsByUserId($tokenvalue->uuid);
					} else if($_POST['t']=="roles"){
						$is_fixed_roles = (int)$_SESSION['site_id']===0?1:0; 
						$result = $UM->fetchDynamicRoles($_SESSION['site_id'], $is_fixed_roles);
						$return['result'] = $result;
						$return['is_fixed_roles'] = $is_fixed_roles;
						$return['site_id'] = $_SESSION['site_id'];
					}else if($_POST['t']=="services"){
						$result = $UM->getGlobalbySpecificField($_POST['t'], 'site_id', $_SESSION['site_id']);
					}else if($_POST['t']=="clients"){
						$filter = [];
						$filter['site_id'] = $_SESSION['site_id'];
						$filter['role_id'] = 4;
						$_SESSION['is_client'] = true;
						$result = $UM->getGlobal('users',null,null,$filter);
					}else if($_POST['t']=="users"){
						$_SESSION['is_client'] = false;
						$result = $UM->getGlobal($_POST['t']);	
					}else if($_POST['t']=="tickets"){
						$result = $UM->getGlobalbySpecificField($_POST['t'], 'order_id', $_POST['order_id']);	
					}
					else {
						$result = $UM->getGlobal($_POST['t']);	
					}
				}	
				foreach ($result as $key => $value) {
					if ($_POST['t']=="clients") {
						$col = [];
						$col[] = ucwords($value['first_name']." ".$value['last_name']);
						$col[] = $value['email'];
						$col[] = ucwords($value['company']);						
						$col[] = "<label class='badge bg-label-success' style='color:#fff !important;'>FREE</label>";
						$datatable[] = $col;
						
						/*
						$col = [];
						$col[] = '<strong>'.$value['client_code'].'</strong>';						
						$col[] = $value['client_name'];
						$consolidated = [];
						if ($value['client_address1']!="") {
							$consolidated[] = $value['client_address1'];
						}
						if ($value['client_address2']!="") {
							$consolidated[] = $value['client_address2'];
						}
						$col[] = implode("<br>",$consolidated);
						$consolidated = [];
						if ($value['client_contact']!="") {
							$consolidated[] = "C: ".$value['client_contact'];
						}
						if ($value['client_email']!="") {
							$consolidated[] = "E: ".$value['client_email'];
						}
						if ($value['client_telephone']!="") {
							$consolidated[] = "T: ".$value['client_telephone'];
						}
						if ($value['client_cellphone']!="") {
							$consolidated[] = "P: ".$value['client_cellphone'];
						}
						if ($value['client_fax']!="") {
							$consolidated[] = "F: ".$value['client_fax'];
						}

						$col[] = implode("<br>",$consolidated);
						$col[] = $value['client_type'];						
						if ($tokenvalue->role=="admin") {
							$col[] = '<a class="pointer openBoxBtn" data-id="'.$value['uuid'].'"><i class="bx bx-edit-alt me-1"></i></a>';
						} else {
							$col[] = '<a class="pointer infoBoxBtn" data-id="'.$value['uuid'].'"><i class="bx bx-info-circle me-1"></i></a>';
						}
						$datatable[] = $col;
						*/
						
					} else if ($_POST['t']=="users" && $tokenvalue->role=="admin") {				
						$col = [];
						$col[] = ucwords($value['first_name']." ".$value['last_name']);
						$col[] = ucwords($value['company']);
						$col[] = $value['email'];
						$col[] = ucwords($value['role_description']);						
						$col[] = '<a class="pointer openBoxBtn" data-id="'.$value['uuid'].'"><i class="bx bx-edit-alt me-1"></i></a><a class="pointer changePassBtn" data-id="'.$value['uuid'].'"><i class="bx bx-lock me-1"></i></a>';
						$datatable[] = $col;
						
					} else if ($_POST['t']=="roles" && $tokenvalue->role=="admin") {											
						if ($_SESSION['role']=="admin") {
							$col = [];
							$col[] = ucwords($value['role_name']);
							$col[] = ucwords($value['role_description']);						
							$col[] = '<a class="pointer openBoxBtn" data-id="'.$value['uuid'].'"><i class="bx bx-edit-alt me-1"></i></a>';
							$datatable[] = $col;
						}
						
					} else if ($_POST['t']=="modules" && $tokenvalue->role=="admin") {											
						if ($_SESSION['role']=="admin") {
							$col = [];
							$col[] = $value['module_name'];
							$col[] = $value['module_description'];						
							$col[] = '<a class="pointer openBoxBtn" data-id="'.$value['uuid'].'"><i class="bx bx-edit-alt me-1"></i></a>';
							$datatable[] = $col;
						}
						
					}else if ($_POST['t']=="services" && $tokenvalue->role=="admin") {											
						if ($_SESSION['role']=="admin") {
							$col = [];
							$col[] = $value['service_name'];
							$col[] = $value['service_description'];
							$col[] = "$ " . number_format((float)$value['service_price'], 2, '.', '');				
							$col[] = '<a class="pointer openBoxBtn" data-id="'.$value['uuid'].'"><i class="bx bx-edit-alt me-1"></i></a>';
							$datatable[] = $col;
						}
						
					} else if ($_POST['t']=="services" && $tokenvalue->role=="client") {
						$col = [];
						$col['id'] = $value['id'];
						$col['uuid'] = $value['uuid'];
						$col['service_name'] = $value['service_name'];
						$col['service_description'] = $value['service_description'];
						$col['service_price'] = "$ " . number_format((float)$value['service_price'], 2, '.', '');	
						$datatable[] = $col;
						//$datatable[] = $result;
					} else if ($_POST['t']=="sites" && $tokenvalue->role=="admin") {											
						if ($_SESSION['role']=="admin") {
							$col = [];
							$col[] = $value['company_name'];
							$col[] = $value['company_domain'];
							$col[] = ucwords($value['first_name']." ".$value['last_name']);		
							$col[] = '<a class="pointer openBoxBtn" data-id="'.$value['uuid'].'"><i class="bx bx-edit-alt me-1"></i></a>';
							$datatable[] = $col;
						}
						
					} else if ($_POST['t']=="coupons" && $tokenvalue->role=="admin") {											
						$col = [];						
						$col[] = $value['coupon_code'];
						if ($value['coupon_type']=="value") {
							$col[] = "-$".number_format($value['coupon_value'],2,".",",");
						} else {
							$col[] = number_format($value['coupon_value'],1)."% OFF";
						}
						
						if (isset($value['client_details'])) {
							$col[] = ucwords($value['client_details']['first_name']." ".$value['client_details']['last_name']);
						} else {
							$col[] = ""; 
						}
						$col[] = '<a class="pointer openBoxBtn" data-id="'.$value['uuid'].'"><i class="bx bx-edit-alt me-1"></i></a>';
						$datatable[] = $col;
						
					} else if ($_POST['t']=="tickets") {				
						$col = [];
						$col[] = '<a class="pointer openBoxBtn" data-id="'.$value['uuid'].'"><b>'.str_pad($value['id'], 5, '0', STR_PAD_LEFT).'</b></a>';
						$col[] = '<span class="ellipsis">'.ucwords($value['header']).'</span>';
						$col[] = $value['status'];
						$col[] = $value['updated_at'];					
						$datatable[] = $col;
					} 
					
				}						
				
				$return['status'] = true;
				$return['datatable'] = $datatable;											
			} else {
				$return['status'] = false;
				$return['message'] = "Access denied. Please re-log in or contact support.";
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function fetch_data() {
		if(isset($_POST['token']) && isset($_POST['t']) && isset($_POST['id']))
		{
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			$UM = new UserModel;				
			$datatable = [];
			if(isset($_POST['feature']) && $_POST['feature']=='site_settings'){
				$result = $UM->getRowByField($_POST['t'], 'id', $_POST['id']);
			} else {
				$result = $UM->getGlobalbyId($_POST['t'],$_POST['id']);	
			}
			
			unset($result['is_deleted']);		
			$return['status'] = true;
			foreach ($result as $key => $value) {
				if ($value!="" && $value!=NULL) {
					if($_POST['t']=='tickets'){
						//$result[$key] = $result[$key]=='id'?str_pad($value, 5, '0', STR_PAD_LEFT):unclean($value);
						//$result[$key] = $result[$key]=='header'?ucwords($value):unclean($value);
					} else {
						$result[$key] = unclean($value);
					}	
				}
			}
			if ($_POST['t']=="clients") {
				$products = $UM->fetchProductsByClientId($result['uuid']);
				$result['products'] = [];
				foreach ($products as $key => $value) {
					$col = [];		
					$col[] = $value['product_code'];
					$col[] = $value['product_name'];
					$col[] = $value['product_unit'];
					$result['products'][] = $col;
				}
			}
			if ($_POST['t']=="roles") {
				if(isset($result['uuid']) && $result['uuid']!=''){
					$rolemodules = $UM->fetchModulesJoiningRoleModules($result['uuid']);
					$result['rolemodules'] = $rolemodules;
				}
			}
			$return['data'] = $result;					
		} else {
			$return['status'] = false;
			$return['message'] = "Invalid Token. Please re-log in or contact support.";
		}
		
		echo json_encode($return);
		exit;
	}

	function fetch_for_dropdown() {
		if(isset($_POST['token']) && isset($_POST['t']))
		{
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			$UM = new UserModel;				
			$datatable = [];
			if($_POST['t']=='users' && isset($_POST['field']) && isset($_POST['value'])){
				if(isset($_POST['tier'])){
					$filter = [];
					$filter['su.site_id'] = $_SESSION['site_id'];
					$result = $UM->getGlobalbySpecificField($_POST['t'], $_POST['field'], $_POST['value'], $filter);
				} else {
					$result = $UM->getGlobalbySpecificField($_POST['t'], $_POST['field'], $_POST['value']);
				}
			} else if($_POST['t']=="roles"){
				$is_fixed_roles = (int)$_SESSION['site_id']===0?1:0; 
				$result = $UM->fetchDynamicRoles($_SESSION['site_id'], $is_fixed_roles);
			} else {
				$result = $UM->getGlobal($_POST['t']);	
			}
			$return['status'] = true;
			$return['data'] = $result;					
		} else {
			$return['status'] = false;
			$return['message'] = "Invalid Token. Please re-log in or contact support.";
		}
		
		echo json_encode($return);
		exit;
	}

	function addupdate_data() {
		if(isset($_POST['token']) && isset($_POST['t']) && isset($_POST['data']))
		{
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			$UM = new UserModel;				
			
			//restrict by role
			parse_str($_POST['data'],$postdata);
			$newData = [];
			$newData = $postdata;				
						
			if ($newData['id']=="") {
				unset($newData['id']);
				if ($_POST['t']=="licenses" && !isset($postdata['is_enabled'])) {
					$newData['is_enabled'] = 0;
				}
				$result = $UM->addGlobal($_POST['t'],$newData);
			} else {				
				$id = $newData['id'];
				if ($_POST['t']=="licenses" && !isset($postdata['is_enabled'])) {
					$newData['is_enabled'] = 0;
				}
				$result = $UM->updateGlobal($_POST['t'],$id,$newData);	
			}
			if ($result===true) {
				$return['postData'] = $postdata;
				$return['status'] = true;

				if ($postdata['id']=="") {
					$return['message'] = "Successfully added data";
				} else {
					$return['message'] = "Successfully updated data";
				}
			} else {
				$return['status'] = false;
				$return['error_message'] = $result;
				if ($postdata['id']=="") {
					$return['message'] = "Failed to add data";
				} else {					
					$return['message'] = "Failed to update data";
				}
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function delete_data() {
		if(isset($_POST['token']) && isset($_POST['t']) && isset($_POST['id']))
		{
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			$UM = new UserModel;				
			
			//restrict by role
			$result = $UM->deleteGlobalById($_POST['t'],$_POST['id']);	
			if ($result) {
				$return['status'] = true;
				$return['message'] = "Successfully deleted data";
			} else {
				$return['status'] = false;
				$return['message'] = "Failed to delete data";
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function check_coupon() {
		if(isset($_POST['token']))
		{
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			$UM = new UserModel;
			$check = $UM->checkCoupon($_POST['coupon']);			
			
			if ($check===false) {
				$return['message'] = "Coupon code is invalid";
				$return['status'] = false;
			} else {
				if ($check['assigned_to']!=null && $check['assigned_to']!="") {					
					if ($check['assigned_to']==$tokenvalue->uuid) {
						$return['status'] = true;
						$return['coupon_id'] = $check['uuid'];
						$return['coupon_value'] = $check['coupon_value'];
						$return['coupon_type'] = $check['coupon_type'];	
					} else {
						$return['message'] = "Coupon code is invalid";
						$return['status'] = false;
					}
				} else {
					$return['status'] = true;
					$return['coupon_id'] = $check['uuid'];
					$return['coupon_value'] = $check['coupon_value'];
					$return['coupon_type'] = $check['coupon_type'];	
				}				
			}			
			
		} else {
			$return['status'] = false;
			$return['message'] = "Invalid Token. Please re-log in or contact support.";
		}
		echo json_encode($return);
		exit;
	}

	function service_pricing() {
		$service = [];
		$service['blogger outreach']['dr_30'] = 120;
		$service['blogger outreach']['dr_40'] = 160;
		$service['blogger outreach']['dr_50'] = 250;
		$service['blogger outreach']['dr_60'] = 300;
		$service['ecommerce seo']['LITE Package'] = 1199;
		$service['ecommerce seo']['PRO Package'] = 1799;
		$service['ecommerce seo']['ENT Package'] = 2499;
		$service['keyword research']['$99/Report'] = 99;
		$service['pdp optimization']['$99/Content per page'] = 99;
		$service['seo audit reports']['Basic Report'] = 99;
		$service['seo audit reports']['PRO Report'] = 149;
		$service['blog management']['Basic Package'] = 1000;
		$service['blog management']['Standard Package'] = 1350;
		$service['blog management']['Premium Package'] = 2000;
		return $service;
	}

	function calculate_subtotal($service,$packages) {
		$subtotal = 0;
		$prices = service_pricing();
		switch ($service) {
			case 'blogger outreach':
				foreach ($packages as $key => $value) {
					$subtotal += ($value*$prices[$service][$key]);
				}
				break;
			case 'ecommerce seo':
				$subtotal += $prices[$service][$packages];
				break;
			case 'keyword research':
				$subtotal += $prices[$service][$packages];
				break;			
			default:
				$subtotal += $prices[$service][$packages];
				break;
		}

		return $subtotal;
	}

	function add_order() {
		if(isset($_POST['token']) && isset($_POST['data']))
		{			
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			$UM = new UserModel;				
						
			parse_str($_POST['data'],$postdata);			

			$newData = [];
			$order_id = $newData['uuid'] = uuidv4();
			$last_order = $UM->fetchLastOrder($tokenvalue->uuid);
			if ($last_order===false) {
				$last_order['order_id'] = 0;
			}
			$newData['order_id'] = (int)$last_order['order_id']+1;
			$newData['site_id'] = $_SESSION['site_id'];	
			$newData['user_id'] = $tokenvalue->uuid;
			$newData['service'] = $postdata['service'];
			if ($postdata['service']=="blogger outreach") {
				$newData['line_items'] = json_encode($postdata['package']);
				$newData['amount'] = calculate_subtotal($postdata['service'],$postdata['package']);
			} else {				
				//$newData['amount'] = calculate_subtotal($postdata['service'],$postdata['package_value']);
				$quantity = isset($postdata['quantity'])?$_POST['quantity']:1;
				$newData['amount'] = $postdata['price'] * $quantity;
			}
								
			$newData['comments'] = $postdata['comments'];

			if (isset($postdata['package_value'])) {
				$newData['package'] = $postdata['package_value'];
			}			

			if (isset($postdata['pre_approved_websites'])) {
				$newData['pre_approved_websites'] = 1;
			}
			if (isset($postdata['anchor_landing_pages'])) {
				$newData['anchor_landing_pages'] = 1;
			}
			if (isset($postdata['website'])) {
				$newData['website'] = $postdata['website'];
			}
			if (isset($postdata['no_pages'])) {
				$newData['no_pages'] = $postdata['no_pages'];
			}

			$newData['payment_method'] = $postdata['payment_method'];
			if ($newData['payment_method']=="paypal" && isset($_POST['paymentID'])) {
				$newData['status'] = "Paid";
				$newData['payment_id'] = $_POST['paymentID'];
				$newData['paid_with'] = "paypal";
				$newData['payment_date'] = date("Y-m-d");
			} else if ($newData['payment_method']=="razorpay" && isset($_POST['paymentID'])) {
				$attributes = array(
				    'razorpay_order_id' => $_POST['razorpay_order_id'],
				    'razorpay_payment_id' => $_POST['paymentID'],
				    'razorpay_signature' => $_POST['razorpay_signature']
				);

				$api = new Api(RAZORPAY_API_KEY, RAZORPAY_CLIENT_SECRET);

				try {
				    $success = $api->utility->verifyPaymentSignature($attributes);
				    $newData['status'] = "Paid";
					$newData['payment_id'] = $_POST['paymentID'];
					$newData['paid_with'] = "razorpay";
					$newData['payment_date'] = date("Y-m-d");
					//continue with adding order
				} catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
				    // Payment verification failed				   
				    $return['status'] = false;
				    $return['message'] = "Payment verification failed";
				    echo json_encode($return);
				    exit;
				}				
			}
			
			if ($postdata['coupon_code']!="") {
				$newData['coupon'] = $postdata['coupon_code'];
			}

			$doc_link= isset($postdata['doc_link'])?$postdata['doc_link']:'';
			if ($doc_link!="") {
				$transformedData = fetchSheetData($postdata['doc_link']);
				$newData['order_details'] = json_encode($transformedData);
			}			

			$result = $UM->addGlobal("orders",$newData);			
			if ($result===true) {
				$user = $UM->getGlobalbyId("users",$newData['user_id']);
				$order = $newData;
				if ($newData['payment_method']=="pay_later") {
					$status = "Awaiting Payment";
				} else {
					$status = "Paid";
				}
				
				send_order_status_update_email($user,$order,$status);
				send_order_status_update_email_admin($user,$order,$status);
				$return['status'] = true;
				$return['message'] = "Successfully placed order";
				$return['order_id'] = $order_id;
			} else {
				$return['status'] = false;
				$return['error_message'] = $result;
				$return['message'] = "Failed to place order";
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function update_order() {
		if(isset($_POST['token']) && isset($_POST['data']))
		{
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			parse_str($_POST['data'],$postdata);						
			$UM = new UserModel;				
			if ($tokenvalue->role!="admin") {
				$order = $UM->fetchOrderById($postdata['uuid'],$tokenvalue->uuid);
			} else {
				$order = $UM->fetchOrderById($postdata['uuid']);
			}
			$newDate = [];
			$newData['order_details'] = json_encode($postdata['order_details']);			
			$result = $UM->updateGlobal("orders",$order['uuid'],$newData);			
			if ($result===true) {
				$return['status'] = true;
				$return['message'] = "Successfully updated order";
			} else {
				$return['status'] = false;
				$return['error_message'] = $result;
				$return['message'] = "Failed to update order";
			}									
			echo json_encode($return);
		}
		exit;
	}

	function update_doc_link() {
		if(isset($_POST['token']) && isset($_POST['order_id']))
		{
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));						
			$UM = new UserModel;							
			$newDate = [];
			$newData['doc_link'] = $_POST['doc_link'];
			$transformedData = fetchSheetData($_POST['doc_link']);
			$newData['order_details'] = json_encode($transformedData);				
			$result = $UM->updateGlobal("orders",$_POST['order_id'],$newData);			
			if ($result===true) {
				$return['status'] = true;
				$return['message'] = "Successfully updated order";
			} else {
				$return['status'] = false;
				$return['error_message'] = $result;
				$return['message'] = "Failed to update order";
			}												
		} else {
			$return['status'] = false;
			$return['message'] = "Missing token. Please contact support.";
		}
		echo json_encode($return);
		exit;
	}

	function order_payment() {
		if(isset($_POST['token']) && isset($_POST['order_id']))
		{
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));						
			$UM = new UserModel;
			$newData = [];
			if (isset($_POST['paymentID']) && $newData['paid_with']=="paypal") {
				$newData['payment_id'] = $_POST['paymentID'];
				$newData['paid_with'] = $_POST['paid_with'];
			} else if (isset($_POST['paymentID']) && $newData['paid_with']=="razorpay") {
				$attributes = array(
				    'razorpay_order_id' => $_POST['razorpay_order_id'],
				    'razorpay_payment_id' => $_POST['paymentID'],
				    'razorpay_signature' => $_POST['razorpay_signature']
				);

				$api = new Api(RAZORPAY_API_KEY, RAZORPAY_CLIENT_SECRET);

				try {
				    $success = $api->utility->verifyPaymentSignature($attributes);
					$newData['payment_id'] = $_POST['paymentID'];
					$newData['paid_with'] = $_POST['paid_with'];
					//continue with adding order
				} catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
				    // Payment verification failed				   
				    $return['status'] = false;
				    $return['message'] = "Payment verification failed";
				    echo json_encode($return);
				    exit;
				}	
			}
			$status = $newData['status'] = "Paid";			
			$newData['payment_date'] = date("Y-m-d");
			$result = $UM->updateGlobal("orders",$_POST['order_id'],$newData);
			if ($result===true) {
				$order = $UM->fetchOrderById($_POST['order_id']);
				$user = $UM->getGlobalbyId("users",$order['user_id']);
				send_order_status_update_email($user,$order,$status);
				send_order_status_update_email_admin($user,$order,$status);

				$return['status'] = true;
				$return['message'] = "Successfully paid order";
			} else {
				$return['status'] = false;
				$return['error_message'] = $result;
				$return['message'] = "Failed to pay order";
			}									
			echo json_encode($return);
		}
		exit;
	}

	function fetch_current_orders() {
		if(isset($_POST['token']))
		{
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			$UM = new UserModel;			
			if ($tokenvalue->role=="admin") {
				$orders = $UM->fetchOrdersByUserId(null,"current",$_POST['service']);	
			} else {
				$orders = $UM->fetchOrdersByUserId($tokenvalue->uuid,"current",$_POST['service']);	
			}
			
			$datatable = [];
			foreach ($orders as $key => $value) {
				$col = [];
				$col[] = '<b><a href="/order/'.$value['uuid'].'" target="_blank">'.str_pad($value['order_id'], 5, '0', STR_PAD_LEFT).'</a>';
				if ($tokenvalue->role!="client") {
					$users = $UM->getGlobalbyId("users",$value['user_id']);
					$col[] = $users['email'];
				}
				$col[] = ucwords(str_replace("pdp optimization","PDP Optimization",$value['service']));
				$col[] = date("d/m/Y",strtotime($value['date_ordered']));				

				$total_links = 0;
				if ($value['line_items']!=null && $value['line_items']!="") {
					foreach (json_decode($value['line_items'],true) as $links) {
						$total_links += (int)$links;
					}
					$total_amount = calculate_subtotal($value['service'],json_decode($value['line_items'],true)) - $value['coupon_discount'];
				} else {
					//$total_amount = calculate_subtotal($value['service'],$value['package']) - $value['coupon_discount'];
					$total_amount = $value['amount'];
				}				
								
				$col[] = number_format($total_amount,2,".",",");
				if ($tokenvalue->role=="client") {
					switch($value['status']) {
						case "Paid":							
							$status = '<label class="badge bg-info">Paid & Received</label>';		
							break;
						case "Client Approval Required":
							$status = '<button class="btn btn-primary btn-sm approvedBtn" data-id="'.$value['uuid'].'">Approved</button>';
							break;
						default:
							$status = '<label class="badge bg-info">'.$value['status'].'</label>';		
							break;
					}
				} else {
					switch($value['status']) {
						case "Paid":
							$status = '<button class="btn btn-primary btn-sm approvalRequired" data-id="'.$value['uuid'].'">Get Approval</button>';
							break;
						default:
							$status = '<label class="badge bg-info">'.$value['status'].'</label>';		
							break;
					}
				}
				$col[] = $status;
				
				$datatable[] = $col;
			}
			$return['status'] = true;
			$return['datatable'] = $datatable;
			
			echo json_encode($return);
		}
		exit;
	}

	function fetch_confirmed_orders() {
		if(isset($_POST['token']))
		{
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			$UM = new UserModel;			
			if ($tokenvalue->role=="admin") {
				$orders = $UM->fetchOrdersByUserId(null,"confirmed",$_POST['service']);	
			} else {
				$orders = $UM->fetchOrdersByUserId($tokenvalue->uuid,"confirmed",$_POST['service']);	
			}
			
			$datatable = [];
			foreach ($orders as $key => $value) {
				$col = [];
				$col[] = '<b><a href="/order/'.$value['uuid'].'" target="_blank">'.str_pad($value['order_id'], 5, '0', STR_PAD_LEFT).'</a>';
				if ($tokenvalue->role!="client") {
					$users = $UM->getGlobalbyId("users",$value['user_id']);
					$col[] = $users['email'];
				}
				$col[] = ucwords(str_replace("pdp optimization","PDP Optimization",$value['service']));
				$col[] = date("d/m/Y",strtotime($value['date_ordered']));				
				if ($value['line_items']!=null && $value['line_items']!="") {					
					$total_amount = calculate_subtotal($value['service'],json_decode($value['line_items'],true)) - $value['coupon_discount'];
				} else {
					$total_amount = calculate_subtotal($value['service'],$value['package']) - $value['coupon_discount'];
				}
				
				$col[] = number_format($total_amount,2,".",",");
				if ($tokenvalue->role=="client") {
					switch($value['status']) {
						case "Paid":							
							$status = '<label class="badge bg-info">Paid & Received</label>';		
							break;
						case "Client Approval Required":
							$status = '<button class="btn btn-primary btn-sm approvedBtn" data-id="'.$value['uuid'].'">Approved</button>';
							break;
						default:
							$status = '<label class="badge bg-info">'.$value['status'].'</label>';		
							break;
					}
				} else {
					switch($value['status']) {
						case "Paid":
							$status = '<button class="btn btn-primary btn-sm approvalRequired" data-id="'.$value['uuid'].'">asClient Approval Required</button>';
							break;
						case "Client Approval Required":
							$status = '<label class="badge bg-info">Pending Approval</label>';		
							break;
						case "In Process":
							$status = '<button class="btn btn-primary btn-sm completedBtn" data-id="'.$value['uuid'].'">Order Completed</button>';
							break;
						default:
							$status = '<label class="badge bg-info">'.$value['status'].'</label>';		
							break;
					}
				}
				$col[] = $status;
				
				$datatable[] = $col;
			}
			$return['status'] = true;
			$return['datatable'] = $datatable;
			
			echo json_encode($return);
		}
		exit;
	}

	function fetch_completed_orders() {
		if(isset($_POST['token']))
		{
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			$UM = new UserModel;			
			if ($tokenvalue->role=="admin") {
				$orders = $UM->fetchOrdersByUserId(null,"Completed",$_POST['service']);	
			} else {
				$orders = $UM->fetchOrdersByUserId($tokenvalue->uuid,"Completed",$_POST['service']);	
			}			
			
			$datatable = [];
			foreach ($orders as $key => $value) {
				$col = [];
				$col[] = '<b><a href="/order/'.$value['uuid'].'" target="_blank">'.str_pad($value['order_id'], 5, '0', STR_PAD_LEFT).'</a>';
				if ($tokenvalue->role!="client") {
					$users = $UM->getGlobalbyId("users",$value['user_id']);
					$col[] = $users['email'];
				}
				$col[] = ucwords(str_replace("pdp optimization","PDP Optimization",$value['service']));
				$col[] = date("d/m/Y",strtotime($value['date_ordered']));								
				if ($value['line_items']!=null && $value['line_items']!="") {					
					$total_amount = calculate_subtotal($value['service'],json_decode($value['line_items'],true)) - $value['coupon_discount'];
				} else {
					$total_amount = calculate_subtotal($value['service'],$value['package']) - $value['coupon_discount'];
				}
				$col[] = number_format($total_amount,2,".",",");
				$status = '<label class="badge bg-success">'.$value['status'].'</label>';		
				$col[] = $status;
				
				$datatable[] = $col;
			}
			$return['status'] = true;
			$return['datatable'] = $datatable;
			
			echo json_encode($return);
		}
		exit;
	}

	function set_order_status() {
		if(isset($_POST['token']))
		{
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			$UM = new UserModel;			
			if ($tokenvalue->role=="admin") {
				$order = $UM->fetchOrderById($_POST['order_id']);
				$user = $UM->getGlobalbyId("users",$order['user_id']);
				send_order_status_update_email($user,$order,$_POST['status']);
				send_order_status_update_email_admin($user,$order,$_POST['status']);
			} else {
				$order = $UM->fetchOrderById($_POST['order_id'],$tokenvalue->uuid);
			}
			
			$newData = [];
			$newData['status'] = $_POST['status'];
			$UM->updateGlobal("orders",$_POST['order_id'],$newData);
			$return['status'] = true;
			
			echo json_encode($return);
		}
		exit;
	}

	function send_order_status_update_email($user,$order,$status) {		
		if ($user!=null && $order!=null && $status!=null) {
			$UM = new UserModel();
			$service = str_replace("seo","SEO",$order['service']);
            $service = str_replace("pdp","PDP",$service);
            $service = ucwords($service);
			$client_name = ucwords($user['first_name']." ".$user['last_name']);
			$order_link = SITE_URL."/order/".$order['uuid'];
			$order_id = str_pad($order['order_id'], 5, '0', STR_PAD_LEFT);		
			switch ($status) {
				case 'Awaiting Payment':
					$subject = "Order #".$order_id." (".$service.") received & awaiting payment";
					$bodyMessage = 'Your order #'.$order_id.' ('.$service.') has been received and saved. Please send us a receipt of payment or contact us if you need assistance in paying.';
					break;
				case 'Paid':
					$subject = "Order #".$order_id." (".$service.") set as paid & received";
					$bodyMessage = 'Your payment for order #'.$order_id.' ('.$service.') has been confirmed and order details has been received.';
					break;
				case 'Client Approval Required':
					$subject = "Order #".$order_id." (".$service.") needs your approval";
					$bodyMessage = 'Your order #'.$order_id.' ('.$service.') has been checked and requires your approval before getting started. Please confirm and approve to proceed.';
					break;
				case 'In Process':
					$subject = "Order #".$order_id." (".$service.") confirmed and in process";
					$bodyMessage = 'Your order #'.$order_id.' ('.$service.') has been confirmed and currently in process.';
					break;
				case 'Completed':
					$subject = "Order #".$order_id." (".$service.") completed";
					$bodyMessage = 'Your order #'.$order_id.' ('.$service.') is now completed. If you want to make another order, click here: <a href="'.SITE_URL.'/order/'.str_replace(" ","-",$order['service']).'">'.SITE_URL.'/order/'.str_replace(" ","-",$order['service']).'</a>';
					break;
				default:
					$subject = "Order #".$order_id." (".$service.") update";
					$bodyMessage = "";
					break;
			}

			// CUSTOMIZE SUBJECT & BODY
			
			$body = '<table class="body-sub" role="presentation"><tr><td><p style="margin:0;" class="f-fallback sub">Hi '.$client_name.',';			
        	$body .= '<br><br>'.$bodyMessage;
        	$body .= '<br><br>Click here to view the order:<br><a href="'.$order_link.'">'.$order_link.'</a>';
        	$body .= '<br><br>For any questions please email us at info@creativebraindesign.com';
        	$body .= '<br><br>Your Creative Brain Design Team';
	    	$body .= '</p></td></tr></table>';
			//--- CUSTOMIZE SUBJECT & BODY
			
			$supplierAddress = 'app.creativebraindesign.com';
			$supplierName = 'Creative Brains for Design and Marketing';
			$supplierLogo = '<img src="https://app.creativebraindesign.com/assets/img/logo.png" alt="Creative Brains for Design and Marketing Logo" style="max-height:100%;max-width:100%;object-fit: contain;"/>';				

			$html = '<table id="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="padding-top:20px !important;background-color: #f2f4f6;">
			  <tr>
			    <td align="center">
			      <table class="email-content" width="100%" cellpadding="0" cellspacing="0" role="presentation">          
			        <tr>
			          <td class="email-body" style="max-width: 570px;" cellpadding="0" cellspacing="0">
			            <table class="email-body_inner" align="center" style="max-width: 570px;padding:10px 0;background-color:#fff;" cellpadding="0" cellspacing="0" role="presentation">
			              <tr>
			                <td>
			                  <div style="padding: 10px 30px 10px;">
			                  <div style="display: flex;justify-content: center;align-items: center;max-height: 125px;max-width: 200px;text-align: center;margin:20px auto;">'.$supplierLogo.'</div>
			                    '.$body.'<br>';
			                    $html .= '
			                  </div>
			                </td>
			              </tr>
			            </table>
			          </td>
			        </tr>
			        <tr>
			          <td>
			            <table style="margin: 20px auto;" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
			              <tr>
			                <td class="content-cell" align="center">
			                  <p style="color: #a8aaaf;font-size: 13px;max-width:300px;">
			                    '.$supplierName.'
			                    <br>'.$supplierAddress.'
			                  </p>
			                </td>
			              </tr>
			            </table>
			          </td>
			        </tr>
			      </table>
			    </td>
			  </tr>
			</table>';
			
			$col = [];
		    $col['address']['email'] = $user['email'];		    
		    $recipients[] = $col;		
		
			$css_styles = '<style type="text/css">@import url("https://fonts.googleapis.com/css?family=Nunito+Sans:400,700&display=swap"); body { margin: 0; } #email-wrapper { font-family: "Nunito Sans", Helvetica, Arial, sans-serif !important; }</style>';
			
		    $attachments = [];	        		    
			$emailArr = [];
			$emailArr['campaign_id'] = $order['uuid'].$status;
			$emailArr['recipients'] = $recipients;								
			
			$emailArr['content']['from']['email'] = "no-reply@mailer.creativebraindesign.com";			
			$emailArr['content']['from']['name'] = $supplierName;
			$emailArr['content']['subject'] = $subject;
			$emailArr['content']['html'] = $css_styles.$html;
			$emailArr['content']['text'] = strip_tags($html);					
						
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, 'https://api.sparkpost.com/api/v1/transmissions?num_rcpt_errors=3');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailArr));
			
			$headers = array();
			$headers[] = 'Content-Type: application/json';
			$headers[] = 'Accept: application/json';
			$headers[] = 'Authorization: '.SPARKPOST_API_KEY;
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

			$result = json_decode(curl_exec($ch));					
			if (curl_errno($ch)) {
			      echo 'Error:' . curl_error($ch);
			      return false;
			}
			
			if ($result) {
			    if (isset($result->errors)) {
			      	$return['status'] = false;
					$return['message'] = "Failed: ".$result->errors[0]->message;   					     
					return false;
			    } else {
			    	$return['status'] = true;
					$return['message'] = __("Email sent!",true);
					$return['sent_at'] = date(DATEFORMAT." H:i A");
					return true;
			    }    
			} else {
			    $return['status'] = false;
				$return['message'] = __("Failed to send email. Please contact support.",true);
				return false;
			}
			curl_close($ch);
		} else {
			return false;
		}
		exit;
	}

	function send_order_status_update_email_admin($user,$order,$status) {		
		if ($user!=null && $order!=null && $status!=null) {
			$UM = new UserModel();
			$service = str_replace("seo","SEO",$order['service']);
            $service = str_replace("pdp","PDP",$service);
            $service = ucwords($service);
			$client_name = ucwords($user['first_name']." ".$user['last_name']);
			$client_email = $user['email'];
			$order_link = SITE_URL."/order/".$order['uuid'];
			$order_id = str_pad($order['order_id'], 5, '0', STR_PAD_LEFT);		
			switch ($status) {
				case 'Awaiting Payment':
					$subject = "Client Order #".$order_id." (".$service.") received & awaiting payment";
					$bodyMessage = 'Client order #'.$order_id.' ('.$service.') has been received and saved.';					
					break;
				case 'Paid':
					$subject = "Client order #".$order_id." (".$service.") set as paid & received";
					$bodyMessage = 'Client payment for order #'.$order_id.' ('.$service.') has been confirmed and order details has been received.';
					break;
				case 'Client Approval Required':
					$subject = "Client order #".$order_id." (".$service.") sent for client approval";
					$bodyMessage = 'Client order #'.$order_id.' ('.$service.') has been checked and requires client approval before getting started.';
					break;
				case 'In Process':
					$subject = "Client order #".$order_id." (".$service.") confirmed and in process";
					$bodyMessage = 'Client order #'.$order_id.' ('.$service.') has been confirmed and currently in process.';
					break;
				case 'Completed':
					$subject = "Client order #".$order_id." (".$service.") completed";
					$bodyMessage = 'Client order #'.$order_id.' ('.$service.') is now completed.';
					break;
				default:
					$subject = "Client order #".$order_id." (".$service.") update";
					$bodyMessage = "";
					break;
			}

			// CUSTOMIZE SUBJECT & BODY
			$bodyMessage .= '<br>Client Name: '.$client_name.'<br>Client Email: '.$client_email;
			$body = '<table class="body-sub" role="presentation"><tr><td><p style="margin:0;" class="f-fallback sub">Hi Admin,';			
        	$body .= '<br><br>'.$bodyMessage;
        	$body .= '<br><br>Click here to view the order:<br><a href="'.$order_link.'">'.$order_link.'</a>';
        	$body .= '<br><br>Your Creative Brain Design Team';
	    	$body .= '</p></td></tr></table>';
			//--- CUSTOMIZE SUBJECT & BODY
			
			$supplierAddress = 'app.creativebraindesign.com';
			$supplierName = 'Creative Brains for Design and Marketing';
			$supplierLogo = '<img src="https://app.creativebraindesign.com/assets/img/logo.png" alt="Creative Brains for Design and Marketing Logo" style="max-height:100%;max-width:100%;object-fit: contain;"/>';				

			$html = '<table id="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="padding-top:20px !important;background-color: #f2f4f6;">
			  <tr>
			    <td align="center">
			      <table class="email-content" width="100%" cellpadding="0" cellspacing="0" role="presentation">          
			        <tr>
			          <td class="email-body" style="max-width: 570px;" cellpadding="0" cellspacing="0">
			            <table class="email-body_inner" align="center" style="max-width: 570px;padding:10px 0;background-color:#fff;" cellpadding="0" cellspacing="0" role="presentation">
			              <tr>
			                <td>
			                  <div style="padding: 10px 30px 10px;">
			                  <div style="display: flex;justify-content: center;align-items: center;max-height: 125px;max-width: 200px;text-align: center;margin:20px auto;">'.$supplierLogo.'</div>
			                    '.$body.'<br>';
			                    $html .= '
			                  </div>
			                </td>
			              </tr>
			            </table>
			          </td>
			        </tr>
			        <tr>
			          <td>
			            <table style="margin: 20px auto;" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
			              <tr>
			                <td class="content-cell" align="center">
			                  <p style="color: #a8aaaf;font-size: 13px;max-width:300px;">
			                    '.$supplierName.'
			                    <br>'.$supplierAddress.'
			                  </p>
			                </td>
			              </tr>
			            </table>
			          </td>
			        </tr>
			      </table>
			    </td>
			  </tr>
			</table>';
			
			$col = [];
		    
		    if (ENV=="PRODUCTION") {
		    	$col['address']['email'] = "info@creativebraindesign.com";
		    } else if (ENV=="DEVELOPMENT") {
		    	$col['address']['email'] = "neilangelodavid@gmail.com";			    
		    }
		    $recipients[] = $col;		
		
			$css_styles = '<style type="text/css">@import url("https://fonts.googleapis.com/css?family=Nunito+Sans:400,700&display=swap"); body { margin: 0; } #email-wrapper { font-family: "Nunito Sans", Helvetica, Arial, sans-serif !important; }</style>';
			
		    $attachments = [];	        		    
			$emailArr = [];
			$emailArr['campaign_id'] = $order['uuid'].$status;
			$emailArr['recipients'] = $recipients;								
			
			$emailArr['content']['from']['email'] = "no-reply@mailer.creativebraindesign.com";			
			$emailArr['content']['from']['name'] = $supplierName;
			$emailArr['content']['subject'] = $subject;
			$emailArr['content']['html'] = $css_styles.$html;
			$emailArr['content']['text'] = strip_tags($html);					
						
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, 'https://api.sparkpost.com/api/v1/transmissions?num_rcpt_errors=3');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailArr));
			
			$headers = array();
			$headers[] = 'Content-Type: application/json';
			$headers[] = 'Accept: application/json';
			$headers[] = 'Authorization: '.SPARKPOST_API_KEY;
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

			$result = json_decode(curl_exec($ch));					
			if (curl_errno($ch)) {
			      echo 'Error:' . curl_error($ch);
			      return false;
			}
			
			if ($result) {
			    if (isset($result->errors)) {
			      	$return['status'] = false;
					$return['message'] = "Failed: ".$result->errors[0]->message;   					     
					return false;
			    } else {
			    	$return['status'] = true;
					$return['message'] = __("Email sent!",true);
					$return['sent_at'] = date(DATEFORMAT." H:i A");
					return true;
			    }    
			} else {
			    $return['status'] = false;
				$return['message'] = __("Failed to send email. Please contact support.",true);
				return false;
			}
			curl_close($ch);
		} else {
			return false;
		}
		exit;
	}

	function send_coupon_email($coupon,$user) {
		if ($user!=null && $coupon!=null) {
			$UM = new UserModel();
			$client_name = ucwords($user['first_name']." ".$user['last_name']);
			$client_email = $user['email'];
			$subject = " Your Exclusive Client Discount Inside!";
			$bodyMessage = "Hi ".$client_name.",<br><br>";
			$bodyMessage .= "Great news! As a token of appreciation, here's an exclusive coupon code just for you:<br><br>";
			$bodyMessage .= "Use Code: <b style='color:#172c5c;font-size:16px;'>".$coupon['coupon_code']."</b><br><br>";
			if ($coupon['coupon_type']=="percentage") {
				$coupon_discount = "<b style='color:#172c5c;font-size:14px;'>".$coupon['coupon_value']."% Off</b>";
			} else {
				$coupon_discount = "<b style='color:#172c5c;font-size:14px;'>$".number_format($coupon['coupon_value'],2,".",",")." Off</b>";
			}
			
			$bodyMessage .= "Ready to order? Simply enter the code at checkout on our website<br> to enjoy ".$coupon_discount." on your purchase.<br><br>";
			$bodyMessage .= "Thanks for being a fantastic client! 🌟<br><br>";
			$bodyMessage .= "Your Creative Brain Design Team";

			// CUSTOMIZE SUBJECT & BODY
			$body = '<table class="body-sub" role="presentation"><tr><td><p style="margin:0;" class="f-fallback sub">';			
        	$body .= $bodyMessage;
	    	$body .= '</p></td></tr></table>';
			//--- CUSTOMIZE SUBJECT & BODY
			
			$supplierAddress = 'app.creativebraindesign.com';
			$supplierName = 'Creative Brains for Design and Marketing';
			$supplierLogo = '<img src="https://app.creativebraindesign.com/assets/img/logo.png" alt="Creative Brains for Design and Marketing Logo" style="max-height:100%;max-width:100%;object-fit: contain;"/>';				

			$html = '<table id="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="padding-top:20px !important;background-color: #f2f4f6;">
			  <tr>
			    <td align="center">
			      <table class="email-content" width="100%" cellpadding="0" cellspacing="0" role="presentation">          
			        <tr>
			          <td class="email-body" style="max-width: 570px;" cellpadding="0" cellspacing="0">
			            <table class="email-body_inner" align="center" style="max-width: 570px;padding:10px 0;background-color:#fff;" cellpadding="0" cellspacing="0" role="presentation">
			              <tr>
			                <td>
			                  <div style="padding: 10px 30px 10px;">
			                  <div style="display: flex;justify-content: center;align-items: center;max-height: 125px;max-width: 200px;text-align: center;margin:20px auto;">'.$supplierLogo.'</div>
			                    '.$body.'<br>';
			                    $html .= '
			                  </div>
			                </td>
			              </tr>
			            </table>
			          </td>
			        </tr>
			        <tr>
			          <td>
			            <table style="margin: 20px auto;" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
			              <tr>
			                <td class="content-cell" align="center">
			                  <p style="color: #a8aaaf;font-size: 13px;max-width:300px;">
			                    '.$supplierName.'
			                    <br>'.$supplierAddress.'
			                  </p>
			                </td>
			              </tr>
			            </table>
			          </td>
			        </tr>
			      </table>
			    </td>
			  </tr>
			</table>';
			
			$col = [];
		    $col['address']['email'] = $client_email;
		    $recipients[] = $col;		
		
			$css_styles = '<style type="text/css">@import url("https://fonts.googleapis.com/css?family=Nunito+Sans:400,700&display=swap"); body { margin: 0; } #email-wrapper { font-family: "Nunito Sans", Helvetica, Arial, sans-serif !important; }</style>';
			
		    $attachments = [];	        		    
			$emailArr = [];
			$emailArr['campaign_id'] = $coupon['uuid'];
			$emailArr['recipients'] = $recipients;								
			
			$emailArr['content']['from']['email'] = "no-reply@mailer.creativebraindesign.com";			
			$emailArr['content']['from']['name'] = $supplierName;
			$emailArr['content']['subject'] = $subject;
			$emailArr['content']['html'] = $css_styles.$html;
			$emailArr['content']['text'] = strip_tags($html);					
						
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, 'https://api.sparkpost.com/api/v1/transmissions?num_rcpt_errors=3');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailArr));
			
			$headers = array();
			$headers[] = 'Content-Type: application/json';
			$headers[] = 'Accept: application/json';
			$headers[] = 'Authorization: '.SPARKPOST_API_KEY;
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

			$result = json_decode(curl_exec($ch));					
			if (curl_errno($ch)) {
			      echo 'Error:' . curl_error($ch);
			      return false;
			}
			
			if ($result) {
			    if (isset($result->errors)) {
			      	$return['status'] = false;
					$return['message'] = "Failed: ".$result->errors[0]->message;   					     
					return false;
			    } else {
			    	$return['status'] = true;
					$return['message'] = __("Email sent!",true);
					$return['sent_at'] = date(DATEFORMAT." H:i A");
					return true;
			    }    
			} else {
			    $return['status'] = false;
				$return['message'] = __("Failed to send email. Please contact support.",true);
				return false;
			}
			curl_close($ch);
		} else {
			return false;
		}
		exit;	
	}

	function fetch_invoices() {
		if(isset($_POST['token']))
		{
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			$UM = new UserModel;			
			if ($tokenvalue->role=="admin") {
				$invoices = $UM->fetchInvoices();	
			} else {
				$invoices = $UM->fetchInvoices($tokenvalue->uuid);
			}
			
			$datatable = [];
			foreach ($invoices as $key => $value) {
				$col = [];
				$col[] = '<a href="/invoice/'.$value['uuid'].'" target="_blank" class="me-1"><i class="bx bx-receipt"></i></a><a href="/i/'.$value['uuid'].'?download=1" target="_blank"><i class="bx bxs-download"></i></a>';
				$col[] = '<b><a href="/order/'.$value['uuid'].'" target="_blank">'.str_pad($value['order_id'], 5, '0', STR_PAD_LEFT).'</a>';
				if ($tokenvalue->role!="client") {
					$users = $UM->getGlobalbyId("users",$value['user_id']);
					$col[] = ucwords($users['first_name']." ".$users['last_name'])."<br>".$users['email']."";
				}
				$service = str_replace("pdp","PDP",$value['service']);
				$service = str_replace("seo","SEO",$service);
				$service = ucwords($service);
				$col[] = date("d/m/Y",strtotime($value['date_ordered']));
				$col[] = $service;
				if ($value['paid_with']!=null && $value['paid_with']!='') {
					$payment_method = $value['paid_with'];
				} else {
					if ($value['payment_method']=="pay_later") {
						$payment_method = '<span class="badge bg-danger">Awaiting Payment</span>';
					} else {
						$payment_method = $value['payment_method'];		
					}
				}
				$col[] = ucwords(str_replace("_"," ",$payment_method));
				
				if ($value['line_items']!=null && $value['line_items']!="") {
					$total_amount = calculate_subtotal($value['service'],json_decode($value['line_items'],true)) - $value['coupon_discount'];
				} else {
					//$total_amount = calculate_subtotal($value['service'],$value['package']) - $value['coupon_discount'];
					$total_amount = $value['amount'];
				}
				
				$col[] = number_format($total_amount,2,".",",");
												
				$datatable[] = $col;
			}
			$return['status'] = true;
			$return['datatable'] = $datatable;
			
			echo json_encode($return);
		}
		exit;
	}

	function generate_razorpay_id() {		
		if(isset($_POST['token']) && isset($_POST['data']))
		{			
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));						
			parse_str($_POST['data'],$postdata);
			$orderData = [
			    'receipt'         => uuidv4(),
			    'amount'          => $_POST['amount'],
			    'currency'        => 'USD',
			    'payment_capture' => 1 // Auto capture
			];

			try {
				$api = new Api(RAZORPAY_API_KEY, RAZORPAY_CLIENT_SECRET);
				$razorpayOrder = $api->order->create($orderData);	
				if ($razorpayOrder->status=="created") {
					$return['status'] = true;
					$return['razorpay_id'] = $razorpayOrder->id;
				} else {
					$return['status'] = false;
					$return['message'] = "Sorry for the inconvenience. Razorpay is not available as of the moment. Please try again later or use another payment method.";
				}
			} catch (Exception $e) {
				$return['status'] = false;
				$return['message'] = "Sorry for the inconvenience. Razorpay is not available as of the moment. Please try again later or use another payment method.";
				$return['message'] = $e->getMessage();
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("Missing Token",true);
		}
		echo json_encode($return);
		exit;
	}

	function test_razorpay_id() {		
		$api = new Api(RAZORPAY_API_KEY, RAZORPAY_CLIENT_SECRET);
		$orderData = [
		    'receipt'         => uuidv4(),
		    'amount'          => 10*100,
		    'currency'        => 'USD',
		    'payment_capture' => 1 // Auto capture
		];

		try {
			$razorpayOrder = $api->order->create($orderData);	
			if ($razorpayOrder->status=="created") {
				$return['status'] = true;
				$return['razorpay_id'] = $razorpayOrder->id;
			} else {
				$return['status'] = false;
				$return['message'] = "Sorry for the inconvenience. Razorpay is not available as of the moment. Please try again later or use another payment method.";
			}
		} catch (Exception $e) {
			$return['status'] = false;
			$return['message'] = "Sorry for the inconvenience. Razorpay is not available as of the moment. Please try again later or use another payment method.";
		}
		echo json_encode($return);
		exit;
	}

	function fetch_ticket_thread(){
		if(isset($_POST['token'])){	
			$UM = new UserModel;
			$result = $UM->getGlobalbySpecificField($_POST['t'], 'ticket_id', $_POST['ticket_id']);
			
			$return['status'] = true;
			$return['data'] = $result;
			$return['ticket_id'] = $_POST['ticket_id'];
		}
		
		echo json_encode($return);
		exit;
	}

	function fetch_data_by_id(){
		if(isset($_POST['token'])){
			$UM = new UserModel;
			$result = $UM->getRowByField($_POST['t'], 'id', $_POST['id']);

			$return['status'] = true;
			$return['data'] = $result;
		}

		echo json_encode($return);
		exit;
	}
    
	function fetch_licenses() {
		if(isset($_POST['token'])){	
			$UM = new UserModel;
			$result = $UM->fetchLicenses();
			$datatable = [];
			foreach ($result as $key => $value) {
				$col = [];
				$col[] = $value['order_sort'];
				$col[] = $value['label'];
				$col[] = $value['description'];
				$col[] = "$".number_format($value['price'],2,".",",");
				$col[] = ucwords($value['integration']);
				if ($value['is_enabled']=="1") {
					$col[] = '<label class="badge bg-label-success">Enabled</label>';
				} else {
					$col[] = '<label class="badge bg-label-dark">Disabled</label>';
				}
								
				$col[] = '<i class="bx bx-edit openBoxBtn pointer" data-id="'.$value['id'].'"></i>';
				$datatable[] = $col;
			}
			$return['status'] = true;
			$return['datatable'] = $datatable;
		} else {
			$return['status'] = false;
		}
		
		echo json_encode($return);
		exit;
	}
?>