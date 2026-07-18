<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
            View user details. 
        </div>
    </div>
</div> <!--/Row-->

<div class="row">
    <div class="col-sm-12"> 
        <?php
            $photo_file = isset($user_page_items['photo_file']) ? $user_page_items['photo_file'] : "0";
        ?>
        <div class="box box-default">
            <div class="box-header with-border">
            <h3 class="box-title">Details | <?php echo !empty($user_page_items['is_activated']) ? " <span style='color:green;'>Activated</span>" : " <span style='color:red;'>Not Activated</span>"; ?></h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                
                
                    <div class="col-sm-12">
                        <div class="col-sm-2">
                            <img src="<?php echo empty($photo_file) ? "../img/usersample.jpg" : "../photofile.php?file=". $photo_file;?>" class="img-circle img-responsive" />
                            <br />
                        </div>
                        <div class="col-sm-10">
                            <div class="spacer"></div>
                            <h2 style="margin-top:0;"><?php echo ucwords(strtolower($user_page_items['firstname']) . " " . strtolower($user_page_items['lastname']));echo !empty($user_page_items['available']) ? " <span style='font-weight:bold;color:green;font-size:14px;'>[Online]</span>" : " <span style='font-size:14px;font-weight:bold;color:red;'>[Offline]</span>";?></h2>
                            
                            <img style="width:100px;" src="../img/rating-<?php echo empty($user_page_items['drv_address']) ? "1.png" : $user_page_items['driver_rating'] . ".png"; ?>" class="" /><br>
                            <h5><?php echo $user_page_items['drv_address'] . ", " . $user_page_items['state']. ", " . $user_page_items['drv_country'] . "."; ?></h5>
                            <a href="modify-drvr.php?id=<?php echo  $user_page_items['driver_id']; ?>" class="btn btn-primary btn-sm">Edit Profile</a>
                            <br>
                        </div>
                    </div>
                    <div class="col-sm-3" style="border-left:thin solid #ccc;">               
                    <h5>Last seen: <?php echo !empty($user_page_items['location_date']) ? date('d/m/Y g:i A',strtotime($user_page_items['location_date'] . ' UTC')) : "---"; ?></h5>
                        <h5>Phone: <?php echo $user_page_items['phone']; ?></h5>
                        <h5>Email: <?php echo $user_page_items['email']; ?></h5>
                        <h5>Franchise: <?php echo $user_page_items['franchise_name']; ?></h5>
                        <h5>Commision: <?php echo $user_page_items['driver_commision']; ?>%</h5>
                        <h5>Wallet Amount: <?php echo $_SESSION['default_currency']['symbol'] . $user_page_items['wallet_amount']; ?></h5>
                    </div>
                    <div class="col-sm-3" style="border-left:thin solid #ccc;"> 
                        <h2 style="margin-top:0;"></h2>
                        <h5>Car Plate #: <?php echo $user_page_items['car_plate_num']; ?></h5>
                        <h5>Car Reg. #: <?php echo $user_page_items['car_reg_num']; ?></h5>
                        <h5>Car Model: <?php echo $user_page_items['car_model']; ?></h5>
                        <h5>Ride Type: <?php echo $user_page_items['ride_type']; ?></h5>
                        <h5>Color: <?php echo $user_page_items['car_color']; ?></h5>
                        <h5>Operational City: <?php echo $user_page_items['r_title']; ?></h5>
                    </div>
                    <div class="col-sm-3" style="border-left:thin solid #ccc;">                
                        <h5>Bank Account Name: <?php echo $user_page_items['d_bank_acc_holder_name']; ?></h5>
                        <h5>Bank Name: <?php echo !empty($user_page_items['d_bank_name']) ? $user_page_items['d_bank_name'] : ''; ?></h5>
                        <h5>Account Number: <?php echo $user_page_items['d_bank_acc_num']; ?></h5>
                        <h5>Swift / BIC Code: <?php echo $user_page_items['d_bank_swift_code']; ?></h5>
                    </div>
                


            
            
            </div><!-- /.box-body -->
        </div>


    </div><!--/col-sm-12-->    
</div>






<div class="row">	
    <div class="col-sm-12" >
    

        <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="<?php echo $active_tab == 0 ? 'active' : ''?>"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Transactions</a></li>
                    <li class="<?php echo $active_tab == 1 ? 'active' : ''?>"><a href="#tab_2" data-toggle="tab" aria-expanded="false">Bookings</a></li>
                    <li class="<?php echo $active_tab == 2 ? 'active' : ''?>"><a href="#tab_3" data-toggle="tab" aria-expanded="false">Withdrawal Requests</a></li>
                    <li class="<?php echo $active_tab == 3 ? 'active' : ''?>"><a href="#tab_4" data-toggle="tab" aria-expanded="false">Reviews</a></li>                            
                    <li class="<?php echo $active_tab == 4 ? 'active' : ''?>"><a href="#tab_5" data-toggle="tab" aria-expanded="false">Documents</a></li>                            
                </ul>
                
                <div class="tab-content">
                    <div class="tab-pane <?php echo $active_tab == 0 ? 'active' : ''?>" id="tab_1">
                        <?php include('../../drop-files/templates/admin/viewdrivertransactionstpl.php'); ?>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane <?php echo $active_tab == 1 ? 'active' : ''?>" id="tab_2">
                        <?php include('../../drop-files/templates/admin/viewdriverbookingstpl.php'); ?>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane <?php echo $active_tab == 2 ? 'active' : ''?>" id="tab_3">
                        <?php include('../../drop-files/templates/admin/viewdriverwithdrawalstpl.php'); ?>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane <?php echo $active_tab == 3 ? 'active' : ''?>" id="tab_4">
                        <?php include('../../drop-files/templates/admin/viewdriverreviewstpl.php'); ?>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane <?php echo $active_tab == 4 ? 'active' : ''?>" id="tab_5">
                        <?php include('../../drop-files/templates/admin/viewdriverdocumentstpl.php'); ?>
                    </div>
                    <!-- /.tab-pane -->
                </div>
                <!-- /.tab-content -->           
        </div>

        
		

    </div> <!--/col-sm-12-->
    <div id="driver-location-map-container" style='display:none;' >
        <h4 id='location-update'></h4>
        <div style="width:500px;height:500px;" id="driver-location-map">
    </div>
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


var active_tab = $('.nav-tabs li.active a').attr('href');
var active_tab_url = location.href;
var default_url = "<?php echo $_SERVER['SCRIPT_NAME'] . "?id={$id}"; ?>";



$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
  var el = e.target;
  var tab_href = $(el).attr('href');
  if(tab_href && tab_href == active_tab){
    history.replaceState({},'',active_tab_url);
  }else{
    switch(tab_href){
        case '#tab_1':
        history.replaceState({},'',default_url + '&tab=dtransactions');    
        break;
        case '#tab_2':
        history.replaceState({},'',default_url + '&tab=dbookings');    
        break;
        case '#tab_3':
        history.replaceState({},'',default_url + '&tab=dwithdraw');    
        break;
        case '#tab_4':
        history.replaceState({},'',default_url + '&tab=dreviews');    
        break;
        case '#tab_5':
        history.replaceState({},'',default_url + '&tab=ddocuments');    
        break;
    };
  }
})


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





















