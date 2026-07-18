

<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        View all transactions. 
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">   
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">STATS</h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <br />

                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                    <span class="info-box-icon bg-grey"><i class="fa fa-usd"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Transactions</span>
                        <span class="info-box-number"><?php echo $number_of_wallet_transactions; ?></span>
                    </div>
                    <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>

            </div><!-- /.box-body -->
        </div>

    </div><!--/col-sm-12-->
</div>


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
                                    <select class="form-control" id="trans-type" name="trans-type">
                                        
                                    <option value="" selected>Transaction Status</option> 
                                    <option value="0" <?php echo isset($_GET['trans-type']) && $_GET['trans-type'] == "0" ? "selected" : ""; ?>>Wallet funding (User)</option>
                                    <option value="1" <?php echo isset($_GET['trans-type']) && $_GET['trans-type'] == "1" ? "selected" : ""; ?>>Wallet funding (Admin)</option>
                                    <option value="2" <?php echo isset($_GET['trans-type']) && $_GET['trans-type'] == "2" ? "selected" : ""; ?>>Credit</option>
                                    <option value="3" <?php echo isset($_GET['trans-type']) && $_GET['trans-type'] == "3" ? "selected" : ""; ?>>Debit</option>
                                    </select>                
                                </div>
                                
                                <div class="col-sm-6">
                                    <input  type="text" class="form-control" id="datepickerbsearch" placeholder="Transaction date" name="trans-date" value="<?php echo isset($_GET["trans-date"]) ? $_GET["trans-date"] : ''; ?>" >
                                </div>



                            </div>   
                            
                            
                            <div class="form-group">

                                <div  class="col-sm-6">                
                                    <select class="form-control" id="trans-acc-type" name="trans-acc-type">                                                
                                        <option value="" selected>Account type</option> 
                                        <option value="0" <?php echo isset($_GET['trans-acc-type']) && $_GET['trans-acc-type'] == "0" ? "selected" : ""; ?>>Customer</option>
                                        <option value="1" <?php echo isset($_GET['trans-acc-type']) && $_GET['trans-acc-type'] == "1" ? "selected" : ""; ?>>Driver</option>
                                        <option value="2" <?php echo isset($_GET['trans-acc-type']) && $_GET['trans-acc-type'] == "2" ? "selected" : ""; ?>>Franchise</option>
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
    <div class="col-sm-12" >
		<div class="box box-success">
            <div class="box-header with-border">
              <h3 class="box-title">Transactions <?php if($number_of_wallet_transactions) echo "- Showing " . $page_number ." of ". $pages; ?></h3>
             
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                    
                <div style="text-align: right;display:none"><button class="btn btn-success" id="export-data" >Export Data</button></div>
             
                <br />

            
                <form  enctype="multipart/form-data" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="get" >                                   
        
                    <div style="float:left;width:40%;"><a href="all-transactions.php"class="btn btn-default">Show All Transactions</a> </div>
                    <div class="input-group add-on" style="float:right;width:40%;">
                        
                        <input required class="form-control" placeholder="Search by Transaction ID" name="search-term" id="search-term" type="text" maxlength = "50" >
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
                <table class='table table-bordered table-striped'>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Transaction ID</th>
                        <th>User</th>    
                        <th>Amount</th>
                        <th>Wallet Balance</th> 
                        <th>Booking ID</th>                      
                        <th>Type</th>
                        <th>Description</th>
                        <th>Date Processed</th>                   
                    </tr>
                </thead>
                <tbody>
                    <?php  
                    
                    
                    $count = 1 + (($page_number - 1) * ITEMS_PER_PAGE);
                    $default_currency_symbol = !empty($_SESSION['default_currency']) ? $_SESSION['default_currency']['symbol'] : "₦";
                    
                    foreach($wallet_transactions_data as $wallettransactiondata){
                        
                        $user_type_name = "";
                        if($wallettransactiondata['user_type'] == 2){
                            $user_type_name = "Franchise";
                            $user = "<a title='{$user_type_name}' style='color:orange;' href='view-franchise.php?id={$wallettransactiondata['user_id']}'>" .$wallettransactiondata['franchise_name'] . "<br> " .  (!empty(DEMO) ? mask_string($wallettransactiondata['franchise_phone']) : $wallettransactiondata['franchise_phone']) . "</a>";
                        }elseif($wallettransactiondata['user_type'] == 1){
                            $user_type_name = "Driver";
                            $user = "<a title='{$user_type_name}' style='color:blue;' href='view-driver.php?id={$wallettransactiondata['user_id']}'>" . $wallettransactiondata['firstname'] . " " . $wallettransactiondata['lastname'] . "<br> " . $wallettransactiondata['country_dial_code']. " " . (!empty(DEMO) ? mask_string($wallettransactiondata['phone']) : $wallettransactiondata['phone']) . "</a>";
                        }else{
                            
                            if($wallettransactiondata['account_type'] == 1){
                                $user_type_name = "Customer";
                                $user = "<a title='{$user_type_name}' style='color:green;' href='view-customer.php?id={$wallettransactiondata['user_id']}'>" . $wallettransactiondata['ufirstname'] . " " . $wallettransactiondata['ulastname'] . "<br> " . $wallettransactiondata['ucountry_dial_code']. " " . (!empty(DEMO) ? mask_string($wallettransactiondata['uphone']) : $wallettransactiondata['uphone']) . "</a>";
                            }else{
                                $user_type_name = "Staff";
                                $user = "<a title='{$user_type_name}' style='color:green;' href='view-staff.php?id={$wallettransactiondata['user_id']}'>" . $wallettransactiondata['ufirstname'] . " " . $wallettransactiondata['ulastname'] . "<br> " . $wallettransactiondata['ucountry_dial_code']. " " . (!empty(DEMO) ? mask_string($wallettransactiondata['uphone']) : $wallettransactiondata['uphone']) . "</a>";
                            }                            
                        }

                        $date_settled = !empty($wallettransactiondata['transaction_date']) ? date('l, M j, Y H:i:s',strtotime($wallettransactiondata['transaction_date'].' UTC')) : "---";
                                               

                        $transaction_type = '';
                        $booking_id = !empty($wallettransactiondata['book_id']) ? "<a href='view-booking.php?bkid={$wallettransactiondata['book_id']}'>" . str_pad($wallettransactiondata['book_id'] , 5, '0', STR_PAD_LEFT) . "</a>" : 'N/A';
                        switch($wallettransactiondata['type']){
                            case 0:
                            $transaction_type = "<i class='fa fa-circle' style='color:green;'></i> Wallet Funding (Self)";
                            break;

                            case 1:
                            $transaction_type = "<i class='fa fa-circle' style='color:green;'></i> Wallet Funding (Admin)";
                            break;

                            case 2:
                            $transaction_type = "<i class='fa fa-circle' style='color:green;'></i> Credit";
                            break;

                            case 3:
                            $transaction_type = "<i class='fa fa-circle' style='color:red;'></i> Debit";
                            break;
                        }
                                                    
                        echo "<tr><td>". $count++ . "</td><td>" . strtoupper($wallettransactiondata['transaction_id']) . "</td><td>". $user ."</td><td>" . $wallettransactiondata['cur_symbol']. $wallettransactiondata['amount']. "</td><td>" . $default_currency_symbol . $wallettransactiondata['wallet_balance'] ."</td><td>". $booking_id ."</td><td>". $transaction_type . "</td><td>" .$wallettransactiondata['desc'] . "</td><td>" . date('l, M j, Y H:i:s',strtotime($wallettransactiondata['transaction_date'].' UTC')) . "</td><td></tr>";
                    }
                    
                    ?>
                </tbody>
                </table>
            </div>
                                  
            <?php if(empty($wallet_transactions_data)){ echo "<h1 style='text-align:center;'>Nothing to Show. No Transaction Data.</h1>";} ?>
      
      
      
      				            
            </div>
            <!-- /.box-body -->
          </div>

    </div> <!--/col-sm-8-->
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


$('#export_data_btn').on('click', function(e){

  e.preventDefault();

  var export_file_format = $('#export-file-format').val();
  //send message through AJAX

  $('#busy').modal('show');
  $('#export-dialog').modal('hide');
  var post_data = {'action':'exportPayoutsData','type': export_file_format};
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


















