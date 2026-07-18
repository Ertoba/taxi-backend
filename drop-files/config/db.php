<?php

/**
 * Configuration for: Database Connection
 */

if (!function_exists('droptaxiConfigValue')) {
    function droptaxiConfigValue($name, $default = '') {
        $file = getenv($name . '_FILE');
        if ($file !== false && $file !== '' && is_readable($file)) {
            return trim(file_get_contents($file));
        }

        $value = getenv($name);
        return $value === false ? $default : $value;
    }
}

define("DB_HOST", droptaxiConfigValue("DB_HOST", "127.0.0.1"));
define("DB_TBL_PREFIX", droptaxiConfigValue("DB_TBL_PREFIX", "cab_"));
define("DB_SCHEMA", droptaxiConfigValue("DB_SCHEMA", "dropDB"));
define("DB_USER", droptaxiConfigValue("DB_USER", "root"));
define("DB_PASS", droptaxiConfigValue("DB_PASS", ""));

// Skip DB connection for these endpoints.
$actions_arr = ['getplacesautocomplete','getdirections','geocodeplace','getavailablecitydrivers','setDriverLocation','setDriverLocationbg','whatsappAuthCheck','getScheduledBookings'];
if(isset($_GET['action_get']) && in_array($_GET['action_get'],$actions_arr))return;
if(isset($_GET['action']) && in_array($_GET['action'],$actions_arr))return;

// Establish a connection to the database server.
if(!connectMysqlDB()){
    die("Error: Unable to connect to database server.");
    exit;
};

function connectMysqlDB(){
    if (!$GLOBALS['DB'] = mysqli_connect(DB_HOST, DB_USER, DB_PASS))
    {
        return false;
    }

    mysqli_set_charset($GLOBALS['DB'],'utf8mb4'); // Enables MySQL/PHP Unicode communication.

    if (!mysqli_select_db($GLOBALS['DB'], DB_SCHEMA ))
    {
        mysqli_close($GLOBALS['DB']);
        return false;
    }

    return true;
}

?>
