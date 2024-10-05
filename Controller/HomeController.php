<?php 	
include('Model/Model.php');
include('Model/UserModel.php');

	function index() {			
		Header("Location: /account/");			
		return $data;		
	}

	function maintenance() {
		$data['title'] = "Maintenance";
		$data['path'] = "includes/maintenance";
		return $data;
	}
?>