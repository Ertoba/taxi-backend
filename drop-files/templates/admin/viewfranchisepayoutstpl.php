<?php
$franchise_payouts_data = [];
$query_modifier  = ' = 1 AND ' . DB_TBL_PREFIX . 'tbl_wallet_withdrawal.person_id = ' . $id;
$number_of_payouts_data = 0;

if(isset($_POST['requestpayout'])){

    $req_payout_amt = isset($_POST['payout-amount']) ? (float) $_POST['payout-amount'] : 0.00;

    if(empty($req_payout_amt) || $req_payout_amt < 0){
        $cache_prevent = RAND();
        echo"<script>
                setTimeout(function(){ 
                        jQuery( function(){
                        swal({
                            title: '<h1>Error</h1>'".',
                text:"<i style=\'color:red;\' class=\'fa fa-circle-o\'></i> Invalid payout amount requested.",'.
                "imageUrl: '../img/info_.gif?a=" . $cache_prevent . "',
                html:true,
                        });
                        });
                        },500); 
                        
            </script>";
    }else{
        $payout_req = franchiseRequestPayout($req_payout_amt, $id);
        if(isset($payout_req['error'])){
            $cache_prevent = RAND();
            echo"<script>
                    setTimeout(function(){ 
                            jQuery( function(){
                            swal({
                                title: '<h1>Error</h1>'".',
                    text:"<i style=\'color:red;\' class=\'fa fa-circle-o\'></i> '.$payout_req['error'] .'",'.
                    "imageUrl: '../img/info_.gif?a=" . $cache_prevent . "',
                    html:true,
                            });
                            });
                            },500); 
                            
                </script>";
        }elseif(isset($payout_req['success'])){
            $cache_prevent = RAND();
            echo"<script>
                    setTimeout(function(){ 
                            jQuery( function(){
                            swal({
                                title: '<h1>Success</h1>'".',
                    text:"<i style=\'color:green;\' class=\'fa fa-circle-o\'></i> Your payout request is successful and awaiting approval. It will be processed shortly.",'.
                    "imageUrl: '../img/success_.gif?a=" . $cache_prevent . "',
                    html:true,
                            });
                            });
                            },500); 
                            
                </script>";
        }
    }

    

}

//get number of payouts
$query = sprintf('SELECT COUNT(*) FROM %1$stbl_wallet_withdrawal WHERE %1$stbl_wallet_withdrawal.user_type %2$s', DB_TBL_PREFIX, $query_modifier);

if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){
        $row = mysqli_fetch_assoc($result);        
        $number_of_payouts_data = $row['COUNT(*)'];
    }
}

//calculate pages
if(isset($_GET['page']) && (isset($_GET['tab']) && $_GET['tab'] == "fpayouts")){
    $page_number = (int) $_GET['page'];
}else{
    $page_number = 1;
}
    
$pages = ceil($number_of_payouts_data / ITEMS_PER_PAGE) ;
if($page_number > $pages)$page_number = 1; 
if($page_number < 0)$page_number = 1; 
$offset = ($page_number - 1) * ITEMS_PER_PAGE;

//get transactions data
$query = sprintf('SELECT * FROM %1$stbl_wallet_withdrawal WHERE %1$stbl_wallet_withdrawal.user_type %2$s ORDER BY %1$stbl_wallet_withdrawal.date_requested DESC LIMIT %3$d, %4$d', DB_TBL_PREFIX, $query_modifier, $offset, ITEMS_PER_PAGE);

if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){
        while($row = mysqli_fetch_assoc($result)){
            $franchise_payouts_data[$row['id']] = $row;
        }
        
    }
}



if(isset($_GET['action']) && ($_SESSION['account_type'] == 3 || $_SESSION['account_type'] == 5)){
    if($_GET['action'] == 'approve'){
        $withdrawal_id = isset($_GET['wid']) ? (int) $_GET['wid'] : 0;
        if(!empty($withdrawal_id) && isset($franchise_payouts_data[$withdrawal_id])){//verify this withdrawal status on db first
            if($franchise_payouts_data[$withdrawal_id]['request_status'] != 0){
                $cache_prevent = RAND();
                $msgs = "<p style='text-align:left;'><i style='color:red;' class='fa fa-circle-o'></i> Processing withdrawal request failed as request has already been processed.</p>";
                echo"<script>
                setTimeout(function(){ 
                        jQuery( function(){
                        swal({
                            title: '<h1>Error</h1>'".',
                text:"'.$msgs .'",'.
                "imageUrl: '../img/info_.gif?a=" . $cache_prevent . "',
                html:true,
                        });
                        });
                        },500); 
                        
                        </script>";
            }else{

                $franchise_gateway_payment_status = 1; //returned value from function that pays the franchise through the gateway
        
                if($franchise_gateway_payment_status){ //if gateway payment of driver was successful
                    
                    //update withdrawal table
                    $query = sprintf('UPDATE %stbl_wallet_withdrawal SET request_status = %d, date_settled = "%s" WHERE id = %d',DB_TBL_PREFIX,2,gmdate('Y-m-d H:i:s', time()) ,$withdrawal_id);
                    $result = mysqli_query($GLOBALS['DB'], $query);

                    $franchise_payouts_data[$withdrawal_id]['request_status'] = 2; //update already read db data record to reflect success

                    $cache_prevent = RAND();
                    $msgs = "<p style='text-align:left;'><i style='color:green;' class='fa fa-circle-o'></i> Withdrawal request has been approved and money transfered to driver account.</p>";
                    echo"<script>
                    setTimeout(function(){ 
                            jQuery( function(){
                            swal({
                                title: '<h1>Success</h1>'".',
                    text:"'.$msgs .'",'.
                    "imageUrl: '../img/success_.gif?a=" . $cache_prevent . "',
                    html:true,
                            });
                            });
                            },500); 
                            
                            </script>";
                }else{
                    $cache_prevent = RAND();
                    $msgs = "<p style='text-align:left;'><i style='color:red;' class='fa fa-circle-o'></i> Processing withdrawal request failed as payment gateway returned error.</p>";
                    echo"<script>
                    setTimeout(function(){ 
                            jQuery( function(){
                            swal({
                                title: '<h1>Error</h1>'".',
                    text:"'.$msgs .'",'.
                    "imageUrl: '../img/info_.gif?a=" . $cache_prevent . "',
                    html:true,
                            });
                            });
                            },500); 
                            
                            </script>";
                }
            }
                

        }else{

            $cache_prevent = RAND();
            $msgs = "<p style='text-align:left;'><i style='color:red;' class='fa fa-circle-o'></i> Processing withdrawal request failed. Invalid record</p>";
            echo"<script>
            setTimeout(function(){ 
                    jQuery( function(){
                    swal({
                        title: '<h1>Error</h1>'".',
            text:"'.$msgs .'",'.
            "imageUrl: '../img/info_.gif?a=" . $cache_prevent . "',
            html:true,
                    });
                    });
                    },500); 
                    
                    </script>";
            
        }        
        

        
    }elseif($_GET['action'] == 'reject' && ($_SESSION['account_type'] == 3 || $_SESSION['account_type'] == 5)){
        $withdrawal_id = isset($_GET['wid']) ? (int) $_GET['wid'] : 0;
        if(!empty($withdrawal_id) && isset($franchise_payouts_data[$withdrawal_id])){//verify this withdrawal status on db first
            if($franchise_payouts_data[$withdrawal_id]['request_status'] != 0){
                $cache_prevent = RAND();
                $msgs = "<p style='text-align:left;'><i style='color:red;' class='fa fa-circle-o'></i> Processing withdrawal request failed as request has already been processed.</p>";
                echo"<script>
                setTimeout(function(){ 
                        jQuery( function(){
                        swal({
                            title: '<h1>Error</h1>'".',
                text:"'.$msgs .'",'.
                "imageUrl: '../img/info_.gif?a=" . $cache_prevent . "',
                html:true,
                        });
                        });
                        },500); 
                        
                        </script>";
            }else{

                
                    
                    //update withdrawal table
                    $query = sprintf('UPDATE %stbl_wallet_withdrawal SET request_status = %d,date_settled = "%s" WHERE id = %d',DB_TBL_PREFIX,1,gmdate('Y-m-d H:i:s', time()),$withdrawal_id);
                    $result = mysqli_query($GLOBALS['DB'], $query);

                    //convert amount to be withdrawn to default local currency
                    $withdrawal_amount_converted = $franchise_payouts_data[$withdrawal_id]['withdrawal_amount'] / $franchise_payouts_data[$withdrawal_id]['cur_exchng_rate'];

                    //update franchise wallet by reversing initial debit on franchise wthrawal request action
                    $query = sprintf('UPDATE %stbl_franchise SET fwallet_amount = fwallet_amount + "%s" WHERE id = %d', DB_TBL_PREFIX, $withdrawal_amount_converted, $id);
                    $result = mysqli_query($GLOBALS['DB'], $query);


                    
                    //add record to transaction table as wallet funding from admin
                    $transaction_id = crypto_string();
                    $query = sprintf('INSERT INTO %stbl_wallet_transactions (transaction_id,amount,cur_symbol,cur_exchng_rate,cur_code,wallet_balance,`user_id`,user_type,`desc`,`type`,transaction_date) VALUES 
                    ("%s","%f","%s","%s","%s","%f","%d","%d","%s","%d","%s")',
                    DB_TBL_PREFIX,
                    $transaction_id,
                    $franchise_payouts_data[$withdrawal_id]['withdrawal_amount'],
                    $franchise_payouts_data[$withdrawal_id]['cur_symbol'],
                    $franchise_payouts_data[$withdrawal_id]['cur_exchng_rate'],
                    $franchise_payouts_data[$withdrawal_id]['cur_code'],
                    $franchise_data['fwallet_amount'] + $withdrawal_amount_converted,
                    $id,
                    2,
                    'Withdrawal request reversal',
                    2,
                    gmdate('Y-m-d H:i:s', time()) 
                    );
                    $result = mysqli_query($GLOBALS['DB'], $query);


                    $franchise_payouts_data[$withdrawal_id]['request_status'] = 1; //update already ready db data record to reflect success

                    $cache_prevent = RAND();
                    $msgs = "<p style='text-align:left;'><i style='color:green;' class='fa fa-circle-o'></i> Withdrawal request rejected with initial transactions reversed.</p>";
                    echo"<script>
                    setTimeout(function(){ 
                            jQuery( function(){
                            swal({
                                title: '<h1>Success</h1>'".',
                    text:"'.$msgs .'",'.
                    "imageUrl: '../img/success_.gif?a=" . $cache_prevent . "',
                    html:true,
                            });
                            });
                            },500); 
                            
                            </script>";
                
            }
                

        }else{

            $cache_prevent = RAND();
            $msgs = "<p style='text-align:left;'><i style='color:red;' class='fa fa-circle-o'></i> Processing withdrawal request failed. Invalid record</p>";
            echo"<script>
            setTimeout(function(){ 
                    jQuery( function(){
                    swal({
                        title: '<h1>Error</h1>'".',
            text:"'.$msgs .'",'.
            "imageUrl: '../img/info_.gif?a=" . $cache_prevent . "',
            html:true,
                    });
                    });
                    },500); 
                    
                    </script>";
            
        }        
        

        
    }

}


?>


<div class="box box-success">
        <!-- <div class="box-header with-border">
        <h3 class="box-title">Options</h3>
        
        </div> -->
        <!-- /.box-header -->
        <div class="box-body">

            <form  enctype="multipart/form-data" id="payout-request-form" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']) . "?id={$id}&tab=fpayouts"; ?>" method="post" >
                
                <div class="form-group">                                
                    <div class="col-sm-4">
                        <label for="payout-amount">Request Payout</label>
                        <input  style="display: inline-block; width: calc(100% - 50px);" type="number" class="form-control" id="payout-amount" placeholder="Enter Amount" name="payout-amount" value="" >
                        <button type="submit" class="btn btn-primary" value="1" id="requestpayout" name="requestpayout" >OK</button> 
                    </div>                  
                </div>
                
            </form>


            <br />
            <div> <!--pages-->
            
                <?php
                    
                    
                    if(!empty($pages)){
                        $url = $_SERVER['REQUEST_URI'];
                        $url_parts = parse_url($url);
                        if(isset($url_parts['query'])){
                            parse_str($url_parts['query'], $params);
                        }
                        $params['tab'] = 'fpayouts';     // Overwrite if exists
                        echo "Pages: ";

                        if($page_number > 1){
                            
                            $params['page'] = 1;     // Overwrite if exists
                            $url_parts['query'] = http_build_query($params);
                            echo "<a class='btn' href='".htmlspecialchars($_SERVER['SCRIPT_NAME']) . "?" . $url_parts['query']."'> << </a>";

                            $prev_page = $page_number - 1;
                            $params['page'] = $prev_page;     // Overwrite if exists
                            $url_parts['query'] = http_build_query($params);
                            echo "<a class='btn' href='".htmlspecialchars($_SERVER['SCRIPT_NAME']) . "?" . $url_parts['query']."'> < </a>";

                        }
                        
                        // range of num links to show
                        $range = 2;

                        // display links to 'range of pages' around 'current page'
                        $initial_num = $page_number - $range;
                        $condition_limit_num = ($page_number + $range)  + 1;

                        
                        for($i = $initial_num;$i < $condition_limit_num + 1; $i++){

                            // be sure '$i is greater than 0' AND 'less than or equal to the $total_pages'
                            if (($i > 0) && ($i <= $pages)) {

                                if($i == $page_number){
                                    echo "<a class='disabled btn btn-default' href=''>".$i."</a>";
                                }else{
                                    
                                    $params['page'] = $i;     // Overwrite if exists
                                    $url_parts['query'] = http_build_query($params);                                                
                                    echo "<a class='btn' href='".htmlspecialchars($_SERVER['SCRIPT_NAME']) . "?" . $url_parts['query']."'>".$i."</a>";
                                        
                                } 

                            }
                            
                             
                            
                        }

                        if($page_number < $pages){

                            $next_page = $page_number + 1;
                            $params['page'] = $next_page;     // Overwrite if exists
                            $url_parts['query'] = http_build_query($params);
                            echo "<a class='btn' href='".htmlspecialchars($_SERVER['SCRIPT_NAME']) . '?' . $url_parts['query']."'> > </a>";
                            
                            $params['page'] = $pages;     // Overwrite if exists
                            $url_parts['query'] = http_build_query($params);
                            echo "<a class='btn' href='".htmlspecialchars($_SERVER['SCRIPT_NAME']) . '?' . $url_parts['query']."'> >> </a>";

                            

                        }


                    }
                ?>
            </div><!--/pages-->
            <br />
            <div class="table-responsive">
                <table class='table table-bordered table-striped'>
                <thead>
                    <tr>
                    <th>#</th>    
                        <th>Amount</th>
                        <th>Wallet Amount (Old)</th>
                        <th>Wallet Balance (New)</th>                       
                        <th>Status</th>
                        <th>Date Requested</th>
                        <th>Date Settled</th>  
                        <th>Actions</th>                    
                    </tr>
                </thead>
                <tbody>
                    <?php  
                    
                    
                    $count = 1 + (($page_number - 1) * ITEMS_PER_PAGE);
                    $default_currency_symbol = !empty($_SESSION['default_currency']) ? $_SESSION['default_currency']['symbol'] : "₦";
                    
                    foreach($franchise_payouts_data as $franchisepayoutsdata){
                        $payout_status = '';
                        $payout_actions = '';
                        $date_settled = !empty($franchisepayoutsdata['date_settled']) ? date('l, M j, Y H:i:s',strtotime($franchisepayoutsdata['date_settled'].' UTC')) : "---";
                        $approve_request = $franchisepayoutsdata['request_status'] == 0 && $_SESSION['account_type'] == 3 ? "<a id='approve-btn' href='#' data-url='{$_SERVER['SCRIPT_NAME']}?id={$id}&tab=fpayouts&action=approve&wid={$franchisepayoutsdata['id']}' data-msg='This action will approve this withdrawal request and money transfered to the driver account' class='btn btn-xs btn-success confirm-action'>Approve</a>" : "";
                        $decline_request = $franchisepayoutsdata['request_status'] == 0 && $_SESSION['account_type'] == 3 ? "<a id='reject-btn' href='#' data-url='{$_SERVER['SCRIPT_NAME']}?id={$id}&tab=fpayouts&action=reject&wid={$franchisepayoutsdata['id']}' data-msg='This action will reject this withdrawal request and driver wallet debit will be reversed' class='btn btn-xs btn-danger confirm-action'>Reject</a>" : "";
                        
                        if($_SESSION['account_type'] == 4){
                            $approve_request = "";
                            $decline_request = "";
                        }

                        switch($franchisepayoutsdata['request_status']){
                            case 0:
                            $payout_status = "<i class='fa fa-circle' style='color:purple;'></i> Pending";
                            break;

                            case 1:
                            $payout_status = "<i class='fa fa-circle' style='color:red;'></i> Declined";
                            break;

                            case 2:
                            $payout_status = "<i class='fa fa-circle' style='color:green;'></i> Settled";
                            break;

                        }


                                                    
                        echo "<tr><td>". $count++ . "</td><td>" . $default_currency_symbol. $franchisepayoutsdata['withdrawal_amount'] . "</td><td>" . $franchisepayoutsdata['cur_symbol']. $franchisepayoutsdata['wallet_amount']. "</td><td>" . $default_currency_symbol . $franchisepayoutsdata['wallet_balance'] ."</td><td>". $payout_status ."</td><td>". date('l, M j, Y H:i:s',strtotime($franchisepayoutsdata['date_requested'].' UTC')) . "</td><td>" . $date_settled . "</td><td>{$approve_request} {$decline_request}</td></tr>";
                    }
                    
                    ?>
                </tbody>
                </table>
            </div>
                                  
            <?php if(empty($franchise_payouts_data)){ echo "<h1 style='text-align:center;'>Nothing to Show. No Payouts Data.</h1>";} ?>
                        
            
                            
        </div><!-- /.box-body -->
    </div>


