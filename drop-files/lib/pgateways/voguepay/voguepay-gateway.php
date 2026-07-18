<?php

$transaction_details = array();

$merchant_id = MERCHANT_ID;


if(isset($_POST['transaction_id'])){

    //get the full transaction details as an json from voguepay
    $url = 'https://voguepay.com/?v_transaction_id='.$_POST['transaction_id'].'&type=json'.'&demo=true';
	$curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPGET, true);
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
    //curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
    $json = curl_exec($curl);
	

	//create new array to store our transaction detail
	$transaction = json_decode($json, true);

}else{

    return 0;
    exit;
}


##Check if transaction ID has been submitted
	if($transaction['merchant_id'] != $merchant_id){
        /* return 0;
        exit; */
	}

    $user_type = 0;
    $user_id = 0;
    $driver_data = [];
    $franchise_name = '';
    $wallet_amount = 0.00;
    $details = [];

    $details = explode("-",$transaction['merchant_ref']); //get transaction details passed from app
    $currency_symbol = $details[0];
    $currency_code = $details[1];
    $exchange_rate = (float) $details[2];
    $user_type = (int) $details[3];
    $user_id = (int) $details[4];

    if($user_type){ //driver

        $query = sprintf('SELECT * FROM %1$stbl_drivers 
        LEFT JOIN %1$stbl_franchise ON %1$stbl_franchise.id = %1$stbl_drivers.franchise_id 
        WHERE %1$stbl_drivers.driver_id = "%2$d"', DB_TBL_PREFIX, $user_id);

        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){

            $driver_data = mysqli_fetch_assoc($result);

            }
        }

        $franchise_name = !empty($driver_data['franchise_name']) ? $driver_data['franchise_name'] : '';
        $wallet_amount =  !empty($driver_data['wallet_amount']) ? $driver_data['wallet_amount'] : 0.00;

    }



	//Add this transaction to the vogue pay database table
	$query = sprintf('INSERT INTO %stbl_vogue_pay (merchant_id,v_transaction_id,email,total,total_paid_by_buyer,total_credited_to_merchant,extra_charges_by_merchant,transaction_ref,memo,status,`date`,referrer,method,fund_maturity,cur,user_type,user_id,franchise_name) VALUES'.
	'("%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%d","%d","%s")', 
	DB_TBL_PREFIX, 
	$transaction['merchant_id'],
	$transaction['transaction_id'],
	$transaction['email'],
	$transaction['total'],
	$transaction['total_paid_by_buyer'],
	$transaction['total_credited_to_merchant'],
	$transaction['extra_charges_by_merchant'],
	$transaction['merchant_ref'],
	$transaction['memo'],
	$transaction['status'],
	$transaction['date'],
	mysqli_real_escape_string($GLOBALS['DB'], $transaction['referrer']),
	$transaction['method'],
	$transaction['fund_maturity'],
    $transaction['cur'],
    $user_type,
    $user_id,
    $franchise_name

	);

    $result = mysqli_query($GLOBALS['DB'], $query);
    
    $insert_id = mysqli_insert_id ( $GLOBALS['DB']);
    $vouguepay_rec_id = !empty($insert_id) ? $insert_id : NULL;
    

	

	if($transaction['status'] == 'Approved'){
        
        //update user wallet
        if($user_type){ //driver
            
            //convert the amount paid by user to the default currency. wallet amount is based on the default currency set
            $converted_amount_paid = (float) $transaction['total_credited_to_merchant'] / $exchange_rate;
            $wallet_amount += (float) $converted_amount_paid;

            $query = sprintf('UPDATE %stbl_drivers SET wallet_amount = %f WHERE driver_id = "%d"', DB_TBL_PREFIX, $wallet_amount,$user_id);
            $result = mysqli_query($GLOBALS['DB'], $query);

            $query = sprintf('UPDATE %stbl_vogue_pay SET wallet_balance = %f WHERE v_transaction_id = "%s"', DB_TBL_PREFIX, $wallet_amount,$transaction['transaction_id']);
            $result = mysqli_query($GLOBALS['DB'], $query);

            
            //Add this transaction to wallet transactions database table
            $transaction_id = crypto_string();
            $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,transaction_id,amount,wallet_balance,user_id,voguepay_id,user_type,`desc`,`type`,transaction_date) VALUES'.
            '("%s","%s","%s","%s","%s","%s","%d","%d","%d","%s","%d","%s")', 
            DB_TBL_PREFIX,
            $currency_symbol,
            $exchange_rate,
            $currency_code,
            $transaction_id,
            $transaction['total_credited_to_merchant'],
            $wallet_amount,
            $user_id,
            $vouguepay_rec_id,
            1,
            "Wallet funding through app", 
            0,
            gmdate('Y-m-d H:i:s', time())

            );

            $result = mysqli_query($GLOBALS['DB'], $query);


        }else{ //customer

            $customer_data = [];

            $query = sprintf('SELECT * FROM %1$stbl_users WHERE %1$stbl_users.user_id = "%2$d"', DB_TBL_PREFIX, $user_id);

            if($result = mysqli_query($GLOBALS['DB'], $query)){
                if(mysqli_num_rows($result)){

                $customer_data = mysqli_fetch_assoc($result);

                }
            }


            $wallet_amount =  !empty($customer_data['wallet_amount']) ? $customer_data['wallet_amount'] : 0.00;

            
            //convert the amount paid by user to the default currency. wallet amount is based on the default currency set
            $converted_amount_paid = (float) $transaction['total_credited_to_merchant'] / $exchange_rate;
            $wallet_amount += (float) $converted_amount_paid;


            $query = sprintf('UPDATE %stbl_users SET wallet_amount = %f WHERE user_id = "%d"', DB_TBL_PREFIX, $wallet_amount,$user_id);
            $result = mysqli_query($GLOBALS['DB'], $query);
            
            
            $query = sprintf('UPDATE %stbl_vogue_pay SET wallet_balance = %f WHERE v_transaction_id = "%s"', DB_TBL_PREFIX, $wallet_amount,$transaction['transaction_id']);
            $result = mysqli_query($GLOBALS['DB'], $query);

            
            //Add this transaction to wallet transactions database table
            $transaction_id = crypto_string();
            $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,transaction_id,amount,wallet_balance,user_id,voguepay_id,user_type,`desc`,`type`,transaction_date) VALUES'.
            '("%s","%s","%s","%s","%s","%s","%d","%d","%d","%s","%d","%s")', 
            DB_TBL_PREFIX,
            $currency_symbol,
            $exchange_rate,
            $currency_code,
            $transaction_id,
            $transaction['total_credited_to_merchant'],
            $wallet_amount,
            $user_id,
            $vouguepay_rec_id,
            0,
            "Wallet funding through app", 
            0,
            gmdate('Y-m-d H:i:s', time())

            );

            $result = mysqli_query($GLOBALS['DB'], $query);


        }
        
        

    
    
    }



?>