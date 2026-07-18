<?php
session_start();
include("../../drop-files/lib/common.php");
include "../../drop-files/config/db.php";

if(isset($_SESSION['expired_session'])){
    header("location: ".SITE_URL."login.php?timeout=1");
    exit;
}

if(!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1)){ //if user is not logged in run this code
  header("location: ".SITE_URL."login.php"); //Yes? then redirect user to the login page
  exit;
}

if($_SESSION['account_type'] /* != 2 && $_SESSION['account_type'] */ != 3){ ////if user is not an admin
    $_SESSION['action_error'][] = "Access Denied!";
    header("location: ".SITE_URL."admin/index.php"); //Yes? then redirect user to the login page
    exit;
}



$GLOBALS['admin_template']['page_title'] = "<i class='fa fa-plane'></i> Add Zone"; //Set the title of the page on the admin interface
$GLOBALS['admin_template']['active_menu'] = "zones"; //Set the appropriate menu item active
$GLOBALS['admin_template']['active_sub_menu'] = "zones-new"; //Set the appropriate menu item active



$intra_city_routes = [];


$query = sprintf('SELECT * FROM %stbl_routes WHERE r_scope = %d', DB_TBL_PREFIX, 0);

if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){
        while($row = mysqli_fetch_assoc($result)){
            $intra_city_routes[] = $row;
        }
    }
}



if(empty($_POST)){ //let's render the add-new-zone page UI'
    
    ob_start();
    include('../../drop-files/templates/admin/addzonetpl.php');
    $pageContent = ob_get_clean();
    $GLOBALS['admin_template']['page_content'] = $pageContent;
    include "../../drop-files/templates/admin/admin-interface.php";
    exit;


}




if(DEMO){
    $_SESSION['action_error'][] = "You are running in Demo mode. Action cannot be completed";
    header("location: ".SITE_URL."admin/all-zones.php"); //Yes? then redirect 
    exit;
}


//var_dump($_POST);

if(empty($_POST['zone-name'])){

    $_SESSION['action_error'][]    = "Please enter a name for this zone";
}


if(empty($_POST['zone-city']) || $_POST['zone-city'] == 0){

    $_SESSION['action_error'][]    = "Please select a city for this zone";
}


if(empty($_POST['zone-boundary-data'])){

    $_SESSION['action_error'][]    = "Please draw a boundary for this zone inside the city.";
}



if(empty($_POST['zone-inc-val'])){

    $_SESSION['action_error'][]    = "Zone fare increase value is not valid";
}




if(!empty($_SESSION['action_error'])){
   
    ob_start();
    include('../../drop-files/templates/admin/addzonetpl.php'); 
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
    



    $pageContent = ob_get_clean();
    $GLOBALS['admin_template']['page_content'] = $pageContent;
    include "../../drop-files/templates/admin/admin-interface.php";
    exit;



}



$zone_boundary_data_sanitized = htmlspecialchars(mysqli_real_escape_string($GLOBALS['DB'], $_POST['zone-boundary-data']));


//Store zone data to database
$query = sprintf('INSERT INTO %stbl_zones(title,city_id,zone_fare_type,zone_fare_value,zone_bound_coords) VALUES'.
'("%s","%d","%d","%s","%s")', 
    DB_TBL_PREFIX,
    mysqli_real_escape_string($GLOBALS['DB'], $_POST['zone-name']),
    (int) $_POST['zone-city'],
    (int) $_POST['zone-fare-type'],
    (float) $_POST['zone-inc-val'],
    $zone_boundary_data_sanitized

);


    if(! $result = mysqli_query($GLOBALS['DB'], $query)){
        //echo mysqli_error($GLOBALS['DB']);
        
        $_SESSION['action_error'][] = "An error has occured. Could not save new zone data to database. Ensure database connection is working";
    
        ob_start();
        include('../../drop-files/templates/admin/addzonetpl.php'); 
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
        



        $pageContent = ob_get_clean();
        $GLOBALS['admin_template']['page_content'] = $pageContent;
        include "../../drop-files/templates/admin/admin-interface.php";
        exit;

       
    }
    else{
            $id = mysqli_insert_id ( $GLOBALS['DB'] );
            
        }




   
        $_SESSION['action_success'][] = "The zone was created successfully.";
        header("location: ".SITE_URL."admin/all-zones.php"); //Yes? then redirect
        exit;



?>