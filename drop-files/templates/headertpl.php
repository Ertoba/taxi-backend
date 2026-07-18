<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title><?php echo WEBSITE_NAME . " - " . WEBSITE_DESC;?></title>
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <meta content="taxi app,online ride sharing app, ride booking app, best taxi app, Best taxi App" name="keywords">
  <meta content="<?php echo WEBSITE_DESC ?>" name="description">


  <!-- Canonical SEO -->
  <link rel="canonical" href="<?php echo SITE_URL?>" />
  <link rel="stylesheet" href="/font-awesome/css/font-awesome.min.css">
  
  <!-- Schema.org markup for Google+ -->
  <meta itemprop="name" content="<?php echo WEBSITE_NAME ?>">
  <meta itemprop="description" content="<?php echo WEBSITE_DESC ?>">
  <meta itemprop="image" content="<?php echo SITE_URL . "img/seo-img.jpg"; ?>">
  <!-- Twitter Card data -->
  <meta name="twitter:card" content="product">
  <meta name="twitter:site" content="<?php echo SITE_URL?>">
  <meta name="twitter:title" content="<?php echo WEBSITE_NAME ?>">
  <meta name="twitter:description" content="<?php echo WEBSITE_DESC ?>">
  <meta name="twitter:creator" content="<?php echo WEBSITE_NAME ?>">
  <meta name="twitter:image" content="<?php echo SITE_URL . "img/seo-img.jpg"; ?>">
  <!-- Open Graph data -->
  <meta property="fb:app_id" content="">
  <meta property="og:title" content="<?php echo WEBSITE_NAME ?>" />
  <meta property="og:type" content="article" />
  <meta property="og:url" content="<?php echo SITE_URL?>" />
  <meta property="og:image" content="<?php echo SITE_URL . "img/seo-img.jpg"; ?>" />
  <meta property="og:description" content="<?php echo WEBSITE_DESC ?>" />
  <meta property="og:site_name" content="<?php echo WEBSITE_NAME ?>" />

  <!-- Favicons -->
  <link href="/img/favicon.png" rel="icon">
  <link href="/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Montserrat:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

  <script src="lib/jquery/jquery.min.js"></script>
	<script src="lib/jquery/jquery-migrate.min.js"></script>

 
</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="fixed-top d-flex align-items-center">
    <div class="container d-flex align-items-center justify-content-between">

      <div class="logo">
        <a href="index.php"><img src="/img/logo.png" alt="" class="img-fluid"></a>
      </div>

      <nav id="navbar" class="navbar">
        <ul>
          
          
          <?php
            if(isset($_SESSION['loggedin'])){
          ?>
              <li><a href='login.php?logout=1'>Sign-Out</a></li>
          <?php
            }else{
          ?>
              <li class="<?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "main-signin"){echo "active" ;} ?>"><a href='login.php'>Sign-In</a></li>					
              <!-- <li class="<?php if(isset($GLOBALS['admin_template']['active_menu']) && $GLOBALS['admin_template']['active_menu'] == "main-signup"){echo "current_page_item" ;} ?>"><a href='register.php'>SignUp</a></li> -->
          <?php
            }
          ?>

          <?php
            if(isset($_SESSION['loggedin']) && ($_SESSION['account_type'] == 2 || $_SESSION['account_type'] == 3 || $_SESSION['account_type'] == 4 || $_SESSION['account_type'] == 5)){
            
              echo "<li><a href='".SITE_URL."admin/index.php'>Dash.</a></li>";
            }
          ?>        
        </ul>
        <i class="bi bi-list mobile-nav-toggle"></i>
      </nav><!-- .navbar -->

    </div>
  </header><!-- End Header -->