
            <div class="row">
              <div class="col-sm-12">
                  <div class="alert alert-info alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
                    View all Scheduled ride bookings.
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
                        <span class="info-box-text">All Scheduled Bookings</span>
                        <span class="info-box-number"><?php echo $number_of_bookings; ?></span>
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
            <div class="col-sm-12" >
            <div class="box box-success">
                    <div class="box-header with-border">
                      <h3 class="box-title">Filter</h3>
                    
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                    
                    
                              <form  enctype="multipart/form-data" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="get" >
                                    
                                      <div class="form-group">                                                                               
                                        
                                        <div  class="col-sm-6">                
                                          <select class="form-control" id="booking-type" name="booking-type">                                              
                                            <option value="">Booking type</option> 
                                            <option value="0">Intra-city bookings</option>
                                            <option value="1">Inter-state bookings</option>
                                          </select>                
                                        </div>
                                      
                                        <div class="col-sm-6" id="city-route-select" style="display:none;">
                                          <select class="form-control" id="booking-type-city" name="booking-type-city">                                              
                                            <option value="">Select city</option>
                                            <?php
                                              foreach($inter_city_routes as $intercityroutes){
                                                echo "<option value='{$intercityroutes['id']}'>{$intercityroutes['r_title']}</option>\n";
                                              }
                                            ?>
                                            
                                          </select>                                          
                                        </div>

                                        <div class="col-sm-6" id="state-route-select" style="display:none;">
                                          <select class="form-control" id="booking-type-state" name="booking-type-state">                                              
                                            <option value="">Select state routes</option>
                                            <?php
                                              foreach($inter_state_routes as $interstateroutes){
                                                echo "<option value='{$interstateroutes['id']}'>{$interstateroutes['r_title']}</option>\n";
                                              }
                                            ?> 
                                            
                                          </select>                                          
                                        </div>



                                    </div>
                                    
                                    
                                    
                                    <div class="form-group">
                                        
                                        <div class="col-sm-6">
                                          <input  type="text"  class="form-control" id="bookingid" placeholder="Booking ID" name="bookingid" value="<?php echo isset($_GET["bookingid"]) ? $_GET["bookingid"] : ''; ?>" >
                                        </div>

                                        <div class="col-sm-6">
                                          <input  type="text"  class="form-control" id="custphone" placeholder="Customer Phone Number" name="custphone" value="<?php echo isset($_GET["custphone"]) ? $_GET["custphone"] : ''; ?>" >
                                        </div>

                                        

                                    </div>


                                     <div class="form-group">
                                        
                                        
                                        <div class="col-sm-6">
                                          <input  type="text"  class="form-control" id="custname" placeholder="Customer Name" name="custname" value="<?php echo isset($_GET["custname"]) ? $_GET["custname"] : ''; ?>" >
                                        </div>

                                        <div class="col-sm-6">
                                            <div id="">
                                                <input  type="text" class="form-control" id="all-booking-driver" placeholder="Driver" name="booking-driver" value="<?php echo isset($_GET["booking-driver"]) ? $_GET["booking-driver"] : ''; ?>" >
                                                <input  type="text" hidden='hidden' id="booking-driverid" placeholder="" name="booking-driverid" value="<?php echo isset($_GET["booking-driverid"]) ? $_GET["booking-driverid"] : ''; ?>" >
                                            </div>
                                          
                                            
                                        </div>



                                    </div>



                                    <div class="form-group">
                                                                               
                                        
                                        <div  class="col-sm-6">                
                                          <select class="form-control" id="bookstatus" name="bookstatus">
                                              
                                            <option value="" selected>Status</option> 
                                            <option value="0">Pending</option>
                                            <option value="1">Onride</option>
                                            <option value="2">Cancelled</option>
                                            <option value="3">Completed</option>
                                          </select>                
                                      </div>
                                      
                                      <div class="col-sm-6">
                                          <input  type="text" class="form-control" id="datepickerbsearch" placeholder="Booking date" name="bookingdate" value="<?php echo isset($_GET["bookingdate"]) ? $_GET["bookingdate"] : ''; ?>" >
                                      </div>



                                    </div>


                                    <div class="form-group">
                                                                               
                                        
                                        <div  class="col-sm-6">                
                                          <select class="form-control" id="franchise" name="franchise">
                                          <option value="">Franchise</option> 
                                           <?php 
                                             $select = '';
                                             foreach($franchise_data as $franchisedata){
                                              $select = !empty($_GET['franchise']) && (urldecode($_GET['franchise']) == $franchisedata['franchise_name']) ? "selected" : '';  
                                            ?>   
                                            <option value="<?php echo urlencode($franchisedata['franchise_name']) ?>" <?php echo $select; ?> ><?php echo $franchisedata['franchise_name'] ?></option>  
                                           <?php } ?>
                                            
                                          </select>                
                                      </div>

                                      <div  class="col-sm-6">                
                                          <select class="form-control" id="bookspmethod" name="bookspmethod">
                                              
                                            <option value="" selected>Payment Method</option> 
                                            <option value="1">Cash</option>
                                            <option value="2">Wallet</option>
                                            <option value="3">Card</option>
                                            <option value="4">POS</option>
                                            
                                          </select>                
                                      </div>
                                      
                                      
                                    </div>


                                    

                                    
                                    
                                                                   
                                  
                                    
                                    
                                                                                   
                                    
                                    
                                      <hr />
                                  <div style='text-align:left;'><button type="submit" class="btn btn-primary" value="1" name="search-booking" >Search bookings</button> <a href='<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>' class="btn btn-success" value="1" name="search-reset" >Reset</a></div> 
                                </form>
              
              
              
                                  
                    </div>
                    <!-- /.box-body -->
                  </div>

            </div> <!--/col-sm-8-->
        </div>




        

        <div class="row">
              <div class="col-sm-12">   
                <div class="box box-success">
                  <div class="box-header with-border">
                      
                    <h3 class="box-title">All Scheduled Bookings <?php if($number_of_bookings) echo "- Showing " . $page_number ." of ". $pages; ?></h3>
                    <div style="text-align: right;"><button class="btn btn-success" id="export-data" >Export Data</button></div>
                  </div><!-- /.box-header -->
                <div class="box-body">
                <!-- <h4 style='text-align:center;'><?php echo $search_result_price_sum_summary; ?></h4> -->
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
                      <table class='table table-bordered table-striped '>
                      <thead>
                          <tr>
                              <th style="">#</th>  
                              <th style="">Booking ID</th>    
                              <th style="">Customer</th>
                              <th style="">Car Type</th>
                              <th style="">Pick-up</th>
                              <th style="">Drop-off</th>
                              <th style="">Pickup Time</th>
                              <th style="">Booking Time</th>
                              <th style="">Est.Fare</th>
                              <th style="">Amount Paid</th>
                              <th style="">Payment Method</th>
                              <th style="">Assigned Driver</th>
                              <th style="">Status</th>
                              <th style="">Actions</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php
                          
                          $count = 1 + (($page_number - 1) * ITEMS_PER_PAGE);
                          $default_currency_symbol = !empty($_SESSION['default_currency']) ? $_SESSION['default_currency']['symbol'] : "₦";
                              foreach($bookings_page_items as $bookingspageitems){
                                  $booking_status = '';
                                  switch($bookingspageitems['status']){
                                      case 0:
                                      if(!empty($bookingspageitems['driver_id'])){
                                        $booking_status = "<span style='color:purple'><b>Driver en route</b></span>";
                                      }else{                                        
                                        $booking_status = "<span style='color:purple'><b>Pending</b></span>";
                                      }                                      
                                      break;

                                      case 1:
                                      $booking_status = "<span style='color:orange'><b>On Ride</b></span>";
                                      break;

                                      case 2:
                                      if(!empty($bookingspageitems['cancel_comment'])){
                                        $booking_status = "<span style='color:red'><b>Rider Cancelled</b><br><b style='color:black'>Reason: {$bookingspageitems['cancel_comment']}</b></span>";
                                      }else{
                                        $booking_status = "<span style='color:red'><b>Rider Cancelled</b></span>";
                                      }                                      
                                      break;

                                      case 3:
                                      $booking_status = "<span style='color:Green'><b>Completed</b></span>";
                                      break;

                                      case 4:
                                      if(!empty($bookingspageitems['cancel_comment'])){
                                        $booking_status = "<span style='color:red'><b>Driver Cancelled</b><br><b style='color:black'>Reason: {$bookingspageitems['cancel_comment']}</b></span>";
                                      }else{
                                        $booking_status = "<span style='color:red'><b>Driver Cancelled</b></span>";
                                      }
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

                                $bookingspageitems['dropoff_address'] = empty($bookingspageitems['dropoff_address']) ? "<span style='color:red;'>Destination not specified</span>" : $bookingspageitems['dropoff_address'];
                                  
                                  $ride_type = !empty($bookingspageitems['ride_type']) ? $bookingspageitems['ride_type'] : "N/A";
                                                                  
                                  $customer_details = $bookingspageitems['user_firstname'] . " " . $bookingspageitems['user_lastname'] . "<br>" . (!empty(DEMO) ? mask_string($bookingspageitems['user_phone']) : $bookingspageitems['user_phone']);
                                  $driver_assigned = "<span style='color:red;'>Not Assigned</span>";
                                  if(!empty($bookingspageitems['driver_id'])){
                                    $driver_assigned = $bookingspageitems['driver_firstname'] . " " . $bookingspageitems['driver_lastname']; 
                                  }elseif(!empty($bookingspageitems['scheduled_driver'])){
                                    $driver_assigned = $bookingspageitems['drvr_firstname'] . " " . $bookingspageitems['drvr_lastname']; 
                                  }
                                  
                                  if($bookingspageitems['status'] == 0){
                                    $assign_driver = '';  
                                    //$assign_driver = " <a data-bookid='{$bookingspageitems['booking_id']}' data-rowid = '{$bookingspageitems['booking_id']}' href='#' data-href='drivers-list.php?booking_id=".$bookingspageitems['booking_id'] ."&plat={$bookingspageitems['pickup_lat']}&plng={$bookingspageitems['pickup_long']}&dlat={$bookingspageitems['dropoff_lat']}&dlng={$bookingspageitems['dropoff_long']}&ride_id={$bookingspageitems['booking_ride']}&route_id={$bookingspageitems['booking_route_id']}&schd_bk=1' class='btn btn-success btn-margin-top btn-xs dispatch-driver-list'>Assign Driver</a>";;
                                  }else{
                                    $assign_driver = '';
                                  }

                                  if($bookingspageitems['status'] == 1 || $bookingspageitems['status'] == 3){  
                                    $del_action = '';
                                  }else{                                    
                                    $del_action = " <a data-msg='Are you sure you want to delete this booking?' href='edit-booking.php?rdir=all-sbookings.php&action=delete&id=".$bookingspageitems['booking_id'] ."' class='btn-margin-top delete-item btn btn-danger btn-xs'>Delete</a>";
                                  }

                                  if($bookingspageitems['status'] == 1 && !empty($bookingspageitems['location_date'])){ 
                                    $location_date = date('d/m/Y g:i A',strtotime($bookingspageitems['location_date'] . ' UTC'));
                                    $track_ride = " <a data-driverid = '{$bookingspageitems['driver_id']}' data-datetime='{$location_date}' data-lat='{$bookingspageitems['lat']}' data-long='{$bookingspageitems['long']}' href='#driver-location-map-container' class='btn btn-margin-top btn-xs btn-success drvr-location' >Track</a>";
                                  }else{
                                    $track_ride = '';
                                  }

                                  if($bookingspageitems['status'] == 0 || empty($bookingspageitems['scheduled_driver'] || empty($bookingspageitems['driver_id']))){
                                    $broadcast_schd_bk = " <a data-bookid = '{$bookingspageitems['booking_id']}' class='btn btn-margin-top btn-xs btn-warning broadcast-btn' >Broadcast</a>";
                                  }else{
                                    $broadcast_schd_bk = "";
                                  }
                                    
                                  
                                  if($bookingspageitems['status'] == 0 && empty($bookingspageitems['driver_id'])){  
                                    $edit_action = " <a href='edit-booking.php?action=edit&id=".$bookingspageitems['booking_id'] ."' class='btn btn-success btn-margin-top btn-xs'>Modify</a>";
                                  }else{
                                    $edit_action = '';
                                  }

                                  if($bookingspageitems['status'] == 3){  
                                    $invoice_action = '';//" <a href='booking-invoice.php?bkid=".$bookingspageitems['booking_id'] ."' class='btn btn-success btn-margin-top btn-xs'>Invoice</a>";
                                  }else{
                                    $invoice_action = '';
                                  }

                                  $view_details = "<a href='view-booking.php?bkid=".$bookingspageitems['booking_id'] ."' class='btn btn-primary btn-margin-top btn-xs'>View</a> ";

                                  $estimated_cost = $bookingspageitems['cur_symbol'] . ($bookingspageitems['estimated_cost']);
                                  $estimated_cost_local = (int) ($bookingspageitems['estimated_cost'] / $bookingspageitems['cur_exchng_rate'] * 100);
                                  $estimated_cost_local = $default_currency_symbol . ($estimated_cost_local / 100);
                                  $amount_paid = !empty($bookingspageitems['paid_amount']) ? $bookingspageitems['cur_symbol'] . $bookingspageitems['paid_amount'] : "N/A";
                                  $amount_paid_local = (int) ($bookingspageitems['paid_amount'] / $bookingspageitems['cur_exchng_rate'] * 100);
                                  $amount_paid_local = !empty($bookingspageitems['paid_amount']) ? $default_currency_symbol . ($amount_paid_local / 100) : "0.00";
                                  echo "<tr><td>".$count++."</td><td>". str_pad($bookingspageitems['booking_id'] , 5, '0', STR_PAD_LEFT) . "</td><td>".$customer_details."</td><td>".$bookingspageitems['ride_type']."</td><td>" . $bookingspageitems['pickup_address'] ."</td><td>". $bookingspageitems['dropoff_address'] ."</td><td>".date('l, M j, Y H:i:s',strtotime($bookingspageitems['pickup_datetime'].' UTC')) ."</td><td>".date('l, M j, Y H:i:s',strtotime($bookingspageitems['date_created'].' UTC'))."</td><td title='{$estimated_cost_local}'>".$estimated_cost."</td><td title='{$amount_paid_local}'>".$amount_paid."</td><td>". $paymethod ."</td><td id='driver-assigned-{$bookingspageitems['booking_id']}'>".$driver_assigned."</td><td>".$booking_status."</td><td>". $view_details . $track_ride .$edit_action . " " . $del_action . " ". $invoice_action . $broadcast_schd_bk . $assign_driver . "</td></tr>";
                              }
                        
                          ?>
                      </tbody>
                      </table>
                    </div>
                                  
                   <?php if(!$number_of_bookings){ echo "<h1 style='text-align:center;'>Nothing to Show. No bookings found.</h1>";} ?>
                </div><!-- /.box-body -->
              </div>


              </div><!--/col-sm-12-->
              

              <div id="driver-location-map-container" style='display:none;' >
                  <h4 id='location-update'></h4>
                  <div style="width:500px;height:500px;" id="driver-location-map">
              </div>


            </div> <!--/row-->

            <div class="modal fade" id="export-dialog" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
              <div class="modal-dialog " role="document">
                  <div class="modal-content">
                      <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                          <h4 class="modal-title" id="gridSystemModalLabel">Export Settings</h4>
                      </div>
                      <div class="modal-body">
                          <form class="form-horizontal" id="export-data-form" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                              <div class="form-group">

                                  <div class="col-sm-6" >
                                      <p style="margin-top: 10px;margin-bottom: 2px;">Select a file format</p>
                                      <select class="form-control" id="export-file-format" name="export-file-format"> 
                                          <option value="1">Microsoft Excel (XLSX)</option>                                           
                                          <option value="2">CSV</option>                                    
                                      </select>  
                                  </div>
                                    
                                    <!-- <div class="col-sm-12">
                                        <p style="margin-top: 10px;margin-bottom: 2px;">Select a date to export data</p>
                                        <input  type="text" readonly required= "required" class="form-control" id="exportdateinput" name="data-date" value="" > 
                                    </div> -->  
                                        
                              </div>
                          </form>
                      </div>
                      <div class="modal-footer">
                        <button type="button" id="export_data_btn" class="btn btn-primary">Export</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                      </div>
                    </div>
                </div>
            </div>
          <style>
            .datepicker {
              z-index: 1600 !important; /* has to be larger than 1050 */
            }
          </style>


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
var infowindow_content;
var driver_icon_image = "<?php echo SITE_URL . 'img/driver-marker-icon.png';?>";
var driver_data;


$('#export-data').on('click', function(){
  $('#export-dialog').modal('show');
})

$('#export-dialog').on('shown.bs.modal', function() {
  $('#exportdateinput').datepicker({
    format: "yyyy-mm-dd",
    todayHighlight: true
  });
});


$('.broadcast-btn').on('click', function(){

    let booking_id = $(this).data('bookid');

    $('#busy').modal('show');
    var post_data = {'action':'sbookingbroadcast','booking_id': booking_id};
    $.ajax({
        url: ajaxurl,
        type: 'POST',
        timeout : 60000,
        crossDomain:true,
        xhrFields: {withCredentials: true},
        data: post_data,
        success: function (data, status)
        {
          $('#busy').modal('hide');

            try{
                var data_obj = JSON.parse(data);
            }catch(e){

                imgurl = '../img/info_.gif?a=' + Math.random();

                swal({
                            title: '<h1>Error</h1>',
                            text: 'Failed to notify all drivers in the city of the booking',
                            imageUrl:imgurl,
                            html:true
                });
                return;

            }

            
            if(data_obj.hasOwnProperty('error')){
                imgurl = '../img/info_.gif?a=' + Math.random();

                swal({
                            title: '<h1>Error</h1>',
                            text: data_obj.error,
                            imageUrl:imgurl,
                            html:true
                });
            }
            
            
            if(data_obj.hasOwnProperty('success')){

                imgurl = '../img/success_.gif?a=' + Math.random();

                swal({
                            title: '<h1>Success</h1>',
                            text: "All drivers have been notified of the scheduled booking",
                            imageUrl:imgurl,
                            html:true
                });
                
                
            }       

            


        },
        error: function(jqXHR,textStatus, errorThrown) {  
            
            $('#busy').modal('hide');

            imgurl = '../img/info_.gif?a=' + Math.random();

            swal({
                        title: '<h1>Error</h1>',
                        text: 'Failed to notify all drivers in the city of the booking',
                        imageUrl:imgurl,
                        html:true
            });
            
        }
        
    });
    
})


$('#export_data_btn').on('click', function(e){

e.preventDefault();

var export_file_format = $('#export-file-format').val();



//send message through AJAX

$('#busy').modal('show');
$('#export-dialog').modal('hide');
var post_data = {'action':'exportsBookings','type': export_file_format};
$.ajax({
    url: ajaxurl,
    type: 'POST',
    timeout : 60000,
    crossDomain:true,
    xhrFields: {withCredentials: true},
    data: post_data,
    success: function (data, status)
    {
      $('#busy').modal('hide');

        try{
            var data_obj = JSON.parse(data);
        }catch(e){

            imgurl = '../img/info_.gif?a=' + Math.random();

            swal({
                        title: '<h1>Error</h1>',
                        text: 'Failed to send message!',
                        imageUrl:imgurl,
                        html:true
            });
            return;

        }

        
        if(data_obj.hasOwnProperty('error')){
            imgurl = '../img/info_.gif?a=' + Math.random();

            swal({
                        title: '<h1>Error</h1>',
                        text: 'Failed to export data! - ' + data_obj.error,
                        imageUrl:imgurl,
                        html:true
            });
        }
        
        
        if(data_obj.hasOwnProperty('success')){

            if(data_obj.hasOwnProperty('download')){
                imgurl = '../img/success_.gif?a=' + Math.random();

                swal({
                            title: '<h1>Success</h1>',
                            text: data_obj.success,
                            imageUrl:imgurl,
                            html:true,
                            confirmButtonText: "Download"
                }, function(){
                  var file_path = data_obj.download;
                  var a = document.createElement('A');
                  a.href = file_path;
                  document.body.appendChild(a);
                  a.click();
                  document.body.removeChild(a);
                  
                })
                return;
            }

            imgurl = '../img/success_.gif?a=' + Math.random();

            swal({
                        title: '<h1>Success</h1>',
                        text: data_obj.success,
                        imageUrl:imgurl,
                        html:true
            });
            
            
        } 
        
        

        


    },
    error: function(jqXHR,textStatus, errorThrown) {  
        
        $('#busy').modal('hide');

        imgurl = '../img/info_.gif?a=' + Math.random();

        swal({
                    title: '<h1>Error</h1>',
                    text: 'Failed to export data',
                    imageUrl:imgurl,
                    html:true
        });
        
    }
    
});

  
})



jQuery("select#bookstatus option[value='<?php echo isset($_GET['bookstatus']) ? $_GET['bookstatus'] : '';  ?>']").attr('selected', 'selected');
jQuery("select#booking-type option[value='<?php echo isset($_GET['booking-type']) ? $_GET['booking-type'] : '';  ?>']").attr('selected', 'selected');
jQuery("select#booking-type-city option[value='<?php echo isset($_GET['booking-type-city']) ? $_GET['booking-type-city'] : '';  ?>']").attr('selected', 'selected');
jQuery("select#booking-type-state option[value='<?php echo isset($_GET['booking-type-state']) ? $_GET['booking-type-state'] : '';  ?>']").attr('selected', 'selected');

if(jQuery('#booking-type').val() == "0"){
  jQuery('#city-route-select').show();
  jQuery('#state-route-select').hide();
}else if(jQuery('#booking-type').val() == "1"){
  jQuery('#city-route-select').hide();
  jQuery('#state-route-select').show();
}else{
  jQuery('#city-route-select').hide();
  jQuery('#state-route-select').hide();
}

jQuery('#booking-type').on('change', function(){
  if(jQuery('#booking-type').val() == "0"){
    jQuery('#city-route-select').show();
    jQuery('#state-route-select').hide();
  }else if(jQuery('#booking-type').val() == "1"){
    jQuery('#city-route-select').hide();
    jQuery('#state-route-select').show();
  }else{
    jQuery('#city-route-select').hide();
    jQuery('#state-route-select').hide();
  }
});



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
            infoWindow = new google.maps.InfoWindow();
            latLong = new google.maps.LatLng(latitude,longitude);
            
            marker = new google.maps.Marker({
                position: latLong,
                map: map,
                icon: driver_icon_image,
                title : ''
            });
            marker.addListener("click", () => {
              infoWindow.close();
              infoWindow.setContent(infowindow_content);
              infoWindow.open(marker.getMap(), marker);
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
                        
                      driver_data = data_obj.data;
                
                      for(let key in driver_data){

                            $('#location-update').html('Last location update: ' + driver_data[key].location_date);
                            latLong = new google.maps.LatLng(driver_data[key].lat,driver_data[key].lng);
                            let driver_name = driver_data[key].name;
                            infowindow_content = `<h3>${driver_name}</h3><p>Last seen: ${driver_data[key].location_date}</p><p>${driver_data[key].view_link}</p>`;
                            
                            marker.setPosition(latLong); 
                            marker.setTitle(driver_name);
                            map.panTo(marker.getPosition());
                            
                      }  
                    
                    }  
        
                },
                error:function(jqXHR,textStatus, errorThrown){
                    return;
                }
                
            });
        
        
        }
        
        

</script>
    
    