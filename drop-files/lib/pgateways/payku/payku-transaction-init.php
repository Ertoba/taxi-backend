<?php

    $data = $_POST['data'];

	if(!is_array($data) || empty($data))return false;
    //$data['callback_url'] = ""; //not used since we are using webhooks
	$transaction_id = crypto_string('numeric');

	$payku_data = array(
		"email"=> $data['email'],
		"order"=> $transaction_id,
		"subject"=> $data['metadata']['memo'],
		"amount"=> (int) $data['amount'] / 100,
		"payment"=> 1,
		"urlreturn"=> SITE_URL . "paynotify.php?pg=payku&callback=true&orderid={$transaction_id}",
		"urlnotify"=> SITE_URL . "paynotify.php?pg=payku"
	);


	

	

	$content = json_encode($payku_data);
	$p_pk = DEFAULT_PAYMENT_GATEWAY['payku']['payku-public-token'];
  
	$curl = curl_init("https://app.payku.cl/api/transaction");
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	//curl_setopt($curl, CURLOPT_USERPWD, $p_sk);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json","authorization: Bearer {$p_pk}","cache-control: no-cache"));
	//curl_setopt($curl, CURLOPT_HTTPGET, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
  
    $response = json_decode($json_response, true);
    
	if(json_last_error()){
        $data = array('error'=>'Could not initialize transaction with gateway');
        echo json_encode($data);
        exit;
    };

	
	if(!empty($response['url'])){

		//save data to database
		//add transaction to gateways database table
		$query = sprintf('INSERT INTO %stbl_pgateway (p_transaction_ref,transaction_ref,amount,`date`,cur,user_id,user_type,gateway,memo) VALUES ("%s","%s","%s","%s","%s","%d","%d","%s","%s")', 
		DB_TBL_PREFIX,
		$response['id'],
		$transaction_id,
		(int) $data['amount'] / 100,
		gmdate('Y-m-d H:i:s', time()),
		$data['currency'],
		$data['metadata']['user_id'],
		$data['metadata']['user_type'],
		"payku",
		mysqli_real_escape_string($GLOBALS['DB'],json_encode($data['metadata']))

		);

		if(!$result = mysqli_query($GLOBALS['DB'], $query)){
			$data = array('error'=>'Unable to Initialize transaction: DB write error');
			echo json_encode($data);
			exit; 
		}

        $data = array('success'=>1,'url'=>$response['url']);
        echo json_encode($data);
        exit;
	}else{
		$data = array('error'=>'Could not initialize transaction with gateway');
        echo json_encode($data);
        exit;
    }
    
?>