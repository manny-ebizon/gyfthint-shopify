<?php 
include('Model/Model.php');
include('Model/UserModel.php');
// Include autoloader 
require_once 'dompdf/autoload.inc.php'; 
 
// Reference the Dompdf namespace 
use Dompdf\Dompdf; 
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


	function index() {
		header("Location: /dashboard");
		exit;
	}	

	//////	
	function invoice() {
		if (getUriSegment(2)!="") {
			$UM = new UserModel;
			$order_id = str_replace("_","-",getUriSegment(2));
			$order = $UM->fetchOrderById($order_id);
			$data['title'] = "View Invoice ".str_pad($order['order_id'], 5, '0', STR_PAD_LEFT);
			$data['path'] = "account/invoice-detail";
			$data['menu-link'] = "view invoices";
			$data['order_details'] = $order;
			$data['order_details']['order_id'] = str_pad($order['order_id'], 5, '0', STR_PAD_LEFT);				
			$data['client_details'] = $UM->getGlobalbyId("users",$order['user_id']);				
			return $data;					
		} else {
			header("Location: /");
		}
		exit;
	}
	function view_invoices() {
		$data['title'] = "View Invoices";
		$data['path'] = "account/invoices";
		$data['menu-link'] = "view invoices";
		
		return $data;
	}

	function profile() {				
		$UM = new UserModel;
		$data['title'] = "Profile";
		$data['path'] = "account/profile";
		$data['menu-link'] = "profile";
		$data['menu-title'] = '<i class="menu-icon tf-icons bx bxs-user"></i> Profile Settings';
		return $data;
	}
	
	function suggested_hints() {		
		$UM = new UserModel;
		$data['title'] = "Suggested Hints";
		$data['path'] = "account/suggested-hints";
		$data['menu-link'] = "suggested hints";
		$data['menu-title'] = '<i class="menu-icon tf-icons bx bx-link"></i> Suggested Hints';
		$payload = JWT::decode($_SESSION['token'], new Key(KEY, 'HS256'));		
		$data['stores'] = $UM->fetchStoresByUserId($payload->id);
		$last_store_id = false;
		foreach ($data['stores'] as $key => $value) {
			$last_store_id = $value['id'];
		}		
		$data['license'] = $UM->fetchStoresLicenseByStoreId($last_store_id);
		$data['suggested_hints'] = $UM->fetchSuggestedHintsByStoreID($data['stores'][0]['id']);		
		return $data;
	}

	function curated_hints() {		
		$UM = new UserModel;
		$data['title'] = "Curated Hints";
		$data['path'] = "account/curated-hints";
		$data['menu-link'] = "curated hints";
		$data['menu-title'] = '<i class="menu-icon tf-icons bx bxs-collection"></i> Curated Hints';
		$data['hints'] = $UM->fetchHints();
		return $data;
	}

	function gyfthint_value() {
		$data['title'] = "Gyfthint Value";
		$data['path'] = "admin/gyfthint-value";
		$data['menu-link'] = "gyfthint value";		
		$data['menu-title'] = '<i class="menu-icon tf-icons bx bxs-gift"></i> Gyfthint Value';
		return $data;
	}

	function analytics() {
		$data['title'] = "Analytics";
		$data['path'] = "admin/analytics";
		$data['menu-link'] = "analytics";		
		$data['menu-title'] = '<i class="menu-icon tf-icons bx bxs-bar-chart-square"></i> Analytics';
		return $data;
	}

	function promotions() {
		$data['title'] = "Promotions";
		$data['path'] = "admin/promotions";
		$data['menu-link'] = "promotions";
		$data['menu-title'] = '<i class="menu-icon tf-icons bx bxs-megaphone"></i> Promotions';
		$UM = new UserModel;		
		$data['hints'] = $UM->fetchHints();		
		return $data;
	}

	function dashboard() {
		if (isset($_SESSION['role']) && $_SESSION['role']=="admin") {
			header("Location: /admin/manage-merchants");
			exit;
		}
		$data['title'] = "Dashboard";
		$data['path'] = "account/dashboard";
		$data['menu-link'] = "dashboard";
		$data['menu-title'] = '<i class="menu-icon tf-icons bx bxs-dashboard"></i> Dashboard';
		return $data;
	}

	function changepass() {
		if(isset($_POST['data']))
		{
			parse_str($_POST['data'],$postdata);
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));
			if ($tokenvalue) {
				$id = $tokenvalue->uuid;				
				$UM = new UserModel;
				$result = $UM->changePassword($id,$postdata);				
				if($result)
				{					
					$return['status'] = true;
				}
				else
				{
					$return['message'] = "Incorrect old password. Please try again.";
					$return['status'] = false;
				}
			} else {
				$return['status'] = false;	
				$return['message'] = "Invalid Token. Please re-log in or contact support.";			
			}
			
			echo json_encode($return);
			exit;
		}		
	}
	

?>