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

if($_SESSION['account_type'] != 5 && $_SESSION['account_type'] != 3 && $_SESSION['account_type'] != 2){ ////if user is an admin or dispatcher
    $_SESSION['action_error'][] = "Access Denied!";
    header("location: ".SITE_URL."admin/index.php"); //Yes? then redirect user to the login page
    exit;
}

$GLOBALS['admin_template']['page_title'] = "<i class='fa fa-comments'></i> Drivers Chat support"; //Set the title of the page on the admin interface
$GLOBALS['admin_template']['active_menu'] = "chats-support"; //Set the appropriate menu item active
$GLOBALS['admin_template']['active_sub_menu'] = "drv-chats-support"; //Set the appropriate menu item active



$open_chat_support_data = [];
$query_modifier  = '1 ';
$number_of_open_chat_data = 0;



//get number of open chat supports
$query = sprintf('SELECT COUNT(DISTINCT user_id) AS num_open_chats FROM %stbl_chatsupport WHERE driver_id != 0 AND session_status = 1 GROUP BY user_id', DB_TBL_PREFIX); 

if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){
        $row = mysqli_fetch_assoc($result);        
        $number_of_open_chat_data = $row['num_open_chats'];
    }
}


//calculate pages
if(isset($_GET['page'])){
    $page_number = (int) $_GET['page'];
}else{
    $page_number = 1;
}
    
$pages = ceil($number_of_open_chat_data / ITEMS_PER_PAGE) ;
if($page_number > $pages)$page_number = 1; 
if($page_number < 0)$page_number = 1; 
$offset = ($page_number - 1) * ITEMS_PER_PAGE;

//get open chats data
$query = sprintf('SELECT %1$stbl_chatsupport.*, %1$stbl_drivers.firstname, %1$stbl_drivers.lastname, %1$stbl_drivers.phone, %1$stbl_drivers.country_dial_code FROM %1$stbl_chatsupport
INNER JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_chatsupport.driver_id
WHERE %1$stbl_chatsupport.session_status = 1 GROUP BY `driver_id` ORDER BY %1$stbl_chatsupport.date_created DESC LIMIT %2$d, %3$d', DB_TBL_PREFIX, $offset, ITEMS_PER_PAGE);

if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){
        while($row = mysqli_fetch_assoc($result)){
            $open_chat_support_data[] = $row;
        }
        
    }
}





ob_start();
include('../../drop-files/templates/admin/dchatsupporttpl.php');
$pageContent = ob_get_clean();
$GLOBALS['admin_template']['page_content'] = $pageContent;
include "../../drop-files/templates/admin/admin-interface.php";
exit;


?>