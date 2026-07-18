<?php

    $data = $_POST['data'];

	if(!is_array($data) || empty($data))return false;
    //$data['callback_url'] = ""; //not used since we are using webhooks
	$transaction_id = crypto_string();
	$data['metadata']['transaction_id'] = $transaction_id;
    $stripe_data = array(
		'customer_email'=>$data['email'],
		'line_items' => [[
			'price_data' => [
				'currency' => $data['currency'],
				'product_data' => [	'name' => $data['metadata']['memo'],],
				'unit_amount' => $data['amount'],
			],
			'quantity' => 1,
		]],
		
		"mode"=> "payment",
		"success_url" => SITE_URL . "paynotify.php?pg=stripe&callback=true&resp=success",
		"cancel_url" => SITE_URL . "paynotify.php?pg=stripe&callback=true&resp=failed",
		'payment_intent_data' => ['metadata' => $data['metadata']]
	);


	//save data to database
	//add transaction to gateways database table
	$query = sprintf('INSERT INTO %stbl_pgateway (transaction_ref,amount,`date`,cur,user_id,user_type,gateway,memo) VALUES ("%s","%s","%s","%s","%d","%d","%s","%s")', 
	DB_TBL_PREFIX,
	$transaction_id,
	$data['amount'] / 100,
	gmdate('Y-m-d H:i:s', time()),
	$data['currency'],
	$data['metadata']['user_id'],
	$data['metadata']['user_type'],
	"stripe",
	mysqli_real_escape_string($GLOBALS['DB'],json_encode($data['metadata']))

	);

	if(!$result = mysqli_query($GLOBALS['DB'], $query)){
		$data = array('error'=>'Unable to Initialize transaction: DB write error');
		echo json_encode($data);
		exit; 
	}

	

	$content = http_build_query($stripe_data);
	$p_sk = DEFAULT_PAYMENT_GATEWAY['stripe']['stripe-secret-key'];
  
	$curl = curl_init("https://api.stripe.com/v1/checkout/sessions");
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_USERPWD, $p_sk);
	//curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json","authorization: Bearer {$p_sk}","cache-control: no-cache"));
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
        $data = array('success'=>1,'stripe_url'=>$response['url']);
        echo json_encode($data);
        exit;
	}else{
		$data = array('error'=>'Could not initialize transaction with gateway');
        echo json_encode($data);
        exit;
    }
    
?>