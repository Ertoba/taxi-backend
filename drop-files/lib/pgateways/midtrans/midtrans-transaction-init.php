<?php

    $data = $_POST['data'];

	if(!is_array($data) || empty($data))return false;
    //$data['callback_url'] = ""; //not used since we are using webhooks
	$transaction_id = crypto_string();
	$data['metadata']['transaction_id'] = $transaction_id;
    $pg_data = array(
		'transaction_details'=>array("order_id"=>$transaction_id,"gross_amount"=>(int) $data['amount'] / 100),
		'customer_details' => array("email"=>$data['email']),
		"credit_card"=> array("credit_card"=>true),
		"callbacks" => array("finish" => SITE_URL . "paynotify.php?pg=midtrans&callback=true")
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
	"midtrans",
	mysqli_real_escape_string($GLOBALS['DB'],json_encode($data['metadata']))

	);

	if(!$result = mysqli_query($GLOBALS['DB'], $query)){
		$data = array('error'=>'Unable to Initialize transaction: DB write error');
		echo json_encode($data);
		exit; 
	}

	

	$content = json_encode($pg_data);
	$p_sk = base64_encode(DEFAULT_PAYMENT_GATEWAY['midtrans']['midtrans-server-key'] . ":");
	$notification_url = SITE_URL . "paynotify.php?pg=midtrans";
	$curl = curl_init("https://app.midtrans.com/snap/v1/transactions");
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	//curl_setopt($curl, CURLOPT_USERPWD, $p_sk);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json","Authorization: Basic {$p_sk}","cache-control: no-cache","X-Override-Notification: {$notification_url}"));
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

	
	if(!empty($response['redirect_url'])){
        $data = array('success'=>1,'url'=>$response['redirect_url']);
        echo json_encode($data);
        exit;
	}else{
		$data = array('error'=>'Could not initialize transaction with gateway');
        echo json_encode($data);
        exit;
    }
    
?>