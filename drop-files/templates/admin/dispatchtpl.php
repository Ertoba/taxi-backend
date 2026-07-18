
            <div class="row">
              <div class="col-sm-12">
                  <div class="alert alert-info alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
                    Manually dispatch interstate ride bookings to drivers.
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
                      <span class="info-box-icon bg-yellow"><i class="fa fa-bookmark"></i></span>

                      <div class="info-box-content">
                        <span class="info-box-text">Pending Bookings</span>
                        <span class="info-box-number"><?php echo $number_of_pending_bookings; ?></span>
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
                      
                    <h3 class="box-title">Pending Bookings <?php if($number_of_pending_bookings) echo "- Showing " . $page_number ." of ". $pages; ?></h3>
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
                              <th>Booking ID</th>    
                              <th>Customer</th>
                              <th>Ride Type</th>
                              <th>Pick-up</th>
                              <th>Drop-off</th>
                              <th>Booking Time</th>
                              <th>Price</th>
                              <th>Assigned Driver</th>
                              <th>Status</th>
                              <th>Actions</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php
                          
                          $count = 1 + (($page_number - 1) * ITEMS_PER_PAGE);
                              foreach($dispatch_page_items as $dispatchpageitems){
                                  $booking_status = '';
                                  $del_action = "";
                                  switch($dispatchpageitems['booking_status']){
                                      case 0:
                                      $booking_status = "<span style='color:purple'><b>Pending</b></span>";
                                      $del_action = " <a data-msg='Are you sure you want to delete this booking?' href='edit-booking.php?rdir=dispatch.php&action=delete&id=".$dispatchpageitems['booking_id'] ."' class='btn-margin-top delete-item btn btn-danger btn-xs'>Delete</a>";
                                      break;

                                      case 1:
                                      $booking_status = "<span style='color:orange'><b>On Ride</b></span>";
                                      break;

                                      case 2:
                                      $booking_status = "<span style='color:red'><b>Cancelled (Rider)</b></span>";
                                      $del_action = " <a data-msg='Are you sure you want to delete this booking?' href='edit-booking.php?rdir=dispatch.php&action=delete&id=".$dispatchpageitems['booking_id'] ."' class='btn-margin-top delete-item btn btn-danger btn-xs'>Delete</a>";
                                      break;

                                      case 3:
                                      $booking_status = "<span style='color:Green'><b>Completed</b></span>";
                                      break;

                                      case 4:
                                      $booking_status = "<span style='color:red'><b>Cancelled (Driver)</b></span>";
                                      $del_action = " <a data-msg='Are you sure you want to delete this booking?' href='edit-booking.php?rdir=dispatch.php&action=delete&id=".$dispatchpageitems['booking_id'] ."' class='btn-margin-top delete-item btn btn-danger btn-xs'>Delete</a>";
                                      break;

                                      case 5:
                                      $booking_status = "<span style='color:red'><b>Cancelled (System)</b></span>";
                                      $del_action = " <a data-msg='Are you sure you want to delete this booking?' href='edit-booking.php?rdir=dispatch.php&action=delete&id=".$dispatchpageitems['booking_id'] ."' class='btn-margin-top delete-item btn btn-danger btn-xs'>Delete</a>";
                                      break;

                                      default:
                                      $booking_status = "<span style='color:purple'><b>Pending</b></span>";
                                      $del_action = " <a data-msg='Are you sure you want to delete this booking?' href='edit-booking.php?rdir=dispatch.php&action=delete&id=".$dispatchpageitems['booking_id'] ."' class='btn-margin-top delete-item btn btn-danger btn-xs'>Delete</a>";
                                      break;
                                      
                                  }

                                  $booking_available = 1;
                                  $driver_assigned = "Not Assigned"; 

                                  if(!empty($dispatchpageitems['booking_driver_id'])){
                                    $booking_available = 0;
                                    $driver_assigned = "<a href='view-driver.php?id={$dispatchpageitems['booking_driver_id']}'>" .$dispatchpageitems['drvr_firstname'] . " " . $dispatchpageitems['drvr_lastname'] . "</a>"; 
                                  }

                                  if(isset($bookings_allocated[$dispatchpageitems['booking_id']])){
                                    $booking_available = 0;
                                    $driver_assigned = "<a href='view-driver.php?id={$dispatchpageitems['booking_driver_alloc']}'>Driver Allocated</a>"; 
                                  }

                                  if($dispatchpageitems['booking_status'] != 0 ){
                                      $booking_available = 0;
                                  }

                                  
                                  

                                  if($booking_available){
                                    
                                    $assign_driver = "<a data-bookid='{$dispatchpageitems['booking_id']}' data-rowid = '{$dispatchpageitems['booking_id']}' href='#' data-href='drivers-list.php?booking_id=".$dispatchpageitems['booking_id'] ."&plat={$dispatchpageitems['pickup_lat']}&plng={$dispatchpageitems['pickup_long']}&dlat={$dispatchpageitems['dropoff_lat']}&dlng={$dispatchpageitems['dropoff_long']}&ride_id={$dispatchpageitems['booking_ride']}&route_id=0' class='btn-margin-top btn btn-success btn-xs dispatch-driver-list'>Assign Driver</a> ";
                                  }else{
                                    $assign_driver = '';
                                  }

                                  $view_details = "<a href='view-booking.php?bkid=".$dispatchpageitems['booking_id'] ."' class='btn-margin-top btn btn-primary btn-xs'>View</a> ";

                                  $ride_type = !empty($dispatchpageitems['ride_type']) ? $dispatchpageitems['ride_type'] : "N/A";
                                  $customer_details = $dispatchpageitems['firstname'] . " " . $dispatchpageitems['lastname'] . "<br>" . $dispatchpageitems['user_country_dial_code'] . " " .(!empty(DEMO) ? mask_string($dispatchpageitems['phone']) : $dispatchpageitems['phone']) . "<br>" . (!empty(DEMO) ? mask_email($dispatchpageitems['email']) : $dispatchpageitems['email']);
                                  
                                  
                                  echo "<tr><td>".$count++."</td><td>". str_pad($dispatchpageitems['booking_id'] , 5, '0', STR_PAD_LEFT) . "</td><td>".$customer_details."</td><td>".$dispatchpageitems['ride_type']."</td><td>". $dispatchpageitems['pickup_address'] ."</td><td>". $dispatchpageitems['dropoff_address'] ."</td><td>".date('l, M j, Y H:i:s',strtotime($dispatchpageitems['pickup_datetime'].' UTC'))."</td><td>". $dispatchpageitems['cur_symbol'].$dispatchpageitems['estimated_cost']."</td><td id='driver-assigned-{$dispatchpageitems['booking_id']}'>".$driver_assigned."</td><td>".$booking_status."</td><td>" . $view_details . $assign_driver . $del_action ."</td></tr>";

                              }
                        
                          ?>
                      </tbody>
                      </table>
                    </div>
                                  
                   <?php if(!$dispatch_page_items){ echo "<h1 style='text-align:center;'>Nothing to Show. No bookings available.</h1>";} ?>
                </div><!-- /.box-body -->
              </div>


              </div><!--/col-sm-12-->



            </div> <!--/row-->



    
    