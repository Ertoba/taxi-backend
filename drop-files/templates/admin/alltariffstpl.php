
            <div class="row">
              <div class="col-sm-12">
                  <div class="alert alert-info alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
                    Get an overview of all tariffs.
                  </div>
              </div>

              <div class="col-sm-12">   
                <div class="box box-default">
                  <div class="box-header with-border">
                    <h3 class="box-title">STATS</h3>
                  </div><!-- /.box-header -->
                <div class="box-body">
                  <br />

                  <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                      <span class="info-box-icon bg-yellow"><i class="fa fa-tags"></i></span>

                      <div class="info-box-content">
                        <span class="info-box-text">Tariffs</span>
                        <span class="info-box-number"><?php echo $number_of_tariffs; ?></span>
                      </div>
                      <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                 </div>



               </div><!-- /.box-body -->
              </div>


              </div><!--/col-sm-12-->



            </div> <!--/row-->



        <div class="row">
              <div class="col-sm-12">   
                <div class="box box-success">
                  <div class="box-header with-border">
                      
                    <h3 class="box-title">Tariffs <?php if($number_of_tariffs) echo "- Showing " . $page_number ." of ". $pages; ?></h3>
                  </div><!-- /.box-header -->
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
                                <th>Title</th>
                                <th>Scope</th>
                                <th>Ride Tariffs</th>
                                <th style="width:100px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            
                            $count = 1 + (($page_number - 1) * ITEMS_PER_PAGE);
                                foreach($tariffs_page_items as $tariffspageitems){
                                    $scope = $tariffspageitems['r_scope'] == 1 ? "Inter-State" : "Intra-city";
                                    $rides_tariff_summary = '';
                                    $city_currency_symbol = !empty($tariffspageitems['symbol']) ? $tariffspageitems['symbol'] : "₦";
                                    $count1 = 1;
                                    if(!empty($rides_data[$tariffspageitems['id']])){
                                        foreach($rides_data[$tariffspageitems['id']] as $ridestariff){
                                            $pp_details = '';
                                            $pp_active_days = '';
                                            $days_array = array(1=>"Monday",2=>"Tuesday",3=>"Wednesday",4=>"Thursday",5=>"Friday",6=>"Saturday",7=>"Sunday");
                                            if(!empty($ridestariff['pp_active_days'])){
                                                $active_days=[];
                                                $active_days = json_decode($ridestariff['pp_active_days'],1);
                                                if(!json_last_error()){
                                                    foreach($active_days as $activedays){
                                                        $pp_active_days .= $days_array[$activedays] . ", ";
                                                        $days_array[$activedays] = "selected='selected'";
                                                    }
                                                }
                                                

                                            } 

                                            if(!empty($ridestariff['pp_enabled'])){
                                                $pp_times = $ridestariff['pp_start'].":00 - ".$ridestariff['pp_end'] . ":00";
                                                $pp_charge_type = !empty($ridestariff['pp_charge_type']) && $ridestariff['pp_charge_type'] == 1 ? "Multiplier" : "Nominal";
                                                $pp_details .= "<h5><b style='color:#777'>Peak Period ({$pp_times})</b></h5><hr> <p title='Active days: {$pp_active_days}'>Active days: {$pp_active_days}</p><p>Charge: {$ridestariff['pp_charge_value']} ({$pp_charge_type})</p>";
                                            }
                                        
                                            $rides_tariff_summary .="<div style='width:280px;display: inline-block;overflow-x:hidden;'><div style='float:left;display:inline-block;width:60px;margin-right:10px;'><img src='{$ridestariff['ride_img']}' style='width:100%;height:100%' /></div>";
                                            $rides_tariff_summary .= "<div style='float:left;display:inline-block;width:200px;'><h5><b style='color:#777'>{$ridestariff['ride_type']} (Day time)</b></h5><hr> <p>Base Distance: {$ridestariff['init_distance']} Km</p> <p>Cost Per Km | mi: {$city_currency_symbol}{$ridestariff['cost_per_km']}</p> <p>Cost Per Minute: {$city_currency_symbol}{$ridestariff['cost_per_minute']}</p> <p>Pick-up Cost: {$city_currency_symbol}{$ridestariff['pickup_cost']}</p> <p>Drop-off Cost: {$city_currency_symbol}{$ridestariff['drop_off_cost']}</p><br> <h5><b style='color:#777'>{$ridestariff['ride_type']} (Night time)</b></h5><hr> <p>Base Distance: {$ridestariff['init_distance_n']} Km</p> <p>Cost Per Km | mi: {$city_currency_symbol}{$ridestariff['ncost_per_km']}</p> <p>Cost Per Minute: {$city_currency_symbol}{$ridestariff['ncost_per_minute']}</p> <p>Pick-up Cost: {$city_currency_symbol}{$ridestariff['npickup_cost']}</p> <p>Drop-off Cost: {$city_currency_symbol}{$ridestariff['ndrop_off_cost']}</p><br>{$pp_details}</div></div>";  
                                        }
                                        
                                        $rides_tariff_summary = "<div style='overflow-x:auto;width:100%;white-space: nowrap;'>" . $rides_tariff_summary . "</div>";
                                    }
                                    $del_action = $tariffspageitems['id'] == 1 ? "" : "<a href='edit-tariff.php?action=delete&id=".$tariffspageitems['id'] ."' data-msg='This city will be deleted including all zones, documents and coupons data linked to it. Drivers and riders in this city will be moved to the default city. Do you want to continue?' class='delete-item btn btn-danger btn-xs'>Delete</a>";
                                    
                                    echo "<tr><td>". $count++ . "</td><td>" . $tariffspageitems['r_title']. "</td><td>" . $scope . "</td><td>".$rides_tariff_summary."</td><td>"."<a href='edit-tariff.php?id=".$tariffspageitems['id'] ."' class='btn btn-success btn-xs'>edit</a> " . $del_action ."</td></tr>";
                                }
                        
                            ?>
                        </tbody>
                        </table>
                    </div>
                                  
                   <?php if(!$number_of_tariffs){ echo "<h1 style='text-align:center;'>Nothing to Show. Add Tariffs to get this area populated.</h1>";} ?>
                </div><!-- /.box-body -->
              </div>


              </div><!--/col-sm-12-->



            </div> <!--/row-->



    
    