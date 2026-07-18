<?php

    $data = $_POST['data'];

	if(!is_array($data) || empty($data))return false;
    //$data['callback_url'] = ""; //not used since we are using webhooks

	/*
		{
			"merchantId": "MERCHANTUAT",
			"merchantTransactionId": "MT7850590068188104",
			"merchantUserId": "MUID123",
			"amount": 10000,
			"redirectUrl": "https://webhook.site/redirect-url",
			"redirectMode": "REDIRECT",
			"callbackUrl": "https://webhook.site/callback-url",
			"mobileNumber": "9999999999",
			"paymentInstrument": {
				"type": "PAY_PAGE"
			}
		}
	*/

	$transaction_id = crypto_string();
	$phonepe_data = [];
	$merchant_id = DEFAULT_PAYMENT_GATEWAY['phonepe']['phonepe-merchant-id'];
	$merchant_salt = DEFAULT_PAYMENT_GATEWAY['phonepe']['phonepe-merchant-salt'];	



	/* $phonepe_data = array(
				"merchantId" => DEFAULT_PAYMENT_GATEWAY['phonepe']['phonepe-merchant-id'],
				"merchantTransactionId" => $transaction_id,
				"merchantUserId" => $data['metadata']['user_id'],
				"amount" => $data['amount'],
				"mobileNumber" => $data['user_phone'],
				"redirectUrl" => SITE_URL . "paynotify.php?pg=phonepe&callback=true",
				"redirectMode" => "POST",
				"callbackUrl" => SITE_URL . "paynotify.php?pg=phonepe",
				"paymentInstrument" => ["type" => "PAY_PAGE"]
			); */

		$phonepe_data = array(
			"merchantId" => DEFAULT_PAYMENT_GATEWAY['phonepe']['phonepe-merchant-id'],
			"merchantTransactionId" => $transaction_id,
			"merchantUserId" => $data['metadata']['user_id'],
			"amount" => $data['amount'],
			"redirectUrl" => SITE_URL . "paynotify.php?pg=phonepe&callback=true",
			"redirectMode" => "POST",
			"callbackUrl" => SITE_URL . "paynotify.php?pg=phonepe",
			"paymentInstrument" => ["type" => "PAY_PAGE"]
		);

	
	

	$payload_enc = base64_encode(json_encode($phonepe_data));

	


	$payload_hash = hash("sha256", $payload_enc . "/pg/v1/pay" . $merchant_salt) . "###" . "1";

	
	
	$request = json_encode(["request" => $payload_enc]);
	
	
	$headers = ['Content-Type: application/json','accept: application/json','X-VERIFY:' . $payload_hash];

	
	
  
	$curl = curl_init("https://api.phonepe.com/apis/hermes/pg/v1/pay");
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	//curl_setopt($curl, CURLOPT_HTTPGET, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
  
    $response = json_decode($json_response, true);
    
	if(json_last_error()){
        $data = array('error'=>'Could not initialize transaction with gateways');
        echo json_encode($data);
        exit;
    };

	

	if(!(isset($response['success']) && $response['success'] == true)){
		$data = array('error'=>'Could not initialize transaction with gateway', 'data'=>$response, 'res'=>$paytm_data);
    	echo json_encode($data);
    	exit;
    }
	

		


	//save transaction data to database

	$query = sprintf('INSERT INTO %stbl_pgateway (transaction_ref,amount,`date`,cur,user_id,user_type,gateway,memo) VALUES ("%s","%s","%s","%s","%d","%d","%s","%s")', 
	DB_TBL_PREFIX,
	$transaction_id,
	$data['amount'] / 100,
	gmdate('Y-m-d H:i:s', time()),
	$data['metadata']['cur_code'],
	$data['metadata']['user_id'],
	$data['metadata']['user_type'],
	"phonepe",
	mysqli_real_escape_string($GLOBALS['DB'],json_encode($data['metadata']))

	);

	if(!$result = mysqli_query($GLOBALS['DB'], $query)){
		$data = array('error'=>'Unable to Initialize transaction: DB write error');
		echo json_encode($data);
		exit; 
	}


	$data = array('success'=>1,'url'=> $response['data']['instrumentResponse']['redirectInfo']['url']);
    echo json_encode($data);
    exit;
    
?>