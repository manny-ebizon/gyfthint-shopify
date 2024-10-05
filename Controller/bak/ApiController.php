<?php 
include('Model/Model.php');
include('Model/UserModel.php');
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;
use chillerlan\QRCode\QRCode;

	function test() {
		$data = '{"messageId": "c55bc0f3-0771-5a2a-8a97-9c27b079dd83", "topic": "Financas.ContaReceber.Alterado", "event": {"baixa_bloqueada": "N", "bloqueado": "N", "boleto_gerado": "", "chave_nfe": "", "codigo_categoria": "1.01.02", "codigo_cliente_fornecedor": 7560605653, "codigo_cmc7_cheque": "", "codigo_lancamento_integracao": "", "codigo_lancamento_omie": 7560614916, "codigo_projeto": 0, "codigo_tipo_documento": "99999", "codigo_vendedor": 0, "data_emissao": "2023-05-08T00:00:00-03:00", "data_previsao": "2023-05-31T00:00:00-03:00", "data_registro": "2023-05-08T00:00:00-03:00", "data_vencimento": "2023-05-31T00:00:00-03:00", "id_conta_corrente": 7560605644, "id_origem": "MANR", "nsu": "", "numero_documento": "", "numero_documento_fiscal": "", "numero_parcela": "001/001", "numero_pedido": "", "observacao": "", "operacao": "", "pix_gerado": "", "retem_cofins": "", "retem_csll": "", "retem_inss": "", "retem_ir": "", "retem_iss": "", "retem_pis": "", "situacao": "A vencer", "valor_cofins": 0, "valor_csll": 0, "valor_documento": 111111111, "valor_inss": 0, "valor_ir": 0, "valor_iss": 0, "valor_pis": 0}, "author": {"email": "alt.cw-5oldp48r@yopmail.com", "name": "TESTING OMIE", "userId": 593519}, "appKey": "3431512235151", "appHash": "testing-22zaopsa", "origin": "omie-connect-1.6"}';

		$data = json_decode($data);
		print("<pre>".print_r($data,true)."</pre>");
		exit;
	}

	function index() {				
		echo "Api v1.0.3 initiated";
		exit;
	}

	function get_access_token() {
		//BELVO
		$username = "32abd1af-68d6-4c16-a1c2-507f5c345f03";
		$password = "NRR6W#sy4CryDOd9pw-Grwn_x-#eGLVe-u9x5bK-qcLr4@9pmxcqT34QG6l0F158";

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'https://development.belvo.com/api/token/');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n    \"id\": \"$username\",\n    \"password\": \"$password\",\n    \"scopes\": \"read_institutions,write_links,read_links\"\n  }");

		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Host: development.belvo.com';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
		    echo 'Error:' . curl_error($ch);
		}
		
		curl_close($ch);
		return $result;
		exit;
	}

	function omie_webhook() {
		//when data is received, do action
		$data = date("Y-m-d H:i")."\n";
		$input = file_get_contents('php://input');
		if ($input!="") {
			$webhookData = json_decode($input);
			$data .= $input;
		}
		if (isset($_GET['test'])) {
			$data .= $_GET['test'];
		}
		$data .= "\n-------------\n";
		$file = fopen("webhook-omie.txt", "a");
		fwrite($file, $data . "\n");
		fclose($file);
		echo true;
		exit;
	}

	function quanto_token() {
		$UM = new UserModel;
		$client = new \GuzzleHttp\Client();
		
		$quantoToken = $UM->fetchQuantoToken(date("Y-m-d H:i:s"));		
		if ($quantoToken==false) {
			// TOKEN DOESNT EXIST OR EXPIRED

			// GENERATE ACCESS TOKEN
			$response = $client->request('POST', 'https://api-quanto.com/v1/api/token', [
			  'form_params' => [
			    'client_id' => QUANTO_CLIENT_ID,
			    'client_secret' => QUANTO_CLIENT_SECRET,
			    'grant_type' => 'client_credentials'
			  ],
			  'headers' => [
			    'accept' => 'application/json',
			    'content-type' => 'application/x-www-form-urlencoded',
			  ],
			]);
			$token_result = json_decode($response->getBody());
			$access_token = $token_result->access_token;
			$newData = [];
			$newData['access_token'] = $access_token;
			$newData['expiry_time'] = date("Y-m-d H:i:s",strtotime("+".$token_result->expires_in." seconds"));			
			if ($UM->fetchQuantoToken()) {
				$UM->updateQuantoToken($newData);
			} else {
				$UM->addGlobal("quanto_token",$newData);	
			}
		} else {
			// USE LATEST TOKEN
			$access_token = $quantoToken["access_token"];
		}
		return $access_token;
	}	

	function quanto() {						
		if (null !== getUriSegment(3) && getUriSegment(3)=="webhook") {
			$data = date("Y-m-d H:i")."\n";
			$input = file_get_contents('php://input');
			if ($input!="") {
				$webhookData = json_decode($input);
				if ($webhookData->status == "AUTHORISED") {
					$UM = new UserModel;
					$UM->updateQuantoPermissionBySessionId($webhookData->sessionId,$webhookData->permissionId,$webhookData->participant);
				}
				$data .= $input;
			}
			if (isset($_GET['test'])) {
				$data .= $_GET['test'];
			}
			$data .= "\n-------------\n";
			$file = fopen("webhook.txt", "a");
			fwrite($file, $data . "\n");
			fclose($file);
			echo true;
		} else if (null !== getUriSegment(3) && getUriSegment(3)=="consent" && $_POST['token']) {
			$jwt = $_POST['token'];
			try {
				$UM = new UserModel;
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$client = new \GuzzleHttp\Client();

				$access_token = quanto_token();

				// GENERATE CONSENT URL
				$response = $client->request('POST', 'https://api-quanto.com/v1/cf/session', [
				  'body' => '{"accountType":["PERSONAL","BUSINESS"],"returnUri":"'.SITE_URL.'/bank-accounts?processing=1","webhookUrl":"'.SITE_URL.'/api/quanto/webhook"}',
				  'headers' => [
				    'accept' => 'application/json, charset=utf-8',
				    'authorization' => 'Bearer '.$access_token,
				    'content-type' => 'application/json, charset=utf-8',
				  ],
				]);

				$consent_result = json_decode($response->getBody());
				$return['status'] = true;
				$return['redirect_url'] = $consent_result->data->url;
				$newData = [];
				$newData['uuid'] = hexdec(uniqid());
				$newData['user_id'] = $payload->uuid;
				$newData['session_id'] = $consent_result->data->sessionId;
				$UM->addGlobal("quanto_permissions",$newData);
				echo json_encode($return);
				exit;

			} catch (Exception $e) {
				$return['status'] = false;
				$return['message'] = $e->getMessage();
				echo json_encode($return);
				exit;	
			}
		} else if (null !== getUriSegment(3) && getUriSegment(3)=="connect" && $_POST['token'] && isset($_POST['processing'])) {
			$jwt = $_POST['token'];
			try {
				$UM = new UserModel;
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$client = new \GuzzleHttp\Client();
				$access_token = quanto_token();
				
				$quantoPermissionData = $UM->checkQuantoLastPermissionByUserId($payload->uuid);				

				if($quantoPermissionData===false) { 
					$return['status'] = true;
					$return['message'] = __("Nothing to process",true);
					echo json_encode($return);
					exit;	
				} else {
					$connected_banks = 0;
					foreach ($quantoPermissionData as $value) {
						$permission_id = $value['permission_id'];
						$response = $client->request('GET', 'https://api-quanto.com/v1/opf/accounts?page=1&page-size=25', [
						  'headers' => [
						    'accept' => 'application/json, charset=utf-8',
						    'authorization' => 'Bearer '.$access_token,
						    'x-permission-id' => $permission_id,
						  ],
						]);

						$account_result = json_decode($response->getBody());
						$bank_url = "";
						
						foreach ($account_result->data as $key => $account) {
							$accountData = [];
							$accountData['bank_code'] = $account->compeCode;
							$accountData['branch_code'] = $account->branchCode;
							$accountData['account_number'] = $account->number;
							$accountData['account_id'] = $account->accountId;
							$accountData['permission_id'] = $permission_id;							
							$bank_url = $UM->connectBankToQuantoPermissionByUserId($payload->uuid,$accountData);
							if ($bank_url==false) {
								$bank = $UM->fetchBankbyBankName($accountData['bank_code']);
								if ($bank) {
									$newBankAccountData = [];
									$newBankAccountData['uuid'] = hexdec(uniqid());
									$newBankAccountData['user_id'] = $payload->uuid;
									$newBankAccountData['bank_name'] = $bank['bank_name'];
									$newBankAccountData['account_name'] = $accountData['branch_code'];
									$newBankAccountData['account_number'] = $accountData['account_number'];
									$newBankAccountData['country'] = "brazil";
									$newBankAccountData['of_supplier'] = "quanto";
									$newBankAccountData['quanto_account_id'] = $accountData['account_id'];
									$newBankAccountData['quanto_permission_id'] = $accountData['permission_id'];
									$addedBankAccount = $UM->addGlobal("bank_accounts",$newBankAccountData);
									if ($addedBankAccount) {
										$bank_url = $newBankAccountData['uuid'];
										$processedData = [];
										$processedData['is_processed'] = 1;
										$UM->updateGlobal("quanto_permissions",$value['uuid'],$processedData);
										$connected_banks++;
									}
								}
							} else {								
								$processedData = [];
								$processedData['is_processed'] = 1;
								$UM->updateGlobal("quanto_permissions",$value['uuid'],$processedData);
								if ($bank_url) {
									$connected_banks++;
								}
							}							
						}										
					}
				}

				$return['status'] = true;
				if ($connected_banks==1) {
					$return['redirect_url'] = "/bank-accounts/".$bank_url;
				} else if ($connected_banks>1) { 
					$return['message'] = __("Bank accounts successfully connected",true);
				} else {
					$return['message'] = __("No bank accounts connected",true);
				}
				echo json_encode($return);
				exit;				

			} catch (Exception $e) {
				$return['status'] = false;
				$return['message'] = $e->getMessage();
				echo json_encode($return);
				exit;
			}
		} else {
			header("Location: /");
		}
		exit;
	}

	function pluggy_api_key() {
		$ch = curl_init();

		$UM = new UserModel;

		$pluggyApiKey = $UM->fetchPluggyApiKey(date("Y-m-d H:i:s"));		
		if ($pluggyApiKey==false) {
			// API DOESNT EXIST OR EXPIRED
			$payload = array("clientId"=>PLUGGY_CLIENT_ID,"clientSecret"=>PLUGGY_CLIENT_SECRET);
			curl_setopt($ch, CURLOPT_URL, 'https://api.pluggy.ai/auth');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

			$headers = array();
			$headers[] = 'Accept: application/json';
			$headers[] = 'Content-Type: application/json';
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

			$result = curl_exec($ch);
			if (curl_errno($ch)) {
			    echo 'Error:' . curl_error($ch);
			    exit;
			}
			curl_close($ch);
			$api_key = json_decode($result)->apiKey;					

			//SAVE/UPDATE pluggy api key to db
			$newData = [];
			$newData['api_key'] = $api_key;
			$newData['expiry_time'] = date("Y-m-d H:i:s",strtotime("+7200 seconds"));			
			if ($UM->fetchPluggyApiKey()) {
				$UM->updatePluggyApiKey($newData);
			} else {
				$UM->addGlobal("pluggy_api_key",$newData);	
			}
		} else {
			// USE LATEST API KEY
			$api_key = $pluggyApiKey['api_key'];
		}
		
		return $api_key;
		exit;		
	}

	function pluggy() {
		if (isset($_POST['token'])) {
			$jwt = $_POST['token'];
			try {
				$UM = new UserModel;
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));				
				$pluggy_api_key = pluggy_api_key();
			} catch (Exception $e) {
				$return['status'] = false;
				$return['message'] = $e->getMessage();
				exit;
			}

			if (null !== getUriSegment(3) && getUriSegment(3)=="auth") {
				$return['status'] = true;

				$curl = curl_init();

				curl_setopt_array($curl, [
				  CURLOPT_URL => "https://api.pluggy.ai/connect_token",
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "POST",
				  CURLOPT_HTTPHEADER => [
				    "X-API-KEY: ".$pluggy_api_key,
				    "accept: application/json",
				    "content-type: application/json"
				  ],
				]);

				$result = curl_exec($curl);
				if (curl_errno($curl)) {
				    $return['status'] = false;
				    $return['message'] = 'Error:'.curl_error($curl);
				    echo json_encode($return);
				    exit;
				}
				curl_close($curl);
				$return['access_token'] = json_decode($result)->accessToken;
			} else if (null !== getUriSegment(3) && getUriSegment(3)=="processing" && isset($_POST['data'])) {
				$itemData = $_POST['data'];
				$item_id = $itemData['item']['id'];
				$bank_name = $itemData['item']['connector']['name'];

				$curl = curl_init();
				curl_setopt_array($curl, [
				  CURLOPT_URL => "https://api.pluggy.ai/accounts?itemId=".$item_id."&pageSize=500",
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "GET",
				  CURLOPT_HTTPHEADER => [
				    "X-API-KEY: ".$pluggy_api_key,
				    "accept: application/json"
				  ],
				]);

				$result = curl_exec($curl);
				if (curl_errno($curl)) {
				    $return['status'] = false;
				    $return['message'] = 'Error:' . curl_error($curl);
				    echo json_encode($return);
				    exit;
				}
				curl_close($curl);
				$accounts = json_decode($result);

				if ($accounts->total > 0) {
					$statements_added = 0;			
					foreach ($accounts->results as $account) {						
						$account_check = [];

						if ($account->type=="BANK") {

							//check if account # is separated by /
							$account_number_arr = explode("/",$account->number);					

							$account_number = $account->number;
							$account_name = $account->owner;					
							$account_number_only = $branch_number = $bank_number = "";

							if (count($account_number_arr)>1) {
								$account_number_only = $account_number = $account_number_arr[1];
								$branch_number = $account_name = $account_number_arr[0];
								$account_number = $account->number;						
							}

							$bankDataExp = explode("/",$account->bankData->transferNumber);					
							if (count($bankDataExp) == 3) {
								$bank_number = $bankDataExp[0];
								$branch_number = $bankDataExp[1];
								$account_number_only = $bankDataExp[2];
								$account_number = $bankDataExp[1]."/".$bankDataExp[2];
								$account_name = $account->owner;
							}

							//check if bank account exists
							$account_check['user_id'] = $payload->uuid;
							$account_check['bank_name'] = $bank_name;
							$account_check['bank_number'] = $bank_number;
							$account_check['branch_number'] = $branch_number;
							$account_check['account_name'] = $account_name;
							$account_check['account_number'] = $account_number;
							$account_check['account_number_only'] = $account_number_only;
							$bankAccountExist = $UM->checkOFBankAccountConnectionExist($account_check);

							
							$from_date = $bank_account_id = "";					
							$bankAccountData = [];
							if ($bankAccountExist) {
								if ($bankAccountExist['last_sync']!=NULL) {
									$from_date = "&from=".date("Y-m-d",strtotime($bankAccountExist['last_sync']." -2 days"));
								}
								
								//fetch statements and save using account id
								$bank_account_id = $bankAccountExist['uuid'];
								$bankAccountData['bank_name'] = $bank_name;
								$bankAccountData['account_name'] = $account_name;
								$bankAccountData['account_number'] = $account_number;
								$bankAccountData['is_deleted'] = 0;
								$bankAccountData['of_supplier'] = "pluggy";
								$bankAccountData['pluggy_item_id'] = $item_id;
								$bankAccountData['pluggy_account_id'] = $account->id;
								$bankAccountData['pluggy_account_name'] = $account->owner;
								$bankAccountData['last_balance'] = $account->balance;
								$bankAccountData['last_sync'] = date("Y-m-d H:i:s");
								$UM->updateGlobal("bank_accounts",$bank_account_id,$bankAccountData);
							} else {						
								$bank_account_id = $bankAccountData['uuid'] = hexdec(uniqid());
								$bankAccountData['user_id'] = $payload->uuid;
								$bankAccountData['bank_name'] = $bank_name;
								$bankAccountData['account_name'] = $account_name;
								$bankAccountData['account_number'] = $account_number;
								$bankAccountData['account_type'] =  ucwords(strtolower(str_replace("_"," ",$account->subtype)));
								$bankAccountData['country'] = "brazil";
								$bankAccountData['of_supplier'] = "pluggy";
								$bankAccountData['pluggy_item_id'] = $item_id;
								$bankAccountData['pluggy_account_id'] = $account->id;
								$bankAccountData['pluggy_account_name'] = $account->owner;
								$bankAccountData['last_balance'] = $account->balance;
								$bankAccountData['last_sync'] = date("Y-m-d H:i:s");
								$UM->addGlobal("bank_accounts",$bankAccountData);
							}												

							//fetch transactions statements
							$curl = curl_init();
							curl_setopt_array($curl, [
							  CURLOPT_URL => "https://api.pluggy.ai/transactions?accountId=".$account->id."&pageSize=500".$from_date,
							  CURLOPT_RETURNTRANSFER => true,
							  CURLOPT_ENCODING => "",
							  CURLOPT_MAXREDIRS => 10,
							  CURLOPT_TIMEOUT => 30,
							  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
							  CURLOPT_CUSTOMREQUEST => "GET",
							  CURLOPT_HTTPHEADER => [
							    "X-API-KEY: ".$pluggy_api_key,
							    "accept: application/json"
							  ],
							]);

							$resultStatement = curl_exec($curl);
							if (curl_errno($curl)) {
								$return['status'] = false;
							    $return['message'] = 'Error:' . curl_error($curl);
							    echo json_encode($return);
							    exit;
							}
							curl_close($curl);

							$statements = [];

							$account_statement_result = json_decode($resultStatement);
							$page = $account_statement_result->page;
							$page_total = $account_statement_result->totalPages;
							$total_result = $account_statement_result->total;					
							foreach ($account_statement_result->results as $transaction) {
								$statements[] = $transaction;
							}					
							
							if ($page!=$page_total) {
								while ($page++ <= $page_total) {
									$curl = curl_init();
									curl_setopt_array($curl, [
									  CURLOPT_URL => "https://api.pluggy.ai/transactions?accountId=".$account->id."&pageSize=500&page=".$page.$from_date,
									  CURLOPT_RETURNTRANSFER => true,
									  CURLOPT_ENCODING => "",
									  CURLOPT_MAXREDIRS => 10,
									  CURLOPT_TIMEOUT => 30,
									  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
									  CURLOPT_CUSTOMREQUEST => "GET",
									  CURLOPT_HTTPHEADER => [
									    "X-API-KEY: ".$pluggy_api_key,
									    "accept: application/json"
									  ],
									]);

									$resultStatement = curl_exec($curl);
									if (curl_errno($curl)) {
										$return['status'] = false;
									    $return['message'] = 'Error:' . curl_error($curl);
									    echo json_encode($return);
									    exit;
									}
									curl_close($curl);
									$account_statement_result = json_decode($resultStatement);
									foreach ($account_statement_result->results as $transaction) {
										$statements[] = $transaction;
									}
									$page = $account_statement_result->page;
								}
							}

							if (count($statements)>0) {
								foreach ($statements as $statement) {							
									$statementData = [];
									$statementExist = $UM->fetchStatementByTransactionId($statement->id);

									if ($statementExist == false) {
										//only add new statements
										$statementData['transaction_id'] = $statement->id;
										$statementData['bank_account_id'] = $bank_account_id;
										$statementData['credit_debit_type'] = $statement->type;
										$statementData['description'] = $statement->description;
										$statementData['amount'] = abs($statement->amount);
										$statementData['currency'] = $statement->currencyCode;
										$statementData['category'] = $statement->category;
										$statementData['transaction_date'] = date("Y-m-d",strtotime($statement->date));
										$statementData['of_supplier'] = "pluggy";
										$res = $UM->addGlobal("bank_account_transactions",$statementData);
										if ($res) {
											$statements_added++;
										}
									} else {

									}
								}
							}
						}
					}
					$return['status'] = true;
					$return['message'] = $statements_added." ".__("statements successfully added",true);
				} else {
					$return['status'] = false;
					$return['message'] = __("No accounts found.",true);		
				}
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);			
		}		
		echo json_encode($return);
		exit;
	}


	function format_cnpj($cnpj) {
	    return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj);
	}

	function format_cpf($cpf) {
	    return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $cpf);
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
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
			}
			
			echo json_encode($return);
		}
		exit;
	}




	//----- PROFILE ------//
	function update_profile() {
		if(isset($_POST['data']) && isset($_POST['token']))
		{
			parse_str($_POST['data'],$postdata);		
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
					$return['message'] = __("Not able to update profile. Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
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
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function update_settings() {
		if (isset($_POST['token']) && isset($_POST['data'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				parse_str($_POST['data'],$postdata);
				$settings = $UM->lifecycle_settings($payload->uuid);
				$settingsData = [];
				if ($settings===false) {
					$settingsData = $postdata;
					$settingsData['uuid'] = hexdec(uniqid());
					$settingsData['user_id'] = $payload->uuid;
					$result = $UM->addGlobal("lifecycle_settings",$settingsData);
				} else {
					$id = $settings['uuid'];
					$settingsData = $postdata;
					unset($settingsData['uuid']);
					unset($settingsData['id']);
					$result = $UM->updateGlobal("lifecycle_settings",$id,$settingsData);
				}

				if ($result) {
					$return['status'] = true;
					$return['message'] = __("Successfully updated settings",true);
				} else {
					$return['status'] = false;
					$return['message'] = __("Failed to update settings",true);
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

	function fetch_settings() {
		if (isset($_POST['token'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				$result = $UM->lifecycle_settings($payload->uuid);

				if ($result) {
					$return['status'] = true;
					$return['data'] = $result;
				} else {
					$return['status'] = false;
					$return['data'] = [];
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

	

	//---- CLIENT USER -----//
	function add_client_user() {
		if (isset($_POST['token']) && isset($_POST['data'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				parse_str($_POST['data'],$postdata);
				
				$validate_email = email_validation($postdata['email']);
				if ($validate_email['status']) {
					$postdata['email'] = $validate_email['email'];
				} else {
					echo json_encode($validate_email);
					exit;
				}

				$check = $UM->getUser($postdata['email']);
				if ($check) {
					$return['status'] = false;
					$return['message'] = __("Email already exist. Please try another email.",true);
					echo json_encode($return);
					exit;
				}

				$clientdata = $postdata;
				$clientdata['uuid'] = hexdec(uniqid());
				$clientdata['password'] = sha1('abcd1234');
				$clientdata['role'] = "client_user";
				$clientdata['country'] = $payload->country;
				$clientdata['industry'] = $payload->industry;
				$result = $UM->addGlobal("users",$clientdata);
				if ($result) {
					$return['status'] = true;
					$return['message'] = __("Successfully added a user",true);
					$return['user_id'] = $clientdata['uuid'];
					$newdata = [];
					$newdata['client_id'] = $payload->uuid;
					$newdata['user_id'] = $clientdata['uuid'];
					$UM->addGlobal("client_user_relation",$newdata);
				} else {
					$return['status'] = false;
					$return['message'] = __("Failed to add user",true);
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

	function fetch_client_users() {
		if (isset($_POST['token'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));				
				$UM = new UserModel();
				$result = $UM->getClientUsers($payload->uuid);
				if (count($result)>0) {
					$datatable = [];
					foreach ($result as $key => $value) {
						$col = [];
						$col[] = ucwords($value['first_name']." ".$value['last_name']);
						$col[] = $value['email'];							
						$col[] = $value['contact'];
						if ($value['last_login']==null && $value['last_login']=="") {
							$col[] = __("Not yet logged in",true);
						} else {
							$col[] = date(DATEFORMAT,strtotime($value['last_login']))."<br>".date("h:i A",strtotime($value['last_login']));
						}
						if ($value['is_deleted']==1) {
							$status = "<span class='label label-default'>".__('Deactivated',true)."</div>";
						} else {
							if ($value['last_login']==null && $value['last_login']=="") {
								$status = "<span class='label label-default'>".__('Inactive',true)."</div>";
							} else {
								$status = "<span class='label label-success'>".__('Active',true)."</div>";	
							}
						}
						$col[] = $status;
						$col[] = '<div class="text-center"><a class="editBtn text-default" data-id="'.$value['uuid'].'"><i class="icon-pencil"></i></a>
								  <a class="deleteBtn mgl-10 text-default" data-id="'.$value['uuid'].'"><i class="icon-trash"></i></a></div>';
						$datatable[] = $col;
					}
					$return['status'] = true;
					$return['datatable'] = $datatable;
				} else {
					$return['status'] = true;
					$return['datatable'] = [];
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

	function fetch_client_userdata() {
		if (isset($_POST['user_id'])) {
			$UM = new UserModel();
			$userdata = $UM->getUserbyId($_POST['user_id']);
			if ($userdata) {
				$return['status'] = true;
				$return['data'] = $userdata;
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid User ID.",true);
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("No User ID found.",true);
		}
		echo json_encode($return);
		exit;
	}

	function update_client_user() {
		if(isset($_POST['data']) && isset($_POST['token']))
		{
			parse_str($_POST['data'],$postdata);
			$id = $postdata['uuid'];
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;
				$newdata = [];
				if (isset($postdata['email'])) {
					$email = $postdata['email'];
				} else {
					$email=null;
				}
				$check = $UM->getUser($email);
				if ($check) {
					$return['status'] = false;
					$return['message'] = __("Email already exists.",true);
				} else {
					if (isset($postdata['is_deleted'])) {
						$newdata['is_deleted'] = $postdata['is_deleted'];
					}
					if (isset($postdata['first_name'])) {
						$newdata['first_name'] = ucwords($postdata['first_name']);
					}
					if (isset($postdata['last_name'])) {
						$newdata['last_name'] = ucwords($postdata['last_name']);
					}
					if (isset($postdata['contact'])) {
						$newdata['contact'] = ucwords($postdata['contact']);
					}
					if (isset($postdata['business_name'])) {
						$newdata['business_name'] = ucwords($postdata['business_name']);
					}
					if (isset($postdata['country'])) {
						$newdata['country'] = $postdata['country'];
					}

					$result = $UM->updateUser($id,$newdata);	
					if($result)
					{
						$return['status'] = true;
						if (isset($postdata['is_deleted'])) {
							if ($postdata['is_deleted']==1) {
								$return['message'] = __("Successfully deactivated user",true);
							} else if($postdata['is_deleted']==2) {
								$return['message'] = __("Successfully deleted user",true);
							} else {
								$return['message'] = __("Successfully reactivated user",true);
							}
							
						} else {
							$return['message'] = __("Successfully updated user details",true);
						}
						
					}
					else
					{
						$return['status'] = false;
						$return['message'] = __("Not able to update user. Please recheck all fields or contact support.",true);
					}
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
			}
			
			echo json_encode($return);
		}
		exit;
	}





	//---- USER -----//
	function add_user() {
		if (isset($_POST['token']) && isset($_POST['data'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				if ($payload->role=="admin") {
					$UM = new UserModel();
					parse_str($_POST['data'],$postdata);

					$check = $UM->getUser($postdata['email']);
					if ($check) {
						$return['status'] = false;
						$return['message'] = __("Email already exist. Please try another email.",true);
						echo json_encode($return);
						exit;
					}

					$clientdata = $postdata;				
					$clientdata['uuid'] = hexdec(uniqid());
					$clientdata['password'] = sha1('abcd1234');
					$clientdata['industry'] = ucwords($clientdata['industry']);
					$result = $UM->addGlobal("users",$clientdata);
					if ($result) {
						$return['status'] = true;
						$return['message'] = __("Successfully added a user",true);
						$return['user_id'] = $clientdata['uuid'];
					} else {
						$return['status'] = false;
						$return['message'] = __("Failed to add user",true);
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

	function fetch_users() {
		if (isset($_POST['token'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				
				if ($payload->role=="admin") {
					$UM = new UserModel();
					$result = $UM->getAllUsers();
					if (count($result)>0) {
						$datatable = [];
						foreach ($result as $key => $value) {
							$col = [];							
							if ($value['role']=="client") {
								$role = "<span class='label label-primary'>".ucwords($value['role'])."</span>";
							} else {
								$role = "<span class='label label-primary'>".ucwords(str_replace("_"," ",$value['role']))."</span>";							
							}
							$col[] = $role;
							$col[] = ucwords($value['first_name']." ".$value['last_name'])."<br>".$value['contact'];
							$col[] = $value['email'];
							$col[] = $value['industry'];
							if ($value['is_deleted']==1) {
								$status = "<span class='label label-default'>Deactivated</div>";
							} else {
								if ($value['last_login']==null && $value['last_login']=="") {
									$status = "<span class='label label-default'>Inactive</div>";
								} else {
									$status = "<span class='label label-success'>Active</div>";	
								}								
							}
							$col[] = $status;
							if ($value['last_login']==null && $value['last_login']=="") {
								$col[] = __("Not yet logged in",true);
							} else {
								$col[] = date(DATEFORMAT,strtotime($value['last_login']))."<br>".date("h:i A",strtotime($value['last_login']));
							}
							
							$col[] = '<div class="text-center"><a class="editBtn text-default" data-id="'.$value['uuid'].'"><i class="icon-pencil"></i></a>
									  <a class="deleteBtn mgl-10 text-default" data-id="'.$value['uuid'].'"><i class="icon-trash"></i></a></div>';
							$datatable[] = $col;
						}
						$return['status'] = true;
						$return['datatable'] = $datatable;
					} else {
						$return['status'] = true;
						$return['datatable'] = [];
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

	function fetch_userdata() {
		if (isset($_POST['user_id'])) {
			$UM = new UserModel();
			$userdata = $UM->getUserbyId($_POST['user_id']);
			if ($userdata) {
				$return['status'] = true;
				$return['data'] = $userdata;
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid User ID.",true);
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("No User ID found.",true);
		}
		echo json_encode($return);
		exit;
	}

	function update_user() {
		if(isset($_POST['data']) && isset($_POST['token']))
		{
			parse_str($_POST['data'],$postdata);
			$id = $postdata['uuid'];
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;
				$newdata = [];
				if (isset($postdata['email'])) {
					$email = $postdata['email'];
				} else {
					$email=null;
				}
				$check = $UM->getUser($email);
				if ($check) {
					$return['status'] = false;
					$return['message'] = __("Email already exists.",true);
				} else {
					if (isset($postdata['is_deleted'])) {
						$newdata['is_deleted'] = $postdata['is_deleted'];
					}
					if (isset($postdata['first_name'])) {
						$newdata['first_name'] = ucwords($postdata['first_name']);
					}
					if (isset($postdata['last_name'])) {
						$newdata['last_name'] = ucwords($postdata['last_name']);
					}
					if (isset($postdata['contact'])) {
						$newdata['contact'] = ucwords($postdata['contact']);
					}
					if (isset($postdata['business_name'])) {
						$newdata['business_name'] = ucwords($postdata['business_name']);
					}
					if (isset($postdata['country'])) {
						$newdata['country'] = $postdata['country'];
					}
					if (isset($postdata['industry'])) {
						$newdata['industry'] = ucwords($postdata['industry']);
					}

					$result = $UM->updateUser($id,$newdata);	
					if($result)
					{
						$return['status'] = true;
						if (isset($postdata['is_deleted'])) {
							if ($postdata['is_deleted']==1) {
								$return['message'] = __("Successfully deactivated user",true);
							} else if($postdata['is_deleted']==2) {
								$return['message'] = __("Successfully deleted user",true);
							} else {
								$return['message'] = __("Successfully reactivated user",true);
							}
							
						} else {
							$return['message'] = __("Successfully updated user details",true);
						}
						
					}
					else
					{
						$return['status'] = false;
						$return['message'] = __("Not able to update user. Please recheck all fields or contact support.",true);
					}
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
			}
			
			echo json_encode($return);
		}
		exit;
	}


	// --- BANKS --- //
	function add_bank() {
		if (isset($_POST['token']) && isset($_POST['data'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				if ($payload->role=="admin") {
					$UM = new UserModel();
					parse_str($_POST['data'],$postdata);
					$bankdata = $postdata;				
					$bankdata['uuid'] = hexdec(uniqid());
					$result = $UM->addGlobal("banks",$bankdata);
					if ($result) {
						$return['status'] = true;
						$return['message'] = __("Successfully added a bank",true);
					} else {
						$return['status'] = false;
						$return['message'] = __("Failed to add bank",true);
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

	function fetch_banks() {
		if (isset($_POST['token'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				
				$UM = new UserModel();
				if (isset($_POST['zone']) && $_POST['zone']!="") {
					$result = $UM->getGlobal("banks",null,null,$_POST['zone']);
				} else {
					$result = $UM->getGlobal("banks");	
				}
				
				$banks = [];
				if (count($result)>0) {
					$datatable = [];
					foreach ($result as $key => $value) {
						$col = [];
						$col[] = ucwords($value['bank_name']);
						$banks[] = ucwords($value['bank_name']);
						$zone = '';
						if ($value['zone']=="brazil") {
							$zone = "<img src='/assets/images/flags/br.png'/>";
						} else if($value['zone']=="philippines") {
							$zone = "<img src='/assets/images/flags/ph.png'/>";
						} else if ($value['zone']=="united states of america") {
			    			$zone = '<img src="/assets/images/flags/us.png"/>';
			    		} else if ($value['zone']=="singapore") {
			    			$zone = '<img src="/assets/images/flags/sg.png"/>';
			    		}

						$col[] = $zone;							
						if ($value['is_deleted']==1) {
							$status = "<span class='label label-default'>".__('Deactivated',true)."</div>";
						} else {
							$status = "<span class='label label-success'>".__('Active',true)."</div>";
						}
						$col[] = $status;
						$col[] = '<div class="text-center"><a class="editBtn text-default" data-id="'.$value['uuid'].'"><i class="icon-pencil"></i></a>
								  <a class="deleteBtn mgl-10 text-default" data-id="'.$value['uuid'].'"><i class="icon-trash"></i></a></div>';
						$datatable[] = $col;
					}
					sort($banks);
					$selectdata = '';
					foreach ($banks as $key => $value) {
						$selectdata .= '<option value="'.$value.'">'.$value.'</option>';
					}

					$return['status'] = true;
					$return['datatable'] = $datatable;
					$return['selectdata'] = $selectdata;
				} else {
					$return['status'] = true;
					$return['datatable'] = [];
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

	function fetch_bankdata() {
		if (isset($_POST['bank_id'])) {
			$UM = new UserModel();
			$userdata = $UM->getGlobalbyId("banks",$_POST['bank_id']);
			if ($userdata) {
				$return['status'] = true;
				$return['data'] = $userdata;
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Bank ID.",true);
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("No Bank ID found.",true);
		}
		echo json_encode($return);
		exit;
	}

	function update_bank() {
		if(isset($_POST['data']) && isset($_POST['token']))
		{
			parse_str($_POST['data'],$postdata);
			$id = $postdata['uuid'];
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;
				$newdata = [];			
				if (isset($postdata['is_deleted'])) {
					$newdata['is_deleted'] = $postdata['is_deleted'];
				}
				if (isset($postdata['bank_name'])) {
					$newdata['bank_name'] = ucwords($postdata['bank_name']);
				}
				if (isset($postdata['zone'])) {
					$newdata['zone'] = $postdata['zone'];
				}

				$result = $UM->updateGlobal('banks',$id,$newdata);	

				if($result)
				{
					$return['status'] = true;
					if (isset($postdata['is_deleted'])) {
						if ($postdata['is_deleted']==1) {
							$return['message'] = __("Successfully deactivated bank",true);
						} else if($postdata['is_deleted']==2) {
							$return['message'] = __("Successfully deleted bank",true);
						} else {
							$return['message'] = __("Successfully reactivated bank",true);
						}
						
					} else {
						$return['message'] = __("Successfully updated bank details",true);
					}
					
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to update bank. Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
			}
			
			echo json_encode($return);
		}
		exit;
	}



	// --- CLIENT --- //
	function add_client() {
		if (isset($_POST['token']) && isset($_POST['data'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				parse_str($_POST['data'],$postdata);				

				$validate_email = email_validation($postdata['email']);
				if ($validate_email['status']) {
					$postdata['email'] = $validate_email['email'];
				} else {
					echo json_encode($validate_email);
					exit;
				}

				if ($postdata['email_cc']!="") {
					$validate_email = email_validation($postdata['email_cc']);
					if ($validate_email['status']) {
						$postdata['email_cc'] = $validate_email['email'];
					} else {
						echo json_encode($validate_email);
						exit;
					}
				}				

				$clientdata = $postdata;
				$clientdata['uuid'] = hexdec(uniqid());

				if ($payload->role=="client_user") {
					$user_id = $UM->getClientIdbyClientUserId($payload->uuid);
				} else {
					$user_id = $payload->uuid;
				}
				$clientdata['user_id'] = $user_id;
				$clientdata['created_by'] = $payload->uuid;
				$result = $UM->addGlobal("client_lists",$clientdata);
				if ($result) {
					$return['status'] = true;
					if ($_SESSION['userdata']['industry']=="School") {
						$return['message'] = __("Successfully added a student",true);
					} else {
						$return['message'] = __("Successfully added a client",true);	
					}
					
				} else {
					$return['status'] = false;
					if ($_SESSION['userdata']['industry']=="School") {
						$return['message'] = __("Failed to add student",true);
					} else {
						$return['message'] = __("Failed to add client",true);
					}					
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

	function fetch_clients() {
		if (isset($_POST['token'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();

				if ($payload->role=="client_user") {
					$user_id = $UM->getClientIdbyClientUserId($payload->uuid);
				} else {
					$user_id = $payload->uuid;
				}

				if ($payload->role=="admin") {
					$result = $UM->fetchClients();
				} else {
					$result = $UM->fetchClients($user_id);
				}
				if (count($result)>0) {
					$datatable = [];
					$clients = [];
					$count = 1;
					foreach ($result as $key => $value) {
						$clients[$value['name']] = $value;
						$col = [];

						if ($payload->role=="admin") {
							if (isset($value['user']) && $value['user']!=NULL) {
								$col[] = ucwords($value['user']['first_name']." ".$value['user']['last_name']);
							} else {
								$col[] = "";
							}
							
						} 
						
						$emailcontacts = "<b>".__('Primary',true).":</b><br><small class='text-muted'>".str_replace(",",", ",$value['email'])."</small>";

						if ($value['email_cc']!="") {
							$emailcontacts .= "<br><b>".__('CC',true).":</b><br><small class='text-muted'>".str_replace(",",", ",$value['email_cc'])."</small>";
						}											

						$info_addr = [];
						if ($value['business_name']!="") {
							$business_info = '';
							if ($value['business_no']!="") {
								$business_info = '<br><small class="text-muted">'.$value['business_no'].'</small>';
							}
							$info_addr[] = '<b>'.ucwords($value['business_name']).'</b>'.$business_info;
						}						
						$col[] = implode('<br>',$info_addr);
						$col[] = $emailcontacts;
						$col[] = '<b>'.$value['name'].'</b><br><small class="text-muted">'.$value['contact'].'</small>';
						$col[] = $value['notes'];
						$col[] = '<div class="text-center"><a class="editBtn text-default" data-id="'.$value['uuid'].'"><i class="icon-pencil" data-popup="tooltip" title="'.__("Edit",true).'"></i></a>
									  <a class="deleteBtn mgl-10 text-default" data-id="'.$value['uuid'].'"><i class="icon-trash" data-popup="tooltip" title="'.__("Delete",true).'"></i></a></div>';
						$datatable[] = $col;
						ksort($clients);
						$selectdata = "<option value=''>".__('Please choose client from list',true)."</option>";
						foreach ($clients as $key => $value) {
							$selectdata .= "<option value='".$value['uuid']."'>".ucwords($value['name'])."</option>";
						}
					}
					$return['status'] = true;
					$return['datatable'] = $datatable;
					$return['selectdata'] = $selectdata;
				} else {
					$return['status'] = true;
					$return['datatable'] = [];
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

	function fetch_clientdata() {
		if (isset($_POST['client_id'])) {
			$UM = new UserModel();
			$resultdata = $UM->getGlobalbyId("client_lists",$_POST['client_id']);
			if ($resultdata) {
				$return['status'] = true;
				$result = [];
				foreach ($resultdata as $key => $value) {
					$result[$key] = unclean($value);
				}
				$return['data'] = $result;
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Client ID.",true);
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("No Client ID found.",true);
		}
		echo json_encode($return);
		exit;
	}

	function update_client() {
		if(isset($_POST['data']) && isset($_POST['token']))
		{
			parse_str($_POST['data'],$postdata);
			$id = $postdata['uuid'];
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;
				$newdata = [];			
				unset($postdata['uuid']);

				$validate_email = email_validation($postdata['email']);				
				if ($validate_email['status']) {
					$postdata['email'] = $validate_email['email'];
				} else {
					echo json_encode($validate_email);
					exit;
				}

				if ($postdata['email_cc']!="") {
					$validate_email = email_validation($postdata['email_cc']);
					if ($validate_email['status']) {
						$postdata['email_cc'] = $validate_email['email'];
					} else {
						echo json_encode($validate_email);
						exit;
					}
				}

				$newdata = $postdata;				
				if ($_SESSION['userdata']['industry']=="School") {
					$client = "student";
				} else {
					$client = "client";
				}
				$result = $UM->updateGlobal('client_lists',$id,$newdata);	
				if($result)
				{
					$return['status'] = true;
					
					if (isset($postdata['is_deleted'])) {
						if ($postdata['is_deleted']==1) {							
							$return['message'] = __("Successfully deactivated ".$client,true);
						} else if($postdata['is_deleted']==2) {
							$return['message'] = __("Successfully deleted ".$client,true);
						} else {
							$return['message'] = __("Successfully reactivated ".$client,true);
						}
						
					} else {
						$return['message'] = __("Successfully updated ".$client." details",true);
					}					
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to update ".$client.". Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function delete_client() {
		if(isset($_POST['client_id']) && isset($_POST['token']))
		{
			$id = $_POST['client_id'];
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;
				$newdata['is_deleted'] = 1;
				if ($_SESSION['userdata']['industry']=="School") {
					$client = "student";
				} else {
					$client = "client";
				}
				$result = $UM->updateGlobal('client_lists',$id,$newdata);	
				if($result)
				{
					$return['status'] = true;
					$return['message'] = __("Successfully deleted ".$client,true);
					
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to delete ".$client.". Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function bulkClientImport() {		
		if (isset($_POST['token'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel;
				if ($payload->role=="client_user") {
					$user_id = $UM->getClientIdbyClientUserId($payload->uuid);
				} else {
					$user_id = $payload->uuid;
				}
				/* Getting file name */
				$filename = $_FILES['file']['name'];	
				
				$uploadOk = 1;
				$fileType = pathinfo($filename,PATHINFO_EXTENSION);	
				$location = "./tmp/".generate_string(8).".".$fileType;
				
				
				/* Valid Extensions */
				$valid_extensions = array("xlsx");

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

				   	if(move_uploaded_file($_FILES['file']['tmp_name'],$location)){		      
				      	$return['status']=1;
				      	$file_import = $location;
				      	$newVal = [];
				      	$newData = [];
				      	if ($fileType == "xlsx") {						   
						    if ( $xlsx = SimpleXLSX::parse($file_import) ) {
						    	$row = 1;
						    	$message = [];
						        foreach ($xlsx->rows() as $key => $value) {
						        	if ($key>0) {
						        		$duplicate = 0;
						        		$newVal = [];
						                $newVal['uuid'] = hexdec(uniqid());
						                $newVal['user_id'] = $user_id;

						                if ($_SESSION['userdata']['industry']=="School") {
						                	$newVal['business_name'] = $value[0];
							                $newVal['name'] = $value[1];                
							                $newVal['contact'] = $value[2];

							                $validate_email = email_validation($value[3]);
							                if ($validate_email) {
							                	$newVal['email'] = $validate_email['email'];
							                } else {
							                	$newVal['email'] = "";	
							                }
							                $validate_email = email_validation($value[4]);
							                if ($validate_email) {
							                	$newVal['email_cc'] = $validate_email['email'];
							                } else {
							                	$newVal['email_cc'] = "";
							                }
							                							                
							                $newVal['notes'] = $value[5];
						                } else {
						                	if (isset($value[6]) && isset($value[7])) {
						                		$newVal['business_name'] = $value[0];
								                $newVal['business_no'] = $value[1];
								                $newVal['name'] = $value[2];                
								                $newVal['contact'] = $value[3];								                
								                $validate_email = email_validation($value[4]);
								                if ($validate_email) {
								                	$newVal['email'] = $validate_email['email'];
								                } else {
								                	$newVal['email'] = "";	
								                }
								                $validate_email = email_validation($value[5]);
								                if ($validate_email) {
								                	$newVal['email_cc'] = $validate_email['email'];
								                } else {
								                	$newVal['email_cc'] = "";
								                }
								                if ($value[6]=="") {
								                	$newVal['payment_term'] = 30;
								                } else {
								                	$newVal['payment_term'] = $value[6];
								                }								                
								                $newVal['notes'] = $value[7];
						                	} else {
						                		$return['status'] = false;
						        				$return['message'] = __("Cannot read file",true);
						        				echo json_encode($return);
						        				exit;
						                	}						                	
						                }						                
						                
						                $newVal['created_by'] = $_SESSION['userdata']['uuid'];
						                $newData[] = $newVal;

						                $checkClientBusiness = $UM->checkUserClientsBusinessNameExist($newVal['business_name'],$user_id);
						                if ($checkClientBusiness) {
						                	unset($newVal['uuid']);
						                	$newUpdateData = [];
						                	foreach ($newVal as $key2 => $value2) {
						                		if ($value2!="") {
						                			$newUpdateData[$key2] = $value2;
						                		}
						                	}
						                	$newUpdateData['is_deleted'] = 0;
						                	$res = $UM->updateGlobal("client_lists",$checkClientBusiness['uuid'],$newUpdateData);
						                	if ($res) {
								        		$message[] = __("Line",true)." ".$row.": ".__("details updated",true);
								        	} else {
								        		$message[] = __("Line",true)." ".$row.": ".__("not updated",true);
								        	}
						                } else {
						                	$res = $UM->addGlobal("client_lists",$newVal);
						                	if ($res) {
								        		$message[] = __("Line",true)." ".$row.": ".__("details added",true);
								        	} else {
								        		$message[] = __("Line",true)." ".$row.": ".__("not added",true);
								        	}
						                }
						                $row++;
						        	}
						        }
						    } else {
						    	$return['status'] = false;
						        $return['message'] = SimpleXLSX::parseError();
						    }
						} else if ($fileType == ".csv") {     
						    $row = 1;
						    if (($handle = fopen($file_import, "r")) !== FALSE) {
						        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
						            $num = count($data);
						            $row++;
						            for ($c=0; $c < $num; $c++) {
						                echo $data[$c] . ", \n";
						            }
						            echo "<hr>";
						        }
						        fclose($handle);
						    } else {
						    	$return['status'] = false;
						        $return['message'] = __("Cannot read file",true);
						    }
						}
						unlink($location);
						$return['message'] = implode("<br>",$message);
				   	} else {
				   		$return['status']=false;
						$return['message'] = __("Not uploaded due to an error #",true).$_FILES["file"]["error"];
						echo json_encode($return);
						exit;
				   	}
				}

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




	// --- BUSINESS --- //
	function add_business() {
		if (isset($_POST['token']) && isset($_POST['data'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				parse_str($_POST['data'],$postdata);
				$newdata = $postdata;				
				$newdata['uuid'] = hexdec(uniqid());
				if ($payload->role!="admin") {
					$newdata['user_id'] = $payload->uuid;
				}				
				unset($newdata['file']);
				$result = $UM->addGlobal("business_accounts",$newdata);
				if ($result) {
					$return['status'] = true;
					$return['message'] = __("Successfully added business",true);
				} else {
					$return['status'] = false;
					$return['message'] = __("Failed to add business",true);
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

	function fetch_businesses() {
		if (isset($_POST['token'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				if ($payload->role=="admin") {
					$result = $UM->fetchBusinesses();
				} else {
					$result = $UM->fetchBusinesses($payload->uuid);	
				}
				$datahtml = '';
				if (count($result)>0) {
					$datatable = [];
					
					foreach ($result as $key => $value) {
						$col = [];
						$img = "";						
												
						if ($value['business_logo']!="") {
							$img = "<div><img src='".$value['business_logo']."' height='100px'/></div>";
						}
						$col[] = $img;
						if ($payload->role=="admin") {
							if (isset($value['user']) && $value['user']!=null) {
								$col[] = ucwords($value['user']['first_name']." ".$value['user']['last_name']);
							} else {
								$col[] = "";
							}
						}						
						$col[] = $value['business_name'];
						$col[] = str_replace(",",", ",$value['business_email']);
						$col[] = str_replace(",",", ",$value['business_contact']);
						$col[] = '<div class="text-center"><a class="editBtn text-default" data-id="'.$value['uuid'].'"><i class="icon-pencil"></i></a>
									  <a class="deleteBtn mgl-10 text-default" data-id="'.$value['uuid'].'"><i class="icon-trash"></i></a></div>';
						$datatable[] = $col;

						$contacts = [];
						if ($value['business_email']!="") {
							$contacts[] = str_replace(",","<br>",$value['business_email']);
						}
						if ($value['business_contact']!="") {
							$contacts[] = str_replace(",",", ",$value['business_contact']);
						}
						

						$datahtml .= '<div class="col-lg-3 col-md-4 col-sm-4 col-xs-6">
							<div class="thumbnail no-padding">
								<div class="thumb bg-kolek-white" style="background-image:url('.str_replace(" ","%20",$value['business_logo']).')">
								</div>
							
						    	<div class="caption text-center">
						    		<div class="clear10"></div>
						    		<h6 class="text-semibold no-margin">'.$value['business_name'].' <small class="display-block">'.implode('<div class="clear10"></div>',$contacts).'</small></h6>
						    		<div class="clear10"></div>';
						    	$datahtml .= '</div>
						    	<ul class="thumbnail-footer-buttons thumbnail-footer-buttons-2 icons-list">';
						    		if ($value['is_primary']==1) {
										$datahtml .= '<li><i class="icon-star-full2 text-primary"></i></li>';
									} else {
										$datahtml .= '<li><a class="setPrimaryBtn text-default" data-id="'.$value['uuid'].'" data-popup="tooltip" title="'.__("Set as Primary",true).'"><i class="icon-star-empty3 bold"></i></a></li>';
									}
						    		
			                    	$datahtml .= '<li><a class="editBtn text-default" data-id="'.$value['uuid'].'" data-id="'.$value['uuid'].'" data-popup="tooltip" title="'.__("Edit",true).'"><i class="icon-pencil"></i></a></li>
			                    </ul>
					    	</div>
						</div>';
					}
					if (false) {
						$datahtml .= '<div class="col-lg-3 col-md-4 col-sm-6">
							<div class="thumbnail no-padding">
								<div class="thumb pointer addNew bg-kolek-white" style="height:250px;text-align:center;padding-top:60px;">
									<div class="icon-object border-muted text-muted"><i class="icon-plus3"></i></div>
									<div class="caption text-center">
							    		<h6 class="text-semibold text-muted no-margin">'.__("Add Business",true).'</h6>
							    		<div class="clear10"></div>
							    	</div>
								</div>								
					    	</div>
						</div>';
					}
					$return['status'] = true;
					$return['datatable'] = $datatable;
					$return['datahtml'] = $datahtml;
				} else {
					$return['status'] = true;
					$return['datatable'] = [];
					if (false) {
						$datahtml .= '<div class="col-lg-3 col-md-6">
							<div class="thumbnail no-padding">
								<div class="thumb pointer addNew bg-kolek-white" style="height:250px;text-align:center;padding-top:60px;">
									<div class="icon-object border-muted text-muted"><i class="icon-plus3"></i></div>
									<div class="caption text-center">
							    		<h6 class="text-semibold text-muted no-margin">'.__("Add Business",true).'</h6>
							    		<div class="clear10"></div>
							    	</div>
								</div>								
					    	</div>
						</div>';
					}
					
					$return['datahtml'] = $datahtml;
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

	function fetch_businessdata() {
		if (isset($_POST['id'])) {
			$UM = new UserModel();
			$resultdata = $UM->getGlobalbyId("business_accounts",$_POST['id']);
			if ($resultdata) {
				$return['status'] = true;
				$return['data'] = $resultdata;
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Business ID.",true);
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("No Business ID found.",true);
		}
		echo json_encode($return);
		exit;
	}

	function update_business() {
		if(isset($_POST['data']) && isset($_POST['token']))
		{
			parse_str($_POST['data'],$postdata);
			$id = $postdata['uuid'];
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;
				$newdata = [];			
				unset($postdata['uuid']);
				$newdata = $postdata;

				if ($newdata['business_email']!="") {
					$validate_email = email_validation($newdata['business_email']);
					if ($validate_email['status']) {
						$newdata['business_email'] = $validate_email['email'];
					} else {
						echo json_encode($validate_email);
						exit;
					}
				}
				
				if (isset($newdata['is_primary'])===false) {
					$newdata['is_primary']=0;
				} else {
					$UM->clearPrimary('business_accounts',$tokenvalue->uuid);
				}				
				$result = $UM->updateGlobal('business_accounts',$id,$newdata);	
				if($result)
				{
					$return['status'] = true;
					if (isset($postdata['is_deleted'])) {
						if ($postdata['is_deleted']==1) {
							$return['message'] = __("Successfully deactivated business",true);
						} else if($postdata['is_deleted']==2) {
							$return['message'] = __("Successfully deleted business",true);
						} else {
							$return['message'] = __("Successfully reactivated business",true);
						}
						
					} else {
						$return['message'] = __("Successfully updated business details",true);
					}
					
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to update business. Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function set_business_primary() {
		if(isset($_POST['id']) && isset($_POST['token']))
		{
			$id = $_POST['id'];
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;
				$newdata['is_primary'] = 1;
				$UM->clearPrimary('business_accounts',$tokenvalue->uuid);
				$result = $UM->updateGlobal('business_accounts',$id,$newdata);	
				if($result)
				{
					$return['status'] = true;
					$return['message'] = __("Successfully set business account to primary",true);
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to set business account to primary. Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function delete_business() {
		if(isset($_POST['id']) && isset($_POST['token']))
		{
			$id = $_POST['id'];
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;
				$newdata['is_deleted'] = 1;
				
				$result = $UM->updateGlobal('business_accounts',$id,$newdata);	
				if($result)
				{
					$return['status'] = true;
					$return['message'] = __("Successfully deleted business",true);
					
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to delete business. Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
			}
			
			echo json_encode($return);
		}
		exit;
	}



	// --- BANK ACCOUNT --- //
	function add_bank_account() {
		if (isset($_POST['token']) && isset($_POST['data'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				parse_str($_POST['data'],$postdata);				
				$newdata = $postdata;				
				$newdata['uuid'] = hexdec(uniqid());
				$newdata['user_id'] = $payload->uuid;				
				$result = $UM->addGlobal("bank_accounts",$newdata);
				if ($result) {
					$return['status'] = true;
					$return['message'] = __("Successfully added bank account",true);
				} else {
					$return['status'] = false;
					$return['message'] = __("Failed to add bank account",true);
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

	function fetch_bank_accounts() {
		if (isset($_POST['token'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				if ($payload->role=="admin") {
					$result = $UM->fetchBankAccounts();
				} else {
					$result = $UM->fetchBankAccounts($payload->uuid);	
				}

				if (false) {
					//BELVO ACCESS TOKEN
					$return['token_result'] = get_access_token();
				}
				
				$datahtml = '';
				if (count($result)>0) {
					$datatable = [];
					foreach ($result as $key => $value) {
						$col = [];						
						$col[] = __(ucwords($value['country']),true);
						$col[] = $value['bank_name'];
						$col[] = $value['account_name'];
						$col[] = $value['account_number'];
						$col[] = '<div class="text-center"><a class="editBtn text-default" data-id="'.$value['uuid'].'"><i class="icon-pencil"></i></a>
									  <a class="deleteBtn mgl-10 text-default" data-id="'.$value['uuid'].'"><i class="icon-trash"></i></a></div>';
						$datatable[] = $col;
						if ($value['of_supplier']!="" && $value['of_supplier']!=null) {
							$thumbnail_class = "of-connected";
						} else {
							$thumbnail_class = "of-not-connected";
						}
						$datahtml .= '<div class="col-lg-3 col-md-4 col-sm-4 col-xs-6">
							<div class="thumbnail no-padding '.$thumbnail_class.'">						
						    	<div class="caption" style="min-height:155px;">';

						   	$datahtml .= '<div class="clear"></div>';
						   			if ($value['of_supplier']!=null && $value['of_supplier']!="") {
							   			$datahtml .= '<p class="mgt-0"><small class="display-block" data-popup="tooltip" title="'.__("Bank Connected",true).'">'.$value['bank_name'].'<i class="mgl-5 icon-link f-14"></i></small></p>';
							   		} else {
							   			$datahtml .= '<p class="mgt-0 text-muted"><small class="display-block">'.$value['bank_name'].'</small></p>';
							   		}
						    		if ($payload->country=="brazil" && $value['account_name']!="") {
						    			if ($value['of_supplier']=="pluggy") {
						    				$datahtml .= '<p class="text-semibold no-margin ellipsis-nowrap">'.$value['account_name'].'</p><p class="no-margin"><b>'.__("Account No",true).':</b> '.$value['account_number'].'</p>';
							    			if ($value['pix_number']!="") {
							    				$datahtml .= '<p class="no-margin"><b>'.__("Pix No.",true).'</b> '.$value['pix_number'].'</p>';
							    			}
						    			} else {
						    				$datahtml .= '<p class="no-margin"><b>'.__("Branch #",true).':</b> '.$value['account_name'].'</p><p class="no-margin"><b>'.__("Account No",true).':</b> '.$value['account_number'].'</p>';
							    			if ($value['pix_number']!="") {
							    				$datahtml .= '<p class="no-margin"><b>'.__("Pix No.",true).'</b> '.$value['pix_number'].'</p>';
							    			}	
						    			}
						    		} else {
						    			$datahtml .= '<p class="text-semibold no-margin">'.$value['account_name'].'<br>'.$value['account_number'].'</p>';
						    		}
						    		if ($value['account_type']!="" && $value['account_type']!=null) {
						    			$datahtml .= '<p class="text-bold no-margin">'.strtoupper($value['account_type']).'</p>';
						    		}

						    		$datahtml .= '<div class="clear10"></div>';						    		
							if (($value['of_supplier']=="quanto" && $value['quanto_account_id']!="" && $value['quanto_account_id']!=null)||($value['of_supplier']=="pluggy" && $value['pluggy_item_id']!="" && $value['pluggy_account_id']!=null)) 
							{
								$datahtml .= '<div class="text-center"><a href="/bank-accounts/'.$value['uuid'].'"><button class="btn btn-success viewStatementsBtn btn-xs">'.__("View Statements",true).' <i class="icon-file-text2 position-left f-13 mgl-5"></i></button></a></div>';
							}
							
						  	$datahtml .= '<div class="clear10"></div>					    			
						    	</div>
						    	<ul class="thumbnail-footer-buttons thumbnail-footer-buttons-3 icons-list">';
					    			if ($value['is_primary']==1) {
										$datahtml .= '<li><i class="icon-star-full2 text-primary"></i></li>';
									} else {
										$datahtml .= '<li><a class="setPrimaryBtn text-default" data-id="'.$value['uuid'].'" data-popup="tooltip" title="'.__("Set as Primary",true).'"><i class="icon-star-empty3 bold"></i></a></li>';
									}
				                    	$datahtml .= '<li><a class="editBtn text-default" data-id="'.$value['uuid'].'"><i class="icon-pencil" data-popup="tooltip" title="'.__("Edit",true).'"></i></a></li>
				                    	<li><a class="deleteBtn text-default" data-id="'.$value['uuid'].'" data-popup="tooltip" title="'.__("Delete",true).'"><i class="icon-trash"></i></a></li>
			                    	</ul>
					    	</div>
						</div>';
					}
					$datahtml .= '<div class="col-lg-3 col-md-4 col-sm-4 col-xs-6">
							<div class="thumbnail no-padding bg-kolek-white">
								<div class="thumb pointer addNew bg-kolek-white" style="min-height:200px;text-align:center;padding-top:30px;">
									<div class="icon-object border-muted text-muted"><i class="icon-plus3"></i></div>
									<div class="caption text-center">
							    		<h6 class="text-semibold text-muted no-margin">'.__("Add Bank",true).'</h6>
							    		<div class="clear10"></div>
							    	</div>
								</div>								
					    	</div>
						</div>';
					$return['status'] = true;
					$return['datatable'] = $datatable;
					$return['datahtml'] = $datahtml;
				} else {
					$datahtml .= '<div class="col-lg-3 col-md-6">
							<div class="thumbnail no-padding bg-kolek-white">
								<div class="thumb pointer addNew bg-kolek-white" style="height:250px;text-align:center;padding-top:60px;">
									<div class="icon-object border-muted text-muted"><i class="icon-plus3"></i></div>
									<div class="caption text-center">
							    		<h6 class="text-semibold text-muted no-margin">'.__("Add Bank",true).'</h6>
							    		<div class="clear10"></div>
							    	</div>
								</div>								
					    	</div>
						</div>';
					$return['status'] = true;
					$return['datatable'] = [];
					$return['datahtml'] = $datahtml;
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

	function fetch_bankaccountdata() {
		if (isset($_POST['id']) && isset($_POST['token'])) {
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel();
				$resultdata = $UM->getGlobalbyId("bank_accounts",$_POST['id']);
				if ($resultdata) {
					$return['status'] = true;
					$return['data'] = $resultdata;
				} else {
					$return['status'] = false;
					$return['message'] = __("Invalid Bank Account ID.",true);
				}
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("No Bank Account ID found.",true);
		}
		echo json_encode($return);
		exit;
	}

	function update_bankaccount() {
		if(isset($_POST['data']) && isset($_POST['token']))
		{
			parse_str($_POST['data'],$postdata);
			$id = $postdata['uuid'];
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;
				$newdata = [];			
				unset($postdata['uuid']);
				$newdata = $postdata;
				if (isset($newdata['is_primary'])===false) {
					$newdata['is_primary']=0;
				} else {
					$UM->clearPrimary('bank_accounts',$tokenvalue->uuid);
				}
				$result = $UM->updateGlobal('bank_accounts',$id,$newdata);	
				if($result)
				{
					$return['status'] = true;
					if (isset($postdata['is_deleted'])) {
						if ($postdata['is_deleted']==1) {
							$return['message'] = __("Successfully deactivated bank account",true);
						} else if($postdata['is_deleted']==2) {
							$return['message'] = __("Successfully deleted bank account",true);
						} else {
							$return['message'] = __("Successfully reactivated bank account",true);
						}
						
					} else {
						$return['message'] = __("Successfully updated bank account details",true);
					}
					
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to update bank account. Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function set_bankaccount_primary() {
		if(isset($_POST['id']) && isset($_POST['token']))
		{
			$id = $_POST['id'];
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;
				$newdata['is_primary'] = 1;
				$UM->clearPrimary('bank_accounts',$tokenvalue->uuid);			
				$result = $UM->updateGlobal('bank_accounts',$id,$newdata);	
				if($result)
				{
					$return['status'] = true;
					$return['message'] = __("Successfully set bank account to primary",true);
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to set bank account to primary. Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function delete_bankaccount() {
		if(isset($_POST['id']) && isset($_POST['token']))
		{
			$id = $_POST['id'];
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;				
				$result = $UM->deleteGlobalById('bank_accounts',$id);
				$UM->deleteStatementsFromBankAccount($id);
				if($result)
				{
					$return['status'] = true;
					$return['message'] = __("Successfully deleted bank account",true);
					
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to delete bank account. Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
			}
			
			echo json_encode($return);
		}
		exit;
	}




	// --- RECEIVABLES --- //
	function add_invoice() {
		if (isset($_POST['token']) && isset($_POST['data'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();				
				$boleto = $attachment = "";
				if (isset($_FILES['file'])) {
					$upfile = file_upload($_FILES['file']['name'],$_FILES['file']['tmp_name'],"attachment");
					$attachment = $upfile['data']['file'];
				}
				if (isset($_FILES['boleto'])) {
					$upfile = file_upload($_FILES['boleto']['name'],$_FILES['boleto']['tmp_name'],"attachment");					
					$boleto = $upfile['data']['file'];
				}

				parse_str($_POST['data'],$postdata);
				$newdata = $postdata;				
				$inv_id = $newdata['uuid'] = hexdec(uniqid());
				if ($payload->role=="client_user") {
					$user_id = $UM->getClientIdbyClientUserId($payload->uuid);
				} else {
					$user_id = $payload->uuid;
				}

				$newdata['user_id'] = $user_id;
				$newdata['created_by'] = $payload->uuid;;
				$newdata['boleto'] = $boleto;
				$newdata['attachment'] = $attachment;

				$earlier = new DateTime(date("Y-m-d",strtotime($newdata['issued_date'])));
				$later = new DateTime(date("Y-m-d",strtotime($newdata['due_date'])));
				$newdata['payment_term'] = $later->diff($earlier)->format("%a");

				$line_items = "";
				$line_items_arr = [];
				$line_items_not_empty = false;

				for ($i=1; $i <= count($newdata['description']); $i++) { 
					$col = [];
					if ($newdata['price'][$i]!="") {
						$line_items_not_empty = true;
						if (($newdata['price'][$i]*$newdata['quantity'][$i])>0) {
							$col['description'] = $newdata['description'][$i];
							$col['quantity'] = $newdata['quantity'][$i];
							$col['price'] = $newdata['price'][$i];
							$line_items_arr[] = $col;
						}
					}					
				}

				if ($line_items_not_empty) {
					$line_items = json_encode($line_items_arr);					
				}
				
				unset($newdata['description']);
				unset($newdata['price']);
				unset($newdata['quantity']);

				$newdata['line_items'] = $line_items;
				
				$result = $UM->addGlobal("account_receivables",$newdata);				

				if ($result) {
					add_lifecycle_default($payload->uuid,$newdata);
					$return['status'] = true;
					$return['message'] = __("Successfully added a invoice",true);
					$return['invoice_id'] = $inv_id;
				} else {
					$return['status'] = false;
					$return['message'] = __("Failed to add invoice",true);
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

	function fetch_invoices_for_lifecycle() {
		if (isset($_POST['token'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();

				if ($_SESSION['userdata']['industry']=="School" || $payload->role=="client_user") {
					$user_id = $UM->getClientIdbyClientUserId($payload->uuid);
				} else {
					$user_id = $payload->uuid;
				}
				
				$filters['status_filter'] = $_POST['invoice_type'];				

				if ($payload->role=="admin") {
					$result = $UM->fetchInvoicesbyUserId();
				} else {
					$result = $UM->fetchInvoicesbyUserId($user_id,$filters);
				}
				
						
				if (count($result)>0) {
					$datatable = [];
					foreach ($result as $key => $value) {
						$col = [];
						$currency = $UM->fetchCurrencyByCode($value['currency']);
						$earlier = new DateTime(date("Y-m-d"));
						$later = new DateTime(date("Y-m-d",strtotime($value['due_date'])));
						$abs_diff = $later->diff($earlier)->format("%a");
						$lifecycles = $UM->fetchLifecycles($value['uuid']);
						$payment_date = "";
						$due = $value['due_date'];
						$now = date("Y-m-d");
						$due_d_text = __("left",true);
						if ($due < $now) {
						 	$abs_diff = ($abs_diff*-1);
						 	$due_d_text = __("Overdue",true);
						}		
						$due_urgency = '';

						if ($abs_diff<3) {
							$due_urgency = '<b class="text-danger">';
						} else {
							$due_urgency = '<b class="text-primary">';							 
						}

						if (isset($value['client_details']['business_name'])===false) {
							$value['client_details']['business_name'] = "";
						}
						if (isset($value['client_details']['email'])===false) {
							$value['client_details']['email'] = "";
						}

						if ($value['currency']=="BRL") {
							$decimals = ",";
							$thousands = ".";
						} else {
							$decimals = ".";
							$thousands = ",";
						}

						$badge_text = __($value['status'],true);							
						switch($value['status']) {
							case 'Ongoing': $labelColor = 'primary';
							break;
							case 'On-hold': $labelColor = 'default';
							break;
							case 'Overdue': $labelColor = 'danger';
							break;
							case 'Paid': $labelColor = 'success';
							break;
							case 'Cancelled': $labelColor = 'default';
							break;
							case 'Bad Debt': $labelColor = 'danger';
							break;
						}
						$prio = 3;
						if ($value['status']=="Ongoing") {
							if ($due < $now) {
							 	$labelColor = "danger";
							 	$badge_text = __("Overdue",true);
							 	$prio = 0;
							} else {
								if ($abs_diff==0) {
									$labelColor = "warning";
									$badge_text = __("Due Today",true);
									$prio = 1;
								} else if ($abs_diff>0 && $abs_diff<=5) {
									$labelColor = "reminder";
									$prio = 2;
								}
							}
						}

						
						if ($value['status']=="Paid") {
							$payment_date = '<br>'.date(DATEFORMAT,strtotime($value['payment_date']));
						}

						//AGING
						if ($value['status']=="Paid") {
							$aging_later = new DateTime(date("Y-m-d",strtotime($value['payment_date'])));
						} else {
							$aging_later = new DateTime(date("Y-m-d"));	
						}
						$aging_earlier = new DateTime(date("Y-m-d",strtotime($value['issued_date'])));
						$aging = $aging_later->diff($aging_earlier)->format("%a");

						
						$added_by = '';
						if (isset($value['added_by'])) {
							$added_by = ucwords($value['added_by']['first_name'].' '.$value['added_by']['last_name']);
						}

						//Payment Term
						$term_earlier = new DateTime(date("Y-m-d",strtotime($value['issued_date'])));
						$term_later = new DateTime(date("Y-m-d",strtotime($value['due_date'])));
						$payment_term = $term_later->diff($term_earlier)->format("%a");

						//updated amount
						$partialPayments = $UM->fetchInvoicePayments($value['uuid']);
						$discounts = $late_fee = $interest = $total_invoice_amount = $total_updated_amount = $total_partial = 0;
						if (count($partialPayments)>0) {
							foreach ($partialPayments as $pp) {
								$total_partial += $pp['amount'];
							}
						}

						if ($value['payment_date']!=NULL) {
							$earlier = new DateTime(date("Y-m-d",strtotime($value['payment_date'])));
						} else {
							$earlier = new DateTime(date("Y-m-d"));	
						}
						
				        $later = new DateTime(date("Y-m-d",strtotime($value['due_date'])));
				        $day_overdue = $later->diff($earlier)->format("%a");							

						$discounts = ($value['amount']*($value['discount']/100));
		        		$late_fee = ($value['amount']*($value['late_fee']/100));
				        $interest = (($value['interest']/100)/30) * $day_overdue * $value['amount'];

				        if ($later<$earlier) {
				        	$total_updated_amount = $value['amount'] - $total_partial - $discounts + $late_fee + $interest;
				        } else {
				        	$total_updated_amount = $value['amount'] - $total_partial - $discounts;
				        }
				        

						$col[] = '<div class="text-center"><input type="checkbox" name="select_box" value="'.$value['uuid'].'"/></div>';
						$col[] = '<a href="/invoice/'.$value['uuid'].'" class="bold" target="_blank">'.$value['invoice_no'].'<i class="icon-new-tab mgl-5" style="font-size:13px;"></i></a>';
						$col[] = '<h6 class="no-margin">
		                		<b>'.$value['client_details']['business_name'].'</b>
		                		<small class="display-block text-muted">'.str_replace(',','<br>',$value['client_details']['name']).'</small>
	                		</h6>';	                	
	                	$col[] = number_format($total_updated_amount,2,$decimals,$thousands);
	                	if ($payment_date!="") {
	                		$col[] = '<label class="label label-'.$labelColor.'">'.$badge_text.'</label>'.$payment_date;
	                	} else {
	                		$col[] = '<span style="display:none;">'.$prio.'</span><label class="label label-'.$labelColor.'">'.$badge_text.'</label>';
	                	}
						$col[] = '<span style="display:none;">'.strtotime($value['due_date']).'</span>'.date(DATEFORMAT,strtotime($value['due_date']));					
						
						$datatable[] = $col;
					}
					$return['status'] = true;
					$return['datatable'] = $datatable;
					$return['data'] = $result;
				} else {
					$return['status'] = true;
					$return['datatable'] = [];
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

	function fetch_invoices() {
		if (isset($_POST['token']) && isset($_POST['filters'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				$filters = json_decode($_POST['filters']);

				if ($_SESSION['userdata']['industry']=="School" || $payload->role=="client_user") {
					$user_id = $UM->getClientIdbyClientUserId($payload->uuid);
				} else {
					$user_id = $payload->uuid;
				}
				
				if ($payload->role=="admin") {
					$result = $UM->fetchInvoices(null,$filters);
				} else {
					$result = $UM->fetchInvoices($user_id,$filters);
				}
						
				if (count($result)>0) {
					$datatable = [];
					foreach ($result as $key => $value) {
						$col = [];
						$currency = $UM->fetchCurrencyByCode($value['currency']);
						$earlier = new DateTime(date("Y-m-d"));
						$later = new DateTime(date("Y-m-d",strtotime($value['due_date'])));
						$abs_diff = $later->diff($earlier)->format("%a");
						$lifecycles = $UM->fetchLifecycles($value['uuid']);
						$payment_date = "";
						$due = $value['due_date'];
						$now = date("Y-m-d");
						$due_d_text = __("left",true);
						if ($due < $now) {
						 	$abs_diff = ($abs_diff*-1);
						 	$due_d_text = __("Overdue",true);
						}		
						$due_urgency = '';

						if ($abs_diff<3) {
							$due_urgency = '<b class="text-danger">';
						} else {
							$due_urgency = '<b class="text-primary">';							 
						}

						if (isset($value['client_details']['business_name'])===false) {
							$value['client_details']['business_name'] = "";
						}
						if (isset($value['client_details']['email'])===false) {
							$value['client_details']['email'] = "";
						}

						if ($value['currency']=="BRL") {
							$decimals = ",";
							$thousands = ".";
						} else {
							$decimals = ".";
							$thousands = ",";
						}

						$badge_text = __($value['status'],true);							
						switch($value['status']) {
							case 'Ongoing': $labelColor = 'primary';
							break;
							case 'On-hold': $labelColor = 'default';
							break;
							case 'Overdue': $labelColor = 'danger';
							break;
							case 'Paid': $labelColor = 'success';
							break;
							case 'Cancelled': $labelColor = 'default';
							break;
							case 'Bad Debt': $labelColor = 'danger';
							break;
						}
						$prio = 3;
						if ($value['status']=="Ongoing") {
							if ($due < $now) {
							 	$labelColor = "danger";
							 	$badge_text = __("Overdue",true);
							 	$prio = 0;
							} else {
								if ($abs_diff==0) {
									$labelColor = "warning";
									$badge_text = __("Due Today",true);
									$prio = 1;
								} else if ($abs_diff>0 && $abs_diff<=5) {
									$labelColor = "reminder";
									$prio = 2;
								}
							}
						}

						
						if ($value['status']=="Paid") {
							$payment_date = '<br>'.date(DATEFORMAT,strtotime($value['payment_date']));
						}

						//AGING
						if ($value['status']=="Paid") {
							$aging_later = new DateTime(date("Y-m-d",strtotime($value['payment_date'])));
						} else {
							$aging_later = new DateTime(date("Y-m-d"));	
						}
						$aging_earlier = new DateTime(date("Y-m-d",strtotime($value['issued_date'])));
						$aging = $aging_later->diff($aging_earlier)->format("%a");

						
						$added_by = '';
						if (isset($value['added_by'])) {
							$added_by = ucwords($value['added_by']['first_name'].' '.$value['added_by']['last_name']);
						}

						//Payment Term
						$term_earlier = new DateTime(date("Y-m-d",strtotime($value['issued_date'])));
						$term_later = new DateTime(date("Y-m-d",strtotime($value['due_date'])));
						$payment_term = $term_later->diff($term_earlier)->format("%a");

						//updated amount
						$partialPayments = $UM->fetchInvoicePayments($value['uuid']);
						$discounts = $late_fee = $interest = $total_invoice_amount = $total_updated_amount = $total_partial = 0;
						if (count($partialPayments)>0) {
							foreach ($partialPayments as $pp) {
								$total_partial += $pp['amount'];
							}
						}

						if ($value['payment_date']!=NULL) {
							$earlier = new DateTime(date("Y-m-d",strtotime($value['payment_date'])));
						} else {
							$earlier = new DateTime(date("Y-m-d"));	
						}
						
				        $later = new DateTime(date("Y-m-d",strtotime($value['due_date'])));
				        $day_overdue = $later->diff($earlier)->format("%a");							

						$discounts = ($value['amount']*($value['discount']/100));
		        		$late_fee = ($value['amount']*($value['late_fee']/100));
				        $interest = (($value['interest']/100)/30) * $day_overdue * $value['amount'];

				        if ($later<$earlier) {
				        	$total_updated_amount = $value['amount'] - $total_partial - $discounts + $late_fee + $interest;
				        } else {
				        	$total_updated_amount = $value['amount'] - $total_partial - $discounts;
				        }
				        

						$col[] = '<div class="text-center"><input type="checkbox" name="select_box" value="'.$value['uuid'].'"/></div>';
						$col[] = '<a href="/invoice/'.$value['uuid'].'" class="bold" target="_blank">'.$value['invoice_no'].'<i class="icon-new-tab mgl-5" style="font-size:13px;"></i></a><br><a href="'.SITE_URL.'/p/'.$value['uuid'].'" class="copy text-muted">'.__("Invoice Link",true).' <i class="icon-copy3 f-14 mgl-5"></i></a>';
						if (isset($value['client_details']['name'])) {
							$client_name = $value['client_details']['name'];
						} else {
							$client_name = "";
						}
						$col[] = '<h6 class="no-margin">
		                		<b>'.$value['client_details']['business_name'].'</b>
		                		<small class="display-block text-muted">'.str_replace(',','<br>',$client_name).'</small>
	                		</h6>';
	                	$col[] = number_format($value['amount'],2,$decimals,$thousands);
	                	$col[] = number_format($total_updated_amount,2,$decimals,$thousands);
	                	if ($payment_date!="") {
	                		$col[] = '<label class="label label-'.$labelColor.'">'.$badge_text.'</label>'.$payment_date;
	                	} else {
	                		$col[] = '<span style="display:none;">'.$prio.'</span><label class="label label-'.$labelColor.'">'.$badge_text.'</label>';
	                	}

	                	
	                	$col[] = '<span style="display:none;">'.strtotime($value['issued_date']).'</span>'.date(DATEFORMAT,strtotime($value['issued_date']));
	                	$col[] = $payment_term;
						$col[] = '<span style="display:none;">'.strtotime($value['due_date']).'</span>'.date(DATEFORMAT,strtotime($value['due_date']));
						
						$col[] = '<div class="text-center">'.$aging."</div>";
						
						$datatable[] = $col;
					}
					$return['status'] = true;
					$return['datatable'] = $datatable;
					$return['data'] = $result;
				} else {
					$return['status'] = true;
					$return['datatable'] = [];
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

	function fetch_invoicedata() {
		if (isset($_POST['id']) && isset($_POST['token'])) {
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));
			if ($tokenvalue) {
				$UM = new UserModel();				
				if (isset($_POST['invoice_type']) && $_POST['invoice_type']=="consolidated") {
					$resultdata = $UM->getGlobalbyId("debt_collection",$_POST['id']);
				} else {
					$resultdata = $UM->getGlobalbyId("account_receivables",$_POST['id']);	
				}
				
				if ($resultdata) {
					$return['status'] = true;
					$return['data'] = $resultdata;
				} else {
					$return['status'] = false;
					$return['message'] = __("Invalid Invoice ID.",true);
				}
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("No Invoice ID found.",true);
		}
		echo json_encode($return);
		exit;
	}

	function update_invoice() {
		if((isset($_POST['data']) || isset($_POST['id'])) && isset($_POST['token']))
		{			
			if (isset($_POST['type']) && $_POST['type']=="reconciled") {
				parse_str($_POST['data'],$postdata);
				$id = $postdata['uuid'];
			} else {
				if (isset($_POST['id'])) {
					$id = $_POST['id'];
				} else {
					parse_str($_POST['data'],$postdata);
					$id = $postdata['uuid'];
				}
			}
			
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));
			if ($tokenvalue) {				
				$UM = new UserModel;
				$newdata = [];			
				if (isset($_POST['type'])){
					if ($_POST['type']=="reconciled") {
						$newdata['status'] = "Paid";
					} else {
						$newdata['status'] = $_POST['type'];
					}
				} else {
					unset($postdata['uuid']);
					$newdata = $postdata;	
				}							

				$boleto = $attachment = "";
				if (isset($_FILES['file'])) {
					$upfile = file_upload($_FILES['file']['name'],$_FILES['file']['tmp_name'],"attachment");
					$attachment = $upfile['data']['file'];
				}
				if (isset($_FILES['boleto'])) {
					$upfile = file_upload($_FILES['boleto']['name'],$_FILES['boleto']['tmp_name'],"attachment");					
					$boleto = $upfile['data']['file'];
				}

				if (isset($newdata['status']) && $newdata['status']=="Paid") {
					if (isset($newdata['payment_date'])===false) {
						$newdata['payment_date'] = date("Y-m-d");
					}					
				}

				if (isset($newdata['attachment']) && $newdata['attachment']=="") {
					$newdata['attachment'] = $attachment;
				}

				if (isset($newdata['boleto']) && $newdata['boleto']=="") {
					$newdata['boleto'] = $boleto;
				}

				if (isset($newdata['issued_date']) && isset($newdata['due_date'])) {
					$earlier = new DateTime(date("Y-m-d",strtotime($newdata['issued_date'])));
					$later = new DateTime(date("Y-m-d",strtotime($newdata['due_date'])));
					$newdata['payment_term'] = $later->diff($earlier)->format("%a");
				}				

				if (is_array($id)) {
					foreach ($id as $key => $value) {
						if ($newdata['status']=="Remove") {
							$result = $UM->deleteGlobalById('account_receivables',$value);
						} else {
							if (isset($_POST['invoice_type']) && $_POST['invoice_type']=="consolidated") {
								$result = $UM->updateGlobal('debt_collection',$value,$newdata);
							} else {
								$result = $UM->updateGlobal('account_receivables',$value,$newdata);
							}
						}
					}
				} else {
					if (isset($_POST['payment'])) {
						if ($newdata['payment_type']=="full") {							
							unset($newdata['payment_type']);
							unset($newdata['amount']);
							unset($newdata['notes']);
							if (isset($_POST['invoice_type']) && $_POST['invoice_type']=="consolidated") {
								$invoice = $UM->getGlobalbyId("debt_collection",$id);
								foreach (json_decode($invoice['invoice_items']) as $inv_id) {
									$UM->updateGlobal('account_receivables',$inv_id,$newdata);
								}
								$result = $UM->updateGlobal('debt_collection',$id,$newdata);
							} else {
								$result = $UM->updateGlobal('account_receivables',$id,$newdata);
							}
							$payment_type = "full";
						} else {
							$pp_newdata = [];
							$pp_newdata['uuid'] = hexdec(uniqid());
							$pp_newdata['ar_id'] = $id;
							$pp_newdata['amount'] = $newdata['amount'];
							$pp_newdata['notes'] = $newdata['notes'];
							$pp_newdata['created_by'] = $tokenvalue->uuid;
							$pp_newdata['payment_date'] = $newdata['payment_date'];							
							$result = $UM->addGlobal('partial_payments',$pp_newdata);
							$payment_type = "partial";
						}
					} else {
						$line_items = "";
						if (isset($newdata['description'])) {
							$line_items_arr = [];
							$line_items_not_empty = false;
							for ($i=1; $i <= count($newdata['description']); $i++) { 
								$col = [];
								if ($newdata['price'][$i]!="") {
									$line_items_not_empty = true;
									if (($newdata['price'][$i]*$newdata['quantity'][$i])>0) {
										$col['description'] = $newdata['description'][$i];
										$col['quantity'] = $newdata['quantity'][$i];
										$col['price'] = $newdata['price'][$i];
										$line_items_arr[] = $col;										
									}
								}					
							}
							if ($line_items_not_empty) {
								$line_items = json_encode($line_items_arr);
							}						
							unset($newdata['description']);
							unset($newdata['price']);
							unset($newdata['quantity']);
						}
						
						$newdata['line_items'] = $line_items;
						$result = $UM->updateGlobal('account_receivables',$id,$newdata);
					}
				}
				if($result)
				{
					$return['status'] = true;
					if (isset($postdata['is_deleted'])) {
						if ($postdata['is_deleted']==1) {
							$return['message'] = __("Successfully deactivated invoice",true);
						} else if($postdata['is_deleted']==2 || $newdata['status']=="Remove") {
							$return['message'] = __("Successfully deleted invoice",true);
						} else {
							$return['message'] = __("Successfully reactivated invoice",true);
						}
						
					} else {
						if (isset($newdata['status']) && $newdata['status']=="Paid") {
							if ($payment_type=="partial") {
								$return['message'] = __("Invoice paid partially",true)."!";
							} else {
								$return['message'] = __("Invoice set to paid!",true);
							}
						} else if($newdata['status']=="Remove") { 
							$return['message'] = __("Successfully deleted invoice",true);
						} else {
							$return['message'] = __("Successfully updated invoice",true);	
						}
					}
					
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to update invoice. Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function delete_invoice() {
		if(isset($_POST['id']) && isset($_POST['token']))
		{
			$id = $_POST['id'];
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;
				$result = $UM->deleteGlobalById('account_receivables',$id);
				if($result)
				{
					$return['status'] = true;
					$return['message'] = __("Successfully removed invoice",true);
					
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to remove invoice. Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function bulkReceivablesImport() {		
		if (isset($_POST['token'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel;
				$country = "United States of America";
				if ($payload->role=="client_user") {
					$user_id = $UM->getClientIdbyClientUserId($payload->uuid);
					$userdata = $UM->getUserbyId($user_id);
					$country = __($userdata['country'],true);
				} else {
					$user_id = $payload->uuid;
					$country = __($payload->country,true);
				}				
				$currencyData = $UM->fetchCurrency($country);
				$currency = $currencyData['code'];

				/* Getting file name */
				$filename = $_FILES['file']['name'];			
				
				$uploadOk = 1;
				$fileType = pathinfo($filename,PATHINFO_EXTENSION);	
				$location = "./tmp/INV-".generate_string(16).".".$fileType;
				
				/* Valid Extensions */
				$valid_extensions = array("xlsx");

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

				   	if(move_uploaded_file($_FILES['file']['tmp_name'],$location)){		      
				      	$return['status']=1;
				      	$file_import = $location;
				      	$newData = [];
				      	if ($fileType == "xlsx") {						   
						    if ( $xlsx = SimpleXLSX::parse($file_import) ) {    
						        foreach ($xlsx->rows() as $key => $value) {
						        	if ($key>0) {
						        		$duplicate = 0;
						        		$newVal = [];
						                $newVal['uuid'] = hexdec(uniqid());
						                $newVal['user_id'] = $user_id;
						                $newVal['invoice_no'] = $value[0];
						                $newVal['amount'] = $value[1];

						                $client_business = $UM->checkUserClientsBusinessNameExist(clean($value[2]),$user_id);
						                if ($client_business) {
						                	$newVal['client_id'] = $client_business['uuid'];
						                }
						                
						                if ($value[3]!="") {
						                	$newVal['issued_date'] = date("Y-m-d",strtotime($value[3]));
						                } else {
						                	$newVal['issued_date'] = date("Y-m-d");
						                }
						                
						                if ($value[4]!="") {
						                	$newVal['payment_term'] = $value[4];
						                } else {
						                	$newVal['payment_term'] = 30;
						                }
						                
						                if ($value[5]!="") {
						                	$newVal['due_date'] = date("Y-m-d",strtotime($value[5]));
						                } else {
						                	$newVal['due_date'] = date("Y-m-d",strtotime($value[3]." +".$newVal['payment_term']." days"));
						                }

						                if ($value[6]!="") {
						                	$newVal['discount'] = $value[6];
						                }

						                if ($value[7]!="") {
						                	$newVal['late_fee'] = $value[7];
						                }

						                if ($value[8]!="") {
						                	$newVal['interest'] = $value[8];
						                }
						                
						                $line_items = "";
										$line_items_arr = [];
										$line_items_not_empty = false;
										$line_item = 1;
										$line_item_total = 0;
						                for ($i=9; $i <= 53; $i++) { 
						                	if ($value[$i]!="") {
						                		$line_items_not_empty = true;
						                		if ($line_item == 1) {
						                			$col = [];
							                		$col['description'] = $value[$i];
							                		$line_item = 2;
							                	} else if ($line_item == 2) {
							                		$col['quantity'] = $value[$i];
							                		$line_item = 3;
							                	} else if ($line_item == 3) {
							                		$col['price'] = $value[$i];
							                		$line_item_total += ($col['price']*$col['quantity']);
							                		if (($col['price']*$col['quantity'])>0) {
							                			$line_items_arr[] = $col;
							                		}
							                		$line_item = 1;
							                	}
						                	}
						                }
										
										if ($line_items_not_empty) {
											$line_items = json_encode($line_items_arr);
											$newVal['amount'] = $line_item_total;
										}

										$newVal['line_items'] = $line_items;
						                $pbusinessa = $UM->fetchPrimaryBusinessAccount($user_id);
						                $newVal['business_id'] = $pbusinessa['uuid'];
						                $pbanka = $UM->fetchPrimaryBankAccount($user_id);						                
						                $newVal['bank_account_id'] = $pbanka['uuid'];
						                $newVal['created_by'] = $_SESSION['userdata']['uuid'];
						                $newVal['currency'] = $currency;
						                $newVal['attachment'] = "";
						                $newData[] = $newVal;

						                $checkClientInv = $UM->checkUserInvoiceExist($newVal['invoice_no'],$user_id);
						                if ($checkClientInv) {
						                	unset($newVal['uuid']);
						                	$newUpdateData = [];
						                	foreach ($newVal as $key2 => $value2) {
						                		if ($value2!="") {
						                			$newUpdateData[$key2] = $value2;
						                		}
						                	}
						                	if (isset($newUpdateData['attachment']) && $newUpdateData['attachment']=="") {
						                		$newUpdateData['attachment'] = $checkClientInv['attachment'];
						                	}
						                	$res = $UM->updateGlobal("account_receivables",$checkClientInv['uuid'],$newUpdateData);
						                } else {
						                	$res = $UM->addGlobal("account_receivables",$newVal);
						                	add_lifecycle_default($payload->uuid,$newVal);
						                }
						        	}
						        }
						        $return['data'] = $newData;
						    } else {
						    	$return['status'] = false;
						        $return['message'] = SimpleXLSX::parseError();
						    }
						} else if ($fileType == ".csv") {     
						    $row = 1;
						    if (($handle = fopen($file_import, "r")) !== FALSE) {
						        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
						            $num = count($data);
						            $row++;
						            for ($c=0; $c < $num; $c++) {
						                echo $data[$c] . ", \n";
						            }
						            echo "<hr>";
						        }
						        fclose($handle);
						    } else {
						    	$return['status'] = false;
						        $return['message'] = __("Cannot read file",true);
						    }
						}
						unlink($location);
				   	} else {
				   		$return['status']=false;
						$return['message'] = __("Not uploaded due to an error #",true).$_FILES["file"]["error"];
						echo json_encode($return);
						exit;
				   	}
				}

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


	// --- Open Finance Suppliers --- //
	function add_of_supplier() {
		if (isset($_POST['token']) && isset($_POST['data'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				parse_str($_POST['data'],$postdata);
				$newdata = $postdata;				
				$newdata['uuid'] = hexdec(uniqid());
				$newdata['name'] = strtolower($newdata['name']);
				$newdata['country'] = strtolower($newdata['country']);
				foreach ($newdata as $key => $value) {
					if ($value=="") {
						unset($newdata[$key]);
					}
				}
				$newdata['is_active'] = 1;
				$result = $UM->addGlobal("of_supplier",$newdata);
				if ($result) {
					$return['status'] = true;
					$return['message'] = __("Successfully added supplier",true);
				} else {
					$return['status'] = false;
					$return['message'] = __("Failed to add supplier",true);
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

	function fetch_of_suppliers() {		
		if (isset($_POST['token'])) {						
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				$result = $UM->getGlobal('of_supplier');
				$datatable = [];
				if ($result) {
					foreach ($result as $key => $value) {
						$details = $col = [];
						$col[] = '<label class="label label-primary">'.$value['environment'].'</label>';
						$col[] = ucwords($value['name']);

						if ($value['client_id']!=null || $value['client_id']!="") {
							$details[] = '<b>Client ID:</b> '.encode_str_asterisk($value['client_id']);
						}
						if ($value['client_secret']!=null || $value['client_secret']!="") {
							$details[] = '<b>Client Secret:</b> '.encode_str_asterisk($value['client_secret']);
						}
						if ($value['api_key']!=null || $value['api_key']!="") {
							$details[] = '<b>API Key:</b> '.encode_str_asterisk($value['api_key']);
						}
						if ($value['path']!=null || $value['path']!="") {
							$details[] = '<b>API Path:</b> '.$value['path'];
						}
						if ($value['country']!=null || $value['country']!="") {
							$details[] = '<b>Country:</b> '.ucwords($value['country']);
						}

						$col[] = implode("<br>",$details);
						if ($value['is_active']==0) {
							$status = '<label class="label label-default">Inactive</label>';
						} else {
							$status = '<label class="label label-success">Active</label>';
						}
						$col[] = $status;
						$col[] = '<div class="text-center"><a class="editBtn text-default" data-id="'.$value['uuid'].'"><i class="icon-pencil"></i></a>
										  <a class="deleteBtn mgl-10 text-default" data-id="'.$value['uuid'].'"><i class="icon-trash"></i></a></div>';
						$datatable[] = $col;
					}
				}

				$return['status'] = true;
				$return['datatable'] = $datatable;

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

	function fetch_ofdata() {
		if (isset($_POST['id'])) {
			$UM = new UserModel();
			$resultdata = $UM->getGlobalbyId("of_supplier",$_POST['id']);
			if ($resultdata) {
				$return['status'] = true;
				$resultdata['name'] = ucwords($resultdata['name']);
				$resultdata['country'] = ucwords($resultdata['country']);
				$return['data'] = $resultdata;
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Supplier ID.",true);
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("No Supplier found.",true);
		}
		echo json_encode($return);
		exit;
	}

	function update_of_supplier() {
		if(isset($_POST['data']) && isset($_POST['token']))
		{
			parse_str($_POST['data'],$postdata);
			$id = $postdata['uuid'];
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;
				$newdata = [];			
				unset($postdata['uuid']);
				$newdata = $postdata;
				if (isset($postdata['is_active'])) {
					$newdata['is_active'] = 1;
				} else {
					$newdata['is_active'] = 0;
				}
				$newdata['name'] = strtolower($newdata['name']);
				$newdata['country'] = strtolower($newdata['country']);
				$result = $UM->updateGlobal('of_supplier',$id,$newdata);
				if($result)
				{
					$return['status'] = true;
					if (isset($postdata['is_deleted'])) {
						if ($postdata['is_deleted']==1) {
							$return['message'] = __("Successfully deactivated supplier",true);
						} else if($postdata['is_deleted']==2) {
							$return['message'] = __("Successfully deleted supplier",true);
						} else {
							$return['message'] = __("Successfully reactivated supplier",true);
						}
						
					} else {
						$return['message'] = __("Successfully updated supplier details",true);
					}
					
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to update supplier. Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function delete_of_supplier() {
		if(isset($_POST['id']) && isset($_POST['token']))
		{
			$id = $_POST['id'];
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;
				$newdata['is_deleted'] = 1;
				
				$result = $UM->updateGlobal('of_supplier',$id,$newdata);	
				if($result)
				{
					$return['status'] = true;
					$return['message'] = __("Successfully removed supplier",true);
					
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to delete supplier. Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
			}
			
			echo json_encode($return);
		}
		exit;
	}



	// --- EMAIL TEMPLATES --- //
	function add_template() {
		if (isset($_POST['token']) && isset($_POST['data'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				parse_str($_POST['data'],$postdata);
				$newdata = $postdata;				
				$newdata['uuid'] = hexdec(uniqid());
				$newdata['created_by'] = $payload->uuid;
				unset($newdata['_wysihtml5_mode']);
				$result = $UM->addGlobal("email_templates",$newdata);
				if ($result) {
					$return['status'] = true;
					$return['message'] = __("Successfully added a template",true);
				} else {
					$return['status'] = false;
					$return['message'] = __("Failed to add template",true);
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

	function fetch_templates() {		
		if (isset($_POST['token'])) {						
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				$result = $UM->getGlobal('email_templates');
				if (count($result)>0) {
					if (isset($_POST['data_type']) && $_POST['data_type']=="email_list") {
						$emailtemplates = '';
						if ($_POST['ar_id']) {
							parse_str($_POST['data'],$postdata);

							$invoicedata = $UM->getGlobalbyId("account_receivables",$_POST['ar_id']);
							$businessdata = $UM->getGlobalbyId("business_accounts",$invoicedata['business_id']);
							$bankaccountdata = $UM->getGlobalbyId("bank_accounts",$invoicedata['bank_account_id']);
							$currency = $UM->fetchCurrencyByCode($invoicedata['currency']);
							$senddate = date("Y-m-d");

							if ($postdata['date-radio']=="by day") {
								if ($postdata['sched-date']=="on") {
									$postdata['days'] = 0;
								} else if ($postdata['sched-date']=="after") {
									$_POST['email_type'] = $postdata['trigger_type']="overdue reminder";
								}
								if ($postdata['trigger_type']=="overdue reminder") {
									$senddate = date("Y-m-d",strtotime($invoicedata['due_date']." +".$postdata['days']." days"));
								} else {
									$senddate = date("Y-m-d",strtotime($invoicedata['due_date']." -".$postdata['days']." days"));
								}
							} else {
								$senddate = $postdata['reminder_date'];
								if (strtotime($senddate) > strtotime($invoicedata['due_date'])) {
									$_POST['email_type'] = $postdata['trigger_type']="overdue reminder";
								}
							}							
							
							foreach ($result as $key => $value) {
								if ($value['email_type']==$_POST['email_type'] && $value['is_deleted']==0 && $value['language']==$postdata['language']) {
									$earlier = new DateTime(date("Y-m-d",strtotime($senddate)));
									$later = new DateTime(date("Y-m-d",strtotime($invoicedata['due_date'])));
									$day_diff = $later->diff($earlier)->format("%a");
									
									$subject = str_replace("{{user.business.name}}",$businessdata['business_name'],$value['subject']);
									$subject = str_replace("{{discount}}",$_POST['discount_percentage']."%",$subject);
									$subject = str_replace("{{invoice.number}}","#".$invoicedata['invoice_no'],$subject);
									$subject = str_replace("{{due.date}}",date(DATEFORMAT,strtotime($invoicedata['due_date'])),$subject);
									if ($_POST['paid_by_date']!="") {
										$subject = str_replace("{{new.due.date}}",date(DATEFORMAT,strtotime($_POST['paid_by_date'])),$subject);
									}
									$subject = str_replace("[Service/Product]","<span style='background-color:yellow'>[Service/Product]</span>",$subject);									

									$body = str_replace("\r\n","<br>",$value['body']);
									$body = str_replace("{{user.business.name}}",$businessdata['business_name'],$body);
									$body = str_replace("{{name}}",$invoicedata['client_details']['name'],$body);
									$body = str_replace("{{due.date}}",date(DATEFORMAT,strtotime($invoicedata['due_date'])),$body);
									$body = str_replace("{{invoice.link}}",'<a href="'.SITE_URL.'/invoice/'.$invoicedata['uuid'].'" target="_blank">'.__("View Invoice",true).'</a>',$body);
									$body = str_replace("{{days.until.due}}",$day_diff,$body);
									$body = str_replace("{{discount}}",$_POST['discount_percentage']."%",$body);
									$body = str_replace("{{amount}}",$currency.number_format($invoicedata['amount'],2,DECIMALS,THOUSANDS),$body);
									if ($_POST['discount_value']!="") {
										$body = str_replace("{{savings}}",$currency.number_format($_POST['discount_value'],2,DECIMALS,THOUSANDS),$body);
									}
									if ($_POST['paid_by_date']!="") {
										$body = str_replace("{{new.due.date}}",date(DATEFORMAT,strtotime($_POST['paid_by_date'])),$body);
									}
									if ($_POST['discount_value']!="") {
										$body = str_replace("{{new.total}}",$currency.number_format($invoicedata['amount']-$_POST['discount_value'],2,DECIMALS,THOUSANDS),$body);
										$amount_due = $currency.number_format($invoicedata['amount']-$_POST['discount_value'],2,DECIMALS,THOUSANDS);
									} else {
										$amount_due = $currency.number_format($invoicedata['amount'],2,DECIMALS,THOUSANDS);
									}
									$body = str_replace("[Service/Product]","<span style='background-color:yellow'>[Service/Product]</span>",$body);
									$body = str_replace("[acquired/hired]","<span style='background-color:yellow'>[acquired/hired]</span>",$body);
									$body = str_replace("<h1>","<h1 style='font-size:16px;'>",$body);
									
									$payment_method = "<br>Bank: ".$bankaccountdata['bank_name']."<br>Account Name: ".$bankaccountdata['account_name']."<br>Account No:".$bankaccountdata['account_number'];
									$body = str_replace("{{payment.method}}",$payment_method,$body);

									$emailtemplates .= '<div class="email-template-holder">
									<button class="btn btn-xs btn-primary pull-right emailSelectBtn" data-id="'.$value['uuid'].'">'.__("Select",true).'</button>';
									$emailtemplates .= '<h6><b>'.$subject.'</b></h6>';
									$emailtemplates .= '<table class="email-body_inner" align="center" style="max-width: 570px;padding:10px 0;background-color:#fff;" cellpadding="0" cellspacing="0" role="presentation">
						              <tr>
						                <td>
						                  <div style="padding: 0px;font-size:10px;">
						                    '.$body.'
						                    <br><p>';
						                    if ($invoicedata['attachment']!="") {
						                    	$emailtemplates .= __('Please find attached the invoice, and also below a summary to make the payment process easier for you:',true);
						                    } else {
						                    	$emailtemplates .= __('Please find below a summary to make the payment process easier for you:',true);
						                    }
						                    $emailtemplates .= '</p>
						                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f5f5f5;margin:0;">
						                      <tr>
						                        <td class="attributes_content">
						                          <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
						                            <tr>
						                              <td class="attributes_item" style="width:50%;text-align: center;padding-top:25px;color:#212122;">
						                                <span class="f-fallback">
						                                  <strong>'.__("Amount Due",true).':</strong> '.$amount_due.'
						                                </span>
						                              </td>
						                              <td class="attributes_item" style="text-align: center;padding-top:25px;color:#212122;">
						                                <span class="f-fallback">
						                                  <strong>'.__("Due Date",true).':</strong> '.date(DATEFORMAT,strtotime($invoicedata['due_date'])).'
						                                </span>
						                              </td>
						                            </tr>
						                            </table>
						                        </td>
						                      </tr>
						                    </table>';
						                    $emailtemplates .= '<table align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f5f5f5;margin:0 0 20px;">
							                      <tr>
							                        <td align="center">
							                          <table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation">
							                            <tr>
							                              <td align="center" style="padding: 30px 0;">
							                                <a style="cursor:pointer;text-decoration: none!important;color: #212122!important;border-radius: 3px;padding: 10px 15px;background-color: #c2d5a8;font-size:10px;font-weight:500;">'.__("View Invoice",true).'</a>
							                              </td>
							                            </tr>
							                          </table>
							                        </td>
							                      </tr>
							                    </table>';
						                    
						                    $emailtemplates .= '<p>'.__("After completing the payment, please answer this e-mail and attach the receipt.",true).'</p><p>'.__("Please, let us know if you have any doubt or if we can do anything else to help.",true).'</p>
						                    <p>'.__("Thank you!",true).'</p>
						                    <p>'.__("Best",true).',<br>'.__("Team Kolek",true).'</p>
						                    <table class="body-sub" role="presentation">
						                      <tr>
						                        <td>
						                          <p class="f-fallback sub">'.__("Please ignore this e-mail in case you already paid the invoice",true).'</p>
						                          <p class="f-fallback sub">'.__("If you have any questions about this invoice, simply reply to this email or reach out to our",true).' <a href="mailto:support@wekolek.com" style="color:#98a783;">'.__("support team",true).'</a> '.__("for help",true).'.</p>
						                        </td>
						                      </tr>
						                    </table>
						                  </div>
						                </td>
						              </tr>
						            </table>';
									$emailtemplates .='</div>';
								}
							}
						} else {
							foreach ($result as $key => $value) {
								if ($value['email_type']==$_POST['email_type']) {
									$emailtemplates .= '<div class="email-template-holder">
									<button class="btn btn-xs btn-primary pull-right emailSelectBtn" data-id="'.$value['uuid'].'">'.__("Select",true).'</button>';
									$emailtemplates .= '<h6><b>'.$value['subject'].'</b></h6>';
									$emailtemplates .= str_replace("\r\n","<br>",$value['body']);
									$emailtemplates .='</div>';
								}
							}
						}
						$return['status'] = true;
						$return['emailtemplates'] = $emailtemplates;
					} else {
						$datatable = [];					
						foreach ($result as $key => $value) {
							$is_default = '';
							if ($value['is_default']==1) {
								$is_default = '<label class="mgl-5 label label-primary">'.__("Default",true).'</label>';
							}
							$col = [];
							$col[] = __(ucwords($value['email_type']),true);
							$col[] = '<button class="btn btn-xs btn-primary viewBtn" data-id="'.$value['uuid'].'"><i class="icon-envelope"></i> '.__("View Email",true).'</button>';
							$col[] = ucfirst($value['title']).$is_default;
							if ($value['is_deleted']==0) {
								$col[] = '<label class="label label-success">'.__("Active",true).'</label>';
							} else {
								$col[] = '<label class="label label-default">'.__("Inactive",true).'</label>';
							}
							$col[] = __(ucwords($value["language"]),true);
							$col[] = '<div class="text-center"><a class="editBtn text-default" data-id="'.$value['uuid'].'"><i class="icon-pencil"></i></a>
										  <a class="deleteBtn mgl-10 text-default" data-id="'.$value['uuid'].'"><i class="icon-trash"></i></a></div>';
							$datatable[] = $col;
						}
						$return['status'] = true;
						$return['datatable'] = $datatable;
					}					
				} else {
					$return['status'] = true;
					$return['datatable'] = [];
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

	function fetch_template() {
		if (isset($_POST['token'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				$result = $UM->getGlobalbyId('email_templates',$_POST['id']);
				if (count($result)>0) {
					if (isset($_POST['data_type']) && $_POST['data_type']=="email_list") {
						$emailtemplate = '';
						if ($_POST['ar_id']) {
							parse_str($_POST['data'],$postdata);
							$invoicedata = $UM->getGlobalbyId("account_receivables",$_POST['ar_id']);
							$businessdata = $UM->getGlobalbyId("business_accounts",$invoicedata['business_id']);
							$bankaccountdata = $UM->getGlobalbyId("bank_accounts",$invoicedata['bank_account_id']);
							$currency = $UM->fetchCurrencyByCode($invoicedata['currency']);

							$senddate = date("Y-m-d");

							if ($postdata['date-radio']=="by day") {
								if ($postdata['trigger_type']=="overdue reminder") {
									$senddate = date("Y-m-d",strtotime($invoicedata['due_date']." +".$postdata['days']." days"));
								} else {
									$senddate = date("Y-m-d",strtotime($invoicedata['due_date']." -".$postdata['days']." days"));
								}
							} else {
								$senddate = $postdata['reminder_date'];								
							}

							if ($result['email_type']==$_POST['email_type']) {
								$earlier = new DateTime(date("Y-m-d",strtotime($senddate)));
								$later = new DateTime(date("Y-m-d",strtotime($invoicedata['due_date'])));
								$day_diff = $later->diff($earlier)->format("%a");

								
								$subject = str_replace("{{user.business.name}}",$businessdata['business_name'],$result['subject']);
								$subject = str_replace("{{discount}}",$_POST['discount_percentage']."%",$subject);
								$subject = str_replace("{{invoice.number}}","#".$invoicedata['invoice_no'],$subject);
								$subject = str_replace("{{due.date}}",date(DATEFORMAT,strtotime($invoicedata['due_date'])),$subject);
								if ($_POST['paid_by_date']!="") {
									$subject = str_replace("{{new.due.date}}",date(DATEFORMAT,strtotime($_POST['paid_by_date'])),$subject);
								}

								$body = str_replace("\r\n","<br>",$result['body']);
								$body = str_replace("{{user.business.name}}",$businessdata['business_name'],$body);
								$body = str_replace("{{name}}",$invoicedata['client_details']['name'],$body);
								$body = str_replace("{{due.date}}",date(DATEFORMAT,strtotime($invoicedata['due_date'])),$body);
								$body = str_replace("{{invoice.link}}",'<a href="'.SITE_URL.'/invoice/'.$invoicedata['uuid'].'" target="_blank">'.__("View Invoice",true).'</a>',$body);
								$body = str_replace("{{days.until.due}}",$day_diff,$body);
								$body = str_replace("{{discount}}",$_POST['discount_percentage']."%",$body);
								$body = str_replace("{{amount}}",$currency.number_format($invoicedata['amount'],2,DECIMALS,THOUSANDS),$body);
								if ($_POST['discount_value']!="") {
									$body = str_replace("{{savings}}",$currency.number_format($_POST['discount_value'],2,DECIMALS,THOUSANDS),$body);
								}
								if ($_POST['paid_by_date']!="") {
									$body = str_replace("{{new.due.date}}",date(DATEFORMAT,strtotime($_POST['paid_by_date'])),$body);
								}
								if ($_POST['discount_value']!="") {
									$body = str_replace("{{new.total}}",$currency.number_format($invoicedata['amount']-$_POST['discount_value'],2,DECIMALS,THOUSANDS),$body);
								}
								
								$payment_method = "<br>Bank: ".$bankaccountdata['bank_name']."<br>Account Name: ".$bankaccountdata['account_name']."<br>Account No:".$bankaccountdata['account_number'];
								$body = str_replace("{{payment.method}}",$payment_method,$body);

								$emailtemplate .= '<div class="form-group"><label>'.__("Subject Line",true).'</label><input name="subject" class="form-control" value="'.$subject.'"/>';
								$emailtemplate .= '</div><div class="form-group"><label>'.__("Email Body",true).'</label><textarea class="wysihtml5 wysihtml5-default form-control" name="email_body">';
								$emailtemplate .= $body;
								$emailtemplate .='</textarea></div>';
							}
						} else {
							if ($value['email_type']==$_POST['email_type']) {
								$emailtemplate .= '<div class="email-template-holder">
								<button class="btn btn-xs btn-primary pull-right emailSelectBtn" data-id="'.$result['uuid'].'">'.__("Select",true).'</button>';
								$emailtemplate .= '<h6><b>'.$result['subject'].'</b></h6>';
								$emailtemplate .= str_replace("\r\n","<br>",$result['body']);
								$emailtemplate .='</div>';
							}
						}
						
						$return['status'] = true;
						$return['emailtemplate'] = $emailtemplate;
						$return['body'] = $body;
						$return['subject'] = $subject;
					}
					
				} else {
					$return['status'] = true;
					$return['datatable'] = [];
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

	function fetch_preview_email_default() {
		if (isset($_POST['token'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();				
				parse_str($_POST['data'],$postdata);				
				$invoicedata = $UM->getGlobalbyId("account_receivables",$_POST['ar_id']);
				$reminder_date = date("Y-m-d");
				$total_updated_amount = 0;

				if ($invoicedata['currency']=="BRL") {
					$email_decimals = ",";
					$email_thousands = ".";
				} else {
					$email_decimals = ".";
					$email_thousands = ",";
				}				

				if (isset($postdata['date-radio'])) {
					if ($postdata['date-radio']=="specific") {					
						if ($postdata['reminder_date'] > $invoicedata['due_date']) {
							$email_type = "overdue reminder";
						} else {
							$email_type = $postdata['trigger_type'];
						}
						$reminder_date = $postdata['reminder_date'];
					} else if ($postdata['date-radio']=="by day") {
						if ($postdata["sched-date"]=="before") {
							$reminder_date = date("Y-m-d",strtotime($invoicedata['due_date']." -".$postdata['days']." days"));
							$email_type = $postdata['trigger_type'];
						} else if ($postdata["sched-date"]=="on") {
							$reminder_date = date("Y-m-d",strtotime($invoicedata['due_date']));
							$email_type = $postdata['trigger_type'];
						} else if ($postdata["sched-date"]=="after") {
							$reminder_date = date("Y-m-d",strtotime($invoicedata['due_date']." +".$postdata['days']." days"));
							$email_type = "overdue reminder";
						}										
					}
				} else {
					if ($postdata['reminder_date'] > $invoicedata['due_date']) {
						$email_type = "overdue reminder";
					} else {
						$email_type = $postdata['trigger_type'];
					}
					$reminder_date = $postdata['reminder_date'];	
				}
				
				if (LANG=="BR") {
					$lang = "portuguese";
				} else {
					$lang = "english";
				}
					
				$result = $UM->fetchDefaultEmailTemplate($email_type,$lang);

				//Calculate Updated Amount
				$pp_val = 0;
				$partial_payments = $UM->fetchInvoicePayments($_POST['ar_id']);
				if (count($partial_payments)>0) {
					foreach ($partial_payments as $pp) {
						$pp_val += $pp['amount'];
					}
				}

				if (isset($invoicedata['discount']) && $invoicedata['discount']!=null) {
		           $discounts = $invoicedata['discount'];
		        } else {
		           $discounts = 0;
		        }
		        if (isset($invoicedata['interest']) && $invoicedata['interest']!=null && (date("Y-m-d") > date("Y-m-d",strtotime($invoicedata['due_date'])))) {
		           $interest = $invoicedata['interest'];
		        } else {
		           $interest = 0;
		        }   
		        if (isset($invoicedata['late_fee']) && $invoicedata['late_fee']!=null && (date("Y-m-d") > date("Y-m-d",strtotime($invoicedata['due_date'])))) {
		           $late_fee = $invoicedata['late_fee'];
		        } else {
		           $late_fee = 0;
		        }			        

				$subtotal = $invoicedata['amount'];
		        $discounts = ($subtotal*($discounts/100));
		        $late_fee = ($subtotal*($late_fee/100));

		        $earlier = new DateTime(date("Y-m-d"));
		        $later = new DateTime(date("Y-m-d",strtotime($invoicedata['due_date'])));
		        $day_overdue = $later->diff($earlier)->format("%a");
		        $interest = (($interest/100)/30) * $day_overdue * $subtotal;
		        $total_updated_amount = $subtotal - $pp_val - $discounts + $late_fee + $interest;

				if ($result==true && count($result)>0) {
					if (isset($_POST['data_type']) && $_POST['data_type']=="email_list") {
						$emailtemplate = '';
						if ($_POST['ar_id']) {
							
							$businessdata = $UM->getGlobalbyId("business_accounts",$invoicedata['business_id']);
							$bankaccountdata = $UM->getGlobalbyId("bank_accounts",$invoicedata['bank_account_id']);

							$currency = $UM->fetchCurrencyByCode($invoicedata['currency']);
							$earlier = new DateTime(date("Y-m-d",strtotime($reminder_date)));
							$later = new DateTime(date("Y-m-d",strtotime($invoicedata['due_date'])));
							$day_diff = $later->diff($earlier)->format("%a");

							
							$subject = str_replace("{{user.business.name}}",$businessdata['business_name'],$result['subject']);
							$subject = str_replace("{{discount}}",$_POST['discount_percentage']."%",$subject);
							$subject = str_replace("{{invoice.number}}","#".$invoicedata['invoice_no'],$subject);
							$subject = str_replace("{{due.date}}",date(DATEFORMAT,strtotime($invoicedata['due_date'])),$subject);
							if ($_POST['paid_by_date']!="") {
								$subject = str_replace("{{new.due.date}}",date(DATEFORMAT,strtotime($_POST['paid_by_date'])),$subject);
							}

							$body = str_replace("\r\n","<br>",$result['body']);
							$body = str_replace("{{user.business.name}}",$businessdata['business_name'],$body);
							if ($invoicedata['client_details']['name']=="") {
								$body = str_replace(" {{name}}","",$body);
							} else {
								$body = str_replace("{{name}}",$invoicedata['client_details']['name'],$body);	
							}
							
							$body = str_replace("{{due.date}}",date(DATEFORMAT,strtotime($invoicedata['due_date'])),$body);
							$body = str_replace("{{invoice.link}}",'<a href="'.SITE_URL.'/invoice/'.$invoicedata['uuid'].'" target="_blank">'.__("View Invoice",true).'</a>',$body);
							$body = str_replace("{{days.until.due}}",$day_diff,$body);
							$body = str_replace("{{discount}}",$_POST['discount_percentage']."%",$body);
							$body = str_replace("{{amount}}",$currency.number_format($invoicedata['amount'],2,$email_decimals,$email_thousands),$body);
							$body = str_replace(" 0&nbsp;days"," until today",$body);
							$body = str_replace(" 1&nbsp;days"," 1 day",$body);
							$body = str_replace("0&nbsp;days","until today",$body);
							$body = str_replace("1&nbsp;days","1 day",$body);
							$body = str_replace("<b>0</b><span>&nbsp;<b>dias</b>","<b>até hoje</b>",$body);
							$body = str_replace("<b>1</b><span>&nbsp;<b>dias</b>","<b>1 dia</b>",$body);


							if ($_POST['discount_value']!="") {
								$body = str_replace("{{savings}}",$currency.number_format($_POST['discount_value'],2,$email_decimals,$email_thousands),$body);
							}							

							if ($_POST['paid_by_date']!="") {
								$body = str_replace("{{new.due.date}}",date(DATEFORMAT,strtotime($_POST['paid_by_date'])),$body);
							}
							if ($_POST['discount_value']!="" && $_POST['email_type']=="incentive") {
								$body = str_replace("{{new.total}}",$currency.number_format($invoicedata['amount']-$_POST['discount_value'],2,$email_decimals,$email_thousands),$body);

								$total_updated_amount -= $_POST['discount_value'];

								$amount_due = $currency.number_format($total_updated_amount,2,$email_decimals,$email_thousands);
							} else {
								$amount_due = $currency.number_format($total_updated_amount,2,$email_decimals,$email_thousands);
							}
							
							$payment_method = "<br>".__('Bank Name',true).": ".$bankaccountdata['bank_name']."<br>".__('Account Name',true).": ".$bankaccountdata['account_name']."<br>".__('Account No',true).":".$bankaccountdata['account_number'];
							$body = str_replace("{{payment.method}}",$payment_method,$body);

							$due = $invoicedata['due_date'];
							$now = date("Y-m-d");

							$due_d_text = "left";
							if ($email_type=="overdue reminder") {
							    $is_overdue = '<tr>
							                    <td colspan=2 class="attributes_item" style="text-align: center;padding-top:25px;color:#F44336;font-size: 32px;"><b>'.__("OVERDUE",true).'</b></td>
							                  </tr>';
							} else {
							    $is_overdue = '';
							}
							$supplierLogo = "";
							if ($businessdata['business_logo']!="") {
							    $supplierLogo = '<img src="'.$businessdata['business_logo'].'" alt="'.$businessdata['business_name'].'" style="max-height:120px;"/>';
							}
							$supplierName = $businessdata['business_name'];
							$supplierNumber = $businessdata['business_no'];
							$supplierAccountName = $bankaccountdata['account_name'];
							$supplierBankName = $bankaccountdata['bank_name'];
							$supplierAccountNumber = $bankaccountdata['account_number'];
							$supplierPixNumber = $bankaccountdata['pix_number'];

							$name = $invoicedata['client_details']['name'];

							if ($email_type=="incentive") {
								$due_date = date(DATEFORMAT,strtotime($_POST['paid_by_date']));
							} else {
								$due_date = date(DATEFORMAT,strtotime($invoicedata['due_date']));	
							}

							$amount = $currency.number_format($invoicedata['amount'],2,$email_decimals,$email_thousands);


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
							                  <div style="text-align:center;">'.$supplierLogo.'</div>
							                    '.$body.'
							                    <br><p>';
					                    if ($invoicedata['attachment']!="") {
					                    	$html .= __("Please find attached the invoice, and also below a summary to make the payment process easier for you",true).':';
					                    } else {
					                    	$html .= __("Please find below a summary to make the payment process easier for you:",true);
					                    }
					                    $html .= '</p>
							                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f5f5f5;margin:0;">
							                      '.$is_overdue.'
							                      <tr>
							                        <td class="attributes_content">
							                          <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
							                            <tr>
							                              <td class="attributes_item" style="width:50%;text-align: center;padding-top:25px;color:#212122;">
							                                <span class="f-fallback">
							                                  <strong>'.__("Amount Due",true).':</strong> '.$amount_due.'
							                                </span>
							                              </td>
							                              <td class="attributes_item" style="text-align: center;padding-top:25px;color:#212122;">
							                                <span class="f-fallback">
							                                  <strong>'.__("Due Date",true).':</strong> '.$due_date.'
							                                </span>
							                              </td>
							                            </tr></table>
							                        </td>
							                      </tr>
							                    </table>';
							                    $html .= '<table align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="height:100px;background-color: #f5f5f5;margin:0 0 20px;padding: 40px 0;">
								                      <tr>
								                        <td align="center">
								                          <table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation">
								                            <tr>
								                              <td align="center">
								                                <a href="/p/'.$invoicedata['uuid'].'" target="_blank" style="text-decoration: none!important;color: #212122!important;border-radius: 3px;padding: 14px 20px;background-color: #c2d5a8;font-weight:500;">'.__("View Invoice",true).'</a>
								                              </td>
								                            </tr>
								                          </table>
								                        </td>
								                      </tr>
								                    </table>';
							                    
							                    $html .= '<p>'.__("After completing the payment, please answer this e-mail and attach the receipt.",true).'</p>';

							                    if ($email_type=="overdue reminder") {
							                    	$html .= '<p>'.__("It is important to remember that non-payment of this debt can lead to a protest at the notary and restrictive credit registration.",true).'</p><p>'.__("If we do not receive a response within 3 days of receiving this email, active services will be blocked.",true).'</p>';
							                    } else {
							                    	$html .= '<p>'.__("Please, let us know if you have any doubt or if we can do anything else to help.",true).'</p>';	
							                    }							                    

							                    $html .= '<p>'.__("Thank you!",true).'</p>
							                    <p>'.__("Best",true).',<br>'.__("Team Kolek",true).'</p>
							                    <table class="body-sub" role="presentation">
							                      <tr>
							                        <td>
							                          <p class="f-fallback sub">'.__("Please ignore this e-mail in case you already paid the invoice",true).'</p>
							                          <p class="f-fallback sub">'.__("If you have any questions about this invoice, simply reply to this email or reach out to our",true).' <a href="mailto:support@wekolek.com" style="color:#98a783;">'.__("support team",true).'</a> '.__("for help",true).'.</p>
							                        </td>
							                      </tr>
							                    </table>
							                  </div>
							                </td>
							              </tr>
							              <tr>
							                <td class="text-center" style="padding:0px 20px 20px 20px;text-align: center;;">
							                  <a href="https://wekolek.com/" style="text-decoration: none;color: #a8aaaf;font-weight: 500;font-size: 12px;line-height: 1.4 !important;">
							                  '.__("If you are also tired of chasing clients for payments",true).', <br>'.__("Click Here and let KoleK do that work for you!",true).' <br>
							                  '.__("That way, you can spend less time worrying about your finances and <br>more time doing what really matters: running your business.",true).'
							                </a>
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
							                  <p style="color: #a8aaaf;font-size: 13px;">
							                    '.CORPORATE_NAME.'
							                    <br>'.str_replace(", SINGAPORE","<br>SINGAPORE",FOOTER_ADDRESS).'
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

							$emailtemplate .= $html;
						} else {
							if ($value['email_type']==$_POST['email_type']) {
								$emailtemplate .= '<div class="email-template-holder">
								<button class="btn btn-xs btn-primary pull-right emailSelectBtn" data-id="'.$result['uuid'].'">'.__("Select",true).'</button>';
								$emailtemplate .= '<h6><b>'.$result['subject'].'</b></h6>';
								$emailtemplate .= str_replace("\r\n","<br>",$result['body']);
								$emailtemplate .='</div>';
							}
						}
						
						$return['status'] = true;
						$return['emailtemplate'] = $emailtemplate;
					}
					
				} else {
					$return['status'] = true;
					$return['datatable'] = [];
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

	function fetch_preview_email_default_collection() {
		if (isset($_POST['token'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();				
				parse_str($_POST['data'],$postdata);				
				$invoicedata = $UM->getGlobalbyId("debt_collection",$_POST['ar_id']);

				$inv_id = $_POST['ar_id'];				
				$invoicedata['invoice_no'] = "1".sprintf('%06d', $invoicedata['id']);
				$reminder_date = date("Y-m-d");				
				$email_type = $_POST['email_type'];				
				
				if (LANG=="BR") {
					$lang = "portuguese";
				} else {
					$lang = "english";
				}
					
				$result = $UM->fetchDefaultEmailTemplate($email_type,$lang);
				if ($result==true && count($result)>0) {
					if (isset($_POST['data_type']) && $_POST['data_type']=="email_list") {
						$emailtemplate = '';
						if ($_POST['ar_id']) {
							
							$businessdata = $UM->getGlobalbyId("business_accounts",$invoicedata['business_id']);
							$bankaccountdata = $UM->getGlobalbyId("bank_accounts",$invoicedata['bank_account_id']);
							$invoicedata['client_details'] = $UM->getGlobalbyId("client_lists",$invoicedata['client_id']);
							$invoicePayments = $UM->fetchInvoicePayments($inv_id);
							$total_partial_collection =0;
							if (count($invoicePayments)>0) {
								foreach ($invoicePayments as $pp) {
									$total_partial_collection += $pp['amount'];
								}
							}
							$currency = "USD";
							$total_updated_amount = 0;
							foreach (json_decode($invoicedata['invoice_items']) as $line_inv_id) {
								$invoice = $UM->getGlobalbyId('account_receivables',$line_inv_id);
								$currency = $invoice['currency'];										
								$pp_val = 0;
								$partial_payments = $UM->fetchInvoicePayments($line_inv_id);
								if (count($partial_payments)>0) {
									foreach ($partial_payments as $pp) {
										$pp_val += $pp['amount'];
									}
								}
								$partialPayments[$line_inv_id] = $pp_val;

								if (isset($invoice['discount']) && $invoice['discount']!=null) {
						           $discounts = $invoice['discount'];
						        } else {
						           $discounts = 0;
						        }
						        if (isset($invoice['interest']) && $invoice['interest']!=null && (date("Y-m-d") > date("Y-m-d",strtotime($invoice['due_date'])))) {
						           $interest = $invoice['interest'];
						        } else {
						           $interest = 0;
						        }   
						        if (isset($invoice['late_fee']) && $invoice['late_fee']!=null && (date("Y-m-d") > date("Y-m-d",strtotime($invoice['due_date'])))) {
						           $late_fee = $invoice['late_fee'];
						        } else {
						           $late_fee = 0;
						        }			        
								$subtotal = $invoice['amount'];
						        $discounts = ($subtotal*($discounts/100));
						        $late_fee = ($subtotal*($late_fee/100));
						        $earlier = new DateTime(date("Y-m-d"));
						        $later = new DateTime(date("Y-m-d",strtotime($invoice['due_date'])));
						        $day_overdue = $later->diff($earlier)->format("%a");
						        $interest = (($interest/100)/30) * $day_overdue * $subtotal;
						        $total_updated_amount += $subtotal - $pp_val - $discounts + $late_fee + $interest;

						        $invoice['discount'] = $discounts;
						        $invoice['late_fee'] = $late_fee;
						        $invoice['interest'] = $interest;
						        $invoice['partial_payments'] = $pp_val;
						        $invoice['updated_amount'] = $subtotal - $pp_val - $discounts + $late_fee + $interest;
						        $inv_items[] = $invoice;
							}
							$data['currency'] = $UM->fetchCurrencyByCode($currency);

							$invoicedata['amount'] = $total_updated_amount - $total_partial_collection;


							if ($currency=="BRL") {
								$email_decimals = ",";
								$email_thousands = ".";
							} else {
								$email_decimals = ".";
								$email_thousands = ",";
							}

							$currency = $UM->fetchCurrencyByCode($currency);
							$subject = str_replace("{{user.business.name}}",$businessdata['business_name'],$result['subject']);
							$subject = str_replace("{{discount}}",$_POST['discount_percentage']."%",$subject);
							$subject = str_replace("{{invoice.number}}","#".$invoicedata['invoice_no'],$subject);
							if ($_POST['paid_by_date']!="") {
								$subject = str_replace("{{new.due.date}}",date(DATEFORMAT,strtotime($_POST['paid_by_date'])),$subject);
							}

							$body = str_replace("\r\n","<br>",$result['body']);
							$body = str_replace("{{user.business.name}}",$businessdata['business_name'],$body);
							if ($invoicedata['client_details']['name']=="") {
								$body = str_replace(" {{name}}","",$body);
							} else {
								$body = str_replace("{{name}}",$invoicedata['client_details']['name'],$body);	
							}
							
							$body = str_replace("{{invoice.link}}",'<a href="'.SITE_URL.'/invoice/'.$invoicedata['uuid'].'" target="_blank">'.__("View Invoice",true).'</a>',$body);
							$body = str_replace("{{discount}}",$_POST['discount_percentage']."%",$body);
							$body = str_replace("{{amount}}",$currency.number_format($invoicedata['amount'],2,$email_decimals,$email_thousands),$body);
							$body = str_replace(" 0&nbsp;days"," until today",$body);
							$body = str_replace(" 1&nbsp;days"," 1 day",$body);
							$body = str_replace("<b>0</b><span>&nbsp;<b>dias</b>","<b>até hoje</b>",$body);
							$body = str_replace("<b>1</b><span>&nbsp;<b>dias</b>","<b>1 dia</b>",$body);


							if ($_POST['discount_value']!="") {
								$body = str_replace("{{savings}}",$currency.number_format($_POST['discount_value'],2,$email_decimals,$email_thousands),$body);
							}

							if ($_POST['paid_by_date']!="") {
								$body = str_replace("{{new.due.date}}",date(DATEFORMAT,strtotime($_POST['paid_by_date'])),$body);
							}
							if ($_POST['discount_value']!="" && $_POST['email_type']=="incentive") {
								$body = str_replace("{{new.total}}",$currency.number_format($invoicedata['amount']-$_POST['discount_value'],2,DECIMALS,THOUSANDS),$body);
								$amount_due = $currency.number_format($invoicedata['amount']-$_POST['discount_value'],2,$email_decimals,$email_thousands);
							} else {
								$amount_due = $currency.number_format($invoicedata['amount'],2,$email_decimals,$email_thousands);
							}
							
							$payment_method = "<br>".__('Bank Name',true).": ".$bankaccountdata['bank_name']."<br>".__('Account Name',true).": ".$bankaccountdata['account_name']."<br>".__('Account No',true).":".$bankaccountdata['account_number'];
							$body = str_replace("{{payment.method}}",$payment_method,$body);
							$now = date("Y-m-d");

							$due_d_text = "left";
							if ($email_type=="overdue reminder" || $email_type=="reminder debt collection" || $email_type=="debt collection negotiation") {
							    $is_overdue = '<tr>
							                    <td colspan=2 class="attributes_item" style="text-align: center;padding-top:25px;color:#F44336;font-size: 32px;"><b>'.__("OVERDUE",true).'</b></td>
							                  </tr>';
							} else {
							    $is_overdue = '';
							}
							$supplierLogo = "";
							if ($businessdata['business_logo']!="") {
							    $supplierLogo = '<img src="'.$businessdata['business_logo'].'" alt="'.$businessdata['business_name'].'" style="max-height:120px;"/>';
							}
							$supplierName = $businessdata['business_name'];
							$supplierNumber = $businessdata['business_no'];
							$supplierAccountName = $bankaccountdata['account_name'];
							$supplierBankName = $bankaccountdata['bank_name'];
							$supplierAccountNumber = $bankaccountdata['account_number'];
							$supplierPixNumber = $bankaccountdata['pix_number'];

							$name = $invoicedata['client_details']['name'];							

							$amount = $currency.number_format($invoicedata['amount'],2,$email_decimals,$email_thousands);


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
							                  <div style="text-align:center;">'.$supplierLogo.'</div>
							                    '.$body;
							            $html .= '<p style="margin-top:10px;">'.__("Click the link to access the breakdown of overdue invoices and also see the payment options to settle these pending issues quickly and easily.",true).'</p>';

					                    $html .= '
							                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f5f5f5;margin:0;">
							                      '.$is_overdue.'
							                      <tr>
							                        <td class="attributes_content">
							                          <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
							                            <tr>
							                              <td class="attributes_item" style="text-align: center;padding-top:25px;color:#212122;">
							                                <span class="f-fallback">
							                                  <strong>'.__("Amount Due",true).':</strong> '.$amount_due.'
							                                </span>
							                              </td>
							                            </tr></table>
							                        </td>
							                      </tr>
							                    </table>';
							                    $html .= '<table align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="height:100px;background-color: #f5f5f5;margin:0 0 20px;padding: 40px 0;">
								                      <tr>
								                        <td align="center">
								                          <table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation">
								                            <tr>
								                              <td align="center">
								                                <a href="/p/'.$invoicedata['uuid'].'" target="_blank" style="text-decoration: none!important;color: #212122!important;border-radius: 3px;padding: 14px 20px;background-color: #c2d5a8;font-weight:500;">'.__("View Invoice",true).'</a>
								                              </td>
								                            </tr>
								                          </table>
								                        </td>
								                      </tr>
								                    </table>';
								                $html .= '<p>'.__("After completing the payment, please answer this e-mail and attach the receipt.",true).'</p>';
							                    if ($email_type=="debt collection negotiation") {
							                    	$html .= '<p>'.__("We know that having debts is bad, so we would like to help you settle this debt by giving the option to request a renegotiation - just click on the link below and press the 'Request Renegotiation' button and we will contact you shortly.",true).'</p>';
							                    	$html .= '<p>'.__("It is important to remember that non-payment of this debt can lead to a protest at the notary and restrictive credit registration.",true).'</p><p>'.__("If we do not receive a response within 3 days of receiving this email, active services will be blocked.",true).'</p>';
							                    } else {
							                    	$html .= '<p>'.__("Please, let us know if you have any doubt or if we can do anything else to help.",true).'</p>';
							                    }
							                    $html .= '<p>'.__("Thank you!",true).'</p>
							                    <p>'.__("Best",true).',<br>'.__("Team Kolek",true).'</p>
							                    <table class="body-sub" role="presentation">
							                      <tr>
							                        <td>
							                          <p class="f-fallback sub">'.__("Please ignore this e-mail in case you already paid the invoice",true).'</p>
							                          <p class="f-fallback sub">'.__("If you have any questions about this invoice, simply reply to this email or reach out to our",true).' <a href="mailto:support@wekolek.com" style="color:#98a783;">'.__("support team",true).'</a> '.__("for help",true).'.</p>
							                        </td>
							                      </tr>
							                    </table>
							                  </div>
							                </td>
							              </tr>
							              <tr>
							                <td class="text-center" style="padding:0px 20px 20px 20px;text-align: center;;">
							                  <a href="https://wekolek.com/" style="text-decoration: none;color: #a8aaaf;font-weight: 500;font-size: 12px;line-height: 1.4 !important;">
							                  '.__("If you are also tired of chasing clients for payments",true).', <br>'.__("Click Here and let KoleK do that work for you!",true).' <br>
							                  '.__("That way, you can spend less time worrying about your finances and <br>more time doing what really matters: running your business.",true).'
							                </a>
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
							                  <p style="color: #a8aaaf;font-size: 13px;">
							                    '.CORPORATE_NAME.'
							                    <br>'.str_replace(", SINGAPORE","<br>SINGAPORE",FOOTER_ADDRESS).'
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

							$emailtemplate .= $html;
						} else {
							if ($value['email_type']==$_POST['email_type']) {
								$emailtemplate .= '<div class="email-template-holder">
								<button class="btn btn-xs btn-primary pull-right emailSelectBtn" data-id="'.$result['uuid'].'">'.__("Select",true).'</button>';
								$emailtemplate .= '<h6><b>'.$result['subject'].'</b></h6>';
								$emailtemplate .= str_replace("\r\n","<br>",$result['body']);
								$emailtemplate .='</div>';
							}
						}
						
						$return['status'] = true;
						$return['emailtemplate'] = $emailtemplate;
					}
					
				} else {
					$return['status'] = true;
					$return['datatable'] = [];
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

	function fetch_preview_email() {
		if (isset($_POST['token'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();				
				$result = $UM->getGlobalbyId('email_templates',$_POST['id']);
				if (count($result)>0) {
					if (isset($_POST['data_type']) && $_POST['data_type']=="email_list") {
						$emailtemplate = '';
						if ($_POST['ar_id']) {
							$invoicedata = $UM->getGlobalbyId("account_receivables",$_POST['ar_id']);
							$businessdata = $UM->getGlobalbyId("business_accounts",$invoicedata['business_id']);
							$bankaccountdata = $UM->getGlobalbyId("bank_accounts",$invoicedata['bank_account_id']);

							$currency = $UM->fetchCurrencyByCode($invoicedata['currency']);

							if ($result['email_type']==$_POST['email_type']) {
								$earlier = new DateTime(date("Y-m-d"));
								$later = new DateTime(date("Y-m-d",strtotime($invoicedata['due_date'])));
								$day_diff = $later->diff($earlier)->format("%a");

								
								$subject = str_replace("{{user.business.name}}",$businessdata['business_name'],$_POST['subject']);
								$subject = str_replace("{{discount}}",$_POST['discount_percentage']."%",$subject);
								$subject = str_replace("{{invoice.number}}","#".$invoicedata['invoice_no'],$subject);
								$subject = str_replace("{{due.date}}",date(DATEFORMAT,strtotime($invoicedata['due_date'])),$subject);
								if ($_POST['paid_by_date']!="") {
									$subject = str_replace("{{new.due.date}}",date(DATEFORMAT,strtotime($_POST['paid_by_date'])),$subject);
								}

								$body = str_replace("\r\n","<br>",$_POST['body']);
								$body = str_replace("{{user.business.name}}",$businessdata['business_name'],$body);
								$body = str_replace("{{name}}",$invoicedata['client_details']['name'],$body);
								$body = str_replace("{{due.date}}",date(DATEFORMAT,strtotime($invoicedata['due_date'])),$body);
								$body = str_replace("{{invoice.link}}",'<a href="'.SITE_URL.'/invoice/'.$invoicedata['uuid'].'" target="_blank">'.__("View Invoice",true).'</a>',$body);
								$body = str_replace("{{days.until.due}}",$day_diff,$body);
								$body = str_replace("{{discount}}",$_POST['discount_percentage']."%",$body);
								$body = str_replace("{{amount}}",$currency.number_format($invoicedata['amount'],2,DECIMALS,THOUSANDS),$body);
								$body = str_replace("[Service/Product]","<span style='background-color:yellow'>[Service/Product]</span>",$body);
								$body = str_replace("[acquired/hired]","<span style='background-color:yellow'>[acquired/hired]</span>",$body);


								if ($_POST['discount_value']!="") {
									$body = str_replace("{{savings}}",$currency.number_format($_POST['discount_value'],2,DECIMALS,THOUSANDS),$body);
								}


								if ($_POST['paid_by_date']!="") {
									$body = str_replace("{{new.due.date}}",date(DATEFORMAT,strtotime($_POST['paid_by_date'])),$body);
								}
								if ($_POST['discount_value']!="" && $_POST['email_type']=="incentive") {
									$body = str_replace("{{new.total}}",$currency.number_format($invoicedata['amount']-$_POST['discount_value'],2,DECIMALS,THOUSANDS),$body);
									$amount_due = $currency.number_format($invoicedata['amount']-$_POST['discount_value'],2,DECIMALS,THOUSANDS);
								} else {
									$amount_due = $currency.number_format($invoicedata['amount'],2,DECIMALS,THOUSANDS);
								}
								
								$payment_method = "<br>".__('Bank Name',true).": ".$bankaccountdata['bank_name']."<br>".__('Account Name',true).": ".$bankaccountdata['account_name']."<br>".__('Account No',true).":".$bankaccountdata['account_number'];
								$body = str_replace("{{payment.method}}",$payment_method,$body);

								$due = $invoicedata['due_date'];
								$now = date("Y-m-d");

								$due_d_text = "left";
								if ($due < $now) {
								    $is_overdue = '<tr>
								                    <td colspan=2 class="attributes_item" style="text-align: center;padding-top:25px;color:#F44336;font-size: 32px;"><b>'.__("OVERDUE",true).'</b></td>
								                  </tr>';
								} else {
								    $is_overdue = '';
								}
								$supplierLogo = "";
								if ($businessdata['business_logo']!="") {
								    $supplierLogo = '<img src="'.$businessdata['business_logo'].'" alt="'.$businessdata['business_name'].'" style="max-height:120px;"/>';
								}
								$supplierName = $businessdata['business_name'];
								$supplierNumber = $businessdata['business_no'];
								$supplierAccountName = $bankaccountdata['account_name'];
								$supplierBankName = $bankaccountdata['bank_name'];
								$supplierAccountNumber = $bankaccountdata['account_number'];
								$supplierPixNumber = $bankaccountdata['pix_number'];

								$name = $invoicedata['client_details']['name'];
								$due_date = date(DATEFORMAT,strtotime($invoicedata['due_date']));

								$amount = $currency.number_format($invoicedata['amount'],2,DECIMALS,THOUSANDS);


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
								                  <div style="text-align:center;">'.$supplierLogo.'</div>
								                    '.$body.'
								                    <br><p>';
						                    if ($invoicedata['attachment']!="") {
						                    	$html .= __("Please find attached the invoice, and also below a summary to make the payment process easier for you",true).':';
						                    } else {
						                    	$html .= __("Please find below a summary to make the payment process easier for you:",true);
						                    }
						                    $html .= '</p>
								                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f5f5f5;margin:0;">
								                      '.$is_overdue.'
								                      <tr>
								                        <td class="attributes_content">
								                          <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
								                            <tr>
								                              <td class="attributes_item" style="width:50%;text-align: center;padding-top:25px;color:#212122;">
								                                <span class="f-fallback">
								                                  <strong>'.__("Amount Due",true).':</strong> '.$amount_due.'
								                                </span>
								                              </td>
								                              <td class="attributes_item" style="text-align: center;padding-top:25px;color:#212122;">
								                                <span class="f-fallback">
								                                  <strong>'.__("Due Date",true).':</strong> '.$due_date.'
								                                </span>
								                              </td>
								                            </tr></table>
								                        </td>
								                      </tr>
								                    </table>';
								                    $html .= '<table align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="height:100px;background-color: #f5f5f5;margin:0 0 20px;padding: 40px 0;">
									                      <tr>
									                        <td align="center">
									                          <table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation">
									                            <tr>
									                              <td align="center">
									                                <a href="/p/'.$invoicedata['uuid'].'" target="_blank" style="text-decoration: none!important;color: #212122!important;border-radius: 3px;padding: 14px 20px;background-color: #c2d5a8;font-weight:500;">'.__("View Invoice",true).'</a>
									                              </td>
									                            </tr>
									                          </table>
									                        </td>
									                      </tr>
									                    </table>';
								                    
								                    $html .= '<p>'.__("After completing the payment, please answer this e-mail and attach the receipt.",true).'</p><p>'.__("Please, let us know if you have any doubt or if we can do anything else to help.",true).'</p>
								                    <p>'.__("Thank you!",true).'</p>
								                    <p>'.__("Best",true).',<br>'.__("Team Kolek",true).'</p>
								                    <table class="body-sub" role="presentation">
								                      <tr>
								                        <td>
								                          <p class="f-fallback sub">'.__("Please ignore this e-mail in case you already paid the invoice",true).'</p>
								                          <p class="f-fallback sub">'.__("If you have any questions about this invoice, simply reply to this email or reach out to our",true).' <a href="mailto:support@wekolek.com" style="color:#98a783;">'.__("support team",true).'</a> '.__("for help",true).'.</p>
								                        </td>
								                      </tr>
								                    </table>
								                  </div>
								                </td>
								              </tr>
								              <tr>
								                <td class="text-center" style="padding:0px 20px 20px 20px;text-align: center;;">
								                  <a href="https://wekolek.com/" style="text-decoration: none;color: #a8aaaf;font-weight: 500;font-size: 12px;line-height: 1.4 !important;">
								                  '.__("If you are also tired of chasing clients for payments",true).', <br>'.__("Click Here and let KoleK do that work for you!",true).' <br>
								                  '.__("That way, you can spend less time worrying about your finances and <br>more time doing what really matters: running your business.",true).'
								                </a>
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
								                  <p style="color: #a8aaaf;font-size: 13px;">
								                    '.CORPORATE_NAME.'
								                    <br>'.str_replace(", SINGAPORE","<br>SINGAPORE",FOOTER_ADDRESS).'
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

								$emailtemplate .= $html;
							}
						} else {
							if ($value['email_type']==$_POST['email_type']) {
								$emailtemplate .= '<div class="email-template-holder">
								<button class="btn btn-xs btn-primary pull-right emailSelectBtn" data-id="'.$result['uuid'].'">'.__("Select",true).'</button>';
								$emailtemplate .= '<h6><b>'.$result['subject'].'</b></h6>';
								$emailtemplate .= str_replace("\r\n","<br>",$result['body']);
								$emailtemplate .='</div>';
							}
						}
						
						$return['status'] = true;
						$return['emailtemplate'] = $emailtemplate;
					}
					
				} else {
					$return['status'] = true;
					$return['datatable'] = [];
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

	function fetch_templatedata() {
		if (isset($_POST['id'])) {
			$UM = new UserModel();
			$resultdata = $UM->getGlobalbyId("email_templates",$_POST['id']);
			if ($resultdata) {
				$return['status'] = true;
				$resultdata['body_html'] = str_replace("\r\n","<br>",$resultdata['body']);
				$return['data'] = $resultdata;
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Template ID.",true);
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("No Template ID found.",true);
		}
		echo json_encode($return);
		exit;
	}

	function update_template() {
		if(isset($_POST['data']) && isset($_POST['token']))
		{
			parse_str($_POST['data'],$postdata);
			$id = $postdata['uuid'];
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;
				$newdata = [];			
				unset($postdata['uuid']);
				$newdata = $postdata;
				unset($newdata['_wysihtml5_mode']);
				$newdata['title'] = clean($newdata['title']);
				$newdata['subject'] = clean($newdata['subject']);
				$newdata['body'] = clean($newdata['body']);
				if (isset($postdata['is_default'])) {
					$newdata['is_default'] = 1;
				} else {
					$newdata['is_default'] = 0;
				}
				$result = $UM->updateGlobal('email_templates',$id,$newdata);
				if($result)
				{
					$return['status'] = true;
					if (isset($postdata['is_deleted'])) {
						if ($postdata['is_deleted']==1) {
							$return['message'] = __("Successfully deactivated template",true);
						} else if($postdata['is_deleted']==2) {
							$return['message'] = __("Successfully deleted template",true);
						} else {
							$return['message'] = __("Successfully reactivated template",true);
						}
						
					} else {
						$return['message'] = __("Successfully updated template details",true);
					}
					
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to update template. Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function delete_template() {
		if(isset($_POST['id']) && isset($_POST['token']))
		{
			$id = $_POST['id'];
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;
				$newdata['is_deleted'] = 1;
				
				$result = $UM->updateGlobal('email_templates',$id,$newdata);	
				if($result)
				{
					$return['status'] = true;
					$return['message'] = __("Successfully removed template",true);
					
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to delete template. Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
			}
			
			echo json_encode($return);
		}
		exit;
	}


	// --- INVOICE LIFECYCLE --- //
	function email_open() {
		if (getUriSegment(3)!="") {
			$tracking_id = getUriSegment(3);
			$newData = [];
			$newData['is_opened']=1;
			$UM = new UserModel;
			$result = $UM->updateGlobal("invoice_lifecycle",$tracking_id,$newData);
			if ($result) {
				echo true;
			} else {
				echo false;
			}
		} else {
			echo false;
		}
		exit;
	}

	function add_bulk_lifecycle() {
		if (isset($_POST['token']) && isset($_POST['data'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				parse_str($_POST['data'],$postdata);
				
				foreach ($postdata['ar_id'] as $ar_id) {
					if (isset($_POST['invoice_type']) && $_POST['invoice_type']=="consolidated") {
						$invoicedata = $UM->getGlobalbyId("debt_collection",$ar_id);
						$invoicedata['client_details'] = $UM->getGlobalbyId("client_lists",$invoicedata['client_id']);
					} else {
						$invoicedata = $UM->getGlobalbyId("account_receivables",$ar_id);
					}											
					if (isset($postdata['email_type']) && $postdata['email_type']=="internal notification") {
						$email_type = $postdata['email_type'];
						$send_to = [];
						unset($postdata['notify_user_id']);					
						$newdata = $postdata;
						$newdata['uuid'] = hexdec(uniqid());
						$newdata['created_by'] = $payload->uuid;
						$newdata['template_id'] = null;
						foreach ($postdata['notif_user_id'] as $user_id) {
							$userData = $UM->getUserbyId($user_id);
							$send_to[] = $userData['email'];
						}
						unset($newdata['notif_user_id']);
						$newdata['send_to'] = implode(",",$send_to);
						$newdata['send_date'] = $postdata['reminder_date'];
						unset($newdata['reminder_date']);
					} else {
						$newdata['uuid'] = hexdec(uniqid());
						$newdata['created_by'] = $payload->uuid;
						$newdata['ar_id'] = $ar_id;
						$newdata['email_type'] = $postdata['trigger_type'];
						$newdata['send_to'] = $invoicedata['client_details']['email'];
						$newdata['send_cc'] = $invoicedata['client_details']['email_cc'];
						$newdata['body'] = $postdata['set_body'];
						$newdata['subject'] = $postdata['set_subject'];
						$newdata['template_id'] = $postdata['template_id'];
						$newdata['language'] = $postdata['language'];
					}				
								
					if (isset($postdata['trigger_type']) && $postdata['trigger_type']=="incentive") {
						$newdata['discount_amount'] = $postdata['discount_amount_value'];
						$newdata['discount_percentage'] = $postdata['discount_amount_percentage'];
						$newdata['paid_by_date'] = $postdata['paid_by_date'];
					}				

					if ($newdata['subject']=="" && $newdata['body']=="" && $newdata['template_id']=="") {					
						$reminder_date = date("Y-m-d");
						if (isset($postdata['date-radio']) && $postdata['date-radio']=="specific") {
							if ($postdata['reminder_date'] > $invoicedata['due_date']) {
								$email_type = "overdue reminder";
							} else {
								$email_type = $postdata['trigger_type'];
							}
							$reminder_date = $postdata['reminder_date'];
						} else if (isset($postdata['date-radio']) && $postdata['date-radio']=="by day") {
							if ($postdata["sched-date"]=="before") {
								$reminder_date = date("Y-m-d",strtotime($invoicedata['due_date']." -".$postdata['days']." days"));
								$email_type = $postdata['trigger_type'];
							} else if ($postdata["sched-date"]=="on") {
								$reminder_date = date("Y-m-d",strtotime($invoicedata['due_date']));
								$email_type = $postdata['trigger_type'];
							} else if ($postdata["sched-date"]=="after") {
								$reminder_date = date("Y-m-d",strtotime($invoicedata['due_date']." +".$postdata['days']." days"));
								$email_type = "overdue reminder";
							}											
						} else {
							$email_type = $postdata['trigger_type'];
							$reminder_date = $postdata['reminder_date'];
						}
						if (LANG=="BR") {
							$lang = "portuguese";
						} else {
							$lang = "english";
						}					

						$result = $UM->fetchDefaultEmailTemplate($email_type,$lang);
						$businessdata = $UM->getGlobalbyId("business_accounts",$invoicedata['business_id']);
						$bankaccountdata = $UM->getGlobalbyId("bank_accounts",$invoicedata['bank_account_id']);

						if ($email_type=="overdue reminder" || $email_type=="reminder" || $email_type=="incentive") {
							$currency = $UM->fetchCurrencyByCode($invoicedata['currency']);
							$earlier = new DateTime(date("Y-m-d",strtotime($reminder_date)));
							$later = new DateTime(date("Y-m-d",strtotime($invoicedata['due_date'])));
							$day_diff = $later->diff($earlier)->format("%a");
							
							$subject = str_replace("{{user.business.name}}",$businessdata['business_name'],$result['subject']);
							if (array_key_exists("discount_percentage",$newdata)) {
								$subject = str_replace("{{discount}}",$newdata['discount_percentage']."%",$subject);
							}
							$subject = str_replace("{{invoice.number}}","#".$invoicedata['invoice_no'],$subject);
							$subject = str_replace("{{due.date}}",date(DATEFORMAT,strtotime($invoicedata['due_date'])),$subject);
							if ($postdata['paid_by_date']!="") {
								$subject = str_replace("{{new.due.date}}",date(DATEFORMAT,strtotime($postdata['paid_by_date'])),$subject);
							}

							$body = str_replace("\r\n","<br>",$result['body']);
							$body = str_replace("{{user.business.name}}",$businessdata['business_name'],$body);

							if ($invoicedata['client_details']['name']=="") {
								$body = str_replace(" {{name}}","",$body);
							} else {
								$body = str_replace("{{name}}",$invoicedata['client_details']['name'],$body);
							}
							if (isset($postdata['discount_percentage'])) {
								$body = str_replace("{{discount}}",$postdata['discount_percentage']."%",$body);
							}
							if (isset($postdata['discount_amount_percentage'])) {
								$body = str_replace("{{discount}}",$postdata['discount_amount_percentage']."%",$body);
							}
							if ($postdata['discount_amount_value']!="") {
								$body = str_replace("{{savings}}",$currency.number_format($postdata['discount_amount_value'],2,DECIMALS,THOUSANDS),$body);
							}
							if ($postdata['paid_by_date']!="") {
								$body = str_replace("{{new.due.date}}",date(DATEFORMAT,strtotime($postdata['paid_by_date'])),$body);
							}
							if ($postdata['discount_amount_value']!="") {
								$body = str_replace("{{new.total}}",$currency.number_format($invoicedata['amount']-$postdata['discount_amount_value'],2,DECIMALS,THOUSANDS),$body);
							}
							$body = str_replace("{{due.date}}",date(DATEFORMAT,strtotime($invoicedata['due_date'])),$body);
							$body = str_replace("{{invoice.link}}",'<a href="'.SITE_URL.'/invoice/'.$invoicedata['uuid'].'" target="_blank">'.__("View Invoice",true).'</a>',$body);
							$body = str_replace("{{days.until.due}}",$day_diff,$body);
							if (array_key_exists("discount_percentage",$newdata)) {
								$body = str_replace("{{discount}}",$newdata['discount_percentage']."%",$body);
							}
							$body = str_replace("{{amount}}",$currency.number_format($invoicedata['amount'],2,DECIMALS,THOUSANDS),$body);
							$body = str_replace(" 0&nbsp;days"," until today",$body);
							$body = str_replace(" 1&nbsp;days"," 1 day",$body);
							$body = str_replace("<b>0</b><span>&nbsp;<b>dias</b>","<b>até hoje</b>",$body);
							$body = str_replace("<b>1</b><span>&nbsp;<b>dias</b>","<b>1 dia</b>",$body);
						} else {						
							$currency = "USD";
							foreach (json_decode($invoicedata['invoice_items']) as $line_inv_id) {
								$invoice = $UM->getGlobalbyId('account_receivables',$line_inv_id);
								$currency = $invoice['currency'];										
							}											
							if ($currency=="BRL") {
								$email_decimals = ",";
								$email_thousands = ".";
							} else {
								$email_decimals = ".";
								$email_thousands = ",";
							}												
							$invoicedata['invoice_no'] = "1".sprintf('%06d', $invoicedata['id']);
							$currency = $UM->fetchCurrencyByCode($currency);
							$subject = str_replace("{{user.business.name}}",$businessdata['business_name'],$result['subject']);
							if (isset($postdata['discount_amount_percentage'])) {
								$subject = str_replace("{{discount}}",$postdata['discount_amount_percentage']."%",$subject);
							}
							$subject = str_replace("{{invoice.number}}","#".$invoicedata['invoice_no'],$subject);						

							$body = str_replace("\r\n","<br>",$result['body']);
							$body = str_replace("{{user.business.name}}",$businessdata['business_name'],$body);
							if ($invoicedata['client_details']['name']=="") {
								$body = str_replace(" {{name}}","",$body);
							} else {
								$body = str_replace("{{name}}",$invoicedata['client_details']['name'],$body);	
							}
							
							$body = str_replace("{{invoice.link}}",'<a href="'.SITE_URL.'/invoice/'.$invoicedata['uuid'].'" target="_blank">'.__("View Invoice",true).'</a>',$body);
							if (isset($postdata['discount_amount_percentage'])) {
								$body = str_replace("{{discount}}",$postdata['discount_amount_percentage']."%",$body);
							}
							$body = str_replace(" 0&nbsp;days"," until today",$body);
							$body = str_replace(" 1&nbsp;days"," 1 day",$body);
							$body = str_replace("<b>0</b><span>&nbsp;<b>dias</b>","<b>até hoje</b>",$body);
							$body = str_replace("<b>1</b><span>&nbsp;<b>dias</b>","<b>1 dia</b>",$body);
						}

						$newdata['body'] = $body;
						$newdata['subject'] = $subject;
						$newdata['template_id'] = $result['uuid'];
					}								
					if ((isset($postdata['date-radio']) && $postdata['date-radio']=="specific") || $email_type=="reminder debt collection" || $email_type=="debt collection negotiation" || $email_type=="internal notification") {
						$send_date = $postdata['reminder_date'];
					} else {
						if ($postdata['sched-date']=="before") {
							$send_date = date("Y-m-d",strtotime($invoicedata['due_date']." -".$postdata['days']." days"));
						} else if ($postdata['sched-date']=="after") {
							$send_date = date("Y-m-d",strtotime($invoicedata['due_date']." +".$postdata['days']." days"));
						} else {
							$send_date = date("Y-m-d",strtotime($invoicedata['due_date']));
						}				
					}
					if ($newdata['send_to']=="") {
						$return['status'] = false;
						$return['message'] = __("Client has no valid email. Please add and try again.",true);
						echo json_encode($return);
						exit;
					}
					
					$newdata['send_date'] = $send_date;										
					$result = $UM->addGlobal("invoice_lifecycle",$newdata);
				}
				
				if ($result) {					
					$return['status'] = true;
					$return['message'] = __("Successfully added lifecycle action",true);
				} else {
					$return['status'] = false;
					$return['message'] = __("Failed to add lifecycle action",true);
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

	function add_lifecycle_default($user_id=null,$dataParam=null) {
		if ($user_id!=null && $dataParam!=null) {
			try {
				$UM = new UserModel();
				$postdata = $dataParam;
				$postdata['ar_id'] = $dataParam['uuid'];
				
				$invoicedata = $UM->getGlobalbyId("account_receivables",$postdata['ar_id']);

				if (((isset($invoicedata['client_details']['email'])) && $invoicedata['client_details']['email'] == "" || $invoicedata['client_details']['email'] == NULL) && ((isset($invoicedata['client_details']['email_cc'])) && $invoicedata['client_details']['email_cc'] == "" || $invoicedata['client_details']['email_cc'] == NULL)) {
				    return false;
				}
				
				$lifecyclePayload = [];

				//FETCH SETTINGS DEFAULT
				$default = $UM->lifecycle_settings($user_id);
				if ($default===false) {
					$default = [];
					$default['days_before'] = 5;
					$default['days_after'] = 5;
					$default['days_after_debt_collection_starts'] = 30;
					$default['debt_reminder_every_day'] = 7;
				}
				
				$due_date = $invoicedata['due_date'];
				$today = date('Y-m-d');

			
				//create payload for lifecylce notification				
				$lifecyclePayload['created_by'] = $user_id;
				$lifecyclePayload['ar_id'] = $postdata['ar_id'];

				$reminderDateArr = [];
				//determine email type: reminder, overdue reminder, debt reminder
				if ($due_date >= $today) {
				    // Invoice is not yet due
				    $lifecyclePayload['email_type'] = "reminder";
				    $default_email_type = "reminder";
				    if (isset($default['days_before'])) {
				    	$dueDate = date("Y-m-d",strtotime($invoicedata['due_date']."-".$default['days_before']." days"));
						$today = date("Y-m-d");
						if ($dueDate > $today) {
							$col = [];
							$col['date'] = date("Y-m-d",strtotime($invoicedata['due_date']."-".$default['days_before']." days"));
							$col['template'] = "reminder";
						    $reminderDateArr[] = $col;
						}
				    }
				    if (isset($default['days_after'])) {
				    	$col = [];
						$col['date'] = date("Y-m-d",strtotime($invoicedata['due_date']."+".$default['days_after']." days"));
						$col['template'] = "overdue reminder";
					    $reminderDateArr[] = $col;
				    }
				    $col = [];
					$col['date'] = date("Y-m-d",strtotime($invoicedata['due_date']));
					$col['template'] = "reminder";
				    $reminderDateArr[] = $col;
				} elseif (($due_date <= $today) && ($today <= date('Y-m-d', strtotime($due_date .'+'.$default['days_after_debt_collection_starts'].' days')))) {
				    // Invoice is overdue but not yet considered for debt collection
				    $lifecyclePayload['email_type'] = "reminder";
				    $default_email_type = "overdue reminder";

				    if (isset($default['days_after'])) {

				    	$dueDate = date("Y-m-d",strtotime($invoicedata['due_date']."+".$default['days_after']." days"));
						$today = date("Y-m-d");
						if ($dueDate > $today) {
							$col = [];
							$col['date'] = date("Y-m-d",strtotime($invoicedata['due_date']."+".$default['days_after']." days"));
							$col['template'] = "overdue reminder";
						    $reminderDateArr[] = $col;
						} else {
							$col = [];
							$col['date'] = date("Y-m-d");
							$col['template'] = "overdue reminder";
						    $reminderDateArr[] = $col;
						}
				    }
				} else {
					return; // don't create lifecycle for debt invoices
				    // Invoice is considered for debt collection
				    $lifecyclePayload['email_type'] = "reminder debt collection";
				    $default_email_type = "reminder debt collection";
				    if (isset($default['days_after'])) {
				    	$col = [];
						$col['date'] = date("Y-m-d");
						$col['template'] = "reminder debt collection";
					    $reminderDateArr[] = $col;
					    $col = [];
						$col['date'] = date("Y-m-d",strtotime("+".$default['debt_reminder_every_day']." days"));
						$col['template'] = "reminder debt collection";
					    $reminderDateArr[] = $col;
				    }
				}											
				$lifecyclePayload['send_to'] = $invoicedata['client_details']['email'];
				$lifecyclePayload['send_cc'] = $invoicedata['client_details']['email_cc'];

				$lang = (LANG=="BR")? "portuguese":"english";				
								
				$businessdata = $UM->getGlobalbyId("business_accounts",$invoicedata['business_id']);
				$bankaccountdata = $UM->getGlobalbyId("bank_accounts",$invoicedata['bank_account_id']);								
				$currency = $UM->fetchCurrencyByCode($invoicedata['currency']);

				foreach ($reminderDateArr as $reminder) {
					$send_date = $reminder_date = $reminder['date'];
					$defaultTemplate = $UM->fetchDefaultEmailTemplate($reminder['template'],$lang);					
					$earlier = new DateTime(date("Y-m-d",strtotime($reminder_date)));
					$later = new DateTime(date("Y-m-d",strtotime($invoicedata['due_date'])));
					$day_diff = $later->diff($earlier)->format("%a");
					
					$subject = str_replace("{{user.business.name}}",$businessdata['business_name'],$defaultTemplate['subject']);
					$subject = str_replace("{{invoice.number}}","#".$invoicedata['invoice_no'],$subject);
					$subject = str_replace("{{due.date}}",date(DATEFORMAT,strtotime($invoicedata['due_date'])),$subject);					

					$body = str_replace("\r\n","<br>",$defaultTemplate['body']);
					$body = str_replace("{{user.business.name}}",$businessdata['business_name'],$body);

					if ($invoicedata['client_details']['name']=="") {
						$body = str_replace(" {{name}}","",$body);
					} else {
						$body = str_replace("{{name}}",$invoicedata['client_details']['name'],$body);
					}
					
					$body = str_replace("{{due.date}}",date(DATEFORMAT,strtotime($invoicedata['due_date'])),$body);
					$body = str_replace("{{invoice.link}}",'<a href="'.SITE_URL.'/invoice/'.$invoicedata['uuid'].'" target="_blank">'.__("View Invoice",true).'</a>',$body);
					$body = str_replace("{{days.until.due}}",$day_diff,$body);					
					$body = str_replace("{{amount}}",$currency.number_format($invoicedata['amount'],2,DECIMALS,THOUSANDS),$body);
					$body = str_replace("0&nbsp;days","until today",$body);
					$body = str_replace(" 0&nbsp;days","until today",$body);
					$body = str_replace("1&nbsp;days"," 1 day",$body);
					$body = str_replace(" 1&nbsp;days"," 1 day",$body);
					$body = str_replace("<b>0</b><span>&nbsp;<b>dias</b>","<b>até hoje</b>",$body);
					$body = str_replace("<b>1</b><span>&nbsp;<b>dias</b>","<b>1 dia</b>",$body);

					$lifecyclePayload['body'] = $body;
					$lifecyclePayload['subject'] = $subject;
					$lifecyclePayload['template_id'] = $defaultTemplate['uuid'];					
					$lifecyclePayload['send_date'] = $send_date;
					$lifecyclePayload['uuid'] = hexdec(uniqid());
					$lifecyclePayload['language'] = $lang;
					$result = $UM->addGlobal("invoice_lifecycle",$lifecyclePayload);
				}
				return true;
			} catch (Exception $e) {
				return false;
			}
		} else {
			return false;
		}
		exit;
	}

	function add_lifecycle() {
		if (isset($_POST['token']) && isset($_POST['data'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				parse_str($_POST['data'],$postdata);

				if (isset($_POST['invoice_type']) && $_POST['invoice_type']=="consolidated") {
					$invoicedata = $UM->getGlobalbyId("debt_collection",$postdata['ar_id']);
					$invoicedata['client_details'] = $UM->getGlobalbyId("client_lists",$invoicedata['client_id']);
				} else {
					$invoicedata = $UM->getGlobalbyId("account_receivables",$postdata['ar_id']);
				}											
				if (isset($postdata['email_type']) && $postdata['email_type']=="internal notification") {
					$email_type = $postdata['email_type'];
					$send_to = [];
					unset($postdata['notify_user_id']);					
					$newdata = $postdata;
					$newdata['uuid'] = hexdec(uniqid());
					$newdata['created_by'] = $payload->uuid;
					$newdata['template_id'] = null;
					foreach ($postdata['notif_user_id'] as $user_id) {
						$userData = $UM->getUserbyId($user_id);
						$send_to[] = $userData['email'];
					}
					unset($newdata['notif_user_id']);
					$newdata['send_to'] = implode(",",$send_to);
					$newdata['send_date'] = $postdata['reminder_date'];
					unset($newdata['reminder_date']);
				} else {
					$newdata['uuid'] = hexdec(uniqid());
					$newdata['created_by'] = $payload->uuid;
					$newdata['ar_id'] = $postdata['ar_id'];
					$newdata['email_type'] = $postdata['trigger_type'];
					$newdata['send_to'] = $invoicedata['client_details']['email'];
					$newdata['send_cc'] = $invoicedata['client_details']['email_cc'];
					$newdata['body'] = $postdata['set_body'];
					$newdata['subject'] = $postdata['set_subject'];
					$newdata['template_id'] = $postdata['template_id'];
					$newdata['language'] = $postdata['language'];
				}				
							
				if (isset($postdata['trigger_type']) && $postdata['trigger_type']=="incentive") {
					$newdata['discount_amount'] = $postdata['discount_amount_value'];
					$newdata['discount_percentage'] = $postdata['discount_amount_percentage'];
					$newdata['paid_by_date'] = $postdata['paid_by_date'];
				}				

				if ($newdata['subject']=="" && $newdata['body']=="" && $newdata['template_id']=="") {					
					$reminder_date = date("Y-m-d");
					if (isset($postdata['date-radio']) && $postdata['date-radio']=="specific") {
						if ($postdata['reminder_date'] > $invoicedata['due_date']) {
							$email_type = "overdue reminder";
						} else {
							$email_type = $postdata['trigger_type'];
						}
						$reminder_date = $postdata['reminder_date'];
					} else if (isset($postdata['date-radio']) && $postdata['date-radio']=="by day") {
						if ($postdata["sched-date"]=="before") {
							$reminder_date = date("Y-m-d",strtotime($invoicedata['due_date']." -".$postdata['days']." days"));
							$email_type = $postdata['trigger_type'];
						} else if ($postdata["sched-date"]=="on") {
							$reminder_date = date("Y-m-d",strtotime($invoicedata['due_date']));
							$email_type = $postdata['trigger_type'];
						} else if ($postdata["sched-date"]=="after") {
							$reminder_date = date("Y-m-d",strtotime($invoicedata['due_date']." +".$postdata['days']." days"));
							$email_type = "overdue reminder";
						}											
					} else {
						$email_type = $postdata['trigger_type'];
						$reminder_date = $postdata['reminder_date'];
					}
					if (LANG=="BR") {
						$lang = "portuguese";
					} else {
						$lang = "english";
					}					

					$result = $UM->fetchDefaultEmailTemplate($email_type,$lang);
					$businessdata = $UM->getGlobalbyId("business_accounts",$invoicedata['business_id']);
					$bankaccountdata = $UM->getGlobalbyId("bank_accounts",$invoicedata['bank_account_id']);

					if ($email_type=="overdue reminder" || $email_type=="reminder" || $email_type=="incentive") {
						$currency = $UM->fetchCurrencyByCode($invoicedata['currency']);
						$earlier = new DateTime(date("Y-m-d",strtotime($reminder_date)));
						$later = new DateTime(date("Y-m-d",strtotime($invoicedata['due_date'])));
						$day_diff = $later->diff($earlier)->format("%a");
						
						$subject = str_replace("{{user.business.name}}",$businessdata['business_name'],$result['subject']);
						if (array_key_exists("discount_percentage",$newdata)) {
							$subject = str_replace("{{discount}}",$newdata['discount_percentage']."%",$subject);
						}
						$subject = str_replace("{{invoice.number}}","#".$invoicedata['invoice_no'],$subject);
						$subject = str_replace("{{due.date}}",date(DATEFORMAT,strtotime($invoicedata['due_date'])),$subject);
						if ($postdata['paid_by_date']!="") {
							$subject = str_replace("{{new.due.date}}",date(DATEFORMAT,strtotime($postdata['paid_by_date'])),$subject);
						}

						$body = str_replace("\r\n","<br>",$result['body']);
						$body = str_replace("{{user.business.name}}",$businessdata['business_name'],$body);

						if ($invoicedata['client_details']['name']=="") {
							$body = str_replace(" {{name}}","",$body);
						} else {
							$body = str_replace("{{name}}",$invoicedata['client_details']['name'],$body);
						}
						if (isset($postdata['discount_percentage'])) {
							$body = str_replace("{{discount}}",$postdata['discount_percentage']."%",$body);
						}
						if (isset($postdata['discount_amount_percentage'])) {
							$body = str_replace("{{discount}}",$postdata['discount_amount_percentage']."%",$body);
						}
						if ($postdata['discount_amount_value']!="") {
							$body = str_replace("{{savings}}",$currency.number_format($postdata['discount_amount_value'],2,DECIMALS,THOUSANDS),$body);
						}
						if ($postdata['paid_by_date']!="") {
							$body = str_replace("{{new.due.date}}",date(DATEFORMAT,strtotime($postdata['paid_by_date'])),$body);
						}
						if ($postdata['discount_amount_value']!="") {
							$body = str_replace("{{new.total}}",$currency.number_format($invoicedata['amount']-$postdata['discount_amount_value'],2,DECIMALS,THOUSANDS),$body);
						}
						$body = str_replace("{{due.date}}",date(DATEFORMAT,strtotime($invoicedata['due_date'])),$body);
						$body = str_replace("{{invoice.link}}",'<a href="'.SITE_URL.'/invoice/'.$invoicedata['uuid'].'" target="_blank">'.__("View Invoice",true).'</a>',$body);
						$body = str_replace("{{days.until.due}}",$day_diff,$body);
						if (array_key_exists("discount_percentage",$newdata)) {
							$body = str_replace("{{discount}}",$newdata['discount_percentage']."%",$body);
						}
						$body = str_replace("{{amount}}",$currency.number_format($invoicedata['amount'],2,DECIMALS,THOUSANDS),$body);
						$body = str_replace(" 0&nbsp;days"," until today",$body);
						$body = str_replace(" 1&nbsp;days"," 1 day",$body);
						$body = str_replace("<b>0</b><span>&nbsp;<b>dias</b>","<b>até hoje</b>",$body);
						$body = str_replace("<b>1</b><span>&nbsp;<b>dias</b>","<b>1 dia</b>",$body);
					} else {						
						$currency = "USD";
						foreach (json_decode($invoicedata['invoice_items']) as $line_inv_id) {
							$invoice = $UM->getGlobalbyId('account_receivables',$line_inv_id);
							$currency = $invoice['currency'];										
						}											
						if ($currency=="BRL") {
							$email_decimals = ",";
							$email_thousands = ".";
						} else {
							$email_decimals = ".";
							$email_thousands = ",";
						}												
						$invoicedata['invoice_no'] = "1".sprintf('%06d', $invoicedata['id']);
						$currency = $UM->fetchCurrencyByCode($currency);
						$subject = str_replace("{{user.business.name}}",$businessdata['business_name'],$result['subject']);
						if (isset($postdata['discount_amount_percentage'])) {
							$subject = str_replace("{{discount}}",$postdata['discount_amount_percentage']."%",$subject);
						}
						$subject = str_replace("{{invoice.number}}","#".$invoicedata['invoice_no'],$subject);						

						$body = str_replace("\r\n","<br>",$result['body']);
						$body = str_replace("{{user.business.name}}",$businessdata['business_name'],$body);
						if ($invoicedata['client_details']['name']=="") {
							$body = str_replace(" {{name}}","",$body);
						} else {
							$body = str_replace("{{name}}",$invoicedata['client_details']['name'],$body);	
						}
						
						$body = str_replace("{{invoice.link}}",'<a href="'.SITE_URL.'/invoice/'.$invoicedata['uuid'].'" target="_blank">'.__("View Invoice",true).'</a>',$body);
						if (isset($postdata['discount_amount_percentage'])) {
							$body = str_replace("{{discount}}",$postdata['discount_amount_percentage']."%",$body);
						}
						$body = str_replace(" 0&nbsp;days"," until today",$body);
						$body = str_replace(" 1&nbsp;days"," 1 day",$body);
						$body = str_replace("<b>0</b><span>&nbsp;<b>dias</b>","<b>até hoje</b>",$body);
						$body = str_replace("<b>1</b><span>&nbsp;<b>dias</b>","<b>1 dia</b>",$body);
					}

					$newdata['body'] = $body;
					$newdata['subject'] = $subject;
					$newdata['template_id'] = $result['uuid'];
				}								
				if ((isset($postdata['date-radio']) && $postdata['date-radio']=="specific") || $email_type=="reminder debt collection" || $email_type=="debt collection negotiation" || $email_type=="internal notification") {
					$send_date = $postdata['reminder_date'];
				} else {
					if ($postdata['sched-date']=="before") {
						$send_date = date("Y-m-d",strtotime($invoicedata['due_date']." -".$postdata['days']." days"));
					} else if ($postdata['sched-date']=="after") {
						$send_date = date("Y-m-d",strtotime($invoicedata['due_date']." +".$postdata['days']." days"));
					} else {
						$send_date = date("Y-m-d",strtotime($invoicedata['due_date']));
					}				
				}
				if ($newdata['send_to']=="") {
					$return['status'] = false;
					$return['message'] = __("Client has no valid email. Please add and try again.",true);
					echo json_encode($return);
					exit;
				}
				
				$newdata['send_date'] = $send_date;				
				$result = $UM->addGlobal("invoice_lifecycle",$newdata);
				if ($result) {					
					$return['status'] = true;
					$return['message'] = __("Successfully added lifecycle action",true);
				} else {
					$return['status'] = false;
					$return['message'] = __("Failed to add lifecycle action",true);
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

	function fetch_lifecycles() {
		if (isset($_POST['token']) && isset($_POST['ar_id'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				$result = $UM->fetchLifecycles($_POST['ar_id']);
				$invoicedata = $UM->getGlobalbyId("account_receivables",$_POST['ar_id']);
				$invoice_type = "single";

				if (count($invoicedata)==0) {
					$invoicedata = $UM->getGlobalbyId("debt_collection",$_POST['ar_id']);
					$invoice_type = "consolidated";
					$issued_date = $invoicedata['created_on'];
				} else {
					$issued_date = $invoicedata['issued_date'];
				}
				if (count($result)>0) {					
					$lifecycle_data = '<tr>
										<td><i class="icon-checkmark-circle text-success"></i></td>
										<td>'.__("Issued on",true).' '.date(DATEFORMAT,strtotime($issued_date)).'</td>
										<td class="text-right"><label class="label label-success">'.__("Issued",true).'</label></td>
									</tr>';
					foreach ($result as $key => $value) {
						if ($value['status']=="completed") {
							$type_label = 'label-success';
						} else {
							$type_label = 'label-reminder';
						}

						if ($value['status']=="completed") {
							$status = 'text-success';
						} else {
							$status = 'text-muted';
						}

						$lifecycle_data .= '<tr data-id="'.$value['uuid'].'">
								<td><i class="icon-checkmark-circle '.$status.'"></i></td>
								<td>';
								

								if ($value['status']=="completed") {
									if ($value['email_type']=="internal notification") {
										$lifecycle_data .= __("Notification sent on",true).' ';
									} else {
										$lifecycle_data .= __("Email sent on",true).' ';	
									}
									
								} else {
									if ($value['email_type']=="internal notification") {
										$lifecycle_data .= __("Scheduled notification for",true).' ';
									} else {
										$lifecycle_data .= __("Scheduled email for",true).' ';
									}
								}								

								$lifecycle_data .= date(DATEFORMAT,strtotime($value["send_date"]));

								if ($value['email_type']=="internal notification") {
									$lifecycle_data .= '<button class="btn btn-success btn-xs mgl-10 lifecycleNotesBtn" data-id="'.$value['uuid'].'">'.__("Open",true).' <i class="icon-bubbles10 mgl-5"></i></button>';
								}

								if ($value['is_opened']=="1" && $value['email_type']!="internal notification") {
									$lifecycle_data .= '<br><label class="label label-green">'.__("Opened",true).' <i class="icon-mail-read mgl-5"></i></label>';
								}								

								$lifecycle_data .= '</td><td class="text-right">';								
								
								if ($value['email_type']=="reminder debt collection") {
									$value['email_type'] = "reminder";
								}
								if ($value['email_type']=="debt collection negotiation") {
									$value['email_type'] = "negotiation";
								}
								if ($value['email_type']=="internal notification") {
									$value['email_type'] = "notification";
								}

								$lifecycle_data .= '<label class="label '.$type_label.'">'.ucwords(__(ucfirst($value['email_type']),true)).'</label>';
								if ($value['status']!="completed") {
									$editLifecycleBtn = "editLifecycleBtn";
									if ($value['email_type']=="internal notification") {
										$editLifecycleBtn = "editInternalLifecycleBtn";
									}

									$lifecycle_data .= '<br><a class="'.$editLifecycleBtn.' text-default" data-id="'.$value['uuid'].'"><i class="icon-pencil f-13"></i></a>
									  <a class="deleteLifecycleBtn mgl-10 mgr-25 text-default" data-id="'.$value['uuid'].'"><i class="icon-trash f-13"></i></a>';
								}

								$lifecycle_data .= '</td>
							</tr>';
					}
					if ($invoicedata['status']=="Paid") {
						$invoice_status = 'text-success';
					} else {
						$invoice_status = 'text-muted';
					}
					$lifecycle_data .= '<tr>
							<td><i class="icon-checkmark-circle '.$invoice_status.'"></i></td>
							<td>'.__("Payment Received",true).'</td>
							<td class="text-right"><label class="label label-primary">'.__("Complete",true).'</label></td>
						</tr>';
					$return['lifecycle_data'] = $lifecycle_data;
					$return['status'] = true;
				} else {
					if ($invoicedata['status']=="Paid") {
						$invoice_status = 'text-success';
					} else {
						$invoice_status = 'text-muted';
					}
					$return['status'] = true;
					if ($invoice_type=="consolidated") {
						$invoicedata['issued_date'] = $invoicedata['created_on'];
					}
					$return['lifecycle_data'] = '<tr>
										<td><i class="icon-checkmark-circle text-success"></i></td>
										<td>'.__("Issued on",true).' '.date(DATEFORMAT,strtotime($invoicedata['issued_date'])).'</td>
										<td class="text-right"><label class="label label-primary">'.__("Issued",true).'</label></td>
									</tr>
									<tr>
										<td><i class="icon-checkmark-circle '.$invoice_status.'"></i></td>
										<td>'.__("Payment Received",true).'</td>
										<td class="text-right"><label class="label label-primary">'.__("Complete",true).'</label></td>
									</tr>';
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

	function fetch_lifecycledata() {
		if (isset($_POST['id'])) {
			$UM = new UserModel();
			$resultdata = $UM->getGlobalbyId("invoice_lifecycle",$_POST['id']);
			$resultdata['invoicedata'] = $UM->getGlobalbyId("account_receivables",$resultdata['ar_id']);
			if ($resultdata) {
				$return['status'] = true;				
				if ($resultdata['email_type']=="internal notification") {
					$notif_user_id = [];
					foreach (explode(",",$resultdata['send_to']) as $email) {
						$user = $UM->getUser($email);
						$notif_user_id[] = '<span data-id="'.$user['uuid'].'">'.ucwords($user['first_name']." ".$user['last_name']).' <i class="icon-cross"></i></span>';
					}
					$resultdata['notif_user_id'] = implode("",$notif_user_id);
				}

				$return['data'] = $resultdata;
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid lifecycle ID.",true);
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("No lifecycle ID found.",true);
		}
		echo json_encode($return);
		exit;
	}

	function update_lifecycle() {
		if(isset($_POST['data']) && isset($_POST['token']))
		{
			parse_str($_POST['data'],$postdata);
			$id = $postdata['uuid'];
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;
				$newdata = [];	
				$send_date = "";		
				if (isset($postdata['ar_id'])) {
					$invoicedata = $UM->getGlobalbyId("account_receivables",$postdata['ar_id']);
				}
				unset($postdata['uuid']);				
				if (isset($postdata['ar_id'])) {
					$newdata['ar_id'] = $postdata['ar_id'];
				}
				if (isset($postdata['trigger_type'])) {
					$newdata['email_type'] = $postdata['trigger_type'];
				}
				if (isset($postdata['email_type'])) {
					$newdata['email_type'] = $postdata['email_type'];
				}
				if (isset($postdata['set_body'])) {
					$newdata['body'] = $postdata['set_body'];
				}
				if (isset($postdata['set_subject'])) {
					$newdata['subject'] = $postdata['set_subject'];
				}
				if (isset($postdata['template_id'])) {
					$newdata['template_id'] = $postdata['template_id'];
				}
				if (isset($postdata['trigger_type']) && $postdata['trigger_type']=="incentive") {
					$newdata['discount_amount'] = $postdata['discount_amount_value'];
					$newdata['discount_percentage'] = $postdata['discount_amount_percentage'];
					$newdata['paid_by_date'] = $postdata['paid_by_date'];
				}
				if ((isset($postdata['date-radio']) && $postdata['date-radio']=="specific") || (isset($postdata['email_type']) && $postdata['email_type']=="internal notification")) {
					$send_date = $postdata['reminder_date'];
				} else {
					if (isset($invoicedata)) {
						if ($postdata['sched-date']=="before") {
							$send_date = date("Y-m-d",strtotime($invoicedata['due_date']." -".$postdata['days']." days"));
						} else if ($postdata['sched-date']=="after") {
							$send_date = date("Y-m-d",strtotime($invoicedata['due_date']." +".$postdata['days']." days"));
						} else {
							$send_date = date("Y-m-d",strtotime($invoicedata['due_date']));
						}
					}
				}
				if ($send_date!="") {
					$newdata['send_date'] = $send_date;
				}
				if (isset($postdata['subject'])) {
					$newdata['subject'] = $postdata['subject'];
				}
				if (isset($postdata['body'])) {
					$newdata['body'] = $postdata['body'];
				}
				if (isset($postdata['notif_user_id'])) {
					$send_to = [];
					foreach ($postdata['notif_user_id'] as $user_id) {
						$user = $UM->getUserbyId($user_id);
						$send_to[] = $user['email'];
					}
					$newdata['send_to'] = implode(",",$send_to);
				}
				if (isset($postdata['notes'])) {
					$newdata['notes'] = $postdata['notes'];
				}
				$result = $UM->updateGlobal('invoice_lifecycle',$id,$newdata);
				if($result)
				{
					$return['status'] = true;
					$return['message'] = __("Successfully updated lifecycle",true);
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to update lifecycle. Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
			}
			
			echo json_encode($return);
		}
		exit;
	}

	function delete_lifecycle() {
		if(isset($_POST['id']) && isset($_POST['token']))
		{
			$id = $_POST['id'];
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;
				$newdata['is_deleted'] = 1;
				
				$result = $UM->updateGlobal('invoice_lifecycle',$id,$newdata);	
				if($result)
				{
					$return['status'] = true;
					$return['message'] = __("Successfully removed lifecycle.",true);
					
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to remove lifecycle. Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
			}
			
			echo json_encode($return);
		}
		exit;
	}

	// function send_test_email() {
	// 	if (isset($_POST['token'])) {
	// 		$jwt = $_POST['token'];
	// 		try {
	// 			$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
	// 			if ($payload) {
	// 				$UM = new UserModel();
	// 				parse_str($_POST['data'],$postdata);
	// 				$invoice = $UM->getGlobalbyId("account_receivables",$postdata['ar_id']);
	// 				$css_styles = '<style type="text/css">@import url("https://fonts.googleapis.com/css?family=Nunito+Sans:400,700&display=swap"); body { margin: 0; } #email-wrapper { font-family: "Nunito Sans", Helvetica, Arial, sans-serif !important; }</style>';

	// 				$client_data = $UM->getGlobalbyId('client_lists',$invoice['client_id']);
	// 				$bank_account_data = $UM->getGlobalbyId('bank_accounts',$invoice['bank_account_id']);
	// 				$business_account_data = $UM->getGlobalbyId('business_accounts',$invoice['business_id']);
	// 				$currency = $UM->fetchCurrencyByCode($invoice['currency']);
	// 				$due = $invoice['due_date'];
	// 				$now = date("Y-m-d");
	// 				$due_d_text = "left";
	// 				if ($due < $now) {
	// 				    $is_overdue = '<tr><td colspan=2 class="attributes_item" style="text-align: center;padding-top:25px;color:#F44336;font-size: 32px;"><b>'.__("OVERDUE",true).'</b></td></tr>';
	// 				} else {
	// 				    $is_overdue = '';
	// 				}
											
	// 				$col = [];
	// 			    $col['address']['email'] = $postdata['test_email'];
	// 			    $recipients[] = $col;											

	// 				$supplierLogo = "";
	// 				if ($business_account_data['business_logo']!="") {
	// 				    $supplierLogo = '<img src="'.$business_account_data['business_logo'].'" alt="'.$business_account_data['business_name'].'" style="max-height:125px;"/>';
	// 				}
	// 				$supplierName = $business_account_data['business_name'];
	// 				$supplierNumber = $business_account_data['business_no'];
	// 				$supplierAccountName = $bank_account_data['account_name'];
	// 				$supplierBankName = $bank_account_data['bank_name'];
	// 				$supplierAccountNumber = $bank_account_data['account_number'];
	// 				$supplierPixNumber = $bank_account_data['pix_number'];

	// 				$name = $client_data['name'];
	// 				$due_date = date(DATEFORMAT,strtotime($invoice['due_date']));						

	// 				if ($postdata['discount_amount_value']!=null) {
	// 				    $amount = $currency.number_format(($invoice['amount']-$postdata['discount_amount_value']),2,DECIMALS,THOUSANDS);
	// 				} else {
	// 				    $amount = $currency.number_format($invoice['amount'],2,DECIMALS,THOUSANDS);  
	// 				}
					  

	// 				if ($invoice['attachment']!="") {
	// 				    $attachment_text = __('Please find attached the invoice, and also below a summary to make the payment process easier for you:',true);
	// 				    $invoiceBtnLink = '<tr>
	// 				                          <td class="attributes_item" colspan="2" style="padding:40px 0;text-align:center;">
	// 				                                <a href="'.SITE_URL.'/p/'.$invoice['uuid'].'" target="_blank" style="text-decoration: none!important;color: #212122!important;border-radius: 3px;padding: 14px 20px;background-color: #c2d5a8;font-weight:500;">'.__("View Invoice",true).'</a>
	// 				                              </td>
	// 				                        </tr>';
	// 				} else {
	// 				    $attachment_text = __("Please find below a summary to make the payment process easier for you:",true);
	// 				    $invoiceBtnLink = '<tr>
	// 				                          <td class="attributes_item" colspan="2" style="padding:40px 0;text-align:center;">
	// 				                                <a href="'.SITE_URL.'/p/'.$invoice['uuid'].'" target="_blank" style="text-decoration: none!important;color: #212122!important;border-radius: 3px;padding: 14px 20px;background-color: #c2d5a8;font-weight:500;">'.__("View Invoice",true).'</a>
	// 				                              </td>
	// 				                        </tr>';
	// 				}

					  
	// 				$html = '<table id="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="padding-top:20px !important;    background-color: #f2f4f6;">
	// 				  <tr style="display:none;">'.$postdata['set_body'].'<td></td></tr>
	// 				  <tr>
	// 				    <td align="center">
	// 				      <table class="email-content" width="100%" cellpadding="0" cellspacing="0" role="presentation">          
	// 				        <tr>
	// 				          <td class="email-body" style="max-width: 570px;" cellpadding="0" cellspacing="0">
	// 				            <table class="email-body_inner" align="center" style="max-width: 570px;padding:10px 0;background-color:#fff;" cellpadding="0" cellspacing="0" role="presentation">
	// 				              <tr>
	// 				                <td>
	// 				                  <div style="padding: 10px 30px 20px;">
	// 				                  <div style="text-align:center;">'.$supplierLogo.'</div>
	// 				                    '.$postdata['set_body'].'
	// 				                    <p>'.$attachment_text.'</p>
	// 				                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f5f5f5;margin:0;">
	// 				                      '.$is_overdue.'
	// 				                      <tr>
	// 				                        <td class="attributes_content">
	// 				                          <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
	// 				                            <tr>
	// 				                              <td class="attributes_item" style="width:50%;text-align: center;padding-top:25px;color:#212122">
	// 				                                <span class="f-fallback">
	// 				                                  <strong>'.__("Amount Due",true).':</strong> '.$amount.'
	// 				                                </span>
	// 				                              </td>
	// 				                              <td class="attributes_item" style="text-align: center;padding-top:25px;color:#212122">
	// 				                                <span class="f-fallback">
	// 				                                  <strong>'.__("Due Date",true).':</strong> '.$due_date.'
	// 				                                </span>
	// 				                              </td>
	// 				                            </tr>';
					                            
					                            
	// 				                            $html .= $invoiceBtnLink.'</table>
	// 				                        </td>
	// 				                      </tr>
	// 				                    </table>                    
	// 				                    <p>'.__("After completing the payment, please answer this e-mail and attach the receipt.",true).'</p><p>'.__("Please, let us know if you have any doubt or if we can do anything else to help.",true).'</p>
	// 				                    <p>'.__("Thank you!",true).'</p>
	// 				                    <p>'.__("Best",true).',<br>'.__("Team Kolek",true).'</p>
	// 				                    <table class="body-sub" role="presentation">
	// 				                      <tr>
	// 				                        <td>
	// 				                          <p class="f-fallback sub">'.__("Please ignore this e-mail in case you already paid the invoice",true).'</p>
	// 				                          <p class="f-fallback sub">'.__("If you have any questions about this invoice, simply reply to this email or reach out to our",true).' <a href="mailto:support@wekolek.com" style="color:#98a783;">'.__("support team",true).'</a> '.__("for help",true).'.</p>
	// 				                        </td>
	// 				                      </tr>
	// 				                    </table>
	// 				                  </div>
	// 				                </td>
	// 				              </tr>
	// 				              <tr>
	// 				                <td class="text-center" style="padding:0px 20px 20px 20px;text-align: center;;">
	// 				                  <a href="https://wekolek.com/" style="text-decoration: none;color: #a8aaaf;font-weight: 500;font-size: 12px;line-height: 1.4 !important;">
	// 				                  '.__("If you are also tired of chasing clients for payments",true).', <br>'.__("Click Here and let KoleK do that work for you!",true).' <br>
	// 				                  '.__("That way, you can spend less time worrying about your finances and <br>more time doing what really matters: running your business.",true).'
	// 				                </a>
	// 				                </td>
	// 				              </tr>
	// 				            </table>
	// 				          </td>
	// 				        </tr>
	// 				        <tr>
	// 				          <td>
	// 				            <table style="margin: 20px auto;" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
	// 				              <tr>
	// 				                <td class="content-cell" align="center">
	// 				                  <p style="color: #a8aaaf;font-size: 13px;">
	// 				                    '.CORPORATE_NAME.'
	// 				                    <br>'.str_replace(", SINGAPORE","<br>SINGAPORE",FOOTER_ADDRESS).'
	// 				                  </p>
	// 				                </td>
	// 				              </tr>
	// 				            </table>
	// 				          </td>
	// 				        </tr>
	// 				      </table>
	// 				    </td>
	// 				  </tr>
	// 				</table>';					

	// 				if ($invoice['attachment']!="") {
	// 				    $fetch_attachments = explode(",",$invoice['attachment']);
	// 				    $attachments = [];
	// 				    $count = 1;
	// 				    foreach ($fetch_attachments as $key3 => $value3) {
	// 				        $b64Doc = chunk_split(base64_encode(file_get_contents($value3)));
	// 				        $pdffile = str_replace("\r\n", "", $b64Doc);
	// 				        $col = [];
	// 				        if (count($fetch_attachments)==1) {
	// 				          $col['name'] = __("Invoice #",true).$invoice['invoice_no'].".pdf";
	// 				        } else {
	// 				          $col['name'] = __("Invoice #",true).$invoice['invoice_no']."-".$count++.".pdf";
	// 				        }
					        
	// 				        $col['type'] = "application/pdf";
	// 				        $col['data'] = $pdffile;
	// 				        $attachments[] = $col;
	// 				    }
	// 				}				

	// 				$emailArr = [];
	// 				$emailArr['campaign_id'] = $postdata['ar_id'];
	// 				$emailArr['recipients'] = $recipients;
	// 				if ($invoice['attachment']!="") {
	// 					$emailArr['content']['attachments'] = $attachments;
	// 				}
	// 				$emailArr['content']['reply_to'] = "support@wekolek.com";  
	// 				$emailArr['content']['from']['email'] = "invoicing@mail.wekolek.com";
	// 				$emailArr['content']['from']['name'] = $supplierName;
	// 				$emailArr['content']['subject'] = $postdata['set_subject'];
	// 				$emailArr['content']['html'] = $css_styles.$html;
	// 				$emailArr['content']['text'] = strip_tags($html);

	// 				$ch = curl_init();

	// 				curl_setopt($ch, CURLOPT_URL, 'https://api.sparkpost.com/api/v1/transmissions?num_rcpt_errors=3');
	// 				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// 				curl_setopt($ch, CURLOPT_POST, 1);
	// 				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailArr));
					
	// 				$headers = array();
	// 				$headers[] = 'Content-Type: application/json';
	// 				$headers[] = 'Accept: application/json';
	// 				$headers[] = 'Authorization: cc7433de5492eb10c89fbd43d3f7887fabed61c1';
	// 				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	// 				$result = json_decode(curl_exec($ch));
	// 				if (curl_errno($ch)) {
	// 				      echo 'Error:' . curl_error($ch);
	// 				}

	// 				if ($result) {
	// 				    if (isset($result->errors)) {
	// 				      	$return['status'] = false;
	// 						$return['message'] = "Failed: ".$result->errors[0]->message;   					     
	// 				    } else {
	// 				    	$return['status'] = true;
	// 						$return['message'] = __("Email sent!",true);
	// 						$return['data'] = $payload;
	// 						$return['sent_at'] = date(DATEFORMAT." H:i A");
	// 				    }    
	// 				} else {
	// 				    $return['status'] = false;
	// 					$return['message'] = __("Failed to send email. Please contact support.",true);
	// 				}
	// 				curl_close($ch);				
	// 			} else {
	// 				$return['status'] = false;
	// 				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
	// 			}
				
	// 		} catch (Exception $e) {
	// 			$return['status'] = false;
	// 			$return['message'] = $e->getMessage();
	// 		}
	// 	} else {
	// 		$return['status'] = false;
	// 		$return['message'] = __("Missing Token",true);
	// 	}

	// 	echo json_encode($return);
	// 	exit;
	// }


	function send_test_email_default() {
		if (isset($_POST['token'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				if ($payload) {
					$UM = new UserModel();
					parse_str($_POST['data'],$postdata);
					$invoice = $UM->getGlobalbyId("account_receivables",$postdata['ar_id']);
					$reminder_date = date("Y-m-d");
					if ($postdata['date-radio']=="specific") {					
						if ($postdata['reminder_date'] > $invoice['due_date']) {
							$email_type = "overdue reminder";
						} else {
							$email_type = $postdata['trigger_type'];
						}
						$reminder_date = $postdata['reminder_date'];
					} else if ($postdata['date-radio']=="by day") {
						if ($postdata["sched-date"]=="before") {
							$reminder_date = date("Y-m-d",strtotime($invoice['due_date']." -".$postdata['days']." days"));
							$email_type = $postdata['trigger_type'];
						} else if ($postdata["sched-date"]=="on") {
							$reminder_date = date("Y-m-d",strtotime($invoice['due_date']));
							$email_type = $postdata['trigger_type'];
						} else if ($postdata["sched-date"]=="after") {
							$reminder_date = date("Y-m-d",strtotime($invoice['due_date']." +".$postdata['days']." days"));
							$email_type = "overdue reminder";
						}					
						
					}			
					if (LANG=="BR") {
						$lang = "portuguese";
					} else {
						$lang = "english";
					}

					if ($invoice['currency']=="BRL") {
						$email_decimals = ",";
						$email_thousands = ".";
					} else {
						$email_decimals = ".";
						$email_thousands = ",";
					}	

					$result = $UM->fetchDefaultEmailTemplate($email_type,$lang);					
					$businessdata = $UM->getGlobalbyId("business_accounts",$invoice['business_id']);
					$bankaccountdata = $UM->getGlobalbyId("bank_accounts",$invoice['bank_account_id']);

					//Calculate Updated Amount
					$pp_val = 0;
					$partial_payments = $UM->fetchInvoicePayments($postdata['ar_id']);
					if (count($partial_payments)>0) {
						foreach ($partial_payments as $pp) {
							$pp_val += $pp['amount'];
						}
					}
					$invoicedata = $invoice;
					if (isset($invoicedata['discount']) && $invoicedata['discount']!=null) {
			           $discounts = $invoicedata['discount'];
			        } else {
			           $discounts = 0;
			        }
			        if (isset($invoicedata['interest']) && $invoicedata['interest']!=null && (date("Y-m-d") > date("Y-m-d",strtotime($invoicedata['due_date'])))) {
			           $interest = $invoicedata['interest'];
			        } else {
			           $interest = 0;
			        }   
			        if (isset($invoicedata['late_fee']) && $invoicedata['late_fee']!=null && (date("Y-m-d") > date("Y-m-d",strtotime($invoicedata['due_date'])))) {
			           $late_fee = $invoicedata['late_fee'];
			        } else {
			           $late_fee = 0;
			        }			        

					$subtotal = $invoicedata['amount'];
			        $discounts = ($subtotal*($discounts/100));
			        $late_fee = ($subtotal*($late_fee/100));

			        $earlier = new DateTime(date("Y-m-d"));
			        $later = new DateTime(date("Y-m-d",strtotime($invoicedata['due_date'])));
			        $day_overdue = $later->diff($earlier)->format("%a");
			        $interest = (($interest/100)/30) * $day_overdue * $subtotal;
			        $total_updated_amount = $subtotal - $pp_val - $discounts + $late_fee + $interest;

					$currency = $UM->fetchCurrencyByCode($invoice['currency']);
					$earlier = new DateTime(date("Y-m-d",strtotime($reminder_date)));
					$later = new DateTime(date("Y-m-d",strtotime($invoice['due_date'])));
					$day_diff = $later->diff($earlier)->format("%a");
					
					$subject = str_replace("{{user.business.name}}",$businessdata['business_name'],$result['subject']);
					if (isset($_POST['discount_percentage'])) {
						$subject = str_replace("{{discount}}",$_POST['discount_percentage']."%",$subject);
					}
					if (isset($postdata['discount_amount_percentage'])) {
						$subject = str_replace("{{discount}}",$postdata['discount_amount_percentage']."%",$subject);
					}
					$subject = str_replace("{{invoice.number}}","#".$invoice['invoice_no'],$subject);
					$subject = str_replace("{{due.date}}",date(DATEFORMAT,strtotime($invoice['due_date'])),$subject);
					if (isset($_POST['paid_by_date']) && $_POST['paid_by_date']!="") {
						$subject = str_replace("{{new.due.date}}",date(DATEFORMAT,strtotime($_POST['paid_by_date'])),$subject);
					}
										
					$body = str_replace("\r\n","<br>",$result['body']);
					$body = str_replace("{{user.business.name}}",$businessdata['business_name'],$body);

					if ($invoice['client_details']['name']=="") {
						$body = str_replace(" {{name}}","",$body);
					} else {
						$body = str_replace("{{name}}",$invoice['client_details']['name'],$body);
					}
					
					$body = str_replace("{{due.date}}",date(DATEFORMAT,strtotime($invoice['due_date'])),$body);
					$body = str_replace("{{invoice.link}}",'<a href="'.SITE_URL.'/invoice/'.$invoice['uuid'].'" target="_blank">'.__("View Invoice",true).'</a>',$body);
					$body = str_replace("{{days.until.due}}",$day_diff,$body);
					if (isset($postdata['discount_percentage'])) {
						$body = str_replace("{{discount}}",$postdata['discount_percentage']."%",$body);
					}
					if (isset($postdata['discount_amount_percentage'])) {
						$body = str_replace("{{discount}}",$postdata['discount_amount_percentage']."%",$body);
					}
					if (isset($postdata['discount_amount_value']) && $postdata['discount_amount_value']!="") {
						$body = str_replace("{{savings}}",$currency.number_format($postdata['discount_amount_value'],2,DECIMALS,THOUSANDS),$body);
					}
					if (isset($postdata['paid_by_date']) && $postdata['paid_by_date']!="") {
						$body = str_replace("{{new.due.date}}",date(DATEFORMAT,strtotime($postdata['paid_by_date'])),$body);
					}
					if (isset($postdata['discount_amount_value']) && $postdata['discount_amount_value']!="") {
						$body = str_replace("{{new.total}}",$currency.number_format($invoice['amount']-$postdata['discount_amount_value'],2,DECIMALS,THOUSANDS),$body);
					}

					$body = str_replace("{{amount}}",$currency.number_format($invoice['amount'],2,$email_decimals,$email_thousands),$body);
					$body = str_replace(" 0&nbsp;days"," until today",$body);
					$body = str_replace(" 1&nbsp;days"," 1 day",$body);
					$body = str_replace("0&nbsp;days","until today",$body);
					$body = str_replace("1&nbsp;days","1 day",$body);
					$body = str_replace("<b>0</b><span>&nbsp;<b>dias</b>","<b>até hoje</b>",$body);
					$body = str_replace("<b>1</b><span>&nbsp;<b>dias</b>","<b>1 dia</b>",$body);

					$css_styles = '<style type="text/css">@import url("https://fonts.googleapis.com/css?family=Nunito+Sans:400,700&display=swap"); body { margin: 0; } #email-wrapper { font-family: "Nunito Sans", Helvetica, Arial, sans-serif !important; }</style>';



					$client_data = $UM->getGlobalbyId('client_lists',$invoice['client_id']);
					$bank_account_data = $UM->getGlobalbyId('bank_accounts',$invoice['bank_account_id']);
					$business_account_data = $UM->getGlobalbyId('business_accounts',$invoice['business_id']);
					$currency = $UM->fetchCurrencyByCode($invoice['currency']);
					$due = $invoice['due_date'];
					$now = date("Y-m-d");
					$due_d_text = "left";
					if ($email_type == "overdue reminder") {
					    $is_overdue = '<tr><td colspan=2 class="attributes_item" style="text-align: center;padding-top:25px;color:#F44336;font-size: 32px;"><b>'.__("OVERDUE",true).'</b></td></tr>';
					    $belowBoxText = '<p>'.__("It is important to remember that non-payment of this debt can lead to a protest at the notary and restrictive credit registration.",true).'</p><p>'.__("If we do not receive a response within 3 days of receiving this email, active services will be blocked.",true).'</p>';
					} else {
					    $is_overdue = '';
					    $belowBoxText = '<p>'.__("Please, let us know if you have any doubt or if we can do anything else to help.",true).'</p>';
					}
											
					$col = [];
				    $col['address']['email'] = $postdata['test_email'];
				    $recipients[] = $col;

					$supplierLogo = "";
					if ($business_account_data['business_logo']!="") {
					    $supplierLogo = '<img src="'.$business_account_data['business_logo'].'" alt="'.$business_account_data['business_name'].'" style="max-height:125px;"/>';
					}
					$supplierName = $business_account_data['business_name'];
					$supplierNumber = $business_account_data['business_no'];
					$supplierAccountName = $bank_account_data['account_name'];
					$supplierBankName = $bank_account_data['bank_name'];
					$supplierAccountNumber = $bank_account_data['account_number'];
					$supplierPixNumber = $bank_account_data['pix_number'];

					$name = $client_data['name'];
					$due_date = date(DATEFORMAT,strtotime($invoice['due_date']));						

					if (isset($postdata['discount_amount_value']) && $postdata['discount_amount_value']!=null) {
						$total_updated_amount -= $postdata['discount_amount_value'];
					    $amount = $currency.number_format($total_updated_amount,2,$email_decimals,$email_thousands);
					} else {
					    $amount = $currency.number_format($total_updated_amount,2,$email_decimals,$email_thousands);  
					}
					  

					if ($invoice['attachment']!="") {
					    $attachment_text = __('Please find attached the invoice, and also below a summary to make the payment process easier for you:',true);
					    $invoiceBtnLink = '<tr>
					                          <td class="attributes_item" colspan="2" style="padding:40px 0;text-align:center;">
					                                <a href="'.SITE_URL.'/p/'.$invoice['uuid'].'" target="_blank" style="text-decoration: none!important;color: #212122!important;border-radius: 3px;padding: 14px 20px;background-color: #c2d5a8;font-weight:500;">'.__("View Invoice",true).'</a>
					                              </td>
					                        </tr>';
					} else {
					    $attachment_text = __("Please find below a summary to make the payment process easier for you:",true);
					    $invoiceBtnLink = '<tr>
					                          <td class="attributes_item" colspan="2" style="padding:40px 0;text-align:center;">
					                                <a href="'.SITE_URL.'/p/'.$invoice['uuid'].'" target="_blank" style="text-decoration: none!important;color: #212122!important;border-radius: 3px;padding: 14px 20px;background-color: #c2d5a8;font-weight:500;">'.__("View Invoice",true).'</a>
					                              </td>
					                        </tr>';
					}

					  
					$html = '<table id="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="padding-top:20px !important;    background-color: #f2f4f6;">
					  <tr style="display:none;">'.$body.'<td></td></tr>
					  <tr>
					    <td align="center">
					      <table class="email-content" width="100%" cellpadding="0" cellspacing="0" role="presentation">          
					        <tr>
					          <td class="email-body" style="max-width: 570px;" cellpadding="0" cellspacing="0">
					            <table class="email-body_inner" align="center" style="max-width: 570px;padding:10px 0;background-color:#fff;" cellpadding="0" cellspacing="0" role="presentation">
					              <tr>
					                <td>
					                  <div style="padding: 10px 30px 20px;">
					                  <div style="text-align:center;">'.$supplierLogo.'</div>
					                    '.$body.'
					                    <p>'.$attachment_text.'</p>
					                    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f5f5f5;margin:0;">
					                      '.$is_overdue.'
					                      <tr>
					                        <td class="attributes_content">
					                          <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
					                            <tr>
					                              <td class="attributes_item" style="width:50%;text-align: center;padding-top:25px;color:#212122">
					                                <span class="f-fallback">
					                                  <strong>'.__("Amount Due",true).':</strong> '.$amount.'
					                                </span>
					                              </td>
					                              <td class="attributes_item" style="text-align: center;padding-top:25px;color:#212122">
					                                <span class="f-fallback">
					                                  <strong>'.__("Due Date",true).':</strong> '.$due_date.'
					                                </span>
					                              </td>
					                            </tr>';
					                            
					                            
					                            $html .= $invoiceBtnLink.'</table>
					                        </td>
					                      </tr>
					                    </table>                    
					                    <p>'.__("After completing the payment, please answer this e-mail and attach the receipt.",true).'</p>'.$belowBoxText.'
					                    <p>'.__("Thank you!",true).'</p>
					                    <p>'.__("Best",true).',<br>'.__("Team Kolek",true).'</p>
					                    <table class="body-sub" role="presentation">
					                      <tr>
					                        <td>
					                          <p class="f-fallback sub">'.__("Please ignore this e-mail in case you already paid the invoice",true).'</p>
					                          <p class="f-fallback sub">'.__("If you have any questions about this invoice, simply reply to this email or reach out to our",true).' <a href="mailto:support@wekolek.com" style="color:#98a783;">'.__("support team",true).'</a> '.__("for help",true).'.</p>
					                        </td>
					                      </tr>
					                    </table>
					                  </div>
					                </td>
					              </tr>
					              <tr>
					                <td class="text-center" style="padding:0px 20px 20px 20px;text-align: center;;">
					                  <a href="https://wekolek.com/" style="text-decoration: none;color: #a8aaaf;font-weight: 500;font-size: 12px;line-height: 1.4 !important;">
					                  '.__("If you are also tired of chasing clients for payments",true).', <br>'.__("Click Here and let KoleK do that work for you!",true).' <br>
					                  '.__("That way, you can spend less time worrying about your finances and <br>more time doing what really matters: running your business.",true).'
					                </a>
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
					                  <p style="color: #a8aaaf;font-size: 13px;">
					                    '.CORPORATE_NAME.'
					                    <br>'.str_replace(", SINGAPORE","<br>SINGAPORE",FOOTER_ADDRESS).'
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

					if ($invoice['attachment']!="") {
					    $fetch_attachments = explode(",",$invoice['attachment']);
					    $attachments = [];
					    $count = 1;
					    foreach ($fetch_attachments as $key3 => $value3) {
					        $b64Doc = chunk_split(base64_encode(file_get_contents($value3)));
					        $pdffile = str_replace("\r\n", "", $b64Doc);
					        $col = [];
					        if (count($fetch_attachments)==1) {
					          $col['name'] = __("Invoice #",true).$invoice['invoice_no'].".pdf";
					        } else {
					          $col['name'] = __("Invoice #",true).$invoice['invoice_no']."-".$count++.".pdf";
					        }
					        
					        $col['type'] = "application/pdf";
					        $col['data'] = $pdffile;
					        $attachments[] = $col;
					    }
					}				

					$emailArr = [];
					$emailArr['campaign_id'] = $postdata['ar_id'];
					$emailArr['recipients'] = $recipients;
					if ($invoice['attachment']!="") {
						$emailArr['content']['attachments'] = $attachments;
					}
					$emailArr['content']['reply_to'] = "support@wekolek.com";  
					$emailArr['content']['from']['email'] = "invoicing@mail.wekolek.com";
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
					$headers[] = 'Authorization: cc7433de5492eb10c89fbd43d3f7887fabed61c1';
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

					$result = json_decode(curl_exec($ch));					
					if (curl_errno($ch)) {
					      echo 'Error:' . curl_error($ch);
					}

					if ($result) {
					    if (isset($result->errors)) {
					      	$return['status'] = false;
							$return['message'] = "Failed: ".$result->errors[0]->message;   					     
					    } else {
					    	$return['status'] = true;
							$return['message'] = __("Email sent!",true);
							$return['data'] = $payload;
							$return['sent_at'] = date(DATEFORMAT." H:i A");
					    }    
					} else {
					    $return['status'] = false;
						$return['message'] = __("Failed to send email. Please contact support.",true);
					}
					curl_close($ch);				
				} else {
					$return['status'] = false;
					$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
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

	function send_test_email_default_collection() {
		if (isset($_POST['token'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				if ($payload) {
					$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
					$UM = new UserModel();
					parse_str($_POST['data'],$postdata);
					$invoicedata = $UM->getGlobalbyId("debt_collection",$postdata['ar_id']);
					$inv_id = $postdata['ar_id'];
					$invoicedata['invoice_no'] = "1".sprintf('%06d', $invoicedata['id']);

					$reminder_date = date("Y-m-d");
					$email_type = $postdata['trigger_type'];					
					
					if (LANG=="BR") {
						$lang = "portuguese";
					} else {
						$lang = "english";
					}

					$result = $UM->fetchDefaultEmailTemplate($email_type,$lang);
							
					$businessdata = $UM->getGlobalbyId("business_accounts",$invoicedata['business_id']);
					$bankaccountdata = $UM->getGlobalbyId("bank_accounts",$invoicedata['bank_account_id']);
					$invoicedata['client_details'] = $UM->getGlobalbyId("client_lists",$invoicedata['client_id']);

					$col = [];
				    $col['address']['email'] = $postdata['test_email'];
				    $recipients[] = $col;

					$invoicePayments = $UM->fetchInvoicePayments($inv_id);
					$total_partial_collection =0;
					if (count($invoicePayments)>0) {
						foreach ($invoicePayments as $pp) {
							$total_partial_collection += $pp['amount'];
						}
					}
					$currency = "USD";
					$total_updated_amount = 0;
					foreach (json_decode($invoicedata['invoice_items']) as $line_inv_id) {
						$invoice = $UM->getGlobalbyId('account_receivables',$line_inv_id);
						$currency = $invoice['currency'];										
						$pp_val = 0;
						$partial_payments = $UM->fetchInvoicePayments($line_inv_id);
						if (count($partial_payments)>0) {
							foreach ($partial_payments as $pp) {
								$pp_val += $pp['amount'];
							}
						}
						$partialPayments[$line_inv_id] = $pp_val;

						if (isset($invoice['discount']) && $invoice['discount']!=null) {
				           $discounts = $invoice['discount'];
				        } else {
				           $discounts = 0;
				        }
				        if (isset($invoice['interest']) && $invoice['interest']!=null && (date("Y-m-d") > date("Y-m-d",strtotime($invoice['due_date'])))) {
				           $interest = $invoice['interest'];
				        } else {
				           $interest = 0;
				        }   
				        if (isset($invoice['late_fee']) && $invoice['late_fee']!=null && (date("Y-m-d") > date("Y-m-d",strtotime($invoice['due_date'])))) {
				           $late_fee = $invoice['late_fee'];
				        } else {
				           $late_fee = 0;
				        }			        
						$subtotal = $invoice['amount'];
				        $discounts = ($subtotal*($discounts/100));
				        $late_fee = ($subtotal*($late_fee/100));
				        $earlier = new DateTime(date("Y-m-d"));
				        $later = new DateTime(date("Y-m-d",strtotime($invoice['due_date'])));
				        $day_overdue = $later->diff($earlier)->format("%a");
				        $interest = (($interest/100)/30) * $day_overdue * $subtotal;
				        $total_updated_amount += $subtotal - $pp_val - $discounts + $late_fee + $interest;

				        $invoice['discount'] = $discounts;
				        $invoice['late_fee'] = $late_fee;
				        $invoice['interest'] = $interest;
				        $invoice['partial_payments'] = $pp_val;
				        $invoice['updated_amount'] = $subtotal - $pp_val - $discounts + $late_fee + $interest;
				        $inv_items[] = $invoice;
					}
					$data['currency'] = $UM->fetchCurrencyByCode($currency);

					$invoicedata['amount'] = $total_updated_amount - $total_partial_collection;


					if ($currency=="BRL") {
						$email_decimals = ",";
						$email_thousands = ".";
					} else {
						$email_decimals = ".";
						$email_thousands = ",";
					}

					$currency = $UM->fetchCurrencyByCode($currency);
					$subject = str_replace("{{user.business.name}}",$businessdata['business_name'],$result['subject']);					
					$subject = str_replace("{{invoice.number}}","#".$invoicedata['invoice_no'],$subject);
					if (isset($postdata['paid_by_date']) && $postdata['paid_by_date']!="") {
						$subject = str_replace("{{new.due.date}}",date(DATEFORMAT,strtotime($postdata['paid_by_date'])),$subject);
					}

					$body = str_replace("\r\n","<br>",$result['body']);
					$body = str_replace("{{user.business.name}}",$businessdata['business_name'],$body);
					if ($invoicedata['client_details']['name']=="") {
						$body = str_replace(" {{name}}","",$body);
					} else {
						$body = str_replace("{{name}}",$invoicedata['client_details']['name'],$body);	
					}
					
					$body = str_replace("{{invoice.link}}",'<a href="'.SITE_URL.'/invoice/'.$invoicedata['uuid'].'" target="_blank">'.__("View Invoice",true).'</a>',$body);
					$body = str_replace("{{amount}}",$currency.number_format($invoicedata['amount'],2,$email_decimals,$email_thousands),$body);
					$body = str_replace(" 0&nbsp;days"," until today",$body);
					$body = str_replace(" 1&nbsp;days"," 1 day",$body);
					$body = str_replace("0&nbsp;days","until today",$body);
					$body = str_replace("1&nbsp;days","1 day",$body);
					$body = str_replace("<b>0</b><span>&nbsp;<b>dias</b>","<b>até hoje</b>",$body);
					$body = str_replace("<b>1</b><span>&nbsp;<b>dias</b>","<b>1 dia</b>",$body);					
					
					$payment_method = "<br>".__('Bank Name',true).": ".$bankaccountdata['bank_name']."<br>".__('Account Name',true).": ".$bankaccountdata['account_name']."<br>".__('Account No',true).":".$bankaccountdata['account_number'];
					$body = str_replace("{{payment.method}}",$payment_method,$body);
					$now = date("Y-m-d");

					$due_d_text = "left";
					if ($email_type=="overdue reminder" || $email_type=="reminder debt collection" || $email_type=="debt collection negotiation") {
					    $is_overdue = '<tr>
					                    <td colspan=2 class="attributes_item" style="text-align: center;padding-top:25px;color:#F44336;font-size: 32px;"><b>'.__("OVERDUE",true).'</b></td>
					                  </tr>';
					} else {
					    $is_overdue = '';
					}
					$supplierLogo = "";
					if ($businessdata['business_logo']!="") {
					    $supplierLogo = '<img src="'.$businessdata['business_logo'].'" alt="'.$businessdata['business_name'].'" style="max-height:120px;"/>';
					}
					$supplierName = $businessdata['business_name'];
					$supplierNumber = $businessdata['business_no'];
					$supplierAccountName = $bankaccountdata['account_name'];
					$supplierBankName = $bankaccountdata['bank_name'];
					$supplierAccountNumber = $bankaccountdata['account_number'];
					$supplierPixNumber = $bankaccountdata['pix_number'];

					$name = $invoicedata['client_details']['name'];							

					$amount = $currency.number_format($invoicedata['amount'],2,$email_decimals,$email_thousands);


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
					                  <div style="text-align:center;">'.$supplierLogo.'</div>
					                    '.$body;					            

					            $html .= '<p style="margin-top:10px;">'.__("Click the link to access the breakdown of overdue invoices and also see the payment options to settle these pending issues quickly and easily.",true).'</p>';

			                    $html .= '<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f5f5f5;margin:0;">
					                      '.$is_overdue.'
					                      <tr>
					                        <td class="attributes_content">
					                          <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
					                            <tr>
					                              <td class="attributes_item" style="text-align: center;padding-top:25px;color:#212122;">
					                                <span class="f-fallback">
					                                  <strong>'.__("Amount Due",true).':</strong> '.$amount.'
					                                </span>
					                              </td>
					                            </tr></table>
					                        </td>
					                      </tr>
					                    </table>';
					                    $html .= '<table align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="height:100px;background-color: #f5f5f5;margin:0 0 20px;padding: 40px 0;">
						                      <tr>
						                        <td align="center">
						                          <table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation">
						                            <tr>
						                              <td align="center">
						                                <a href="'.SITE_URL.'/p/'.$invoicedata['uuid'].'" target="_blank" style="text-decoration: none!important;color: #212122!important;border-radius: 3px;padding: 14px 20px;background-color: #c2d5a8;font-weight:500;">'.__("View Invoice",true).'</a>
						                              </td>
						                            </tr>
						                          </table>
						                        </td>
						                      </tr>
						                    </table>';
					                    $html .= '<p>'.__("After completing the payment, please answer this e-mail and attach the receipt.",true).'</p>';

					                    if ($email_type=="debt collection negotiation") {
					                    	$html .= '<p>'.__("We know that having debts is bad, so we would like to help you settle this debt by giving the option to request a renegotiation - just click on the link below and press the 'Request Renegotiation' button and we will contact you shortly.",true).'</p>';
					                    	$html .= '<p>'.__("It is important to remember that non-payment of this debt can lead to a protest at the notary and restrictive credit registration.",true).'</p><p>'.__("If we do not receive a response within 3 days of receiving this email, active services will be blocked.",true).'</p>';
					                    } else {
					                    	$html .= '<p>'.__("Please, let us know if you have any doubt or if we can do anything else to help.",true).'</p>';
					                    }

					                    $html .= '<p>'.__("Thank you!",true).'</p>
					                    <p>'.__("Best",true).',<br>'.__("Team Kolek",true).'</p>
					                    <table class="body-sub" role="presentation">
					                      <tr>
					                        <td>
					                          <p class="f-fallback sub">'.__("Please ignore this e-mail in case you already paid the invoice",true).'</p>
					                          <p class="f-fallback sub">'.__("If you have any questions about this invoice, simply reply to this email or reach out to our",true).' <a href="mailto:support@wekolek.com" style="color:#98a783;">'.__("support team",true).'</a> '.__("for help",true).'.</p>
					                        </td>
					                      </tr>
					                    </table>
					                  </div>
					                </td>
					              </tr>
					              <tr>
					                <td class="text-center" style="padding:0px 20px 20px 20px;text-align: center;;">
					                  <a href="https://wekolek.com/" style="text-decoration: none;color: #a8aaaf;font-weight: 500;font-size: 12px;line-height: 1.4 !important;">
					                  '.__("If you are also tired of chasing clients for payments",true).', <br>'.__("Click Here and let KoleK do that work for you!",true).' <br>
					                  '.__("That way, you can spend less time worrying about your finances and <br>more time doing what really matters: running your business.",true).'
					                </a>
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
					                  <p style="color: #a8aaaf;font-size: 13px;">
					                    '.CORPORATE_NAME.'
					                    <br>'.str_replace(", SINGAPORE","<br>SINGAPORE",FOOTER_ADDRESS).'
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

					$css_styles = '<style type="text/css">@import url("https://fonts.googleapis.com/css?family=Nunito+Sans:400,700&display=swap"); body { margin: 0; } #email-wrapper { font-family: "Nunito Sans", Helvetica, Arial, sans-serif !important; }</style>';			

					$emailArr = [];
					$emailArr['campaign_id'] = $postdata['ar_id'];
					$emailArr['recipients'] = $recipients;
					$emailArr['content']['reply_to'] = "support@wekolek.com";  
					$emailArr['content']['from']['email'] = "invoicing@mail.wekolek.com";
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
					$headers[] = 'Authorization: cc7433de5492eb10c89fbd43d3f7887fabed61c1';
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

					$result = json_decode(curl_exec($ch));
					if (curl_errno($ch)) {
					      echo 'Error:' . curl_error($ch);
					}

					if ($result) {
					    if (isset($result->errors)) {
					      	$return['status'] = false;
							$return['message'] = "Failed: ".$result->errors[0]->message;   					     
					    } else {
					    	$return['status'] = true;
							$return['message'] = __("Email sent!",true);
							$return['data'] = $payload;
							$return['sent_at'] = date(DATEFORMAT." H:i A");
					    }    
					} else {
					    $return['status'] = false;
						$return['message'] = __("Failed to send email. Please contact support.",true);
					}
					curl_close($ch);				
				} else {
					$return['status'] = false;
					$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
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



	// DASHBOARD
	
	function fetch_cashflow_graph() {
		if (isset($_POST['token'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
					
				$filters = $_POST['filters'];
				$duration = $accumulatedData = $unpaidData = $paidData = [];
				$accumulatedTotal = 0;
				$total_paid = 0;
				$total_unpaid = 0;
				$total_accumulated = 0;

				if ($_SESSION['userdata']['country']=="philippines") {
					$currency = "₱";
				} else if ($_SESSION['userdata']['country']=="brazil") {
					$currency = "R$";
				} else {
					$currency = "$";
				}

				$total_date['start_date'] = date('Y-m-d', strtotime('first day of +0 month',strtotime($filters['start_date'])));
                $total_date['end_date'] = date('Y-m-d', strtotime('last day of +0 month',strtotime($filters['end_date'])));

                if ($payload->role=="admin") {
					$paidResult = $UM->fetchCashflowGraph(null,$total_date,"Paid");
					$unpaidResult = $UM->fetchCashflowGraph(null,$total_date,"Unpaid");
				} else {
					$paidResult = $UM->fetchCashflowGraph($payload->uuid,$total_date,"Paid");
					$unpaidResult = $UM->fetchCashflowGraph($payload->uuid,$total_date,"Unpaid");
				}

				foreach ($paidResult as $ar) {
					$total_paid += $ar['amount'];
				}

				foreach ($unpaidResult as $ar) {										
					if ($ar['due_date'] >= date("Y-m-d")) {
						$total_accumulated += $ar['amount'];
					} else {
						$total_unpaid += $ar['amount'];	
					}
				}

				
				switch($filters['filter_view']){
					case "daily":
						$begin = new DateTime( date("Y-m-d",strtotime($filters['start_date'])) );
						$end = new DateTime( date("Y-m-d",strtotime($filters['end_date'])) );
						$interval = DateInterval::createFromDateString('1 day');
						$period = new DatePeriod($begin, $interval, $end);

						foreach($period as $dt) {
							$dt = (array)$dt;
							$col = [];
						    $col['start_date'] = date("Y-m-d",strtotime($dt['date']));
						    $col['end_date'] = date("Y-m-d",strtotime($dt['date']));
						    $duration[] = $col;
						}
						$col = [];
						$col['start_date'] = $filters['end_date'];
						$col['end_date'] = $filters['end_date'];
						$duration[] = $col;
						
						foreach ($duration as $key_index => $date_period) {
							$array_index = date(DATEFORMAT2,strtotime($date_period['start_date']));
							$paidData[$array_index] = 0;
							$unpaidData[$array_index] = 0;
							$accumulatedData[$array_index] = $accumulatedTotal;

							if ($payload->role=="admin") {
								$paid = $UM->fetchCashflowGraph(null,$date_period,"Paid");
								$unpaid = $UM->fetchCashflowGraph(null,$date_period,"Unpaid");
							} else {
								$paid = $UM->fetchCashflowGraph($payload->uuid,$date_period,"Paid");
								$unpaid = $UM->fetchCashflowGraph($payload->uuid,$date_period,"Unpaid");
							}

							foreach ($paid as $ar) {
								$paidData[$array_index] += $ar['amount'];
							}

							foreach ($unpaid as $ar) {
								$unpaidData[$array_index] += $ar['amount'];
								$accumulatedData[$array_index] += $ar['amount'];
								$accumulatedTotal += $ar['amount'];
							}							
							
						}
					break;
					case "weekly":
						$begin = new DateTime( date("Y-m-d",strtotime($filters['start_date'])) );
						$end = new DateTime( date("Y-m-d",strtotime($filters['end_date'])) );
						$interval = DateInterval::createFromDateString('1 week');
						$period = new DatePeriod($begin, $interval, $end);

						foreach($period as $dt) {
							$dt = (array)$dt;
							$col = [];
						    $col['start_date'] = date("Y-m-d",strtotime('last Sunday', strtotime($dt['date'])));
						    $col['end_date'] = date("Y-m-d",strtotime('Saturday this week', strtotime($dt['date'])));
						    $duration[] = $col;
						}

						$duration[0]['start_date'] = $filters['start_date'];
						$duration[count($duration)-1]['end_date'] = $filters['end_date'];
						foreach ($duration as $key_index => $date_period) {
							$array_index = date(DATEFORMAT2,strtotime($date_period['end_date']));
							$paidData[$array_index] = 0;
							$unpaidData[$array_index] = 0;
							$accumulatedData[$array_index] = $accumulatedTotal;

							if ($payload->role=="admin") {
								$paid = $UM->fetchCashflowGraph(null,$date_period,"Paid");
								$unpaid = $UM->fetchCashflowGraph(null,$date_period,"Unpaid");
							} else {
								$paid = $UM->fetchCashflowGraph($payload->uuid,$date_period,"Paid");
								$unpaid = $UM->fetchCashflowGraph($payload->uuid,$date_period,"Unpaid");
							}

							foreach ($paid as $ar) {
								$paidData[$array_index] += $ar['amount'];
							}

							foreach ($unpaid as $ar) {
								$unpaidData[$array_index] += $ar['amount'];
								$accumulatedData[$array_index] += $ar['amount'];
								$accumulatedTotal += $ar['amount'];
							}							
						}
					break;
					case "monthly":
						$begin = new DateTime( date("Y-m-d",strtotime($filters['start_date'])) );
						$end = new DateTime( date("Y-m-d",strtotime($filters['end_date'])) );
						$interval = DateInterval::createFromDateString('1 month');
						$period = new DatePeriod($begin, $interval, $end);

						foreach($period as $dt) {
							$dt = (array)$dt;
							$col = [];
						    $col['start_date'] = date("Y-m-d",strtotime('first day of +0 month', strtotime($dt['date'])));
						    $col['end_date'] = date("Y-m-d",strtotime('last day of +0 month', strtotime($dt['date'])));
						    $duration[] = $col;
						}

						$duration[0]['start_date'] = $filters['start_date'];
						$duration[count($duration)-1]['end_date'] = $filters['end_date'];

						foreach ($duration as $key_index => $date_period) {
							
							$array_index = date("M",strtotime($date_period['start_date']));
							$paidData[$array_index] = 0;
							$unpaidData[$array_index] = 0;
							$accumulatedData[$array_index] = $accumulatedTotal;

							if ($payload->role=="admin") {
								$paid = $UM->fetchCashflowGraph(null,$date_period,"Paid");
								$unpaid = $UM->fetchCashflowGraph(null,$date_period,"Unpaid");
							} else {
								$paid = $UM->fetchCashflowGraph($payload->uuid,$date_period,"Paid");
								$unpaid = $UM->fetchCashflowGraph($payload->uuid,$date_period,"Unpaid");
							}

							foreach ($paid as $ar) {
								$paidData[$array_index] += $ar['amount'];
							}

							foreach ($unpaid as $ar) {
								$unpaidData[$array_index] += $ar['amount'];
								$accumulatedData[$array_index] += $ar['amount'];								
								$accumulatedTotal += $ar['amount'];
							}
							
						}
					break;
				}
				$graphData = [];										

				if ($filters['cashflow_type']=="actual") {
					$graphData[0] = [__('Receivables',true),__('Paid',true),__('Unpaid',true)];
					foreach ($paidData as $key => $value) {
						$col = [];
						$col[] = $key;
						$col[] = $value;
						$col[] = $unpaidData[$key];
						$graphData[] = $col;
					}
				} else if ($filters['cashflow_type']=="projected") {
					$graphData[0] = [__('Receivables',true),__('Projected',true)];
					foreach ($accumulatedData as $key => $value) {
						$col = [];
						$col[] = $key;
						$col[] = $value;
						$graphData[] = $col;
					}
				} else if ($filters['cashflow_type']=="projected inflow") {
					$graphData[0] = [__('Receivables',true),__('Projected Inflow',true)];
					foreach ($unpaidData as $key => $value) {
						$col = [];
						$col[] = $key;
						$col[] = $value;
						$graphData[] = $col;
					}
				}

				$return['duration'] = $duration;
				$return['status'] = true;
				$return['graph_data'] = $graphData;
				$return['accumulated_data'] = $accumulatedData;
				$return['total_paid'] = $currency.number_format($total_paid,2,DECIMALS,THOUSANDS);
				$return['total_unpaid'] = $currency.number_format($total_unpaid,2,DECIMALS,THOUSANDS);
				if ($total_unpaid==0) {
					$return['total_default'] = "0%";
				} else {
					$return['total_default'] = number_format(($total_unpaid / ($total_unpaid + $total_paid)*100),2)."%";	
				}
				
				$return['total_projected'] = $currency.number_format($total_accumulated,2,DECIMALS,THOUSANDS);
				$return['total_date'] = date("M j, Y",strtotime($total_date['start_date']))." - ".date("M j, Y",strtotime($total_date['end_date']));
							
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

	function fetch_cashflow() {
		if (isset($_POST['token'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();				
				$statements = $UM->fetchAllUserStatements($payload->uuid);
				if (count($statements)==0 || NULL) {
					$return['status'] = true;
					$return['datatable'] = [];
					$return['message'] = __("No bank statements found. Please connect bank account first.",true);
					echo json_encode($return);exit;
				}
				$datatable = [];
				$decimals = ".";
				$thousands = ",";
				$banks = [];
				$graph = [];
				$graphtotal = 0;
				if ($statements) {
					$inflow = 0;
					$outflow = 0;
					$currency = "USD";
					foreach ($statements as $key => $value) {
						if ($value['currency']=="BRL") {
							$decimals = ",";
							$thousands = ".";
						} else {
							$decimals = ".";
							$thousands = ",";
						}
						$col = [];
						$sign = "-";
						if ($value['credit_debit_type']=="CREDITO") {
							$sign = "+";
							$inflow += $value['amount'];
						} else {
							$outflow += $value['amount'];
						}
						$col[] = date(DATEFORMAT,strtotime($value['transaction_date']));
						$col[] = $value['description'];
						$col[] = $sign.number_format($value['amount'],2,$decimals,$thousands);
						$col[] = $value['bank_account_details']['bank_name'];
						$col[] = $value['credit_debit_type'];
						$col[] = $value['transaction_type'];
						$datatable[] = $col;
						$currency = $value['currency'];

						if (isset($banks[$value['bank_account_details']['uuid']])) {
						 	if ($value['credit_debit_type']=="CREDITO") {
								$banks[$value['bank_account_details']['uuid']]['amount'] += $value['amount'];
							} else {
								$banks[$value['bank_account_details']['uuid']]['amount'] -= $value['amount'];
							}
					 	} else {
					 		$banks[$value['bank_account_details']['uuid']]['amount'] = 0;
					 		$banks[$value['bank_account_details']['uuid']]['name'] = $value['bank_account_details']['bank_name'];
					 		$banks[$value['bank_account_details']['uuid']]['account_number'] = $value['bank_account_details']['account_number'];
					 		$banks[$value['bank_account_details']['uuid']]['logo'] = $value['bank_logo'];
					 		if ($value['credit_debit_type']=="CREDITO") {
								$banks[$value['bank_account_details']['uuid']]['amount'] += $value['amount'];
							} else {
								$banks[$value['bank_account_details']['uuid']]['amount'] -= $value['amount'];
							}					 		
					 	} 
					 	
						if ($value['credit_debit_type']=="CREDITO") {
							if (!isset($graph[$value['transaction_date']]['credit'])) {
								$graph[$value['transaction_date']]['credit'] = 0;
								$graph[$value['transaction_date']]['debit'] = 0;
							}
							$graph[$value['transaction_date']]['credit'] += $value['amount'];
						}
						if ($value['credit_debit_type']=="DEBITO") {
							if (!isset($graph[$value['transaction_date']]['debit'])) {
								$graph[$value['transaction_date']]['debit'] = 0;
								$graph[$value['transaction_date']]['credit'] = 0; 
							}
							$graph[$value['transaction_date']]['debit'] += $value['amount'];
						}
					}
				}				
				$loopCount = round(count($graph) / 5);
				$startCount = 1;
				ksort($graph);
				$startValue = 0;

				$graphArr = [];
				$graphLowest = null;
				$graphHighest = null;
				$counter = 1;
				$startCounter = 1;
				$loopCount = round(count($graph)/5);
				foreach ($graph as $key => $value) {					
					$graphtotal += $value['credit']-$value['debit'];					
					if ($startCounter++ == $loopCount || $counter==count($graph)) {
						$col = [];
						$col['amount'] = $graphtotal;
						$col['date'] = $key;
						$graphArr[] = $col;
						$startCounter = 0;

						if ($graphLowest=== null || $graphtotal < $graphLowest) {
							$graphLowest = $graphtotal;
						}
						if ($graphHighest=== null || $graphtotal > $graphHighest) {
							$graphHighest = $graphtotal;
						}
					}															
					$counter++;
				}
				$graphData = '<tbody>';
				$prev = 0;
				$graphLowest *= 1.25;
				$graphHighest *= 1.50;
				
				foreach ($graphArr as $key => $value) {
					if ($value['amount']<0) {
						$percentage = 0;
					} else {
						$percentage = ($value['amount'] - $graphLowest) / ($graphHighest - $graphLowest);
					}
					$graphData .= '<tr>';
					$graphData .= '<th scope="row">'.date(DATEFORMAT2,strtotime($value['date'])).'</th>';
					$graphData .= '<td scope="row"></td>';
				    $graphData .= '<td style="--start: '.$prev.'; --size: '.$percentage.'"> <span class="data">'.number_format($value['amount'],2,DECIMALS,THOUSANDS).'</span> </td></tr>';
					$prev = $percentage;
				}
				$graphData .= '<tr><th scope="row">'.date(DATEFORMAT2).'</th><td scope="row"></td></tr></tbody>';

				$cashbalance = '';
				$currency = $UM->fetchCurrencyByCode($currency);
				$totalbalance = 0;
				foreach ($banks as $key => $value) {
					$negSign = "";
					$amount = $value['amount'];
					if ($value['amount']<0) {
						$amount = abs($value['amount']);
						$negSign = "-";
					}

					$cashbalance .= '<tr>
						<td class="pdx-0">
							<img src="'.$value['logo'].'" style="height:40px;float:left;margin-right:10px;">
							<div class="p mg0 semi-bold">'.$value['account_number'].'</div>
							<div class="p mg0 text-muted">'.$value['name'].'</div>
						</td>
						<td class="pdx-0 h5 text-right">'.$negSign.$currency.number_format($amount,2,$decimals,$thousands).'</td>
					</tr>';
					$totalbalance += $value['amount'];
				}
				$cashbalance .= '<tr>
					<td class="pdx-0">
						<div class="h4 mg0 semi-bold">'.__("Total Balance",true).'</div>
					</td>
					<td class="pdx-0 h5 text-right">'.$currency.number_format($totalbalance,2,$decimals,$thousands).'</td>
				</tr>';


				
				$return['status'] = 1;
				$return['datatable'] = $datatable;
				$return['cashbalance'] = $cashbalance;
				$return['inflow'] = $currency.number_format($inflow,2,$decimals,$thousands);
				$return['outflow'] = $currency.number_format($outflow,2,$decimals,$thousands);
				$return['netcash'] = $currency.number_format(($inflow-$outflow),2,$decimals,$thousands);
				$return['graphdata'] = $graphData;

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


	function fetch_dashboard_data() {
		if (isset($_POST['token'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
					
				$filters = $_POST['filters'];
				$total_date = null;				
				// $total_date['start_date'] = date('Y-m-d', strtotime('first day of +0 month'));
                // $total_date['end_date'] = date('Y-m-d', strtotime('last day of +0 month'));
                $total_date['start_date'] = date('Y-m-d', strtotime($filters['start_date']));
                $total_date['end_date'] = date('Y-m-d', strtotime($filters['end_date']));
				
				$currencyData = $UM->fetchCurrency($payload->country);				
				if ($currencyData) {
					$cur_symbol = $currencyData['symbol'];
					$currency = $currencyData['code'];
				} else {
					$cur_symbol = "$";
					$currency = "USD";
				}

				$cur_symbol = html_entity_decode($cur_symbol, ENT_COMPAT, 'UTF-8');
				
				$user_id = null;
                if ($payload->role=="client") {
					$user_id = $payload->uuid;
				}
								
				$accumulatedTotal = $total_projected = $weekly_paid_total = $monthly_paid_total = $monthly_overdue_total = $overdue_total = 0;
				$upcoming_table = $paid_table = $overdue_table = '';


				// PAID RECEIVABLES
				$resultPaid = $UM->fetchPaidAR($user_id,$total_date);
				$resultPaidPartial = $UM->fetchPaidPartialAR($user_id,$total_date);
				$inv_paid_temp = [];
				$inv_paid_partial_temp = [];
				$date_paid_partial_temp = [];
				$paid_temp = [];
				if ($resultPaidPartial) {
					foreach ($resultPaidPartial as $key => $value) {
						if (isset($paid_temp[$value['payment_date']][$value['uuid']])) {
							$paid_temp[$value['payment_date']][$value['uuid']]['amount'] += $value['amount'];
						} else {
							$paid_temp[$value['payment_date']][$value['uuid']]['amount'] = $value['amount'];
							$paid_temp[$value['payment_date']][$value['uuid']]['invoice_no'] = $value['invoice_no'];
						}

						if (isset($inv_paid_temp[$value['uuid']])) {
							$inv_paid_temp[$value['uuid']] += $value['amount'];
						} else {
							$inv_paid_temp[$value['uuid']] = $value['amount'];
						}

						if (isset($inv_paid_partial_temp[$value['uuid']][$value['payment_date']])) {
							$inv_paid_partial_temp[$value['uuid']][$value['payment_date']]['amount'] += $value['amount'];
						} else {
							$inv_paid_partial_temp[$value['uuid']][$value['payment_date']]['amount'] = $value['amount'];
						}

						if (isset($date_paid_partial_temp[$value['payment_date']][$value['uuid']])) {
							$date_paid_partial_temp[$value['payment_date']][$value['uuid']]['amount'] += $value['amount'];
						} else {
							$date_paid_partial_temp[$value['payment_date']][$value['uuid']]['amount'] = $value['amount'];
							$date_paid_partial_temp[$value['payment_date']][$value['uuid']]['invoice_no'] = $value['invoice_no'];
						}
					}
				}
				if ($resultPaid) {
					foreach ($resultPaid as $key => $value) {
						if (isset($paid_temp[$value['payment_date']][$value['uuid']])) {
							$paid_temp[$value['payment_date']][$value['uuid']]['amount'] += $value['amount'];
						} else {
							$paid_temp[$value['payment_date']][$value['uuid']]['amount'] = $value['amount'];
							$paid_temp[$value['payment_date']][$value['uuid']]['invoice_no'] = $value['invoice_no'];
						}

						if (isset($inv_paid_temp[$value['uuid']])) {
							$inv_paid_temp[$value['uuid']] += $value['amount'];
						} else {
							$inv_paid_temp[$value['uuid']] = $value['amount'];
						}
					}
				}
				if (count($paid_temp)>0) {
					$startdayweek = date('Y-m-d', strtotime('monday this week'));
					foreach ($paid_temp as $key_date => $value_arr) {						
						foreach ($value_arr as $inv_id => $inv_data) {
							$monthly_paid_total += $inv_data['amount'];
							if (date("Y-m-d",strtotime($key_date)) >= date("Y-m-d",strtotime($startdayweek))) {
								$paid_table .= '<tr><td><a href="/invoice/'.$inv_id.'" class="letter-icon-title" target="_blank">#'.$inv_data['invoice_no'].'</a></td><td><div><span class="text-size-small">'.date(DATEFORMAT,strtotime($key_date)).'</span></div></td><td>'.number_format($inv_data['amount'],2,DECIMALS,THOUSANDS).'<br><span class="text-size-small text-muted">'.$currency.'</span></td></tr>';
								$weekly_paid_total += $inv_data['amount'];
							}
						}
					}
				} else {
					$paid_table .= '<tr><td colspan="3">'.__("No recent payments found",true).'</td></tr>';
				}
				// END PAID RECEIVABLES


				// OVERDUE RECEIVABLES
				$resultOverdue = $UM->fetchOverdueAR($user_id,$total_date);
				if (count($resultOverdue)>0) {
					$startdaymonth = date('Y-m-d', strtotime('first day of +0 month'));
					foreach ($resultOverdue as $key => $value) {
						$earlier = new DateTime(date("Y-m-d"));
						$later = new DateTime(date("Y-m-d",strtotime($value['due_date'])));
						$abs_diff = $later->diff($earlier)->format("%a");
						if ($abs_diff==1) {
							$abs_diff = $abs_diff." ".__("day",true);
						} else {
							$abs_diff = $abs_diff." ".__("days",true);
						}

						if (isset($inv_paid_temp[$value['uuid']])) {
							$value['amount'] -= $inv_paid_temp[$value['uuid']];
						}

						$overdue_total += $value['amount'];
						if ($value['due_date'] >= date("Y-m-d",strtotime($startdaymonth))) {
							$monthly_overdue_total += $value['amount'];
						}
						$overdue_table .= '<tr><td><a href="/invoice/'.$value['uuid'].'" class="letter-icon-title" target="_blank">#'.$value['invoice_no'].'</a></td><td><div><b>'.$abs_diff.'</b><br><span class="text-size-small text-muted">'.date(DATEFORMAT,strtotime($value["due_date"])).'</span></div></td><td>'.number_format($value['amount'],2,DECIMALS,THOUSANDS).'<br><span class="text-size-small text-muted">'.$currency.'</span></td></tr>';
					}
				} else {
					$overdue_table .= '<tr><td colspan="3">'.__("No overdues found",true).'</td></tr>';
				}
				// END OVERDUE RECEIVABLES UNPAID

				// DEFAULT
				if ($monthly_overdue_total==0) {
					$return['total_default'] = "0%";
				} else {
					$return['total_default'] = number_format(($monthly_overdue_total / ($monthly_overdue_total + $monthly_paid_total)*100),2)."%";	
				}
				// END DEFAULT


				// PROJECTED TOTAL 
				$resultProjectedTotal = $UM->fetchUpcomingAR($user_id,$total_date);				
				if (count($resultProjectedTotal)>0) {
					foreach ($resultProjectedTotal as $key => $value) {
						if (isset($inv_paid_temp[$value['uuid']])) {
							$value['amount'] -= $inv_paid_temp[$value['uuid']];
						}
						$total_projected += $value['amount'];
					}
				}
				// END PROJECTED TOTAL				



				// UPCOMING RECEIVABLES THIS WEEK				
				$resultUpcoming = $UM->fetchUpcomingAR($user_id,null);
				if (count($resultUpcoming)>0) {
					foreach ($resultUpcoming as $key => $value) {
						$earlier = new DateTime(date("Y-m-d"));
						$later = new DateTime(date("Y-m-d",strtotime($value['due_date'])));
						$abs_diff = $later->diff($earlier)->format("%a");
						if ($abs_diff==1) {
							$abs_diff = $abs_diff." ".__("day",true);
						} else {
							$abs_diff = $abs_diff." ".__("days",true);
						}

						$lifecycles = $UM->fetchLifecycles($value['uuid']);						
						$sent = 0;
						foreach ($lifecycles as $key2 => $value2) {
							if ($value2['status']=="completed") {
								$sent++;
							}
						}
						$client_businessname = "";
						if (isset($value['client_details']['business_name'])) {
							$client_businessname = $value['client_details']['business_name'];
						}
						$client_email = "";
						if (isset($value['client_details']['email'])) {
							$client_email = $value['client_details']['email'];
						}

						if (isset($inv_paid_temp[$value['uuid']])) {
							$value['amount'] -= $inv_paid_temp[$value['uuid']];
						}

						$upcoming_table .= '<tr><td><a href="/invoice/'.$value['uuid'].'" class="letter-icon-title" target="_blank">#'.$value['invoice_no'].'</a></td><td><div><b>'.$abs_diff.'</b><br><span class="text-size-small text-muted">'.date(DATEFORMAT,strtotime($value["due_date"])).'</span></div></td><td><h6 class="no-margin"><b>'.$client_businessname.'</b><small class="display-block text-muted">'.str_replace(',','<br>',$client_email).'</small></h6></td><td>'.$sent.'</td><td>'.number_format($value['amount'],2,DECIMALS,THOUSANDS).'<br><span class="text-size-small text-muted">'.$currency.'</span></td></tr>';
					}
				} else {
					$upcoming_table .= '<tr><td colspan="5">'.__("No receivables found",true).'</td></tr>';
				}
				// END UPCOMING RECEIVABLE	


				// GRAPH DATA
				switch($filters['filter_view']){
					case "daily":
						$begin = new DateTime( date("Y-m-d",strtotime($filters['start_date'])) );
						$end = new DateTime( date("Y-m-d",strtotime($filters['end_date'])) );
						$interval = DateInterval::createFromDateString('1 day');
						$period = new DatePeriod($begin, $interval, $end);

						foreach($period as $dt) {
							$dt = (array)$dt;
							$col = [];
						    $col['start_date'] = date("Y-m-d",strtotime($dt['date']));
						    $col['end_date'] = date("Y-m-d",strtotime($dt['date']));
						    $duration[] = $col;
						}
						$col = [];
						$col['start_date'] = $filters['end_date'];
						$col['end_date'] = $filters['end_date'];
						$duration[] = $col;
						foreach ($duration as $key_index => $date_period) {
							$array_index = date(DATEFORMAT2,strtotime($date_period['start_date']));
							$paidData[$array_index] = 0;
							$unpaidData[$array_index] = 0;
							$accumulatedData[$array_index] = $accumulatedTotal;

							if ($payload->role=="admin") {
								$paid = $UM->fetchCashflowGraph($user_id,$date_period,"Paid");
								$unpaid = $UM->fetchCashflowGraph($user_id,$date_period,"Unpaid");
								$partialpayment = $UM->fetchPaidPartialAR($user_id,$date_period);
							} else {
								$paid = $UM->fetchCashflowGraph($user_id,$date_period,"Paid");
								$unpaid = $UM->fetchCashflowGraph($user_id,$date_period,"Unpaid");
								$partialpayment = $UM->fetchPaidPartialAR($user_id,$date_period);
							}

							foreach ($paid as $ar) {
								$paidData[$array_index] += $ar['amount'];
							}						

							foreach ($partialpayment as $pp) {
								if (isset($paidData[date(DATEFORMAT2,strtotime($pp['payment_date']))])) {
									$paidData[date(DATEFORMAT2,strtotime($pp['payment_date']))] += $pp['amount'];
								} else {
									$paidData[date(DATEFORMAT2,strtotime($pp['payment_date']))] = $pp['amount'];
								}
							}

							foreach ($unpaid as $ar) {
								if (isset($unpaidData[$array_index])) {
									$unpaidData[$array_index] += $ar['amount'];
								} else {
									$unpaidData[$array_index] = $ar['amount'];
								}
								
								$accumulatedData[$array_index] += $ar['amount'];
								$accumulatedTotal += $ar['amount'];
								if (isset($inv_paid_partial_temp[$ar['uuid']])) {
									foreach ($inv_paid_partial_temp[$ar['uuid']] as $key2 => $value2) {
										if ($key2<=$date_period['start_date']) {
											$unpaidData[$array_index] -= $value2['amount'];
											$accumulatedData[$array_index] -= $value2['amount'];
											$accumulatedTotal -= $value2['amount'];
										}
									}
								}								
							}
						}

					break;
					case "weekly":
						$begin = new DateTime( date("Y-m-d",strtotime($filters['start_date'])) );
						$end = new DateTime( date("Y-m-d",strtotime($filters['end_date'])) );
						$interval = DateInterval::createFromDateString('1 week');
						$period = new DatePeriod($begin, $interval, $end);

						foreach($period as $dt) {
							$dt = (array)$dt;
							$col = [];
						    $col['start_date'] = date("Y-m-d",strtotime('last Sunday', strtotime($dt['date'])));
						    $col['end_date'] = date("Y-m-d",strtotime('Saturday this week', strtotime($dt['date'])));
						    $duration[] = $col;
						}

						$duration[0]['start_date'] = $filters['start_date'];
						$duration[count($duration)-1]['end_date'] = $filters['end_date'];
						foreach ($duration as $key_index => $date_period) {							
							$array_index = date(DATEFORMAT2,strtotime($date_period['end_date']));
							$paidData[$array_index] = 0;
							$unpaidData[$array_index] = 0;
							$accumulatedData[$array_index] = $accumulatedTotal;

							if ($payload->role=="admin") {
								$paid = $UM->fetchCashflowGraph($user_id,$date_period,"Paid");
								$unpaid = $UM->fetchCashflowGraph($user_id,$date_period,"Unpaid");
								$partialpayment = $UM->fetchPaidPartialAR($user_id,$date_period);
							} else {
								$paid = $UM->fetchCashflowGraph($user_id,$date_period,"Paid");
								$unpaid = $UM->fetchCashflowGraph($user_id,$date_period,"Unpaid");
								$partialpayment = $UM->fetchPaidPartialAR($user_id,$date_period);
							}

							foreach ($paid as $ar) {
								$paidData[$array_index] += $ar['amount'];
							}

							foreach ($partialpayment as $pp) {
								if ($pp['payment_date'] >= $date_period['start_date'] && $pp['payment_date']<=$date_period['end_date']) {
									if (isset($paidData[$array_index])) {
										$paidData[$array_index] += $pp['amount'];
									} else {
										$paidData[$array_index] = $pp['amount'];
									}
								}
							}

							foreach ($unpaid as $ar) {
								$unpaidData[$array_index] += $ar['amount'];
								$accumulatedData[$array_index] += $ar['amount'];
								$accumulatedTotal += $ar['amount'];

								if (isset($inv_paid_partial_temp[$ar['uuid']])) {
									foreach ($inv_paid_partial_temp[$ar['uuid']] as $key2 => $value2) {
										if ($key2<=$date_period['start_date']) {
											$unpaidData[$array_index] -= $value2['amount'];
											$accumulatedData[$array_index] -= $value2['amount'];
											$accumulatedTotal -= $value2['amount'];
										}
									}
								}
							}							
						}
					break;
					case "monthly":
						$begin = new DateTime( date("Y-m-d",strtotime($filters['start_date'])) );
						$end = new DateTime( date("Y-m-d",strtotime($filters['end_date'])) );
						$interval = DateInterval::createFromDateString('1 month');
						$period = new DatePeriod($begin, $interval, $end);

						foreach($period as $dt) {
							$dt = (array)$dt;
							$col = [];
						    $col['start_date'] = date("Y-m-d",strtotime('first day of +0 month', strtotime($dt['date'])));
						    $col['end_date'] = date("Y-m-d",strtotime('last day of +0 month', strtotime($dt['date'])));
						    $duration[] = $col;
						}

						$duration[0]['start_date'] = $filters['start_date'];
						$duration[count($duration)-1]['end_date'] = $filters['end_date'];

						foreach ($duration as $key_index => $date_period) {														
							$array_index = date("M",strtotime($date_period['start_date']));
							$paidData[$array_index] = 0;
							$unpaidData[$array_index] = 0;
							$accumulatedData[$array_index] = $accumulatedTotal;

							if ($payload->role=="admin") {
								$paid = $UM->fetchCashflowGraph($user_id,$date_period,"Paid");
								$unpaid = $UM->fetchCashflowGraph($user_id,$date_period,"Unpaid");
								$partialpayment = $UM->fetchPaidPartialAR($user_id,$date_period);
							} else {
								$paid = $UM->fetchCashflowGraph($user_id,$date_period,"Paid");
								$unpaid = $UM->fetchCashflowGraph($user_id,$date_period,"Unpaid");
								$partialpayment = $UM->fetchPaidPartialAR($user_id,$date_period);
							}

							foreach ($paid as $ar) {
								$paidData[$array_index] += $ar['amount'];
							}

							foreach ($partialpayment as $pp) {
								if ($pp['payment_date'] >= $date_period['start_date'] && $pp['payment_date']<=$date_period['end_date']) {
									if (isset($paidData[$array_index])) {
										$paidData[$array_index] += $pp['amount'];
									} else {
										$paidData[$array_index] = $pp['amount'];
									}
								}
							}

							foreach ($unpaid as $ar) {
								$unpaidData[$array_index] += $ar['amount'];
								$accumulatedData[$array_index] += $ar['amount'];								
								$accumulatedTotal += $ar['amount'];

								if (isset($inv_paid_partial_temp[$ar['uuid']])) {
									foreach ($inv_paid_partial_temp[$ar['uuid']] as $key2 => $value2) {
										if ($key2>=$date_period['start_date'] && $key2<=$date_period['end_date']) {
											$unpaidData[$array_index] -= $value2['amount'];
											$accumulatedData[$array_index] -= $value2['amount'];
											$accumulatedTotal -= $value2['amount'];
										}
									}
								}
							}
							
						}
					break;
				}
				$graphData = [];										

				if ($filters['cashflow_type']=="actual") {
					$graphData[0] = [__('Receivables',true),__('Paid',true),__('Unpaid',true)];
					foreach ($paidData as $key => $value) {						
						$col = [];						
						if ($value!=0 || $unpaidData[$key]!=0) {
							$col[] = $key;
							$col[] = $value;
							$col[] = $unpaidData[$key];
							$graphData[] = $col;
						}
					}
					if (count($graphData)==1) {
						$col = [];
						$col[] = $key;
						$col[] = $value;
						$col[] = $unpaidData[$key];
						$graphData[] = $col;
					};
				} else if ($filters['cashflow_type']=="projected") {
					$graphData[0] = [__('Receivables',true),__('Projected',true)];
					foreach ($accumulatedData as $key => $value) {
						$col = [];
						if ($value!=0) {
							$col[] = $key;
							$col[] = $value;
							$graphData[] = $col;
						}
					}
					if (count($graphData)==1) {
						$col = [];
						$col[] = $key;
						$col[] = $value;
						$graphData[] = $col;
					};					
				} else if ($filters['cashflow_type']=="projected inflow") {
					$graphData[0] = [__('Receivables',true),__('Projected Inflow',true)];
					foreach ($unpaidData as $key => $value) {						
						$col = [];
						if ($value!=0) {
							$col[] = $key;
							$col[] = $value;
							$graphData[] = $col;
						}
					}

					if (count($graphData)==1) {
						$col = [];
						$col[] = $key;
						$col[] = $value;
						$graphData[] = $col;
					};
				}

				$return['status'] = true;
				$return['overdue_total_qty'] = count($resultOverdue);				
				$return['monthly_overdue_total'] = $cur_symbol.number_format($monthly_overdue_total,2,DECIMALS,THOUSANDS);
				$return['overdue_total'] = $cur_symbol.number_format($overdue_total,2,DECIMALS,THOUSANDS);
				$return['overdue_table'] = $overdue_table;

				$return['monthly_paid_total'] = $cur_symbol.number_format($monthly_paid_total,2,DECIMALS,THOUSANDS);
				$return['weekly_paid_total'] = $cur_symbol.number_format($weekly_paid_total,2,DECIMALS,THOUSANDS);
				$return['paid_table'] = $paid_table;
				$return['total_projected'] = $cur_symbol.number_format($total_projected,2,DECIMALS,THOUSANDS);
				$return['projected_date'] = date("M d")."-".date("d Y",strtotime($total_date['end_date']));

				$return['upcoming_table'] = $upcoming_table;
				$return['upcoming_qty'] = count($resultUpcoming);

				if (date("M Y",strtotime($total_date['start_date'])) == date("M Y",strtotime($total_date['end_date']))) {
					$return['date_range'] = date("M Y",strtotime($total_date['start_date']));
				} else {
					$return['date_range'] = date(DATEFORMAT,strtotime($total_date['start_date']))." - ".date(DATEFORMAT,strtotime($total_date['end_date']));
				}
				

				$return['graph_data'] = $graphData;
							
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
	
	function getTimeDiffInMinutes($time1, $time2) {
        $datetime1 = new DateTime($time1);
        $datetime2 = new DateTime($time2);
        $interval = $datetime1->diff($datetime2);
        $minutes = $interval->days * 24 * 60;
        $minutes += $interval->h * 60;
        $minutes += $interval->i;
        return $minutes;
    }

	// NOTIFICATIONS	
	function fetch_notifications() {
		if (isset($_POST['token'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();				
				$result = $UM->fetchUserNotifications($payload->uuid);
				$notif_body = '';
				if ($result) {
					$unseen = 0;
					foreach ($result as $key => $value) {
						$seen = 'notif-unseen';
						if($value['is_seen'] == 0) {
							$unseen++;
						} else {
							$seen = '';
						}			
					    $time1 = date("Y-m-d H:i:s",strtotime($value['created_on']));
					    $time2 = date("Y-m-d H:i:s");
					    $diffInMinutes = getTimeDiffInMinutes($time1, $time2);

					    if ($diffInMinutes>59) {
					    	if (floor($diffInMinutes/60)>23) {
					    		$elapsed = floor($diffInMinutes/(60*24))."d";
					    	} else {
					    		$elapsed = floor($diffInMinutes/60)."h";
					    	}
					    } else {
					    	$elapsed = $diffInMinutes."m";
					    }

					    if (strlen($value['message'])>45) {
					    	$message = substr($value['message'], 0, 45)."...";
					    } else {
					    	$message = $value['message'];
					    }
					    

				        $notif_body .= '<li class="media notif-msg-box mgt-0 '.$seen.'" data-id="'.$value['notification_id'].'">
				              <div class="media-body">
				                <div class="media-heading">';
				                	$notif_body .= '<span class="notif-headline" data-val="'.unclean($value['headline']).'">'.$value['headline'].'</span>';
				                  	$notif_body .= '<span class="media-annotation notif-time pull-right text-right" data-val="'.date("F d",strtotime($value['created_on']))." at ".date("H:i A",strtotime($value['created_on'])).'">'.$elapsed.'</span>
				                </div>';
				               $notif_body .= '<span class="notif-message" data-val="'.unclean($value['message']).'">'.$message.'</span>';
				              $notif_body .= '</div></li>';
					}

					$return['status'] = true;
					$return['data'] = $result;
					if ($unseen==0) {
						$unseen = "";
					}
					$return['total_unseen'] = $unseen;
					$return['notif_body'] = $notif_body;
				} else {
					$return['status'] = true;
					$notif_body .= '<li class="media mgt-0" style="padding:10px 15px;border-top:1px solid #eee;">
				              <div class="media-body">';
				               $notif_body .= '<span class="notif-message ellipsis">'.__("No notifications available",true).'</span>';
				              $notif_body .= '</div></li>';
				    $return['notif_body'] = $notif_body;
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

	function fetch_notificationdata() {
		if (isset($_POST['token']) && isset($_POST['id'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				$result = $UM->getGlobalbyId("notifications",$_POST['id']);
				$return['status'] = true;
				if (isset($result['message'])) {
					$result['message'] = unclean($result['message']);
					$result['headline'] = unclean($result['headline']);
					$result['notif_time'] = date("F d",strtotime($result['created_on']))." at ".date("H:i A",strtotime($result['created_on']));
				}
				$return['data'] = $result;
				$test = $UM->updateSeenNotification($_POST['id'],$payload->uuid,1);
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

	function mark_seen_notification() {
		if (isset($_POST['token']) && isset($_POST['id'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				$UM->updateSeenNotification($_POST['id'],$payload->uuid,1);
				$return['status'] = true;
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

	function export_receivables() {
		if (isset($_GET['token'])) {
			$jwt = $_GET['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				$filters = $_GET;
				$xlxs_content = [];
				$xlxs_content[] = [__("Invoice No.",true),__("Business Name",true),__("Amount",true),__("Status",true),__("Issued Date",true),__("Payment Term",true),__("Due Date",true),__("Days",true)." ".__("Aging",true),__("Payment Date",true),__("Notes",true),__("Added By",true)];

				if ($_SESSION['userdata']['industry']=="School" && $payload->role=="client_user") {
					$user_id = $UM->getClientIdbyClientUserId($payload->uuid);
				} else {
					$user_id = $payload->uuid;
				}
				
				if ($payload->role=="admin") {
					$result = $UM->fetchInvoices(null,$filters);
				} else {
					$result = $UM->fetchInvoices($user_id,$filters);
				}
				
				if (count($result)>0) {
					foreach ($result as $key => $value) {
						$col = [];
						$currency = $UM->fetchCurrencyByCode($value['currency']);
						$earlier = new DateTime(date("Y-m-d"));
						$later = new DateTime(date("Y-m-d",strtotime($value['due_date'])));
						$abs_diff = $later->diff($earlier)->format("%a");
						$lifecycles = $UM->fetchLifecycles($value['uuid']);
						$payment_date = "";
						if (isset($value['client_details']['business_name'])===false) {
							$value['client_details']['business_name'] = "";
						}
						switch($value['status']) {
							case 'Ongoing': $labelColor = 'primary';
							break;
							case 'On-hold': $labelColor = 'default';
							break;
							case 'Overdue': $labelColor = 'danger';
							break;
							case 'Paid': $labelColor = 'success';
							break;
							case 'Cancelled': $labelColor = 'default';
							break;
							case 'Bad Debt': $labelColor = 'danger';
							break;
						}
						$badge_text = __($value['status'],true);
						if ($value['status']=="Paid") {
							$payment_date = date(DATEFORMAT,strtotime($value['payment_date']));
						}
						if ($value['status']=="Paid") {
							$later = new DateTime(date("Y-m-d",strtotime($value['payment_date'])));
						} else {
							$later = new DateTime(date("Y-m-d"));	
						}
						$earlier = new DateTime(date("Y-m-d",strtotime($value['issued_date'])));
						$abs_diff = $later->diff($earlier)->format("%a");
						$added_by = '';
						if (isset($value['added_by'])) {
							$added_by = ucwords($value['added_by']['first_name'].' '.$value['added_by']['last_name']);
						}

						if ($value['notes']!="" && $value['notes']!=NULL) {
							$notes = $value['notes'];
						} else {
							$notes = "";
						}

						$payment_earlier = new DateTime(date("Y-m-d",strtotime($value['issued_date'])));
						$payment_later = new DateTime(date("Y-m-d",strtotime($value['due_date'])));
						$payment_term = $payment_later->diff($payment_earlier)->format("%a");
						
						$business_name = str_replace("&apos;","'",$value['client_details']['business_name']);
						$notes = str_replace("&apos;","'",$notes);
						$business_name = str_replace("&quot;",'"',$business_name);
						$notes = str_replace("&quot;",'"',$notes);

						$col[] = $value['invoice_no'];
						$col[] = $business_name;
	                	$col[] = number_format($value['amount'],2,DECIMALS,THOUSANDS);
	                	$col[] = $badge_text;	                	
	                	$col[] = $value['issued_date'];                	
	                	$col[] = $payment_term;
	                	$col[] = $value['due_date'];					
						$col[] = $abs_diff;
						$col[] = $payment_date;
						$col[] = $notes;
						$col[] = $added_by;
						$xlxs_content[] = $col;
					}
				}
				$xlsx = Shuchkin\SimpleXLSXGen::fromArray( $xlxs_content );
				$xlsx->downloadAs('export'.date(DATEFORMAT).'.xlsx');
			} catch (Exception $e) {
				header("Location: /receivables/?message=".$e->getMessage()) ;
			}
		} else {
			header("Location: /");
		}		
		exit;
	}


	//---- OPEN FINANCE ---//

	function fetch_OF_banks() {
		if (isset($_POST['token'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));				
				if ($payload) {
					$UM = new UserModel();
					$selecthtml = '<option value="">'.__("Select Bank",true).'</option>';
					if (LANG=="BR") {
						$auth = base64_encode(BELVO_USER.":".BELVO_PASS);
						$client = new \GuzzleHttp\Client();
						$response = $client->request('GET', BELVO_PATH.'/api/institutions/', [
						  'headers' => [
						    'accept' => 'application/json',
						    'authorization' => 'Basic '.$auth,
						  ],
						]);

						$result = json_decode($response->getBody());						
						if ($result) {
							foreach ($result->results as $key => $value) {
								if ($value->country_code=="BR") {
									$selecthtml .= '<option value="'.$value->id.'">'.$value->display_name.'</option>';
								}
							}							
						}
						$return['status'] = true;
						$return['selectdata'] = $selecthtml;
					} else {						
						$postdata = array("country"=>"PH","organization_display_name"=>"KoleK","app_redirect_uri"=>SITE_URL."/api/connect-brankas-callback","external_id"=>$payload->uuid);
						$ch = curl_init();

						curl_setopt($ch, CURLOPT_URL, 'https://statement.'.BRANKAS_PATH.'/v1/statement-init');
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));

						$headers = array();
						$headers[] = 'X-Api-Key: '.BRANKAS_API_KEY;
						$headers[] = 'Content-Type: application/x-www-form-urlencoded';
						curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

						$result = curl_exec($ch);
						if (curl_errno($ch)) {
						    $return['status'] = false;
						    $return['message'] = 'Error:' . curl_error($ch);
						} else {
							$result = json_decode($result);
							if (isset($result->redirect_uri) && $result->redirect_uri!=null) {
								$return['status'] = true;
								$return['redirect_url'] = $result->redirect_uri;
							} else {
								$return['status'] = false;
								$return['message'] = $result->message;
							}
							
						}
					}

				} else {
					$return['status'] = false;
					$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
				}				

			} catch (Exception $e) {				
				$return['status'] = false;
				$return['message'] = $e->getMessage();
			}									
		} else {			
			$return['status'] = false;
			$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
		}
		echo json_encode($return);
		exit;
	}


	function fetch_OF_bank_details() {
		if (isset($_POST['token'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				
				if ($payload) {
					$UM = new UserModel();
					if (LANG=="BR") {
						$auth = base64_encode(BELVO_USER.":".BELVO_PASS);
						$client = new \GuzzleHttp\Client();
						$response = $client->request('GET', BELVO_PATH.'/api/institutions/'.$_POST['id'], [
						  'headers' => [
						    'accept' => 'application/json',
						    'authorization' => 'Basic '.$auth,
						  ],
						]);

						$result = json_decode($response->getBody());
						if (isset($result->form_fields)) {
							$return['status'] = true;
							if ($result->icon_logo!="") {
								$addfields = '<div class="bank-logo-holder text-center"><img src="'.$result->icon_logo.'" class="bank-logo" style="height:75px;"></div>';
							} else {
								$addfields = '<div class="clear20"></div>';
							}
							foreach ($result->form_fields as $key => $value) {
								$required = 'required';
								if (isset($value->optional) && $value->optional==true) {
									$required = '';
								}
								$val = '';
								if (isset($value->value) && $value->value!="") {
									$val = ' value="'.$value->value.'"';
								}
								$label = '';
								if (isset($value->label) && $value->label!="") {
									$label = $value->label;
								}
								$space = '<div class="clear20"></div>';								
								if (isset($value->type)===false) {
									$type = "text";
								} else {
									if ($value->type=="hidden") {
										$space = '';
									}
									$type = $value->type;
								}
								if ($type=="select") {
									$addfields .= '<select class="form-control input-xlg" name="'.$value->name.'" '.$required.'>';
									foreach ($value->values as $key2 => $value2) {
										$addfields .= '<option value="'.$value2->code.'">'.$value2->label.'</option>';
									}
									$addfields .= '</select>'.$space;
								} else {
									$addfields .= '<input class="form-control input-xlg" type="'.$type.'" name="'.$value->name.'" placeholder="'.$label.'" '.$val.' '.$required.'>'.$space;	
								}
								
							}

							$return['addfields'] = $addfields;
							$return['fulldata'] = $result;
						} else {
							$return['status'] = false;
							$return['message'] = "Currently unavailable. Please select another bank or try again later.";
						}
						
					}
				} else {
					$return['status'] = false;
					$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
				}				
			} catch (Exception $e) {
				$return['status'] = false;
				$return['message'] = $e->getMessage();
			}									
		} else {
			$return['status'] = false;
			$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
		}
		echo json_encode($return);
		exit;
	}

	function connect_brankas_callback() {
		if (isset($_GET['statement_id'])) {
			$statement_id = $_GET['statement_id'];
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://statement.'.BRANKAS_PATH.'/v1/statements?statement_ids='.$statement_id);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			$headers = array();
			$headers[] = 'X-Api-Key: '.BRANKAS_API_KEY;
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$result = curl_exec($ch);
			if (curl_errno($ch)) {
			    echo 'Error:' . curl_error($ch);
			}

			$statement = json_decode($result)->statements[0];
			curl_close($ch);			
			
			$newData = [];
			if ($statement->status=="COMPLETED") {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'https://direct.'.BRANKAS_PATH.'/v1/banks?country=PH&bank_code='.$statement->bank_code);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
				$headers = array();
				$headers[] = 'X-Api-Key: '.BRANKAS_API_KEY;
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				$banks = curl_exec($ch);
				if (curl_errno($ch)) {
				    echo 'Error:' . curl_error($ch);
				}
				$banks = json_decode($banks)->banks[0];
				curl_close($ch);

				$UM = new UserModel();			
				$return['status'] = true;			
				$newData['user_id'] = $statement->external_id;
				$newData['bank_name'] = $banks->title;
				$newData['country'] = "philippines";
				$newData['of_supplier'] = "brankas";
				$newData['brankas_statement_id'] = $statement_id;
				if (isset($statement->account_link_id)) {
					$newData['brankas_account_link_id'] = $statement->account_link_id;
				}
				$newData['brankas_bank_code'] = $statement->bank_code;
				$newData['last_sync'] = date("Y-m-d H:i:s");
				foreach ($statement->account_statements as $key => $value) {
					$newData['uuid'] = hexdec(uniqid());
					$newData['account_type'] = ucwords(strtolower($value->account->type));
					$newData['account_name'] = ucwords(strtolower($value->account->holder_name));
					$newData['account_number'] = $value->account->account_number;
					
					//check if account number, bank and user_id already exist
					$check = $UM->checkOFBankAccountConnectionExist($newData);
					if ($check===false) {
						$res = $UM->addGlobal("bank_accounts",$newData);
					} else {
						unset($newData['uuid']);
						$newData['is_deleted'] = 0;
						$res = $UM->updateGlobal("bank_accounts",$check['uuid'],$newData);
					}			
				}
				$payload = [];
				$payload['statement_id'] = $statement_id;
				$jwt = JWT::encode($payload, KEY, 'HS256');
				header("Location: /receivables?callback=".$jwt);
			} else {
				$return['status'] = false;
				$return['message'] = __("Statement Incomplete/Cancelled.",true);
				header("Location: /bank-accounts/");
			}
		}
		exit;
	}

	function fetch_available_banks_reconciliation() {
		if (isset($_POST['token'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));								
				if ($payload) {
					$UM = new UserModel;
					if ($payload->role=="client_user") {
						$user_id = $UM->getClientIdbyClientUserId($payload->uuid);
					} else {
						$user_id = $payload->uuid;
					}
					$result = $UM->fetchBankAccounts($user_id,true);
					$html_list = '<table class="table"><thead>
						<tr><th></th><th>'.__("Bank Name",true).'</th><th>'.__("Account No",true).'</th><th>'.__("Account Type",true).'</th><th>'.__("Last Sync",true).'</th><th></th></thead><tbody>';					
					if ($result) {
						foreach ($result as $key => $value) {
							if ($value['of_supplier']!=null && $value['of_supplier']!="") {
								$html_list .= '<tr data-id="'.$value['uuid'].'"><td><i class="icon-checkbox-checked2 text-success pointer bank-select"></i></td>';
								$html_list .= '<td>'.$value['bank_name'].'</td>';
								$html_list .= '<td>'.$value['account_number'].'</td>';
								$html_list .= '<td>'.$value['account_type'].'</td>';
								if ($value['last_sync']==null || $value['last_sync']=="") {
									$html_list .= '<td><label class="label label-success">'.__("Up to date",true).'</label></td>';
								} else {
									$html_list .= '<td><button class="btn btn-primary btn-xs syncBtn" data-id="'.$value['uuid'].'">'.__("Sync Now",true).' <i class="icon-sync mgl-5 f-14"></i></button></td>';	
								}
								$html_list .= '<td><small class="text-muted">'.__("Last updated",true).' '.date(DATEFORMAT,strtotime($value['last_sync'])).'</small><input type="hidden" name="banks[]" value="'.$value['uuid'].'"/></td>';
								$html_list .= '</tr>';
							}							
						}						
					} else {
						$html_list .= '<tr><td colspan="7" class="text-center">'.__("No available banks connected",true).'<div class="clear10"></div><button class="btn btn-xs btn-primary mgl-5 connectBankBtn" data-toggle="modal" data-target="#connectBankOFAccountModal">'.__("Connect Bank",true).' <i class="icon-link mgl-5 f-14"></i></button></td></tr>';
					}
					$html_list .= '</tbody</html>';
					$return['status'] = true;
					$return['html_list'] = $html_list;
				} else {
					$return['status'] = false;
					$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
				}
			} catch (Exception $e) {
				$return['status'] = false;
				$return['message'] = $e->getMessage();
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
		}
		echo json_encode($return);
		exit;
	}

	function bank_sync() {
		if (isset($_POST['token'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));								
				if ($payload) {
					$UM = new UserModel;
					if ($payload->role=="client_user") {
						$user_id = $UM->getClientIdbyClientUserId($payload->uuid);
					} else {
						$user_id = $payload->uuid;
					}

					$bank_details = $UM->getGlobalbyId("bank_accounts",$_POST['id']);					

					if ($bank_details['brankas_bank_code']!="" && $bank_details['brankas_bank_code']!=NULL) {
						$postdata = array("country"=>"PH","organization_display_name"=>"KoleK","app_redirect_uri"=>SITE_URL."/api/connect-brankas-callback/","external_id"=>$user_id,"bank_codes"=>array($bank_details['brankas_bank_code']));
					} else {
						$postdata = array("country"=>"PH","organization_display_name"=>"KoleK","app_redirect_uri"=>SITE_URL."/api/connect-brankas-callback/","external_id"=>$user_id);	
					}
										
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, 'https://statement.'.BRANKAS_PATH.'/v1/statement-init');
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));

					$headers = array();
					$headers[] = 'X-Api-Key: '.BRANKAS_API_KEY;
					$headers[] = 'Content-Type: application/x-www-form-urlencoded';
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

					$result = curl_exec($ch);
					if (curl_errno($ch)) {
					    $return['status'] = false;
					    $return['message'] = 'Error:' . curl_error($ch);
					} else {
						$result = json_decode($result);
						if (isset($result->redirect_uri) && $result->redirect_uri!=null) {
							$return['status'] = true;
							$return['redirect_url'] = $result->redirect_uri;
						} else {
							$return['status'] = false;
							$return['message'] = $result->message;
						}						
					}
				} else {
					$return['status'] = false;
					$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
				}
			} catch (Exception $e) {
				$return['status'] = false;
				$return['message'] = $e->getMessage();
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
		}
		echo json_encode($return);
		exit;
	}

	function brankas_reconcile_banks() {
		if (isset($_POST['token'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));								
				if ($payload) {
					$UM = new UserModel;
					parse_str($_POST['data'],$postdata);

					$account_numbers = $statements = [];
					foreach ($postdata['banks'] as $key => $value) {
						$bank_details = $UM->getGlobalbyId("bank_accounts",$value);
						$statements[$bank_details['brankas_statement_id']] = $bank_details['brankas_account_link_id'];
						$account_numbers[] = $bank_details['account_number'];
					}
					$html_list = '<table class="table"><thead>
						<tr><th></th><th>'.__("Inv #",true).'</th><th>'.__("Payer",true).'</th><th>'.__("Amount",true).'</th><th>'.__("Date Paid",true).'</th><th></th></thead><tbody>';

					foreach ($statements as $key => $value) {
						$statement_id = $key;
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, 'https://statement.'.BRANKAS_PATH.'/v1/statements?statement_ids='.$statement_id);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
						$headers = array();
						$headers[] = 'X-Api-Key: '.BRANKAS_API_KEY;
						curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
						$result = curl_exec($ch);
						if (curl_errno($ch)) {
						    echo 'Error:' . curl_error($ch);
						}
						$statement = json_decode($result)->statements[0];
						curl_close($ch);			
						$matched = [];						

						if (isset($statement->account_statements)) {
							foreach ($statement->account_statements as $accounts) {
								if (isset($accounts->transactions)) {
									if (in_array($accounts->account->account_number,$account_numbers)) {
										foreach ($accounts->transactions as $transaction) {
											if ($transaction->type=="CREDIT") {							
												$currency = $UM->fetchCurrencyByCode($transaction->amount->cur);
												$currency = "₱";
												$newData = [];
												$newData['amount'] = $transaction->amount->decimal->num;
												$newData['client'] = str_replace("Payment from ","",$transaction->descriptor);
												$newData['payment_date'] = date("Y-m-d",strtotime($transaction->date));
												$result = $UM->matchStatementReceivables($newData);
												if ($result) {
													$matched[$result['uuid']] = $newData;
													$html_list .= '<tr data-id="'.$result['uuid'].'"><td><i class="icon-checkbox-checked2 text-success pointer inv-paid"></i></td>';
													$html_list .= '<td><a href="/invoice/'.$result['uuid'].'">'.$result['invoice_no'].'</td>';
													$html_list .= '<td>'.ucwords($newData['client']).'</td>';
													$html_list .= '<td>'.$currency.number_format($newData['amount'],2,DECIMALS,THOUSANDS).'</td>';											
													$html_list .= '<td>'.date(DATEFORMAT,strtotime($newData['payment_date'])).'</td>';
													$html_list .= '<td><input type="hidden" name="reconcile[]" value="'.$result['uuid'].'"/></td>';
													$html_list .= '</tr>';
												}
											}
										}
									}
								}				
							}							
						}
					}
					$html_list .= '</tbody></table>';
					if (count($matched)==0) {
						$return['status'] = true;
						$return['message'] = __("No receivables for reconciliation.",true);
						$return['match_total'] = 0;
					} else {
						$return['status'] = true;
						$return['matched'] = JWT::encode($matched, KEY, 'HS256');
						$return['match_total'] = count($matched);
						$return['html_list'] = $html_list;	
					}					
				} else {
					$return['status'] = false;
					$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
				}
			} catch (Exception $e) {
				$return['status'] = false;
				$return['message'] = $e->getMessage();
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
		}
		echo json_encode($return);
		exit;
	}

	function brankas_reconcile_callback() {
		if (isset($_GET['statement_id'])) {
			$payload = [];
			$payload['statement_id'] = $_GET['statement_id'];
			$jwt = JWT::encode($payload, KEY, 'HS256');
			header("Location: /receivables?callback=".$jwt);
			exit;
		} else if (isset($_POST['statement_id'])) {
			$jwt = $_POST['token'];
			$stmt = $_POST['statement_id'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				if ($payload) {					
					try {
						$callback = JWT::decode($stmt, new Key(KEY, 'HS256'));
						$statement_id = $callback->statement_id;
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, 'https://statement.'.BRANKAS_PATH.'/v1/statements?statement_ids='.$statement_id);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
						$headers = array();
						$headers[] = 'X-Api-Key: '.BRANKAS_API_KEY;
						curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
						$result = curl_exec($ch);
						if (curl_errno($ch)) {
						    echo 'Error:' . curl_error($ch);
						}

						$statement = json_decode($result)->statements[0];
						
						curl_close($ch);			
						$UM = new UserModel;
						$matched = [];
						$html_list = '<table class="table"><thead>
						<tr><th></th><th>'.__("Inv #",true).'</th><th>'.__("Payer",true).'</th><th>'.__("Amount",true).'</th><th>'.__("Date Paid",true).'</th><th></th></thead><tbody>';

						if (isset($statement->account_statements)) {
							foreach ($statement->account_statements as $accounts) {
								if (isset($accounts->transactions)) {
									foreach ($accounts->transactions as $transaction) {
										if ($transaction->type=="CREDIT") {							
											$currency = $UM->fetchCurrencyByCode($transaction->amount->cur);
											$newData = [];
											$newData['amount'] = $transaction->amount->decimal->num;
											$newData['client'] = str_replace("Payment from ","",$transaction->descriptor);
											$newData['payment_date'] = date("Y-m-d",strtotime($transaction->date));
											$result = $UM->matchStatementReceivables($newData);
											if ($result) {
												$matched[$result['uuid']] = $newData;
												$html_list .= '<tr data-id="'.$result['uuid'].'"><td><i class="icon-checkbox-checked2 text-success pointer inv-paid"></i></td>';
												$html_list .= '<td><a href="/invoice/'.$result['uuid'].'">'.$result['invoice_no'].'</td>';
												$html_list .= '<td>'.ucwords($newData['client']).'</td>';
												$html_list .= '<td>'.$currency.number_format($newData['amount'],2,DECIMALS,THOUSANDS).'</td>';											
												$html_list .= '<td>'.date(DATEFORMAT,strtotime($newData['payment_date'])).'</td>';
												$html_list .= '<td><input type="hidden" name="reconcile[]" value="'.$result['uuid'].'"/></td>';
												$html_list .= '</tr>';
											}
										}
									}
								}				
							}
							$html_list .= '</tbody></table>';
							if (count($matched)==0) {
								$return['status'] = true;
								$return['message'] = __("No receivables for reconciliation.",true);
								$return['match_total'] = 0;
							} else {
								$return['status'] = true;
								$return['matched'] = JWT::encode($matched, KEY, 'HS256');
								$return['match_total'] = count($matched);
								$return['html_list'] = $html_list;	
							}
						} else {
							$return['status'] = true;
							$return['message'] = __("No receivables for reconciliation.",true);
						}
					} catch (Exception $e) {
						$return['status'] = false;
						$return['message'] = __("Invalid Callback. Please re-log in or contact support.",true);	
					}
				} else {
					$return['status'] = false;
					$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
				}				
			} catch (Exception $e) {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
			}
			echo json_encode($return);	
		} else {
			header("Location: /");	
		}		
		exit;
	}	

	function brankas_pay() {
		if (isset($_POST['token'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				if ($payload) {
					$UM = new UserModel;
					parse_str($_POST['data'],$postdata);					
					
					$invoice_details = $UM->getGlobalbyId("account_receivables",$postdata['invoice_id']);
					$business_details = $UM->getGlobalbyId("business_accounts",$invoice_details['business_id']);
					$logo = "https://app.wekolek.com/assets/images/logo-landscape.png";
					if ($business_details['business_logo']!="") {
						$logo = $business_details['business_logo'];
					}
					$postdata = array(
						"reference_id"=>$invoice_details['uuid'],
						"amount"=>array(
							"cur"=> $invoice_details['currency'],
							"num"=> strval($invoice_details['amount']*100),
						),
						"destination_account_id"=>"0068ae74-482e-11ed-b0bd-42010a880002",
						"memo"=>"Payment from ".$invoice_details['client_details']['business_name'],
						"from"=> array(
							"type"=> "BANK",
							"country"=> "PH"
						),
						"payment_channel"=> "_",
						"client"=> array(
						    "display_name"=> $business_details['business_name'],
						    "logo_url"=> $logo,
						    "return_url"=> SITE_URL."/api/brankas_pay_callback/".$invoice_details['uuid'],
						    "fail_url"=> SITE_URL."/api/brankas_pay_callback/".$invoice_details['uuid'],
						    "deep_link"=> true,
						    "short_redirect_uri"=> true
						),
					);						
										
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, 'https://direct.'.BRANKAS_PATH.'/v1/checkout');
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));

					$headers = array();
					$headers[] = 'X-Api-Key: '.BRANKAS_API_KEY;
					$headers[] = 'Content-Type: application/x-www-form-urlencoded';
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

					$result = curl_exec($ch);
					if (curl_errno($ch)) {
					    $return['status'] = false;
					    $return['message'] = 'Error:' . curl_error($ch);
					} else {$result = json_decode($result);
						if (isset($result->redirect_uri)) {
							$return['redirect_url'] = $result->redirect_uri;
							$return['status'] = true;
						} else {
							$return['status'] = false;
							$return['message'] = $result->message;
						}
					}				
				} else {
					$return['status'] = false;
					$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
				}
			} catch (Exception $e) {
				$return['status'] = false;
				$return['message'] = $e->getMessage();
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
		}
		echo json_encode($return);
		exit;
	}

	function brankas_pay_callback() {
		if (isset($_GET['transaction_id']) && getUriSegment(3)!="" && $_GET['status']==2) {
			$UM = new UserModel;
			$invoice = $UM->getGlobalbyId("account_receivables",getUriSegment(3));
			$newData = [];
			$newData['status'] = "Paid";
			$newData['payment_date'] = date("Y-m-d");
			$result = $UM->updateGlobal("account_receivables",$invoice['uuid'],$newData);			
		}
		header("Location: ".SITE_URL."/pay/".getUriSegment(3));
		exit;
	}

	function destination_account() {
		echo "OPC";
		exit;
	}
	//---- OPEN FINANCE ---//


	// PIX GENERATOR //
	function generate_pix($id=null) {
		if ($id==null) {
			header("Location: /");
			exit;
		}
		$pix_arr = [];
		$pix_arr[0] = "000201";

		$id = "1746388360290846";
		$UM = new UserModel;
		$invoice = $UM->getGlobalbyId("account_receivables",$id);
		$business = $UM->getGlobalbyId("business_accounts",$invoice['business_id']);
		$bank_account = $UM->getGlobalbyId("bank_accounts",$invoice['bank_account_id']);

		function crcChecksum($str) {
	    	// The PHP version of the JS str.charCodeAt(i)
		    function charCodeAt($str, $i) {
		        return ord(substr($str, $i, 1));
		    }

		    $crc = 0xFFFF;
		    $strlen = strlen($str);
		    for($c = 0; $c < $strlen; $c++) {
		        $crc ^= charCodeAt($str, $c) << 8;
		        for($i = 0; $i < 8; $i++) {
		            if($crc & 0x8000) {
		                $crc = ($crc << 1) ^ 0x1021;
		            } else {
		                $crc = $crc << 1;
		            }
		        }
		    }
		    $hex = $crc & 0xFFFF;
		    $hex = dechex($hex);
		    $hex = strtoupper($hex);

		    return $hex;
		}
		$pixkey = $bank_account['pix_number'];
		$amount = number_format($invoice['amount'],2,".","");
		$merchant_name = $business['business_name'];
		$city = $bank_account['city'];
		$reference_id = $invoice['uuid'];		

		if ($pixkey=="") {
			$return['status'] = false;
			$return['message'] = __("Missing Pix Key",true);
			exit;
		}
		$val26a = "0014BR.GOV.BCB.PIX";
		$val26b = "01".sprintf('%02d', strlen($pixkey)).$pixkey;
		$val26 = $val26a.$val26b;
		$pix_arr[1] = "26".sprintf('%02d', strlen($val26)).$val26;
		$pix_arr[2] = "52040000";
		$pix_arr[3] = "5303986";
		$pix_arr[4] = "54".sprintf('%02d', strlen($amount)).$amount;
		$pix_arr[5] = "5802BR";
		$pix_arr[6] = "59".sprintf('%02d', strlen($merchant_name)).$merchant_name;
		if ($city=="") {
			$city="Sao Paulo";
		}
		$pix_arr[7] = "60".sprintf('%02d', strlen($city)).$city;
		$val62 = "05".sprintf('%02d', strlen($reference_id)).$reference_id;
		$pix_arr[8] = "62".sprintf('%02d', strlen($val62)).$val62;
		$pix_arr[9] = "6304";
		$pix_str = implode("",$pix_arr);
		$val63 = $result = crcChecksum($pix_str);
		$pix_str .= $val63;
		
		$return['status'] = true;
		$return['pix_text'] = $pix_str;
		$return['pix_qr'] = '<img src="'.(new QRCode)->render($pix_str).'" class="qr-code" alt="PIX" style="width:150px;"/>';

		exit;
	}
	//



	//DEBT COLLECTION 
	function fetch_debt_collection() {
		if (isset($_POST['token'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();
				if ($payload->role=="client_user") {
					$user_id = $UM->getClientIdbyClientUserId($payload->uuid);
				} else {
					$user_id = $payload->uuid;
				}

				if ($payload->role=="admin") {
					$collection = $UM->fetchDebtCollection();
				} else {					
					$collection = $UM->fetchDebtCollection($user_id);
				}						
						
				if (count($collection)>0) {
					$collection_html = '';
					$collection_datatable = [];
					foreach ($collection as $key => $value) {
						$col = [];
						$total_invoice_amount = 0;
						$total_updated_amount = 0;
						$currency = "USD";
						foreach (json_decode($value['invoice_items']) as $inv_id) {
							$total = 0;
							$invoice = $UM->getGlobalbyId('account_receivables',$inv_id);
							$partialPayments = $UM->fetchInvoicePayments($inv_id);
							$total_partial = 0;
							if (count($partialPayments)>0) {
								foreach ($partialPayments as $pp) {
									$total_partial += $pp['amount'];
								}
							}							

							$discounts = ($invoice['amount']*($invoice['discount']/100));
			        		$late_fee = ($invoice['amount']*($invoice['late_fee']/100));
			        		$earlier = new DateTime(date("Y-m-d"));
					        $later = new DateTime(date("Y-m-d",strtotime($invoice['due_date'])));
					        $day_overdue = $later->diff($earlier)->format("%a");
					        $interest = (($invoice['interest']/100)/30) * $day_overdue * $invoice['amount'];
					        $total = $invoice['amount'] - $total_partial - $discounts + $late_fee + $interest;					        
							
							$total_updated_amount += $total;
							$total_invoice_amount += $invoice['amount'];
							$currency = $invoice['currency'];
						}

						if ($currency=="BRL") {
							$decimals = ",";
							$thousands = ".";
						} else {
							$decimals = ".";
							$thousands = ",";
						}
						$total_partial_collection = 0;
						$partial_payments_collection = $UM->fetchInvoicePayments($value['uuid']);
						if (count($partial_payments_collection)>0) {
							foreach ($partial_payments_collection as $pp) {
								$total_partial_collection += $pp['amount'];
							}
						}
						$total_updated_amount -= $total_partial_collection;						
						$collection_html .= '<tr data-arid="'.$value['uuid'].'">
							<td><a href="/invoice/'.$value['uuid'].'" class="bold" target="_blank">1'.sprintf('%06d', $value['id']).'<i class="icon-new-tab mgl-5" style="font-size:13px;"></i></a></td>
							<td><div class="ellipsis-100"><b>';
							$collection_html .= $value['client_details']['business_name'].'</b></div></td>
							<td class="text-center">'.number_format($total_invoice_amount,2,$decimals,$thousands).'</td>
							<td class="text-center">'.number_format($total_updated_amount,2,$decimals,$thousands).'</td>
							<td><label class="label label-primary">'.__($value['status'],true).'</label></td>
						</tr>';
						$col[] = '<a href="/invoice/'.$value['uuid'].'" class="bold" target="_blank">1'.sprintf('%06d', $value['id']).'<i class="icon-new-tab mgl-5" style="font-size:13px;"></i></a>';
						$col[] = '<div class="ellipsis-100"><b>'.$value['client_details']['business_name'].'</b></div>';
						// $col[] = number_format($total_invoice_amount,2,$decimals,$thousands);
						$col[] = number_format($total_updated_amount,2,$decimals,$thousands);
						$col[] = '<label class="label label-primary">'.__($value['status'],true).'</label>';
						$collection_datatable[] = $col;
					}					
					$return['status'] = true;
					$return['collection_html'] = $collection_html;
					$return['collection_datatable'] = $collection_datatable;
					$return['collection_data'] = $collection;					
				} else {
					$return['status'] = true;
					$return['collection_html'] = '<tr><td colspan="5">'.__("No receivables found",true).'</td></tr>';
				}				

				$filters['status_filter'] = "Overdue";
				if ($payload->role=="admin") {
					$clientList = $UM->fetchInvoices(null,$filters);
				} else {
					$clientList = $UM->fetchInvoices($user_id,$filters);
				}
										
				if (count($clientList)>0) {
					$client_html = "";
					$client_datatable = [];					
					$clients = [];
					$currency = "USD";
					foreach ($clientList as $key => $value) {
						$invoice = $value;
						$partialPayments = $UM->fetchInvoicePayments($invoice['uuid']);
						$total_invoice_amount = $total_updated_amount = $total_partial = 0;
						if (count($partialPayments)>0) {
							foreach ($partialPayments as $pp) {
								$total_partial += $pp['amount'];
							}
						}							

						$discounts = ($invoice['amount']*($invoice['discount']/100));
		        		$late_fee = ($invoice['amount']*($invoice['late_fee']/100));
		        		$earlier = new DateTime(date("Y-m-d"));
				        $later = new DateTime(date("Y-m-d",strtotime($invoice['due_date'])));
				        $day_overdue = $later->diff($earlier)->format("%a");
				        $interest = (($invoice['interest']/100)/30) * $day_overdue * $invoice['amount'];
				        $total = $invoice['amount'] - $total_partial - $discounts + $late_fee + $interest;
						
						$total_updated_amount += $total;
						$total_invoice_amount += $invoice['amount'];
						$currency = $invoice['currency'];



						if (isset($clients[$value['client_id']])) {
							$clients[$value['client_id']]['receivables'] += 1;
							$clients[$value['client_id']]['total_amount'] += $total_invoice_amount;
							$clients[$value['client_id']]['total_updated_amount'] += $total_updated_amount;
						} else {
							$clients[$value['client_id']]['business_name'] = $value['client_details']['business_name'];
							$clients[$value['client_id']]['receivables'] = 1;
							$clients[$value['client_id']]['total_updated_amount'] = $total_updated_amount;
							$clients[$value['client_id']]['total_amount'] = $total_invoice_amount;
						}
						$currency = $value['currency'];
					}

					if ($value['currency']=="BRL") {
						$decimals = ",";
						$thousands = ".";
					} else {
						$decimals = ".";
						$thousands = ",";
					}
					
					foreach ($clients as $key => $value) {
						$client_html .= "<tr><td><div class='ellipsis-250'>";
						$client_html .= $value['business_name']."</div></td>";
						$client_html .= "<td>".$value['receivables']."</td>";
						$client_html .= "<td>".number_format($value['total_updated_amount'],2,$decimals,$thousands)."</td>";
						$client_html .= '<td><button class="btn btn-primary btn-xs viewBtn" data-client="'.$key.'" data-business="'.$value['business_name'].'">'.__("View",true).'</button></td></tr>';

						$col = [];
						$col[] = '<div class="ellipsis-250">'.$value['business_name'].'</div>';
						$col[] = $value['receivables'];
						$col[] = number_format($value['total_updated_amount'],2,$decimals,$thousands);
						$col[] = '<button class="btn btn-primary btn-xs viewBtn" data-client="'.$key.'" data-business="'.$value['business_name'].'">'.__("View",true).'</button>';
						$client_datatable[] = $col;
					}					
					$return['status'] = true;
					$return['client_html'] = $client_html;
					$return['client_datatable'] = $client_datatable;
					$return['client_data'] = $clientList;
				} else {
					$return['status'] = true;
					$return['client_html'] = '<tr><td colspan="4">'.__("No receivables found",true).'</td></tr>';
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

	function fetch_debt_client_receivables() {
		if (isset($_POST['token'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();

				if ($payload->role=="client_user") {
					$user_id = $UM->getClientIdbyClientUserId($payload->uuid);
				} else {
					$user_id = $payload->uuid;
				}
				$filters['status_filter'] = "Overdue";
				if (isset($_POST['client_id'])) {
					$filters['client_id'] = $_POST['client_id'];
				}
				if ($payload->role=="admin") {
					$result = $UM->fetchInvoices(null,$filters);
				} else {
					$result = $UM->fetchInvoices($user_id,$filters);
				}

				$invoice_arr = [];
				if (isset($_POST['ar_id'])) {
					$invoiceCollection = $UM->getGlobalbyId("debt_collection",$_POST['ar_id']);
					$invoice_arr = json_decode($invoiceCollection['invoice_items']);
				}
						
				if (count($result)>0) {
					$datatable = "";
					$clients = [];
					foreach ($result as $key => $value) {
						$earlier = new DateTime(date("Y-m-d"));
						$later = new DateTime(date("Y-m-d",strtotime($value['due_date'])));
						$day_overdue = $later->diff($earlier)->format("%a");

						$invoice = $value;
						$partialPayments = $UM->fetchInvoicePayments($invoice['uuid']);
						$total_invoice_amount = $total_updated_amount = $total_partial = 0;
						if (count($partialPayments)>0) {
							foreach ($partialPayments as $pp) {
								$total_partial += $pp['amount'];
							}
						}							

						$discounts = ($invoice['amount']*($invoice['discount']/100));
		        		$late_fee = ($invoice['amount']*($invoice['late_fee']/100));
				        $interest = (($invoice['interest']/100)/30) * $day_overdue * $invoice['amount'];
				        $total = $invoice['amount'] - $total_partial - $discounts + $late_fee + $interest;

				        $total_updated_amount = $total;

						if (in_array($value['uuid'],$invoice_arr)) {
							$datatable .= '<tr data-amount="'.$value['amount'].'" data-updatedamount="'.$total_updated_amount.'" data-currency="'.$value['currency'].'">';
							$datatable .= '<td><input type="checkbox" name="receivables_selected[]" value="'.$value['uuid'].'" checked="checked"></td>';
						} else {
							if ($res = $UM->isDebtInvoiceCollected($value['uuid'])) {
								$datatable .= '<tr data-amount="'.$value['amount'].'" data-updatedamount="'.$total_updated_amount.'" data-currency="'.$value['currency'].'" class="text-muted">';
								$datatable .= '<td><input type="checkbox" name="receivables_selected[]" value="'.$value['uuid'].'" disabled="disabled"></td>';
							} else {
								$datatable .= '<tr data-amount="'.$value['amount'].'" data-updatedamount="'.$total_updated_amount.'" data-currency="'.$value['currency'].'">';
								$datatable .= '<td><input type="checkbox" name="receivables_selected[]" value="'.$value['uuid'].'"></td>';
							}
							
						}

						if ($value['currency']=="BRL") {
							$decimals = ",";
							$thousands = ".";
						} else {
							$decimals = ".";
							$thousands = ",";
						}

							$datatable .= '<td><a href="/invoice/'.$value['uuid'].'" target="_blank"><b>'.$value['invoice_no'].'<i class="icon-new-tab mgl-5" style="font-size:13px;"></i></b></a></td>';
							$datatable .= '<td>'.number_format($value['amount'],2,$decimals,$thousands).'</td>';
							$datatable .= '<td>'.number_format($total_updated_amount,2,$decimals,$thousands).'</td>';
							$datatable .= '<td><b>'.$day_overdue.' '.__("Days",true).'</b><br>'.date(DATEFORMAT,strtotime($value['due_date'])).'</td>';
						$datatable .= '</tr>';
					}
					
					$return['status'] = true;
					$return['datatable'] = $datatable;
					$return['data'] = $result;
				} else {
					$return['status'] = true;
					$return['datatable'] = [];
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

	function add_debt_collection() {
		if (isset($_POST['token']) && isset($_POST['data'])) {			
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel();								
				parse_str($_POST['data'],$newdata);
				$inv_id = $newdata['uuid'] = hexdec(uniqid());
				if ($payload->role=="client_user") {
					$user_id = $UM->getClientIdbyClientUserId($payload->uuid);
				} else {
					$user_id = $payload->uuid;
				}

				$newdata['user_id'] = $user_id;
				$newdata['created_by'] = $payload->uuid;
				$newdata['invoice_items'] = json_encode($newdata['receivables_selected']);
				unset($newdata['receivables_selected']);
				unset($newdata['all']);
				$bankaccount = $UM->fetchPrimaryBankAccount($user_id);
				$newdata['bank_account_id'] = $bankaccount['uuid'];
				$business = $UM->fetchPrimaryBusinessAccount($user_id);
				$newdata['business_id'] = $business['uuid'];				
				$result = $UM->addGlobal("debt_collection",$newdata);				
				if ($result) {
					$return['status'] = true;
					$return['message'] = __("Successfully added an invoice",true);
					$return['invoice_id'] = $inv_id;
				} else {
					$return['status'] = false;
					$return['message'] = __("Failed to add invoice",true);
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

	function update_debt_collection() {
		if((isset($_POST['data'])) && isset($_POST['token']))
		{
			parse_str($_POST['data'],$postdata);
			$id = $postdata['uuid'];
			
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));
			if ($tokenvalue) {			
				$UM = new UserModel;
				$newdata = [];			
				$newdata['invoice_items'] = json_encode($postdata['receivables_selected']);
				
				$result = $UM->updateGlobal('debt_collection',$id,$newdata);

				if($result)
				{
					$return['status'] = true;
					if (isset($newdata['status']) && $newdata['status']=="Paid") {
						if ($payment_type=="partial") {
							$return['message'] = __("Invoice paid partially",true)."!";
						} else {
							$return['message'] = __("Invoice set to paid!",true);
						}	
					} else {
						$return['message'] = __("Successfully updated invoice",true);	
					}
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to update invoice. Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
			}					
		} else {
			$return['status'] = false;
			$return['message'] = __("Missing Token",true);
		}
		echo json_encode($return);
		exit;
	}

	function delete_debt_collection() {
		if(isset($_POST['id']) && isset($_POST['token']))
		{
			$id = $_POST['id'];
			$tokenvalue = JWT::decode($_POST['token'], new Key(KEY, 'HS256'));			
			if ($tokenvalue) {
				$UM = new UserModel;
				$newdata['is_deleted'] = 1;
				
				$result = $UM->updateGlobal('debt_collection',$id,$newdata);	
				if($result)
				{
					$return['status'] = true;
					$return['message'] = __("Successfully remove invoice",true);
					
				}
				else
				{
					$return['status'] = false;
					$return['message'] = __("Not able to remove invoice. Please recheck all fields or contact support.",true);
				}
				
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);				
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("Missing Token",true);
		}
		echo json_encode($return);
		exit;
	}

	function send_email_negotiation_alert() {
		if(isset($_POST['id']) && isset($_POST['token'])) {
			

		    $UM = new UserModel;
		    $invoice = $UM->getGlobalbyId("debt_collection",$_POST['id']);
		    $client = $UM->getGlobalbyId("client_lists",$invoice['client_id']);		    
		    $supplier_business = $UM->getGlobalbyId("business_accounts",$invoice['business_id']);
		    $supplier = $UM->getGlobalbyId("users",$invoice['user_id']);

		    if ($supplier_business['business_email']!="") {
			    $reply_email = $supplier_business['business_email'];
			} else {
			    $reply_email = $supplier['email'];
			}

			$col = [];
			if (ENV == "DEVELOPMENT") {
				$col['address']['email'] = "neilangelodavid@gmail.com";
			} else {
				$col['address']['email'] = $reply_email;
			}
		    $recipients[] = $col;

		    $html = '<p style=""><b>'.ucwords($client['business_name']).'</b> '.__("requested a deal for",true).' <b>'.ucwords($supplier_business['business_name']).'</b> '.__("on Invoice",true).' <b>#'."1".sprintf('%06d', $invoice['id']).'</b>.</p>';

		    $html .= __("We suggest that you contact",true).' '.ucwords($client['business_name']).' '.__("to proceed with the negotiation.",true);
		    $html .= '<p><a href="'.SITE_URL.'/p/'.$invoice['uuid'].'" target="_blank" style="text-decoration: none!important;color: #212122!important;border-radius: 3px;padding: 6px 14px;background-color: #c2d5a8;font-weight:500;">'.__("View Invoice",true).'</a></p>';

		    $html .= '<table style="border:1px solid;width:500px;border-collapse: collapse;"><tr style="border-bottom:1px solid;"><td style="padding-left: 10px;"><b>'.__("Client Details",true).':</b></td><td style="padding-left: 10px;border-left: 1px solid;"><b>'.__("Supplier Details",true).':</b></td></tr>';
		    $html .= '<tr><td style="padding-left: 10px;">'.__("Name",true).': ';		    
		    if ($client['name']!="") {
		    	$html .= ucwords($client['name']);
		    }		    
		    $html .= '</td><td style="padding-left: 10px;border-left: 1px solid;">'.__("Name",true).': ';
		    $html .= ucwords($supplier['first_name']." ".$supplier['last_name']);
		    $html .= '</td></tr>';
		    $html .= '<tr><td style="padding-left: 10px;">'.__("Email",true).': ';
		    if ($client['email']!="") {
		    	$html .= $client['email'];
		    }
		    $html .= '</td><td style="padding-left: 10px;border-left: 1px solid;">'.__("Email",true).': ';
		    if ($supplier['email']!="") {
		    	$html .= $supplier['email'];
		    }
		    $html .= '</td></tr>';
		    $html .= '<tr><td style="padding-left: 10px;">'.__("Contact",true).': ';
		    if ($client['contact']!="") {
		    	$html .= $client['contact'];
		    }
		    $html .= '</td><td style="padding-left: 10px;border-left: 1px solid;">'.__("Contact",true).': ';
		    if ($supplier['contact']!="") {
		    	$html .= $supplier['contact'];
		    }
		    $html .= '</td></tr></table>';

			$emailArr = [];
			$emailArr['campaign_id'] = $invoice['uuid'];
			$emailArr['recipients'] = $recipients;
			$emailArr['content']['reply_to'] = "support@wekolek.com";
			$emailArr['content']['from']['email'] = "invoicing@mail.wekolek.com";
			$emailArr['content']['from']['name'] = __("Team Kolek",true);
			$emailArr['content']['subject'] = __("Invoice #",true)."1".sprintf('%06d', $invoice['id'])." ".__("Negotiation Alert!",true);
			$emailArr['content']['html'] = $html;
			$emailArr['content']['text'] = strip_tags($html);			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://api.sparkpost.com/api/v1/transmissions?num_rcpt_errors=3');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailArr));
			
			$headers = array();
			$headers[] = 'Content-Type: application/json';
			$headers[] = 'Accept: application/json';
			$headers[] = 'Authorization: cc7433de5492eb10c89fbd43d3f7887fabed61c1';
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

			$result = json_decode(curl_exec($ch));
			if (curl_errno($ch)) {
			      echo 'Error:' . curl_error($ch);
			}

			if ($result) {
			    if (isset($result->errors)) {
			      	$return['status'] = false;
					$return['message'] = "Failed: ".$result->errors[0]->message;   					     
			    } else {
			    	$return['status'] = true;
					$return['message'] = __("Please give us a moment and our team will be in contact with you.",true);
					$return['sent_at'] = date(DATEFORMAT." H:i A");
			    }    
			} else {
			    $return['status'] = false;
				$return['message'] = __("Failed to send email. Please contact support.",true);
			}
			curl_close($ch);
		} else {
			$return['status'] = false;
			$return['message'] = __("Missing Token",true);
		}
		echo json_encode($return);
		exit;
	}
	// END DEBT COLLECTION


	//OF - QUANTO
	function sync_bank_account_statements() {
		exit;
	}

	function fetch_bank_account_statements() {
		if(isset($_POST['of_supplier']) && $_POST['of_supplier']=="quanto" && isset($_POST['token']) && isset($_POST['bank_account_id'])) {		
			$jwt = $_POST['token'];
			try {
				$UM = new UserModel;
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$client = new \GuzzleHttp\Client();
				$access_token = quanto_token();				
				if ($_POST['start_date']!="" && $_POST['end_date']!="") {
					$period['start'] = $_POST['start_date'];
					$period['end'] = $_POST['end_date'];
				} else {
					$period = null;
				}
				

				$html_reconciliation = '';
				$bankAccountData = $UM->getGlobalbyId("bank_accounts",$_POST['bank_account_id']);				
				if (true || $bankAccountData['last_sync']==NULL || date("Y-m-d",strtotime($bankAccountData['last_sync'])) != date("Y-m-d")) {
					$account_id = $bankAccountData['quanto_account_id'];
					$permission_id = $bankAccountData['quanto_permission_id'];
					
					// FETCH STATEMENTS from Account Id via CATEGORIZED ACCOUNT STATEMENT
					$response = $client->request('GET', 'https://api-quanto.com/v1/account-statement/'.$account_id, [
					  'headers' => [
					    'accept' => 'application/json, charset=utf-8',
					    'authorization' => 'Bearer '.$access_token,
					    'x-permission-id' => $permission_id,
					  ],
					]);

					// FETCH STATEMENTS from Account Id via ACCOUNT > ACCOUNT TRANSACTIONS
					// $response = $client->request('GET', 'https://api-quanto.com/v1/opf/accounts/'.$account_id.'/transactions?fromBookingDate='.$period['start'].'&toBookingDate='.$period['end'], [
					//   'headers' => [
					//     'accept' => 'application/json, charset=utf-8',
					//     'authorization' => 'Bearer '.$access_token,
					//     'x-permission-id' => $permission_id,
					//   ],
					// ]);

					$account_statement_result = json_decode($response->getBody());					
					$currency = $html_body = "";
					$decimals = ".";
					$thousands = ",";
					$total = 0; 

					foreach ($account_statement_result->data->transactions as $transaction) {						
						if ($period==null || ($transaction->date >= $period['start'] && $transaction->date <= $period['end'])) {
							if ($currency == "") {
								$currency = $UM->fetchCurrencyByCode($transaction->currency);
								if ($transaction->currency=="BRL") {
									$decimals = ",";
									$thousands = ".";
								}
							}
							$html_body .= "<tr><td>".date(DATEFORMAT,strtotime($transaction->date))."</td><td>".$transaction->name."</td>";
							if ($transaction->creditDebitType=="DEBITO") {
								// $html_body .= __("Outflow",true);
								$html_body .= "<td></td><td>".number_format($transaction->amount,2,$decimals,$thousands)."</td>";
								$total -= $transaction->amount;
							} else if ($transaction->creditDebitType=="CREDITO") {
								// $html_body .= __("Inflow",true);
								$html_body .= "<td>".number_format($transaction->amount,2,$decimals,$thousands)."</td><td></td>";
								$total += $transaction->amount;
							}
							$html_body .= "<td>".$transaction->currency."</td></tr>";							
						}

						if ($transaction->creditDebitType=="DEBITO") {
							$total -= $transaction->amount;
						} else if ($transaction->creditDebitType=="CREDITO") {
							$total += $transaction->amount;
						}


						$newData = [];
						$newData['transaction_id'] = $transaction->quantoData->transactionId;
						$newData['bank_account_id'] = $bankAccountData['uuid'];
						$newData['credit_debit_type'] = $transaction->creditDebitType;
						$newData['description'] = $transaction->name;
						$newData['transaction_type'] = $transaction->type;
						$newData['amount'] = $transaction->amount;
						$newData['currency'] = $transaction->currency;
						$newData['transaction_date'] = $transaction->date;
						// Insert transaction
						$res = $UM->syncBankAccountTransactions($newData);
					}
					if($html_body=="") {
						$html_body = '<tr><td colspan="5">'.__("No transactions found",true).'.</td></tr>';
					}					

					// Update sync time
					$syncUpdateData = [];
					$syncUpdateData['last_sync'] = date("Y-m-d H:i:s");
					$syncUpdateData['quanto_account_name'] = $account_statement_result->data->owner->name;					

					$abs = "";
					if ($total<0) {
						$abs = "-";
						$total = abs($total);
					}

					$html_data = '<p class="mg0">'.__("Name",true).': '.$account_statement_result->data->owner->name.' <label class="label label-success pull-right">'.__("Total",true).' '.$abs.$currency.number_format($total,2,$decimals,$thousands).'</label></p>';
					if (isset($account_statement_result->data->owner->cnpj)) {
						$html_data .= '<p class="mgb-10">CNPJ: '.format_cnpj($account_statement_result->data->owner->cnpj).'</p>';
						$syncUpdateData['quanto_cnpj'] = $account_statement_result->data->owner->cnpj;
					}
					if (isset($account_statement_result->data->owner->cpf)) {
						$html_data .= '<p class="mgb-10">CPF: '.format_cpf($account_statement_result->data->owner->cpf).'</p>';
						$syncUpdateData['quanto_cpf'] = $account_statement_result->data->owner->cpf;
					}

					$UM->updateGlobal("bank_accounts",$bankAccountData['uuid'],$syncUpdateData);
					
					$html_data .= '<table class="table lean-padding table-responsive table-striped"><thead><tr><th style="width:15%;">'.__("Date",true)."</th><th>".__("Transaction details",true)."</th><th>".__("Credits",true)."</th><th>".__("Debits",true)."</th><th>".__("Currency",true)."</th></tr></thead><tbody>";
					$html_data .= $html_body;
					$html_data .= "</tbody></table>";
				} else {												
					// fetch statement via Database
					$account_statement_result = $transctions = $UM->fetchBankAccountTransactions($_POST['bank_account_id'],$period,"quanto");
					$currency = $html_body = "";
					$decimals = ".";
					$thousands = ",";
					$total = 0;
					$html_reconciliation = '<table class="table lean-padding"><thead>
						<tr><th style="width:50px;"></th><th>'.__("Invoice No.",true).'</th><th>'.__("Amount",true).'</th><th>'.__("Issued Date",true).'</th><th></th></thead><tbody>';
					if (count($transctions)>0) {
						foreach ($transctions as $transctions_value) {
							if ($currency == "") {
								$currency = $UM->fetchCurrencyByCode($transctions_value['currency']);
								if ($transctions_value['currency']=="BRL") {
									$decimals = ",";
									$thousands = ".";
								} else {
									$decimals = ".";
									$thousands = ",";
								}	
							}
							$html_body .= "<tr><td>".date(DATEFORMAT,strtotime($transctions_value['transaction_date']))."</td><td>".$transctions_value['description']."</td>";
							$color_type = "class='text-inflow'";
							if ($transctions_value['credit_debit_type']=="DEBITO") {
								$color_type = "class='text-outflow'";
								$total -= $transctions_value['amount'];
								$html_body .= "<td></td><td>".number_format($transctions_value['amount'],2,$decimals,$thousands)."</td>";
							} else if ($transctions_value['credit_debit_type']=="CREDITO") {
								$total += $transctions_value['amount'];
								$html_body .= "<td>".number_format($transctions_value['amount'],2,$decimals,$thousands)."</td><td></td>";


								//check matching statements & receivables
								$statementData = [];
								$statementData['amount'] = $transctions_value['amount'];
								$statementData['transaction_date'] = $transctions_value['transaction_date'];
								$reconciliationResult = $UM->matchStatementReceivables($payload->uuid,$statementData);
								
								if ($reconciliationResult) {
									$html_reconciliation .= '<tr data-id="'.$reconciliationResult['uuid'].'"><td><i class="icon-checkbox-checked2 text-success pointer inv-paid"></i></td>';
									$html_reconciliation .= '<td><a href="/invoice/'.$reconciliationResult['uuid'].'">'.$reconciliationResult['invoice_no'].'</td>';
									$html_reconciliation .= '<td>'.$currency.number_format($reconciliationResult['amount'],2,DECIMALS,THOUSANDS).'</td>';											
									$html_reconciliation .= '<td>'.date(DATEFORMAT,strtotime($reconciliationResult['issued_date'])).'</td>';
									$html_reconciliation .= '<td><input type="hidden" name="reconcile[]" value="'.$reconciliationResult['uuid'].'"/></td>';
									$html_reconciliation .= '</tr>';
								}
								
							}							
							$html_body .= "<td>".$transctions_value['currency']."</td></tr>";			
						}

					}	
					
					$html_reconciliation .= '</table>';

					if($html_body=="") {
						$html_body = '<tr><td colspan="5">'.__("No transactions found",true).'.</td></tr>';
					}

					$abs = "";
					if ($total<0) {
						$abs = "-";
						$total = abs($total);
					}

					$html_data = '<p class="mg0">'.__("Name",true).': '.$bankAccountData['quanto_account_name'].' <label class="label label-success pull-right">'.__("Total",true).' '.$abs.$currency.number_format($total,2,$decimals,$thousands).'</label></p>';
					if ($bankAccountData['quanto_cnpj']!="" && $bankAccountData['quanto_cnpj']!=NULL) {
						$html_data .= '<p class="mgb-10">CNPJ: '.format_cnpj($bankAccountData['quanto_cnpj']).'</p>';
					}
					if ($bankAccountData['quanto_cpf']!="" && $bankAccountData['quanto_cpf']!=NULL) {
						$html_data .= '<p class="mgb-10">CPF: '.format_cpf($bankAccountData['quanto_cpf']).'</p>';
					}
					$html_data .= '<table class="table lean-padding table-responsive table-striped"><thead><tr><th style="width:15%;">'.__("Date",true)."</th><th>".__("Transaction details",true)."</th><th>".__("Credits",true)."</th><th>".__("Debits",true)."</th><th>".__("Currency",true)."</th></tr></thead><tbody>";
					$html_data .= $html_body;
					$html_data .= "</tbody></table>";
				}
				$return['status'] = true;				
				$return['json_data'] = "<pre>".print_r($account_statement_result,true)."</pre>";
				$return['html_data'] = $html_data;
				$return['reconciliation_data'] = $html_reconciliation;

				echo json_encode($return);
				exit;
				

			} catch (Exception $e) {				
				$return['status'] = false;
				$return['message'] = "<h5>".__("Sorry for the inconvenience.",true)."</h5><p>".__("We are unable to fetch data. Our team is looking at this issue and we'll resolve this as soon as possible.",true)."</p>";
				$return['error_message'] = $e->getMessage();
				echo json_encode($return);
				exit;
			}
			
		} else if(isset($_POST['of_supplier']) && $_POST['of_supplier']=="pluggy" && isset($_POST['token']) && isset($_POST['bank_account_id'])) {		
			$jwt = $_POST['token'];
			try {
				$UM = new UserModel;
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$pluggy_api_key = pluggy_api_key();

				if ($_POST['start_date']!="" && $_POST['end_date']!="") {
					$period['start'] = $_POST['start_date'];
					$period['end'] = $_POST['end_date'];
				} else {
					$period = null;
				}
				
				$bank_account_id = $_POST['bank_account_id'];
				$html_reconciliation = '';
				$bankAccountData = $UM->getGlobalbyId("bank_accounts",$_POST['bank_account_id']);				

				$lastSync = new DateTime($bankAccountData['last_sync']);
				$currentDateTime = new DateTime();
				// Add 8 hours to lastSync datetime
				$lastSync->modify('+8 hours');
				
				if ($bankAccountData['last_sync']==NULL || $currentDateTime > $lastSync) {
					$account_id = $bankAccountData['pluggy_account_id'];
					$curl = curl_init();
					curl_setopt_array($curl, [
					  CURLOPT_URL => "https://api.pluggy.ai/accounts/".$account_id,
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => "",
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 30,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => "GET",
					  CURLOPT_HTTPHEADER => [
					    "X-API-KEY: ".$pluggy_api_key,
					    "accept: application/json"
					  ],
					]);

					$result = curl_exec($curl);
					if (curl_errno($curl)) {
					    $return['status'] = false;
					    $return['message'] = 'Error:' . curl_error($curl);
					    echo json_encode($return);
					    exit;
					}
					curl_close($curl);
					$account = json_decode($result);					
					$balance = $account->balance;						
					
					//check if account # is separated by /
					$account_number_arr = explode("/",$account->number);
					$account_number = $account->number;
					$account_name = $account->owner;
					$account_number_only = $branch_number = $bank_number = "";

					if (count($account_number_arr)>1) {
						$account_number_only = $account_number = $account_number_arr[1];
						$branch_number = $account_name = $account_number_arr[0];
						$account_number = $account->number;						
					}

					$bankDataExp = explode("/",$account->bankData->transferNumber);					
					if (count($bankDataExp) == 3) {
						$bank_number = $bankDataExp[0];
						$branch_number = $bankDataExp[1];
						$account_number_only = $bankDataExp[2];
						$account_number = $bankDataExp[1]."/".$bankDataExp[2];
						$account_name = $account->owner;
					}				
						
					
					$last_statement = $UM->fetchLastStatementByBankAccountId($bank_account_id);
					$from_date = "";
					if ($last_statement && $last_statement['transaction_date']!=date("Y-m-d")) {
						$from_date = "&from=".date("Y-m-d",strtotime($last_statement['transaction_date']." -2 days"));
					}
					
					//fetch statements and save using account id
					$bankAccountDataUpdate = [];					
					$bankAccountDataUpdate['bank_name'] = $bankAccountData['bank_name'];
					$bankAccountDataUpdate['account_name'] = $account_name;
					$bankAccountDataUpdate['account_number'] = $account_number;
					$bankAccountDataUpdate['last_balance'] = $account->balance;
					$bankAccountDataUpdate['last_sync'] = date("Y-m-d H:i:s"); //not update sync datetime
					
					$UM->updateGlobal("bank_accounts",$bank_account_id,$bankAccountDataUpdate);					
					
					//fetch transactions statements
					$curl = curl_init();
					curl_setopt_array($curl, [
					  CURLOPT_URL => "https://api.pluggy.ai/transactions?accountId=".$account_id."&pageSize=500".$from_date,
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => "",
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 30,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => "GET",
					  CURLOPT_HTTPHEADER => [
					    "X-API-KEY: ".$pluggy_api_key,
					    "accept: application/json"
					  ],
					]);

					$resultStatement = curl_exec($curl);

					if (curl_errno($curl)) {
						$return['status'] = false;
					    $return['message'] = 'Error:' . curl_error($curl);
					    echo json_encode($return);
					    exit;
					}
					curl_close($curl);					
					$account_statement_result = json_decode($resultStatement);
					$statements = [];
					
					$page = $account_statement_result->page;
					$page_total = $account_statement_result->totalPages;
					$total_result = $account_statement_result->total;					
					foreach ($account_statement_result->results as $transaction) {
						$statements[] = $transaction;
					}							
					
					if ($page!=$page_total) {
						while ($page++ <= $page_total) {
							$curl = curl_init();
							curl_setopt_array($curl, [
							  CURLOPT_URL => "https://api.pluggy.ai/transactions?accountId=".$account_id."&pageSize=500&page=".$page.$from_date,
							  CURLOPT_RETURNTRANSFER => true,
							  CURLOPT_ENCODING => "",
							  CURLOPT_MAXREDIRS => 10,
							  CURLOPT_TIMEOUT => 30,
							  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
							  CURLOPT_CUSTOMREQUEST => "GET",
							  CURLOPT_HTTPHEADER => [
							    "X-API-KEY: ".$pluggy_api_key,
							    "accept: application/json"
							  ],
							]);

							$resultStatement = curl_exec($curl);
							if (curl_errno($curl)) {
								$return['status'] = false;
							    $return['message'] = 'Error:' . curl_error($curl);
							    echo json_encode($return);
							    exit;
							}
							curl_close($curl);
							$account_statement_result = json_decode($resultStatement);
							foreach ($account_statement_result->results as $transaction) {
								$statements[] = $transaction;
							}
							$page = $account_statement_result->page;
						}
					}
				

					if (count($statements)>0) {
						foreach ($statements as $statement) {							
							$statementData = [];
							$statementExist = $UM->fetchStatementByTransactionId($statement->id);

							if ($statementExist == false) {
								//only add new statements
								$statementData['transaction_id'] = $statement->id;
								$statementData['bank_account_id'] = $bank_account_id;
								$statementData['credit_debit_type'] = $statement->type;
								$statementData['description'] = $statement->description;
								$statementData['amount'] = abs($statement->amount);
								$statementData['currency'] = $statement->currencyCode;
								$statementData['category'] = $statement->category;
								$statementData['transaction_date'] = date("Y-m-d",strtotime($statement->date));
								$statementData['of_supplier'] = "pluggy";
								$UM->addGlobal("bank_account_transactions",$statementData);								
							}
						}
					}					
				}
				
				// fetch statement via Database
				$account_statement_result = $transctions = $UM->fetchBankAccountTransactions($_POST['bank_account_id'],$period,"pluggy");				
				$currency = $html_body = "";
				$decimals = ".";
				$thousands = ",";
				$total = 0;
				
				$datatable = [];
				if (count($transctions)>0) {
					foreach ($transctions as $transctions_value) {
						$col = [];
						if ($currency == "") {
							$currency = $UM->fetchCurrencyByCode($transctions_value['currency']);
							if ($transctions_value['currency']=="BRL") {
								$decimals = ",";
								$thousands = ".";
							} else {
								$decimals = ".";
								$thousands = ",";
							}	
						}
						$html_body .= "<tr><td>".date(DATEFORMAT,strtotime($transctions_value['transaction_date']))."</td><td>".$transctions_value['description']."</td>";
						$color_type = "class='text-inflow'";

						$col[] = date(DATEFORMAT,strtotime($transctions_value['transaction_date']));
						$col[] = $transctions_value['description'];

						if ($transctions_value['credit_debit_type']=="DEBIT") {
							$color_type = "class='text-outflow'";
							$total -= $transctions_value['amount'];
							$html_body .= "<td></td><td>".number_format($transctions_value['amount'],2,$decimals,$thousands)."</td>";
							$col[] = "";
							$col[] = number_format($transctions_value['amount'],2,$decimals,$thousands);
						} else if ($transctions_value['credit_debit_type']=="CREDIT") {
							$total += $transctions_value['amount'];
							$html_body .= "<td>".number_format($transctions_value['amount'],2,$decimals,$thousands)."</td><td></td>";
							$col[] = number_format($transctions_value['amount'],2,$decimals,$thousands);
							$col[] = "";
						}							
						$html_body .= "<td>".$transctions_value['currency']."</td></tr>";
						$col[] = $transctions_value['currency'];
						$datatable[] = $col;	
					}

				}	

				if($html_body=="") {
					$html_body = '<tr><td colspan="5">'.__("No transactions found",true).'.</td></tr>';					
				}

				$html_data = '<h5 class="mg0"><b>'.$bankAccountData['account_name'].'</b> <label class="f-14 label label-success pull-right">'.__("Balance",true).' '.$currency.number_format($bankAccountData['last_balance'],2,$decimals,$thousands).'</label></h5><small class="semi-bold text-muted">'.$bankAccountData['bank_name'].' '.$bankAccountData['account_number'].'</small>';
				if (false) {
					$html_data .= '<table class="table lean-padding table-responsive table-striped"><thead><tr><th style="width:15%;">'.__("Date",true)."</th><th>".__("Transaction details",true)."</th><th>".__("Credits",true)."</th><th>".__("Debits",true)."</th><th>".__("Currency",true)."</th></tr></thead><tbody>";
					$html_data .= $html_body;
					$html_data .= "</tbody></table>";
				}

				$return['status'] = true;				
				$return['html_data'] = $html_data;
				$return['datatable'] = $datatable;

				echo json_encode($return);
				exit;
				

			} catch (Exception $e) {				
				$return['status'] = false;
				$return['message'] = "<h5>".__("Sorry for the inconvenience.",true)."</h5><p>".__("We are unable to fetch data. Our team is looking at this issue and we'll resolve this as soon as possible.",true)."</p>";
				$return['error_message'] = $e->getMessage();
				echo json_encode($return);
				exit;
			}
			
		} else {
			$return['status'] = false;
			$return['message'] = __("Missing Token",true);
		}
		echo json_encode($return);
		exit;
	}

	function email_validation($email=null) {
		$email = trim($email);
		$email = str_replace(";",",",$email);		
		if (isset($_GET['email']) && $email==null) {
			$email = str_replace(" ","+",$_GET['email']);
			// Use a regular expression to validate the email address
			if (preg_match('/^[^@\s]+@([^@\s]+\.)+[^@\s]+$/', $email)) {
			    echo true;
			} else {
				echo false;
			}
			exit;
		} else if ($email!=null) {
			$newEmail = explode(",",$email);						
			$returnEmail = [];
			if ($newEmail!=null) {
				foreach ($newEmail as $value) {					
					$value = trim($value);
					if ($value!="") {
						if (preg_match('/^[^@\s]+@([^@\s]+\.)+[^@\s]+$/', $value)) {
						    $returnEmail[] = $value;
						} else {
							$return['status'] = false;
							$return['message'] = __("Invalid email",true).": ".$value.". ".__("Please try another email",true).".";
							return $return;
						}
					}
				}
				$return['status'] = true;
				$return['email'] = implode(",",$returnEmail);
				return $return;
			} else {
				$return['status'] = false;
				$return['message'] = __("Invalid email",true).". ".__("Please try another email",true).".";
				return $return;
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("Invalid email",true).". ".__("Please try another email",true).".";
			return $return;
		}
		exit;
	}


	// RECONCILIATION 

	function fetch_receivable_match() {
		if (isset($_POST['token'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				$UM = new UserModel;	
				if ($_SESSION['userdata']['industry']=="School" || $payload->role=="client_user") {
					$user_id = $UM->getClientIdbyClientUserId($payload->uuid);
				} else {
					$user_id = $payload->uuid;
				}
				$result = $UM->fetchActiveInvoices($user_id);

				$html_list = '<div class="row">
								<div class="col-xs-6">
								<h6 class="mg0">'.__("Receivables",true).'</h6>
								</div><div class="col-xs-6">
								<h6 class="mg0">'.__("Account Statements",true).'</h6>
								</div>
							</div>';

				$total_matches = 0;

				if (is_array($result) && count($result)>0) {
					foreach ($result as $key => $value) {
						if ($value['currency']=="BRL") {
							$thousands = ".";
    						$decimals = ",";
						} else {
							$decimals = ".";
    						$thousands = ",";
						}

						$partialPayments = $UM->fetchInvoicePayments($value['uuid']);
						$discounts = $late_fee = $interest = $total_invoice_amount = $total_updated_amount = $total_partial = 0;
						if (count($partialPayments)>0) {
							foreach ($partialPayments as $pp) {
								$total_partial += $pp['amount'];
							}
						}

						$earlier = new DateTime(date("Y-m-d"));
				        $later = new DateTime(date("Y-m-d",strtotime($value['due_date'])));
				        $day_overdue = $later->diff($earlier)->format("%a");							

						$discounts = ($value['amount']*($value['discount']/100));
		        		$late_fee = ($value['amount']*($value['late_fee']/100));
				        $interest = (($value['interest']/100)/30) * $day_overdue * $value['amount'];

				        $no_interest_amount = $value['amount'] - $total_partial - $discounts;
				        if ($later<$earlier) {
				        	$total_updated_amount = $value['amount'] - $total_partial - $discounts + $late_fee + $interest;
				        } else {
				        	$total_updated_amount = $value['amount'] - $total_partial - $discounts;
				        }

				        $bank_accounts = $UM->fetchBankAccounts($user_id);
				        $bank_account_filter = "";
				        $bank_account_filter_arr = [];
				        foreach ($bank_accounts as $bnk_act) {
				        	$bank_account_filter_arr[] = "bank_account_id='".$bnk_act['uuid']."'";
				        }
				        $bank_account_filter = "(".implode(" OR ",$bank_account_filter_arr).")";
				        $filter['bank_account_filter'] = $bank_account_filter;
				        $filter['issued_date'] = $value['issued_date'];
				        $filter['no_interest_amount'] = $no_interest_amount;
				        $filter['interest_amount'] = $total_updated_amount;
				        $match = $UM->matchInvoiceToStatement($filter);				        

				        if ($match) {
				        	$total_matches++;
				        	$html_list .= '<div class="row">
								<div class="col-xs-6">
									<div class="dotted-grey-box">
										<table class="table lean-padding">
											<thead>
												<tr>
													<th>'.__("Invoice No.",true).'</th>
													<th>'.__("Amount",true).'</th>
													<th>'.__("Updated Amount",true).'</th>
													<th>'.__("Issued Date",true).'</th>
													<th>'.__("Due Date",true).'</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td><b>'.$value['invoice_no'].'</b></td>
													<td>'.number_format($value['amount'],2,$decimals,$thousands).'</td>
													<td>'.number_format($total_updated_amount,2,$decimals,$thousands).'</td>
													<td>'.date(DATEFORMAT,strtotime($value['issued_date'])).'</td>
													<td>'.date(DATEFORMAT,strtotime($value['due_date'])).'</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
								<div class="col-xs-6">
									<div class="dotted-grey-box">
										<table class="table lean-padding">
											<thead>
												<tr>
													<th></th>
													<th>'.__("Payment date",true).'</th>
													<th>'.__("Bank Name",true).'</th>
													<th>'.__("Transaction details",true).'</th>
													<th>'.__("Amount",true).'</th>
												</tr>
											</thead>
											<tbody>';
												foreach ($match as $txn) {
													$html_list .= '<tr>
														<td>
															<input type="radio" name="match_receivable['.$value['uuid'].']" value="'.$txn['transaction_id'].'">
														</td>
														<td>'.date(DATEFORMAT,strtotime($txn['transaction_date'])).'</td>
														<td>'.$txn['bank_account_details']['bank_name'].'</td>
														<td>'.$txn['description'].'</td>
														<td>'.number_format($txn['amount'],2,$decimals,$thousands).'</td>
													</tr>';
												}
											$html_list .= '</tbody>
										</table>
									</div>							
								</div>
							</div><hr/>';
				        }
						
					}
				}
							
				if ($total_matches>0) {
					$return['html_data'] = $html_list;
				} else {
					$return['html_data'] = 	__("No matching statement and invoice found.",true);
				}
				$return['status'] = true;
				$return['matches'] = $total_matches;
				$return['data'] = $result;
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

	function reconcile_receivables() {
		if (isset($_POST['token']) && isset($_POST['data'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				if ($payload) {
					$UM = new UserModel;
					parse_str($_POST['data'],$postdata);					
					foreach ($postdata['match_receivable'] as $key => $value) {						
						$stmt = $UM->fetchStatementByTransactionId($value);						
						$newData = [];
						$newData['status'] = "Paid";
						$newData['payment_date'] = $stmt['transaction_date'];
						$UM->updateGlobal("account_receivables",$key,$newData);
						$UM->reconcileStatementWithInvoice($value,$key);
					}
					$return['status'] = true;
					$return['message'] = __("Successfully reconciled receivalbles.",true);
				} else {
					$return['status'] = false;
					$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
				}
			} catch (Exception $e) {
				$return['status'] = false;
				$return['message'] = $e->getMessage();
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
		}
		echo json_encode($return);
		exit;
	}

	function validate_invoice_numbers() {
		if (isset($_POST['token']) && isset($_POST['ar_id'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				if ($payload) {
					$UM = new UserModel;
					if ($_POST['ar_id']=="") {
						$return['status'] = false;
						$return['message'] = __("No invoice number found.",true);
						echo json_encode($return);
						exit;
					}
					$ar_id = explode("\n",$_POST['ar_id']);
					$valid = [];
					$invalid = [];
					if ($payload->role=="client_user") {
						$user_id = $UM->getClientIdbyClientUserId($payload->uuid);
					} else {
						$user_id = $payload->uuid;
					}					
					foreach ($ar_id as $id) {
						$id = str_replace("#","",$id);
						if (isset($_POST['invoice_type']) && $_POST['invoice_type']=="consolidated") {							
							$newid = (int)substr($id, 1);
							$inv = $UM->fetchDebtCollectionByInvoiceNo($user_id,$newid);
							if (count($inv)>0) {
								$valid[$id]['uuid'] = $inv['uuid'];
								$valid[$id]['type'] = "debt_collection";
							} else {
								$invalid[$id] = "debt_collection";
							}
						} else {
							$inv = $UM->fetchInvoiceByInvoiceNo($user_id,$id);
							if (count($inv)>0) {
								$valid[$id]['uuid'] = $inv['uuid'];
								$valid[$id]['type'] = "account_receivables";
							} else {
								$invalid[$id] = "account_receivables";
							}
						}						
					}
					$validation_result = "<div class='text-right'>".__("Valid",true)." <i class='icon-check text-green'></i>: ".count($valid);
					$validation_result .= "<span class='mgl-20'>".__("Invalid",true)."</span> <i class='icon-x text-danger'></i>: ".count($invalid);
					if (count($invalid)>0) {
						$validation_result .= "<hr class='mg0'/>";
						foreach ($invalid as $invid => $id) {
							$validation_result .= $invid."<br>";
						}
					}
					$validation_result .= "</div>";
					$return['status'] = true;
					$return['validation_result'] = $validation_result;
					$return['valid'] = $valid;
					$return['valid_total'] = count($valid);
					$return['invalid'] = $invalid;
				} else {
					$return['status'] = false;
					$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
				}
			} catch (Exception $e) {
				$return['status'] = false;
				$return['message'] = $e->getMessage();
			}
		} else {
			$return['status'] = false;
			$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
		}
		echo json_encode($return);
		exit;
	}


	// ONE TIME FUNCTION
	function remove_paid_invoice_number_from_debt_collection() {
		$UM = new UserModel;
		$user_id = null;
		$invoice_number = null;
		if ($invoice_number!=null && $user!=null) {
			$inv = $UM->fetchInvoiceByInvoiceNumber($user_id,$ar_id);
			if ($inv) {
				$dc = $UM->fetchInvoiceByIdInDebtCollection("1744510132298618",$inv['uuid']);
				if ($dc) {
					$invoice_items = json_decode($dc['invoice_items']);
					$remove_index = false;
					foreach ($invoice_items as $key2 => $value2) {
						if ($inv['uuid']==$value2) {
							$remove_index = $key2;
							break;
						}
					}
					if ($remove_index!==false) {
						$invoice_items = (array)$invoice_items;						
						unset($invoice_items[$remove_index]);
						$newdata = [];

						if (count($invoice_items)==0) {
							$newdata['status'] = "Paid";
							$newdata['payment_date'] = "2023-04-20";
						} else {
							$newdata['invoice_items'] = json_encode($invoice_items);
						}

						$res = $UM->updateGlobal("debt_collection",$dc['uuid'],$newdata);
						if ($res) {
							echo $key." - ".$inv['uuid']." - true<br>";
						} else {
							echo $key." - ".$inv['uuid']." - false<br>";
						}
						
					}
				}	
			}
		}		
		exit;
	}


	// OMIE INTEGRATION
	function get_omie(){
		if (isset($_POST['token'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				if ($payload) {
					$user_id = $payload->uuid;
					$fetch_limit = 500;
					parse_str($_POST['data'],$postdata);
					$UM = new UserModel;

					if ($postdata['is_new']=="1") {
						//SAVE OMIE CREDENTIALS to DB
						$omieData = array(
							'uuid' => hexdec(uniqid()),
							'erp' => 'omie',
							'user_id' => $user_id,
							'api_key' => $postdata['api_key'], 
							'client_secret' => $postdata['client_secret'] 
						);
					    $res = $UM->addGlobal('erp_integration', $omieData);
					}
									
					$client = new GuzzleHttp\Client();
					
					$api_key = $postdata['api_key'];
					$client_secret = $postdata['client_secret'];				


					//FETCHING CLIENT LIST - no record limit
					$response_clients = $client->request('POST', 
						'https://app.omie.com.br/api/v1/geral/clientes/', [
						'json' => [
							'call' => 'ListarClientes',
							'app_key' => $api_key,
							'app_secret' => $client_secret,
							'param' => [
								[
									'pagina' => 1,
									'registros_por_pagina' => $fetch_limit,
									'apenas_importado_api' => 'N'
								]
							]
						],
						'headers' => [
							'Content-Type' => 'application/json',
							'Accept' => 'application/json'
						],
					]);
					$response_api_clients = json_decode($response_clients->getBody()->getContents());					

					//LOOP client data
					foreach ($response_api_clients->clientes_cadastro as $registro_clientes) {
						//check if client already exist by business name
						$responseClientCheck = $UM->fetchClientByBusinessName($registro_clientes->nome_fantasia,$payload->uuid);
						if ($responseClientCheck===false) {
							$clientData = array(
								'uuid' => $registro_clientes->codigo_cliente_omie,
								'user_id' => $payload->uuid,
								'business_name'=> $registro_clientes->nome_fantasia,
								'name'=> (isset($registro_clientes->contato)) ? $registro_clientes->contato : "",
								'email'=> (isset($registro_clientes->email)) ? $registro_clientes->email : "",
								'contact' => (isset($registro_clientes->telefone1_numero)) ? $registro_clientes->telefone1_numero : ""
							);
							//add client if doesn't exist
							$UM->addGlobal('client_lists', $clientData);
						} else {
							$clientData = array(
								'business_name'=> $registro_clientes->nome_fantasia,
								'name'=> (isset($registro_clientes->contato)) ? $registro_clientes->contato : "",
								'email'=> (isset($registro_clientes->email)) ? $registro_clientes->email : "",
								'contact' => (isset($registro_clientes->telefone1_numero)) ? $registro_clientes->telefone1_numero : ""
							);
							//update client if doesn't exist
							$UM->updateGlobal('client_lists',$responseClientCheck['uuid'],$clientData);
						}
					}
				
					// //FETCHING RECEIVABLES - no record limit
					$response_invoices = $client->request('POST', 'https://app.omie.com.br/api/v1/financas/contareceber/', [
						'json' => [
							'call' => 'ListarContasReceber',
							'app_key' => $api_key,
							'app_secret' => $client_secret,
							'param' => [
								[
									'pagina' => 1,
									'registros_por_pagina' => $fetch_limit,
									'apenas_importado_api' => 'N'
								]
							]
						],
						'headers' => [
							'Content-Type' => 'application/json',
							'Accept' => 'application/json'
						],
					]);
					$response_api_invoices = json_decode($response_invoices->getBody()->getContents());
					

					//Fetch Bank Accounts - no record limit
					$response_banks_accounts = $client->request('POST', 'https://app.omie.com.br/api/v1/geral/contacorrente/', [
						'json' => [
							'call' => 'ListarContasCorrentes',
							'app_key' => $api_key,
							'app_secret' => $client_secret,
							'param' => [
								[
									'pagina' => 1,
									'registros_por_pagina' => $fetch_limit,
									'apenas_importado_api' => 'N'
								]
							]
						],
						'headers' => [
							'Content-Type' => 'application/json',
							'Accept' => 'application/json'
						],
					]);
					$response_api_bank = json_decode($response_banks_accounts->getBody()->getContents());

					
					$bankAccountData = [];
					
					//LOOP bank account records to see if exist in Kolek, if not then add to KoleK
					foreach ($response_api_bank->ListarContasCorrentes as $bankAccount) {
						$bankAccountData = array(	
							'bank_name' => $bankAccount->descricao,
							'account_number' => $bankAccount->nCodCC,
							'uuid' => hexdec(uniqid()),
							'user_id' => $payload->uuid,
							'country' => "brazil"
						);
						//check if account number exists
						$bankAccountCheck = $UM->fetchOmieBankByAccountNumber($bankAccount->nCodCC,$payload->uuid);
						if ($bankAccountCheck===false) {
							//add bank account
							$addBankAccount = $UM->addGlobal('bank_accounts',$bankAccountData);
						}
					}									

					// If there are invoices fetched					
					if (is_array($response_api_invoices->conta_receber_cadastro) && count($response_api_invoices->conta_receber_cadastro) > 0) {
						
						//pull primary business accounts
						$responseBusinessAccountDB = $UM->fetchPrimaryBusinessAccount($payload->uuid);
						
						$countUpdated = 0;
						$countAdded = 0;
						
						$omieInvoices = [];
						//LOOPING of receivables
						foreach ($response_api_invoices->conta_receber_cadastro as $registro) {
							$invoiceData = [];
							$categoria = $registro->categorias;//line items
							
							//FOR LINE ITEMS
							$line_items = [];
							foreach($categoria as $item) {
								$col = [];
								if ($item->valor > 0) {
									$col['description'] = $registro->numero_parcela;
									$col['quantity'] = 1;
									$col['price'] = $item->valor;
									$line_items[] = $col;
								}					
							}
							$line_items = json_encode($line_items);

							$uuid = hexdec(uniqid());							
							$omieInvoices[$registro->codigo_lancamento_omie] = true;

							//Setting date as string 
							$fecha_vencimiento = DateTime::createFromFormat('d/m/Y', $registro->data_vencimento);
							if ($fecha_vencimiento !== false) {
								$due_date = $fecha_vencimiento->format('Y-m-d');
							} else {
								$due_date = $fecha_vencimiento->format('Y-m-d');
							}

							$emission_date = DateTime::createFromFormat('d/m/Y', $registro->data_emissao); 
							if ($emission_date !== false) {
								$issued_date = $emission_date->format('Y-m-d');
							} else {
								$issued_date = $emission_date->format('Y-m-d');
							}

							//GET BOLETO LINK							
							if ($registro->boleto->cGerado =="S") {								
								$response_boleto = $client->request('POST', 'https://app.omie.com.br/api/v1/financas/contareceberboleto/', [
									'json' => [
										'call' => 'ObterBoleto',
										'app_key' => $api_key,
										'app_secret' => $client_secret,
										'param' => [
											[
												'nCodTitulo' => $registro->codigo_lancamento_omie,
												'cCodIntTitulo' => '',
											]
										]
									],
									'headers' => [
										'Content-Type' => 'application/json','encoding=UTF-8',
										'Accept' => 'application/json'
									],
								]);
								$response_api_boleto = json_decode($response_boleto->getBody()->getContents());
							}							
							
							$invoiceData = array(
								'status' => omie_status_translate($registro->status_titulo),  
								'amount' => $registro->valor_documento,
								'issued_date' => $issued_date,
								'due_date' => $due_date,
								'invoice_no' => (isset($registro->numero_documento) ? $registro->numero_documento : '') . (isset($registro->numero_parcela) ? " ".$registro->numero_parcela : ''),
								'currency' => "BRL",
								'line_items'=> $line_items,
								'bank_account_id' => $registro->id_conta_corrente,
								'user_id' => $payload->uuid,
								'client_id' => $registro->codigo_cliente_fornecedor,
								'uuid' => $uuid,
								'business_id'=> $responseBusinessAccountDB['uuid'],
								'fetch_id' => $registro->codigo_lancamento_omie,
								'fetch_from' => 'omie',
								'boleto' => isset($registro->boleto->cGerado) && ($registro->boleto->cGerado == "S") ? $response_api_boleto->cLinkBoleto : '',
								'created_by' => $payload->uuid
							);

							if ($invoiceData['status']=="Paid") {
								// //FETCH Payments details to get payment_date
								$response_paid = $client->request('POST', 'https://app.omie.com.br/api/v1/financas/mf/', [
									'json' => [
										'call' => 'ListarMovimentos',
										'app_key' => $api_key,
										'app_secret' => $client_secret,
										'param' => [
											[
												'nPagina' => 1,
												'nRegPorPagina' => $fetch_limit,
												'cStatus' => 'RECEBIDO',
												'nCodTitulo' => $registro->codigo_lancamento_omie
											]
										]
									],
									'headers' => [
										'Content-Type' => 'application/json',
										'Accept' => 'application/json'
									],
								]);
								$response_api_paid = json_decode($response_paid->getBody()->getContents());
								if (count($response_api_paid->movimentos)>0) {
									$data_pagamento = DateTime::createFromFormat('d/m/Y', $response_api_paid->movimentos[0]->detalhes->dDtPagamento); 
									//add payment_date
									$invoiceData['payment_date'] = $data_pagamento->format('Y-m-d');
								}
							}
													
							$invoiceCheck = $UM->fetchOmieInvoicesByUuid($invoiceData['fetch_id'], null);							


							if ($invoiceCheck===false) {
								//add invoice if new
								$resp = $UM->addGlobal("account_receivables", $invoiceData);
								if ($resp) {
									add_lifecycle_default($payload->uuid,$invoiceData);
									$countAdded++;
								}
							} else {
								//update invoice if new
								unset($invoiceData['uuid']);
								$resp = $UM->updateGlobal('account_receivables', $invoiceCheck['uuid'], $invoiceData);
								if ($resp) {
									$countUpdated++;
								}
							}
						}//END LOOP						
						
						//Delete platform invoice if it's is not found from Omie fetch
						$invoices = $UM->fetchInvoices($user_id,null);						
						foreach ($invoices as $invoice) {
							if (isset($omieInvoices[$invoice['fetch_id']])===false) {
								if ($invoice['fetch_from']=="omie") {
									$UM->deleteGlobalById('account_receivables', $invoice['uuid']);
								}
							}
						}

						$return['status'] = true;
						$return['message'] = __("Successfully added invoices",true).": ".$countAdded."<br>".__("Successfully updated invoices",true).": ".$countUpdated;

					} else {
						$return['status'] = true;
						$return['message'] = __("No invoices fetched.",true);
					}
				} else {
					$return['status'] = false;
					$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
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

	//Replace status from portuguese to english for db				
	function omie_status_translate($status) {
		switch ($status) {
			case 'RECEBIDO':
				return 'Paid';
			case 'VENCE HOJE':
				return 'Ongoing';
			case 'A VENCER':
				return 'Ongoing';
			case 'ATRASADO':
				return 'Ongoing';
			default:
				return 'Ongoing';
		}
		exit;
	}

	function check_omie_integration(){
		if (isset($_POST['token'])) {
			$jwt = $_POST['token'];
			try {
				$payload = JWT::decode($jwt, new Key(KEY, 'HS256'));
				if ($payload) {
					$UM = new UserModel;
					$user_id = $payload->uuid;
					$DBresponse = $UM->fetchOmieErpIntegration($user_id);
					
					if ($DBresponse){
						$return['status'] = true;
						$return['data'] = $DBresponse;
					} else {
						$return['status'] = false;
						$return['data'] = false;
					}					
				} else{
					$return['message'] = __("Invalid Token. Please re-log in or contact support.",true);
					$return['status'] = false;
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

	function omie_create_or_update($result, $cb, $id) {
		$UM = new UserModel;
		$clientDB = $UM->getGlobalbyId("client_lists",$result['event']['codigo_cliente_fornecedor']);		
		$data_vencimento = DateTime::createFromFormat('Y-m-d\TH:i:sO', $result['event']['data_vencimento']);				
		if ($data_vencimento !== false) {
			$due_date = $data_vencimento->format('Y-m-d');
		} else {
			$due_date = $data_vencimento->format('Y-m-d');
		}
		$emission_date = DateTime::createFromFormat('Y-m-d\TH:i:sO', $result['event']['data_emissao']);		
		if ($emission_date !== false) {
			$issued_date = $emission_date->format('Y-m-d');
		} else {
			$issued_date = $emission_date->format('Y-m-d');
		}

		$responseBusinessAccountDB = $UM->fetchPrimaryBusinessAccount($clientDB['user_id']);
		$line_items = [];

		$col = [];
			if ($result['event']['valor_documento'] > 0) {
				$col['description'] = $result['event']['numero_parcela'];
				$col['quantity'] = 1;
				$col['price'] = $result['event']['valor_documento'];
				$line_items[] = $col;
			
			}
		$line_items = json_encode($line_items);			
		$uuid = hexdec(uniqid());			
		$newInvoice = array(
			'status' => omie_status_translate($result['event']['situacao']),
			'amount' => $result['event']['valor_documento'],
			'issued_date' => $issued_date,
			'due_date' => $due_date,
			'invoice_no' => (isset($result['event']['numero_documento']) ? $result['event']['numero_documento'] : '') . (isset($result['event']['numero_parcela']) ? " " . $result['event']['numero_parcela'] : ''),
			'currency' => "BRL",
			'line_items' => $line_items,
			'user_id' => $clientDB['user_id'],
			'bank_account_id' => $result['event']['id_conta_corrente'],
			'business_id' => $responseBusinessAccountDB['uuid'],
			'fetch_id' => $result['event']['codigo_lancamento_omie'],
			'fetch_from' => 'omie',
			'boleto' => null,
			'payment_date' => (isset($result['event']['data_entrada']) ? $result['event']['data_entrada'] : '')
		);



		if ($UM->fetchOmieInvoicesByUuid($result['event']['codigo_lancamento_omie'], null)){
			$invoiceDB = $UM->fetchOmieInvoicesByUuid($result['event']['codigo_lancamento_omie'], null);
			$newInvoice['uuid'] = $invoiceDB['uuid'] ;
		} else {
			$newInvoice['uuid'] = $uuid;
		}		

		if ($cb == "addGlobal"){
			$res = $UM->addGlobal("account_receivables", $newInvoice);
			if ($res) {
				add_lifecycle_default($clientDB['user_id'],$newInvoice);
				echo "Invoice successfully added: ".$newInvoice['uuid'];
			} else {
				echo "Failed to add Invoice: ".$newInvoice['uuid'];
			}
			exit;
		} else {

			$res = $UM->updateGlobal('account_receivables', $id, $newInvoice);
			if ($res) {
				echo "Invoice successfully updated: ".$id;
			} else {
				echo "Failed to updated Invoice: ".$id;
			}
			exit;
	    }

	    exit;
	}

	function omie_update(){
		$UM = new UserModel;
		$data = file_get_contents('php://input');				

		$result = json_decode($data, true);

		if (is_array($result)===false) {
			echo "webhook pending listen";
			exit;
		}

		// DELETED INVOICE
		if ($result['topic'] == "Financas.ContaReceber.Excluido") {
			$invoiceDB = $UM->fetchOmieInvoicesByUuid($result['event']['codigo_lancamento_omie'], null);
			$res = $UM->deleteGlobalById("account_receivables", $invoiceDB['uuid']);
			if ($res) {
				echo "Invoice successfully deleted: ".$invoiceDB['uuid'];
			} else {
				echo "Failed to delelete invoice: ".$invoiceDB['uuid'];
			}
			exit;
		} else if ($result['topic'] == "Financas.ContaReceber.Incluido") { // CREATED INVOICE
			$invoiceDB = $UM->fetchOmieInvoicesByUuid($result['event']['codigo_lancamento_omie'], null);
			if ($invoiceDB===false){
				omie_create_or_update($result,"addGlobal",null);
				exit;
			}
		} else if ($result['topic'] == "Financas.ContaReceber.BaixaRealizada") { //PAID INVOICE 
			$invoiceDB = $UM->fetchOmieInvoicesByUuid($result['event'][0]['conta_a_receber'][0]['codigo_lancamento_omie'], null);
			$clientDB = $UM->getGlobalbyId("client_lists",$result['event'][0]['codigo_cliente_fornecedor']);

			$fecha_vencimiento = DateTime::createFromFormat('Y-m-d\TH:i:sO', $result['event'][0]['conta_a_receber'][0]['data_emissao']);
			if ($fecha_vencimiento !== false) {
				$payment_date = $fecha_vencimiento->format('Y-m-d');
			} else {
				$payment_date = $fecha_vencimiento->format('Y-m-d');
			}
			$newData['status'] = "Paid";
			$newData['payment_date'] = $payment_date;
		
			$res = $UM->updateGlobal('account_receivables', $invoiceDB['uuid'], $newData);
			if ($res) {
				echo "Invoice successfully updated: ".$invoiceDB['uuid'];
			} else {
				echo "Failed to update invoice: ".$invoiceDB['uuid'];
			}
			exit;
		} else if ($result['topic'] == "Financas.ContaReceber.Alterado") { //MODIFIED INVOICE
			$invoiceDB = $UM->fetchOmieInvoicesByUuid($result['event']['codigo_lancamento_omie'], null);				
			$id = $invoiceDB['uuid'];				
			omie_create_or_update($result, "updateGlobal",$id);
			exit;
		} else if (($result['topic']) == 'ClienteFornecedor.Incluido'){ // CLIENT CREATED ClienteFornecedor.Incluido					
			$clientDB = $UM->fetchDataFromErpIntegration($result['appKey']);
			$newData['user_id'] = $clientDB['user_id'];
			$newData['uuid'] = $result['event']['codigo_cliente_omie'];
			$newData['business_name']= $result['event']['nome_fantasia'];
			$newData['contact'] = $result['event']['telefone1_numero'];
			$newData['email'] = $result['event']['email'];

			$res = $UM->addGlobal('client_lists', $newData);
			if ($res) {
				echo "Invoice successfully added: ".$newData['uuid'];
			} else {
				echo "Failed to add invoice: ".$newData['uuid'];
			}
			exit;

		} else if (($result['topic']) == 'ClienteFornecedor.Alterado'){ //CLIENT MODIFIED
			$clientDB = $UM->getGlobalbyId("client_lists",$result['event']['codigo_cliente_omie']);
			$newData['email'] = $result['event']['email'];
			$newData['contact'] = $result['event']['telefone1_numero'];
			$newData['business_name'] = $result['event']['nome_fantasia'];

			$res = $UM->updateGlobal('client_lists', $clientDB['uuid'], $newData);
			if ($res) {
				echo "Client successfully updated: ".$clientDB['uuid'];
			} else {
				echo "Failed to update Client: ".$clientDB['uuid'];
			}
			exit;

		} else if (($result['topic']) == 'ClienteFornecedor.Excluido'){ //CLIENT DELETED
			$clientDB = $UM->getGlobalbyId("client_lists",$result['event']['codigo_cliente_omie']);
			$res = $UM->deleteGlobalById('client_lists', $clientDB['uuid']);
			if ($res) {
				echo "Client successfully deleted: ".$clientDB['uuid'];
			} else {
				echo "Failed to delete Client: ".$clientDB['uuid'];
			}
			exit;

		} else if (($result['topic']) == 'Financas.ContaReceber.BoletoGerado') { //BOLETO CREATED 	
			$clientDB = $UM->fetchDataFromErpIntegration(['appKey']);
			$invoiceDB = $UM->fetchOmieInvoicesByUuid($result['event']['codigo_lancamento_omie'], null);

			$url = 'https://app.omie.com.br/api/v1/financas/contareceberboleto/';
			$body = json_encode([
				'call' => 'ObterBoleto',
				'app_key' => $clientDB['api_key'],
				'app_secret' => $clientDB['client_secret'],
				'param' => [
					[
						'nCodTitulo' => $result['event']['codigo_lancamento_omie']
						,
						'cCodIntTitulo' => '',
					]
				]
			]);
			
			// Configurar las opciones de cURL
			$options = array(
				CURLOPT_URL => $url,
				CURLOPT_POST => false,
				CURLOPT_POSTFIELDS => $body,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json',
					'Accept: application/json',
				),
			);
			
			// Inicializar cURL y ejecutar la solicitud
			$ch = curl_init();
			curl_setopt_array($ch, $options);
			$response_boleto = curl_exec($ch);
			curl_close($ch);		
			$body = json_decode($response_boleto);
			$newData['boleto'] = $body->cLinkBoleto;
			$res = $UM->updateGlobal('account_receivables', $invoiceDB['uuid'], $newData);
			if ($res) {
				echo "Invoice successfully updated: ".$invoiceDB['uuid'];
			} else {
				echo "Failed to updated Invoice: ".$invoiceDB['uuid'];
			}
			exit;
		} else if ($result['topic'] == 'Financas.ContaReceber.BoletoCancelado'){				
			$invoiceDB = $UM->fetchOmieInvoicesByUuid($result['event']['codigo_lancamento_omie'], null);
			$newData['boleto'] = null;
			$res = $UM->updateGlobal('account_receivables', $invoiceDB['uuid'], $newData);
			if ($res) {
				echo "Invoice successfully updated: ".$invoiceDB['uuid'];
			} else {
				echo "Failed to updated Invoice: ".$invoiceDB['uuid'];
			}
			exit;
		}				

		exit;
	}
	
?>