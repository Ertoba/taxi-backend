<?php

// Retrieve the request's body
$body = @file_get_contents("php://input");

//$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];


http_response_code(200);
// parse event (which is json string) as object
// Give value to your customer but don't give any output

$data_arr = json_decode($body,true);
if(!(isset($data_arr['data']) && isset($data_arr['data']['status']) && $data_arr['data']['status'] == 'successful')){
    exit;
}

$gateway_data = $data_arr['data'];


/* file_put_contents('stripe_dump',print_r($body, true));

exit; */


if(!(isset($gateway_data['status']) && $gateway_data['status'] == 'successful')){
    exit;
}



$transaction_ref = $gateway_data['tx_ref'];
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

if(!empty($transaction_data['status'])){ //already processed
    exit;
}

$response_code = $gateway_data['status'];
$response_message = $gateway_data['status'];
$response_amount = $transaction_data['amount'];
$response_pg_ref = $gateway_data['flw_ref'];

$extra_details = json_decode($transaction_data['memo'],true);

if(json_last_error()){
    exit;
}


    
$user_type = 0;
$user_id = 0;
$driver_data = [];
$customer_data = [];
$wallet_amount = 0.00;


$currency_symbol = $extra_details['cur_symbol'];
$currency_code = $extra_details['cur_code'];
$exchange_rate = (float) $extra_details['cur_exchng'];
$user_type = (int) $extra_details['user_type'];
$user_id = (int) $extra_details['user_id'];
$user_currency_amount = ((float) $extra_details['amount']);

    

    

if($response_code == 'successful'){

        
    //update transaction in gateways database table
    $query = sprintf('UPDATE %stbl_pgateway SET  p_transaction_ref = "%s", `status` = "%s", gateway_resp = "%s" WHERE transaction_ref = "%s"', 
    
        DB_TBL_PREFIX,
        $response_pg_ref,
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
        mysqli_real_escape_string($GLOBALS['DB'],$gateway_data['data']['object']['metadata']['memo']), 
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
        mysqli_real_escape_string($GLOBALS['DB'],$gateway_data['data']['object']['metadata']['memo']), 
        0,
        gmdate('Y-m-d H:i:s', time())

        );

        $result = mysqli_query($GLOBALS['DB'], $query);


    }
    
    



}
    






?>