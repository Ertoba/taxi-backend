
            <div class="row">
              <div class="col-sm-12">
                  <div class="alert alert-info alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-drivers-license"></i> Quick Info!</h4>
                    Get an overview of all customers.
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
                        <span class="info-box-text">Customers</span>
                        <span class="info-box-number"><?php echo $number_of_customers; ?></span>
                      </div>
                      <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                 </div>



               </div><!-- /.box-body -->
              </div>


              </div><!--/col-sm-12-->



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
                            </div>  --> 
                                
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

                                            <div class="col-sm-4" id="activity-status" >
                                                <select class="form-control" id="activity-status" name="activity-status"> 
                                                    <option value="---" <?php echo isset($_GET['activity-status']) && $_GET['activity-status'] == 0 ? "selected" : "";  ?> >By Activity</option>                                           
                                                    <option value="1" <?php echo isset($_GET['activity-status']) && $_GET['activity-status'] == 1 ? "selected" : "";  ?> >Active today</option>                                                                                                                                                     
                                                </select>  
                                            </div>

                                            <div class="col-sm-4" id="rating-select" >
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

                                            <div class="col-sm-4">                                                
                                                <input  type="text" placeholder="By date registered" readonly required= "required" class="form-control" id="reg-date" name="reg-date" value="<?php echo isset($_GET['reg-date']) ? $_GET['reg-date'] : '';?>" >   
                                            </div>

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
                      
                    <h3 class="box-title">Customers <?php if($number_of_customers) echo "- Showing " . $page_number ." of ". $pages; ?></h3>
                    <div style="text-align: right;"><button class="btn btn-success" id="export-data" >Export Data</button></div>
                  </div><!-- /.box-header -->
                <div class="box-body">
                    <form  enctype="multipart/form-data" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="get" >                                   
            
                        <div style="float:left;width:40%;"><a href="all-customers.php"class="btn btn-default">Show All Customers</a> </div>
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
                    <div class="table-responsive">
                        <table class='table table-bordered'>
                        <thead>
                            <tr>
                            <th>#</th>
                                <th>Photo</th>
                                <th>Name</th>    
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Wallet Balance</th>
                                <th>Account Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            
                            $count = 1 + (($page_number - 1) * ITEMS_PER_PAGE);
                            $default_currency_symbol = !empty($_SESSION['default_currency']) ? $_SESSION['default_currency']['symbol'] : "₦";

                                foreach($customers_page_items as $customerspageitems){
                                    //$photo = explode('/',$customerspageitems['photo_file']);
                                    $user_req_acc_del = $customerspageitems['account_deleted'] == 1 ? "style='text-decoration:line-through;color:red;'" : "";
                                    $photo_file = isset($customerspageitems['photo_file']) ? $customerspageitems['photo_file'] : "0";
                                    $email = (!empty(DEMO) ? mask_email($customerspageitems['email']) : $customerspageitems['email']);
                                    $phone = (!empty(DEMO) ? mask_string($customerspageitems['phone']) : $customerspageitems['phone']);
                                    $name =  $customerspageitems['firstname'] . " " . $customerspageitems['lastname'];                              
                                    echo "<tr><td>". $count++ . "</td><td>"."<img class='' width='32px' src='../userphotofile.php?file=". $photo_file ."' />" . "</td><td {$user_req_acc_del}>".$name."</td><td>".$email."</td><td>".$customerspageitems['country_dial_code']." ".$phone . "</td><td>".$default_currency_symbol.$customerspageitems['wallet_amount']."</td><td>".date('l, M j, Y H:i:s',strtotime($customerspageitems['account_create_date'].' UTC'))  ."</td><td>". "<a href='view-customer.php?id=". $customerspageitems['user_id'] ."' class='btn btn-margin-top btn-xs btn-success'>View</a> <a href = 'add-booking.php?customer=".$customerspageitems['user_id'] ."' class='btn btn-margin-top btn-xs btn-primary'>New Booking</a> <a href = 'all-bookings.php?search-booking=1&custphone=".$customerspageitems['phone'] ."' class='btn btn-margin-top btn-xs btn-warning'>Bookings</a> <a data-userid = '".$customerspageitems['user_id'] ."' class='btn btn-margin-top btn-xs btn-primary msg-customer'>Message</a> <a data-msg='This will delete all records of this customer. Bookings which this customer has placed will be deleted. Do you want to continue?' href='edit-customer.php?action=del&id=". $customerspageitems['user_id'] ."' class='delete-item btn btn-margin-top btn-xs btn-danger'>Delete</a> " . "</td></tr>";
                                } 
                        
                            ?>
                        </tbody>
                        </table>
                    </div>
                                  
                   <?php if(!$number_of_customers){ echo "<h1 style='text-align:center;'>Nothing to Show. Add Customers to get this area populated.</h1>";} ?>
                </div><!-- /.box-body -->
              </div>


              </div><!--/col-sm-12-->



            </div> <!--/row-->



<script>
  $('#export-data').on('click', function(){
  $('#export-dialog').modal('show');
})

$('#export-dialog').on('shown.bs.modal', function() {
  $('#exportdateinput').datepicker({
    format: "yyyy-mm-dd",
    todayHighlight: true
  });
});


$(function(){
    $('#reg-date').datepicker({
        todayHighlight: true,
        format: "yyyy-mm-dd"
    });
})

$('#export_data_btn').on('click', function(e){

  e.preventDefault();

  var export_file_format = $('#export-file-format').val();

  //send message through AJAX

  $('#busy').modal('show');
  $('#export-dialog').modal('hide');
  var post_data = {'action':'exportCustomerReg','type': export_file_format};
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

</script>



    
    