<?php
$franchise_drivers_data = [];
$query_modifier  = ' = ' . $id;
$number_of_drivers_data = 0;

$booked_drivers_data = [];

//get drivers who are currently onride or allocated to bookings
$query = sprintf('SELECT %1$stbl_bookings.id AS booking_id,%1$stbl_bookings.driver_id AS booking_driver, %1$stbl_driver_allocate.driver_id AS booking_driver_alloc, %1$stbl_driver_allocate.status AS booking_driver_alloc_status  FROM %1$stbl_bookings
INNER JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id
LEFT JOIN %1$stbl_driver_allocate ON %1$stbl_driver_allocate.booking_id = %1$stbl_bookings.id
WHERE (%1$stbl_bookings.status = 0 OR %1$stbl_bookings.status = 1) AND %1$stbl_bookings.franchise_id = %2$d', DB_TBL_PREFIX, $id);

if($result = mysqli_query($GLOBALS['DB'], $query)){
  
    if(mysqli_num_rows($result)){
        while($row = mysqli_fetch_assoc($result)){
            if(!empty($row['booking_driver'])){
                $booked_drivers_data[$row['booking_driver']] = array('driver_id' => $row['booking_driver'], 'status' => "Servicing booking <a href='view-booking.php?bkid={$row['booking_id']}'>#{$row['booking_id']}</a>");
            }
            if(!empty($row['booking_driver_alloc'] && $row['booking_driver_alloc_status'] == 0 )){
                $booked_drivers_data[$row['booking_driver_alloc']] = array('driver_id' => $row['booking_driver_alloc'], 'status' => "Allocated to booking <a href='view-booking.php?bkid={$row['booking_id']}'>#{$row['booking_id']}</a>");
            }
            
        }
    
      }
    mysqli_free_result($result);
}

//get number of drivers
$query = sprintf('SELECT COUNT(*) FROM %1$stbl_drivers WHERE %1$stbl_drivers.franchise_id %2$s', DB_TBL_PREFIX, $query_modifier);

if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){
        $row = mysqli_fetch_assoc($result);        
        $number_of_drivers_data = $row['COUNT(*)'];
    }
}

//calculate pages
if(isset($_GET['page']) && (isset($_GET['tab']) && $_GET['tab'] == "fdrivers")){
    $page_number = (int) $_GET['page'];
}else{
    $page_number = 1;
}
    
$pages = ceil($number_of_drivers_data / ITEMS_PER_PAGE) ;
if($page_number > $pages)$page_number = 1; 
if($page_number < 0)$page_number = 1; 
$offset = ($page_number - 1) * ITEMS_PER_PAGE;

//get transactions data
$query = sprintf('SELECT *,%1$stbl_drivers.driver_id AS driver_ids FROM %1$stbl_drivers 
LEFT JOIN %1$stbl_driver_location ON %1$stbl_driver_location.driver_id = %1$stbl_drivers.driver_id
WHERE %1$stbl_drivers.franchise_id %2$s ORDER BY %1$stbl_drivers.firstname ASC LIMIT %3$d, %4$d', DB_TBL_PREFIX, $query_modifier, $offset, ITEMS_PER_PAGE);

if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){
        while($row = mysqli_fetch_assoc($result)){
            $franchise_drivers_data[] = $row;
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
                        $params['tab'] = 'fdrivers';     // Overwrite if exists
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
                    <th>Photo</th>
                    <th>Driver ID</th>    
                    <th>Driver Name</th>
                    <th>Availability</th>
                    <th>Wallet Amount</th>
                    <th>Car Model</th>
                    <th>Car Plate Number</th>
                    <th>Actions</th>                       
                    </tr>
                </thead>
                <tbody>
                    <?php  
                    
                    
                    $count = 1 + (($page_number - 1) * ITEMS_PER_PAGE);
                    $default_currency_symbol = !empty($_SESSION['default_currency']) ? $_SESSION['default_currency']['symbol'] : "₦";
                    
                    foreach($franchise_drivers_data as $driverspageitems){
                        $drvr_id = str_pad($driverspageitems['driver_ids'] , 5, '0', STR_PAD_LEFT);
                        //$photo = explode('/',$driverspageitems['photo_file']);
                        $photo_file = isset($driverspageitems['photo_file']) ? $driverspageitems['photo_file'] : "0";
                        $driver_name = $driverspageitems['firstname'] . " " .  $driverspageitems['lastname'];
                        $driver_name_enc = urlencode($driver_name);

                        if((strtotime($driverspageitems['location_date'] . ' UTC') > (time() - LOCATION_INFO_VALID_AGE)) && !empty($driverspageitems['available'])){
                            $online_status = "<div title='Online' style='height:5px;background-color:#06d606;width: 32px;margin-top: 3px;'></div>";
                            $driver_online = 1;
                        }else{
                            $online_status = "<div title='Offline' style='height:5px;background-color:red;width: 32px;margin-top: 3px;'></div>";
                            $driver_online = 0;
                        }

                        if(isset($booked_drivers_data[$driverspageitems['driver_id']])){
                            $availability = "<i class='fa fa-info-circle' style='color:blue'></i> " . $booked_drivers_data[$driverspageitems['driver_id']]['status'];
                        }else{
                            $availability = !empty($driver_online) ? "<i class='fa fa-check-circle' style='color:#06d606'></i> Available" : "<i class='fa fa-times-circle' style='color:red'></i> Not Available";
                        }

                        
                        $location = "javascript:;";
                        $location_date = date('d/m/Y g:i A',strtotime($driverspageitems['location_date'] . ' UTC'));
                        $disabled = "disabled='disabled'";
                        if(!empty($driverspageitems['lat'])){
                            $location = "driver-location.php?name={$driver_name_enc}&long={$driverspageitems['long']}&lat={$driverspageitems['lat']}";
                            $disabled = '';
                        }
                        $view_driver = "<a href='view-driver.php?id=". $driverspageitems['driver_ids'] ."' class='btn btn-xs btn-warning'>View</a>";
                        if($_SESSION['account_type'] == 4){
                            $view_driver = "";
                        }

                        echo "<tr><td>". $count++ . "</td><td>"."<img style='display:block;' class='' width='32px' src='../photofile.php?file=". $photo_file ."' />{$online_status}"."</td><td>".$drvr_id."</td><td>".$driver_name . "<br>" . $driverspageitems['country_dial_code'] . " " . (!empty(DEMO) ? mask_string($driverspageitems['phone']) : $driverspageitems['phone']) . "</td><td>". $availability . "</td><td>".$driverspageitems['wallet_amount']."</td><td>".$driverspageitems['car_model']  ."</td><td>". $driverspageitems['car_plate_num']  ."</td><td>". $view_driver ." <a data-driverid = '{$driverspageitems['driver_ids']}' data-datetime='{$location_date}' data-lat='{$driverspageitems['lat']}' data-long='{$driverspageitems['long']}' {$disabled} href='#driver-location-map-container' class='btn btn-xs btn-success drvr-location' >Track Driver </a> <a data-drvrid = '".$driverspageitems['driver_ids'] ."' class='btn btn-xs btn-primary msg-drvr'>Message</a>" . "</td></tr>";
                    }
                    
                    ?>
                </tbody>
                </table>
            </div>
                                  
            <?php if(empty($franchise_drivers_data)){ echo "<h1 style='text-align:center;'>Nothing to Show. No Drivers Registered.</h1>";} ?>
                        
            
                            
        </div><!-- /.box-body -->
    </div>

    <div id="driver-location-map-container" style='display:none;' >
        <h4 id='location-update'></h4>
        <div style="width:500px;height:500px;" id="driver-location-map">
    </div>

    <script>

        var map = undefined; 
        var bounds = undefined; 
        var marker = undefined;                  
        var latLong = undefined;
        var mapOptions = undefined;
        var longitue;
        var latitude;
        var location_update_timer_id;
        var driver_id;
        


        if (typeof google === 'object' && typeof google.maps === 'object') {
            
            if(typeof mapOptions === 'undefined'){
                mapOptions = {
                center: new google.maps.LatLng(9.0338725,8.677457),
                zoom: 5,
                disableDefaultUI: false,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            map = new google.maps.Map(document.getElementById("driver-location-map"), mapOptions);
            directionsService = new google.maps.DirectionsService;
            directionsDisplay = new google.maps.DirectionsRenderer({
                map: map
            });
            bounds = new google.maps.LatLngBounds();
            latitude = 9.0338725;
            longitude = 8.677457;
            latLong = new google.maps.LatLng(latitude,longitude);
            marker = new google.maps.Marker({
                                                position: latLong,
                                                map: map
                                            });
                    

            }

            
        }


        jQuery('.drvr-location').on('click',function(){
                var drv_lat = $(this).data('lat');
                var drv_long = $(this).data('long');
                var location_date = $(this).data('datetime');
                driver_id = $(this).data('driverid');
                clearInterval(location_update_timer_id);
                location_update_timer_id = setInterval(updateDriverLocation,10000);
                $('#location-update').html('Last location update: ' + location_date);
                latLong = new google.maps.LatLng(drv_lat,drv_long);
                marker.setPosition(latLong);
                map.setZoom(18);
                map.panTo(marker.getPosition());
        })



        function updateDriverLocation(){
            if(!driver_id)return;
            var post_data = {'action':'getDriverLocation','driver_id' : driver_id};
            var search_data = [];
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

                    if(data_obj.hasOwnProperty('success')){
                        
                        $('#location-update').html('Last location update: ' + data_obj.location_date);
                        latLong = new google.maps.LatLng(data_obj.lat,data_obj.long);
                        marker.setPosition(latLong);
                        map.setZoom(18);
                        map.panTo(marker.getPosition());
                    
                    }  
        
                },
                error:function(jqXHR,textStatus, errorThrown){
                   return;
                }
                
            });


        }






</script>


