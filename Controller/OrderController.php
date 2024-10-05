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
		$data['title'] = "Orders";
		$data['path'] = "account/orders";
		$data['menu-link'] = "orders";
		$data['menu-title'] = '<i class="menu-icon tf-icons bx bxs-package"></i> Orders';
		return $data;
	}	
?>