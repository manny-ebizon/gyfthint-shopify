<?php 
$fn = "index";
$path;

function getUriSegments() {
	return explode("/", parse_url(str_replace("-", "_", str_replace("//", "/", $_SERVER['REQUEST_URI'])), PHP_URL_PATH));
}

function getUriSegment($n) {		
	$segs = getUriSegments();
	if ($n < count($segs)) {
		return count($segs)>0&&count($segs)>=($n-1)?$segs[$n]:'';
	} else {
		return '';
	}
	
}
function curl_process_email_queue() {
  $c = curl_init();
  curl_setopt($c, CURLOPT_URL, SITE_URL."/send-queued-emails/");
  curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);  // Follow the redirects (needed for mod_rewrite)
  curl_setopt($c, CURLOPT_HEADER, false);         // Don't retrieve headers
  curl_setopt($c, CURLOPT_NOBODY, true);          // Don't retrieve the body
  curl_setopt($c, CURLOPT_RETURNTRANSFER, true);  // Return from curl_exec rather than echoing
  curl_setopt($c, CURLOPT_FRESH_CONNECT, true);   // Always ensure the connection is fresh

  // Timeout super fast once connected, so it goes into async.
  curl_setopt( $c, CURLOPT_TIMEOUT, 1 );
  return curl_exec( $c );
}
if(getUriSegment(1)!="")
{
	if(count(getUriSegments())>=3)
	{
		$fn = getUriSegment(2);		
		if($fn=="" || $fn == NULL)
			$fn = "index";
	}	
}

switch(getUriSegment(1))
{
	case "account":		
		if (!isset($_SESSION['token'])) {
			header("Location: /login/");
			exit;
		}
		$controller = "AccountController";
		break;
	case "orders":		
		if (!isset($_SESSION['token'])) {
			header("Location: /login/");
			exit;
		}
		$controller = "OrderController";
		break;
	case "admin":
		/*	
		This is handles in the manage views
		if ($_SESSION['role']!="admin") {
			header("Location: /login/");
			exit;
		}
		*/
		$controller = "AdminController";
		break;	
	case "api":	
		$controller = "ApiController";
		break;	
	case "login":						
		$controller = "LoginController";
		break;
	case "i":
		$fn = "index";
		$controller = "InvoiceController";
		break;	
	case "reset_password":
		$fn = "reset_password";
		$controller = "LoginController";
		break;
	case "forgot_password":
		$fn = "forgot_password";
		$controller = "LoginController";
		break;
	case "merchant_onboarding":
		$fn = "merchant_onboarding";
		$controller = "LoginController";
		break;	
	case "sign_up":
		$fn = "sign_up";
		$controller = "LoginController";
		break;	
	case "logout":
		$fn = "logout";
		$controller = "LoginController";
		break;	
	case "maintenance":
		$controller = "HomeController";
		break;	
	case "send_queued_emails":
		$controller = "EmailController";		
		break;
	case "roles":
		$controller = "RoleController";
		break;
	case "sites":
		$controller = "SiteController";
		break;
	case "ticketing":
		$controller = "TicketingController";
		break;	
	default:	
		if(getUriSegment(1)!="")
			$fn=getUriSegment(1);
					
		if (isset($_SESSION['token'])===false) {			
			header("Location: /login/");		
			exit;	
		}
		
		$controller = "AccountController";
		break;
}
include('Controller/'.$controller.'.php');
include('Controller/Controller.php');
?>