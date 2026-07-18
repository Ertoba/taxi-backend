<?php

    $data = $_POST['data'];

	if(!is_array($data) || empty($data))return false;
    //$data['callback_url'] = ""; //not used since we are using webhooks
	
    $content_json = json_encode($data);
	$p_sk = DEFAULT_PAYMENT_GATEWAY['paystack']['paystack-secret-key'];
  
	$curl = curl_init("https://api.paystack.co/transaction/initialize/");
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json","authorization: Bearer {$p_sk}","cache-control: no-cache"));
	//curl_setopt($curl, CURLOPT_HTTPGET, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $content_json);
	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
  
    $response = json_decode($json_response, true);
    
	if(json_last_error()){
        $data = array('error'=>'Could not initialize transaction with gateway');
        echo json_encode($data);
        exit;
    };

	if($response['status'] == true){
        $data = array('success'=>1,'paystack_data'=>$response);
        echo json_encode($data);
        exit;
	}else{
		$data = array('error'=>'Could not initialize transaction with gateway');
        echo json_encode($data);
        exit;
    }
    
?>