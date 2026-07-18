<?php
session_start();
include("../../drop-files/lib/common.php");
include ("../../drop-files/config/db.php");
define('ITEMS_PER_PAGE', 50); //define constant for number of items to display per page

$number_of_customers = 0;
$customers_page_items = array();
$query_modifier = '';

if(isset($_SESSION['expired_session'])){
    header("location: ".SITE_URL."login.php?timeout=1");
    exit;
}

if(!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1)){ //if user is not logged in run this code
  header("location: ".SITE_URL."login.php"); //Yes? then redirect user to the login page
  exit;
}

if($_SESSION['account_type'] != 2 && $_SESSION['account_type'] != 3){ ////if user is an admin or dispatcher
    $_SESSION['action_error'][] = "Access Denied!";
    header("location: ".SITE_URL."admin/index.php"); //Yes? then redirect user to the login page
    exit;
}


$GLOBALS['admin_template']['page_title'] = "<i class='fa fa-users'></i> All Customers"; //Set the title of the page on the admin interface
$GLOBALS['admin_template']['active_menu'] = "customers"; //Set the appropriate menu item active
$GLOBALS['admin_template']['active_sub_menu'] = "customer-all"; //Set the appropriate menu item active


if(isset($_GET['search-term'])){

    if(strlen($_GET['search-term']) > 15){
        $_SESSION['action_error'][] = "Search word is too long";
        header("location: ".SITE_URL."admin/all-customers.php"); //Yes? then redirect
        exit;
    }

    $search_string = mysqli_real_escape_string($GLOBALS['DB'], $_GET['search-term']);
    $serach_int = (int) $search_string;
    $query_modifier = ' = '. $serach_int . " OR ". DB_TBL_PREFIX . 'tbl_users.firstname LIKE "%' . $search_string . '%" OR ' . DB_TBL_PREFIX .'tbl_users.lastname LIKE "%' . $search_string . '%"' . " OR ". DB_TBL_PREFIX . 'tbl_users.phone = "' . $search_string .'"' ;
    $GLOBALS['admin_template']['page_title'] = "<i class='fa fa-users'></i> Customer Search - " . $search_string;
}else{

    $query_modifier = ' > 0  '; //display all pending user qualifications
    

}


if(!empty($_GET['filter-records'])){

    if(isset($_GET['city'])){
  
      $city_val = (int) $_GET['city'];
  
      if(!empty($city_val)){
        $query_modifier .= "AND " . DB_TBL_PREFIX . "tbl_users.route_id = {$city_val} ";
      }
  
    } 


    if(isset($_GET['document-status']) && $_GET['document-status'] != "---"){

        $doc_status = (int) $_GET['document-status'];
    
        $query_modifier .= "AND " . DB_TBL_PREFIX . "tbl_users_documents.u_doc_status = {$doc_status} ";
        
        
    
    }



    if(isset($_GET['reg-date'])){
  
                
    
        if(isValidDate($_GET['reg-date'])){
            $start_date = gmdate('Y-m-d 00:00:00', strtotime($_GET['reg-date'] . ' UTC'));
            $end_date = gmdate('Y-m-d 23:59:00', strtotime($_GET['reg-date'] . ' UTC'));
    
          $query_modifier .= "AND " . DB_TBL_PREFIX . "tbl_users.account_create_date BETWEEN \"{$start_date}\" AND \"{$end_date}\" ";
        }
    
    }



    if(isset($_GET['activity-status'])){
  
        $ativity_val = (int) $_GET['activity-status'];
        
    
        if(!empty($ativity_val)){
            $start_date = gmdate('Y-m-d 00:00:00', strtotime('now'));
            $end_date = gmdate('Y-m-d 23:59:00', strtotime('now'));

          $query_modifier .= "AND " . DB_TBL_PREFIX . "tbl_users.last_login_date BETWEEN \"{$start_date}\" AND \"{$end_date}\" ";
        }
    
    } 


  
  
    if(isset($_GET['rating'])){
  
      $rating_val = (int) $_GET['rating'];
  
      if(!empty($rating_val)){
        $query_modifier .= "AND " . DB_TBL_PREFIX . "tbl_users.user_rating = {$rating_val} ";
      }
  
    }
  
    
}


$_SESSION['customers-export_query']['q_mod'] = $query_modifier;
$_SESSION['customers-export_query']['sort'] = DB_TBL_PREFIX . "tbl_users.account_create_date";






//get all intra-city routes
$inter_city_routes = [];

$query = sprintf('SELECT * FROM %1$stbl_routes WHERE r_scope = 0 ORDER BY r_title ASC', DB_TBL_PREFIX);


if($result = mysqli_query($GLOBALS['DB'], $query)){
  
    if(mysqli_num_rows($result)){
        while($row = mysqli_fetch_assoc($result)){
            $inter_city_routes[$row['id']] = $row;
        }
    
     }
    mysqli_free_result($result);
}





//Get number of customers in datatbase
if(!empty($_GET['filter-records']) && isset($_GET['document-status']) && $_GET['document-status'] != "---"){
    $query = sprintf('SELECT COUNT(DISTINCT %1$stbl_users.user_id) AS `COUNT(*)` FROM %1$stbl_users 
    INNER JOIN %1$stbl_users_documents ON %1$stbl_users_documents.u_id = %1$stbl_users.user_id AND %1$stbl_users_documents.u_type = 0
    WHERE %1$stbl_users.account_type != 2 AND %1$stbl_users.account_type != 3 AND %1$stbl_users.account_type != 5 AND ( %1$stbl_users.user_id %2$s )', DB_TBL_PREFIX,$query_modifier);  //Get and count all data
}else{
    $query = sprintf('SELECT COUNT(*) FROM %stbl_users WHERE account_type != 2 AND account_type != 3 AND account_type != 5 AND ( %1$stbl_users.user_id %s )', DB_TBL_PREFIX,$query_modifier);  //Get and count all data
}



/* echo mysqli_error($GLOBALS['DB']); */

if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){

       $row = mysqli_fetch_assoc($result);
          
      $number_of_customers = $row['COUNT(*)'];
         
     }
    mysqli_free_result($result);
}   




if(isset($_GET['page'])){
  $page_number = (int) $_GET['page'];
}else{
    $page_number = 1;
  }
  
  $pages = ceil($number_of_customers / ITEMS_PER_PAGE) ;
  if($page_number > $pages)$page_number = 1; 
  if($page_number < 0)$page_number = 1; 
  $offset = ($page_number - 1) * ITEMS_PER_PAGE;


  if(!empty($_GET['filter-records']) && isset($_GET['document-status']) && $_GET['document-status'] != "---"){
    $query = sprintf('SELECT * FROM %1$stbl_users
    INNER JOIN %1$stbl_users_documents ON %1$stbl_users_documents.u_id = %1$stbl_users.user_id AND %1$stbl_users_documents.u_type = 0
    WHERE %1$stbl_users.account_type != 2 AND %1$stbl_users.account_type != 3 AND %1$stbl_users.account_type != 5 AND (%1$stbl_users.user_id %4$s ) GROUP BY %1$stbl_users.user_id ORDER BY account_create_date DESC LIMIT %2$d,%3$d', DB_TBL_PREFIX, $offset, ITEMS_PER_PAGE, $query_modifier);
  }else{
    $query = sprintf('SELECT * FROM %1$stbl_users
    WHERE %1$stbl_users.account_type != 2 AND %1$stbl_users.account_type != 3 AND %1$stbl_users.account_type != 5 AND (%1$stbl_users.user_id %4$s ) ORDER BY account_create_date DESC LIMIT %2$d,%3$d', DB_TBL_PREFIX, $offset, ITEMS_PER_PAGE, $query_modifier);
  }


if($result = mysqli_query($GLOBALS['DB'], $query)){
  
    if(mysqli_num_rows($result)){
        while($row = mysqli_fetch_assoc($result)){
            $customers_page_items[] = $row;
        }
    
     }
    mysqli_free_result($result);
}   



/* var_dump($customers_page_items);
exit; */

ob_start();
include "../../drop-files/templates/admin/allcustomerstpl.php";

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


$GLOBALS['admin_template']['page_content'] = ob_get_clean();
include "../../drop-files/templates/admin/admin-interface.php";
exit;





























?>