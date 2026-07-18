<?php

	require_once("PaytmChecksum.php");

    $data = $_POST['data'];

	if(!is_array($data) || empty($data))return false;
    //$data['callback_url'] = ""; //not used since we are using webhooks

	$transaction_id = crypto_string();
	$paytm_data = [];
	$merchant_id = DEFAULT_PAYMENT_GATEWAY['paytm']['paytm-merchant-id'];
	$merchant_key = DEFAULT_PAYMENT_GATEWAY['paytm']['paytm-merchant-key'];

	$paytm_data['body'] = array(
				"requestType" => "Payment",
				"mid" => $merchant_id,
				"websiteName" => "DEFAULT",
				"orderId" => $transaction_id,
				"callbackUrl" => SITE_URL . "paynotify.php?pg=paytm&callback=true",
				"txnAmount" => [
					"value" => $data['amount'] / 100,
					"currency" => "INR" /*$data['currency']*/
				],
				"userInfo" => [
					"custId"    => $data['metadata']['user_type'] . "_" . $data['metadata']['user_id']
				]
			);

	$checksum = PaytmChecksum::generateSignature(json_encode($paytm_data["body"], JSON_UNESCAPED_SLASHES), $merchant_key);

	$paytm_data["head"] = array(
		"signature" => $checksum
	);

	$post_data = json_encode($paytm_data, JSON_UNESCAPED_SLASHES);

	   
	
  
	$curl = curl_init("https://securegw.paytm.in/theia/api/v1/initiateTransaction?mid={$merchant_id}&orderId={$transaction_id}");
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	//curl_setopt($curl, CURLOPT_HTTPGET, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
  
    $response = json_decode($json_response, true);
    
	if(json_last_error()){
        $data = array('error'=>'Could not initialize transaction with gateways');
        echo json_encode($data);
        exit;
    };

	if(!(isset($response['body']) && isset($response['body']['txnToken']))){
		$data = array('error'=>'Could not initialize transaction with gateway', 'data'=>$response, 'res'=>$paytm_data);
    	echo json_encode($data);
    	exit;
    }
	

	$transaction_token = $response['body']['txnToken'];

	


	//save transaction data to database

	$query = sprintf('INSERT INTO %stbl_pgateway (transaction_ref,amount,`date`,cur,user_id,user_type,gateway,memo) VALUES ("%s","%s","%s","%s","%d","%d","%s","%s")', 
	DB_TBL_PREFIX,
	$transaction_id,
	$data['amount'] / 100,
	gmdate('Y-m-d H:i:s', time()),
	$data['currency'],
	$data['metadata']['user_id'],
	$data['metadata']['user_type'],
	"paytm",
	mysqli_real_escape_string($GLOBALS['DB'],json_encode($data['metadata']))

	);

	if(!$result = mysqli_query($GLOBALS['DB'], $query)){
		$data = array('error'=>'Unable to Initialize transaction: DB write error');
		echo json_encode($data);
		exit; 
	}


	$data = array('success'=>1,'url'=> SITE_URL . "paynotify.php?pg=paytm&callback=true&token={$transaction_token}&orderid={$transaction_id}&mid={$merchant_id}");
    echo json_encode($data);
    exit;
    
?>