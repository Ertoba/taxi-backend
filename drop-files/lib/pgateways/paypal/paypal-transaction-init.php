<?php
	$paypal_data = [];
    $data = $_POST['data'];
	

	if(!is_array($data) || empty($data))return false;

	$transaction_id = crypto_string();

	
	$payment_data = [
		"intent" => "sale",
		"payer" => ["payment_method" => "paypal"],
		"transactions" => [
							[
								"amount" => [
									"total" => (int) $data['amount'] / 100,
									"currency" => $data['currency'],
									"details" => [
											"subtotal" => (int) $data['amount'] / 100,
											"tax" => "0",
											"shipping" => "0",
											"handling_fee" => "0",
											"shipping_discount" => "0",
											"insurance" => "0"
										]
									],
								"description" => "App wallet funding",
								"custom" => $transaction_id,
								"invoice_number" => microtime(true),
								"payment_options" => ["allowed_payment_method" => "INSTANT_FUNDING_SOURCE"],
								"soft_descriptor" => microtime(true),
								"item_list" => [
									"items" => [
										[
											"name" => WEBSITE_NAME,
											"description" => "App wallet funding",
											"quantity" => "1",
											"price" => (int) $data['amount'] / 100,
											"tax" => "0",
											"sku" => "1",
											"currency" => $data['currency']
										]
									]
								]

							]
						],
		"note_to_payer" => "Thank you for using our service",
		"redirect_urls" => [
			"return_url" => SITE_URL . "paynotify.php?pg=paypal&callback=true&success=1",
			"cancel_url" => SITE_URL . "paynotify.php?pg=paypal&callback=true&success=0"
		]

	];

    


	


	//create a paypal auth token for this transaction

	$content = json_encode($paypal_data);	
	$curl = curl_init("https://api-m.paypal.com/v1/oauth2/token");
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_USERPWD, DEFAULT_PAYMENT_GATEWAY['paypal']['paypal-client-id'] . ":" . DEFAULT_PAYMENT_GATEWAY['paypal']['paypal-secret-key']);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded"));
	//curl_setopt($curl, CURLOPT_HTTPGET, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
  
    $response = json_decode($json_response, true);
    
	if(json_last_error()){
        $data = array('error'=>'Could not initialize transaction with gateway');
        echo json_encode($data);
        exit;
    };


	if(!isset($response['access_token'])){
        $data = array('error'=>'Could not initialize transaction with gateway');
        echo json_encode($data);
        exit;
	}

	$access_token = $response['access_token'];

	
	//save this access token. 
	$data['metadata']['access_token'] = $access_token;


	//create the paypal transaction

	$content = json_encode($payment_data);	
	$curl = curl_init("https://api-m.paypal.com/v1/payments/payment");
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	//curl_setopt($curl, CURLOPT_USERPWD, DEFAULT_PAYMENT_GATEWAY['paypal']['paypal-client-id'] . ":" . DEFAULT_PAYMENT_GATEWAY['paypal']['paypal-secret-key']);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Authorization: Bearer {$access_token}"));
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

	if(!isset($response['state']) && $response['state'] != "created"){
		$data = array('error'=>'Could not initialize transaction with gateway');
        echo json_encode($data);
        exit;
	}

	//save data to database
	//add transaction to gateways database table
	//$data['metadata']['pay_id'] = $response['id'];
	
	$query = sprintf('INSERT INTO %stbl_pgateway (p_transaction_ref,transaction_ref,amount,`date`,cur,user_id,user_type,gateway,memo) VALUES ("%s","%s","%s","%s","%s","%d","%d","%s","%s")', 
	DB_TBL_PREFIX,
	$response['id'],
	$transaction_id,
	$data['amount'] / 100,
	gmdate('Y-m-d H:i:s', time()),
	$data['currency'],
	$data['metadata']['user_id'],
	$data['metadata']['user_type'],
	"paypal",
	mysqli_real_escape_string($GLOBALS['DB'],json_encode($data['metadata']))

	);

	if(!$result = mysqli_query($GLOBALS['DB'], $query)){
		$data = array('error'=>'Unable to Initialize transaction: DB write error');
		echo json_encode($data);
		exit; 
	}

	$links = $response['links'];

	foreach($links as $link_item){
		if(isset($link_item['rel']) && $link_item['rel'] == "approval_url"){
			$data = array('success'=>1,'url'=> $link_item['href']);
			echo json_encode($data);
			exit;
		}
	}


	
	$data = array('error'=>'Could not initialize transaction with gateway');
	echo json_encode($data);
	exit;
	
	
    
?>