<?php 
include('Model/Model.php');
include('Model/UserModel.php');
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
	
	function clean($string) {   
        $string = str_replace('"', "&quot;", $string);
        $string = str_replace("'", "&apos;", $string);
        return $string;
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

	function index() {
		header("Location: /account");
		exit;
	}	
	

	function manage_merchants() {
		if ($_SESSION['role']!="admin") {
			header("Location: /");
			exit;
		}
		$UM = new UserModel;
		$data['merchants'] = $UM->fetchMerchants();		
		$data['title'] = "Manage Merchants";
		$data['path'] = "admin/manage-clients";
		$data['menu-link'] = "merchants";
		$data['menu-title'] = '<i class="menu-icon tf-icons bx bxs-user-account"></i> Manage Merchants';
		
		return $data;
	}

	function manage_clients() {
		$access = $_SESSION['sidebar_menu_access'];
		if(!array_search('Manage Merchants', array_column($access, 'module_name'))){
			header("Location: /");
			exit;
		}

		$UM = new UserModel;
		$data['roleaccess'] = $UM->fetchUsersRoleAccess($_SESSION['role_id'], "Manage Clients");
		$data['title'] = "Manage Merchants";
		$data['path'] = "admin/manage-clients";
		$data['menu-link'] = "manage clients";
		
		return $data;
	}

	function manage_coupons() {
		$access = $_SESSION['sidebar_menu_access'];
		if(!array_search('Manage Cuopons', array_column($access, 'module_name'))){
			header("Location: /");
			exit;
		}
		$UM = new UserModel;
		$data['clients'] = $UM->getUserbyRole('client');
		$data['title'] = "Manage Coupons";
		$data['path'] = "admin/manage-coupons";
		$data['menu-link'] = "manage coupons";
		
		return $data;
	}

	function manage_licenses(){
		if ($_SESSION['role']!="admin") {
			header("Location: /");
			exit;
		}
		$UM = new UserModel;		
		$data['title'] = "Manage Licenses";
		$data['path'] = "admin/manage-licenses";
		$data['menu-link'] = "licenses";
		$data['menu-title'] = '<i class="menu-icon tf-icons bx bx-sidebar"></i> Manage Licenses';
		return $data;
	}

	function manage_modules(){
		//checking will be different when role acess will be implemented
		$access = $_SESSION['sidebar_menu_access'];
		if(!array_search('Manage Modules', array_column($access, 'module_name'))){
			header("Location: /");
			exit;
		}

		$UM = new UserModel;
		$data['roleaccess'] = $UM->fetchUsersRoleAccess($_SESSION['role_id'], "Manage Modules");
		$data['title'] = "Manage Modules";
		$data['path'] = "admin/manage-modules";
		$data['menu-link'] = "manage modules";
		
		return $data;
	}

	function manage_hints() {
		$access = $_SESSION['sidebar_menu_access'];
		if(!array_search('Hints', array_column($access, 'module_name'))){
			header("Location: /");
			exit;
		}
		
		$UM = new UserModel;
		$data['roleaccess'] = $UM->fetchUsersRoleAccess($_SESSION['role_id'], "Manage Roles");
		$data['title'] = "Hints";
		$data['path'] = "roles/manage-roles";
		$data['menu-link'] = "manage roles";
		
		return $data;
	}

	function affiliates() {
		$access = $_SESSION['sidebar_menu_access'];
		if(!array_search('Affiliate Group Details', array_column($access, 'module_name'))){
			header("Location: /");
			exit;
		}
		
		$UM = new UserModel;
		$data['roleaccess'] = $UM->fetchUsersRoleAccess($_SESSION['role_id'], "Manage Users");
		$data['title'] = "Affiliates";
		$data['path'] = "admin/manage-users";
		$data['menu-link'] = "manage users";
		
		return $data;
	}

	function manage_users() {
		$access = $_SESSION['sidebar_menu_access'];
		if(!array_search('Manage Users', array_column($access, 'module_name'))){
			header("Location: /");
			exit;
		}
		
		$UM = new UserModel;
		$data['roleaccess'] = $UM->fetchUsersRoleAccess($_SESSION['role_id'], "Manage Users");
		$data['title'] = "Manage Users";
		$data['path'] = "admin/manage-users";
		$data['menu-link'] = "manage users";
		
		return $data;
	}
	
	function customers() {		
		$UM = new UserModel;
		$data['title'] = "Customers";
		$data['path'] = "admin/customers";
		$data['menu-link'] = "customers";
		$data['menu-title'] = '<i class="menu-icon tf-icons bx bxs-user-account"></i> Manage Customers';
		$data['customers'] = $UM->fetchCustomers();
		return $data;
	}
	

	function manage_services() {
		$access = $_SESSION['sidebar_menu_access'];
		if(!array_search('Manage Services', array_column($access, 'module_name'))){
			header("Location: /");
			exit;
		}

		$UM = new UserModel;
		$data['roleaccess'] = $UM->fetchUsersRoleAccess($_SESSION['role_id'], "Manage Services");
		$data['title'] = "Manage Services";
		$data['path'] = "admin/manage-services";
		$data['menu-link'] = "manage services";
		
		return $data;
	}

	function logs() {
		$data['title'] = "Logs";
		$data['path'] = "admin/logs";
		$data['menu-link'] = "logs";
		$data['upload'] = array_reverse(explode("\n",file_get_contents("./logs/prospect-upload.log")));
		$data['download'] = array_reverse(explode("\n",file_get_contents("./logs/prospect-download.log")));
		return $data;
	}
?>
