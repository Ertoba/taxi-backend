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



$GLOBALS['admin_template']['page_title'] = "<i class='fa fa-plane'></i> Edit Zone"; //Set the title of the page on the admin interface
$GLOBALS['admin_template']['active_menu'] = "zones"; //Set the appropriate menu item active
$GLOBALS['admin_template']['active_sub_menu'] = "zones-all"; //Set the appropriate menu item active

if(!empty($_POST['zone-id'])){
    $id = (int) $_POST['zone-id'];
 }elseif(!empty($_GET['id'])) {
        $id = (int) $_GET['id'] ;
 }

$intra_city_routes = [];
$zone_data = [];



//Process DELETE record command
if(isset($_GET['action']) && $_GET['action']== "delete"){

    if(DEMO){
        $_SESSION['action_error'][] = "You are running in Demo mode. Action cannot be completed";
        header("location: ".SITE_URL."admin/all-franchise.php"); //Yes? then redirect 
        exit;
    }

    
    //Ensure that zone exists on DB
    $query = sprintf('SELECT id FROM %stbl_zones WHERE id = "%d"',DB_TBL_PREFIX, $id );
           if($result = mysqli_query($GLOBALS['DB'], $query)){
       
                if(!mysqli_num_rows($result)){

                    $_SESSION['action_error'][] = "Could not delete the requested record. The record was not found in the database";
                    header("location: ".SITE_URL."admin/all-zones.php"); //Yes? then redirect
                    exit;           

                }   
           mysqli_free_result($result);
       }  

  
   //then delete record
       $query = sprintf('DELETE FROM %stbl_zones WHERE id = "%d"', DB_TBL_PREFIX, $id); 
       if(!$result = mysqli_query($GLOBALS['DB'], $query)){ 
            
            $_SESSION['action_error'][] = "An error occured while trying to delete zone record from the database.";
            header("location: ".SITE_URL."admin/all-zones.php"); //Yes? then redirect
            exit; 

       }

             

        

        $_SESSION['action_success'][] = "The zone record was successfully deleted.";
        header("location: ".SITE_URL."admin/all-zones.php"); //Yes? then redirect
        exit;



}



//get zone data from DB
$query = sprintf('SELECT * FROM %stbl_zones WHERE id = "%d"', DB_TBL_PREFIX, $id); //Get required zone information from DB


if($result = mysqli_query($GLOBALS['DB'], $query)){
    if(mysqli_num_rows($result)){

        $zone_data = mysqli_fetch_assoc($result);

        $query = sprintf('SELECT * FROM %stbl_routes WHERE id = %d AND r_scope = %d', DB_TBL_PREFIX, $zone_data['city_id'], 0);

        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                $row = mysqli_fetch_assoc($result);
                $intra_city_routes = $row;
                
            }else{
                $_SESSION['action_error'][] = "The city this zone belongs to cannot be found.";
            }
        }else{
            $_SESSION['action_error'][]    = "Database error!";
        }
                
    }else{
        $_SESSION['action_error'][]    = "Invalid zone record.";
    }
    
}
else{ //No record matching the USER ID was found in DB. Show view to notify user

    $_SESSION['action_error'][]    = "Database error!";
}



if(!empty($_SESSION['action_error'])){   
    header("location: ".SITE_URL."admin/all-zones.php"); //Yes? then redirect        
    exit;

}






if(empty($_POST)){ //let's render the edit zone page UI'
    
    ob_start();
    include('../../drop-files/templates/admin/editzonetpl.php');
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


/* var_dump($_POST);
exit; */

if(empty($_POST['zone-name'])){

    $_SESSION['action_error'][]    = "Please enter a name for this zone";
}


if(empty($_POST['zone-city-id']) || $_POST['zone-city-id'] == 0){

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
    include('../../drop-files/templates/admin/editzonetpl.php'); 
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


//update zone data in database
$query = sprintf('UPDATE %stbl_zones SET title = "%s",city_id = "%d",zone_fare_type = "%d",zone_fare_value = "%s",zone_bound_coords = "%s",zone_create_date = "%s" WHERE id = "%d"',
    DB_TBL_PREFIX,
    mysqli_real_escape_string($GLOBALS['DB'], $_POST['zone-name']),
    (int) $_POST['zone-city-id'],
    (int) $_POST['zone-fare-type'],
    (float) $_POST['zone-inc-val'],
    $zone_boundary_data_sanitized,
    gmdate('Y-m-d H:i:s', time()),
    $id

);




    if(! $result = mysqli_query($GLOBALS['DB'], $query)){
        //echo mysqli_error($GLOBALS['DB']);
        
        $_SESSION['action_error'][] = "An error has occured. Could not update zone data. Ensure database connection is working";
    
        ob_start();
        include('../../drop-files/templates/admin/editzonetpl.php'); 
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




   
        $_SESSION['action_success'][] = "The zone was updated successfully.";
        header("location: ".SITE_URL."admin/all-zones.php"); //Yes? then redirect
        exit;



?>