<?php

	if(isset($_GET['redir-referrer'])){
		$token = base64_decode($_GET['redir-referrer']);
		ob_start();
		?>
		<!doctype html>
		<html lang="tr">
			<head>
				<meta charset="UTF-8">
				<title>Payment Form</title>
				<script src='https://www.paytr.com/js/iframeResizer.min.js'></script>
			</head>
			<body>
				<br>
				<br>
				<div style="width: 100%;margin: 0 auto;display: table;">			
					<iframe src='https://www.paytr.com/odeme/guvenli/<?php echo $token ?>' id='paytriframe' width='100%' height='800px'  scrolling='no' frameBorder='0'><p>Browser unable to load iFrame</p></iframe>
					
				</div>
				<br>
				<br>
				<script>iFrameResize({},'#paytriframe');</script>
			</body>
		</html>
		<?php
		$html_form = ob_get_clean();
		echo $html_form;
		exit;
	}

$post = $_POST;
//file_put_contents('paytr_dump',print_r($post, true));

$merchant_reference = $post['merchant_oid'];
$status = $post['status'];
$amount_paid_local = $post['total_amount'] / 100;

$merchant_key 	= DEFAULT_PAYMENT_GATEWAY['paytr']['paytr-merchant-key'];
$merchant_salt	= DEFAULT_PAYMENT_GATEWAY['paytr']['paytr-merchant-salt'];

$hash = base64_encode( hash_hmac('sha256', $merchant_reference.$merchant_salt.$post['status'].$post['total_amount'], $merchant_key, true) );

if( $hash != $post['hash'] )die('PAYTR notification failed: bad hash');




echo "OK";


//UPDATE YOUR DB TABLE WITH NEW STATUS FOR TRANSACTION WITH PAYTR DATA
$transaction_data = [];

$query = sprintf('SELECT * FROM %stbl_pgateway WHERE transaction_ref = "%s"', DB_TBL_PREFIX, $merchant_reference);
if($result = mysqli_query($GLOBALS['DB'], $query)){
	if(mysqli_num_rows($result)){

		$transaction_data = mysqli_fetch_assoc($result);

	}
}



if(empty($transaction_data))exit;

if($transaction_data['status'] == "success")exit;

if($status != "success"){
	//update DB status
	$query = sprintf('UPDATE %stbl_pgateway SET `status` = "%s" WHERE transaction_ref = "%s"', DB_TBL_PREFIX, $status, $merchant_reference);
	$result = mysqli_query($GLOBALS['DB'], $query);	
	//notify user
	

	$query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
		("%d","%d","%s",3,"%s")', 
		DB_TBL_PREFIX,
		$transaction_data['user_id'],
		$transaction_data['user_type'],
		"Your last transaction with ID {$merchant_reference} was unsuccessful. Payment gateway reported - {$status}",
		gmdate('Y-m-d H:i:s', time()) 
	);
	$result = mysqli_query($GLOBALS['DB'], $query);
	
	echo "OK";
	exit;
}

echo "OK";
	

//all was successful. Provide value

//update DB status
$query = sprintf('UPDATE %stbl_pgateway SET `status` = "%s" WHERE transaction_ref = "%s"', DB_TBL_PREFIX, $status, $merchant_reference);
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
$transaction_id = $merchant_reference;

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

	$wallet_amount += (float) $amount_paid_local;

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
	$amount_paid_local,
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
	$wallet_amount += (float) $amount_paid_local;

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
	$amount_paid_local,
	$wallet_amount,
	$user_id,
	0,
	"Wallet funding through app", 
	0,
	gmdate('Y-m-d H:i:s', time())

	);

	$result = mysqli_query($GLOBALS['DB'], $query);


}



   

?>