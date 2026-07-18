<div class="row">
    <div class="col-sm-12">
        <div class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-info"></i> Quick Info!</h4>
            Modify system global options and settings here. 
        </div>
    </div>
</div> <!--/Row-->


<div class="row">	
    <div class="col-sm-12" >

        <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                <li class="<?php echo $active_tab == 0 ? 'active' : ''?>"><a href="#tab_1" data-toggle="tab" aria-expanded="true">Basic</a></li>
                <li class="<?php echo $active_tab == 1 ? 'active' : ''?>"><a href="#tab_2" data-toggle="tab" aria-expanded="false">API Keys</a></li>
                <li class="<?php echo $active_tab == 2 ? 'active' : ''?>"><a href="#tab_3" data-toggle="tab" aria-expanded="false">Apps</a></li>
                <li class="<?php echo $active_tab == 3 ? 'active' : ''?>"><a href="#tab_4" data-toggle="tab" aria-expanded="false">Currency</a></li>                            
                <li class="<?php echo $active_tab == 4 ? 'active' : ''?>"><a href="#tab_5" data-toggle="tab" aria-expanded="false">Email</a></li>                            
                </ul>
                <div class="tab-content">
                    <div class="tab-pane <?php echo $active_tab == 0 ? 'active' : ''?>" id="tab_1">
                        <?php include('../../drop-files/templates/admin/settingsbasictpl.php'); ?>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane <?php echo $active_tab == 1 ? 'active' : ''?>" id="tab_2">                        
                        <?php include('../../drop-files/templates/admin/settingsapikeystpl.php'); ?>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane <?php echo $active_tab == 2 ? 'active' : ''?>" id="tab_3">
                        <?php include('../../drop-files/templates/admin/settingsappstpl.php'); ?>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane <?php echo $active_tab == 3 ? 'active' : ''?>" id="tab_4">
                        <?php include('../../drop-files/templates/admin/settingscurrencytpl.php'); ?>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane <?php echo $active_tab == 4 ? 'active' : ''?>" id="tab_5">
                        <?php include('../../drop-files/templates/admin/settingsemailtpl.php'); ?>
                    </div>
                </div>
                <!-- /.tab-content -->           
        </div>

        
		

    </div> <!--/col-sm-12-->
</div>



















