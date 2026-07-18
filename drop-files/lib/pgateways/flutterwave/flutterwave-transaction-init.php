<?php

    $data = $_POST['data'];

	if(!is_array($data) || empty($data))return false;

    $transaction_id = crypto_string();
	$data['meta']['transaction_id'] = $transaction_id;
	
	$flutterwave_payment_options = 'card';
    
    switch($data['currency']){

        case "RWF":
        $flutterwave_payment_options = 'card, mobilemoneyrwanda';
        break;

		case "NGN":
		$flutterwave_payment_options = 'card, ussd, banktransfer';
		break;

		case "KES":
		$flutterwave_payment_options = 'card, mpesa, mobilemoneyrwanda';
		break;

        
        case "ZMW":
        $flutterwave_payment_options = 'card, mobilemoneyzambia';
        break;

        case "UGX":
        $flutterwave_payment_options = 'card, mobilemoneyuganda';
        break;

        case "GHS":
        $flutterwave_payment_options = 'card, mobilemoneyghana';
        break;

        case "TZS":
        $flutterwave_payment_options = 'card, mobilemoneytanzania';
        break;

        case "XOF":
        case "XAF":
		$flutterwave_payment_options = 'card, mobilemoneyfranco';
        break;


    }

	$data['payment_options'] = $flutterwave_payment_options;
	$data['tx_ref'] = $transaction_id;
	$data['redirect_url'] =  SITE_URL . "paynotify.php?pg=flut-wave&callback=true";
	$data['customizations'] = array(
		"title"=> WEBSITE_NAME,
		"description" => $data['meta']['memo'],
		"logo" => SITE_URL . "img/logo.png"
	);

    
	//save data to database
	//add transaction to gateways database table
	$query = sprintf('INSERT INTO %stbl_pgateway (transaction_ref,amount,`date`,cur,user_id,user_type,gateway,memo) VALUES ("%s","%s","%s","%s","%d","%d","%s","%s")', 
	DB_TBL_PREFIX,
	$transaction_id,
	$data['amount'],
	gmdate('Y-m-d H:i:s', time()),
	$data['currency'],
	$data['meta']['user_id'],
	$data['meta']['user_type'],
	"flut-wave",
	mysqli_real_escape_string($GLOBALS['DB'],json_encode($data['meta']))

	);

	if(!$result = mysqli_query($GLOBALS['DB'], $query)){
		$data = array('error'=>'Unable to Initialize transaction: DB write error');
		echo json_encode($data);
		exit; 
	}

	

	$content = json_encode($data);
	$p_sk = DEFAULT_PAYMENT_GATEWAY['flutterwave']['flutterwave-secret-key'];
  
	$curl = curl_init("https://api.flutterwave.com/v3/payments");
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json","authorization: Bearer {$p_sk}","cache-control: no-cache"));
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

	
	if(!empty($response['status']) && $response['status'] == 'success'){
        $data = array('success'=>1,'url'=>$response['data']['link']);
        echo json_encode($data);
        exit;
	}else{
		$data = array('error'=>'Could not initialize transaction with gateways');
        echo json_encode($data);
        exit;
    }
    
?>