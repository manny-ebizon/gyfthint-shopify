<?php 
include('Model/Model.php');
include('Model/UserModel.php');
// Include autoloader 
require_once 'dompdf/autoload.inc.php'; 
 
// Reference the Dompdf namespace 
use Dompdf\Dompdf; 
	
	function index() {
		if (getUriSegment(2)!="") {
			$UM = new UserModel;
			$order_id = str_replace("_","-",getUriSegment(2));
			$order = $UM->fetchOrderById($order_id);
			if (isset($_GET['download'])) {
				// Initialize DOMPDF
				$dompdf = new Dompdf();
				$options = $dompdf->getOptions(); 
			    $options->set(array('isRemoteEnabled' => true));
			    $dompdf->setOptions($options);

				// Load HTML content
				$html = file_get_contents(SITE_URL."/i/".$order['uuid']."?downloadview=1");				
				$dompdf->loadHtml($html);

				// (Optional) Setup the paper size and orientation
				$dompdf->setPaper('A4', 'portrait');

				// Render the HTML as PDF
				$dompdf->render();
				// Output the generated PDF to Browser
				$dompdf->stream("invoice-".str_pad($order['order_id'], 5, '0', STR_PAD_LEFT).".pdf", array("Attachment" => true));
			} else {
				$data['title'] = "View Invoice ".str_pad($order['order_id'], 5, '0', STR_PAD_LEFT);
				$data['path'] = "account/invoice-detail";
				$data['menu-link'] = "view invoices";
				$data['order_details'] = $order;
				$data['order_details']['order_id'] = str_pad($order['order_id'], 5, '0', STR_PAD_LEFT);				
				$data['client_details'] = $UM->getGlobalbyId("users",$order['user_id']);				
				return $data;
			}						
		} else {
			header("Location: /");
		}
		exit;
	}
	

?>