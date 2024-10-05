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
		header("Location: /sites");
		exit;
	}	

	function curated_hints() {
		$access = $_SESSION['sidebar_menu_access'];
		if(!array_search('Curated Hints', array_column($access, 'module_name'))){
			header("Location: /");
			exit;
		}

		$UM = new UserModel;
		$data['roleaccess'] = $UM->fetchUsersRoleAccess($_SESSION['role_id'], "Manage Sites");
		$data['title'] = "Curated Hints";
		$data['path'] = "sites/manage-sites";
		$data['menu-link'] = "manage sites";
		
		return $data;
	}

	function manage_sites() {
		$access = $_SESSION['sidebar_menu_access'];
		if(!array_search('Manage Sites', array_column($access, 'module_name'))){
			header("Location: /");
			exit;
		}

		$UM = new UserModel;
		$data['roleaccess'] = $UM->fetchUsersRoleAccess($_SESSION['role_id'], "Manage Sites");
		$data['title'] = "Manage Sites";
		$data['path'] = "sites/manage-sites";
		$data['menu-link'] = "manage sites";
		
		return $data;
	}

	function settings() {
		$access = $_SESSION['sidebar_menu_access'];
		if(!array_search('Site Settings', array_column($access, 'module_name'))){
			header("Location: /");
			exit;
		}

		$UM = new UserModel;
		$data['roleaccess'] = $UM->fetchUsersRoleAccess($_SESSION['role_id'], "Site Settings");
		$data['title'] = "Sites Settings";
		$data['path'] = "sites/site-settings";
		$data['menu-link'] = "sites settings";
		
		return $data;
	}
    
?>
