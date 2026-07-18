<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        View details of a booking. 
        </div>
    </div>
</div> <!--/Row-->


<div class="row">
    <div class="col-sm-12">
        <div class="row">

            <div class="col-sm-6" >
                <div id = "map1" class="box box-info">
                    <div class="box-header with-border">
                    <h3 id="map1-header-title" class="box-title">Map</h3>             
                    </div><!-- /.box-header -->
                    
                    <div class="box-body">
                        <div id="booking-map1" style="height:400px"></div>



                                                    
                    </div><!-- /.box-body -->
                    
                </div>
            </div>


            <div class="col-sm-6" >
                <div id = "map2" class="box box-info">
                    <div class="box-header with-border">
                    <h3 id="map1-header-title" class="box-title">Driver Travel Route</h3>             
                    </div><!-- /.box-header -->
                    
                    <div class="box-body">
                        
                        <div id="booking-map2" style="height:400px"></div>  
                        
                        <div style="margin: 10px 0;">
                            <p><span style="display: inline-block;width:20px;height:10px;background-color:blue;"></span> Location driver accepted the trip request</p>
                            <p><span style="display: inline-block;width:20px;height:10px;background-color:orange;"></span> Location driver arrived to pickup customer</p>
                            <p><span style="display: inline-block;width:20px;height:10px;background-color:green;"></span> Location driver started the trip</p>
                            <p><span style="display: inline-block;width:20px;height:10px;background-color:red;"></span> Location driver completed the trip</p>
                        </div>
                    </div><!-- /.box-body -->
                    
                </div>

                
                <input type="text" id="pcity-zone-long" hidden="hidden" value="<?php echo !empty($booking_data['pickup_long']) ? $booking_data['pickup_long'] : "0"; ?>">
                <input type="text" id="pcity-zone-lat" hidden="hidden" value="<?php echo !empty($booking_data['pickup_lat']) ? $booking_data['pickup_lat'] : "0"; ?>">
                <input type="text" id="dcity-zone-long" hidden="hidden" value="<?php echo !empty($booking_data['dropoff_long']) ? $booking_data['dropoff_long'] : "0"; ?>">
                <input type="text" id="dcity-zone-lat" hidden="hidden" value="<?php echo !empty($booking_data['dropoff_lat']) ? $booking_data['dropoff_lat'] : "0"; ?>">

                <input type="text" id="driver-route-polyline" hidden="hidden" value="<?php echo !empty($route_polyline_latlong) ? htmlspecialchars(json_encode($route_polyline_latlong)) : ""; ?>">
                <input type="text" id="driver-route-start-lat" hidden="hidden" value="<?php echo !empty($route_start_lat) ? $route_start_lat : ""; ?>">
                <input type="text" id="driver-route-start-long" hidden="hidden" value="<?php echo !empty($route_start_long) ? $route_start_long : ""; ?>">
                <input type="text" id="driver-route-end-lat" hidden="hidden" value="<?php echo !empty($route_end_lat) ? $route_end_lat : ""; ?>">
                <input type="text" id="driver-route-end-long" hidden="hidden" value="<?php echo !empty($route_end_long) ?  $route_end_long : ""; ?>">

                <input type="text" id="driver-route-accept-lat" hidden="hidden" value="<?php echo !empty($booking_data['drv_acc_lat']) ? $booking_data['drv_acc_lat'] : ""; ?>">
                <input type="text" id="driver-route-accept-long" hidden="hidden" value="<?php echo !empty($booking_data['drv_acc_long']) ? $booking_data['drv_acc_long'] : ""; ?>">

                <input type="text" id="driver-route-arrived-lat" hidden="hidden" value="<?php echo !empty($booking_data['drv_arv_lat']) ? $booking_data['drv_arv_lat'] : ""; ?>">
                <input type="text" id="driver-route-arrived-long" hidden="hidden" value="<?php echo !empty($booking_data['drv_arv_long']) ? $booking_data['drv_arv_long'] : ""; ?>">

                <input type="text" id="driver-route-strt-lat" hidden="hidden" value="<?php echo !empty($booking_data['drv_start_lat']) ? $booking_data['drv_start_lat'] : ""; ?>">
                <input type="text" id="driver-route-strt-long" hidden="hidden" value="<?php echo !empty($booking_data['drv_start_long']) ? $booking_data['drv_start_long'] : ""; ?>">

                <input type="text" id="driver-route-comp-lat" hidden="hidden" value="<?php echo !empty($booking_data['drv_comp_lat']) ? $booking_data['drv_comp_lat'] : ""; ?>">
                <input type="text" id="driver-route-comp-long" hidden="hidden" value="<?php echo !empty($booking_data['drv_comp_long']) ? $booking_data['drv_comp_long'] : ""; ?>">

                <input type="text" id="track_booking_id" hidden="hidden" value="<?php echo !empty($booking_data['booking_id']) ? $booking_data['booking_id'] : 0; ?>">
                <input type="text" id="track_driver_id" hidden="hidden" value="<?php echo !empty($booking_data['driver_ids']) ? $booking_data['driver_ids'] : 0; ?>">
                <input type="text" id="track_route_id" hidden="hidden" value="<?php echo !empty($booking_data['b_route_id']) ? $booking_data['b_route_id'] : 0; ?>">


                
            </div> <!--/col-sm-6-->	

        </div>
    </div>
    
    <?php

        //$photo = explode('/',$booking_data['photo_file']);
        $photo_file = isset($booking_data['user_photo']) ? $booking_data['user_photo'] : "0";

        //$photo2 = explode('/',$booking_data['driver_photo']);
        $photo_file2 = isset($booking_data['driver_photo']) ? $booking_data['driver_photo'] : "0";

        if(isset($booking_data['status'])){
           if($booking_data['status'] == 0){
            if(!empty($booking_data['driver_id'])){
                $ride_status = "Driver en route";
            }else{
                $ride_status = "Pending";
            }
            
           }elseif($booking_data['status'] == 1){
            $ride_status = "Customer On Ride";
           }elseif($booking_data['status'] == 2){
            $ride_status = "Cancelled by Customer";
           }elseif($booking_data['status'] == 3){
            $ride_status = "Completed";
           }elseif($booking_data['status'] == 4){
            $ride_status = "Cancelled by Driver";
           }elseif($booking_data['status'] == 5){
            $ride_status = "Cancelled by System";
           }elseif($booking_data['status'] == 6){
            $ride_status = "Driver Arrived";
           }
             
        }else{
            $ride_status = "N/A";
        }

    ?>


    <div class="col-sm-12" >
		<div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Booking #<?php echo str_pad($booking_data['booking_id'] , 5, '0', STR_PAD_LEFT) . " - " . date('l, M j, Y H:i:s',strtotime($booking_data['date_created'].' UTC')) . " | " . $ride_status; ?></h3>                    
            </div>
                        
            <div class="box-body">
                <br>
                <div style="text-align:center;">
                    <div style="width:48%;border-right:thin solid black;display:inline-block;">
                        <a href="<?php echo $booking_data['account_type'] == 1 ? "view-customer.php?id={$booking_data['user_ids']}" : "view-staff.php?id={$booking_data['user_ids']}";?>">
                            <h4 style="text-align:center;">Rider</h4>
                            <div style="text-align:center;">
                                <img src="<?php echo empty($photo_file) ? "../img/usersample.jpg" : "../userphotofile.php?file=". $photo_file;?>" style="width:140px;border:thin solid white;border-radius:50%;" /> 
                            </div>
                            <h3 style="text-align:center;"><?php echo !empty($booking_data['user_firstname']) ? $booking_data['user_firstname'] . " " . $booking_data['user_lastname']  : "N/A"; ?></h3>
                            <p style="font-size: 14px;margin-bottom: 5px;color:#777;text-align:center;"><?php echo !empty($booking_data['user_phone']) ? $booking_data['user_country_dial_code']." ". (!empty(DEMO) ? mask_string($booking_data['user_phone']) : $booking_data['user_phone']) : "---"; ?></p>
                        </a>
                    </div>
                    <div style="width:48%;display:inline-block;">
                        <a href="<?php echo "view-driver.php?id={$booking_data['driver_ids']}";?>">
                            <h4 style="text-align:center;">Driver</h4>
                            <div style="text-align:center;">
                                <img src="<?php echo empty($photo_file2) ? "../img/usersample.jpg" : "../photofile.php?file=". $photo_file2;?>" style="width:140px;border:thin solid white;border-radius:50%;" /> 
                            </div>
                            <h3 style="text-align:center;"><?php echo !empty($booking_data['driver_ids']) ? $booking_data['driver_firstname'] . " " . $booking_data['driver_lastname']  : "Unassigned"; ?></h3>
                            <p style="font-size: 14px;margin-bottom: 5px;color:#777;text-align:center;"><?php echo !empty($booking_data['driver_ids']) ? $booking_data['driver_country_dial_code']." ".(!empty(DEMO) ? mask_string($booking_data['driver_phone']) : $booking_data['driver_phone']) : "---"; ?></p>
                        </a>
                    </div>
                </div>
                <br>
                <h4>Trip Details:</h4>
                <hr>
                <p style="font-size:12px;font-weight:bold;color:#777">SERVICE TYPE</P>
                <p style="font-size: 16px;margin-bottom: 5px;"><?php echo !empty($booking_data['service_mode']) == 1 ? "Quick-Ride" : "Regular Taxi"; ?></p>
                <br>
                <p style="font-size:12px;font-weight:bold;color:#777">CITY / ROUTE</P>
                <p style="font-size: 16px;margin-bottom: 5px;"><?php echo !empty($booking_data['r_title']) ? $booking_data['r_title'] : "N/A"; ?></p>
                <p style="font-size:12px;font-weight:bold;color:#777"><?php echo isset($booking_data['r_scope']) && $booking_data['r_scope'] == "0" ? "Intra city ride" : "Inter state ride"; ?></p> 
                <br>
                <p style="font-size:12px;font-weight:bold;color:#777">CAR TYPE</P>
                <p style="font-size: 16px;margin-bottom: 5px;"><?php echo !empty($booking_data['ride_type']) ? $booking_data['ride_type'] : "N/A"; ?></p>
                <br>
                <p style="font-size:12px;font-weight:bold;color:#777">PICK-UP LOCATION</P>
                <p style="font-size: 16px;margin-bottom: 5px;"><?php echo !empty($booking_data['pickup_address']) ? $booking_data['pickup_address'] : "N/A"; ?></p>
                <p style="font-size:12px;font-weight:bold;color:#777">Longitude: <?php echo !empty($booking_data['pickup_long']) ? $booking_data['pickup_long'] : "N/A"; ?> Latitude: <?php echo !empty($booking_data['pickup_lat']) ? $booking_data['pickup_lat'] : "N/A"; ?></p> 
                <br>
                <p style="font-size:12px;font-weight:bold;color:#777">DROP-OFF LOCATION</P>
                <p style="font-size: 16px;margin-bottom: 5px;"><?php echo !empty($booking_data['dropoff_address']) ? $booking_data['dropoff_address'] : "Destination not specified"; ?></p>
                <p style="font-size:12px;font-weight:bold;color:#777">Longitude: <?php echo !empty($booking_data['dropoff_long']) ? $booking_data['dropoff_long'] : "N/A"; ?> Latitude: <?php echo !empty($booking_data['dropoff_lat']) ? $booking_data['dropoff_lat'] : "N/A"; ?></p>           

                <br>
                <p style="font-size:12px;font-weight:bold;color:#777">PICK-UP DATE/TIME (<?php echo isset($booking_data['scheduled']) && $booking_data['scheduled'] == "1" ? "SCHEDULED RIDE" : "INSTANT RIDE"; ?>)</P>
                <p style="font-size: 16px;margin-bottom: 5px;"><?php echo !empty($booking_data['pickup_datetime']) ? date('l, M j, Y H:i:s',strtotime($booking_data['pickup_datetime'].' UTC')) : "N/A"; ?></p>
                
                <br>
                <p style="font-size:12px;font-weight:bold;color:#777">DRIVER ARRIVAL TIME/DATE </P>
                <p style="font-size: 16px;margin-bottom: 5px;"><?php echo !empty($booking_data['date_arrived']) ? date('l, M j, Y H:i:s',strtotime($booking_data['date_arrived'].' UTC')) : "N/A"; ?></p>

                <br>
                <p style="font-size:12px;font-weight:bold;color:#777">TRIP START TIME/DATE </P>
                <p style="font-size: 16px;margin-bottom: 5px;"><?php echo !empty($booking_data['date_started']) ? date('l, M j, Y H:i:s',strtotime($booking_data['date_started'].' UTC')) : "N/A"; ?></p>

                <br>
                <p style="font-size:12px;font-weight:bold;color:#777">TRIP COMPLETED TIME/DATE </P>
                <p style="font-size: 16px;margin-bottom: 5px;"><?php echo !empty($booking_data['date_completed']) ? date('l, M j, Y H:i:s',strtotime($booking_data['date_completed'].' UTC')) : "N/A"; ?></p>

                <br>
                <p style="font-size:12px;font-weight:bold;color:#777">TRIP OTP CODE </P>
                <p style="font-size: 16px;margin-bottom: 5px;"><?php echo $booking_data['completion_code']; ?></p>

                <br>
                <p style="font-size:12px;font-weight:bold;color:#777">TRIP DURATION </P>
                <?php

                    $ride_duration = '0 Secs';
                    if(!empty($booking_data['date_started']) && !empty($booking_data['date_completed'])){
                        $ride_start_time = strtotime($booking_data['date_started']);
                        $ride_end_time = strtotime($booking_data['date_completed']);
                        $ride_duration_secs = $ride_end_time - $ride_start_time;
                        if($ride_duration_secs){
                                                    
                            $hours = floor($ride_duration_secs / 3600);
                            $minutes = floor(($ride_duration_secs % 3600) / 60 );
                            $seconds = ($ride_duration_secs % 3600) % 60;
                            $ride_duration = '';
                            if(!empty($hours)){
                                $ride_duration = $hours . "H ";
                            }

                            if(!empty($minutes)){
                                $ride_duration .= $minutes . "M ";
                            }


                            if(!empty($seconds)){
                                $ride_duration .= $seconds . "S";
                            }

                        }
                        
                    }

                ?>
                <p style="font-size: 16px;margin-bottom: 5px;"><?php echo $ride_duration; ?></p>
                
                <br>
                <p style="font-size:12px;font-weight:bold;color:#777">TRIP DISTANCE TRAVELLED </P>
                <p style="font-size: 16px;margin-bottom: 5px;"><?php echo !empty($booking_data['distance_travelled']) ? $booking_data['distance_travelled'] . "Meters" : "0 Meters"; ?></p>

                <br>
                <br>

                <h4>Trip Financials:</h4>
                <hr>
                <p style="font-size:12px;font-weight:bold;color:#777">PAYMENT TYPE</P>
                <p style="font-size: 16px;margin-bottom: 5px;">
                    <?php 
                        if(isset($booking_data['payment_type'])){
                            if($booking_data['payment_type'] == '1'){
                                echo "Cash";
                            }elseif($booking_data['payment_type'] == '2'){
                                echo "Wallet";
                            }elseif($booking_data['payment_type'] == '3'){
                                echo "Card";
                            }else{
                                echo "POS";
                            }
                        }else{
                            echo "N/A";
                        }
                     ?>
                </p>


                <br>
                <p style="font-size:12px;font-weight:bold;color:#777"> FARE TYPE</P>
                <p style="font-size: 16px;margin-bottom: 5px;">
                    <?php
                        echo !empty($booking_data['service_mode']) && !empty($booking_data['hr_set_num_h']) ? "Hourly: ({$booking_data['hr_set_num_h']} Hours)" : "Per KM / Mi" ;
                    ?>
                </p>
                <br>
                <p style="font-size:12px;font-weight:bold;color:#777"> GOOGLE MAPS ESTIMATED FARE</P>
                <p style="font-size: 16px;margin-bottom: 5px;">
                    <?php
                        echo !empty($booking_data['estimated_cost']) ? $booking_data['cur_symbol'] . $booking_data['estimated_cost'] : "N/A" ;
                    ?>
                </p>
                <br>
                <p style="font-size:12px;font-weight:bold;color:#777"> ACTUAL FARE</P>
                <p style="font-size: 16px;margin-bottom: 5px;">
                    <?php
                        echo !empty($booking_data['actual_cost']) ? $booking_data['cur_symbol'] . $booking_data['actual_cost'] : "N/A" ;
                    ?>
                </p>
                <br>
                <p style="font-size:12px;font-weight:bold;color:#777">AMOUNT PAID BY RIDER</P>
                <p style="font-size: 16px;margin-bottom: 5px;">
                    <?php
                        
                        echo !empty($booking_data['paid_amount']) ? $booking_data['cur_symbol'] . $booking_data['paid_amount'] : "N/A" ;
                    ?>
                </p>
                <?php
                    $default_currency_symbol = !empty($_SESSION['default_currency']) ? $_SESSION['default_currency']['symbol'] : "₦";
                    $amount = (float) !empty($booking_data['actual_cost']) ? $booking_data['actual_cost'] : "0"; 
                    $owner_franchise_amount_deficit = $booking_data['paid_amount'] - $booking_data['actual_cost'];

                    $currency_symbol = !empty($booking_data['cur_symbol']) ? $booking_data['cur_symbol'] : " ₦"; 
                    $currency_exchange = (float) !empty($booking_data['cur_exchng_rate']) ? $booking_data['cur_exchng_rate'] : "1";     

                    $driver_commision_percent = (float) !empty($booking_data['drv_commision']) ? $booking_data['drv_commision'] : "0";
                    $driver_amount = ($amount * $driver_commision_percent / 100);
                    $driver_amount_converted = $driver_amount / $currency_exchange;

                    if($booking_data['franchise_id'] == 1){
                        //owner franchise; 100% commision after deducting driver commission
                        $owner_franchise_commision_percent = 100;
                        $owner_franchise_amount = (($amount - $driver_amount) * 100 / 100);
                        $owner_franchise_amount = $owner_franchise_amount + $owner_franchise_amount_deficit; //apply deficit due to rider using referral or coupon discounts
                        $owner_franchise_amount_converted = $owner_franchise_amount / $currency_exchange;

                        $other_franchise_commision_percent = 0;
                        $other_franchise_amount = 0;
                        $other_franchise_amount_converted = 0;

                    }else{
                        //other franchise
                        $other_franchise_commision_percent = $booking_data['franch_commision'];
                        $owner_franchise_commision_percent = 100.0 - (float) $booking_data['franch_commision'];
                        $other_franchise_amount = (($amount - $driver_amount) * (float) $booking_data['franch_commision'] / 100);
                        $other_franchise_amount_converted = $other_franchise_amount / $currency_exchange;

                        $owner_franchise_amount = (($amount - $driver_amount) - $other_franchise_amount);
                        $owner_franchise_amount = $owner_franchise_amount + $owner_franchise_amount_deficit; //apply deficit due to rider using referral or coupon discounts
                        $owner_franchise_amount_converted = $owner_franchise_amount / $currency_exchange;

                    }
                    $franchise_commision_percent = $booking_data['franchise_id'] == 1 ? 100 : (float) $booking_data['franch_commision'];
                    
                ?>

                <?php if(!empty($booking_data['coupon_code'])){ ?>
                    <br>
                    <p style="font-size:12px;font-weight:bold;color:#777">COUPON CODE </P>
                    <p style="font-size: 16px;margin-bottom: 5px;"><?php echo $booking_data['coupon_code']; ?></p>
                    <br>
                    <p style="font-size:12px;font-weight:bold;color:#777">COUPON DISCOUNT (<?php echo isset($booking_data['coupon_discount_type']) && $booking_data['coupon_discount_type'] == "0" ? "PERCENTAGE" : "FIXED"; ?>) </P>
                    <p style="font-size: 16px;margin-bottom: 5px;"><?php echo isset($booking_data['coupon_discount_type']) && $booking_data['coupon_discount_type'] == "0" ? $booking_data['coupon_discount_value'] . "%" : $currency_symbol.floattocurrency($booking_data['coupon_discount_value']); ?></p>
                <?php } ?>
                
                <?php if($booking_data['referral_used'] == 1){ ?>
                    <br>
                    <p style="font-size:12px;font-weight:bold;color:#777">REFERRAL DISCOUNT (%) </P>
                    <p style="font-size: 16px;margin-bottom: 5px;"><?php echo !empty($booking_data['referral_used']) ? $booking_data['referral_discount_value'] . "%" : "0.00%"; ?></p>
                <?php } ?>

                <br>
                <p style="font-size:12px;font-weight:bold;color:#777">DRIVER COMMISSION (<?php echo $booking_data['drv_commision'];?>%)</P>
                <p style="font-size: 16px;margin-bottom: 5px;" title="<?php echo $default_currency_symbol . floattocurrency($driver_amount_converted) ?>"><?php echo $booking_data['status'] == 3 ? $currency_symbol.floattocurrency($driver_amount) : "N/A";?></p>

                <br>
                <p style="font-size:12px;font-weight:bold;color:#777">COMPANY COMMISSION (<?php echo $owner_franchise_commision_percent . "%"; ?>)</P>
                <p style="font-size: 16px;margin-bottom: 5px;" title="<?php echo $default_currency_symbol . floattocurrency($owner_franchise_amount_converted) ?>"><?php echo $booking_data['status'] == 3 ? $currency_symbol.floattocurrency($owner_franchise_amount) : "N/A";?></p>
                <br>
                <?php if($booking_data['franchise_id'] != 1){?>
                    <p style="font-size:12px;font-weight:bold;color:#777"><?php echo strtoupper(strVal($booking_data['franchise_name'])); ?> FRANCHISE COMMISSION (<?php echo $other_franchise_commision_percent . "%"; ?>)</P>
                    <p style="font-size: 16px;margin-bottom: 5px;" title="<?php echo $default_currency_symbol . floattocurrency($other_franchise_amount_converted) ?>"><?php echo $booking_data['status'] == 3 ? $currency_symbol.floattocurrency($other_franchise_amount) : "N/A";?></p>
                <?php }?>  
                <br>
                <br>
                <h4>Ratings and Reviews:</h4>
                <hr>
                <div style="width:70%;">
                    <p style="font-size:12px;font-weight:bold;color:#777"><?php echo $booking_data['user_firstname'] . " " . $booking_data['user_lastname']; ?> (RIDER)</P>
                    <p><?php echo !empty($booking_data['user_rating']) ? "<img src='../img/rating-{$booking_data['user_rating']}.png' style='display:block;width:70px;'/>" : "<img src='../img/rating-0.png' style='display:block;width:70px;'/>";?></p>
                    <blockquote>
                        <?php echo !empty($booking_data['user_comment']) ? $booking_data['user_comment'] : "No rider comment for this ride.";?>
                    </blockquote>
                </div>
                <br>
                <div style="width:70%;margin-left:25%;">
                    <p style="font-size:12px;font-weight:bold;color:#777;text-align:right;"><?php echo $booking_data['driver_firstname'] . " " . $booking_data['driver_lastname']; ?> (DRIVER)</P>
                    <p style="text-align:right;"><?php echo !empty($booking_data['driver_rating']) ? "<img src='../img/rating-{$booking_data['driver_rating']}.png' style='width:70px;'/>" : "<img src='../img/rating-0.png' style='width:70px;'/>";?></p>
                    <blockquote style="text-align:right;border-left: none;border-right: 5px solid #eee;">
                    <?php echo !empty($booking_data['driver_comment']) ? $booking_data['driver_comment'] : "No driver comment for this ride.";?>
                    </blockquote>
                </div>
                <?php if(!empty($chat_messages_html)){?>
                    <br>
                    <br>
                    <h4>Chats</h4>
                    <hr>
                    <div>
                        <?php echo $chat_messages_html; ?>
                    </div>
                <?php } ?>

                <?php if(!empty($booking_data['cancel_comment'])){?>
                    <br>
                    <br>
                    <h4>Cancel Reason:</h4>
                    <hr>
                    <p style="font-size: 16px;margin-bottom: 5px;">
                        <?php echo $booking_data['cancel_comment']; ?>
                    </p>
                <?php } ?>
	            
            </div><!-- /.box-body -->
            
        </div>

    </div> <!--/col-sm-6-->



    



</div>

	
    





<script src="../js/carsvg.js"></script>
<script>

    var map1 = undefined; 
    var map2 = undefined;
    var bounds = undefined; 
    var marker1 = undefined;
    var marker = undefined;                  
    var marker2 = undefined;
    var marker3 = undefined;
    var marker4 = undefined; 
    var latLong = undefined;
    var latLong1 = undefined;
    var latLong2 = undefined;
    var latLong3 = undefined;
    var latLong4 = undefined;
    var ride_selected_id = 0;
    var route_rides = [];
    var intra_city_distance_text = "";
    var intra_city_duration_text = "";
    var intra_city_distance = 0;
    var intra_city_duration = 0;
    var inter_city_distance_text = "";
    var inter_city_duration_text = "";
    var inter_city_distance = 0;
    var inter_city_duration = 0;
    var driver_travel_path;
    var driver_location_marker;
    var driver_refresh_travel_path_timer = 0;


    

    if (typeof google === 'object' && typeof google.maps === 'object') {
    
        if(typeof mapOptions === 'undefined'){
            mapOptions = {
            center: new google.maps.LatLng(9.0338725,8.677457),
            zoom: 5,
            disableDefaultUI: false,
            mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            map1 = new google.maps.Map(document.getElementById("booking-map1"), mapOptions);
            directionsService = new google.maps.DirectionsService;
            directionsDisplay = new google.maps.DirectionsRenderer({
                map: map1
            });


            map2 = new google.maps.Map(document.getElementById("booking-map2"), mapOptions);
            directionsService2 = new google.maps.DirectionsService;
            directionsDisplay2 = new google.maps.DirectionsRenderer({
                map: map2
            });

            bounds = new google.maps.LatLngBounds();
            bounds2 = new google.maps.LatLngBounds();
                    
        
        }
        
        
    }


intracityplot();

function intracityplot(){

 
        var plng = jQuery('#pcity-zone-long').val();
        var plat = jQuery('#pcity-zone-lat').val();

        var dlng = jQuery('#dcity-zone-long').val();
        var dlat = jQuery('#dcity-zone-lat').val();

        if(marker1){
            marker1.setMap(null);
            marker1 = [];
        }

        if(marker2){
            marker2.setMap(null);
            marker2 = [];
        }

        setTimeout(() => {

            latLong1 = new google.maps.LatLng(parseFloat(plat), parseFloat(plng));
            marker1 = new google.maps.Marker({
                position: latLong1,
                map: map1,
                animation: google.maps.Animation.DROP
            });
            marker1.setMap(map1);
            if(parseFloat(dlat) && parseFloat(dlng)){
                map1.setZoom(7);
            }else{
                map1.setZoom(17);
            }
            
            //map.setCenter(marker.getPosition());
            map1.panTo(marker1.getPosition());
            
            if(parseFloat(dlat) && parseFloat(dlng)){

                setTimeout(() => {
                    latLong2 = new google.maps.LatLng(parseFloat(dlat), parseFloat(dlng));
                    marker2 = new google.maps.Marker({
                        position: latLong2,
                        map: map1,
                        animation: google.maps.Animation.DROP
                    });
                    marker2.setMap(map1);
                    map1.setZoom(7);
                    //map.setCenter(marker.getPosition());
                    map1.panTo(marker2.getPosition());

                    

                    setTimeout(function(){
                        bounds.extend(latLong1);
                        bounds.extend(latLong2);
                        var pointA = latLong1;
                        var pointB = latLong2;                
                        map1.fitBounds(bounds);
                        
                        bounds = [];
                        bounds = new google.maps.LatLngBounds();  
                        calculateAndDisplayRoute2(directionsService, directionsDisplay, pointA, pointB);
                        marker1.setMap(null);
                        marker2.setMap(null);
                    }, 1500);
                
                }, 1500);
            }

                        
        }, 1500);



}


driverRoutePlot();


function driverRoutePlot(){

    
    var route_polyline = jQuery('#driver-route-polyline').val();

    var route_polyline_arr = JSON.parse(route_polyline);

    if(!route_polyline_arr.length)return;

    

    var start_lng = jQuery('#driver-route-start-long').val();
    var start_lat = jQuery('#driver-route-start-lat').val();

    var end_lng = jQuery('#driver-route-end-long').val();
    var end_lat = jQuery('#driver-route-end-lat').val();

    let drv_acc_marker;
    let drv_acc_lat = jQuery('#driver-route-accept-lat').val();
    let drv_acc_lng = jQuery('#driver-route-accept-long').val();

    let drv_arr_marker;
    let drv_arr_lat = jQuery('#driver-route-arrived-lat').val();
    let drv_arr_lng = jQuery('#driver-route-arrived-long').val();

    let drv_start_marker;
    let drv_start_lat = jQuery('#driver-route-strt-lat').val();
    let drv_start_lng = jQuery('#driver-route-strt-long').val();

    let drv_comp_marker;
    let drv_comp_lat = jQuery('#driver-route-comp-lat').val();
    let drv_comp_lng = jQuery('#driver-route-comp-long').val();

    if(drv_acc_marker){
        drv_acc_marker.setMap(null);
        drv_acc_marker = [];
    }

    if(drv_arr_marker){
        drv_arr_marker.setMap(null);
        drv_arr_marker = [];
    }

    if(drv_start_marker){
        drv_start_marker.setMap(null);
        drv_start_marker = [];
    }

    if(drv_comp_marker){
        drv_comp_marker.setMap(null);
        drv_comp_marker = [];
    }

    if(marker3){
        marker3.setMap(null);
        marker3 = [];
    }

    if(marker4){
        marker4.setMap(null);
        marker4 = [];
    }

    setTimeout(() => {

        

        //driver accept trip location marker
        if(drv_acc_lat){
            latLong3 = new google.maps.LatLng(parseFloat(drv_acc_lat), parseFloat(drv_acc_lng));
            drv_acc_marker = new google.maps.Marker({
                position: latLong3,
                map: map2,
                icon: "https://maps.google.com/mapfiles/ms/micons/blue.png",
                animation: google.maps.Animation.DROP,
                label: { color: '#ffffff', fontWeight: 'bold', fontSize: '14px', text: 'A' }
            });
            drv_acc_marker.setMap(map2);
            map2.setZoom(7);
            map2.panTo(drv_acc_marker.getPosition());
        }else{
            latLong3 = new google.maps.LatLng(parseFloat(start_lat), parseFloat(start_lng));
            marker3 = new google.maps.Marker({
                position: latLong3,
                map: map2,
                icon: "https://maps.google.com/mapfiles/ms/micons/blue.png",
                animation: google.maps.Animation.DROP,
                label: { color: '#ffffff', fontWeight: 'bold', fontSize: '14px', text: 'A' }
            });
            marker3.setMap(map2);
            map2.setZoom(7);
            map2.panTo(marker3.getPosition());
        }

        //driver arrived location marker
        if(drv_arr_lat){
            let latLong_arr = new google.maps.LatLng(parseFloat(drv_arr_lat), parseFloat(drv_arr_lng));
            drv_arr_marker = new google.maps.Marker({
                position: latLong_arr,
                map: map2,
                icon: "https://maps.google.com/mapfiles/ms/micons/orange.png",
                animation: google.maps.Animation.DROP,
                label: { color: '#ffffff', fontWeight: 'bold', fontSize: '14px', text: 'B' }
            });
            drv_arr_marker.setMap(map2);
            map2.setZoom(7);
            //map2.panTo(drv_arr_marker.getPosition());
        }


        //driver start trip location marker
        if(drv_start_lat){
            let latLong_start = new google.maps.LatLng(parseFloat(drv_start_lat), parseFloat(drv_start_lng));
            drv_start_marker = new google.maps.Marker({
                position: latLong_start,
                map: map2,
                icon: "https://maps.google.com/mapfiles/ms/micons/green.png",
                animation: google.maps.Animation.DROP,
                label: { color: '#ffffff', fontWeight: 'bold', fontSize: '14px', text: 'C' }
            });
            drv_start_marker.setMap(map2);
            map2.setZoom(7);
            //map2.panTo(drv_start_marker.getPosition());
        }
        

        setTimeout(() => {
            
            //driver complete trip location marker
            if(drv_comp_lat){
                latLong4 = new google.maps.LatLng(parseFloat(drv_comp_lat), parseFloat(drv_comp_lng));
                drv_comp_marker = new google.maps.Marker({
                    position: latLong4,
                    map: map2,
                    icon: "https://maps.google.com/mapfiles/ms/micons/red.png",
                    animation: google.maps.Animation.DROP,
                    label: { color: '#ffffff', fontWeight: 'bold', fontSize: '14px', text: 'D' }
                });
                drv_comp_marker.setMap(map2);
                map2.setZoom(7);
                map2.panTo(drv_comp_marker.getPosition());
            }else if(end_lat){
                latLong4 = new google.maps.LatLng(parseFloat(end_lat), parseFloat(end_lng));
                marker4 = new google.maps.Marker({
                    position: latLong4,
                    map: map2,
                    icon: "https://maps.google.com/mapfiles/ms/micons/red.png",
                    animation: google.maps.Animation.DROP,
                    label: { color: '#ffffff', fontWeight: 'bold', fontSize: '14px', text: 'D' }
                });
                marker4.setMap(map2);
                map2.setZoom(7);
                map2.panTo(marker4.getPosition());
            }

            

            setTimeout(function(){
                bounds2.extend(latLong3);
                bounds2.extend(latLong4);              
                map2.fitBounds(bounds2);
                
                bounds2 = [];
                bounds2 = new google.maps.LatLngBounds();
                
                driver_travel_path = new google.maps.Polyline({
                path: JSON.parse(route_polyline),
                geodesic: true,
                strokeColor: '#FF0000',
                strokeOpacity: 1.0,
                strokeWeight: 4,
                });

                driver_travel_path.setMap(map2);
                driver_refresh_travel_path_timer = setInterval(updateTravelPath, 5000);
                
            }, 1500);
            
        }, 1500);   

                    
    }, 1500);



}


function updateTravelPath(){

    
    let booking_id = $('#track_booking_id').val();
    let driver_id = $('#track_driver_id').val();
    let route_id = $('#track_route_id').val();

    if(!driver_location_marker){

        latitude = 9.0338725;
        longitude = 8.677457;
        latLong = new google.maps.LatLng(latitude,longitude);
        let icon = {
                        path : car_svg_data,
                        fillColor: '#283593',
                        fillOpacity: 1,
                        anchor: new google.maps.Point(50,50),
                        strokeWeight: 0,
                        scale: 0.4,
                        rotation: 0
                    };
        driver_location_marker = new google.maps.Marker({
            position: latLong,
            map: map2,
            icon: icon,
            title : ''
        });
    }

    if(!driver_id)return;
    var post_data = {'action':'getDriverTravelRouteData','driver_id' : driver_id, 'route_id' : route_id, "booking_id" : booking_id};
    
    $.ajax({
        url: ajaxurl,
        type: 'POST',
        crossDomain:true,
        xhrFields: {withCredentials: true},
        data: post_data,
        success: function (data, status)
        {
            
            try{
                var data_obj = JSON.parse(data);
            }catch(e){                
                return;
            }

            if(data_obj.hasOwnProperty('error')){
                if(data_obj.error_code == 2){
                    clearInterval(driver_refresh_travel_path_timer);
                }
            }

            if(data_obj.hasOwnProperty('success')){
                
                
                latLong = new google.maps.LatLng(data_obj.drv_loc_data.lat,data_obj.drv_loc_data.lng);
                    
                let marker_icon = driver_location_marker.getIcon();
                marker_icon.rotation = data_obj.drv_loc_data.b_angle;
                driver_location_marker.setIcon(marker_icon);
                
                driver_location_marker.setPosition(latLong);
                map2.panTo(driver_location_marker.getPosition());

                driver_travel_path = new google.maps.Polyline({
                path: data_obj.drv_route_data,
                geodesic: true,
                strokeColor: '#FF0000',
                strokeOpacity: 1.0,
                strokeWeight: 4,
                });

                driver_travel_path.setMap(map2);

            
            }  

        },
        error:function(jqXHR,textStatus, errorThrown){
            return;
        }
        
    });




}
    


function calculateAndDisplayRoute2(directionsService, directionsDisplay, pointA, pointB) {
        directionsService.route({
            origin: pointA,
            destination: pointB,
            avoidTolls: false,
            avoidHighways: false,
            unitSystem: google.maps.UnitSystem.METRIC,
            travelMode: google.maps.TravelMode.DRIVING
        }, function (response, status) {
            console.log(response.routes[0].legs[0].distance.text);
            intra_city_duration_text = response.routes[0].legs[0].duration.text;
            intra_city_distance_text = response.routes[0].legs[0].distance.text;

            intra_city_duration = response.routes[0].legs[0].duration.value / 60;
            intra_city_distance = response.routes[0].legs[0].distance.value / 1000;

            if (status == google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(response);
                jQuery("#map1-header-title").html("Map: Distance = " + response.routes[0].legs[0].distance.text + " Duration: " + response.routes[0].legs[0].duration.text);
                                
                                

            } else {

                imgurl = '../img/info_.gif?a=' + Math.random();                    
                swal({
                    title: '<h1>Error</h1>',
                    text: 'Cannot determine location coordinates. Please ensure you use google maps location suggestions!' ,
                    imageUrl:imgurl,
                    html:true
                });
                //window.alert('Directions request failed due to ' + status);
            }
        });
    }





</script>













