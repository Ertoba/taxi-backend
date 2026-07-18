<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
            View customer details. 
        </div>
    </div>
</div> <!--/Row-->

<div class="row">
    <div class="col-sm-12"> 
        <?php
            $photo_file = isset($user_page_items['photo_file']) ? $user_page_items['photo_file'] : "0";
        ?>
        <div class="box box-default">
            <div class="box-header with-border">
            <h3 class="box-title">Details | <?php echo !empty($user_page_items['is_activated']) ? " <span style='color:green;'>Activated</span>" : " <span style='color:red;'>Not Activated</span>"; ?></h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                
                
                    <div class="col-sm-12">
                        <div class="col-sm-2">
                            <img src="<?php echo empty($photo_file) ? "../img/usersample.jpg" : "../userphotofile.php?file=". $photo_file;?>" class="img-circle img-responsive" />
                            <br />
                        </div>
                        <div class="col-sm-3">
                            <div class="spacer"></div>
                            <h2 style="margin-top:0;"><?php echo ucwords(strtolower($user_page_items['firstname']) . " " . strtolower($user_page_items['lastname']));?></h2>
                            
                            <img style="width:100px;" src="../img/rating-<?php echo empty($user_page_items['user_rating']) ? "0.png" : $user_page_items['user_rating'] . ".png"; ?>" class="" /><br>
                            <h5><?php echo !empty($user_page_items['address']) ? $user_page_items['address'] . ", " : "" . $user_page_items['country']; ?></h5>
                            <a href="edit-customer.php?id=<?php echo  $user_page_items['user_ids']; ?>" class="btn btn-primary btn-sm">Edit Profile</a>
                            <br>
                        </div>
                        <div class="col-sm-5" style="border-left:thin solid #ccc;">               
                    
                            <h5>Phone: <?php echo $user_page_items['country_dial_code']. " " .(!empty(DEMO) ? mask_string($user_page_items['phone']) : $user_page_items['phone']); ?></h5>
                            <h5>Email: <?php echo (!empty(DEMO) ? mask_email($user_page_items['email']) : $user_page_items['email']); ?></h5>
                            <h5>Current City: <?php echo $user_page_items['r_title']; ?></h5>
                            <h5>Last Seen: <?php echo !empty($user_page_items['last_login_date']) ? date('d/m/Y g:i A',strtotime($user_page_items['last_login_date'] . ' UTC')) : "---"; ?></h5>
                            <h5>Login Count: <?php echo $user_page_items['login_count']; ?></h5>
                            <h5>Wallet Amount: <?php echo $_SESSION['default_currency']['symbol'] . $user_page_items['wallet_amount']; ?></h5>
                            <h5>Referral Code: <?php echo $user_page_items['referal_code']; ?></h5>
                            <h5>Referral Count: <?php echo $user_page_items['referral_count']; ?></h5>
                        </div>
                    </div>
                    
                   
                


            
            
            </div><!-- /.box-body -->
        </div>


    </div><!--/col-sm-12-->    
</div>






<div class="row">	
    <div class="col-sm-12" >
    

        <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="<?php echo $active_tab == 0 ? 'active' : ''?>"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Transactions</a></li>
                    <li class="<?php echo $active_tab == 1 ? 'active' : ''?>"><a href="#tab_2" data-toggle="tab" aria-expanded="false">Bookings</a></li>
                    <li class="<?php echo $active_tab == 2 ? 'active' : ''?>"><a href="#tab_3" data-toggle="tab" aria-expanded="false">Reviews</a></li>                            
                    <li class="<?php echo $active_tab == 3 ? 'active' : ''?>"><a href="#tab_4" data-toggle="tab" aria-expanded="false">Documents</a></li>                            
                    
                </ul>
                
                <div class="tab-content">
                    <div class="tab-pane <?php echo $active_tab == 0 ? 'active' : ''?>" id="tab_1">
                        <?php include('../../drop-files/templates/admin/viewcustomertransactionstpl.php'); ?>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane <?php echo $active_tab == 1 ? 'active' : ''?>" id="tab_2">
                        <?php include('../../drop-files/templates/admin/viewcustomerbookingstpl.php'); ?>
                    </div>
                   <!-- /.tab-pane -->
                    <div class="tab-pane <?php echo $active_tab == 2 ? 'active' : ''?>" id="tab_3">
                        <?php include('../../drop-files/templates/admin/viewcustomerreviewstpl.php'); ?>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane <?php echo $active_tab == 3 ? 'active' : ''?>" id="tab_4">
                        <?php include('../../drop-files/templates/admin/viewcustomerdocumentstpl.php'); ?>
                    </div>
                    <!-- /.tab-pane -->
                    
                </div>
                <!-- /.tab-content -->           
        </div>

        
		

    </div> <!--/col-sm-12-->
    
</div>

<script>

var active_tab = $('.nav-tabs li.active a').attr('href');
var active_tab_url = location.href;
var default_url = "<?php echo $_SERVER['SCRIPT_NAME'] . "?id={$id}"; ?>";

$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
  var el = e.target;
  var tab_href = $(el).attr('href');
  if(tab_href && tab_href == active_tab){
    history.replaceState({},'',active_tab_url);
  }else{
    switch(tab_href){
        case '#tab_1':
        history.replaceState({},'',default_url + '&tab=ctransactions');    
        break;
        case '#tab_2':
        history.replaceState({},'',default_url + '&tab=cbookings');    
        break;        
        case '#tab_3':
        history.replaceState({},'',default_url + '&tab=creviews');    
        break;
        case '#tab_4':
        history.replaceState({},'',default_url + '&tab=cdocuments');    
        break;
    };
  }
})







</script>





















