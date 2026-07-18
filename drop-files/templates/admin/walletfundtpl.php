

<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Fund any wallet quickly. You can enter a negative amount to deduct from wallet.
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-10" >
		<div class="box box-success">
            <div class="box-header with-border">
              <h3 class="box-title">Wallet</h3>
             
            </div>
            <!-- /.box-header -->
            <div class="box-body">
             
             
                      <form  enctype="multipart/form-data" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" >
                            
                                                  
                             <div class="form-group">                       
                               <div class="col-sm-4">
                                    <label for="scope">Select account &nbsp;</label>
                                    <select class="form-control" id="scope" name="scope">
                                        <option value="1" >Driver</option>
                                        <option value="2" >Customer</option>
                                        <option value="3" >Staff</option>
                                        
                                    </select>
                                </div>                              
                             </div>

                             <div class="form-group">
                                <div class="col-sm-4">
                                    <div id="account-customer" style='display:none'>
                                        <label>Customer name &nbsp;</label>
                                        <input  type="text" class="form-control" id="booking-customer" placeholder="" name="customer-scope-name" value="" >
                                        <input  type="text" hidden='hidden' id="booking-customerid" placeholder="" name="customer-scope-id" value="" >
                                    </div>
                                    
                                    <div id="account-driver" style='display:none'>
                                        <label>Driver name &nbsp;</label>
                                        <input  type="text" class="form-control" id="booking-driver" placeholder="" name="driver-scope-name" value="" >
                                        <input  type="text" hidden='hidden' id="booking-driverid" placeholder="" name="driver-scope-id" value="" >
                                    </div>

                                    <div id="account-staff" style='display:none'>
                                        <label>Staff name &nbsp;</label>
                                        <input  type="text" class="form-control" id="booking-staff" placeholder="" name="staff-scope-name" value="" >
                                        <input  type="text" hidden='hidden' id="booking-staffid" placeholder="" name="staff-scope-id" value="" >
                                    </div>
                                </div>
                             </div>


                             <div class="form-group">
                                <div class="col-sm-4">
                                    <label for="fund-currency">Currency</label>                                    
                                    <select class="form-control" id="fund-currency" name="fund-currency">
                                        <?php
                                            foreach($currency_page_items as $currencypageitems){
                                                $default = "";
                                                $style = "";
                                                if($currencypageitems['default'] == 1){
                                                    $default = "[Default]";
                                                    $style = "style='font-weight:bold;'";
                                                }
                                                echo "<option {$style} value='{$currencypageitems['id']}'>{$currencypageitems['symbol']} {$currencypageitems['name']} - {$currencypageitems['iso_code']} - {$default}</option>";
                                            }
                                        ?>
                                                                                                                                                                                        
                                    </select>
                                </div>

                            </div>


                             <div class="form-group">                       
                               <div class="col-sm-4">
                                    <label for="fund-amount"><span style="color:red">*</span>Amount</label>
                                    <input  type="number"  step="0.01" class="form-control" required="required" id="fund-amount" placeholder="" name="fund-amount" value="" >
                                </div>  
                                
                             </div>


                            <div class="form-group">                       
                               <div class="col-sm-4">
                                    <label for="fund-comment"><span style="color:red">*</span>Comment</label>
                                    <textarea  rows="3" style="display:block; width:100%;" name="fund-comment" required="required" maxlength="250"></textarea>
                                </div>  
                                
                             </div>



                                                       
                              
                           
                          
                             

                                                 
                            
                             
                              <hr />
                           <button type="submit" class="btn btn-primary" value="1" name="fundwallet" >Fund wallet</button> 
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
              <h3 class="box-title">Funding History</h3>
              <div style="text-align: right;"><button class="btn btn-success" id="export-data" >Export Data</button></div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
        

                        <form  enctype="multipart/form-data" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="GET" >

                            <div style="width:40%;"><a href="walletfund.php" class="btn btn-default">Show all Funding</a> </div>
                            <hr>
                            <div style="width:40%;">                                
                                <div class="form-group">                       
                                    <div class="col-sm-12">
                                        <label for="search-context">Select a search context &nbsp;</label>
                                        <select class="form-control" id="scope-context" name="search-context">
                                            <option value="1" <?php echo isset($_GET['search-context']) && (int) $_GET['search-context'] == 1 ? "selected" : "" ;?> >Driver</option>
                                            <option value="2" <?php echo isset($_GET['search-context']) && (int) $_GET['search-context'] == 2 ? "selected" : "" ;?> >Customer</option>
                                            <option value="3" <?php echo isset($_GET['search-context']) && (int) $_GET['search-context'] == 3 ? "selected" : "" ;?> >Staff</option>
                                            <!-- <option value="4" <?php echo isset($_GET['search-context']) && (int) $_GET['search-context'] == 4 ? "selected" : "" ;?> >Date funded</option>  -->
                                        </select>
                                    </div>                              
                                </div>

                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <div id="account-customer2" style='display:none'>
                                            <label>Customer name &nbsp;</label>
                                            <input  type="text" class="form-control" id="booking-customer2" placeholder="" name="customer-scope-name" value="<?php echo isset($_GET['search-context']) && (int) $_GET['search-context'] == 2 ? $_GET['customer-scope-name'] : "" ;?>" >
                                            <input  type="text" hidden='hidden' id="booking-customerid2" placeholder="" name="customer-scope-id2" value="<?php echo isset($_GET['search-context']) && (int) $_GET['search-context'] == 2 ? $_GET['customer-scope-id2'] : "" ;?>" >
                                        </div>
                                        
                                        <div id="account-driver2" style='display:none'>
                                            <label>Driver name &nbsp;</label>
                                            <input  type="text" class="form-control" id="booking-driver2" placeholder="" name="driver-scope-name" value="<?php echo isset($_GET['search-context']) && (int) $_GET['search-context'] == 1 ? $_GET['driver-scope-name'] : "" ;?>" >
                                            <input  type="text" hidden='hidden' id="booking-driverid2" placeholder="" name="driver-scope-id2" value="<?php echo isset($_GET['search-context']) && (int) $_GET['search-context'] == 1 ? $_GET['driver-scope-id2'] : "" ;?>" >
                                        </div>

                                        <div id="account-staff2" style='display:none'>
                                            <label>Staff name &nbsp;</label>
                                            <input  type="text" class="form-control" id="booking-staff2" placeholder="" name="staff-scope-name" value="<?php echo isset($_GET['search-context']) && (int) $_GET['search-context'] == 2 ? $_GET['staff-scope-name'] : "" ;?>" >
                                            <input  type="text" hidden='hidden' id="booking-staffid2" placeholder="" name="staff-scope-id2" value="<?php echo isset($_GET['search-context']) && (int) $_GET['search-context'] == 3 ? $_GET['staff-scope-id2'] : "" ;?>" >
                                        </div>

                                        <div id="fund-date" style='display:block'>
                                            <label>Date funded &nbsp;</label>
                                            <input  type="text" class="form-control" id="datepickerbsearch" placeholder="" name="date-fund" value="<?php echo !empty($_GET['date-fund']) ? $_GET['date-fund'] : ""; ?>" >
                                            
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary" value="1" name="search-fund" >Search</button>
                            </div> 
                            <div class="clearfix"></div>                                                  
                            
                        
                        </form>

                   <!--  <div class="clearfix"></div> -->


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
                                <th style="">Type</th>    
                                <th style="">Details</th>
                                <th style="">Amount funded</th>
                                <th style="">Wallet balance</th>
                                <th style="">Date</th>
                                <th style="">Comment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            
                            $count = 1 + (($page_number - 1) * ITEMS_PER_PAGE);
                                foreach($wallet_funding_data as $walletfundingdata){
                                    $type = '';
                                    $details ="";
                                    if($walletfundingdata['fund_type'] == 1){
                                        $type = "Driver";
                                        $details = $walletfundingdata['driver_firstname'] . " " . $walletfundingdata['driver_lastname'] . "(" . (!empty(DEMO) ? mask_string($walletfundingdata['driver_phone']) : $walletfundingdata['driver_phone']) .") wallet was funded by " . $walletfundingdata['staff_firstname'] . " " .  $walletfundingdata['staff_lastname'] ;
                                    }elseif($walletfundingdata['fund_type'] == 2){
                                        $type = "Customer";
                                        $details = $walletfundingdata['customer_firstname'] . " " . $walletfundingdata['customer_lastname'] . "(" . (!empty(DEMO) ? mask_string($walletfundingdata['customer_phone']) : $walletfundingdata['customer_phone']) .") wallet was funded by " . $walletfundingdata['staff_firstname'] . " " .  $walletfundingdata['staff_lastname'] ;
                                    }else{
                                        $type = "Staff";
                                        $details = $walletfundingdata['customer_firstname'] . " " . $walletfundingdata['customer_lastname'] . "(" . (!empty(DEMO) ? mask_string($walletfundingdata['customer_phone']) : $walletfundingdata['customer_phone']) .") wallet was funded by " . $walletfundingdata['staff_firstname'] . " " .  $walletfundingdata['staff_lastname'] ;
                                    }

                                    $date_funded = date('l, M j, Y H:i:s',strtotime($walletfundingdata['date_fund'] . ' UTC'));
                                    $wallet_fund_amount = $walletfundingdata['cur_symbol'] . $walletfundingdata['fund_amount'];
                                    
                                    echo "<tr><td>". $count++ . "</td><td>{$type}</td><td>{$details}</td><td>{$wallet_fund_amount}</td><td>{$walletfundingdata['wallet_balance']}</td><td>{$date_funded}</td><td>{$walletfundingdata['fund_comment']}</td></tr>";
                                }
                        
                            ?>
                        </tbody>
                        </table>
                    </div>
                                  
                   <?php if(!$number_of_fundings){ echo "<h1 style='text-align:center;'>Nothing to Show. No wallet funding history</h1>";} ?>
      
      
      
      				            
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
  var post_data = {'action':'exportWalletFundData','type': export_file_format};
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

    jQuery(function () {

        var opt_val =  jQuery("#scope").find(':selected').val();

        if(opt_val == 1){
            jQuery("#account-driver").show();
            jQuery("#account-customer").hide();
            jQuery("#account-staff").hide();
        }else if(opt_val == 2){
            jQuery("#account-driver").hide();
            jQuery("#account-customer").show();
            jQuery("#account-staff").hide();
        }else if(opt_val == 3){
            jQuery("#account-driver").hide();
            jQuery("#account-customer").hide();
            jQuery("#account-staff").show();
        }


        var opt_val2 =  jQuery("#scope-context").find(':selected').val();

        if(opt_val2 == 1){
            jQuery("#account-driver2").show();
            jQuery("#account-customer2").hide();
            jQuery("#account-staff2").hide();
            //jQuery("#fund-date").hide();
        }else if(opt_val2 == 2){
            jQuery("#account-driver2").hide();
            jQuery("#account-customer2").show();
            jQuery("#account-staff2").hide();
            //jQuery("#fund-date").hide();
        }else if(opt_val2 == 3){
            jQuery("#account-driver2").hide();
            jQuery("#account-customer2").hide();
            jQuery("#account-staff2").show();
            //jQuery("#fund-date").hide();
        }else{
            jQuery("#account-driver2").hide();
            jQuery("#account-customer2").hide();
            jQuery("#account-staff2").hide();
            //jQuery("#fund-date").show();
        }

        jQuery('#scope').on('change', function(){

        var opt_val =  jQuery("#scope").find(':selected').val();

             if(opt_val == 1){
                jQuery("#account-driver").show();
                jQuery("#account-customer").hide();
                jQuery("#account-staff").hide();
            }else if(opt_val == 2){
                jQuery("#account-driver").hide();
                jQuery("#account-customer").show();
                jQuery("#account-staff").hide();
            }else if(opt_val == 3){
                jQuery("#account-driver").hide();
                jQuery("#account-customer").hide();
                jQuery("#account-staff").show();
            }


        });


        
        jQuery('#scope-context').on('change', function(){

            var opt_val2 =  jQuery("#scope-context").find(':selected').val();

            if(opt_val2 == 1){
                jQuery("#account-driver2").show();
                jQuery("#account-customer2").hide();
                jQuery("#account-staff2").hide();
                //jQuery("#fund-date").hide();
            }else if(opt_val2 == 2){
                jQuery("#account-driver2").hide();
                jQuery("#account-customer2").show();
                jQuery("#account-staff2").hide();
                //jQuery("#fund-date").hide();
            }else if(opt_val2 == 3){
                jQuery("#account-driver2").hide();
                jQuery("#account-customer2").hide();
                jQuery("#account-staff2").show();
                //jQuery("#fund-date").hide();
            }else{
                jQuery("#account-driver2").hide();
                jQuery("#account-customer2").hide();
                jQuery("#account-staff2").hide();
                //jQuery("#fund-date").show();
            }


        });

    });









</script>
















