

<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
        Process withdrawal requests. 
        </div>
    </div>
</div>



<div class="row">
    <div class="col-sm-12" >
		<div class="box box-success">
            <div class="box-header with-border">
              <h3 class="box-title">Withdrawal Requests</h3>
             
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                    <div  class="row"> 
                        <div  class="col-sm-12"> 
                            <form  id="sort-form" enctype="multipart/form-data" class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="get" >                                   
                                <div class="form-group">
                                    <div class="col-sm-3">            
                                        <select style="margin-top:5px;" class="form-control" id="view_user_type" name="view_user_type">
                                            <option value="1" <?php echo isset($_GET['view_user_type']) && $_GET['view_user_type'] == 1 ? "selected" : "";  ?> >Show All</option>
                                            <option value="2" <?php echo isset($_GET['view_user_type']) && $_GET['view_user_type'] == 2 ? "selected" : "";  ?> >Franchise Requests Only</option> 
                                            <option value="3" <?php echo isset($_GET['view_user_type']) && $_GET['view_user_type'] == 3 ? "selected" : "";  ?>>Driver Requests Only</option>                                            
                                        </select>               
                                    </div>
                                    <div class="col-sm-3">
                                        <select style="margin-top:5px;" class="form-control" id="filter" name="filter">
                                            <option value="1" <?php echo isset($_GET['filter']) && $_GET['filter'] == 1 ? "selected" : "";  ?> >Filter by None</option>
                                            <option value="2" <?php echo isset($_GET['filter']) && $_GET['filter'] == 2 ? "selected" : "";  ?> >Filter by Pending</option> 
                                            <option value="3" <?php echo isset($_GET['filter']) && $_GET['filter'] == 3 ? "selected" : "";  ?>>Filter by Settled</option>
                                            <option value="4" <?php echo isset($_GET['filter']) && $_GET['filter'] == 4 ? "selected" : "";  ?>>Filter by Declined</option>                                            
                                        </select>  

                                    </div>    
                                    
                                </div>
                                <button class="btn btn-sm btn-primary" type="submit">OK</button>
                            </form>
                        </div>
                    </div>
                    <br />
                    <div style="float:left;width:40%;"><a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>"class="btn btn-default">Show All Withdrawals</a> </div>
                    
                  <br />
                  <br />
                  <hr>
                  <div style="text-align: right;"><button class="btn btn-success" id="export-data" >Export Data</button></div>
             
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
                        <th>User</th>    
                        <th>Amount</th>
                        <th>Wallet Amount (Old)</th>
                        <th>Wallet Balance (New)</th>                       
                        <th>Status</th>
                        <th>Date Requested</th>
                        <th>Date Processed</th> 
                        <th>Action</th>                     
                    </tr>
                </thead>
                <tbody>
                    <?php  
                    
                    
                    $count = 1 + (($page_number - 1) * ITEMS_PER_PAGE);
                    $default_currency_symbol = !empty($_SESSION['default_currency']) ? $_SESSION['default_currency']['symbol'] : "₦";
                    
                    foreach($withdrawal_requests_data as $withdrawalrequestsdata){
                        $url_str = $_SERVER['REQUEST_URI'];
                        $url_str_parts = parse_url($url);
                        $approve_url = "{$_SERVER['SCRIPT_NAME']}?action=approve&wid={$withdrawalrequestsdata['wid']}";
                        $reject_url = "{$_SERVER['SCRIPT_NAME']}?action=reject&wid={$withdrawalrequestsdata['wid']}";
                        if(isset($url_str_parts['query'])){
                            parse_str($url_str_parts['query'], $params_arr);
                            $params_arr['action'] = "approve";
                            $params_arr['wid'] = $withdrawalrequestsdata['wid'];
                            $url_str_parts['query'] = http_build_query($params_arr);
                            $approve_url = "{$_SERVER['SCRIPT_NAME']}?{$url_str_parts['query']}";
                            $params_arr['action'] = "reject";
                            $url_str_parts['query'] = http_build_query($params_arr);
                            $reject_url = "{$_SERVER['SCRIPT_NAME']}?{$url_str_parts['query']}";

                        }

                        $user_type_name = "";
                        if($withdrawalrequestsdata['user_type']){
                            $user_type_name = "Franchise";
                            $user = "<a style='color:orange;' href='view-franchise.php?id={$withdrawalrequestsdata['person_id']}'>" .$withdrawalrequestsdata['franchise_name'] . "<br> " .  (!empty(DEMO) ? mask_string($withdrawalrequestsdata['franchise_phone']) : $withdrawalrequestsdata['franchise_phone']) . "</a>";
                        }else{
                            $user_type_name = "Driver";
                            $user = "<a style='color:blue;' href='view-driver.php?id={$withdrawalrequestsdata['person_id']}'>" . $withdrawalrequestsdata['firstname'] . " " . $withdrawalrequestsdata['lastname'] . "<br> " . $withdrawalrequestsdata['country_dial_code']. " " . (!empty(DEMO) ? mask_string($withdrawalrequestsdata['phone']) : $withdrawalrequestsdata['phone']) . "</a>";
                        }

                        $date_settled = !empty($withdrawalrequestsdata['date_settled']) ? date('l, M j, Y H:i:s',strtotime($withdrawalrequestsdata['date_settled'].' UTC')) : "---";
                        $approve_request = $withdrawalrequestsdata['request_status'] == 0 ? "<a id='approve-btn' href='#' data-url='{$approve_url}' data-msg='This action will approve this withdrawal request and money transfered to the {$user_type_name} account' class='btn btn-xs btn-success confirm-action'>Approve</a>" : "";
                        $decline_request = $withdrawalrequestsdata['request_status'] == 0 ? "<a id='reject-btn' href='#' data-url='{$reject_url}' data-msg='This action will reject this withdrawal request and {$user_type_name} wallet debit will be reversed' class='btn btn-xs btn-danger confirm-action'>Reject</a>" : "";
                        

                        switch($withdrawalrequestsdata['request_status']){
                            case 0:
                            $payout_status = "<i class='fa fa-circle' style='color:purple;'></i> Pending";
                            break;

                            case 1:
                            $payout_status = "<i class='fa fa-circle' style='color:red;'></i> Declined";
                            break;

                            case 2:
                            $payout_status = "<i class='fa fa-circle' style='color:green;'></i> Settled";
                            break;

                        }
                                                    
                        echo "<tr><td>". $count++ . "</td><td>" . $user. "</td><td>".$withdrawalrequestsdata['cur_symbol']. $withdrawalrequestsdata['withdrawal_amount'] . "</td><td>" . $default_currency_symbol. $withdrawalrequestsdata['wallet_amount']. "</td><td>" . $default_currency_symbol . $withdrawalrequestsdata['wallet_balance'] ."</td><td>". $payout_status ."</td><td>". date('l, M j, Y H:i:s',strtotime($withdrawalrequestsdata['date_requested'].' UTC')) . "</td><td>" . $date_settled . "</td><td>{$approve_request} {$decline_request}</td></tr>";
                    }
                    
                    ?>
                </tbody>
                </table>
            </div>
                                  
            <?php if(empty($withdrawal_requests_data)){ echo "<h1 style='text-align:center;'>Nothing to Show. No Withdrawal Request.</h1>";} ?>
      
      
      
      				            
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


















