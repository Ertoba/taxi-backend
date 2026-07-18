<?php
include_once('OAuth.php');

if(isset($_GET['redir-referrer'])){
	$redir_url = base64_decode($_GET['redir-referrer']);
	echo "
    		<html>
        	<head>
            	<meta name='viewport' content='width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no,viewport-fit=cover'>
        	</head>
         	<body>
    			<iframe src='{$redir_url}' width='100%' height='100%'  scrolling='no' frameBorder='0'><p>Browser unable to load iFrame</p></iframe>
            </body>
       	</html>
    
    	";
	exit;
}

$consumer_key=DEFAULT_PAYMENT_GATEWAY['pesapal']['pesapal-consumer-key'];//Register a merchant account on
                   //demo.pesapal.com and use the merchant key for testing.
                   //When you are ready to go live make sure you change the key to the live account
                   //registered on www.pesapal.com!
$consumer_secret=DEFAULT_PAYMENT_GATEWAY['pesapal']['pesapal-consumer-secret'];// Use the secret from your test
                   //account on demo.pesapal.com. When you are ready to go live make sure you 
                   //change the secret to the live account registered on www.pesapal.com!
$statusrequestAPI = 'https://www.pesapal.com/api/querypaymentstatus';//change to      
                   //https://www.pesapal.com/api/querypaymentstatus' when you are ready to go live!

// Parameters sent to you by PesaPal IPN
$pesapalNotification=$_GET['pesapal_notification_type'];
$pesapalTrackingId=$_GET['pesapal_transaction_tracking_id'];
$pesapal_merchant_reference=$_GET['pesapal_merchant_reference'];

if($pesapalNotification=="CHANGE" && $pesapalTrackingId!='')
{
   $token = $params = NULL;
   $consumer = new OAuthConsumer($consumer_key, $consumer_secret);
   $signature_method = new OAuthSignatureMethod_HMAC_SHA1();

   //get transaction status
   $request_status = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $statusrequestAPI, $params);
   $request_status->set_parameter("pesapal_merchant_reference", $pesapal_merchant_reference);
   $request_status->set_parameter("pesapal_transaction_tracking_id",$pesapalTrackingId);
   $request_status->sign_request($signature_method, $consumer, $token);

   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $request_status);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_HEADER, 1);
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
   

   $response = curl_exec($ch);

   $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
   $raw_header  = substr($response, 0, $header_size - 4);
   $headerArray = explode("\r\n\r\n", $raw_header);
   $header      = $headerArray[count($headerArray) - 1];

   //transaction status
   $elements = preg_split("/=/",substr($response, $header_size));
   $status = $elements[1];

   curl_close ($ch);

   //file_put_contents('pesapal_dump',print_r($response, true));
   
   //UPDATE YOUR DB TABLE WITH NEW STATUS FOR TRANSACTION WITH pesapal_transaction_tracking_id $pesapalTrackingId
   $transaction_data = [];

   $query = sprintf('SELECT * FROM %stbl_pgateway WHERE transaction_ref = "%s"', DB_TBL_PREFIX, $pesapal_merchant_reference);
   if($result = mysqli_query($GLOBALS['DB'], $query)){
		if(mysqli_num_rows($result)){

			$transaction_data = mysqli_fetch_assoc($result);

		}
   }

   

   if(empty($transaction_data))exit;
   if($transaction_data['status'] == "COMPLETED")exit;

   if($status != "COMPLETED"){
	   	//update DB status
		$query = sprintf('UPDATE %stbl_pgateway SET `status` = "%s" WHERE transaction_ref = "%s"', DB_TBL_PREFIX, $status, $pesapal_merchant_reference);
		$result = mysqli_query($GLOBALS['DB'], $query);	
		//notify user
		
	
		$query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
			("%d","%d","%s",3,"%s")', 
			DB_TBL_PREFIX,
			$transaction_data['user_id'],
			$transaction_data['user_type'],
			"Your last transaction with ID {$pesapal_merchant_reference} was unsuccessful. Payment gateway reported - {$status}",
			gmdate('Y-m-d H:i:s', time()) 
		);
		$result = mysqli_query($GLOBALS['DB'], $query);
		exit;
   }

//all was successful. Provide value

//update DB status
$query = sprintf('UPDATE %stbl_pgateway SET `status` = "%s" WHERE transaction_ref = "%s"', DB_TBL_PREFIX, $status, $pesapal_merchant_reference);
$result = mysqli_query($GLOBALS['DB'], $query);	
//update wallet

$transaction_metadata = json_decode($transaction_data['memo'], true);

$user_type = 0;
$user_id = 0;
$driver_data = [];
$customer_data = [];
$wallet_amount = 0.00;


$currency_symbol = $transaction_metadata['cur_symbol'];
$currency_code = $transaction_metadata['cur_code'];
$exchange_rate = (float) $transaction_metadata['cur_exchng'];
$user_type = (int) $transaction_metadata['user_type'];
$user_id = (int) $transaction_metadata['user_id'];
$user_currency_amount = ((float) $transaction_metadata['amount']);
$transaction_id = $pesapal_merchant_reference;

if($user_type){ //driver

	$query = sprintf('SELECT * FROM %1$stbl_drivers
	WHERE %1$stbl_drivers.driver_id = "%2$d"', DB_TBL_PREFIX, $user_id);

	if($result = mysqli_query($GLOBALS['DB'], $query)){
		if(mysqli_num_rows($result)){

			$driver_data = mysqli_fetch_assoc($result);

		}else{
			exit;
		}
	}else{
		exit;
	}

	$wallet_amount =  !empty($driver_data['wallet_amount']) ? $driver_data['wallet_amount'] : 0.00;      
	
	//convert the amount paid by user to the default currency. wallet amount is based on the default currency set

	//$converted_amount_paid = (float) $response_amount / $exchange_rate; //commented out this code beacause amount has already been converted to default currency (Naira) on the app

	$wallet_amount += (float) $user_currency_amount;

	$query = sprintf('UPDATE %stbl_drivers SET wallet_amount = %f WHERE driver_id = "%d"', DB_TBL_PREFIX, $wallet_amount,$user_id);
	$result = mysqli_query($GLOBALS['DB'], $query);

	
	
	//Add this transaction to wallet transactions database table
	
	$query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
	'("%s","%s","%s","%s","%s","%s","%d","%d","%s","%d","%s")', 
	DB_TBL_PREFIX,
	$currency_symbol,
	$exchange_rate,
	$currency_code,
	$transaction_id,
	$user_currency_amount,
	$wallet_amount,
	$user_id,
	1,
	"Wallet funding through app", 
	0,
	gmdate('Y-m-d H:i:s', time())

	);

	$result = mysqli_query($GLOBALS['DB'], $query);


}else{ //customer

	$query = sprintf('SELECT * FROM %1$stbl_users WHERE %1$stbl_users.user_id = "%2$d"', DB_TBL_PREFIX, $user_id);

	if($result = mysqli_query($GLOBALS['DB'], $query)){
		if(mysqli_num_rows($result)){

			$customer_data = mysqli_fetch_assoc($result);

		}else{
			exit;
		}
	}else{
		exit;
	}


	$wallet_amount =  !empty($customer_data['wallet_amount']) ? $customer_data['wallet_amount'] : 0.00;
	
			
	//convert the amount paid by user to the default currency. wallet amount is based on the default currency set
	//$converted_amount_paid = (float) $response_amount / $exchange_rate;
	$wallet_amount += (float) $user_currency_amount;

	$query = sprintf('UPDATE %stbl_users SET wallet_amount = %f WHERE user_id = "%d"', DB_TBL_PREFIX, $wallet_amount,$user_id);
	$result = mysqli_query($GLOBALS['DB'], $query);
	
	
			
	//Add this transaction to wallet transactions database table
	$transaction_id = crypto_string();
	$query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
	'("%s","%s","%s","%s","%s","%s","%d","%d","%s","%d","%s")', 
	DB_TBL_PREFIX,
	$currency_symbol,
	$exchange_rate,
	$currency_code,
	$transaction_id,
	$user_currency_amount,
	$wallet_amount,
	$user_id,
	0,
	"Wallet funding through app", 
	0,
	gmdate('Y-m-d H:i:s', time())

	);

	$result = mysqli_query($GLOBALS['DB'], $query);


}

















   /* if(DB_UPDATE_IS_SUCCESSFUL)
   {
      $resp="pesapal_notification_type=$pesapalNotification&pesapal_transaction_tracking_id=$pesapalTrackingId&pesapal_merchant_reference=$pesapal_merchant_reference";
      ob_start();
      echo $resp;
      ob_flush();
      exit;
   } */
}
?>