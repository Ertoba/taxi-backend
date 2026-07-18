<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
            View franchise details. 
        </div>
    </div>
</div> <!--/Row-->

<div class="row">
    <div class="col-sm-12"> 
                
        <div class="box box-default">
            <div class="box-header with-border">
            <h3 class="box-title">Details</h3>
            </div><!-- /.box-header -->
            <div class="box-body">
                <div class="col-sm-12">
                    <h3><i class='fa fa-briefcase'></i> <?php echo $franchise_data['franchise_name']; ?></h3> 
                    <p><?php echo $franchise_data['franchise_desc']; ?></p>
                    <a href="edit-franchise.php?id=<?php echo  $franchise_data['id']; ?>" class="btn btn-primary btn-sm">Edit Franchise</a>
                    <br />
                    <br />
                </div>
                <div class="col-sm-3" style="border-left:thin solid #ccc;">               
                <div class="spacer"></div>
                    <h5>Phone: <?php echo (!empty(DEMO) ? mask_string($franchise_data['franchise_phone']) : $franchise_data['franchise_phone']); ?></h5>
                    <h5>Email: <?php echo (!empty(DEMO) ? mask_email($franchise_data['franchise_email']) : $franchise_data['franchise_email']); ?></h5>
                    <h5>Commision: <?php echo $franchise_data['franchise_commision']; ?>%</h5>
                </div>
                <div class="col-sm-3" style="border-left:thin solid #ccc;">                
                    <h5>Account Name: <?php echo $franchise_data['bank_acc_holder_name']; ?></h5>
                    <h5>Bank Name: <?php echo !empty($franchise_data['bank_name']) ? $banks_details[$franchise_data['bank_name']] : ''; ?></h5>
                    <h5>Bank Code: <?php echo !empty($franchise_data['bank_code']) ? $banks_details[$franchise_data['bank_code']] : ''; ?></h5>
                    <h5>Account Number: <?php echo $franchise_data['bank_acc_num']; ?></h5>
                    <h5>Swift / BIC Code: <?php echo $franchise_data['bank_swift_code']; ?></h5>
                </div>
            


            
            
            </div><!-- /.box-body -->
        </div>


    </div><!--/col-sm-12-->    
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
                <span class="info-box-icon bg-yellow"><i class="fa fa-user"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">Drivers</span>
                    <span class="info-box-number"><?php echo $number_of_franchise_drivers; ?></span>
                </div>
                <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>


            <!-- <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-money"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">Total Earning</span>
                    <span class="info-box-number"><?php //echo $_SESSION['default_currency']['symbol'] . $total_amount_earned_franchise; ?></span>
                </div>
                
                </div>
                
            </div>
 -->

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                <span class="info-box-icon bg-blue"><i class="fa fa-folder-open"></i></span>

                <div class="info-box-content">
                    <span class="info-box-text">Wallet Amount</span>
                    <span class="info-box-number"><?php echo $_SESSION['default_currency']['symbol'] . $franchise_data['fwallet_amount']; ?></span>
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
    

        <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="<?php echo $active_tab == 0 ? 'active' : ''?>"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Transactions</a></li>
                    <li class="<?php echo $active_tab == 1 ? 'active' : ''?>"><a href="#tab_2" data-toggle="tab" aria-expanded="false">Payouts</a></li>
                    <li class="<?php echo $active_tab == 2 ? 'active' : ''?>"><a href="#tab_3" data-toggle="tab" aria-expanded="false">Drivers</a></li>                            
                </ul>
                
                <div class="tab-content">
                    <div class="tab-pane <?php echo $active_tab == 0 ? 'active' : ''?>" id="tab_1">
                        <?php include('../../drop-files/templates/admin/viewfranchisetransactionstpl.php'); ?>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane <?php echo $active_tab == 1 ? 'active' : ''?>" id="tab_2">
                        <?php include('../../drop-files/templates/admin/viewfranchisepayoutstpl.php'); ?>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane <?php echo $active_tab == 2 ? 'active' : ''?>" id="tab_3">
                    <?php include('../../drop-files/templates/admin/viewfranchisedriverstpl.php'); ?>
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
                history.replaceState({},'',default_url + '&tab=ftransactions');    
                break;
                case '#tab_2':
                history.replaceState({},'',default_url + '&tab=fpayouts');    
                break;
                case '#tab_3':
                history.replaceState({},'',default_url + '&tab=fdrivers');    
                break;
            };
        }
    })

</script>

<?php

    if(!empty($_SESSION['action_success'])){
        $msgs = '';
        foreach($_SESSION['action_success'] as $action_success){
            $msgs .= "<p style='text-align:left;'><i style='color:green;' class='fa fa-circle-o'></i> ".$action_success . "</p>";
        }
    
        $cache_prevent = RAND();
        echo"<script>
        setTimeout(function(){ 
                jQuery( function(){
                swal({
                    title: '<h1>Success</h1>'".',
        text:"'.$msgs .'",'.
        "imageUrl: '../img/success_.gif?a=" . $cache_prevent . "',
        html:true,
                });
                });
                },500); 
                
                </script>";
    
            unset($_SESSION['action_success']);
    
    }elseif(!empty($_SESSION['action_error'])){
            $msgs = '';
            foreach($_SESSION['action_error'] as $action_error){
                $msgs .= "<p style='text-align:left;'><i style='color:red;' class='fa fa-circle-o'></i> ".$action_error . "</p>";
            }
    
            $cache_prevent = RAND();
            echo"<script>
        setTimeout(function(){ 
                jQuery( function(){
                swal({
                    title: '<h1>Error</h1>'".',
        text:"'.$msgs .'",'.
        "imageUrl: '../img/info_.gif?a=" . $cache_prevent . "',
        html:true,
                });
                });
                },500); 
                
                </script>";
        
                unset($_SESSION['action_error']);
        
    }
    
?>



















