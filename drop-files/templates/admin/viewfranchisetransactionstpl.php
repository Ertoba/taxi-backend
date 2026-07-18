<?php
$franchise_transaction_data = [];
$query_modifier  = ' = 2 AND ' . DB_TBL_PREFIX . 'tbl_wallet_transactions.user_id = ' . $id;
$number_of_transactions_data = 0;

//get number of transactions
$query = sprintf('SELECT COUNT(*) FROM %1$stbl_wallet_transactions WHERE %1$stbl_wallet_transactions.user_type %2$s', DB_TBL_PREFIX, $query_modifier);

if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){
        $row = mysqli_fetch_assoc($result);        
        $number_of_transactions_data = $row['COUNT(*)'];
    }
}

//calculate pages
if(isset($_GET['page']) && (isset($_GET['tab']) && $_GET['tab'] == "ftransactions")){
    $page_number = (int) $_GET['page'];
}else{
    $page_number = 1;
}
    
$pages = ceil($number_of_transactions_data / ITEMS_PER_PAGE) ;
if($page_number > $pages)$page_number = 1; 
if($page_number < 0)$page_number = 1; 
$offset = ($page_number - 1) * ITEMS_PER_PAGE;

//get transactions data
$query = sprintf('SELECT * FROM %1$stbl_wallet_transactions WHERE %1$stbl_wallet_transactions.user_type %2$s ORDER BY %1$stbl_wallet_transactions.transaction_date DESC LIMIT %3$d, %4$d', DB_TBL_PREFIX, $query_modifier, $offset, ITEMS_PER_PAGE);

if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){
        while($row = mysqli_fetch_assoc($result)){
            $franchise_transaction_data[] = $row;
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

            <br />
            <div> <!--pages-->
            
                <?php
                    
                    
                    if(!empty($pages)){

                        $url = $_SERVER['REQUEST_URI'];
                        $url_parts = parse_url($url);
                        if(isset($url_parts['query'])){
                            parse_str($url_parts['query'], $params);
                        }
                        $params['tab'] = 'ftransactions';     // Overwrite if exists
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
                        <th>Transaction ID</th>
                        <th>Amount</th>
                        <th>Wallet Balance</th>
                        <th>Booking ID</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Date Created</th>                        
                    </tr>
                </thead>
                <tbody>
                    <?php  
                    
                    
                    $count = 1 + (($page_number - 1) * ITEMS_PER_PAGE);
                    $default_currency_symbol = !empty($_SESSION['default_currency']) ? $_SESSION['default_currency']['symbol'] : "₦";
                    
                    foreach($franchise_transaction_data as $franchisetransactiondata){
                        $transaction_type = '';
                        $booking_id = !empty($franchisetransactiondata['book_id']) ? "<a href='view-booking.php?bkid={$franchisetransactiondata['book_id']}'>" . str_pad($franchisetransactiondata['book_id'] , 5, '0', STR_PAD_LEFT) . "</a>" : 'N/A';
                        switch($franchisetransactiondata['type']){
                            case 0:
                            $transaction_type = "<i class='fa fa-circle' style='color:green;'></i> Credit (Self)";
                            break;

                            case 1:
                            $transaction_type = "<i class='fa fa-circle' style='color:green;'></i> Credit (Admin)";
                            break;

                            case 2:
                            $transaction_type = "<i class='fa fa-circle' style='color:green;'></i> Credit ";
                            break;

                            case 3:
                            $transaction_type = "<i class='fa fa-circle' style='color:red;'></i> Debit";
                            break;
                        }
                                                    
                        echo "<tr><td>". $count++ . "</td><td>" . strtoupper($franchisetransactiondata['transaction_id']) . "</td><td>" . $franchisetransactiondata['cur_symbol']. $franchisetransactiondata['amount']. "</td><td>" . $default_currency_symbol . $franchisetransactiondata['wallet_balance'] ."</td><td>". $booking_id ."</td><td>". $transaction_type . "</td><td>" .$franchisetransactiondata['desc'] . "</td><td>" . date('l, M j, Y H:i:s',strtotime($franchisetransactiondata['transaction_date'].' UTC')) . "</td><td></tr>";
                    }
                    
                    ?>
                </tbody>
                </table>
            </div>
                                  
            <?php if(empty($franchise_transaction_data)){ echo "<h1 style='text-align:center;'>Nothing to Show. No Transaction Data.</h1>";} ?>
                        
            
                            
        </div><!-- /.box-body -->
    </div>


