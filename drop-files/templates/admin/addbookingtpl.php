<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Create bookings for users. 
        </div>
    </div>
</div> <!--/Row-->


<div class="row">	
    <div class="col-sm-6" >
		<div class="box box-success">
                        
            <div class="box-body">
            <form  enctype="multipart/form-data" id="new-booking-form" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >

                    
                    <div class="form-group">
                        <div class="col-sm-12" id="zonemoderadio">
                            <b>Select Transport Scope</b> <br>
                            <label class="radio-inline">
                                <input <?php if(empty($_POST['zonetypeoption'])){echo 'checked';}elseif(isset($_POST['zonetypeoption']) && $_POST['zonetypeoption'] == 'city'){echo 'checked';} ?> type="radio" name="zonetypeoption" id="zone-intra-city" value="city"> Intra-City
                            </label>
                            <label class="radio-inline">
                                <input <?php echo isset($_POST['zonetypeoption']) && $_POST['zonetypeoption'] == 'state' ? 'checked' : ''; ?> type="radio" name="zonetypeoption" id="zone-inter-state" value="state"> Inter-state
                            </label>                            
                        </div>
                    </div>

                    

                    <div id="intra-city-select">

                        <div class="form-group">
                            <div class="col-sm-6" id="r-city">
                                <label for="route-city">Route </label>
                                <select class="form-control" id="route-city" name="route-city">
                                    <?php
                                        foreach($route_data as $routedata){
                                            if($routedata['r_scope'] == 1)continue;
                                            $selected = '';
                                            $style = '';
                                            if(!empty($new_customer_booking)){
                                                $selected = $routedata['r_id'] == $new_customer_booking['route_id'] ? "selected" : '';
                                                $style = $routedata['r_id'] == $new_customer_booking['route_id'] ? "font-weight:bold" : '';
                                            }
                                    ?> 
                                        <option style="<?php echo $style; ?>" <?php echo $selected; ?> data-cursym="<?php echo $routedata['symbol']; ?>" data-routeid="<?php echo $routedata['r_id']; ?>" data-lng="<?php echo $routedata['lng']; ?>" data-lat="<?php echo $routedata['lat']; ?>" value="<?php echo $routedata['r_id']; ?>"><?php echo $routedata['r_title']; ?></option>
                                    <?php
                                        }
                                    ?>
                                    
                                </select>
                                
                            </div>
                            
                        </div>

                       


                        <div class="form-group">
                            <div class="col-sm-6" id="city-rides">
                                <label for="route-city">Ride Type</label>
                                <select class="form-control" id="ride-type" name="ride-type">
                                                                        
                                </select>
                            </div>
                        </div>

                        

                        <div class="form-group">
                            <div class="col-sm-6">
                            <p><b>Pick-up Location</b></p>
                                <input  type="text"   required="required" class="form-control" id="pcity-zone" autocomplete="new-username" placeholder="Pick-up location" name="pcity-zone" value="<?php echo isset($_POST["pcity-zone"]) ? $_POST["pcity-zone"] : ''; ?>" >
                                
                                <p id="pcity-zone-coord"></p>
                                <input  type="text"  hidden="hidden" id="pcity-zone-long"  name="pcity-zone-long" value="<?php echo isset($_POST["pcity-zone-long"]) ? $_POST["pcity-zone-long"] : ''; ?>" >
                                <input  type="text"  hidden="hidden" id="pcity-zone-lat"   name="pcity-zone-lat" value="<?php echo isset($_POST["pcity-zone-lat"]) ? $_POST["pcity-zone-lat"] : ''; ?>" >
                            </div>

                            <div class="col-sm-6">
                            <p><b>Drop-off Location</b></p>
                                <input  type="text"   required="required" class="form-control" id="dcity-zone" autocomplete="new-username" placeholder="Drop-off location" name="dcity-zone" value="<?php echo isset($_POST["dcity-zone"]) ? $_POST["dcity-zone"] : ''; ?>" >
                                
                                <p id="dcity-zone-coord"></p>
                                <input  type="text"  hidden="hidden" id="dcity-zone-long"  name="dcity-zone-long" value="<?php echo isset($_POST["dcity-zone-long"]) ? $_POST["dcity-zone-long"] : ''; ?>" >
                                <input  type="text"  hidden="hidden" id="dcity-zone-lat"   name="dcity-zone-lat" value="<?php echo isset($_POST["dcity-zone-lat"]) ? $_POST["dcity-zone-lat"] : ''; ?>" >
                            </div>

                        </div>

                        <div class="form-group">                            
                            <div class="col-sm-6">
                                <div id="">
                                    <p><b>Price</b> (<b id="ccur-symbol"></b>)</p>
                                    <input  type="number"  min="0.00" step="0.01" required="required" class="form-control" id="cbooking-price" name="cbooking-price" value="<?php echo isset($_POST["cbooking-price"]) ? $_POST["cbooking-price"] : ''; ?>" > 
                                    <p id='ccomputed-price'></p>    
                                </div>
                                                                
                            </div>
                        </div>              


                    </div>



                    <div  id="inter-city-select">

                        <div class="form-group">
                            <div class="col-sm-6" id="r-state">
                                <label for="route-state">Route </label>
                                <select class="form-control" id="route-state" name="route-state">
                                <?php
                                        foreach($route_data as $routedata){
                                            if($routedata['r_scope'] == 0)continue;
                                    ?> 
                                        <option data-pickaddr="<?php echo $routedata['pick_name']; ?>" data-dropaddr="<?php echo $routedata['drop_name']; ?>" data-routeid="<?php echo $routedata['r_id']; ?>" data-cursym="<?php echo $routedata['symbol']; ?>" data-plng="<?php echo $routedata['pick_lng']; ?>" data-plat="<?php echo $routedata['pick_lat']; ?>" data-dlng="<?php echo $routedata['drop_lng']; ?>" data-dlat="<?php echo $routedata['drop_lat']; ?>" value="<?php echo $routedata['r_id']; ?>"><?php echo $routedata['r_title']; ?></option>
                                    <?php
                                        }
                                    ?>
                                </select>
                            </div>
                            


                        </div>

                        

                        <div class="form-group">
                            <div class="col-sm-6" id="state-rides">
                                <label for="route-city">Ride Type</label>
                                <select class="form-control" id="sride-type" name="sride-type">
                                                                        
                                </select>
                            </div>
                        </div>

                        

                        <div class="form-group" >
                            
                            <div class="col-sm-6">
                                <div id="">
                                <p><b>Pick-up City</b></p>
                                    <input  type="text"  readonly class="form-control" id="pcz" placeholder="Pick-up city" name="pcz" value="<?php echo isset($_POST["pcz"]) ? $_POST["pcz"] : ''; ?>" >
                                </div>
                                
                                <p id="pcz-coord"></p>
                                <input  type="text"  hidden="hidden" id="pcz-long"  name="pcz-long" value="<?php echo isset($_POST["pcz-long"]) ? $_POST["pcz-long"] : ''; ?>" >
                                <input  type="text"  hidden="hidden" id="pcz-lat"   name="pcz-lat" value="<?php echo isset($_POST["pcz-lat"]) ? $_POST["pcz-lat"] : ''; ?>" >
                            </div> 

                        
                            <div class="col-sm-6">
                                <div id="">
                                <p><b>Drop-off City</b></p>
                                    <input  type="text"  readonly class="form-control" id="dcz" placeholder="Drop-Off city" name="dcz" value="<?php echo isset($_POST["dcz"]) ? $_POST["dcz"] : ''; ?>" >
                                </div>
                                
                                <p id="dcz-coord"></p>
                                <input  type="text"  hidden="hidden" id="dcz-long"  name="dcz-long" value="<?php echo isset($_POST["dcz-long"]) ? $_POST["dcz-long"] : ''; ?>" >
                                <input  type="text"  hidden="hidden" id="dcz-lat"   name="dcz-lat" value="<?php echo isset($_POST["dcz-lat"]) ? $_POST["dcz-lat"] : ''; ?>" >
                            </div> 
                            
                        </div>

                        <div class="form-group">                            
                            <div class="col-sm-6">
                                <div id="">
                                    <p><b>Price</b> (<b id="cur-symbol"></b>)</p>
                                    <input  type="number"  min="0.00" step="0.01" required="required" class="form-control" id="booking-price" name="booking-price" value="<?php echo isset($_POST["booking-price"]) ? $_POST["booking-price"] : ''; ?>" > 
                                    <p id='computed-price'></p>    
                                </div>
                                                                
                            </div>
                        </div>


                                               

                    </div>


                    
                        <div class="form-group" >
                            
                            <div class="col-sm-6">
                                <div id="">
                                <p><b>Customer</b></p>
                                    <input  type="text" required="required" class="form-control" id="booking-customer" placeholder="" name="booking-customer" value="<?php if(!empty($new_customer_booking)){ echo $new_customer_booking['label'];}else{ echo isset($_POST["booking-customer"]) ? $_POST["booking-customer"] : '';}?>" >
                                    <input  type="text" hidden='hidden' id="booking-customerid" placeholder="" name="booking-customerid" value="<?php if(!empty($new_customer_booking)){ echo $new_customer_booking['value'];}else{ echo isset($_POST["booking-customerid"]) ? $_POST["booking-customerid"] : '';}?>" >
                                </div>
                               
                                
                            </div> 

                            <!-- <div class="col-sm-6">
                                <div id="">
                                <p><b>Driver</b></p>
                                    <input  type="text" class="form-control" id="booking-driver" placeholder="" name="booking-driver" value="<?php echo isset($_POST["booking-driver"]) ? $_POST["booking-driver"] : ''; ?>" >
                                    <input  type="text" hidden='hidden' id="booking-driverid" placeholder="" name="booking-driverid" value="<?php echo isset($_POST["booking-driverid"]) ? $_POST["booking-driverid"] : ''; ?>" >
                                </div>
                               
                                
                            </div> -->
                            
                                
                                                        
                        </div>

                        <hr>


                        <div class="form-group">
                            <div class="col-sm-12">
                                <p><b>Enable auto dispatch</b> <input type="checkbox" id="auto-dispatch" name="auto-dispatch"></p>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <p><b>Schedule booking</b> <input type="checkbox" id="schedule-bk" name="schedule-bk"></p>
                            </div>
                        </div>
                        <hr>


                        <div class="form-group" id="schedule-ride" style="display:none">
                            
                            <div class="col-sm-6">
                                <div id="">
                                <p><b>Pick-up Date</b></p>
                                    <input  type="text" class="form-control" name="date" id="datepickerbsearch" value="<?php echo date('Y-m-d'); ?>" >
                                </div>
                               
                                
                            </div> 

                            <div class="col-sm-6">
                                <div id="">
                                    <p><b>Pick-up Time</b></p>

                                    <div style="display:flex;flex-wrap:nowrap;">

                                        <select class="form-control" name="time-hour">
                                            <?php 
                                                $time_data = array( '00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23');
                                                $current_time_hour = date('G');
                                                $option_selected = '';
                                                foreach($time_data as $timedata){
                                                    $option_selected =  $timedata == $current_time_hour ? "selected" : '';
                                            ?>
                                            <option <?php echo $option_selected; ?> value="<?php echo $timedata;?>" ><?php echo $timedata;?></option> 
                                            <?php
                                                }
                                            ?>
                                            
                                        </select>
                                        
                                        <select class="form-control" name="time-min">
                                            <?php 
                                                $time_data = array( '00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49','50','51','52','53','54','55','56','57','58','59');
                                                $current_time_min = date('i');
                                                $option_selected = '';
                                                foreach($time_data as $timedata){
                                                    $option_selected =  $timedata == $current_time_min ? "selected" : '';
                                            ?>
                                            <option <?php echo $option_selected; ?> value="<?php echo $timedata;?>" ><?php echo $timedata;?></option> 
                                            <?php
                                                }
                                            ?>
                                            
                                        </select>
                                    </div>
                                </div>
                               
                                
                            </div>
                            
                                
                                                        
                        </div>


                        


                        <div class="form-group">                            
                            <div class="col-sm-6">
                                <div id="">
                                    <p><b>Payment Method</b></p>
                                    <select class="form-control" name="booking-paymethod">
                                        <option value="1" selected>Cash</option>
                                        <option value="2">Wallet</option>
                                    </select>  
                                </div>
                                                               
                            </div>
                        </div>



                    
                    
                    
                                        
                    <hr />
                    <button type="button" class="btn btn-primary btn-block" value="1" id="createbooking-btn" name="createbooking" >Create Booking</button> 
                </form>          
                           
      
      
      				            
            </div><!-- /.box-body -->
            
        </div>

    </div> <!--/col-sm-6-->



    <div class="col-sm-6" >
    	<div id = "map1" class="box box-info">
            <div class="box-header with-border">
              <h3 id="map1-header-title" class="box-title">Map</h3>             
            </div><!-- /.box-header -->
            
            <div class="box-body">
                <div id="booking-map1" style="height:400px"></div>
                



                   				            
            </div><!-- /.box-body -->
            
        </div>


        <div id = "map2" class="box box-info">
            <div class="box-header with-border">
              <h3 id="map2-header-title" class="box-title">Map</h3>             
            </div><!-- /.box-header -->
            
            <div class="box-body">
                
                <div id="booking-map2" style="height:400px;"></div>

                   				            
            </div><!-- /.box-body -->
            
        </div>

    </div> <!--/col-sm-6-->




</div>

	
    






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
    var intra_city_distance = 0;
    var intra_city_duration = 0;
    var intra_city_distance_text = "";
    var intra_city_duration_text = "";
    var inter_city_distance = 0;
    var inter_city_duration = 0;
    var inter_city_distance_text = "";
    var inter_city_duration_text = "";
    var cursym = "₦";


    

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
                    
        
        }
        
        
        if(typeof mapOptions2 === 'undefined'){
            mapOptions2 = {
            center: new google.maps.LatLng(9.0338725,8.677457),
            zoom: 5,
            disableDefaultUI: false,
            mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            map2 = new google.maps.Map(document.getElementById("booking-map2"), mapOptions2);
            directionsService2 = new google.maps.DirectionsService;
            directionsDisplay2 = new google.maps.DirectionsRenderer({
                map: map2
            });
            bounds = new google.maps.LatLngBounds();
        
        }
    }
     
    var route_id = jQuery('#route-city').val();
    getRouteRides(route_id, 0);

    var route_id = jQuery('#route-state').val();
    getRouteRides(route_id, 1);

    var cursymbol = jQuery('#route-city').find(':selected').data('cursym');
    if(cursymbol){
        cursym = cursymbol;
    }

    


    if(jQuery('#zone-intra-city').is(':checked')){
        jQuery('#intra-city-select').show();
        jQuery("#pcity-zone").removeAttr("disabled");
        jQuery("#dcity-zone").removeAttr("disabled");
        

        jQuery("#cbooking-price").removeAttr("disabled");
        jQuery("#booking-price").attr("disabled","disabled");
        
        jQuery('#ccur-symbol').html(cursym);
        
        jQuery('#map1').show();
        jQuery('#map2').hide();
        jQuery('#inter-city-select').hide();
    }else{
        jQuery('#intra-city-select').hide();
        jQuery("#pcity-zone").attr("disabled","disabled");
        jQuery("#dcity-zone").attr("disabled","disabled");

        jQuery("#booking-price").removeAttr("disabled");
        jQuery("#cbooking-price").attr("disabled","disabled");

        jQuery('#cur-symbol').html(cursym);
        
        jQuery('#map1').hide();
        jQuery('#map2').show();
        jQuery('#inter-city-select').show();
        var pick_addr =  jQuery("#route-state").find(':selected').data('pickaddr');
        var drop_addr =  jQuery("#route-state").find(':selected').data('dropaddr');
        jQuery('#pcz').val(pick_addr);
        jQuery('#dcz').val(drop_addr);

        var pick_lng =  jQuery("#route-state").find(':selected').data('plng');
        var pick_lat =  jQuery("#route-state").find(':selected').data('plat');
        var drop_lng =  jQuery("#route-state").find(':selected').data('dlng');
        var drop_lat =  jQuery("#route-state").find(':selected').data('dlat');

        jQuery('#pcz-long').val(pick_lng);
        jQuery('#pcz-lat').val(pick_lat);

        jQuery('#dcz-long').val(drop_lng);
        jQuery('#dcz-lat').val(drop_lat);
    }

    jQuery('#zonemoderadio').click(function() {
        if(jQuery('#zone-intra-city').is(':checked')){
            jQuery('#intra-city-select').show();
            jQuery("#pcity-zone").removeAttr("disabled");
            jQuery("#dcity-zone").removeAttr("disabled");

            jQuery("#cbooking-price").removeAttr("disabled");
            jQuery("#booking-price").attr("disabled","disabled");

            jQuery('#ccur-symbol').html(cursym);
            
            jQuery('#map1').show();
            jQuery('#map2').hide();
            jQuery('#inter-city-select').hide();

            let citycursymbol = jQuery('#route-city').find(':selected').data('cursym');
            if(citycursymbol){
                cursym = citycursymbol;
            }

        }else{
            jQuery('#intra-city-select').hide();
            jQuery('#map1').hide();
            jQuery('#map2').show();
            jQuery("#pcity-zone").attr("disabled","disabled");
            jQuery("#dcity-zone").attr("disabled","disabled");

            jQuery("#booking-price").removeAttr("disabled");
            jQuery("#cbooking-price").attr("disabled","disabled");

            jQuery('#cur-symbol').html(cursym);
            
            jQuery('#inter-city-select').show();
            var pick_addr =  jQuery("#route-state").find(':selected').data('pickaddr');
            var drop_addr =  jQuery("#route-state").find(':selected').data('dropaddr');
            jQuery('#pcz').val(pick_addr);
            jQuery('#dcz').val(drop_addr);

            let statecursymbol = jQuery('#route-state').find(':selected').data('cursym');
            if(statecursymbol){
                cursym = statecursymbol;
            }

            var pick_lng =  jQuery("#route-state").find(':selected').data('plng');
            var pick_lat =  jQuery("#route-state").find(':selected').data('plat');
            var drop_lng =  jQuery("#route-state").find(':selected').data('dlng');
            var drop_lat =  jQuery("#route-state").find(':selected').data('dlat');

            jQuery('#pcz-long').val(pick_lng);
            jQuery('#pcz-lat').val(pick_lat);

            jQuery('#dcz-long').val(drop_lng);
            jQuery('#dcz-lat').val(drop_lat);

            interStatePlot();
            


        }
    });  




     jQuery("#schedule-bk").on("click", function(){
        var schedule_enabled = jQuery(this).is(":checked");
        
        if(schedule_enabled){ 
            jQuery("#schedule-ride").fadeIn();
            
        }else{
            jQuery("#schedule-ride").fadeOut();
            
        }
     });




     jQuery('#route-state').on('change', function(){
        
        var route_id = jQuery(this).find(':selected').data('routeid');
        getRouteRides(route_id, 1);

        let statecursymbol = jQuery('#route-state').find(':selected').data('cursym');
        if(statecursymbol){
            cursym = statecursymbol;
        }
        
        jQuery('#cur-symbol').html(cursym);
        
        var pick_addr =  jQuery("#route-state").find(':selected').data('pickaddr');
        var drop_addr =  jQuery("#route-state").find(':selected').data('dropaddr');
        
        jQuery('#pcz').val(pick_addr);
        jQuery('#dcz').val(drop_addr);

        interStatePlot();
        
    });



    jQuery('#route-city').on('change', function(){
    
        var route_id = jQuery(this).find(':selected').data('routeid');
        getRouteRides(route_id,0); 

        let citycursymbol = jQuery('#route-city').find(':selected').data('cursym');
        if(citycursymbol){
            cursym = citycursymbol;
        }
        
        jQuery('#ccur-symbol').html(cursym);

        var puc =  jQuery("#ride-type").find(':selected').data('puc');
        var doc =  jQuery("#ride-type").find(':selected').data('doc');
        var cpm = jQuery("#ride-type").find(':selected').data('cpm');
        var cpk = jQuery("#ride-type").find(':selected').data('cpk');

        var npuc =  jQuery("#ride-type").find(':selected').data('npuc');
        var ndoc =  jQuery("#ride-type").find(':selected').data('ndoc');
        var ncpm = jQuery("#ride-type").find(':selected').data('ncpm');
        var ncpk = jQuery("#ride-type").find(':selected').data('ncpk');
        
        var nest_cost = parseFloat(npuc) + parseFloat(ndoc) + (parseFloat(ncpm) * parseFloat(intra_city_duration)) + (parseFloat(ncpk) * parseFloat(intra_city_distance));           

        var est_cost = parseFloat(puc) + parseFloat(doc) + (parseFloat(cpm) * parseFloat(intra_city_duration)) + (parseFloat(cpk) * parseFloat(intra_city_distance));           
        
        nest_cost = Math.round(nest_cost * 100) / 100;
        est_cost = Math.round(est_cost * 100) / 100;
        
        jQuery('#ccomputed-price').html("Estimated Cost (Day):" + cursym + est_cost + " | Estimated Cost (Night):" + cursym + nest_cost ); 
    
       
    });


    jQuery('#sride-type').on('change', function(){

        
    
        var puc =  jQuery("#sride-type").find(':selected').data('puc');
        var doc =  jQuery("#sride-type").find(':selected').data('doc');
        var cpm = jQuery("#sride-type").find(':selected').data('cpm');
        var cpk = jQuery("#sride-type").find(':selected').data('cpk');

        var npuc =  jQuery("#sride-type").find(':selected').data('npuc');
        var ndoc =  jQuery("#sride-type").find(':selected').data('ndoc');
        var ncpm = jQuery("#sride-type").find(':selected').data('ncpm');
        var ncpk = jQuery("#sride-type").find(':selected').data('ncpk');
        
        var nest_cost = parseFloat(npuc) + parseFloat(ndoc) + (parseFloat(ncpm) * parseFloat(intra_city_duration)) + (parseFloat(ncpk) * parseFloat(intra_city_distance));           

        var est_cost = parseFloat(puc) + parseFloat(doc) + (parseFloat(cpm) * parseFloat(intra_city_duration)) + (parseFloat(cpk) * parseFloat(intra_city_distance));           

        nest_cost = Math.round(nest_cost * 100) / 100;
        est_cost = Math.round(est_cost * 100) / 100;

        jQuery('#computed-price').html("Estimated Cost (Day):" + est_cost + " | Estimated Cost (Night):" + nest_cost ); 
    
    });



    jQuery('#ride-type').on('change', function(){

        if(!intra_city_duration || !intra_city_distance)return;

        var puc =  jQuery("#ride-type").find(':selected').data('puc');
        var doc =  jQuery("#ride-type").find(':selected').data('doc');
        var cpm = jQuery("#ride-type").find(':selected').data('cpm');
        var cpk = jQuery("#ride-type").find(':selected').data('cpk');

        var npuc =  jQuery("#ride-type").find(':selected').data('npuc');
        var ndoc =  jQuery("#ride-type").find(':selected').data('ndoc');
        var ncpm = jQuery("#ride-type").find(':selected').data('ncpm');
        var ncpk = jQuery("#ride-type").find(':selected').data('ncpk');
        
        var nest_cost = parseFloat(npuc) + parseFloat(ndoc) + (parseFloat(ncpm) * parseFloat(intra_city_duration)) + (parseFloat(ncpk) * parseFloat(intra_city_distance));           

        var est_cost = parseFloat(puc) + parseFloat(doc) + (parseFloat(cpm) * parseFloat(intra_city_duration)) + (parseFloat(cpk) * parseFloat(intra_city_distance));           

        nest_cost = Math.round(nest_cost * 100) / 100;
        est_cost = Math.round(est_cost * 100) / 100;
        
        jQuery('#ccomputed-price').html("Estimated Cost (Day):" + cursym + est_cost + " | Estimated Cost (Night):" + cursym + nest_cost );  

    });


    jQuery('#createbooking-btn').off().on('click', function(e){
        e.preventDefault();
        
        $('#createbooking-btn').prop("disabled", true);
        setTimeout(function(){
            $('#createbooking-btn').prop("disabled", false);
        },2000);
        
        let customer_id = jQuery('#booking-customerid').val();
        if(!customer_id){
            jQuery('#booking-customer').val('');
            imgurl = '../img/info_.gif?a=' + Math.random();                    
            swal({
                title: '<h1>Error</h1>',
                text: 'Select a customer for this booking from the auto-complete list that is displayed as you enter the customer name. ' ,
                imageUrl:imgurl,
                html:true
            });
            return;
        }

        jQuery('#new-booking-form').submit();

    })





    

    

     
     

    
    pczAutocomplete();
    dczAutocomplete(); 

    /* jQuery("#city-zone").focusout(function(){
        checkGeocode(document.getElementById("city-zone"));        
    })   */


    

    function czAutocomplete() {                            
        var input = document.getElementById('pcity-zone');
        var options = {
            componentRestrictions: {country: 'ng'},
            strictBounds: true
        };

        autocomplete = new google.maps.places.Autocomplete(input, options);
        
        google.maps.event.addListener(autocomplete, 'place_changed', function() {
            var place = autocomplete.getPlace();
            jQuery('#pcity-zone-long').val(place.geometry.location.lng());
            jQuery('#pcity-zone-lat').val(place.geometry.location.lat());
            jQuery('#pcity-zone-coord').html("Longitude: " + "<span style='color:red'>" + place.geometry.location.lng() + "</span> <br> Latitude:<span style='color:red'> " + place.geometry.location.lat() + "</span>");
            
            
            if(marker){
                marker.setMap(null);
                marker = [];
            }
            var latLong = new google.maps.LatLng(place.geometry.location.lat(), place.geometry.location.lng());
            marker = new google.maps.Marker({
            position: latLong,
            map: map1,
            animation: google.maps.Animation.DROP
            });
            marker.setMap(map1);
            map1.setZoom(7);
            //map.setCenter(marker.getPosition());
            map1.panTo(marker.getPosition());


        });
    }






    function pczAutocomplete() {                            
        var input = document.getElementById('pcity-zone');
        var options = {
            strictBounds: true
        };

        pczautocomplete = new google.maps.places.Autocomplete(input, options);
        
        google.maps.event.addListener(pczautocomplete, 'place_changed', function() {
            var place = pczautocomplete.getPlace();
            jQuery('#pcity-zone-long').val(place.geometry.location.lng());
            jQuery('#pcity-zone-lat').val(place.geometry.location.lat());
            jQuery('#pcity-zone-coord').html("Longitude: " + "<span style='color:red'>" + place.geometry.location.lng() + "</span> <br> Latitude:<span style='color:red'> " + place.geometry.location.lat() + "</span>");
            
            
            
            if(marker1){
                marker1.setMap(null);
                marker1 = [];
            }
            
            latLong1 = new google.maps.LatLng(place.geometry.location.lat(), place.geometry.location.lng());
            marker1 = new google.maps.Marker({
            position: latLong1,
            map: map1,
            animation: google.maps.Animation.DROP
            });
            marker1.setMap(map1);
            map1.setZoom(7);
            //map.setCenter(marker.getPosition());
            map1.panTo(marker1.getPosition());

            if(marker1 && marker2){
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
                       
                }, 2000);
                          
            }
            


        });
    }




    function dczAutocomplete() {                            
        var input = document.getElementById('dcity-zone');
        var options = {
            strictBounds: true
        };

        dczautocomplete = new google.maps.places.Autocomplete(input, options);
        
        google.maps.event.addListener(dczautocomplete, 'place_changed', function() {
            var place = dczautocomplete.getPlace();
            jQuery('#dcity-zone-long').val(place.geometry.location.lng());
            jQuery('#dcity-zone-lat').val(place.geometry.location.lat());
            jQuery('#dcity-zone-coord').html("Longitude: " + "<span style='color:red'>" + place.geometry.location.lng() + "</span> <br> Latitude:<span style='color:red'> " + place.geometry.location.lat() + "</span>");
            
            
            if(marker2){
                marker2.setMap(null);
                marker2 = [];
            }
            latLong2 = new google.maps.LatLng(place.geometry.location.lat(), place.geometry.location.lng());
            marker2 = new google.maps.Marker({
            position: latLong2,
            map: map1,
            animation: google.maps.Animation.DROP
            });
            marker2.setMap(map1);
            map1.setZoom(7);
            //map.setCenter(marker.getPosition());
            map1.panTo(marker2.getPosition());
            
            if(marker1 && marker2){
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
                    
                }, 2000);
                             
                
            }


        });
    }

   
   function calculateAndDisplayRoute(directionsService, directionsDisplay, pointA, pointB) {
        directionsService.route({
            origin: pointA,
            destination: pointB,
            avoidTolls: false,
            avoidHighways: false,
            unitSystem: google.maps.UnitSystem.METRIC,
            travelMode: google.maps.TravelMode.DRIVING
        }, function (response, status) {
            console.log(response.routes[0].legs[0].distance.text);
            inter_city_duration_text = response.routes[0].legs[0].duration.text;
            inter_city_distance_text = response.routes[0].legs[0].distance.text;

            inter_city_duration = response.routes[0].legs[0].duration.value / 60;
            inter_city_distance = response.routes[0].legs[0].distance.value / 1000;

            if (status == google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(response);
                jQuery("#map2-header-title").html("Map: Distance = " + response.routes[0].legs[0].distance.text + " Duration: " + response.routes[0].legs[0].duration.text);
                var puc =  jQuery("#sride-type").find(':selected').data('puc');
                var doc =  jQuery("#sride-type").find(':selected').data('doc');
                var cpm = jQuery("#sride-type").find(':selected').data('cpm');
                var cpk = jQuery("#sride-type").find(':selected').data('cpk');

                var npuc =  jQuery("#sride-type").find(':selected').data('npuc');
                var ndoc =  jQuery("#sride-type").find(':selected').data('ndoc');
                var ncpm = jQuery("#sride-type").find(':selected').data('ncpm');
                var ncpk = jQuery("#sride-type").find(':selected').data('ncpk');
                
                var nest_cost = parseFloat(npuc) + parseFloat(ndoc) + (parseFloat(ncpm) * parseFloat(inter_city_duration)) + (parseFloat(ncpk) * parseFloat(inter_city_distance));           

                var est_cost = parseFloat(puc) + parseFloat(doc) + (parseFloat(cpm) * parseFloat(inter_city_duration)) + (parseFloat(cpk) * parseFloat(inter_city_distance));           

                nest_cost = Math.round(nest_cost * 100) / 100;
                est_cost = Math.round(est_cost * 100) / 100;
                jQuery('#computed-price').html("Estimated Cost (Day):" + cursym + est_cost + " | Estimated Cost (Night):" + cursym + nest_cost ); 


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

            intra_city_duration = response.routes[0].legs[0].duration.text;
            intra_city_distance = response.routes[0].legs[0].distance.text;

            if (status == google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(response);
                jQuery("#map1-header-title").html("Map: Distance = " + response.routes[0].legs[0].distance.text + " Duration: " + response.routes[0].legs[0].duration.text);
                var puc =  jQuery("#ride-type").find(':selected').data('puc');
                var doc =  jQuery("#ride-type").find(':selected').data('doc');
                var cpm = jQuery("#ride-type").find(':selected').data('cpm');
                var cpk = jQuery("#ride-type").find(':selected').data('cpk');

                var npuc =  jQuery("#ride-type").find(':selected').data('npuc');
                var ndoc =  jQuery("#ride-type").find(':selected').data('ndoc');
                var ncpm = jQuery("#ride-type").find(':selected').data('ncpm');
                var ncpk = jQuery("#ride-type").find(':selected').data('ncpk');
                
                var nest_cost = parseFloat(npuc) + parseFloat(ndoc) + (parseFloat(ncpm) * parseFloat(intra_city_duration)) + (parseFloat(ncpk) * parseFloat(intra_city_distance));           

                var est_cost = parseFloat(puc) + parseFloat(doc) + (parseFloat(cpm) * parseFloat(intra_city_duration)) + (parseFloat(cpk) * parseFloat(intra_city_distance));           

                nest_cost = Math.round(nest_cost * 100) / 100;
                est_cost = Math.round(est_cost * 100) / 100;

                jQuery('#ccomputed-price').html("Estimated Cost (Day):" + cursym + est_cost + " | Estimated Cost (Night):" + cursym + nest_cost ); 



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


    


function interStatePlot(){

 
    var plng = jQuery('#route-state').find(':selected').data('plng');
    var plat = jQuery('#route-state').find(':selected').data('plat');

    var dlng = jQuery('#route-state').find(':selected').data('dlng');
    var dlat = jQuery('#route-state').find(':selected').data('dlat');

    if(marker3){
        marker3.setMap(null);
        marker3 = [];
    }

    if(marker4){
        marker4.setMap(null);
        marker4 = [];
    }

    setTimeout(() => {

        latLong3 = new google.maps.LatLng(parseFloat(plat), parseFloat(plng));
        marker3 = new google.maps.Marker({
            position: latLong3,
            map: map2,
            animation: google.maps.Animation.DROP
        });
        marker3.setMap(map2);
        map2.setZoom(7);
        //map.setCenter(marker.getPosition());
        map2.panTo(marker3.getPosition());


        setTimeout(() => {
        latLong4 = new google.maps.LatLng(parseFloat(dlat), parseFloat(dlng));
        marker4 = new google.maps.Marker({
            position: latLong4,
            map: map2,
            animation: google.maps.Animation.DROP
        });
        marker4.setMap(map2);
        map2.setZoom(7);
        //map.setCenter(marker.getPosition());
        map2.panTo(marker4.getPosition());


        setTimeout(function(){
        bounds.extend(latLong3);
        bounds.extend(latLong4);
        var pointA = latLong3;
        var pointB = latLong4;                
        map2.fitBounds(bounds);
        
        bounds = [];
        bounds = new google.maps.LatLngBounds();  
        calculateAndDisplayRoute(directionsService2, directionsDisplay2, pointA, pointB);
        marker3.setMap(null);
        marker4.setMap(null);
    }, 1500);
        
    }, 1500);

                    
    }, 1500);



}



function getRouteRides(route_id,citystate){
    //console.log(route_rides);
    if(typeof route_rides.result === 'object'){
        
        if(citystate){
            jQuery('#sride-type').html(route_rides.result[route_id].cars_html);
        }else{

            jQuery('#ride-type').html(route_rides.result[route_id].cars_html);
        }
        return;
    }

    //call ajax to get the rides available for this route
    var post_data = {'action':'getrouterides','route_id':route_id};       
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            timeout : 5000,            
            data: post_data,
            success: function (data, status)
            {
                //jQuery('#busy').modal('hide');
                //console.log(data);

                try{
                    var data_obj = JSON.parse(data);
                }catch(e){
                    imgurl = '../img/info_.gif?a=' + Math.random();                    
                    swal({
                        title: '<h1>Error</h1>',
                        text: 'Cannot get rides for the selected route!' ,
                        imageUrl:imgurl,
                        html:true
                    });
                    return;
                }

                if(data_obj.hasOwnProperty('error')){                    
                    imgurl = '../img/info_.gif?a=' + Math.random();                    
                    swal({
                        title: '<h1>Error</h1>',
                        text: data_obj.error ,
                        imageUrl:imgurl,
                        html:true
                    });
                    return;
                }


                if(data_obj.hasOwnProperty('success')){ 
                    console.log(data_obj);
                    
                    route_rides = data_obj;
                    
                    if(citystate){

                        jQuery('#sride-type').html(route_rides.result[route_id].cars_html);
                    }else{
                        jQuery('#ride-type').html(route_rides.result[route_id].cars_html);
                    }
                    return;
                }


               
                
                
            },
            error: function() {                                
                
                imgurl = '../img/info_.gif?a=' + Math.random();                    
                swal({
                    title: '<h1>Error</h1>',
                    text: data_obj.notloggedin ,
                    imageUrl:imgurl,
                    html:true
                });
                return;
            }

        });

        


}




</script>













