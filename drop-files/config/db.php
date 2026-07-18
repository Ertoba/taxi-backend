<?php

/**
 * Configuration for: Database Connection
 * 
 */

define("DB_HOST", "127.0.0.1");
define("DB_TBL_PREFIX", "cab_");
define("DB_SCHEMA", "dropDB");
define("DB_USER", "root");
define("DB_PASS", "");



//skip DB connection for these endpoints
$actions_arr = ['getplacesautocomplete','getdirections','geocodeplace','getavailablecitydrivers','setDriverLocation','setDriverLocationbg','whatsappAuthCheck','getScheduledBookings'];
if(isset($_GET['action_get']) && in_array($_GET['action_get'],$actions_arr))return;
if(isset($_GET['action']) && in_array($_GET['action'],$actions_arr))return;


// establish a connection to the database server

if(!connectMysqlDB()){
    die("Error: Unable to connect to database server.");
    exit;
};


function connectMysqlDB(){
    
    if (!$GLOBALS['DB'] = mysqli_connect(DB_HOST, DB_USER, DB_PASS))
    {
        return false;        
    }

    mysqli_set_charset($GLOBALS['DB'],'utf8mb4'); //enables mysql php unicode communication 

    if (!mysqli_select_db($GLOBALS['DB'], DB_SCHEMA ))
    {
        mysqli_close($GLOBALS['DB']);
        return false;        
       
    }

    return true;
}

?>