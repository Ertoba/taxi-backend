<?php

/***
 * Starts a session with a specific timeout and a specific GC probability.
 * @param int $timeout The number of seconds until it should time out.
 * @param int $probability The probablity, in int percentage, that the garbage 
 *        collection routine will be triggered right now.
 * @param strint $cookie_domain The domain path for the cookie.
 * 
 * to set a session that will expire in 60 seconds (1 minute):
 * session_start_timeout(60);
 * in this case the probability of the garbage collector running is 100%. 
 * this can be an issue for high trafic sites. in this case reduce to a lower value using:
 * session_start_timeout(60, 10);
 * this gives a 10% chance for the GC routine to trigger
 * 
 */

function session_start_timeout($timeout=5, $probability=100, $cookie_domain='/') {
    // Set the max lifetime
    ini_set("session.gc_maxlifetime", $timeout);
 
    // Set the session cookie to timout
    ini_set("session.cookie_lifetime", $timeout);
 
    // Change the save path. Sessions stored in teh same path
    // all share the same lifetime; the lowest lifetime will be
    // used for all. Therefore, for this to work, the session
    // must be stored in a directory where only sessions sharing
    // it's lifetime are. Best to just dynamically create on.
    $seperator = strstr(strtoupper(substr(PHP_OS, 0, 3)), "WIN") ? "\\" : "/";
    $path = ini_get("session.save_path") . $seperator . "session_" . $timeout . "sec";
    if(!file_exists($path)) {
        
        if(!mkdir($path, 0777)) {
            trigger_error("Failed to create session save path directory '$path'. Check permissions.", E_USER_ERROR);
        }{
            chmod($path,0777); 
        }
    }
    ini_set("session.save_path", $path);
 
    // Set the chance to trigger the garbage collection.
    ini_set("session.gc_probability", $probability);
    ini_set("session.gc_divisor", 100); // Should always be 100
    ini_set("session.use_cookies",0); //do not use cookie for now
        
     
    // Start the session!
    if(isset($_GET['sess_id']) && $_GET['sess_id'] != "0"){
        session_id(base64_decode($_GET['sess_id'])); 
    }
    
    session_start();

    // Renew the time left until this session times out.
    // If you skip this, the session will time out based
    // on the time when it was created, rather than when
    // it was last used.

    if(isset($_COOKIE[session_name()])) {
        //setcookie(session_name(), $_COOKIE[session_name()], time() + $timeout,$cookie_domain);
        $expiry_time = time() + $timeout;
	    header('Set-Cookie: ' . session_name() . '=' . session_id() . "; expires={$expiry_time}; " .' path=/; SameSite=None; Secure');
    }
}


?>