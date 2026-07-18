<?php



    $data = $_POST['data'];

	$data['currency'] = 'EGP'; //for testing;
	
	if(!is_array($data) || empty($data))return false;

    //$data['callback_url'] = ""; //not used since we are using webhooks
	$transaction_id = crypto_string();
	$data['metadata']['transaction_id'] = $transaction_id;
    

	//first get token from remote paymob gateway server

	$p_sk = DEFAULT_PAYMENT_GATEWAY['paymob']['paymob-api-key'];
	$api_key = array('api_key'=>$p_sk);
	$content = json_encode($api_key);
	
  
	$curl = curl_init("https://accept.paymob.com/api/auth/tokens");
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
	//curl_setopt($curl, CURLOPT_HTTPGET, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
  
    $response = json_decode($json_response, true);
    
	if(json_last_error() || empty($response['token'])){
        $data = array('error'=>'Could not initialize transaction with gateway');
        echo json_encode($data);
        exit;
    };

	$token = $response['token'];

	$payment_data = array(
		'auth_token' => $token,
		'delivery_needed'=> 'false',
		'amount_cents'=> $data['amount'],
		'currency'=>$data['currency'],
		'merchant_order_id'=>$transaction_id,
		'items'=>[]
	);

	$payment_data_json = json_encode($payment_data);

	//Get order ID from remote paymob gateway server
	$curl = curl_init("https://accept.paymob.com/api/ecommerce/orders");
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
	//curl_setopt($curl, CURLOPT_HTTPGET, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $payment_data_json);
	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
  
    $response = json_decode($json_response, true);
    
	if(json_last_error() || empty($response['id'])){
        $data = array('error'=>'Could not initialize transaction with gateway');
        echo json_encode($data);
        exit;
    };

	$order_id = $response['id'];
	
	//save data to database
	//add transaction to gateways database table
	$query = sprintf('INSERT INTO %stbl_pgateway (transaction_ref,p_transaction_ref,amount,`date`,cur,user_id,user_type,gateway,memo) VALUES ("%s","%s","%s","%s","%s","%d","%d","%s","%s")', 
	DB_TBL_PREFIX,
	$transaction_id,
	$response['id'],
	$data['amount'] / 100,
	gmdate('Y-m-d H:i:s', time()),
	$data['currency'],
	$data['metadata']['user_id'],
	$data['metadata']['user_type'],
	"paymob",
	mysqli_real_escape_string($GLOBALS['DB'],json_encode($data['metadata']))

	);

	if(!$result = mysqli_query($GLOBALS['DB'], $query)){
		$data = array('error'=>'Unable to Initialize transaction: DB write error');
		echo json_encode($data);
		exit; 
	}

	
	//Get payment key

	$payment_integration_ids = DEFAULT_PAYMENT_GATEWAY['paymob']['paymob-int-id'];
	
	if(empty($payment_integration_ids)){
		$data = array('error'=>'Could not initialize transaction with gateway');
		echo json_encode($data);
		exit; 
	}

	$p_int_arr = explode('|',$payment_integration_ids);
	@$p_int_id = $data['payment_mode'] == 'kiosk' ? trim($p_int_arr[1]) : trim($p_int_arr[0]);
	


	$payment_key_req_data = array(
		'auth_token'=>$token,
		'amount_cents'=>$data['amount'],
		'expiration'=>3600,
		'order_id'=> $order_id,
		'billing_data'=>[
			"apartment"=> "NA", 
			"email"=> $data['email'], 
			"floor"=> "NA", 
			"first_name"=> $data['firstname'], 
			"street"=> "NA", 
			"building"=> "NA", 
			"phone_number"=> $data['phone'], 
			"shipping_method"=> "NA", 
			"postal_code"=> "NA", 
			"city"=> "NA", 
			"country"=> "NA", 
			"last_name"=> $data['lastname'], 
			"state"=> "NA"
		],
		"currency"=> $data['currency'], 
		"integration_id"=> $p_int_id,
		"lock_order_when_paid"=> "false"
	);


	$payment_key_req_data_json = json_encode($payment_key_req_data);

	$curl = curl_init("https://accept.paymob.com/api/acceptance/payment_keys");
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
	//curl_setopt($curl, CURLOPT_HTTPGET, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $payment_key_req_data_json);
	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
  
    $response = json_decode($json_response, true);
    
	if(json_last_error() || empty($response['token'])){
        $data = array('error'=>'Could not initialize transaction with gateway');
        echo json_encode($data);
        exit;
    };

	$payment_token = $response['token'];

	if($data['payment_mode'] == 'kiosk'){

		//get bill reference for kiosk payment

		$bill_ref_data = array(
			'source' => [
				'identifier'=>'AGGREGATOR',
				'subtype'=>'AGGREGATOR'
			],
			'payment_token'=>$payment_token
		);

		$bill_ref_data_json = json_encode($bill_ref_data);

		$curl = curl_init("https://accept.paymob.com/api/acceptance/payments/pay");
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
		//curl_setopt($curl, CURLOPT_HTTPGET, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $bill_ref_data_json);
		$json_response = curl_exec($curl);
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
	
		$response = json_decode($json_response, true);
		
		if(json_last_error() || empty($response['data']['bill_reference'])){
			$data = array('error'=>'Could not initialize transaction with gateway');
			echo json_encode($data);
			exit;
		};

		$data = array('success'=>1,'bill_ref'=>$response['data']['bill_reference']);
        echo json_encode($data);
        exit;



	}

	
	$iframe_id = DEFAULT_PAYMENT_GATEWAY['paymob']['paymob-iframe-id'];

	$data = array('success'=>1,'url'=>"https://accept.paymobsolutions.com/api/acceptance/iframes/{$iframe_id}?payment_token={$payment_token}");
	echo json_encode($data);
	exit;

	

	
    
?>