<?php

// Retrieve the request's body


$body = @file_get_contents("php://input");


//file_put_contents('phonepe_gateway_dump',print_r($body, true)); 

$p_gateway_res_arr = json_decode($body,true);

if(json_last_error()){
    exit;
}

$gateway_data = json_decode(base64_decode($p_gateway_res_arr['response']),true);

if(json_last_error()){
    exit;
}



if(!(isset($gateway_data['success']) && $gateway_data['success'] == true)){
    exit;
}

$gateway_transaction_id = $gateway_data['data']['transactionId'];

$transaction_ref = $gateway_data['data']['merchantTransactionId'];
$transaction_data = [];

//verify transaction by checking DB
$query = sprintf('SELECT * FROM %stbl_pgateway WHERE transaction_ref = "%s"', DB_TBL_PREFIX, $transaction_ref);

if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){
        $transaction_data = mysqli_fetch_assoc($result);
    }else{
        exit;
    }
}else{
    exit;
}

$response_code = 'success';
$response_message = $gateway_data['code'];
$response_amount = $transaction_data['amount'];

$metadata = json_decode($transaction_data['memo'], true);
    
$user_type = 0;
$user_id = 0;
$driver_data = [];
$customer_data = [];
$wallet_amount = 0.00;


$currency_symbol = $metadata['cur_symbol'];
$currency_code = $transaction_data['cur'];
$exchange_rate = (float) $metadata['cur_exchng'];
$user_type = (int) $metadata['user_type'];
$user_id = (int) $metadata['user_id'];
$user_currency_amount = ((float) $metadata['amount']) / 100;

    
http_response_code(200);
    

if($response_code == 'success'){

        
    //update transaction in gateways database table
    $query = sprintf('UPDATE %stbl_pgateway SET  p_transaction_ref = "%s", `status` = "%s", gateway_resp = "%s" WHERE transaction_ref = "%s"', 
    
        DB_TBL_PREFIX,
        $gateway_transaction_id,
        $response_code,
        $response_message,
        $transaction_ref
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
        $transaction_ref,
        $user_currency_amount,
        $wallet_amount,
        $user_id,
        1,
        mysqli_real_escape_string($GLOBALS['DB'],$metadata['memo']), 
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
        
        $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
        '("%s","%s","%s","%s","%s","%s","%d","%d","%s","%d","%s")', 
        DB_TBL_PREFIX,
        $currency_symbol,
        $exchange_rate,
        $currency_code,
        $transaction_ref,
        $user_currency_amount,
        $wallet_amount,
        $user_id,
        0,
        mysqli_real_escape_string($GLOBALS['DB'],$metadata['memo']), 
        0,
        gmdate('Y-m-d H:i:s', time())

        );

        $result = mysqli_query($GLOBALS['DB'], $query);


    }
    
    



}
    






?>