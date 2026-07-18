<?php
session_start();
include("../../drop-files/lib/common.php");
include "../../drop-files/config/db.php";
define('ITEMS_PER_PAGE', 100); //define constant for number of items to display per page


if(isset($_SESSION['expired_session'])){
    header("location: ".SITE_URL."login.php?timeout=1");
    exit;
}

if(!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1)){ //if user is not logged in run this code
  header("location: ".SITE_URL."login.php"); //Yes? then redirect user to the login page
  exit;
}

if($_SESSION['account_type'] != 5 && $_SESSION['account_type'] != 3){ ////if user is an admin or dispatcher
    $_SESSION['action_error'][] = "Access Denied!";
    header("location: ".SITE_URL."admin/index.php"); //Yes? then redirect user to the login page
    exit;
}

$GLOBALS['admin_template']['page_title'] = "<i class='fa fa-usd'></i> Transactions"; //Set the title of the page on the admin interface
$GLOBALS['admin_template']['active_menu'] = "alltransactions"; //Set the appropriate menu item active



$wallet_transactions_data = [];
$query_modifier  = '1 ';
$number_of_wallet_transactions = 0;


if(isset($_GET['search-term'])){

    if(strlen($_GET['search-term']) > 15){
        $_SESSION['action_error'][] = "Search word is too long";
        header("location: ".SITE_URL."admin/all-transactions.php"); //Yes? then redirect
        exit;
    }

    $search_string = mysqli_real_escape_string($GLOBALS['DB'], $_GET['search-term']);

    $query_modifier .= 'AND ' . DB_TBL_PREFIX . 'tbl_wallet_transactions.transaction_id LIKE "%' . $search_string . '%" ';

}elseif(!empty($_GET['filter-records'])){

    if(isset($_GET['trans-type']) && $_GET['trans-type'] != ""){
        $trans_type = (int) $_GET['trans-type'];
        $query_modifier .= "AND " . DB_TBL_PREFIX . "tbl_wallet_transactions.type = {$trans_type} ";
    }

    if(isset($_GET['trans-acc-type']) && $_GET['trans-acc-type'] != ""){
        $trans_acc_type = (int) $_GET['trans-acc-type'];
        $query_modifier .= "AND " . DB_TBL_PREFIX . "tbl_wallet_transactions.user_type = {$trans_acc_type} ";
    }


    if(isset($_GET['trans-date']) && $_GET['trans-date'] != ""){
        $trans_date = mysqli_real_escape_string($GLOBALS['DB'], $_GET['trans-date']);
        $query_modifier .= "AND DATE(" . DB_TBL_PREFIX . "tbl_wallet_transactions.transaction_date) = '{$trans_date}' ";
    }
    

}



$_SESSION['transactions-export_query']['q_mod'] = $query_modifier;
$_SESSION['transactions-export_query']['sort'] = DB_TBL_PREFIX . "tbl_wallet_transactions.transaction_date";



//get number of wallet transactions
$query = sprintf('SELECT COUNT(*) FROM %1$stbl_wallet_transactions WHERE %2$s', DB_TBL_PREFIX, $query_modifier);

if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){
        $row = mysqli_fetch_assoc($result);        
        $number_of_wallet_transactions = $row['COUNT(*)'];
    }
}

//calculate pages
if(isset($_GET['page'])){
    $page_number = (int) $_GET['page'];
}else{
    $page_number = 1;
}
    
$pages = ceil($number_of_wallet_transactions / ITEMS_PER_PAGE) ;
if($page_number > $pages)$page_number = 1; 
if($page_number < 0)$page_number = 1; 
$offset = ($page_number - 1) * ITEMS_PER_PAGE;

//get withdrawal data
$query = sprintf('SELECT %1$stbl_wallet_transactions.*,%1$stbl_users.account_type,%1$stbl_users.firstname AS ufirstname,%1$stbl_users.lastname AS ulastname,%1$stbl_users.country_dial_code AS ucountry_dial_code,%1$stbl_users.phone AS uphone,%1$stbl_drivers.firstname,%1$stbl_drivers.lastname,%1$stbl_drivers.country_dial_code,%1$stbl_drivers.phone,%1$stbl_franchise.franchise_name,%1$stbl_franchise.franchise_phone FROM %1$stbl_wallet_transactions 
LEFT JOIN %1$stbl_franchise ON %1$stbl_wallet_transactions.user_id = %1$stbl_franchise.id AND %1$stbl_wallet_transactions.user_type = 2
LEFT JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_wallet_transactions.user_id AND %1$stbl_wallet_transactions.user_type = 1
LEFT JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_wallet_transactions.user_id AND %1$stbl_wallet_transactions.user_type = 0
WHERE %2$s ORDER BY %1$stbl_wallet_transactions.transaction_date DESC LIMIT %3$d, %4$d', DB_TBL_PREFIX, $query_modifier, $offset, ITEMS_PER_PAGE);


if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){
        while($row = mysqli_fetch_assoc($result)){
            $wallet_transactions_data[$row['id']] = $row;
        }
        
    }
}





ob_start();
include('../../drop-files/templates/admin/alltransactionstpl.php');

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
$pageContent = ob_get_clean();
$GLOBALS['admin_template']['page_content'] = $pageContent;
include "../../drop-files/templates/admin/admin-interface.php";
exit;


?>