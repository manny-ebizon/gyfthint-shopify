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
		header("Location: /roles");
		exit;
	}	

	function manage_roles() {
		$access = $_SESSION['sidebar_menu_access'];
		if(!array_search('Manage Roles', array_column($access, 'module_name'))){
			header("Location: /");
			exit;
		}
		
		$UM = new UserModel;
		$data['roleaccess'] = $UM->fetchUsersRoleAccess($_SESSION['role_id'], "Manage Roles");
		$data['title'] = "Manage Roles";
		$data['path'] = "roles/manage-roles";
		$data['menu-link'] = "manage roles";
		
		return $data;
	}
    
?>
