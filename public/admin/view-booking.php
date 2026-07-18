<?php
session_start();
include("../../drop-files/lib/common.php");
include "../../drop-files/config/db.php";
include "../../drop-files/config/redis.php";

$route_data = [];
$booking_data = [];
$id = 0;

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

$GLOBALS['admin_template']['page_title'] = "<i class='fa fa-bookmark'></i> View Booking"; //Set the title of the page on the admin interface
$GLOBALS['admin_template']['active_menu'] = "bookings"; //Set the appropriate menu item active



if(!empty($_GET['bkid'])) {
    $id = (int) $_GET['bkid'] ;
}else{
    $id = 0;    
}




/* //Get all route tariff
$query = sprintf('SELECT * FROM %1$stbl_routes', DB_TBL_PREFIX); //Get required user information from DB


if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){
        while($row = mysqli_fetch_assoc($result)){
            $route_data[] = $row;
        }
                         
    }    
} */



//Get all route tariff
$query = sprintf('SELECT *,%1$stbl_drivers.country_dial_code AS driver_country_dial_code,%1$stbl_users.country_dial_code AS user_country_dial_code,%1$stbl_drivers.driver_id AS driver_ids, %1$stbl_users.user_id AS user_ids,%1$stbl_bookings.id AS booking_id,%1$stbl_bookings.route_id AS b_route_id,%1$stbl_users.firstname AS user_firstname, %1$stbl_users.lastname AS user_lastname, %1$stbl_users.phone AS user_phone, %1$stbl_bookings.ride_id AS bookride_id, %1$stbl_drivers.photo_file AS driver_photo, %1$stbl_users.photo_file AS user_photo, %1$stbl_bookings.driver_commision AS drv_commision, %1$stbl_bookings.franchise_commision AS franch_commision FROM %1$stbl_bookings
INNER JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
LEFT JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id
LEFT JOIN %1$stbl_ratings_users ON %1$stbl_ratings_users.booking_id = %1$stbl_bookings.id
LEFT JOIN %1$stbl_ratings_drivers ON %1$stbl_ratings_drivers.booking_id = %1$stbl_bookings.id
LEFT JOIN %1$stbl_rides ON %1$stbl_rides.id = %1$stbl_bookings.ride_id
LEFT JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_bookings.route_id
WHERE %1$stbl_bookings.id = "%2$d"  ', DB_TBL_PREFIX,$id); //Get required user information from DB


if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){
        
            $booking_data = mysqli_fetch_assoc($result);
        
                         
    }    
}



//get driver travel route data for this booking
$redis = connectRedis();

if($redis){
    $driver_route_data = $redis->zRange("drv_route:{$booking_data['driver_ids']}:{$booking_data['booking_id']}",0,-1);

    if(empty($driver_route_data)){
        //check DB
        $query = sprintf('SELECT * FROM %stbl_driver_travel_route WHERE booking_id = %d', DB_TBL_PREFIX, $booking_data['booking_id']);
        if($result = mysqli_query($GLOBALS['DB'], $query)){
            $row = mysqli_fetch_assoc($result);
            if(!empty($row['route_data'])){
                $driver_route_data = unserialize($row['route_data']);
            }
            
        }
    }

    if(!empty($driver_route_data)){

        foreach($driver_route_data as $route_data){
            $data_components = explode("|",$route_data);
            $route_polyline_data[] = ['lat' => $data_components[0], 'lng' => $data_components[1], 'time' => $data_components[2]];
            $route_polyline_latlong[] = ['lat' => floatval($data_components[0]), 'lng' => floatval($data_components[1])];
        }
        $route_start_lat = $route_polyline_data[0]['lat'];
        $route_start_long = $route_polyline_data[0]['lng'];


        //var_dump($route_polyline_data);


        $route_end_lat = $route_polyline_data[count($route_polyline_data) - 1]['lat'];
        $route_end_long = $route_polyline_data[count($route_polyline_data) - 1]['lng'];
    }
}




//get chat messages for booking
$chat_messages_html = '';

$query = sprintf('SELECT %1$stbl_users.photo_file AS user_photo_file,%1$stbl_users.firstname AS user_firstname,%1$stbl_drivers.firstname AS driver_firstname,%1$stbl_drivers.photo_file AS driver_photo_file, %1$stbl_chats.chat_msg, %1$stbl_chats.user_id AS chat_user_id, %1$stbl_chats.driver_id AS chat_driver_id FROM %1$stbl_chats 
LEFT JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_chats.user_id
LEFT JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_chats.driver_id
WHERE %1$stbl_chats.booking_id = %2$d ORDER BY date_created ASC', 
    DB_TBL_PREFIX, 
    $id
);

if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){

        while($row = mysqli_fetch_assoc($result)){
            if($row['chat_driver_id'] != 0){
                //driver chat message                
                $driver_photo = isset($row['driver_photo_file']) ? $row['driver_photo_file'] : "0";
                $driver_photo = SITE_URL . "ajaxphotofile.php?file=" . $driver_photo;
                //$chat_messages_html .= "<div style='text-align:left;margin-top: 5px;'><div style='background-color:#1e88e5;width:55%;border-radius:10px;padding:5px;display:inline-block'><div><img style='width:32px;border-radius:50%;vertical-align: middle;' src='{$driver_photo}' /> <span style='font-weight:bold; font-size:14px;color:white;'>{$row['driver_firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;'>{$row['chat_msg']}</p></div></div>";
                $chat_messages_html .= "<div style='text-align:left;margin-top: 5px;'><div style='background-color:#1e88e5;width:55%;border-radius:10px;padding:5px;display:inline-block'><div><i style='font-size: 22px;color: white;vertical-align: middle;' class='fa fa-user-circle-o'></i> <span style='font-weight:bold; font-size:14px;color:white;'>{$row['driver_firstname']} (Driver)</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;'>{$row['chat_msg']}</p></div></div>";

            }elseif($row['chat_user_id'] != 0){
                //rider chat message
                $user_photo = isset($row['user_photo_file']) ? $row['user_photo_file'] : "0";
                $user_photo = SITE_URL . "ajaxuserphotofile.php?file=" . $user_photo;
                //$chat_messages_html .= "<div style='text-align:right;margin-top: 5px;'><div style='background-color:#7cb342;width:55%;border-radius:10px;padding:5px;display:inline-block'><div style='text-align:left'><img style='width:32px;border-radius:50%;vertical-align: middle;' src='{$user_photo}' /> <span style='font-weight:bold; font-size:14px;color:white;'>{$row['user_firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;'>{$row['chat_msg']}</p></div></div>";
                $chat_messages_html .= "<div style='text-align:right;margin-top: 5px;'><div style='background-color:#7cb342;width:55%;border-radius:10px;padding:5px;display:inline-block'><div style='text-align:left'><i style='font-size: 22px;color: white;vertical-align: middle;' class='fa fa-user-circle-o'></i> <span style='font-weight:bold; font-size:14px;color:white;'>{$row['user_firstname']} (Rider)</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;'>{$row['chat_msg']}</p></div></div>";

            }                

        }
        

    }
}


/* var_dump($booking_data);
exit;
 */

/* //get all drivers

$query = sprintf('SELECT * FROM %1$stbl_drivers ORDER BY firstname ASC', DB_TBL_PREFIX);


if($result = mysqli_query($GLOBALS['DB'], $query)){
  
    if(mysqli_num_rows($result)){
        while($row = mysqli_fetch_assoc($result)){
            $drivers_data[] = $row;
        }
    
     }
    mysqli_free_result($result);
}   */ 






    
    ob_start();
    include('../../drop-files/templates/admin/viewbookingtpl.php');
    $GLOBALS['admin_template']['page_content'] = ob_get_clean();
    include "../../drop-files/templates/admin/admin-interface.php";
    exit;











?>