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

	function raised_tickets() {
		$access = isset($_SESSION['sidebar_menu_access'])?$_SESSION['sidebar_menu_access']:[];
		if(!array_search('Manage Tickets', array_column($access, 'module_name'))){
			header("Location: /");
			exit;
		}
		
		$UM = new UserModel;
		$data['roleaccess'] = $UM->fetchUsersRoleAccess($_SESSION['role_id'], "Manage Tickets");
		$data['title'] = "Raised Tickets";
		$data['path'] = "tickets/raised-tickets";
		$data['menu-link'] = "raised tickets";
		
		return $data;
	}

	function active_tickets() {
		$access = isset($_SESSION['sidebar_menu_access'])?$_SESSION['sidebar_menu_access']:[];
		if(!array_search('Manage Tickets', array_column($access, 'module_name'))){
			header("Location: /");
			exit;
		}
		
		$UM = new UserModel;
		$data['roleaccess'] = $UM->fetchUsersRoleAccess($_SESSION['role_id'], "Manage Tickets");
		$data['title'] = "Active Tickets";
		$data['path'] = "tickets/active-tickets";
		$data['menu-link'] = "active tickets";
		
		return $data;
	}

	function for_review_tickets() {
		$access = isset($_SESSION['sidebar_menu_access'])?$_SESSION['sidebar_menu_access']:[];
		if(!array_search('Manage Tickets', array_column($access, 'module_name'))){
			header("Location: /");
			exit;
		}
		
		$UM = new UserModel;
		$data['roleaccess'] = $UM->fetchUsersRoleAccess($_SESSION['role_id'], "For Review Tickets");
		$data['title'] = "For Review Tickets";
		$data['path'] = "tickets/for-review-tickets";
		$data['menu-link'] = "for review tickets";
		
		return $data;
	}

	function completed_tickets() {
		$access = isset($_SESSION['sidebar_menu_access'])?$_SESSION['sidebar_menu_access']:[];
		if(!array_search('Manage Tickets', array_column($access, 'module_name'))){
			header("Location: /");
			exit;
		}
		
		$UM = new UserModel;
		$data['roleaccess'] = $UM->fetchUsersRoleAccess($_SESSION['role_id'], "Completed Tickets");
		$data['title'] = "Completed Tickets";
		$data['path'] = "tickets/completed-tickets";
		$data['menu-link'] = "completed tickets";
		
		return $data;
	}

	function fetch_tickets(){
		if(isset($_POST['token'])){
			$filter = [];

			$whereIn = [];
			if(isset($_POST['status']) && $_POST['status'] == 'ongoing'){
				$whereIn['t.status'] = array('assigned', 'working');
			}else{
				if($_SESSION['tier'] == 1 && $_POST['status']=='internal review'){
					$whereIn['t.status'] = array('client review');
				} else {
					$whereIn['t.status'] = array($_POST['status']);
				}
			}
		
			$UM = new UserModel;
			$result = $UM->getGlobal('tickets',null,null,$filter, $whereIn);
			
			if(count($result)>0){
				foreach ($result as $key => $value) {
					$col = [];
					$col[] = '<a class="pointer openBoxBtn" data-id="'.$value['uuid'].'"><b> #'.str_pad($value['id'], 5, '0', STR_PAD_LEFT).'</b></a>';
					$col[] = '<span class="ellipsis">'.$value['header'].'</span>';
					$col[] = '<span class="ellipsis">'.$value['body'].'</span>';
					$col[] = $value['status'];
					$col[] = ucwords($value['first_name']." ".$value['last_name']);
					if($_POST['status'] == 'completed'){
						$col[] = '<a class="pointer openBoxBtn" data-id="'.$value['uuid'].'"><i class="bx bx-edit-alt me-1"></i></a>';
					}
					$datatable[] = $col;
				}
				$return['status'] = true;
				$return['datatable'] = $datatable;	
			} else {
				$return['status'] = true;
				$return['datatable'] = [];	
			}
		}
		echo json_encode($return);
		exit;
	}

	function fetch_ticketing_user(){
		if(isset($_POST['token'])){
			
			$UM = new UserModel;
			$filter = [];
			$filter['su.site_id'] = $_POST['site_id'];
			$filter['r.tier'] = $_POST['tier'];
			$result = $UM->getGlobal('ticketing_user',null,null,$filter);

			$return['status'] = true;
			$return['data'] = $result;
		} else {
			$return['status'] = false;
			$return['message'] = "Invalid Token. Please re-log in or contact support.";
		}
		
		echo json_encode($return);
		exit;
	}

	function update_assignee(){
		if(isset($_POST['token']) && isset($_POST['data'])){
			$UM = new UserModel;

			//Update assigned to using user UUID - changed status to 'Active/For Review/Completed'
			parse_str($_POST['data'], $postdata);
			$ticketData = [];
			$ticketData['uuid'] = $postdata['ticket_id'];
			$ticketData['status'] = $_POST['status'];
			$ticketData['assigned_to'] = $postdata['assigned_to']; 
			$result = $UM->updateGlobal('tickets', $postdata['ticket_id'], $ticketData);

			//Insert/values values to ticket_management_relation
			$filter['ticket_id'] = $postdata['ticket_id'];
			$filter['user_id']  = $postdata['assigned_to'];
			$check_if_exisit = $UM->getGlobal('ticket_management_relation', null, null, $filter);
			
			if(count($check_if_exisit) > 0){
				//Update ticket management relation
			} else {
				//Insert to to ticket management relation
				$ticket_management_data = [];
				$ticket_management_data['uuid'] = uuidv4();
				$ticket_management_data['ticket_id'] = $postdata['ticket_id'];
				$ticket_management_data['user_id'] = $postdata['assigned_to'];
				$ticket_management_data['tier_type'] = $postdata['tier_type'];
				
				$result=$UM->addGlobal("ticket_management_relation", $ticket_management_data);
			}
			$return['status'] = true;
			$return['message'] = "Successfully updated data";
		} else {
			$return['status'] = false;
			$return['message'] = "Failed to update data";
		}
			
		echo json_encode($return);
		exit;
	}

	function update_ticket_status(){
		if(isset($_POST['token']) && isset($_POST['status'])){
			$UM = new UserModel;
			$ticket_id = $_POST['ticket_id'];

			$ticket_management_data = [];
			if($_SESSION['tier'] == 2 && $_POST['status']=='internal review'){
				$ticket_management_data['status'] = 'client review';
			}else{
				$ticket_management_data['status'] = $_POST['status'];
			}
			$UM->updateGlobal($_POST['t'], $ticket_id, $ticket_management_data);

			$return['status'] = true;
			$return['message'] = "Successfully updated data";
		} else {
			$return['status'] = false;
			$return['message'] = "Failed to update data";
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
?>
