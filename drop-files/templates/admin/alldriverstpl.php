
            <div class="row">
                <div class="col-sm-12">
                    <div class="alert alert-info alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h4><i class="icon fa fa-drivers-license"></i> Quick Info!</h4>
                        Get an overview of all drivers.
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
                                <span class="info-box-icon bg-yellow"><i class="fa fa-drivers-license"></i></span>

                                <div class="info-box-content">
                                    <span class="info-box-text">Drivers</span>
                                    <span class="info-box-number"><?php echo $number_of_drivers; ?></span>
                                </div>
                                <!-- /.info-box-content -->
                                </div>
                                <!-- /.info-box -->
                            </div>


                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="info-box">
                                <span class="info-box-icon bg-green"><i class="fa fa-bolt"></i></span>

                                <div class="info-box-content">
                                    <span class="info-box-text">Drivers Online</span>
                                    <span class="info-box-number"><?php echo $number_of_drivers_available; ?></span>
                                </div>
                                <!-- /.info-box-content -->
                                </div>
                                <!-- /.info-box -->
                            </div>


                            <div class="col-md-3 col-sm-6 col-xs-12">
                                <div class="info-box">
                                    <span class="info-box-icon bg-grey"><i class="fa fa-user-times"></i></span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Pending Activation</span>
                                        <span class="info-box-number" id="drv-pend-act"><?php echo $number_of_non_activated; ?></span>
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
                                                                                    
                                            <div class="col-sm-4" id="city-select">
                                                <select class="form-control" id="city" name="city">                                            
                                                    <option value="0">By city</option>
                                                    <?php
                                                        foreach($inter_city_routes as $intercityroutes){
                                                            $selected = "";
                                                            
                                                            if(isset($_GET['city']) && $_GET['city'] == $intercityroutes['id']){
                                                                $selected = "selected";
                                                            }
                                                                                                          
                                                            echo "<option value='{$intercityroutes['id']}' {$selected}>{$intercityroutes['r_title']}</option>"; 
                                                        }
                                                        
                                                    ?>
                                                    
                                                
                                                </select> 

                                            </div>

                                            <div class="col-sm-4" id="city-select">
                                                <select class="form-control" id="car-category" name="car-category">                                            
                                                    <option value="0">By vehicle category</option>
                                                    <?php
                                                        foreach($rides_data as $ride){
                                                            $selected = "";
                                                            
                                                            if(isset($_GET['car-category']) && $_GET['car-category'] == $ride['id']){
                                                                $selected = "selected";
                                                            }
                                                                                                          
                                                            echo "<option value='{$ride['id']}' {$selected}>{$ride['ride_type']}</option>"; 
                                                        }
                                                        
                                                    ?>
                                                    
                                                
                                                </select> 

                                            </div>

                                            <div class="col-sm-4">
                                                <select class="form-control" id="act-status" name="act-status"> 
                                                    <option value="---" <?php echo isset($_GET['act-status']) && $_GET['act-status'] == 0 ? "selected" : "";  ?> >By activation status</option>                                           
                                                    <option value="1" <?php echo isset($_GET['act-status']) && $_GET['act-status'] == 1 ? "selected" : "";  ?> >Activated</option>
                                                    <option value="0" <?php echo isset($_GET['act-status']) && $_GET['act-status'] == 0 ? "selected" : "";  ?> >Not activated</option>                                                                                                    
                                                </select>  
                                            </div>

                                        </div>

                                        <div class="form-group"> 

                                            <div class="col-sm-4" >
                                                <select class="form-control" id="online-status" name="online-status"> 
                                                    <option value="---" <?php echo isset($_GET['online-status']) && $_GET['online-status'] == 0 ? "selected" : "";  ?> >By Online status</option>                                           
                                                    <option value="1" <?php echo isset($_GET['online-status']) && $_GET['online-status'] == 1 ? "selected" : "";  ?> >Online</option>
                                                    <option value="0" <?php echo isset($_GET['online-status']) && $_GET['online-status'] == 0 ? "selected" : "";  ?> >Offline</option>                                                                                                    
                                                </select>  
                                            </div>

                                            <div class="col-sm-4">                                                
                                                <input  type="text" placeholder="By date registered" readonly required= "required" class="form-control" id="reg-date" name="reg-date" value="<?php echo isset($_GET['reg-date']) ? $_GET['reg-date'] : '';?>" >   
                                            </div>

                                            

                                            <div class="col-sm-4">
                                                <select class="form-control" id="rating" name="rating">                                            
                                                    <option value="0" <?php echo isset($_GET['rating']) && $_GET['rating'] == 0 ? "selected" : "";  ?> >By rating</option>
                                                    <option value="1" <?php echo isset($_GET['rating']) && $_GET['rating'] == 1 ? "selected" : "";  ?> >1 Star rating</option>
                                                    <option value="2" <?php echo isset($_GET['rating']) && $_GET['rating'] == 2 ? "selected" : "";  ?> >2 Stars rating</option>
                                                    <option value="3" <?php echo isset($_GET['rating']) && $_GET['rating'] == 3 ? "selected" : "";  ?> >3 Stars rating</option>
                                                    <option value="4" <?php echo isset($_GET['rating']) && $_GET['rating'] == 4 ? "selected" : "";  ?> >4 Stars rating</option>
                                                    <option value="5" <?php echo isset($_GET['rating']) && $_GET['rating'] == 5 ? "selected" : "";  ?> >5 Stars rating</option>                                              
                                                
                                                </select>  
                                            </div>



                                        </div>

                                        <div class="form-group"> 

                                            <div class="col-sm-4" >
                                                <select class="form-control" id="document-status" name="document-status"> 
                                                    <option value="---" >By Document status</option>                                           
                                                    <option value="0" <?php echo isset($_GET['document-status']) && $_GET['document-status'] == 0 ? "selected" : "";  ?> >Pending</option>
                                                    <option value="1" <?php echo isset($_GET['document-status']) && $_GET['document-status'] == 1 ? "selected" : "";  ?> >Failed</option>
                                                    <option value="2" <?php echo isset($_GET['document-status']) && $_GET['document-status'] == 2 ? "selected" : "";  ?> >Expired</option>
                                                    <option value="3" <?php echo isset($_GET['document-status']) && $_GET['document-status'] == 3 ? "selected" : "";  ?> >Approved</option>                                                                                                    
                                                </select>  
                                            </div>

                                        </div>
                                        
                                        
                                        <hr />
                                        <div style='text-align:left;'><button type="submit" class="btn btn-primary" value="1" name="filter-records" >Filter</button> <a href='<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>' class="btn btn-success" value="1" name="filter-reset" >Reset</a></div> 
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
                      
                    <h3 class="box-title">Drivers <?php if($number_of_drivers) echo "- Showing " . $page_number ." of ". $pages; ?></h3>
                    <div style="text-align: right;"><button class="btn btn-success" id="export-data" >Export Data</button></div>

                  </div><!-- /.box-header -->
                <div class="box-body">
                    <div  class="row"> 
                        <div  class="col-sm-4"> 
                            <form  id="sort-form" enctype="multipart/form-data" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="get" >                                   
                                            
                                                        
                                    
                                        <select class="form-control" id="sort" name="sort">                                            
                                            <option value="2" <?php echo isset($_GET['sort']) && $_GET['sort'] == 2 ? "selected" : "";  ?> >Sort by Date Registered</option>
                                            <option value="1" <?php echo isset($_GET['sort']) && $_GET['sort'] == 1 ? "selected" : "";  ?> >Sort by Name</option> 
                                            <option value="3" <?php echo isset($_GET['sort']) && $_GET['sort'] == 3 ? "selected" : "";  ?>>Sort by Not Activated</option>
                                            <option value="4" <?php echo isset($_GET['sort']) && $_GET['sort'] == 4 ? "selected" : "";  ?>>Sort by Availability</option>
                                            <option value="5" <?php echo isset($_GET['sort']) && $_GET['sort'] == 5 ? "selected" : "";  ?>>Sort by City</option>
                                        </select>               
                            
                            </form>
                        </div>
                    </div>
                    <br />
                    <form  enctype="multipart/form-data" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="get" >                                   
            
                        <div style="float:left;width:40%;"><a href="all-drivers.php"class="btn btn-default">Show All Drivers</a> </div>
                        <div class="input-group add-on" style="float:right;width:40%;">
                            
                            <input required class="form-control" placeholder="Search:" name="search-term" id="search-term" type="text" maxlength = "50" >
                            <div class="input-group-btn">
                            <button class="btn btn-default" type="submit" name="search" id="search" value = "1" ><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </form>
                  <br />
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
                    <div class = "table-responsive">
                        <table class='table table-bordered'>
                        <thead>
                            <tr>
                            <th>#</th>
                                <th>Photo</th>
                                <th>Driver ID</th>    
                                <th>Driver Name</th>
                                <th>City</th>
                                <th>Availability</th>
                                <th>Wallet Amount</th>
                                <th>Car Model / License</th>
                                <th>Date Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            
                                $count = 1 + (($page_number - 1) * ITEMS_PER_PAGE);
                                $default_currency_symbol = !empty($_SESSION['default_currency']) ? $_SESSION['default_currency']['symbol'] : "₦";
                                foreach($drivers_page_items as $driverspageitems){

                                    if(!empty($driverspageitems['is_activated'])){
                                        //$account_activate = "<a href='modify-drvr.php?action=deact&id=".$driver_page_items['driver_id']."' class='btn btn-xs btn-danger'>Deactivate account</a>";
                                        $account_activate = "<a data-actval='0' id='act-btn-{$driverspageitems['driver_ids']}' onclick='activateDriverAccount({$driverspageitems['driver_ids']},$(this))' style='margin-top: 5px;' class='btn btn-xs btn-danger' >Deactivate account</a>";
                                    }else{
                                        //$account_activate = "<a href='modify-drvr.php?action=act&id=".$driver_page_items['driver_id']."' class='btn btn-xs btn-success'>Activate account [{$act_code}]</a>";
                                        $account_activate = "<a data-actval='1' id='act-btn-{$driverspageitems['driver_ids']}' onclick='activateDriverAccount({$driverspageitems['driver_ids']},$(this))' style='margin-top: 5px;' class='btn btn-xs btn-success'>Activate account</a>";
                                    }
                                    
                                    $driver_req_acc_del = $driverspageitems['account_deleted'] == 1 ? "style='text-decoration:line-through;color:red;'" : "";

                                    $drvr_id = str_pad($driverspageitems['driver_ids'] , 5, '0', STR_PAD_LEFT);
                                    //$photo = explode('/',$driverspageitems['photo_file']);
                                    $photo_file = isset($driverspageitems['photo_file']) ? $driverspageitems['photo_file'] : "0";
                                    $driver_name = $driverspageitems['firstname'] . " " .  $driverspageitems['lastname'];
                                    $driver_name_enc = urlencode($driver_name);
                                    
                                    $phone = (!empty(DEMO) ? mask_string($driverspageitems['phone']) : $driverspageitems['phone']);
                                    if($driverspageitems['is_activated'] && (strtotime($driverspageitems['location_date'] . ' UTC') > (time() - LOCATION_INFO_VALID_AGE)) && !empty($driverspageitems['available'])){
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

                                    $car_model_license = $driverspageitems['car_model']  ."<br>[". $driverspageitems['car_plate_num']."]";
                                    $date_registered = date('l, M j, Y H:i:s',strtotime($driverspageitems['account_create_date'].' UTC'));

                                    echo "<tr><td>". $count++ . "</td><td>"."<img style='display:block;' class='' width='32px' src='../photofile.php?file=". $photo_file . "' />{$online_status}"."</td><td>".$drvr_id."</td><td {$driver_req_acc_del}>".$driver_name . "<br>" . $driverspageitems['country_dial_code']." ".$phone. "</td><td>".$driverspageitems['r_title']."</td><td>". $availability . "</td><td>".$default_currency_symbol.$driverspageitems['wallet_amount']."</td><td>".$car_model_license ."</td><td>". $date_registered  ."</td><td>". "{$account_activate} <a href='view-driver.php?id=". $driverspageitems['driver_ids'] ."' class='btn btn-margin-top btn-xs btn-primary'>View</a> <a href = 'all-bookings.php?search-booking=1&booking-driver=" . $driver_name ."&booking-driverid=".$driverspageitems['driver_ids'] ."' class='btn btn-margin-top btn-xs btn-warning'>Bookings</a> <a data-routeid = '{$driverspageitems['route_id']}' data-driverid = '{$driverspageitems['driver_ids']}' data-datetime='{$location_date}' data-lat='{$driverspageitems['drvlat']}' data-long='{$driverspageitems['drvlong']}' {$disabled} href='#driver-location-map-container' class='btn btn-margin-top btn-xs btn-success drvr-location' >Track Driver </a> <a data-drvrid = '".$driverspageitems['driver_ids'] ."' class='btn btn-margin-top btn-xs btn-primary msg-drvr'>Message</a> <a data-msg='This will delete all records of this driver. Bookings which this driver was assigned will be set to unallocated. Do you want to continue?' href='modify-drvr.php?action=del&id=". $driverspageitems['driver_ids'] ."' class='delete-item btn btn-margin-top btn-xs btn-danger' id='mod-drvr'>Delete Driver</a> " . "</td></tr>";
                                }
                        
                            ?>
                        </tbody>
                        </table>
                    </div>              
                   <?php if(!$number_of_drivers){ echo "<h1 style='text-align:center;'>Nothing to Show. Add Drivers to get this area populated.</h1>";} ?>
                </div><!-- /.box-body -->
              </div>


              </div><!--/col-sm-12-->



            </div> <!--/row-->

                <div id="driver-location-map-container" style='display:none;' >
                    <h4 id='location-update'></h4>
                    <div style="width:500px;height:500px;" id="driver-location-map">
                </div>
                    

            </div>

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
                            </div>   -->
                                
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


<script src="../js/carsvg.js"></script> 
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
        var route_id = 0;

        

$(function(){
    $('#reg-date').datepicker({
        todayHighlight: true,
        format: "yyyy-mm-dd"
    });
})


$('#export-data').on('click', function(){
  $('#export-dialog').modal('show');
})

$('#export-dialog').on('shown.bs.modal', function() {
  $('#exportdateinput').datepicker({
    format: "yyyy-mm-dd",
    todayHighlight: true
  });
});






$('#export_data_btn').on('click', function(e){

  e.preventDefault();

  var export_file_format = $('#export-file-format').val();

  
  //send message through AJAX

  $('#busy').modal('show');
  $('#export-dialog').modal('hide');
  var post_data = {'action':'exportDriverReg', 'type': export_file_format};
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
                          text: 'Failed to export data!',
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
        


        if (typeof google === 'object' && typeof google.maps === 'object') {
            
            if(typeof mapOptions === 'undefined'){
                mapOptions = {
                    center: new google.maps.LatLng(9.0338725,8.677457),
                    zoom: 18,
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
                let icon = {
                                                path : car_svg_data,
                                                fillColor: '#283593',
                                                fillOpacity: 1,
                                                anchor: new google.maps.Point(50,50),
                                                strokeWeight: 0,
                                                scale: 0.4,
                                                rotation: 0
                                            };
                marker = new google.maps.Marker({
                    position: latLong,
                    map: map,
                    icon: icon,
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
                route_id = $(this).data('routeid');
                clearInterval(location_update_timer_id);
                location_update_timer_id = setInterval(updateDriverLocation,5000);
                $('#location-update').html('Last location update: ' + location_date);
                latLong = new google.maps.LatLng(drv_lat,drv_long);
                marker.setPosition(latLong);
                map.setZoom(18);
                map.panTo(marker.getPosition());
        })



        function updateDriverLocation(){
            if(!driver_id)return;
            var post_data = {'action':'getDriverLocation','driver_id' : driver_id, 'route_id' : route_id };
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

                            let marker_icon = marker.getIcon();
                            marker_icon.rotation = driver_data[key].b_angle;
                            marker.setIcon(marker_icon);
                            
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


        jQuery('#sort').on('change',function(){
                
                jQuery('#sort-form').submit();
                                    
        })



        function activateDriverAccount(driver_id,elem){
            let act_val = elem.data('actval');
            if(!driver_id)return;
            $('#busy').modal('show');
            var post_data = {'action_get':'activateDriverAccount','act_value' : act_val,'driver_id' : driver_id};
            var search_data = [];
            $.ajax({
                url: ajaxurl,
                type: 'GET',
                crossDomain:true,
                xhrFields: {withCredentials: true},
                data: post_data,
                success: function (data, status)
                {
                    $('#busy').modal('hide');
                    try{
                        var data_obj = JSON.parse(data);
                    }catch(e){
                        
                        return;
                    }

                    if(data_obj.hasOwnProperty('success')){
                        
                        $(`#act-btn-${driver_id}`).data('actval', act_val == 1 ? 0 : 1);
                        $(`#act-btn-${driver_id}`).html(act_val == 1 ? "Deactivate account" : "Activate account");
                        $(`#act-btn-${driver_id}`).removeClass('btn-danger');
                        $(`#act-btn-${driver_id}`).removeClass('btn-success');
                        $(`#act-btn-${driver_id}`).addClass(act_val == 1 ? "btn-danger" : "btn-success");
                        $('#drv-pend-act').html(data_obj.num_not_activated);

                    
                    }  
        
                },
                error:function(jqXHR,textStatus, errorThrown){
                    $('#busy').modal('hide');
                   return;
                }
                
            });


        }





</script>
   