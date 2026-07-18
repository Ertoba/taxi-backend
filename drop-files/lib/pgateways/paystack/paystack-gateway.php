<?php
define('PAYSTACK_INIT_URL','https://api.paystack.co/transaction/initialize/');
define('PAYSTACK_VERIFY_URL','https://api.paystack.co/transaction/verify/');
define('PAYSTACK_AUTH_URL','https://checkout.paystack.com/');

// Retrieve the request's body
$body = @file_get_contents("php://input");
//$signature = (isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE']) ? $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] : '');




http_response_code(200);
// parse event (which is json string) as object
// Give value to your customer but don't give any output
// Remember that this is a call from Paystack's servers and 
// Your customer is not seeing the response here at all
$paystack_data = json_decode($body,true);




if(!(isset($paystack_data['event']) && $paystack_data['event'] == 'charge.success')){
    exit;
}




$paystack_ref = $paystack_data['data']['reference'];

$response = paystack_transaction_details($paystack_ref);

/* file_put_contents('paystack_dump',print_r($response, true));

exit; */

if(empty($response)){ 
    exit;
}


$response_code = $response['data']['status'];
$response_message = $response['data']['gateway_response'];
$response_amount = ( (int) $response['data']['amount']) / 100;
$response_date = date('Y-m-d H:i:s',strtotime($response['data']['transaction_date']));
$response_currency = $response['data']['currency'];



    
$user_type = 0;
$user_id = 0;
$driver_data = [];
$customer_data = [];
$wallet_amount = 0.00;


$currency_symbol = $response['data']['metadata']['custom_fields'][0]['cur_symbol'];
$currency_code = $response['data']['metadata']['custom_fields'][0]['cur_code'];
$exchange_rate = (float) $response['data']['metadata']['custom_fields'][0]['cur_exchng'];
$user_type = (int) $response['data']['metadata']['custom_fields'][0]['user_type'];
$user_id = (int) $response['data']['metadata']['custom_fields'][0]['user_id'];
$user_currency_amount = ((float) $response['data']['metadata']['custom_fields'][0]['amount']) / 100;

    
//store this transaction data into paystack and transaction table


    

if($response_code == 'success'){

    //generate unique transaction id
    $transaction_id = crypto_string(); //generate a random string as transaction ID
    
    //add transaction to gateways database table
    $query = sprintf('INSERT INTO %stbl_pgateway (transaction_ref,p_transaction_ref,`status`,gateway_resp,amount,`date`,cur,user_id,user_type,gateway) VALUES ("%s","%s","%s","%s","%s","%s","%s","%d","%d","%s")', 
        DB_TBL_PREFIX,
        $transaction_id,
        $paystack_ref,
        $response_code,
        $response_message,
        $response_amount,
        $response_date,
        $response_currency,
        $user_id,
        $user_type,
        DEFAULT_PAYMENT_GATEWAY
    
    );

    $result = mysqli_query($GLOBALS['DB'], $query);


    
    //update wallet

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

        $wallet_amount += (float) $response_amount;

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
        $wallet_amount += (float) $response_amount;

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
    
    



}
    
























//Verify Paystack Transaction

function paystack_transaction_details($t_ref){
    $p_sk = DEFAULT_PAYMENT_GATEWAY['paystack']['paystack-secret-key'];
    $curl = curl_init(PAYSTACK_VERIFY_URL . rawurlencode($t_ref));
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json","authorization: Bearer {$p_sk}","cache-control: no-cache"));
	curl_setopt($curl, CURLOPT_HTTPGET, true);
	//curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
	$json_response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
  
	$response = json_decode($json_response, true);
	if(json_last_error())return null;
	if($response['status'] == true){
		return $response;
	}else{
		return null;
	}
}

?>