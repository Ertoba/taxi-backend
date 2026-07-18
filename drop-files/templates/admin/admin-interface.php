<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo WEBSITE_NAME; ?> Web Admin. Panel</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="../js/plugins/Ionicons/css/ionicons.min.css">
  <!-- Theme style -->
  <!-- Select2 -->
  <link rel="stylesheet" href="../js/plugins/select2/dist/css/select2.min.css">
  <link rel="stylesheet" href="../css/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="../css/skins/skin-blue.min.css">
  
  <link rel="stylesheet" href="../css/admin-style.css">
  <link rel="stylesheet" href="../css/sweetalert.css">
  <link rel="stylesheet" href="../css/google.css">
  <link rel="stylesheet" href="../css/intlTelInput.min.css">
  <!-- Date Picker -->
  <link rel="stylesheet" href="../js/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
  <!-- <link rel="stylesheet" href="../js/plugins/datepicker/datepicker3.css"> -->
  <link href="../img/favicon.png" rel="icon">
  <link href="../img/apple-touch-icon.png" rel="apple-touch-icon">
  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

  <link rel="stylesheet" href="../fancybox/source/jquery.fancybox.css?v=2.1.6" type="text/css" media="screen" />

  

  
  
  <link href="../css/jquery-ui.min.css" rel="stylesheet" type="text/css">
  <link href="../css/jquery-ui.theme.min.css" rel="stylesheet" type="text/css">
  <link href="../css/jquery-ui.structure.min.css" rel="stylesheet" type="text/css">

  <!-- jQuery 3 -->
  <script src="../js/plugins/jquery/dist/jquery.min.js"></script>
  <!-- jQuery UI 1.11.4 -->
  <script src="../js/plugins/jQueryUI/jquery-ui.min.js"></script>
  <script src="../js/plugins/jQueryUI/jquery-migrate-3.0.0.min.js"></script>
  <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->

  <script src="../js/plugins/select2/dist/js/select2.full.min.js"></script>

 
  <!-- jQuery -->
  <!-- <script src="../js/jquery-1.9.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery-ui.min.js"></script>  -->

  <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GMAP_API_KEY; ?>&libraries=places,drawing,geometry"></script>

 

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body class="hold-transition skin-blue sidebar-mini">


<script>

var ajaxurl = <?php $site_url = SITE_URL; echo "'{$site_url}ajaxsd.php'"; ?>;


</script>
<!-- Site wrapper -->
<div class="wrapper">

      <div class="modal fade" id="busy" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
      <div class="modal-dialog loader modal-sm" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="gridSystemModalLabel">Please Wait...</h4>
          </div>
        <div class="modal-body">
        <img class="center-block" src="../img/loadergif.gif" />
        </div>
        </div>
      </div>
    </div>

  <header class="main-header">
    <!-- Logo -->
    <a href="<?php echo SITE_URL; ?>" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><img src='../img/company-logo-button.png' /></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><img src='../img/company-logo-small.png' /></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
        <!-- <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span> -->
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">

        
          <!-- <li class="dropdown notifications-menu">
              <a href="admin-notify.php" title="notifications" class="dropdown-toggle">
                <i class="fa fa-bell-o"></i>
                <span id="n-num"class="label label-success">10</span>
              </a>
              
          </li> -->

           
          
          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img class = "user-image" src='<?php echo empty($_SESSION['photo']) ? "../img/usersample.jpg" : "../userphotofile.php?file=". $_SESSION['photo'];?>' alt="User Image">
              <span class="hidden-xs"><?php echo !empty($_SESSION['firstname']) ? $_SESSION['firstname'] : "";?></span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">
                <img class = "img-circle" src='<?php echo empty($_SESSION['photo']) ? "../img/usersample.jpg" : "../userphotofile.php?file=". $_SESSION['photo'];?>' alt="User Image">

                <p>
                  <?php echo ucfirst((strtolower($_SESSION['lastname'])))." ". ucfirst((strtolower($_SESSION['firstname']))); if($_SESSION['account_type'] == 2){echo " - Dispatcher";}else{echo " - Admin.";}; ?>
                  <!-- <small>Member since: <?php if(isset($_SESSION['joined'])){ echo date('l jS \of F Y h:i:s A', strtotime($_SESSION['joined'].' UTC'));}else{ echo '' ;} ?></small> -->
                  <small>Last seen: <?php if(isset($_SESSION['lastseen'])){ echo date('l jS \of F Y h:i:s A', strtotime($_SESSION['lastseen'].' UTC'));}else{ echo '' ;} ?></small>
                </p>
              </li>
              <!-- Menu Body -->
              <!-- <li class="user-body">
                <div class="row">
                  <div class="col-sm-12" style="text-align:center">
                      <a href="<?php echo SITE_URL; ?>" class="btn btn-default btn-flat">View landing page</a>
                  </div>
                  
                </div>
                
              </li> -->
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="">
                  <a href="view-staff.php?id=<?php echo $_SESSION['uid'];?>" class="btn btn-default btn-block btn-flat">My Profile</a>
                </div>
                
                <div class="" style='margin-top:5px;'>
                  <a href="../login.php?logout=1" class="btn btn-default btn-block btn-flat">Sign out</a>
                </div>
              </li>
            </ul>
          </li>
         
        </ul>
      </div>
    </nav>
  </header>

  <!-- =============================================== -->

  <!-- Left side column. contains the sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <!-- <div class="user-panel">
        <div class="pull-left image">
          <img class = "img-circle" src='<?php echo isset($_SESSION['account_type']) && $_SESSION['account_type'] == 2 ? "../img/disp-avatar.png":"../img/admin-avatar.png"; ?>' alt="User Image">
        </div>
        <div class="pull-left info">
          <p><?php echo ucfirst((strtolower($_SESSION['firstname']))) . " " . ucfirst((strtolower($_SESSION['lastname']))); ?></p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>
     <hr /> -->
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu" data-widget="tree">
        <li class="header">MAIN NAVIGATION</li>
        <li class="<?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "dashb"){echo "active" ;} ?>">
          <a href="index.php">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
            <span class="pull-right-container">
              
            </span>
          </a>
          
        </li>

        <?php 
          if($_SESSION['account_type'] == 2 || $_SESSION['account_type'] == 3 || $_SESSION['account_type'] == 5){       
        ?>
          <li class="treeview <?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "franch"){echo "active" ;} ?>">
            <a href="#">
              <i class="fa  fa-briefcase "></i> <span>Franchise</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
                              
              </span>
            </a>
            <ul class="treeview-menu">
              <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "franch-new"){echo "active" ;} ?>"><a href="new-franchise.php"><i class="fa fa-circle-o"></i> New Franchise</a></li>
              <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "franch-all"){echo "active" ;} ?>"><a href="all-franchise.php"><i class="fa fa-circle-o"></i> All Franchise</a></li>
              
            </ul>
          </li>
        <?php 
          }      
        ?>

        
        <?php 
          if($_SESSION['account_type'] == 3){       
        ?>
          <li class="treeview <?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "rides"){echo "active" ;} ?>">
            <a href="#">
              <i class="fa  fa-car "></i> <span>Cars</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
                              
              </span>
            </a>
            <ul class="treeview-menu">
              <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "rides-new"){echo "active" ;} ?>"><a href="add-ride.php"><i class="fa fa-circle-o"></i> New Car</a></li>
              <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "rides-all"){echo "active" ;} ?>"><a href="all-rides.php"><i class="fa fa-circle-o"></i> All Cars</a></li>
              
            </ul>
          </li>
        <?php 
          }       
        ?>

        <?php 
          if($_SESSION['account_type'] == 3){       
        ?>
          <li class="treeview <?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "tariffs"){echo "active" ;} ?>">
            <a href="index.php">
            <i class="fa  fa-tags  "></i> <span>City | Tariffs</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
                              
              </span>
            </a>
            <ul class="treeview-menu">
              <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "tariffs-new"){echo "active" ;} ?>"><a href="add-tariff.php"><i class="fa fa-circle-o"></i> New Tariff</a></li>
              <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "tariffs-all"){echo "active" ;} ?>"><a href="all-tariffs.php"><i class="fa fa-circle-o"></i> All Tariffs</a></li>
              
            </ul>
          </li>
        <?php 
          }
        ?>



        <?php 
          if($_SESSION['account_type'] == 3){       
        ?>
          <li class="treeview <?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "zones"){echo "active" ;} ?>">
            <a href="index.php">
            <i class="fa  fa-plane  "></i> <span>Zones</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
                              
              </span>
            </a>
            <ul class="treeview-menu">
              <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "zones-new"){echo "active" ;} ?>"><a href="add-zone.php"><i class="fa fa-circle-o"></i> New Zone</a></li>
              <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "zones-all"){echo "active" ;} ?>"><a href="all-zones.php"><i class="fa fa-circle-o"></i> All Zones</a></li>
              
            </ul>
          </li>
        <?php 
          }
        ?>



        
      <?php 
          if($_SESSION['account_type'] == 2 || $_SESSION['account_type'] == 3 || $_SESSION['account_type'] == 5){       
      ?>  
        <li class="treeview <?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "bookings"){echo "active" ;} ?>">
          <a href="#">
            <i class="fa fa-bookmark"></i>
            <span>Bookings</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
                             
            </span>
          </a>
          <ul class="treeview-menu">
          <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "booking-new"){echo "active" ;} ?>"><a href="add-booking.php"><i class="fa fa-circle-o"></i> New Booking</a></li>
            <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "booking-dispatch"){echo "active" ;} ?>"><a href="dispatch.php"><i class="fa fa-circle-o"></i> Dispatch</a></li>
            <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "booking-all"){echo "active" ;} ?>"><a href="all-bookings.php"><i class="fa fa-circle-o"></i> All Instant Bookings</a></li>
            <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "sbooking-all"){echo "active" ;} ?>"><a href="all-sbookings.php"><i class="fa fa-circle-o"></i> All Scheduled Bookings</a></li>
            
          </ul>
        </li>
      <?php 
        }
      ?> 

      <?php 
          if($_SESSION['account_type'] == 2 || $_SESSION['account_type'] == 3 || $_SESSION['account_type'] == 5){       
      ?> 
        <li class="treeview <?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "customers"){echo "active" ;} ?>">
          <a href="#">
            <i class="fa  fa-users "></i> <span>Customers</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
                             
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "customer-new"){echo "active" ;} ?>"><a href="add-customer.php"><i class="fa fa-circle-o"></i> New Customer</a></li>
            <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "customer-all"){echo "active" ;} ?>"><a href="all-customers.php"><i class="fa fa-circle-o"></i> All Customers</a></li>
            
            
          </ul>
        </li>
      <?php 
        }
      ?> 
      
      
      <?php 
          if($_SESSION['account_type'] == 2 || $_SESSION['account_type'] == 3 || $_SESSION['account_type'] == 5){       
      ?>   
        <li class="treeview <?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "drivers"){echo "active" ;} ?>">
          <a href="#">
            <i class="fa  fa-drivers-license "></i> <span>Drivers</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
                             
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "driver-new"){echo "active" ;} ?>"><a href="add-driver.php"><i class="fa fa-circle-o"></i> New Driver</a></li>
            <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "driver-all"){echo "active" ;} ?>"><a href="all-drivers.php"><i class="fa fa-circle-o"></i> All Drivers</a></li>
            
            
          </ul>
        </li>

      <?php 
        }
      ?> 

       
       
        <?php 
          if($_SESSION['account_type'] == 2 || $_SESSION['account_type'] == 3 || $_SESSION['account_type'] == 5){       
        ?>
          <li class="treeview <?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "accounts"){echo "active" ;} ?>">
            <a href="#">
              <i class="fa fa-user"></i> <span>Staff Accounts</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "new_acc"){echo "active" ;} ?>"><a href="add-staff.php"><i class="fa fa-circle-o"></i> Create New Account</a></li>
              <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "manage_acc"){echo "active" ;} ?>"><a href="all-staffs.php"><i class="fa fa-circle-o"></i> Manage Staff Accounts</a></li>
              <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "my_acc"){echo "active" ;} ?>"><a href="view-staff.php?id=<?php echo $_SESSION['uid']; ?>"><i class="fa fa-circle-o"></i> My Profile</a></li>
            </ul>
          </li>
        <?php 
          }
        ?>


        <?php 
          if($_SESSION['account_type'] == 3 || $_SESSION['account_type'] == 2){       
        ?>
          <li class="<?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "tracker"){echo "active" ;} ?>"><a href="maptracker.php"><i class="fa fa-map"></i> <span>Map Tracker</span></a></li>

        <?php 
          }       
        ?> 



        <?php 
          if($_SESSION['account_type'] == 3){       
        ?>
          <li class="treeview <?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "documents"){echo "active" ;} ?>">
            <a href="#">
              <i class="fa fa-file-text"></i> <span>Documents</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "c_docs"){echo "active" ;} ?>"><a href="cdocuments.php"><i class="fa fa-circle-o"></i> Customer Documents</a></li>
              <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "d_docs"){echo "active" ;} ?>"><a href="ddocuments.php"><i class="fa fa-circle-o"></i> Driver Documents</a></li>
              
            </ul>
          </li>
        <?php 
          }
        ?>


        
        <?php 
          if($_SESSION['account_type'] == 3){       
        ?>
          <li class="<?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "broadcast"){echo "active" ;} ?>"><a href="broadcast.php"><i class="fa fa-bullhorn"></i> <span>Message Broadcast</span></a></li>
        <?php 
          }       
        ?>

        <?php 
          if($_SESSION['account_type'] == 3){       
        ?>
          <li class="<?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "banners"){echo "active" ;} ?>"><a href="banners.php"><i class="fa fa-flag"></i> <span>Banners</span></a></li>
        <?php 
          }       
        ?>
        

        <?php 
          if($_SESSION['account_type'] == 3){       
        ?>
          <li class="<?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "coupon"){echo "active" ;} ?>"><a href="coupons.php"><i class="fa fa-gift"></i> <span>Coupons</span></a></li>

        <?php 
          }       
        ?> 

        <?php 
          if($_SESSION['account_type'] == 3){       
        ?>
          <li class="<?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "rewardpoints"){echo "active" ;} ?>"><a href="rewardpoints.php"><i class="fa fa-diamond"></i> <span>Reward Points</span></a></li>

        <?php 
          }       
        ?> 
        
        <?php 
          if($_SESSION['account_type'] == 3){       
        ?>
          <li class="treeview <?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "referrals"){echo "active" ;} ?>">
            <a href="#">
              <i class="fa fa-recycle"></i> <span>Referrals</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "referral-riders"){echo "active" ;} ?>"><a href="referral-riders.php"><i class="fa fa-circle-o"></i> <span>Rider Referral</span></a></li>
              <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "referral-drivers"){echo "active" ;} ?>"><a href="referral-drivers.php"><i class="fa fa-circle-o"></i> <span>Driver Referral</span></a></li>              
            </ul>
          </li>
        <?php 
          }       
        ?>

        <?php 
          if($_SESSION['account_type'] == 3 || $_SESSION['account_type'] == 5){       
        ?> 
          <li class="<?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "alltransactions"){echo "active" ;} ?>"><a href="all-transactions.php"><i class="fa fa-usd"></i> <span>Transactions</span></a></li>
        <?php 
          }      
        ?>

        <?php 
          if($_SESSION['account_type'] == 3 || $_SESSION['account_type'] == 5){       
        ?> 
          <li class="<?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "wallet"){echo "active" ;} ?>"><a href="walletfund.php"><i class="fa fa-google-wallet"></i> <span>Fund Wallet</span></a></li>
        <?php 
          }      
        ?>

        <?php 
          if($_SESSION['account_type'] == 3 || $_SESSION['account_type'] == 5){       
        ?> 

          <li class="<?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "payout"){echo "active" ;} ?>" ><a href="payout.php"><i class="fa fa-handshake-o"></i> <span>Payout</span></a></li>
        
        <?php 
          }       
        ?> 


        <?php 
          if($_SESSION['account_type'] == 3){       
        ?>          
          
          <li class="treeview <?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "app-info"){echo "active" ;} ?>">
            <a href="#">
              <i class="fa fa-clone"></i> <span> App Info Pages</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
          
              <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "app-pages"){echo "active" ;} ?>"><a href="appinfo-pages.php"><i class="fa fa-circle-o"></i> Pages</a></li>
              <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "app-help-topics"){echo "active" ;} ?>"><a href="help-topics.php"><i class="fa fa-circle-o"></i> Help Topics</a></li>
              <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "app-help-cat"){echo "active" ;} ?>"><a href="help-cat.php"><i class="fa fa-circle-o"></i> Help Categories</a></li>
            
            </ul>
          </li>
        <?php 
          }
        ?>

        <?php 
          if($_SESSION['account_type'] == 2 || $_SESSION['account_type'] == 3 || $_SESSION['account_type'] == 5){       
        ?>
          <li class="treeview <?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "chats-support"){echo "active" ;} ?>">
            <a href="#">
              <i class="fa fa-comments"></i> <span>Chat Support</span>
              <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
            </a>
            <ul class="treeview-menu">
              <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "cust-chats-support"){echo "active" ;} ?>"><a href="chatsupport.php"><i class="fa fa-circle-o"></i> Customers Chat Support</a></li>
              <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "drv-chats-support"){echo "active" ;} ?>"><a href="dchatsupport.php"><i class="fa fa-circle-o"></i> Drivers Chat Support</a></li>
            </ul>
          </li>
        <?php 
          }
        ?>

        <?php 
          if($_SESSION['account_type'] == 3){       
        ?> 
          <li class="<?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "settings"){echo "active" ;} ?>"><a href="settings.php"><i class="fa fa-gears"></i> <span>Settings</span></a></li>
        
        <?php 
          }      
        ?>




      <?php 
          if($_SESSION['account_type'] == 3){       
      ?>          
        
        <li class="treeview <?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "reports"){echo "active" ;} ?>">
          <a href="#">
            <i class="fa fa-line-chart"></i> <span> Reports</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
          <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "reports_drv"){echo "active" ;} ?>"><a href="drivers-report.php"><i class="fa fa-circle-o"></i> Drivers</a></li>
            <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "reports_cust"){echo "active" ;} ?>"><a href="customers-report.php"><i class="fa fa-circle-o"></i> Customers</a></li>
            <li class="<?php if(isset($GLOBALS['admin_template']['active_sub_menu']) && $GLOBALS['admin_template']['active_sub_menu'] == "reports_payments"){echo "active" ;} ?>"><a href="payments-report.php"><i class="fa fa-circle-o"></i> Payments</a></li>
          
          </ul>
        </li>
      <?php 
        }
      ?>

      

        


        <li class=" <?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "about-cab"){echo "active" ;} ?>"><a href="about.php"><i class="fa fa-info-circle"></i> <span>About</span></a></li>
        
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>

  <!-- =============================================== -->

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <?php if(!empty($GLOBALS['admin_template']['page_title'])){echo $GLOBALS['admin_template']['page_title'] ;} ?>
        <small></small>
      </h1>
      
    </section>

    <!-- Main content -->
    <section class="content">

      <!-- Dynamic content -->
      <?php if(!empty($GLOBALS['admin_template']['page_content'])){echo $GLOBALS['admin_template']['page_content'] ;} ?>
      

    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer">
    <div class="pull-right hidden-xs">
      <b>Version</b> 2.4.2
    </div>
    <strong>Copyright &copy; <?php echo date('Y')?> <a href="#"><?php echo WEBSITE_NAME; ?></a>.</strong> All rights
    reserved.
  </footer>

 
</div>
<!-- ./wrapper -->


<!-- Bootstrap 3.3.6 -->
<script src="../js/plugins/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- <script src="../js/plugins/datepicker/bootstrap-datepicker.js"></script> -->
<!-- daterangepicker -->
<script src="../js/plugins/moment/min/moment.min.js"></script>
<script src="../js/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
<!-- datepicker -->
<script src="../js/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>


<!-- AdminLTE App -->
<!-- <script src="../js/app.js"></script> -->
<!-- AdminLTE App -->
<script src="../js/adminlte.min.js"></script>
<script src="../js/admin.js"></script>
<script type="text/javascript" src="../fancybox/source/jquery.fancybox.pack.js?v=2.1.6"></script>
<script src="../js/jquery.observe_field.js"></script>
<script src="../js/sweetalert.min.js"></script>
<script src="../tinymce/tinymce.min.js"></script>
<script src="../js/Chart.min.js"></script>

<!-- Bootstrap WYSIHTML5 -->
<script src="../js/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
<!-- Slimscroll -->
<script src="../js/plugins/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="../js/plugins/fastclick/lib/fastclick.js"></script>





</body>
</html>
