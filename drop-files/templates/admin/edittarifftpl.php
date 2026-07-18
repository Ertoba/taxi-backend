<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Edit a tariff. 
        </div>
    </div>
</div> <!--/Row-->


<div class="row">	
    <div class="col-sm-6" >
        <form  enctype="multipart/form-data" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
            <input  type="text" id="route-id" hidden="hidden" name="route-id" value="<?php echo isset($tariff_data['id']) ? $tariff_data['id'] : ''; ?>" />
            <div class="box box-success">
                <div class="box-header with-border">
                <h3 class="box-title">City Details</h3>             
                </div><!-- /.box-header -->
                
                <div class="box-body">
                    
                        <?php 
                        
                            if(!empty($tariff_data['r_scope'])){
                                $inter_state = "checked";
                                $intra_city = ""; 
                            }else{
                                $inter_state = "";
                                $intra_city = "checked";
                            }                    
                        
                        ?>
                        <div class="form-group">
                            <div class="col-sm-12" id="zonemoderadio">
                            <p><b>Select Transport Scope</b></p>
                                <label class="radio-inline">
                                    <input <?php echo $intra_city; ?> type="radio" name="zonetypeoption" id="zone-intra-city" value="city"> Intra-City
                                </label>
                                <label class="radio-inline">
                                    <input <?php echo $inter_state; ?> type="radio" name="zonetypeoption" id="zone-inter-state" value="state"> Inter-state
                                </label>                            
                            </div>
                        </div> 

                        
                        <div class="form-group">
                            <div class="col-sm-6">
                                <label for="tariff-name"><span style="color:red">*</span>Title</label>
                                <input  type="text"  required class="form-control" id="route-title" placeholder="Enter title for this tariff. Must be unique." name="route-title" value="<?php echo isset($tariff_data['r_title']) ? $tariff_data['r_title'] : ''; ?>" >
                                
                            </div>                        
                            
                        </div>

                        
                        <p><span style="color:red">*</span><b>City location</b></p>
                        
                        <div class="form-group" id="intra-city-select">
                            <div class="col-sm-12"><p>Enter a city name and select it from the google places autocomplete dropdown. Then draw a polygon precicely around the boundary of the city </p></div>
                            <div class="col-sm-6">
                                <input  type="text"  disabled="disabled" required="required" class="form-control" id="city-zone" autocomplete="new-password" placeholder="Enter a city" name="city-zone" value="<?php echo isset($tariff_data['c_name']) ? $tariff_data['c_name'] : ''; ?>" >
                                <br>
                                <p id="city-zone-coord"><?php echo isset($tariff_data['lat']) ? "Longitude: <span style='color:red;'>".$tariff_data['lng']."</span><br>"."Latitude: <span style='color:red;'>".$tariff_data['lat'] . "</span>" : ''; ?></p>
                                <input  type="text"  hidden="hidden" id="city-zone-long"  name="city-zone-long" value="<?php echo isset($tariff_data['lng']) ? $tariff_data['lng'] : ''; ?>" >
                                <input  type="text"  hidden="hidden" id="city-zone-lat"   name="city-zone-lat" value="<?php echo isset($tariff_data['lat']) ? $tariff_data['lat'] : ''; ?>" >

                                <div>
                                    <p id='city-boundary-status' style='font-weight:bold;padding:10px;color:white;background-color:#333;text-align:center;'>City boundary not set</p>
                                </div>

                                <input  type="text"  hidden="hidden" id="city-boundary-data"  name="city-boundary-data" value="<?php echo isset($tariff_data['city_bound_coords']) ? $tariff_data['city_bound_coords'] : ''; ?>" >
                            </div>
                            <br>
                            

                        </div>

                        <div class="form-group" id="inter-city-select" >
                            <div class="col-sm-12"><p>Select a pickup city from the list then enter the pickup location within the city and then enter a dropoff location</p></div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <div class="col-sm-6">
                                        <p><b>Pickup city</b></p>                                      
                                    
                                        <select class="form-control" id="preset-pickup-city" name="preset-pickup-city">
                                            <option value="0" selected='selected'>---</option>
                                            <?php
                                                foreach($intra_city_data as $intracitydata){
                                                    $selected = '';
                                                    if($intracitydata['id'] == $tariff_data['pickup_city_id']){
                                                        $selected = "selected";
                                                    }
                                                    echo "<option data-coords='{$intracitydata['city_bound_coords']}' value='{$intracitydata['id']}' {$selected}>{$intracitydata['r_title']}</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                            </div>                            
                            <div class="col-sm-6">
                                <div id="">
                                    <input  type="text"  disabled="disabled" required="required" class="form-control" id="pcz" placeholder="Pick-up city location" name="pcz" value="<?php echo isset($tariff_data['pick_name']) ? $tariff_data['pick_name'] : ''; ?>" >
                                </div>
                                <br>
                                <p id="pcz-coord"><?php echo isset($tariff_data['pick_lng']) ? "Longitude: <span style='color:red;'>".$tariff_data['pick_lng']."</span><br>"."Latitude: <span style='color:red;'>".$tariff_data['pick_lat']."</span>" : ''; ?></p>
                                <input  type="text"  hidden="hidden" id="pcz-long"  name="pcz-long" value="<?php echo isset($tariff_data['pick_lng']) ? $tariff_data['pick_lng'] : ''; ?>" >
                                <input  type="text"  hidden="hidden" id="pcz-lat"   name="pcz-lat" value="<?php echo isset($tariff_data['pick_lat']) ? $tariff_data['pick_lat'] : ''; ?>" >
                            </div> 

                        
                            <div class="col-sm-6">
                                <div id="">
                                    <input  type="text"  disabled="disabled" required="required" class="form-control" id="dcz" placeholder="Drop-Off city location" name="dcz" value="<?php echo isset($tariff_data['drop_name']) ? $tariff_data['drop_name'] : ''; ?>" >
                                </div>
                                <br>
                                <p id="dcz-coord"><?php echo isset($tariff_data['drop_lng']) ? "Longitude: <span style='color:red;'>".$tariff_data['drop_lng']."</span><br>"."Latitude: <span style='color:red;'>".$tariff_data['drop_lat']."</span>" : ''; ?></p>
                                <input  type="text"  hidden="hidden" id="dcz-long"  name="dcz-long" value="<?php echo isset($tariff_data['drop_lng']) ? $tariff_data['drop_lng'] : ''; ?>" >
                                <input  type="text"  hidden="hidden" id="dcz-lat"   name="dcz-lat" value="<?php echo isset($tariff_data['drop_lat']) ? $tariff_data['drop_lat'] : ''; ?>" >
                            </div> 
                            
                        </div>


                        <div class="form-group">

                            <div class="col-sm-6">
                                <p><b>Ditance Unit</b></p>
                                <p>Choose a distance unit. KM | mi.</p>

                                <select class="form-control" id="route-dist-unit" name="route-dist-unit">
                                    <option value="0" <?php echo isset($tariff_data['dist_unit']) && $tariff_data['dist_unit'] == "0" ? "selected='selected'" : "";?> >Kilometers (KM)</option>
                                    <option value="1" <?php echo isset($tariff_data['dist_unit']) && $tariff_data['dist_unit'] == "1" ? "selected='selected'" : "";?> >Miles (mi)</option>

                                </select>
                            </div>

                        </div>


                        <br>

                </div><!-- /.box-body -->                        
            </div>


            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">City Currency</h3>             
                </div><!-- /.box-header -->
                
                <div class="box-body">

                    <div class="form-group">

                        <div class="col-sm-6">
                            <p><b>Select Currency</b></p>
                            <p>Choose a currency for this city for ride charges and payments.</p>
                            
                            <select class="form-control" id="route-currency" name="route-currency">
                            <?php
                                foreach($currency_page_items as $currencypageitems){
                                    $default = "";
                                    $style = "";
                                    $selected = $tariff_data['city_currency_id'] == $currencypageitems['id'] ? "selected = 'selected'" : "";
                                    if(!empty($currencypageitems['default'])){
                                        $default = "[Default]";
                                        $style = "style='font-weight:bold;'";
                                    }
                                    echo "<option {$selected} {$style} value='{$currencypageitems['id']}'>{$currencypageitems['symbol']} {$currencypageitems['name']} - {$currencypageitems['iso_code']}</option>";
                                }
                            ?>
                            </select>
                        </div>
                    
                    </div>

                </div><!-- /.box-body -->                        
            </div>


            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">City Car Tariffs</h3>             
                </div><!-- /.box-header -->            
                
                
                <div class="box-body">
                                            
                        <?php 
                            $count = 0;
                            foreach($rides_array as $ridesarray){
                                $count ++;
                                $ride_identifier = "ride_type_".$count;
                                $ride_selected = !empty($rides_data[$ridesarray['id']])? "checked" : "";
                                $pp_enabled = !empty($rides_data[$ridesarray['id']]['pp_enabled'])? 1 : 0;
                                $pp_selected = !empty($pp_enabled) ? "checked" : "";
                                $pp_show = !empty($pp_enabled) ? "" : "display:none;";
                                
                                $elem_state =  !empty($rides_data[$ridesarray['id']])? "" : "disabled='disabled'";
                                $tariff_show = !empty($rides_data[$ridesarray['id']])? "" : "display:none;";

                                $elem_state_hourlyrate =  "disabled='disabled'";
                                if(!empty($rides_data[$ridesarray['id']])){
                                    if($rides_data[$ridesarray['id']]['hr_enabled']){
                                        $elem_state_hourlyrate = "";
                                    }
                                }
                                
                        ?>

                                <div class="form-group" >
                                    <div class="col-sm-12">
                                        <label style="font-size: 20px;" for="<?php echo $ride_identifier; ?>"><?php echo $ridesarray['ride_type']; ?></label>
                                            <input  data-item="<?php echo $count; ?>" <?php echo $ride_selected; ?> type="checkbox" class="ride-type-chkbox" id="<?php echo $ride_identifier; ?>" name="<?php echo $ride_identifier; ?>">
                                            <br>
                                    </div>
                                    <div id="<?php echo "ride-tariffs-".$count ?>" class=" col-sm-12 <?php echo "ride-tariffs-".$count ?>" style="<?php echo $tariff_show;?>">

                                        <div class="form-group">
                                            <div class="col-sm-12">                                                
                                                <div style="margin-top:15px;font-weight:bold;">Compute Fare with Trip Meter <input <?php echo !empty($rides_data[$ridesarray['id']]['cfare_enabled']) ? "checked":"";?> type="checkbox" class="t-input" id="<?php echo "faretype-".$ridesarray['id']; ?>" name="<?php echo "faretype-".$ridesarray['id']; ?>"></div>
                                                <p style="margin-top: 15px;margin-bottom: 10px;">When enabled, trip fare will be calculated using the actual total distance travelled and time spent on a ride as recorded by the trip meter on the driver app. Disable to use a fixed estimated fare calculated using trip duration and distance provided by Google Maps API</p>
                                                
                                            </div>

                                            <div class="col-sm-12">
                                                <div style="margin-top:15px;font-weight:bold;">Enable Ride Sharing <input <?php echo !empty($rides_data[$ridesarray['id']]['rshare_enabled']) ? "checked":"";?> type="checkbox" class="t-input" id="<?php echo "rideshare-".$ridesarray['id']; ?>" name="<?php echo "rideshare-".$ridesarray['id']; ?>"></div>
                                                <p style="margin-top: 30px;margin-bottom: 10px;">Ride sharing enables different riders from different locations travelling to various destinations share a ride and save costs.</p>
                                                
                                            </div>
                                            
                                            <div class="col-sm-12">                                                    
                                                <div style="margin-top:15px;font-weight:bold;">Enable Hourly Rate <input <?php echo !empty($rides_data[$ridesarray['id']]['hr_enabled']) ? "checked":"";?> data-item="<?php echo $count; ?>" type="checkbox" class="hourlyrate-en-chkbox t-input" id="<?php echo "hourlyrate-".$ridesarray['id']; ?>" name="<?php echo "hourlyrate-".$ridesarray['id']; ?>"></div>
                                                <p style="margin-top: 30px;margin-bottom: 10px;">Enabling hourly rates allows customers to be billed hourly on <b>Quick-Rides</b> for this vehicle. Set the cost per hour and also set a distance in KM. When customers travel for a time or distance less than the values you set they will be charged the amount you set; this is the minimum. If they travel a greater time or distance then they will be charged this value with additional equivalent cost in fractions of an hour (minutes) if its not up to a whole hour extra</p>
                                                <div class="row" id="hourly-rate-set-<?php echo $count;?>" style="<?php echo !empty($rides_data[$ridesarray['id']]['hr_enabled']) ? "":"display:none;";?>">
                                                    <div class="col-sm-12">
                                                        <div class="row">
                                                            <div class="col-sm-3">
                                                                <p style="margin-top: 15px;margin-bottom: 2px;">Cost per hour</p>
                                                                <input  type="number"  min="0" step="0.01" <?php echo $elem_state_hourlyrate ?> required= "required" class="form-control hourlyrate-t-input" id="<?php echo "cph-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "cph-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['hr_cph']) ? $rides_data[$ridesarray['id']]['hr_cph'] : ''; ?>" > 
                                                            </div>

                                                            <div class="col-sm-3">
                                                                <p style="margin-top: 15px;margin-bottom: 2px;">Distance (KM)</p>
                                                                <input  type="number"  min="1" step="0.1" <?php echo $elem_state_hourlyrate ?> required= "required" class="form-control hourlyrate-t-input" id="<?php echo "cphdist-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "cphdist-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['hr_dist']) ? $rides_data[$ridesarray['id']]['hr_dist'] : ''; ?>" > 
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-sm-12">
                                                                <p style="margin: 30px 0 0 0;"><b>Night time hourly rate (<?php echo NIGHT_START.":00 to " . NIGHT_END . ":00" ?>)</b></p>
                                                            </div>
                                                            <div class="col-sm-3">
                                                                <p style="margin-top: 15px;margin-bottom: 2px;">Cost per hour</p>
                                                                <input  type="number"  min="0" step="0.01" <?php echo $elem_state_hourlyrate ?> required= "required" class="form-control hourlyrate-t-input" id="<?php echo "ncph-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "ncph-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['nhr_cph']) ? $rides_data[$ridesarray['id']]['nhr_cph'] : ''; ?>" > 
                                                            </div>

                                                            <div class="col-sm-3">
                                                                <p style="margin-top: 15px;margin-bottom: 2px;">Distance (KM)</p>
                                                                <input  type="number"  min="1" step="0.1" <?php echo $elem_state_hourlyrate ?> required= "required" class="form-control hourlyrate-t-input" id="<?php echo "ncphdist-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "ncphdist-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['nhr_dist']) ? $rides_data[$ridesarray['id']]['nhr_dist'] : ''; ?>" > 
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                                <hr>
                                                
                                            </div>


                                            <div class="col-sm-12">
                                                <p style="font-weight:bold;margin-top: 30px;margin-bottom: 2px;<?php echo count($rides_array) > 1 ? "" : "display:none;";?>">Alternate vehicles</p>
                                                <p style="margin-bottom: 10px">When riders choose this vehicle category and it is not available, drivers using any of the alternate vehicles you choose will be allocated the ride if available.</p>
                                                <?php 
                                                
                                                    foreach($rides_array as $ride){ 
                                                        $checked = "";
                                                        if($ride['id'] == $ridesarray['id'])continue;
                                                        if(!empty($rides_data[$ridesarray['id']]['alt_cars'])){
                                                            $alt_cars = explode(",",$rides_data[$ridesarray['id']]['alt_cars']);
                                                            $checked = in_array($ride['id'], $alt_cars) ? "checked" : "";
                                                        }
                                                ?>
                                                    <label style="font-weight: normal;" for="<?php echo "altv-".$ridesarray['id'] . "-" . $ride['id']; ?>"><?php echo $ride['ride_type']; ?></label>
                                                    <input style="margin-right:10px;" <?php echo $checked?> type="checkbox" class="t-input" id="<?php echo "altv-".$ridesarray['id'] . "-" . $ride['id']; ?>" name="<?php echo "altv-".$ridesarray['id'] . "-" . $ride['id']; ?>">
                                                <?php } ?>
                                            </div>

                                        </div>
                                                                                
                                        
                                        <div class="form-group">
                                            <input  class="t-input" type="text"  hidden="hidden"  <?php echo $elem_state; ?> name="car_type[]" value="<?php echo $ridesarray['id']; ?>" >
                                            <div class="col-sm-3">
                                                <p style="margin-top: 15px;margin-bottom: 2px;">Pick-up cost</p>
                                                <input  type="number"  min="0.00" step="0.01" <?php echo $elem_state; ?> required= "required" class="form-control t-input" id="<?php echo "pcr-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "pcr-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['pickup_cost']) ? $rides_data[$ridesarray['id']]['pickup_cost'] : ''; ?>" > 
                                            </div>

                                            <div class="col-sm-3">
                                                <p style="margin-top: 15px;margin-bottom: 2px;">Base distance (KM)</p>
                                                <input  type="number"  min="0.0" step="0.1" <?php echo $elem_state; ?> required= "required" class="form-control t-input" id="<?php echo "ind-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "ind-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['init_distance']) ? $rides_data[$ridesarray['id']]['init_distance'] : ''; ?>" > 
                                            </div>


                                            <div class="col-sm-3">
                                                <p style="margin-top: 15px;margin-bottom: 2px;">Drop-off cost</p>
                                                <input  type="number"  min="0.00" step="0.01" <?php echo $elem_state; ?> required= "required" class="form-control t-input" id="<?php echo "dcr-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "dcr-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['drop_off_cost']) ? $rides_data[$ridesarray['id']]['drop_off_cost'] : ''; ?>" > 
                                            </div>

                                            <div class="col-sm-3">
                                                <p style="margin-top: 15px;margin-bottom: 2px;">Cost per Kilometer</p>
                                                <input  type="number"  min="0.00" step="0.01" <?php echo $elem_state; ?> required= "required" class="form-control t-input" id="<?php echo "cpkr-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "cpkr-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['cost_per_km']) ? $rides_data[$ridesarray['id']]['cost_per_km'] : ''; ?>" > 
                                            </div>

                                            <div class="col-sm-3">
                                                <p style="margin-top: 15px;margin-bottom: 2px;">Cost per Minute</p>
                                                <input  type="number"  min="0.00" step="0.01" <?php echo $elem_state; ?> required= "required" class="form-control t-input" id="<?php echo "cpmr-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "cpmr-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['cost_per_minute']) ? $rides_data[$ridesarray['id']]['cost_per_minute'] : ''; ?>" > 
                                            </div>

                                            <div class="col-sm-3">
                                                <p style="margin-top: 15px;margin-bottom: 2px;">Cancel Cost</p>
                                                <input  type="number"  min="0.00" step="0.01" <?php echo $elem_state; ?> required= "required" class="form-control t-input" id="<?php echo "cc-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "cc-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['cancel_cost']) ? $rides_data[$ridesarray['id']]['cancel_cost'] : ''; ?>" > 
                                            </div>

                                            <div class="col-sm-3">
                                                <p style="margin-top: 15px;margin-bottom: 2px;">Wait time</p>
                                                <input  type="number"  min="0" step="1" <?php echo $elem_state; ?> required= "required" class="form-control t-input" id="<?php echo "wt-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "wt-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['wait_time']) ? $rides_data[$ridesarray['id']]['wait_time'] : ''; ?>" > 
                                            </div>

                                            <div class="col-sm-3">
                                                <p style="margin-top: 15px;margin-bottom: 2px;">Wait Cost per Minute</p>
                                                <input  type="number"  min="0.00" step="0.01" <?php echo $elem_state; ?> required= "required" class="form-control t-input" id="<?php echo "wcpmr-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "wcpmr-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['wait_cost_per_minute']) ? $rides_data[$ridesarray['id']]['wait_cost_per_minute'] : ''; ?>" > 
                                            </div>

                                            

                                        </div>

                                        <div class="clearfix"></div>
                                        <hr>

                                        <div class="col-sm-12">
                                            <p><b>Night time tariff (<?php echo NIGHT_START.":00 to " . NIGHT_END . ":00" ?>)</b></p>
                                        </div>

                                        <div class="form-group">
                                            <div class="col-sm-3">
                                                <p style="margin-top: 15px;margin-bottom: 2px;">Pick-up cost</p>
                                                <input  type="number"  min="0.00" step="0.01"  <?php echo $elem_state; ?> required= "required" class="form-control t-input" id="<?php echo "npcr-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "npcr-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['npickup_cost']) ? $rides_data[$ridesarray['id']]['npickup_cost'] : ''; ?>" > 
                                            </div>

                                            <div class="col-sm-3">
                                                <p style="margin-top: 15px;margin-bottom: 2px;">Base distance (KM)</p>
                                                <input  type="number"  min="0.0" step="0.1" <?php echo $elem_state; ?> required= "required" class="form-control t-input" id="<?php echo "nind-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "nind-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['init_distance_n']) ? $rides_data[$ridesarray['id']]['init_distance_n'] : ''; ?>" > 
                                            </div>

                                            <div class="col-sm-3">
                                                <p style="margin-top: 15px;margin-bottom: 2px;">Drop-off cost</p>
                                                <input  type="number"  min="0.00" step="0.01"  <?php echo $elem_state; ?> required= "required" class="form-control t-input" id="<?php echo "ndcr-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "ndcr-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['ndrop_off_cost']) ? $rides_data[$ridesarray['id']]['ndrop_off_cost'] : ''; ?>" > 
                                            </div>

                                            <div class="col-sm-3">
                                                <p style="margin-top: 15px;margin-bottom: 2px;">Cost per Kilometer</p>
                                                <input  type="number"  min="0.00" step="0.01"  <?php echo $elem_state; ?> required= "required" class="form-control t-input" id="<?php echo "ncpkr-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "ncpkr-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['ncost_per_km']) ? $rides_data[$ridesarray['id']]['ncost_per_km'] : ''; ?>" > 
                                            </div>

                                            <div class="col-sm-3">
                                                <p style="margin-top: 15px;margin-bottom: 2px;">Cost per Minute</p>
                                                <input  type="number"  min="0.00" step="0.01"  <?php echo $elem_state; ?> required= "required" class="form-control t-input" id="<?php echo "ncpmr-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "ncpmr-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['ncost_per_minute']) ? $rides_data[$ridesarray['id']]['ncost_per_minute'] : ''; ?>" > 
                                            </div>

                                            <div class="col-sm-3">
                                                <p style="margin-top: 15px;margin-bottom: 2px;">Cancel Cost</p>
                                                <input  type="number"  min="0.00" step="0.01"  <?php echo $elem_state; ?> required= "required" class="form-control t-input" id="<?php echo "ncc-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "ncc-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['ncancel_cost']) ? $rides_data[$ridesarray['id']]['ncancel_cost'] : ''; ?>" > 
                                            </div>

                                            <div class="col-sm-3">
                                                <p style="margin-top: 15px;margin-bottom: 2px;">Wait time</p>
                                                <input  type="number"  min="0" step="1" <?php echo $elem_state; ?> required= "required" class="form-control t-input" id="<?php echo "nwt-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "nwt-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['nwait_time']) ? $rides_data[$ridesarray['id']]['nwait_time'] : ''; ?>" > 
                                            </div>

                                            <div class="col-sm-3">
                                                <p style="margin-top: 15px;margin-bottom: 2px;">Wait Cost per Minute</p>
                                                <input  type="number"  min="0.00" step="0.01" <?php echo $elem_state; ?> required= "required" class="form-control t-input" id="<?php echo "nwcpmr-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "nwcpmr-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['nwait_cost_per_minute']) ? $rides_data[$ridesarray['id']]['nwait_cost_per_minute'] : ''; ?>" > 
                                            </div>
                                        </div>

                                        <div class="clearfix"></div>
                                        <hr>

                                        <div class="col-sm-12">
                                            <p><b>Peak period charges</b> <input  <?php echo $pp_selected; ?> data-item="<?php echo $count; ?>" type="checkbox" class="ride-type-pp-chkbox t-input" id="<?php echo "pp-enable-" .$ridesarray['id']; ?>" name="<?php echo "pp-enable-".$ridesarray['id']; ?>"></p>
                                        </div>

                                        
                                        <div id="<?php echo "pp-ride-tariffs-".$count ?>" class="col-sm-12 <?php echo "pp-ride-tariffs-".$count ?>" style="<?php echo $pp_show; ?>padding:0;">
                                            <div class="form-group">
                                                <div class="col-sm-6">
                                                    <p style="margin-top: 15px;margin-bottom: 2px;">Start time</p>
                                                    
                                                    <select <?php echo $pp_enabled ? "" : "disabled='disabled'" ?> class="form-control pp-t-input" required= "required" id="<?php echo "ppst-".$ridesarray['id']; ?>" name="<?php echo "ppst-".$ridesarray['id']; ?>">
                                                        <option value="0" <?php echo isset($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "0"? "selected='selected'" : "";?> >12:00 AM</option> 
                                                        <option value="1" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "1"? "selected='selected'" : "";?> >1:00 AM</option> 
                                                        <option value="2" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "2"? "selected='selected'" : "";?> >2:00 AM</option> 
                                                        <option value="3" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "3"? "selected='selected'" : "";?> >3:00 AM</option> 
                                                        <option value="4" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "4"? "selected='selected'" : "";?> >4:00 AM</option> 
                                                        <option value="5" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "5"? "selected='selected'" : "";?> >5:00 AM</option> 
                                                        <option value="6" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "6"? "selected='selected'" : "";?> >6:00 AM</option> 
                                                        <option value="7" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "7"? "selected='selected'" : "";?> >7:00 AM</option> 
                                                        <option value="8" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "8"? "selected='selected'" : "";?> >8:00 AM</option> 
                                                        <option value="9" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "9"? "selected='selected'" : "";?> >9:00 AM</option> 
                                                        <option value="10" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "10" ? "selected='selected'" : "";?> >10:00 AM</option> 
                                                        <option value="11" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "11" ? "selected='selected'" : "";?> >11:00 AM</option> 
                                                        <option value="12" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "12" ? "selected='selected'" : "";?> >12:00 PM</option> 
                                                        <option value="13" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "13" ? "selected='selected'" : "";?> >1:00 PM</option> 
                                                        <option value="14" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "14" ? "selected='selected'" : "";?> >2:00 PM</option> 
                                                        <option value="15" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "15" ? "selected='selected'" : "";?> >3:00 PM</option> 
                                                        <option value="16" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "16" ? "selected='selected'" : "";?> >4:00 PM</option> 
                                                        <option value="17" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "17" ? "selected='selected'" : "";?> >5:00 PM</option> 
                                                        <option value="18" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "18" ? "selected='selected'" : "";?> >6:00 PM</option> 
                                                        <option value="19" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "19" ? "selected='selected'" : "";?> >7:00 PM</option> 
                                                        <option value="20" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "20" ? "selected='selected'" : "";?> >8:00 PM</option> 
                                                        <option value="21" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "21" ? "selected='selected'" : "";?> >9:00 PM</option> 
                                                        <option value="22" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "22" ? "selected='selected'" : "";?> >10:00 PM</option> 
                                                        <option value="23" <?php echo !empty($rides_data[$ridesarray['id']]['pp_start']) && $rides_data[$ridesarray['id']]['pp_start'] == "23" ? "selected='selected'" : "";?> >11:00 PM</option>                                                                                                      
                                                    </select>
                                                </div>

                                                <div class="col-sm-6">
                                                    <p style="margin-top: 15px;margin-bottom: 2px;">End time</p>
                                                    
                                                    <select <?php echo $pp_enabled ? "" : "disabled='disabled'" ?> class="form-control pp-t-input" required= "required" id="<?php echo "ppet-".$ridesarray['id']; ?>" name="<?php echo "ppet-".$ridesarray['id']; ?>">
                                                        <option value="0" <?php echo isset($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "0" ? "selected='selected'" : "";?> >12:00 AM</option> 
                                                        <option value="1" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "1" ? "selected='selected'" : "";?> >1:00 AM</option> 
                                                        <option value="2" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "2" ? "selected='selected'" : "";?> >2:00 AM</option> 
                                                        <option value="3" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "3" ? "selected='selected'" : "";?> >3:00 AM</option> 
                                                        <option value="4" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "4" ? "selected='selected'" : "";?> >4:00 AM</option> 
                                                        <option value="5" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "5" ? "selected='selected'" : "";?> >5:00 AM</option> 
                                                        <option value="6" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "6" ? "selected='selected'" : "";?> >6:00 AM</option> 
                                                        <option value="7" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "7" ? "selected='selected'" : "";?> >7:00 AM</option> 
                                                        <option value="8" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "8" ? "selected='selected'" : "";?> >8:00 AM</option> 
                                                        <option value="9" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "9" ? "selected='selected'" : "";?> >9:00 AM</option> 
                                                        <option value="10" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "10" ? "selected='selected'" : "";?> >10:00 AM</option> 
                                                        <option value="11" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "11" ? "selected='selected'" : "";?> >11:00 AM</option> 
                                                        <option value="12" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "12" ? "selected='selected'" : "";?> >12:00 AM</option> 
                                                        <option value="13" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "13" ? "selected='selected'" : "";?> >1:00 PM</option> 
                                                        <option value="14" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "14" ? "selected='selected'" : "";?> >2:00 PM</option> 
                                                        <option value="15" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "15" ? "selected='selected'" : "";?> >3:00 PM</option> 
                                                        <option value="16" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "16" ? "selected='selected'" : "";?> >4:00 PM</option> 
                                                        <option value="17" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "17" ? "selected='selected'" : "";?> >5:00 PM</option> 
                                                        <option value="18" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "18" ? "selected='selected'" : "";?> >6:00 PM</option> 
                                                        <option value="19" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "19" ? "selected='selected'" : "";?> >7:00 PM</option> 
                                                        <option value="20" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "20" ? "selected='selected'" : "";?> >8:00 PM</option> 
                                                        <option value="21" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "21" ? "selected='selected'" : "";?> >9:00 PM</option> 
                                                        <option value="22" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "22" ? "selected='selected'" : "";?> >10:00 PM</option> 
                                                        <option value="23" <?php echo !empty($rides_data[$ridesarray['id']]['pp_end']) && $rides_data[$ridesarray['id']]['pp_end'] == "23" ? "selected='selected'" : "";?> >11:00 PM</option>                                                                                                      
                                                    </select>
                                                </div>

                                                <?php
                                                    
                                                    //get and decode active days
                                                    $days_array = array(1=>"",2=>"",3=>"",4=>"",5=>"",6=>"",7=>"");
                                                    if(!empty($rides_data[$ridesarray['id']]['pp_active_days'])){
                                                        $active_days=[];
                                                        $active_days = json_decode($rides_data[$ridesarray['id']]['pp_active_days'],1);
                                                        if(!json_last_error()){
                                                            foreach($active_days as $activedays){
                                                                $days_array[$activedays] = "selected='selected'";
                                                            }
                                                        }
                                                        

                                                    } 
                                                ?>


                                                <div class="col-sm-12">
                                                    <p style="margin-top: 15px;margin-bottom: 2px;">Active days</p>
                                                    <select <?php echo $pp_enabled ? "" : "disabled='disabled'" ?> class="form-control select-box pp-t-input" multiple="multiple" data-placeholder="Select days" id="<?php echo "ppad-".$ridesarray['id']."[]"; ?>" name="<?php echo "ppad-".$ridesarray['id']."[]"; ?>" style="width: 100%;">
                                                        <option value="1" <?php echo $days_array[1]; ?> >Monday</option>
                                                        <option value="2" <?php echo $days_array[2]; ?> >Tuesday</option>
                                                        <option value="3" <?php echo $days_array[3]; ?> >Wednesday</option>
                                                        <option value="4" <?php echo $days_array[4]; ?> >Thursday</option>
                                                        <option value="5" <?php echo $days_array[5]; ?> >Friday</option>
                                                        <option value="6" <?php echo $days_array[6]; ?> >Saturday</option>
                                                        <option value="7" <?php echo $days_array[7]; ?> >Sunday</option>
                                                    </select>
                                                </div>
                                                

                                                <div class="col-sm-6">
                                                    <p style="margin-top: 15px;margin-bottom: 2px;">Charge type</p>
                                                    <select <?php echo $pp_enabled ? "" : "disabled='disabled'" ?> class="form-control pp-t-input" placeholder="Select charge type" id="<?php echo "ppct-".$ridesarray['id']; ?>" name="<?php echo "ppct-".$ridesarray['id']; ?>" style="width: 100%;">
                                                        <option value="0" <?php echo isset($rides_data[$ridesarray['id']]['pp_charge_type']) && $rides_data[$ridesarray['id']]['pp_charge_type'] == "0" ? "selected='selected'" : "";?> >Nominal</option>
                                                        <option value="1" <?php echo isset($rides_data[$ridesarray['id']]['pp_charge_type']) && $rides_data[$ridesarray['id']]['pp_charge_type'] == "1" ? "selected='selected'" : "";?> >Multiplier</option>                                                            
                                                    </select>
                                                </div>

                                                <div class="clearfix"></div>

                                                <div class="col-sm-3">
                                                    <p style="margin-top: 15px;margin-bottom: 2px;">Charge</p>
                                                    <input  <?php echo $pp_enabled ? "" : "disabled='disabled'" ?> type="number"  min="0.00" step="0.01" required= "required" class="form-control pp-t-input" id="<?php echo "ppchrge-".$ridesarray['id']; ?>" placeholder="" name="<?php echo "ppchrge-".$ridesarray['id']; ?>" value="<?php echo isset($rides_data[$ridesarray['id']]['pp_charge_value']) ? $rides_data[$ridesarray['id']]['pp_charge_value'] : "";?>" > 
                                                </div>
                                            </div>
                                        </div>


                                    </div>                                
                                </div>
                                <hr>
                                <br>        

                        <?php
                            }                    
                        ?>

                                            
                            
                                    
                </div><!-- /.box-body -->
                
            </div>

            <hr />
            <button type="submit" class="btn btn-primary btn-block" value="1" name="updatezone" >Update</button> 
        </form> 

    </div> <!--/col-sm-6-->



    <div class="col-sm-6" >
    	<div id = "map1" class="box box-info">
            <div class="box-header with-border">
              <h3 id="map1-header-title" class="box-title">Map</h3>             
            </div><!-- /.box-header -->
            
            <div class="box-body">
                <div id="tariff-map" style="height:400px"></div>
                



                   				            
            </div><!-- /.box-body -->

            <div style="padding: 5px;text-align:center;">
                <div style="display:inline;" id="inter-city-draw-controls">
                    <button class="btn btn-sm btn-primary select-poly" style="margin:5px;">Select</button>
                    <button class="btn btn-sm btn-info draw-poly" style="margin:5px;">Draw</button>
                    <button class="btn btn-sm btn-danger remove-poly" style="margin:5px;" >Remove</button>
                </div>
            </div>
            
        </div>


        <div id = "map2" class="box box-info">
            <div class="box-header with-border">
              <h3 id="map2-header-title" class="box-title">Map</h3>             
            </div><!-- /.box-header -->
            
            <div class="box-body">                
                <div id="tariff-map2" style="height:400px;"></div>                   				            
            </div><!-- /.box-body -->
            
        </div>

    </div> <!--/col-sm-6-->




</div>

	
    






<script>

     var map = undefined; 
     var map2 = undefined;
     var bounds = undefined; 
     var marker1 = undefined;
     var marker = undefined;                  
     var marker2 = undefined; 
     var latLong = undefined;
     var latLong1 = undefined;
     var latLong2 = undefined;
     var directionsService = undefined;
     var directionsDisplay = undefined;
     var city_circle;
     var dist_unit = 0;
     var city_radius_span = 5000; //meters
     var pczautocomplete;
     var city_bounds;
     

     var drawingManager;
    var selectedShape;
    var gmarkers = [];
    var coordinates = [];
    var allShapes = [];
    var shapeColor = "#007cff";
    var default_lat = 0.0;
    var default_lng = 0.0;
    var city_polygon_json = '<?php echo isset($tariff_data['city_bound_coords']) ? $tariff_data['city_bound_coords'] : '[]'; ?>';
    var pickup_city_polygon_json = '{}';
    var drop_city_polygon_json = '{}';
    var city_polygon_data = null;    
    var pickup_city_polygon_data = JSON.parse(pickup_city_polygon_json.replace(/&quot;/g,'"'));
    var drop_city_polygon_data = JSON.parse(drop_city_polygon_json.replace(/&quot;/g,'"'));
    var selectedPolygon;
    const NUM_OF_POLYGONS_INTERSTATE = 2;
    var max_polygon_num_reached = false;

    try{
        city_polygon_data = JSON.parse(city_polygon_json.replace(/&quot;/g,'"'));
    }catch(e){
        city_polygon_data = [];
    }

        
     

    if($('#route-dist-unit').val() == 1){ //miles
        dist_unit = 1;
    }else{ //KM
        dist_unit = 0;
    }
        

    $('#route-dist-unit').on('change',function(){
        
        if($('#route-dist-unit').val() == 1){ //miles
            dist_unit = 1;
        }else{ //KM
            dist_unit = 0;
        }

    })




    $('#preset-pickup-city').on('change', function(){
        
        $('#pcz').val('');
        $('#pcz-lat').val('');
        $('#pcz-long').val('');
        $('#pcz-coord').text('');

        let city_coords_data = $('#preset-pickup-city option:selected').data('coords');
        if(!city_coords_data)return;
        if(!city_coords_data.hasOwnProperty('coords'))return;
        //console.log(coords);

        city_bounds = new google.maps.LatLngBounds();  
        city_coords_data.coords.forEach(function(val,indx){
            city_bounds.extend(val);
        })
        pczautocomplete.setBounds(city_bounds);

    })



    
     
    if(jQuery('#zone-intra-city').is(':checked')){
        jQuery('#intra-city-select').show();
        jQuery("#city-zone").removeAttr("disabled");
        jQuery("#pcz").attr("disabled","disabled");
        jQuery("#dcz").attr("disabled","disabled");
        jQuery('#map1').show();
        jQuery('#map2').hide();
        jQuery('#inter-city-select').hide();
    }else{
        jQuery('#intra-city-select').hide();
        jQuery("#city-zone").attr("disabled","disabled");
        jQuery("#pcz").removeAttr("disabled");
        jQuery("#dcz").removeAttr("disabled");
        jQuery('#map1').hide();
        jQuery('#map2').show();
        jQuery('#inter-city-select').show();
    }

    jQuery('#zonemoderadio').click(function() {
        if(jQuery('#zone-intra-city').is(':checked')){
            jQuery('#intra-city-select').show();
            jQuery("#city-zone").removeAttr("disabled");
            jQuery("#pcz").attr("disabled","disabled");
            jQuery("#dcz").attr("disabled","disabled");
            jQuery('#map1').show();
            jQuery('#map2').hide();
            jQuery('#inter-city-select').hide();
        }else{
            jQuery('#intra-city-select').hide();
            jQuery('#map1').hide();
            jQuery('#map2').show();
            jQuery("#city-zone").attr("disabled","disabled");
            jQuery("#pcz").removeAttr("disabled");
            jQuery("#dcz").removeAttr("disabled");
            jQuery('#inter-city-select').show();
        }
     });
     
    

     
     jQuery(".ride-type-chkbox").on("click", function(){
        var ride_chkbox_enabled = jQuery(this).is(":checked");
        var item_id = jQuery(this).data('item');
        if(ride_chkbox_enabled){ 
            jQuery("#ride-tariffs-" + item_id).fadeIn();
            jQuery("#ride-tariffs-" + item_id + " .t-input").each(function(){
                jQuery(this).removeAttr("disabled");
            });

            var ride_chkbox_pp_enabled = jQuery("#pp-enable-" + item_id).is(":checked");
            
            if(ride_chkbox_pp_enabled){ 
                jQuery("#pp-ride-tariffs-" + item_id + " .pp-t-input").each(function(){
                    jQuery(this).removeAttr("disabled");
                });
            }
            


        }else{
            jQuery("#ride-tariffs-" + item_id).fadeOut();
            /* jQuery("#pp-ride-tariffs-" + item_id).fadeOut(); */
            /* jQuery("#pp_ride_type_" + item_id).prop('checked', false); */
            jQuery("#ride-tariffs-" + item_id + " .t-input").each(function(){
                jQuery(this).attr("disabled","disabled");
            });

            jQuery("#pp-ride-tariffs-" + item_id + " .pp-t-input").each(function(){
                jQuery(this).attr("disabled","disabled");
            }); 
            
            
            
        }
    });


        jQuery(".ride-type-pp-chkbox").on("click", function(){
            var ride_chkbox_pp_enabled = jQuery(this).is(":checked");
            var item_id = jQuery(this).data('item');
            if(ride_chkbox_pp_enabled){ 
                jQuery("#pp-ride-tariffs-" + item_id).fadeIn();
                jQuery("#pp-ride-tariffs-" + item_id + " .pp-t-input").each(function(){
                    jQuery(this).removeAttr("disabled");
                });
            }else{
                jQuery("#pp-ride-tariffs-" + item_id).fadeOut();
                jQuery("#pp-ride-tariffs-" + item_id + " .pp-t-input").each(function(){
                    jQuery(this).attr("disabled","disabled");
                });
            }
        });


        jQuery(".hourlyrate-en-chkbox").on("click", function(){
            var hr_chkbox_enabled = jQuery(this).is(":checked");
            var item_id = jQuery(this).data('item');
            if(hr_chkbox_enabled){ 
                jQuery("#hourly-rate-set-" + item_id).fadeIn();
                jQuery("#hourly-rate-set-" + item_id + " .hourlyrate-t-input").each(function(){
                    jQuery(this).removeAttr("disabled");
                });
            }else{
                jQuery("#hourly-rate-set-" + item_id).fadeOut();
                jQuery("#hourly-rate-set-" + item_id + " .hourlyrate-t-input").each(function(){
                    jQuery(this).attr("disabled","disabled");
                });
            }
        });


    czAutocomplete();
    pczAutocomplete();
    dczAutocomplete(); 

    /* jQuery("#city-zone").focusout(function(){
        checkGeocode(document.getElementById("city-zone"));        
    })   */

    if($('#preset-pickup-city option:selected').val()){
        let city_coords_data = $('#preset-pickup-city option:selected').data('coords');
        
        if(city_coords_data && city_coords_data.hasOwnProperty('coords')){
            city_bounds = new google.maps.LatLngBounds();  
            city_coords_data.coords.forEach(function(val,indx){
                city_bounds.extend(val);
            })
            pczautocomplete.setBounds(city_bounds);
        }
        //console.log(coords);

        
    }


    if (typeof google === 'object' && typeof google.maps === 'object') {
        
      if(typeof mapOptions === 'undefined'){
          mapOptions = {
          center: new google.maps.LatLng(9.0338725,8.677457),
          zoom: 5,
          disableDefaultUI: false,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        map = new google.maps.Map(document.getElementById("tariff-map"), mapOptions);

                

      }


      if(typeof mapOptions2 === 'undefined'){
          mapOptions2 = {
          center: new google.maps.LatLng(9.0338725,8.677457),
          zoom: 5,
          disableDefaultUI: false,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        map2 = new google.maps.Map(document.getElementById("tariff-map2"), mapOptions2);
        directionsService = new google.maps.DirectionsService;
        directionsDisplay = new google.maps.DirectionsRenderer({
            map: map2
        });
        bounds = new google.maps.LatLngBounds();

      }
    }



    if(typeof map !== 'undefined'){

        var polygonOptions = {
            strokeWeight: 1,
            fillOpacity: 0.4,
            fillColor: '#00ff00',
            editable: true,
            draggable: true
        };

        // Initialize Drawing Manager
        drawingManager = new google.maps.drawing.DrawingManager({
            drawingMode: null,
            drawingControl: false, //disable the maps drawing controls
            drawingControlOptions: {
                position: google.maps.ControlPosition.RIGHT_CENTER,
                drawingModes: ['polygon'] //  you can also add: 'marker', 'polyline', 'rectangle', 'circle'
            },
            polygonOptions: polygonOptions,
            map: map
        });

        $('#inter-city-draw-controls .select-poly').on('click', function(){
            drawingManager.setDrawingMode(null);
        });

        $('#inter-city-draw-controls .draw-poly').on('click', function(){
            drawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYGON);
        });

        $('#inter-city-draw-controls .remove-poly').on('click', function(){
            deleteSelectedPolygon();
        });

        google.maps.event.addListener(drawingManager, 'overlaycomplete', function(e) {

            var newShape = e.overlay;
            // console.log(newShape);

            if(allShapes.length == 1){
                newShape.setMap(null);
                imgurl = '../img/info_.gif?a=' + Math.random();                    
                swal({
                    title: '<h1>Error</h1>',
                    text: 'You have already drawn a city boundary. Edit the boundary instead.',
                    imageUrl:imgurl,
                    html:true
                });
                return;
            }

            if(!marker){
                imgurl = '../img/info_.gif?a=' + Math.random();                    
                swal({
                    title: '<h1>Error</h1>',
                    text: 'Cannot add city boundary! Please enter a city name first then draw the boudary around the city marker' ,
                    imageUrl:imgurl,
                    html:true
                });
                newShape.setMap(null);
                return;
            }else if(!google.maps.geometry.poly.containsLocation(marker.getPosition(),newShape)){
                
                imgurl = '../img/info_.gif?a=' + Math.random();                    
                swal({
                    title: '<h1>Error</h1>',
                    text: 'City center not inside boundary. Please draw the city boundary around the city center / marker.' ,
                    imageUrl:imgurl,
                    html:true
                });
                newShape.setMap(null);
                return;
            }

            

            let coord_data_json = JSON.stringify(getPolyGonCoordinates(newShape));
            console.log(coord_data_json);
            $('#city-boundary-data').val(coord_data_json);    

            allShapes.push(newShape); // save the newly created shape to the allShapes list

            console.log(allShapes);

            let lat_lng = [];

            allShapes.forEach(function(data, index) {
                lat_lng[index] = getPolyGonCoordinates(data);
                // console.log(lat_lng);
            });            

            if(allShapes[0]){//pickup city boundary
                newShape.setOptions({ fillColor: '#00ff00' }); // color form with the current value of shapeColor
                $('#city-boundary-status').css('background-color','#00ff00');
                $('#city-boundary-status').text('City boundary set');
                let coord_data_json = JSON.stringify(lat_lng[0]);
                console.log(coord_data_json);
                $('#city-boundary-data').val(coord_data_json);

            }else{
                $('#city-boundary-status').css('background-color','#333');
                $('#city-boundary-status').text('City boundary not set');
                $('#city-boundary-data').val('');
            }
            
            /* if(allShapes[1]){ //drop-off city boundary
                newShape.setOptions({ fillColor: '#ff0000' }); // color form with the current value of shapeColor
                $('#d-boundary-status').css('background-color','#ff0000');
                $('#d-boundary-status').text('Boundary set');
                let coord_data_json = JSON.stringify(lat_lng[1]);
                console.log(coord_data_json);
                $('#d-boundary-data').val(coord_data_json);
            }else{
                $('#d-boundary-status').css('background-color','#333');
                $('#d-boundary-status').text('Boundary not set');
                $('#d-boundary-data').val('');
            } */

            getPolyGonCoordinates(newShape); // find coordinates peaks
            
            // exit drawing mode after completion of the polygon
            drawingManager.setDrawingMode(null);

            setSelectedPolygon(newShape);

            
            // select polygon at "click"
            google.maps.event.addListener(newShape, 'click', function(e) {
                console.log('clicked');
                if (e.vertex !== undefined) {
                    return;
                    //remove polygon vertex when the vertex is clicked
                    var path = newShape.getPaths().getAt(e.path);
                    if (path.length < 4) {
                        return;
                    }
                    path.removeAt(e.vertex);
                    getPolyGonCoordinates(newShape);
                    if (path.length < 3) {
                        newShape.setMap(null);
                    }
                }
                setSelectedPolygon(newShape);
            });


            google.maps.event.addListener(newShape, 'mouseup', function() {
                
                for (i=0; i < allShapes.length; i++) { 
                    if (newShape.getPath() == allShapes[i].getPath()) {
                        allShapes[i] = newShape;                        
                        let coord_data_json = JSON.stringify(getPolyGonCoordinates(newShape));
                        console.log(coord_data_json);
                        $('#city-boundary-data').val(coord_data_json);                       
                        

                    }
                }                
            });


            //update coordinates
            google.maps.event.addListener(newShape, 'click', function(e) { getPolyGonCoordinates(newShape); });
            google.maps.event.addListener(newShape, "dragend", function(e) {
                for (i=0; i < allShapes.length; i++) {
                    if (newShape.getPath() == allShapes[i].getPath()) {

                        if(!google.maps.geometry.poly.containsLocation(marker.getPosition(),newShape)){
                
                            imgurl = '../img/info_.gif?a=' + Math.random();                    
                            swal({
                                title: '<h1>Error</h1>',
                                text: 'City center not inside boundary. Ensure the city boundary encompasses the city center / marker.' ,
                                imageUrl:imgurl,
                                html:true
                            });
                            
                            $('#city-boundary-data').val('');
                            $('#city-boundary-status').css('background-color','#333');
                            $('#city-boundary-status').text('City boundary not set');
                            
                            return;
                        }

                        allShapes[i] = newShape;
                        
                        let coord_data_json = JSON.stringify(getPolyGonCoordinates(newShape));
                        console.log(coord_data_json);                        
                        $('#city-boundary-status').css('background-color','#00ff00');
                        $('#city-boundary-status').text('City boundary set');
                        $('#city-boundary-data').val(coord_data_json);
                        
                        

                    }
                }
            });

            google.maps.event.addListener(newShape.getPath(), "insert_at", function(e) { getPolyGonCoordinates(newShape); });
            google.maps.event.addListener(newShape.getPath(), "remove_at", function(e) { getPolyGonCoordinates(newShape); });
            google.maps.event.addListener(newShape.getPath(), "set_at", function(e) { getPolyGonCoordinates(newShape); });

        });




        // Deselect all polygons when changing the drawing mode or when the user clicks on the map
        google.maps.event.addListener(drawingManager, 'drawingmode_changed', clearSelectedPolygons);
        google.maps.event.addListener(map, 'click', clearSelectedPolygons);

        //inter-state map is ready

        if(city_polygon_json.length > 5){
            //add polygon for pickup location boundary
            let polygon;            
            polygon = new google.maps.Polygon({
                paths: city_polygon_data.coords,
                strokeWeight: 1,
                strokeColor:'#000000',
                fillColor: '#00ff00',
                fillOpacity: 0.4,
                editable: true,
                draggable: true
                
            });
            polygon.setMap(map);

            allShapes[0] = polygon;

            setSelectedPolygon(polygon);

            getPolyGonCoordinates(polygon); // find coordinates peaks

            $('#city-boundary-status').css('background-color','#00ff00');
            $('#city-boundary-status').text('City boundary set');
           

        }


        for(var x = 0; x < allShapes.length;x++){

            let polygon = allShapes[x];

            google.maps.event.addListener(polygon, 'click', function() {
                setSelectedPolygon(polygon); 
            });

            google.maps.event.addListener(polygon, 'mouseup', function() {

                for (i=0; i < allShapes.length; i++) {
                    if (polygon.getPath() == allShapes[i].getPath()) {
                        allShapes[i] = polygon;
                        let coord_data_json = JSON.stringify(getPolyGonCoordinates(polygon));
                        console.log(coord_data_json);
                        $('#city-boundary-data').val(coord_data_json); 
                        

                    }
                }

                        
            });


            //update coordinates

            google.maps.event.addListener(polygon, "dragend", function(e) {
                for (i=0; i < allShapes.length; i++) {
                    if (polygon.getPath() == allShapes[i].getPath()) {
                        allShapes[i] = polygon;
                        let coord_data_json = JSON.stringify(getPolyGonCoordinates(polygon));
                        console.log(coord_data_json);
                        $('#city-boundary-data').val(coord_data_json);                         

                    }
                }
            });
            
        }


        /* if(drop_city_polygon_json.length > 5){
            //add polygon for pickup location boundary
            let polygon;            
            polygon = new google.maps.Polygon({
                paths: drop_city_polygon_data,
                strokeWeight: 1,
                strokeColor:'#000000',
                fillColor: '#ff0000',
                fillOpacity: 0.4,
            });
            polygon.setMap(map2);

            google.maps.event.addListener(polygon, 'click', function() {
                setSelectedPolygon(polygon);
            });



        } */


        function getPolyGonCoordinates(polygon) {

            var path = polygon.getPath();
            coordinates = [];
            for (var i = 0; i < path.length; i++) {
                coordinates.push({
                    lat: path.getAt(i).lat(),
                    lng: path.getAt(i).lng()
                });
            }

            let polygon_center_radius = getPolygonCenterCircleRadius(polygon);

            return {coords : coordinates, center : polygon_center_radius.center, radius : polygon_center_radius.radius}
            
        }


        function clearSelectedPolygons() {
            if (selectedPolygon) { //check that the selected shape is a polygon
                if (selectedPolygon.type !== 'marker') {
                    selectedPolygon.setEditable(false);
                }
                selectedPolygon = null;
            }
        }




        function setSelectedPolygon(polygon){
            clearSelectedPolygons();
            polygon.setEditable(true);
            polygon.setDraggable(true);
            selectedPolygon = polygon;
        }


        function getDistanceInMeters(location1, location2) {
            var lat1 = location1.lat();
            var lon1 = location1.lng();

            var lat2 = location2.lat();
            var lon2 = location2.lng();

            var R = 6371; // Radius of the earth in km
            var dLat = deg2rad(lat2 - lat1);
            var dLon = deg2rad(lon2 - lon1);
            var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
            var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            var d = R * c; // Distance in km
            return (d * 1000);

            function deg2rad(deg) {
                return deg * (Math.PI / 180);
            }
        }

        function getPolygonCenterCircleRadius(polygon){

            var p_bounds = new google.maps.LatLngBounds()

            polygon.getPath().forEach(function(element,index){
                p_bounds.extend(element)
            })

            var ne = p_bounds.getNorthEast(); // LatLng of the north-east corner
            var sw = p_bounds.getSouthWest(); // LatLng of the south-west corder

            var nw = new google.maps.LatLng(ne.lat(), sw.lng());
            var se = new google.maps.LatLng(sw.lat(), ne.lng());

            var length = getDistanceInMeters(sw, nw);
            var breadth = getDistanceInMeters(sw, ne);

            var radius = length >= breadth ? length / 2 : breadth / 2;

            if(city_circle){
                city_circle.setMap(null);
            }
            
            
            city_circle = new google.maps.Circle({
                strokeColor : '#FF0000',
                fillOpacity: 0,
                strokeOpacity: 1,
                strokeWeight: 1,
                map: map,
                center:p_bounds.getCenter(),
                radius:radius,
                editable:false,
                clickable: false

            });

            return {center: p_bounds.getCenter(), radius : radius / 1000}
        }



        function deleteSelectedPolygon() {
            if (selectedPolygon) {
                selectedPolygon.setMap(null);
                var index = allShapes.indexOf(selectedPolygon);
                if (index > -1) {
                    allShapes.splice(index, 1);

                    $('#city-boundary-data').val('');
                    $('#city-boundary-status').css('background-color','#333');
                    $('#city-boundary-status').text('City boundary not set');

                    if(city_circle){
                        city_circle.setMap(null);
                    }
                    
                    
                }
                let lat_lng = [];
                allShapes.forEach(function(data, index) {
                    lat_lng[index] = getCoordinates(data);
                });
                //document.getElementById('info').value = JSON.stringify(lat_lng);
            }
        }







    }
    

    if(jQuery('#city-zone-long').val()){ 

        setTimeout(() => {
            var latLong = new google.maps.LatLng(jQuery('#city-zone-lat').val(), jQuery('#city-zone-long').val());
            marker = new google.maps.Marker({
            position: latLong,
            map: map,
            animation: google.maps.Animation.DROP
            });
            marker.setMap(map);
            map.setZoom(13);
            //map.setCenter(marker.getPosition());
            map.panTo(marker.getPosition());

            /* if(city_circle){
                city_circle.setMap(null);
            }

            var city_radius_val = parseFloat($("#city-radius").val());
            if(!city_radius_val)city_radius_val = 3;
            if(dist_unit){ //miles
                city_radius_val = city_radius_val / 0.000621371;
            }else{ //KM
                city_radius_val = city_radius_val * 1000;
            }

                        
            city_circle = new google.maps.Circle({
                strokeColor : '#FF0000',
                strokeOpacity: 0.5,
                strokeWeight: 2,
                map: map,
                center:latLong,
                radius:city_radius_val,
                editable:true
            });

            $("#city-radius").off('keyup').on('keyup',function(){
                var city_radius_val = parseFloat($("#city-radius").val());
                if(!city_radius_val)return;
                if(dist_unit){ //miles
                    city_radius_val = city_radius_val / 0.000621371;
                }else{ //KM
                    city_radius_val = city_radius_val * 1000;
                }
                city_circle.setRadius(city_radius_val);
            });

            google.maps.event.addListener(city_circle,'radius_changed',function(){
                var map_circle_radius = parseFloat(city_circle.getRadius());
                console.log(map_circle_radius);
                var city_radius_dist_unit = 0.0;
                if(dist_unit){ //miles
                    city_radius_dist_unit = map_circle_radius * 0.000621371; //convert to miles from meters
                }else{ //km
                    city_radius_dist_unit = map_circle_radius / 1000; //convert to KM from meters
                }
                city_radius_dist_unit = (Math.round(city_radius_dist_unit * 10)) / 10;                
                $('#city-radius').val(city_radius_dist_unit);
            }) */


            
        }, 1500);
        
    }




    if(jQuery('#pcz-long').val()){ 
        setTimeout(() => {

            latLong1 = new google.maps.LatLng(parseFloat(jQuery('#pcz-lat').val()), parseFloat(jQuery('#pcz-long').val()));
            marker1 = new google.maps.Marker({
                position: latLong1,
                map: map2,
                animation: google.maps.Animation.DROP
            });
            marker1.setMap(map2);
            map2.setZoom(7);
            //map.setCenter(marker.getPosition());
            map2.panTo(marker1.getPosition());


            setTimeout(() => {
            latLong2 = new google.maps.LatLng(parseFloat(jQuery('#dcz-lat').val()), parseFloat(jQuery('#dcz-long').val()));
            marker2 = new google.maps.Marker({
                position: latLong2,
                map: map2,
                animation: google.maps.Animation.DROP
            });
            marker2.setMap(map2);
            map2.setZoom(7);
            //map.setCenter(marker.getPosition());
            map2.panTo(marker2.getPosition());


            setTimeout(function(){
            bounds.extend(latLong1);
            bounds.extend(latLong2);
            var pointA = latLong1;
            var pointB = latLong2;                
            map2.fitBounds(bounds);
            
            bounds = [];
            bounds = new google.maps.LatLngBounds();  
            calculateAndDisplayRoute(directionsService, directionsDisplay, pointA, pointB);
        }, 1500);
            
        }, 1500);

                        
        }, 1500);
        
        

       

        




    }


    function czAutocomplete() {                            
        var input = document.getElementById('city-zone');
        var options = {
            /* componentRestrictions: {country: 'ng'},
            strictBounds: true */
        };

        autocomplete = new google.maps.places.Autocomplete(input, options);
        
        google.maps.event.addListener(autocomplete, 'place_changed', function() {
            var place = autocomplete.getPlace();
            jQuery('#city-zone-long').val(place.geometry.location.lng());
            jQuery('#city-zone-lat').val(place.geometry.location.lat());
            jQuery('#city-zone-coord').html("Longitude: " + "<span style='color:red'>" + place.geometry.location.lng() + "</span> <br> Latitude:<span style='color:red'> " + place.geometry.location.lat() + "</span>");
            
            
            if(marker){
                marker.setMap(null);
                marker = [];
            }

            


            var latLong = new google.maps.LatLng(place.geometry.location.lat(), place.geometry.location.lng());
            marker = new google.maps.Marker({
            position: latLong,
            map: map,
            animation: google.maps.Animation.BOUNCE
            });
            marker.setMap(map);
            map.setZoom(7);
            //map.setCenter(marker.getPosition());
            map.panTo(marker.getPosition());

            /* if(city_circle){
                city_circle.setMap(null);
            }

            city_circle = new google.maps.Circle({
                strokeColor : '#FF0000',
                strokeOpacity: 0.5,
                strokeWeight: 2,
                map: map,
                center:{lat: place.geometry.location.lat(), lng: place.geometry.location.lng()},
                radius:city_radius_span,
                editable:true
            });

            $("#city-radius").off('keyup').on('keyup',function(){
                var city_radius_val = parseFloat($("#city-radius").val());
                if(!city_radius_val)return;
                if(dist_unit){ //miles
                    city_radius_val = city_radius_val / 0.000621371;
                }else{ //KM
                    city_radius_val = city_radius_val * 1000;
                }
                city_circle.setRadius(city_radius_val);
            }); */


        });
    }





    function pczAutocomplete() {                            
        var input = document.getElementById('pcz');
        var options = {
            /* componentRestrictions: {country: 'ng'},
            strictBounds: true */
        };

        pczautocomplete = new google.maps.places.Autocomplete(input, options);
        
        google.maps.event.addListener(pczautocomplete, 'place_changed', function() {
            var place = pczautocomplete.getPlace();
            jQuery('#pcz-long').val(place.geometry.location.lng());
            jQuery('#pcz-lat').val(place.geometry.location.lat());
            jQuery('#pcz-coord').html("Longitude: " + "<span style='color:red'>" + place.geometry.location.lng() + "</span> <br> Latitude:<span style='color:red'> " + place.geometry.location.lat() + "</span>");
            
            
            
            if(marker1){
                marker1.setMap(null);
                marker1 = [];
            }
            
            latLong1 = new google.maps.LatLng(place.geometry.location.lat(), place.geometry.location.lng());
            marker1 = new google.maps.Marker({
            position: latLong1,
            map: map2,
            animation: google.maps.Animation.DROP
            });
            marker1.setMap(map2);
            map2.setZoom(7);
            //map.setCenter(marker.getPosition());
            map2.panTo(marker1.getPosition());

            if(marker1 && marker2){
                setTimeout(function(){
                    bounds.extend(latLong1);
                    bounds.extend(latLong2);                
                    var pointA = latLong1;
                    var pointB = latLong2;                
                    map2.fitBounds(bounds);
                    
                    bounds = [];
                    bounds = new google.maps.LatLngBounds();  
                    calculateAndDisplayRoute(directionsService, directionsDisplay, pointA, pointB);   
                }, 1000);
                          
            }
            


        });
    }




    function dczAutocomplete() {                            
        var input = document.getElementById('dcz');
        var options = {
            /* componentRestrictions: {country: 'ng'},
            strictBounds: true */
        };

        dczautocomplete = new google.maps.places.Autocomplete(input, options);
        
        google.maps.event.addListener(dczautocomplete, 'place_changed', function() {
            var place = dczautocomplete.getPlace();
            jQuery('#dcz-long').val(place.geometry.location.lng());
            jQuery('#dcz-lat').val(place.geometry.location.lat());
            jQuery('#dcz-coord').html("Longitude: " + "<span style='color:red'>" + place.geometry.location.lng() + "</span> <br> Latitude:<span style='color:red'> " + place.geometry.location.lat() + "</span>");
            
            
            if(marker2){
                marker2.setMap(null);
                marker2 = [];
            }
            latLong2 = new google.maps.LatLng(place.geometry.location.lat(), place.geometry.location.lng());
            marker2 = new google.maps.Marker({
            position: latLong2,
            map: map2,
            animation: google.maps.Animation.DROP
            });
            marker2.setMap(map2);
            map2.setZoom(7);
            //map.setCenter(marker.getPosition());
            map2.panTo(marker2.getPosition());
            
            if(marker1 && marker2){
                setTimeout(function(){
                    bounds.extend(latLong1);
                    bounds.extend(latLong2);
                    var pointA = latLong1;
                    var pointB = latLong2;                
                    map2.fitBounds(bounds);
                    
                    bounds = [];
                    bounds = new google.maps.LatLngBounds();  
                    calculateAndDisplayRoute(directionsService, directionsDisplay, pointA, pointB);
                }, 1000);
                             
                
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
            
            if (status == google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(response);
                jQuery("#map2-header-title").html("Map: Distance = " + response.routes[0].legs[0].distance.text + " Duration: " + response.routes[0].legs[0].duration.text);
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


    function checkGeocode(addr){ //verify if address entered is a valid google map address
            // Get geocoder instance
            var geocoder = new google.maps.Geocoder();
            
            // Geocode the address
            geocoder.geocode({'address': addr.value}, function(results, status){
            var coord = undefined;  
                if (status === google.maps.GeocoderStatus.OK && results.length > 0) {
                //alert('looks good')
                
                    /* // set it to the correct, formatted address if it's valid
                    addr.value = results[0].formatted_address;; */
                    coord = {"lat":results[0].geometry.location.lat(),"long" : results[0].geometry.location.lng()};
                    //return({"lat":results[0].geometry.location.lat(),"long" : results[0].geometry.location.lng()});
                }else{
                    
                imgurl = '../img/info_.gif?a=' + Math.random();                    
                swal({
                    title: '<h1>Error</h1>',
                    text: 'The address you entered is not valid. Please select an option from the google adress list!' ,
                    imageUrl:imgurl,
                    html:true
                });
                
                } 
                return coord;
            });                     
    };






</script>













