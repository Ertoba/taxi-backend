<?php
$user_bookings_data = [];
$query_modifier  = " = {$id}";
$number_of_bookings = 0;


//get number of transactions
$query = sprintf('SELECT COUNT(*) FROM %1$stbl_bookings WHERE %1$stbl_bookings.user_id %2$s', DB_TBL_PREFIX, $query_modifier);

if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){
        $row = mysqli_fetch_assoc($result);        
        $number_of_bookings = $row['COUNT(*)'];
    }
}

//calculate pages
if(isset($_GET['page']) && (isset($_GET['tab']) && $_GET['tab'] == "sbookings")){
    $page_number = (int) $_GET['page'];
}else{
    $page_number = 1;
}
    
$pages = ceil($number_of_bookings / ITEMS_PER_PAGE) ;
if($page_number > $pages)$page_number = 1; 
if($page_number < 0)$page_number = 1; 
$offset = ($page_number - 1) * ITEMS_PER_PAGE;

//get transactions data
$query = sprintf('SELECT *,%1$stbl_bookings.id AS booking_id FROM %1$stbl_bookings 
LEFT JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
LEFT JOIN %1$stbl_driver_location ON %1$stbl_driver_location.driver_id = %1$stbl_bookings.driver_id
WHERE %1$stbl_bookings.user_id %2$s ORDER BY %1$stbl_bookings.date_created DESC LIMIT %3$d, %4$d', DB_TBL_PREFIX, $query_modifier, $offset, ITEMS_PER_PAGE);

if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){
        while($row = mysqli_fetch_assoc($result)){
            $user_bookings_data[] = $row;
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
                        $params['tab'] = 'sbookings';
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
                    <th>Booking ID</th>    
                    <th>Driver</th>
                    <th>Pick-up</th>
                    <th>Drop-off</th>
                    <th>Booking Time</th>
                    <th>Est.Fare</th>
                    <th>Amount Paid</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Actions</th>                       
                    </tr>
                </thead>
                <tbody>
                    <?php  
                    
                    
                    $count = 1 + (($page_number - 1) * ITEMS_PER_PAGE);
                    $default_currency_symbol = !empty($_SESSION['default_currency']) ? $_SESSION['default_currency']['symbol'] : "₦";
                    
                    
                    foreach($user_bookings_data as $bookingspageitems){
                        $estimated_cost = $bookingspageitems['cur_symbol'] . ($bookingspageitems['estimated_cost'] * $bookingspageitems['cur_exchng_rate']);
                        $amount_paid = !empty($bookingspageitems['paid_amount']) ? $bookingspageitems['paid_amount'] : "N/A";
                        $booking_status = '';
                        switch($bookingspageitems['status']){
                            case 0:
                            $booking_status = "<span style='color:purple'><b>Pending</b></span>";
                            break;

                            case 1:
                            $booking_status = "<span style='color:orange'><b>On Ride</b></span>";
                            break;

                            case 2:
                            $booking_status = "<span style='color:red'><b>Cancelled (Rider)</b></span>";
                            break;

                            case 3:
                            $booking_status = "<span style='color:Green'><b>Completed</b></span>";
                            break;

                            case 4:
                            $booking_status = "<span style='color:red'><b>Cancelled (Driver)</b></span>";
                            break;

                            case 5:
                            $booking_status = "<span style='color:red'><b>Cancelled (System)</b></span>";
                            break;

                            default:
                            $booking_status = "<span style='color:purple'><b>Pending</b></span>";
                            break;
                            
                        }

                        $paymethod = '';
                        switch($bookingspageitems['payment_type']){
                            case 1:
                            $paymethod = "CASH";
                            break;

                            case 2:
                            $paymethod = "WALLET";
                            break;

                            case 3:
                            $paymethod = "CARD";
                            break;

                            case 4:
                            $paymethod = "POS";
                            break;

                            
                            
                        }

                        
                        
                        if(!empty($bookingspageitems['driver_id'])){
                            $driver_details = $bookingspageitems['driver_firstname'] . " " . $bookingspageitems['driver_lastname'] . "<br>" . $bookingspageitems['country_dial_code'] . " ". (!empty(DEMO) ? mask_string($bookingspageitems['driver_phone']) : $bookingspageitems['driver_phone']);
                        }else{
                            $driver_details = "N/A";
                        }                                
                        
                        
                        

                        if($bookingspageitems['status'] == 1 && !empty($bookingspageitems['location_date'])){ 
                            $location_date = date('d/m/Y g:i A',strtotime($bookingspageitems['location_date'] . ' UTC'));
                            $track_ride = " <a data-driverid = '{$bookingspageitems['driver_id']}' data-datetime='{$location_date}' data-lat='{$bookingspageitems['lat']}' data-long='{$bookingspageitems['long']}' href='#driver-location-map-container' class='btn btn-xs btn-success drvr-location' >Track</a>";
                        }else{
                            $track_ride = '';
                        }
                        
                        
                        
                        $view_details = "<a href='view-booking.php?bkid=".$bookingspageitems['booking_id'] ."' class='btn btn-primary btn-xs'>View</a> ";

                        $estimated_cost = $bookingspageitems['cur_symbol'] . ($bookingspageitems['estimated_cost']);
                        $estimated_cost_local = (int) ($bookingspageitems['estimated_cost'] / $bookingspageitems['cur_exchng_rate'] * 100);
                        $estimated_cost_local = $default_currency_symbol . ($estimated_cost_local / 100);
                        $amount_paid = !empty($bookingspageitems['paid_amount']) ? $bookingspageitems['cur_symbol'] . $bookingspageitems['paid_amount'] : "N/A";
                        $amount_paid_local = (int) ($bookingspageitems['paid_amount'] / $bookingspageitems['cur_exchng_rate'] * 100);
                        $amount_paid_local = !empty($bookingspageitems['paid_amount']) ? $default_currency_symbol . ($amount_paid_local / 100) : "0.00";
                        echo "<tr><td>".$count++."</td><td>". str_pad($bookingspageitems['booking_id'] , 5, '0', STR_PAD_LEFT) . "</td><td>".$driver_details."</td><td>". $bookingspageitems['pickup_address'] ."</td><td>". $bookingspageitems['dropoff_address'] ."</td><td>".date('l, M j, Y H:i:s',strtotime($bookingspageitems['date_created'].' UTC')) ."</td><td title='{$estimated_cost_local}' >".$estimated_cost."</td><td title='{$amount_paid_local}' >".$amount_paid."</td><td>". $paymethod ."</td><td>".$booking_status."</td><td>". $view_details . $track_ride ."</td></tr>";

                    }
                    
                    ?>
                </tbody>
                </table>
            </div>
                                  
            <?php if(empty($user_bookings_data)){ echo "<h1 style='text-align:center;'>Nothing to Show. No Booking Data.</h1>";} ?>
             
            
            
                            
        </div><!-- /.box-body -->
    </div>

    



    