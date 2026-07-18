<?php
include("../drop-files/lib/session_start_timeout.php");
session_start_timeout(25920002, 1);// start a session using our custom session routine instead of (session_start()) to avoid PHP Garbarge collector interferring.
include("../drop-files/lib/common.php");
include "../drop-files/config/db.php";
include "../drop-files/config/redis.php";



/*

This Script handles ajax calls from the client end. Useful for tasks that don't require client page refresh or reload'

return values
--------------

X99 = User not logged in.
X100 = no action set; invalid function


*/

/* var_dump($_POST);
var_dump($_SERVER);
exit; */

//load appropriate language file

if(!empty($_SESSION['lang'])){
    if(file_exists(FILES_FOLDER . "/lang/rider/{$_SESSION['lang']}.php")){
        include FILES_FOLDER . "/lang/rider/{$_SESSION['lang']}.php"; 
    }else{
        include FILES_FOLDER . "/lang/rider/en.php";
    }
}else{
    include FILES_FOLDER . "/lang/rider/en.php";
}


if(isset($_POST['action'])){

    if(function_exists($_POST['action'])){

        call_user_func($_POST['action']);
        exit;
    
    
    }
    
}elseif(isset($_GET['action_get'])){

    if(function_exists($_GET['action_get'])){

        call_user_func($_GET['action_get']);
        exit;
    
    
    }

}else{

    echo "X100"; //no action set; invalid function
    exit;
} 








echo "invalid function call";
exit;



function calctariff(){

    
    $tariff_data = [];


    if(!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1)){ //if user is not logged in run this code
        $error = array("notloggedin"=>"Please login to cotinue.");
        echo json_encode($error); //database error
        exit;
    }


    $route_id = (int) $_POST['route_id'];
    $ride_id = (int) $_POST['ride_id'];


    $query = sprintf('SELECT * FROM %stbl_rides_tariffs WHERE routes_id = "%d" AND ride_id = "%d"', DB_TBL_PREFIX, $route_id,$ride_id); //Get required user information from DB

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $tariff_data = mysqli_fetch_assoc($result);                    
        }else{
            $error = array("error"=>"Error computing tariff. Please retry.");
            echo json_encode($error); //database error
            exit;
        }
        
    }
    else{ //No record matching the USER ID was found in DB. Show view to notify user

        $error = array("error"=>"Error computing tariff. Please retry.");
        echo json_encode($error); //database error
        exit;
    }

    
    //Get distance data from google maps
    $url = "https://maps.googleapis.com/maps/api/directions/json?origin={$_POST['a_lat']},{$_POST['a_lng']}&destination={$_POST['b_lat']},{$_POST['b_lng']}&key=" . GMAP_API_KEY_BACKEND;
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPGET, true);
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
    //curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
    $json_response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    $response = json_decode($json_response, true);
    if(json_last_error()){
        $error = array("error"=>"Error computing tariff. Please retry.");
        echo json_encode($error); //database error
        exit;
    }

    $pickup_cost = $tariff_data['pickup_cost'];
    $drop_off_cost = $tariff_data['drop_off_cost'];
    $cost_per_km = $tariff_data['cost_per_km'];
    $cost_per_minute = $tariff_data['cost_per_minute'];


    $distance = $response['routes'][0]['legs'][0]['distance']['text'];
    $duration = $response['routes'][0]['legs'][0]['duration']['text'];
    $price = ($cost_per_km * $distance) + $drop_off_cost + $pickup_cost;

    //save this information in a session just in case the user decides to book this ride

    $token = crypto_string("nozero",5); //generate token

    unset($_SESSION['booking']);

    $_SESSION['booking'][$token]['a_lat'] = $_POST['a_lat'];
    $_SESSION['booking'][$token]['a_lng'] = $_POST['a_lng'];
    $_SESSION['booking'][$token]['b_lat'] = $_POST['b_lat'];
    $_SESSION['booking'][$token]['b_lng'] = $_POST['b_lng'];
    $_SESSION['booking'][$token]['p_addr'] = $_POST['p_addr'];
    $_SESSION['booking'][$token]['d_addr'] = $_POST['d_addr'];
    $_SESSION['booking'][$token]['route_id'] = $_POST['route_id'];
    $_SESSION['booking'][$token]['distance'] = $distance;
    $_SESSION['booking'][$token]['duration'] = $duration;
    $_SESSION['booking'][$token]['ride_id'] = $_POST['ride_id'];
    $_SESSION['booking'][$token]['token'] = $token;
    $_SESSION['booking'][$token]['cost'] = $price;
    



    
    $route_price_data = array('distance'=>$distance,'duration'=>$duration,'price'=>$price,'token'=>$token);
    echo json_encode($route_price_data); 
    exit;
    




}





function updateUserPhoto(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }


    $uploaded_photo_encoded = $_POST['photo']; //Get Base64 encoded image data. Encoded by our cropit jQuery plugin
    $uploaded_photo_encoded_array = explode(',', $uploaded_photo_encoded);
    $image_data = array_pop($uploaded_photo_encoded_array);
    $uploaded_photo_decoded = base64_decode($image_data); //Decode the data

    
    if(!$uploaded_photo_decoded){ //Verify that data is valid base64 data
        
        $error = array("error"=>__("Invalid photo format"));
        echo json_encode($error); 
        exit;
    } 

    //prepare filename and save the file. Cropit plugin has been configured to export base64 image data in JPEG format. We should be expecting a JPEG image data then.
   
    $filename =  crypto_string('distinct',20);

    @mkdir(realpath(CUSTOMER_PHOTO_PATH) . "/". $filename[0] . "/" . $filename[1] . "/" . $filename[2], 0777, true);


    $image_path = realpath(CUSTOMER_PHOTO_PATH) .  "/". $filename[0] . "/" . $filename[1] . "/" . $filename[2] . "/";
    $file = $image_path . $filename . ".jpg";
    
    file_put_contents($file, $uploaded_photo_decoded); //store the photo to disk.       

    $user_photo = $filename . ".jpg";

    
    //update database
    $query = sprintf('UPDATE %stbl_users SET photo_file = "%s" WHERE user_id = "%d"', 
    DB_TBL_PREFIX, 
    $user_photo,
    $_SESSION['uid']
    );

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){
        $error = array("error"=>__("Failed to update your photo"));
        echo json_encode($error); 
        exit;
    }
        
    $_SESSION['photo'] = SITE_URL . "ajaxuserphotofile.php?file=" . $user_photo;

    

    
    $data = array("success"=>1,'photo_url'=> SITE_URL . "ajaxuserphotofile.php?file=" . $user_photo);
    echo json_encode($data);
    exit;






}


function verifyUserEmail(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); //invalid record
        exit; 
    }


    $_POST['email'] = trim($_POST['email']);


    if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
        
        $error = array("error"=>__("Your email is not a valid email format"));
        echo json_encode($error); 
        exit;
    }


    $user_email_components = explode('@', $_POST['email']);

    if(!empty($user_email_components)){
        if($user_email_components[1] == "fakemail.com"){
            $error = array("error"=>__("This is a placeholder email format. Please use a valid email"));
            echo json_encode($error); //invalid record
            exit;
        }
    }


    if($_POST['email'] == $_SESSION['email']){
        $error = array("error"=>__("The email already exists. Please use a different email"));
        echo json_encode($error); //invalid record
        exit; 
    }

    

    //Check if email already exists
    $msg = '';
    $query = sprintf('SELECT user_id,email FROM %stbl_users WHERE user_id != "%d" AND email = "%s"', DB_TBL_PREFIX,$_SESSION['uid'], mysqli_real_escape_string($GLOBALS['DB'], $_POST['email']));

    
    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){

            $row = mysqli_fetch_assoc($result);
            $error = array("error"=>__("The email already exists. Please use a different email"));
            echo json_encode($error); //invalid record
            exit;    
            
        
        }
    }else{

        
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //invalid record
        exit;
        
    }

    $verification_code = crypto_string("nozero",6);

    $_SESSION['change_user_email']['email'] = mysqli_real_escape_string($GLOBALS['DB'], $_POST['email']);
    $_SESSION['change_user_email']['code'] = $verification_code;

    //send code to email

    $mail_sender_address = 'From: '.MAIL_SENDER;
    $headers = array($mail_sender_address,'MIME-Version: 1.0', 'Content-Type: text/html; charset="iso-8859-1"'); //Required for a HTML formatted E-mail ;)

    
    
    $msg = "Please find your email verification code below: <br> <b>{$verification_code}</b>";

    if(EMAIL_TRANSPORT == 1){
        mail($_POST['email'], WEBSITE_NAME . " - Email Verification Code","<html>".$msg."</html>", join("\r\n", $headers));
    }else{
        sendMail($_POST['email'], WEBSITE_NAME . " - Email Verification Code", $msg);
    }


    
    $resp = array("success"=> 1);
    echo json_encode($resp); 
    exit;



}


function saveUserEmail(){


    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); //invalid record
        exit; 
    }


    

    if(!isset($_SESSION['change_user_email'])){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //invalid record
        exit; 
    }

    if($_SESSION['change_user_email']['code'] != $_POST['code']){
        $error = array("error"=>__("The email verification code you've entered is incorrect"));
        echo json_encode($error); //invalid record
        exit;
    }
    

    
    //update email
    $query = sprintf('UPDATE %stbl_users SET `email` = "%s" WHERE user_id = "%d"', 
        DB_TBL_PREFIX,
        $_SESSION['change_user_email']['email'],
        $_SESSION['uid']
    );

    $result = mysqli_query($GLOBALS['DB'], $query);

    $_SESSION['email'] = $_SESSION['change_user_email']['email'];
    
    $resp = array("success"=> 1, 'email' => $_SESSION['change_user_email']['email']);
    echo json_encode($resp); 
    unset($_SESSION['change_user_email']);
    exit;


}



function saveUserDoc(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); //invalid record
        exit; 
    } 

    
    $doc_id = (int) $_POST['doc_id'];
    $doc_data = [];
    $doc_expiry_date = !empty($_POST['doc_expiry']) ? mysqli_real_escape_string($GLOBALS['DB'],$_POST['doc_expiry']) : "";
    $doc_id_num = !empty($_POST['doc_id_input']) ? mysqli_real_escape_string($GLOBALS['DB'],$_POST['doc_id_input']) : "";


    //get the details about this document
    $query = sprintf('SELECT *, %1$stbl_users_documents.id AS user_doc_id FROM %1$stbl_documents 
    LEFT JOIN %1$stbl_users_documents ON %1$stbl_users_documents.doc_id = %1$stbl_documents.id AND %1$stbl_users_documents.u_id = %3$d AND %1$stbl_users_documents.u_type = 0
    WHERE %1$stbl_documents.id = %2$d LIMIT 1', DB_TBL_PREFIX, $doc_id,$_SESSION['uid']);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $doc_data = mysqli_fetch_assoc($result);
        }else{
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); //invalid record
            exit;
        }

    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //invalid record
        exit;
    }

    


    $user_doc_photo = "";
    if(!empty($_POST['doc_img'])){ 

        $uploaded_photo_encoded = $_POST['doc_img']; //Get Base64 encoded image data.
        $uploaded_photo_encoded_array = explode(',', $uploaded_photo_encoded);
        $image_data = array_pop($uploaded_photo_encoded_array);
        $uploaded_photo_decoded = base64_decode($image_data); //Decode the data

        
        if(!$uploaded_photo_decoded){ //Verify that data is valid base64 data
            
            $error = array("error"=>__("Invalid photo format"));
            echo json_encode($error); 
            exit;
        } 

        //prepare filename and save the file.
    
        $filename =  crypto_string('distinct',20);

        @mkdir(realpath(CUSTOMER_PHOTO_PATH) . "/". $filename[0] . "/" . $filename[1] . "/" . $filename[2], 0777, true);


        $image_path = realpath(CUSTOMER_PHOTO_PATH) .  "/". $filename[0] . "/" . $filename[1] . "/" . $filename[2] . "/";
        $file = $image_path . $filename . ".jpg";
        
        file_put_contents($file, $uploaded_photo_decoded); //store the photo to disk.       

        $user_doc_photo = $filename . ".jpg";

    }


    if(!empty($doc_data['user_doc_id'])){ //document already exists. Update

        if($doc_data['u_can_edit'] == 0){ //user not allowed to edit document
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); //invalid record
            exit;
        }


        $old_doc_image_filename = $doc_data['u_doc_img'];
        $image_path = realpath(CUSTOMER_PHOTO_PATH) .  "/". $old_doc_image_filename[0] . "/" . $old_doc_image_filename[1] . "/" . $old_doc_image_filename[2] . "/";
        $old_image_full_path = $image_path . $old_doc_image_filename;

        $user_doc_photo = empty($user_doc_photo) ? $old_doc_image_filename : $user_doc_photo;
        $doc_expiry_date = empty($doc_expiry_date) ? "NULL" : '"' . $doc_expiry_date . '"';

        $query = sprintf('UPDATE %stbl_users_documents SET u_can_edit = "%d", u_doc_status = "%d", u_doc_img = "%s", u_doc_expiry_date = %s, u_doc_id_num = "%s", date_updated = "%s" WHERE id = "%d"', 
            DB_TBL_PREFIX,
            0,
            0,
            $user_doc_photo,            
            $doc_expiry_date,
            $doc_id_num,
            gmdate('Y-m-d H:i:s', time()),
            $doc_data['user_doc_id']
        );

        if($result = mysqli_query($GLOBALS['DB'], $query)){
            $data = array("success"=>1,'doc_img_url'=> SITE_URL . "ajaxuserphotofile.php?file=" . $user_doc_photo);
            echo json_encode($data);
            exit;
        }else{
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); //invalid record
            exit;
        }




    }else{ //new document uploaded by user. Create

        if(empty($user_doc_photo)){
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); //invalid record
            exit; 
        }

        if($doc_data['doc_expiry'] == 1){
            if(!isValidDate($doc_expiry_date)){
                $error = array("error"=>__("An error has occured"));
                echo json_encode($error); //invalid record
                exit; 
            }
        }
    
        if($doc_data['doc_id_num'] == 1){
            if(empty($doc_id_num)){
                $error = array("error"=>__("An error has occured"));
                echo json_encode($error); //invalid record
                exit;
            }
        }

        $doc_expiry_date = empty($doc_expiry_date) ? "NULL" : '"' . $doc_expiry_date . '"';

        $query = sprintf('INSERT INTO %stbl_users_documents (u_doc_status,u_doc_img,u_doc_title,u_doc_expiry_date,u_doc_id_num,u_doc_id_num_title,u_type,u_id,doc_id) VALUES
        ("%d","%s","%s",%s,"%s","%s","%d","%d","%d")',
         DB_TBL_PREFIX,
         0,
         $user_doc_photo,
         $doc_data['title'],
         $doc_expiry_date,
         $doc_id_num,
         $doc_data['doc_id_num_title'],
         0, //rider
         $_SESSION['uid'],
         $doc_id
        
        );

        if($result = mysqli_query($GLOBALS['DB'], $query)){            
            $data = array("success"=>1,'doc_img_url'=> SITE_URL . "ajaxuserphotofile.php?file=" . $user_doc_photo);
            echo json_encode($data);
            exit;
        }else{
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); //invalid record
            exit;
        }
    }
    
}


function getUserDocs(){
    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); //invalid record
        exit; 
    }

    //get user documents

    $user_documents = [];
    $query = sprintf('SELECT *,%1$stbl_documents.id AS d_id FROM %1$stbl_documents
    LEFT JOIN %1$stbl_users_documents ON %1$stbl_users_documents.doc_id = %1$stbl_documents.id AND %1$stbl_users_documents.u_id = %2$d AND %1$stbl_users_documents.u_type = 0
    WHERE %1$stbl_documents.doc_user = 0 AND %1$stbl_documents.status = 1', DB_TBL_PREFIX, $_SESSION['uid']);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                if(!empty($row['u_doc_img'])){
                    $row['u_doc_img'] = SITE_URL . "ajaxuserphotofile.php?file=" . $row['u_doc_img'];
                }
                $user_documents[$row['d_id']] = $row;
            }

            $data = array("success"=>1,'user_docs'=> $user_documents);
            echo json_encode($data);
            exit;


        }else{
            $data = array("success"=>1,'user_docs'=> $user_documents);
            echo json_encode($data);
            exit;
        }

    }


    $error = array("error"=>__("An error has occured"));
    echo json_encode($error); //invalid record
    exit;




}


function saveUserPwd(){


    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); //invalid record
        exit; 
    }


    

    if((strlen($_POST['password']) < 8 )){
        
        $error = array("error"=>__("Password is too short"));
        echo json_encode($error); 
        exit;
    }
    

    
    //update password

    $query = sprintf('UPDATE %stbl_users SET `pwd_raw` = "%s",password_hash = "%s" WHERE user_id = "%d"', 
        DB_TBL_PREFIX,
        mysqli_real_escape_string($GLOBALS['DB'], $_POST['password']),
        password_hash(mysqli_real_escape_string($GLOBALS['DB'], $_POST['password']), PASSWORD_DEFAULT),
        $_SESSION['uid']
    );

    $result = mysqli_query($GLOBALS['DB'], $query);

    
    $resp = array("success"=> 1);
    echo json_encode($resp);
    exit;


}



function updateUserProfile(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); //invalid record
        exit; 
    }

    $user_country = codeToCountryName(strtoupper($_POST['country_code']));

    if(!$user_country){
        
        $error = array("error"=>__("Invalid country selected"));
        echo json_encode($error); //invalid country
        exit;
    }

    if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
        
        $error = array("error"=>__("Your email is not valid"));
        echo json_encode($error); //invalid record
        exit;
    }

    //Check if email or phone number already exists
    $msg = '';
    $query = sprintf('SELECT user_id,email, phone FROM %stbl_users WHERE user_id != "%d" AND (email = "%s" OR phone="%s")', DB_TBL_PREFIX,$_SESSION['uid'], mysqli_real_escape_string($GLOBALS['DB'], $_POST['email']),mysqli_real_escape_string($GLOBALS['DB'], $_POST['phone']));

    
    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){

            $row = mysqli_fetch_assoc($result);
            if($row['email'] == mysqli_real_escape_string($GLOBALS['DB'], $_POST['email'])){
                
                $error = array("error"=>__("The email already exists. Please use a different email"));
                echo json_encode($error); //invalid record
                exit;

            }elseif($row['phone'] == mysqli_real_escape_string($GLOBALS['DB'], $_POST['phone'])){
                
                $error = array("error"=>__("The phone number already exists. Please use a different phone number"));
                echo json_encode($error); //invalid record
                exit;

            }else{
                
                $error = array("error"=>__("The email or phone number already exists. Please use a different email or phone number"));
                echo json_encode($error); //invalid record
                exit;

            }      
            
        
        }
    }else{

        
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //invalid record
        exit;
        
    }


    if(!empty($_POST['oldpassword']) && empty($_POST['newpassword'])){
        $error = array("error"=>__("Your new password cannot be empty"));
        echo json_encode($error); //invalid record
        exit;
    }


    if(!empty($_POST['oldpassword'])){

        //verify the old password is correct
        $query = sprintf('SELECT user_id FROM %stbl_users WHERE pwd_raw = "%s" AND user_id = "%d"', DB_TBL_PREFIX, mysqli_real_escape_string($GLOBALS['DB'], $_POST['oldpassword']), $_SESSION['uid']); //Get required user information from DB

        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                //old password is valid. update record with new password
                $query = sprintf('UPDATE %stbl_users SET `pwd_raw` = "%s" WHERE user_id = "%d"', DB_TBL_PREFIX,mysqli_real_escape_string($GLOBALS['DB'], $_POST['newpassword']),$_SESSION['uid']);
                $result = mysqli_query($GLOBALS['DB'], $query);
                $msg = __('Password was changed successfully').'<br>';

            }
            else{
                $error = array("error"=>__("An error has occured"));
                echo json_encode($error); //invalid record
                exit;

            }
            
        }
        else{ //No record matching the USER ID was found in DB. Show view to notify user

            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); //database error
            exit;
        }

    }



    //update phone number or email
    $query = sprintf('UPDATE %stbl_users SET `phone` = "%s", `email` = "%s", country_code = "%s", country_dial_code = "%s", country = "%s" WHERE user_id = "%d"', 
        DB_TBL_PREFIX,
        mysqli_real_escape_string($GLOBALS['DB'], $_POST['phone']),
        mysqli_real_escape_string($GLOBALS['DB'], $_POST['email']),
        mysqli_real_escape_string($GLOBALS['DB'],$_POST['country_code']),
        "+" . mysqli_real_escape_string($GLOBALS['DB'],$_POST['country_dial_code']),
        $user_country,
        $_SESSION['uid']
    );
    $result = mysqli_query($GLOBALS['DB'], $query);   

    $_SESSION['email'] = mysqli_real_escape_string($GLOBALS['DB'], $_POST['email']);
    $_SESSION['phone'] = mysqli_real_escape_string($GLOBALS['DB'], $_POST['phone']);
    $_SESSION['country_dial_code'] = "+" . mysqli_real_escape_string($GLOBALS['DB'],$_POST['country_dial_code']);


    $msg .= __('Profile updated successfully');
    $error = array("success"=>$msg);
    echo json_encode($error); //invalid record
    exit;



}



function updateProfile(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); //invalid record
        exit; 
    }



    //update phone number or email
    $query = sprintf('UPDATE %stbl_users SET `firstname` = "%s", `lastname` = "%s" WHERE user_id = "%d"', 
        DB_TBL_PREFIX,
        mysqli_real_escape_string($GLOBALS['DB'], $_POST['firstname']),
        mysqli_real_escape_string($GLOBALS['DB'], $_POST['lastname']),
        $_SESSION['uid']
    );
    $result = mysqli_query($GLOBALS['DB'], $query);   

    $_SESSION['firstname'] = mysqli_real_escape_string($GLOBALS['DB'], $_POST['firstname']);
    $_SESSION['lastname'] = mysqli_real_escape_string($GLOBALS['DB'], $_POST['lastname']);


    $msg = __('Profile updated successfully');
    $error = array("success"=>$msg);
    echo json_encode($error); //invalid record
    exit;



}





function updatePushNotificationToken(){

    if(empty($_SESSION['loggedin'])){
        exit; 
    }

   //process or store push notification token
   $push_notification_token = !empty($_POST['token']) ? mysqli_real_escape_string($GLOBALS['DB'], $_POST['token']) : "";

   if(!empty($push_notification_token) && $push_notification_token != $_SESSION['push_token']){
       //delete record of this token as it might be a different user signing in on the same device
       $query = sprintf('UPDATE %stbl_users SET `push_notification_token` = NULL WHERE push_notification_token = "%s"', DB_TBL_PREFIX,$push_notification_token);
       $result = mysqli_query($GLOBALS['DB'], $query);
       //update this users push notification token
       $query = sprintf('UPDATE %stbl_users SET `push_notification_token` = "%s" WHERE user_id = "%d"', DB_TBL_PREFIX,$push_notification_token,$_SESSION['uid'] );
       $result = mysqli_query($GLOBALS['DB'], $query);
       $_SESSION['push_token'] = $push_notification_token;
   }


}



function getbannerdata(){

    
    $banner_data = [];
    $formatted_banners = '';
    $user_route_id = !empty($_SESSION['route_id']) ? $_SESSION['route_id'] : 0;


    //check if cached data is available and return
    $redis = connectRedis();
    
    if($redis){
        $sbooking_redis_data = $redis->get("rdrbannerdata:{$user_route_id}");
        if(!empty($sbooking_redis_data)){
            return $sbooking_redis_data . "<div style='display:none'>From Redis</div>";
        }

    }

        
    $query = sprintf('SELECT * FROM %stbl_banners WHERE `status` = 1 AND (visibility = 0 OR visibility = 1) AND (city = 0 OR city = %d) ORDER BY date_created DESC LIMIT 4', DB_TBL_PREFIX, $user_route_id); //Get banner information from DB

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){

            while($row = mysqli_fetch_assoc($result)){
                
                $banner_data[] = $row;
                
            }
            
            mysqli_free_result($result);

        }else{
            return "nodata"; 
        }
        
    }
    else{ 

        return "nodata"; 
        
    }

    

    

    //format data for display on app
    $count = 0;
    foreach($banner_data as $bannerdata){

        if(!empty($bannerdata['feature_img'])){
            $banner_image_url = SITE_URL . "img/" . $bannerdata['feature_img'];
        }else{
            $banner_image_url = "img/default-banner-img.jpg";
        }

        $banner_content = array(
                                "banner_title"=>$bannerdata['title'],
                                "banner_excerpt"=>$bannerdata['excerpt'],
                                "banner_body"=>htmlspecialchars(preg_replace("/\"/", '&#039;', $bannerdata['content'])),
                                "feature_img"=>$banner_image_url,
                            );
        $banner_content_json = json_encode($banner_content);

        $display_banner = $count == 0 ? "" : "display:none;"; //display only the first banner item
        $formatted_banners .= "<div onclick='showBanner({$bannerdata['id']})' id='banner-info-item-{$bannerdata['id']}' style='background-image: url(img/default-banner-img.jpg); background-size: cover;text-align: left;position: relative;border-radius: 10px;max-width: 1024px;width: 98%;height: 100%;border: 2px solid white;box-sizing: border-box;margin: 0 auto;{$display_banner}'>
                                    <div class='banner-text-container' style='display: none; width: calc(100% - 60px); overflow: hidden;'>
                                        <p class='banner-title' style='padding: 6px;font-size: 13px;font-weight: bold;margin: 0;color: white;height: 3em;overflow: hidden;'>{$bannerdata['excerpt']}</p>
                                        <span id='banner-content-{$bannerdata['id']}' style='display:none;' type='text'>{$banner_content_json}</span>
                                    </div>
                                    <img style='display:none' src='{$banner_image_url}' onload='(function(el){\$(\"#banner-info-item-{$bannerdata['id']}\").css(\"background-image\",`url(\${el.src})`)})(this)' />
                                    
                                </div>";


        
    }


    //cache bannerdata on redis
    
    
    if($redis){
        //successfully connected to redis
        $redis->setEx("rdrbannerdata:{$user_route_id}", 120 , $formatted_banners);          

    }



    return $formatted_banners;

}



function updateBannerViewCount(){
    
    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please re-login and retry.");
        echo json_encode($error); 
        exit; 
    }

    $banner_id = (int) $_GET['id'];    

    if(empty($banner_id))exit;

    $redis = connectRedis();

    if($redis){
        $redis->zAdd("bannerviewcount:{$banner_id}",time(),"ridr:{$_SESSION['uid']}");
    }

    $res = array("success"=>1);
    echo json_encode($res); 
    exit;


}





function userLogin(){
    
    $phone = $_POST['phone'];
    $otp_code = $_POST['otp_code'];
    $phone_formatted = $_POST['phone_formatted'];
    $password = $_POST['password'];
    $country_dial_code = $_POST['country_call_code'];
    $user_account_details = [];
    $display_language = mysqli_real_escape_string($GLOBALS['DB'], $_POST['display_lang']);
    $fb_user_details = $_POST['fb_user_details'];
    $sdl_key = isset($_POST['sdl_key']) ? $_POST['sdl_key'] : "";

    
       

    if(isset($_POST['timezone'])){
        $timezones_fixed = ["Asia/Calcutta" => "Asia/Kolkata"];
        $_POST['timezone'] = isset($timezones_fixed[$_POST['timezone']]) ? $timezones_fixed[$_POST['timezone']] : $_POST['timezone'];
        if(isValidTimezoneId($_POST['timezone'])){
             $_SESSION['timezone'] = $_POST['timezone'];
            date_default_timezone_set($_SESSION['timezone']);
        }else{
            date_default_timezone_set('Africa/Lagos');
        }
       
    }else{
        date_default_timezone_set('Africa/Lagos');
    }

    if(isset($_POST['platform'])){
        $_SESSION['platform'] = $_POST['platform'];
    }

    $phone_num_zero_prefix = substr($phone_formatted,0,1) == "0" ? $phone_formatted : "0" . $phone_formatted;
    $phone_num_no_zero_prefix = substr($phone_formatted,0,1) == "0" ? substr($phone_formatted,1) : $phone_formatted;

    $query_str = "( " . DB_TBL_PREFIX . "tbl_users.phone = \"{$phone_num_zero_prefix}\" OR " . DB_TBL_PREFIX . "tbl_users.phone = \"{$phone_num_no_zero_prefix}\") AND " . DB_TBL_PREFIX . "tbl_users.pwd_raw = \"{$password}\" AND " . DB_TBL_PREFIX . "tbl_users.country_dial_code = \"+{$country_dial_code}\" AND " . DB_TBL_PREFIX . "tbl_users.account_deleted = 0";

    
    

    //verify otp and login user with valid otp
    if(!empty($otp_code)){
        if(SMS_OTP_SERVICE == 'firebase'){

            if(!empty($fb_user_details) && isset($fb_user_details['uid'])){
                //verify otp with firebase
                $verify_res = verifyFirebaseUser($fb_user_details);
                if(isset($verify_res['error'])){
                    $res = array("error"=>__("An error has occured"));
                    echo json_encode($res); //database error
                    exit;
                }

                $user_phone_number = $verify_res['data']->phoneNumber;

                $user_phone_number_val = validatePhoneNumber($user_phone_number);

                if($user_phone_number_val['phone_num_nat'] != $phone_formatted){
                    $error = array("error"=>__("Invalid OTP code"));
                    echo json_encode($error); //database error
                    exit;
                }

                $query_str = "( " . DB_TBL_PREFIX . "tbl_users.phone = \"{$phone_num_zero_prefix}\" OR " . DB_TBL_PREFIX . "tbl_users.phone = \"{$phone_num_no_zero_prefix}\") AND " . DB_TBL_PREFIX . "tbl_users.country_dial_code = \"+{$country_dial_code}\" AND " . DB_TBL_PREFIX . "tbl_users.account_deleted = 0";
                                

            }else{
                $error = array("error"=>__("Invalid OTP code"));
                echo json_encode($error); //database error
                exit;
            }

        }else{

            //verify otp with custom otp service
            $validation_res = validatePhoneNumber('+' . $country_dial_code . $phone);
            
            if(isset($validation_res['error'])){
                $res = array("error"=>__("Phone number is invalid"));
                echo json_encode($res); //invalid phone number
                exit;
            }

            if(isset($_SESSION[$validation_res['phone_num_int']]) && $_SESSION[$validation_res['phone_num_int']]['user_otp_code'] == $otp_code){
                
                $query_str = "( " . DB_TBL_PREFIX . "tbl_users.phone = \"{$phone_num_zero_prefix}\" OR " . DB_TBL_PREFIX . "tbl_users.phone = \"{$phone_num_no_zero_prefix}\") AND " . DB_TBL_PREFIX . "tbl_users.country_dial_code = \"+{$country_dial_code}\" AND " . DB_TBL_PREFIX . "tbl_users.account_deleted = 0";
                

            }else{
                $error = array("error"=>__("Invalid OTP code"));
                echo json_encode($error); //database error
                exit;
            }
        
        }
    }


    //Let's check our local DB for user record'
    $query = sprintf('SELECT referral_count,cancelled_rides,completed_rides,reward_points,route_id,country_code,country_dial_code,user_rating,photo_file,account_active,referal_code,push_notification_token,`address`,account_type,user_id,firstname, lastname,email,phone,is_activated,account_active,last_login_date,account_create_date,referal_code,wallet_amount FROM %stbl_users WHERE %s', DB_TBL_PREFIX, $query_str); //Get required user information from DB
    


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $user_account_details = mysqli_fetch_assoc($result);
            
            if($user_account_details['is_activated'] == 0){
                $_SESSION['not_activated_user']['uid'] = $user_account_details['user_id'];
                $data = array("success"=>"1","is_activated"=>$user_account_details['is_activated'],'loggedin'=>0);
                echo json_encode($data);
                exit;
            }
        }
        else{
            $error = array("error"=>__("Invalid account"));
            echo json_encode($error); //invalid record
            exit;

        }
        
    }
    else{ //No record matching the USER ID was found in DB. Show view to notify user

        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;
    }



    //get user documents
    $user_documents = [];
    $query = sprintf('SELECT *,%1$stbl_documents.id AS d_id FROM %1$stbl_documents
    LEFT JOIN %1$stbl_users_documents ON %1$stbl_users_documents.doc_id = %1$stbl_documents.id AND %1$stbl_users_documents.u_id = %2$d AND %1$stbl_users_documents.u_type = 0
    WHERE %1$stbl_documents.doc_user = 0 AND %1$stbl_documents.status = 1', DB_TBL_PREFIX, $user_account_details['user_id']);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                if(!empty($row['u_doc_img'])){
                    $row['u_doc_img'] = SITE_URL . "ajaxuserphotofile.php?file=" . $row['u_doc_img'];
                }
                $user_documents[$row['d_id']] = $row;
            }
        }
    }


    
    //get referral setting
    $ref_code_settings = [];
    $query = sprintf('SELECT * FROM %stbl_referral WHERE id = 1', DB_TBL_PREFIX); 


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $ref_code_settings = mysqli_fetch_assoc($result);
        }
                    
    }

    $referral_url = isset($_SESSION['platform']) && $_SESSION['platform'] == "android" ? str_replace("market://details?","https://play.google.com/store/apps/details?",CUSTOMER_APP_UPDATE_URL_ANDROID) : CUSTOMER_APP_UPDATE_URL_IOS;//SITE_URL;
    $refcode_copy = isset($ref_code_settings['status']) && $ref_code_settings['status'] == 1 && !empty($user_account_details['referal_code']) ? __("Hi, you can now book rides from your phone anywhere, anytime using Droptaxi Taxi service. Sign-up now with my referral code {---1} and get a discount on your first ride.",["{$user_account_details['referal_code']}"],"r|{$display_language}") : '';
    $ref_code_desc = __('Earn {---1}% discount on your next ride when you invite a friend to register on Droptaxi using your referral code', [round($ref_code_settings['discount_value'])],"r|{$display_language}");
    //$ref_code = isset($ref_code_settings['status']) && $ref_code_settings['status'] == 1 && !empty($user_account_details['referal_code']) ? "<input id='user-refcode-text' type='text' hidden='hidden' value='{$refcode_copy}'><span style='display:block;color:#42a5f5;font-family: Roboto,Noto,sans-serif;font-size:13px;font-weight:400;'>" . __("Referral code") . "</span><p style='margin-top:5px;padding: 15px 5px;border: thin dashed;'><b id='user-refcode'>{$user_account_details['referal_code']}</b><b style='color: blue;float: right;' onclick=share_message('',$('#user-refcode-text').val(),'{$referral_url}') >" . __("Share") . "</b></p><p>{$ref_code_desc}</p>" : "";
    $ref_code = isset($ref_code_settings['status']) && $ref_code_settings['status'] == 1 && !empty($user_account_details['referal_code']) ? $user_account_details['referal_code'] : "";
    

    
    

    
    //$photo = explode('/',$user_account_details['photo_file']);
    $photo_file = isset($user_account_details['photo_file']) ? $user_account_details['photo_file'] : "0";
    
    $_SESSION['firstname'] = $user_account_details['firstname'];
    $_SESSION['lastname'] = $user_account_details['lastname'];
    $_SESSION['uid'] = $user_account_details['user_id'];
    $_SESSION['email'] = $user_account_details['email'];
    $_SESSION['route_id'] = $user_account_details['route_id'];
    $_SESSION['phone'] = $user_account_details['phone'];
    $_SESSION['country_dial_code'] = $user_account_details['country_dial_code'];
    $_SESSION['address'] = $user_account_details['address'];
    $_SESSION['referal_code'] = $user_account_details['referal_code'];
    $_SESSION['account_type'] = $user_account_details['account_type'];
    $_SESSION['lastseen'] = $user_account_details['last_login_date'];
    $_SESSION['joined'] = $user_account_details['account_create_date'];
    $_SESSION['loggedin'] = 1;
    $_SESSION['reward_points'] = $user_account_details['reward_points'];
    $_SESSION['is_activated'] = $user_account_details['is_activated'];
    $_SESSION['wallet_amt'] = $user_account_details['wallet_amount'];
    $_SESSION['push_token'] = $user_account_details['push_notification_token'];
    $_SESSION['photo'] = SITE_URL . "ajaxuserphotofile.php?file=" . $photo_file;
    $_SESSION['user_rating'] = $user_account_details['user_rating'];

    $redis = connectRedis();

    if($redis){
        $redis->set("ridrsdlkey:{$_SESSION['uid']}", $sdl_key);
    }

    //profile information
    
    $profiledata = array(
        'success' => 1,
        'firstname'=> $_SESSION['firstname'],
        'lastname'=> $_SESSION['lastname'],
        'email'=> $_SESSION['email'],
        'phone'=> $_SESSION['phone'],
        'route_id'=> $_SESSION['route_id'],
        'address'=> $_SESSION['address'],
        'userid' => $_SESSION['uid'],
        'ref_code'=>$ref_code,
        'ref_code_copy_msg' => $refcode_copy,
        'ref_desc' => $ref_code_desc,
        'ref_url' => $referral_url,
        'photo'=>$_SESSION['photo'],
        'referral_count'=>$user_account_details['referral_count'],
        'cancelled_rides'=>$user_account_details['cancelled_rides'],
        'completed_rides'=>$user_account_details['completed_rides'],
        'user_rating'=>$user_account_details['user_rating'],
        'country_code' => $user_account_details['country_code'],
        'country_dial_code' => $user_account_details['country_dial_code'],
        'user_docs' => $user_documents

    );

    $ongoing_booking = [];
    //check if user has an ongoing booking
    $query = sprintf('SELECT *,%1$stbl_bookings.id AS booking_id FROM %1$stbl_bookings 
    INNER JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id
    INNER JOIN %1$stbl_driver_location ON %1$stbl_driver_location.driver_id = %1$stbl_bookings.driver_id
    WHERE %1$stbl_bookings.user_id = %2$d AND %1$stbl_bookings.driver_id != 0 AND (%1$stbl_bookings.status = 0 OR %1$stbl_bookings.status = 1) ORDER BY %1$stbl_bookings.id DESC LIMIT 1', DB_TBL_PREFIX, $_SESSION['uid']);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $row = mysqli_fetch_assoc($result);
            $action = '';
            if(!empty($row['date_arrived']) && $row['status'] == 0){
                $action = "driver-arrived";
            }elseif(empty($row['date_arrived']) && $row['status'] == 0 ){
                $action = "driver-assigned";
            }else{
                $action = "customer-onride";
            }
            $driver_photo_file = isset($row['photo_file']) ? $row['photo_file'] : "0";
            $ongoing_booking = [
                "action"=>$action,
                "route_id" => $row['route_id'],
                "booking_id" => $row['booking_id'],
                "driver_id" => $row['driver_id'],
                "driver_firstname" => $row['firstname'],
                "driver_phone" => $row['phone'],
                "driver_platenum" => $row['car_plate_num'],
                "driver_rating" => $row['driver_rating'],
                "driver_location_lat" => $row['lat'],
                "driver_location_long" => $row['long'],
                "pickup_lat"=>$row['pickup_lat'],
                "pickup_long"=>$row['pickup_long'],
                "dropoff_lat"=>$row['dropoff_lat'],
                "dropoff_long"=>$row['dropoff_long'],
                "driver_carmodel" => $row['car_model'],
                "driver_carid" => $row['ride_id'],
                "driver_completed_rides" => $row['completed_rides'],
                "completion_code"=>$row['completion_code'],
                "driver_photo" => SITE_URL . "ajaxphotofile.php?file=" . $driver_photo_file,
                "waypoint1_address" => $row['waypoint1_address'],
                "waypoint1_long" => $row['waypoint1_long'],
                "waypoint1_lat" => $row['waypoint1_lat'],
                "waypoint2_address" => $row['waypoint2_address'],
                "waypoint2_long" => $row['waypoint2_long'],
                "waypoint2_lat" => $row['waypoint2_lat'],
                "date_arrived_stop_1" => !empty($row['date_arrived_stop_1']) ? strtotime($row['date_arrived_stop_1'] . " UTC") : "",
                "date_arrived_stop_2" => !empty($row['date_arrived_stop_2']) ? strtotime($row['date_arrived_stop_2'] . " UTC") : "",
                "date_left_stop_1" => !empty($row['date_left_stop_1']) ? strtotime($row['date_left_stop_1'] . " UTC") : "",
                "date_left_stop_2" => !empty($row['date_left_stop_2']) ? strtotime($row['date_left_stop_2'] . " UTC") : "",
                "service_mode"=>$row['service_mode'],
                "hourly_rate_hours"=>$row['hr_set_num_h']
            ];
        }
    }



    $recent_bookings_loc = [];
    //get 4 recently completed bookings dropoff locations
    if(!empty($_SESSION['route_id'])){
        $query = sprintf('SELECT %1$stbl_bookings.dropoff_address, %1$stbl_bookings.dropoff_long, %1$stbl_bookings.dropoff_lat FROM %1$stbl_bookings
        WHERE %1$stbl_bookings.user_id = %2$d AND %1$stbl_bookings.route_id = %3$d AND %1$stbl_bookings.status = 3 ORDER BY %1$stbl_bookings.id DESC LIMIT 15', DB_TBL_PREFIX, $_SESSION['uid'],$_SESSION['route_id']);

        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                $unique_address = [];
                $count = 0;
                while($row = mysqli_fetch_assoc($result)){
                    if(empty($row['dropoff_long']) || empty($row['dropoff_lat']))continue;
                    $location_coords = $row['dropoff_long'] . $row['dropoff_lat'];
                    if(!in_array($location_coords,$unique_address)){
                        $count++;
                        $unique_address[] = $location_coords;
                        $recent_bookings_loc[] = $row;
                        if($count == 3)break;
                    }                    

                }
            }
        }
    }

    $recent_loc_data = [
        "route_id"=>$_SESSION['route_id'],
        "locations"=>$recent_bookings_loc
    ];
    
    //get default currency data
    $default_currency_data = [];
    $query = sprintf('SELECT * FROM %stbl_currencies WHERE `default` = 1', DB_TBL_PREFIX);
    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $default_currency_data = mysqli_fetch_assoc($result);
        }
    }


    //get tariff data
    $tariff_data = getroutetariffs();


    
    //Get online payment gateway data

    $online_payment_data = array(

        'merchantid'=> P_G_PK,
        'storeid'=> STORE_ID,
        'devid'=> DEV_ID,
        'notifyurl'=> NOTIFY_URL
        
    );

    if(is_array(DEFAULT_PAYMENT_GATEWAY) && !empty(DEFAULT_PAYMENT_GATEWAY)){
        $default_pgs = DEFAULT_PAYMENT_GATEWAY;
        $payment_gateways = [];
        foreach($default_pgs as $key=>$value){
            $payment_gateways[] = $key;
        }
    }else{
        $payment_gateways = DEFAULT_PAYMENT_GATEWAY;
    }

    $app_settings = array(
        'payment_type' => PAYMENT_TYPE,
        'ride_otp' => RIDE_OTP,
        'default_payment_gateway'=> $payment_gateways,
        'wallet_topup_presets' => WALLET_TOPUP_PRESETS,
        'driver_tip_presets' => DRIVER_TIP_PRESETS,
        'vehicle_sel_disp_type' => VEHICLE_SEL_DISPLAY_TYPE,
        'round_trip_fares' => ROUND_TRIP_FARES,
        'trip_cancel_reasons' => RIDER_CANCEL_REASON,
        "allowed_countries_list" => ALLOWED_COUNTRIES_LIST,
        "force_update_customer_android" => FORCE_UPDATE_CUSTOMER_ANDROID,
        "force_update_customer_ios" => FORCE_UPDATE_CUSTOMER_IOS              
    );

    $firebase_rtdb_conf = array(
        'databaseURL' => FB_RTDB_URL,
        'apiKey' => FB_WEB_API_KEY,
        'storageBucket' => FB_STORAGE_BCKT
    );

    

    //update users last seen time and user selected app language
    $query = sprintf('UPDATE %stbl_users SET last_login_date = "%s", disp_lang = "%s", login_count = login_count + 1 WHERE user_id = %d', DB_TBL_PREFIX,gmdate('Y-m-d H:i:s', time()),$display_language,$_SESSION['uid']);
    $result = mysqli_query($GLOBALS['DB'], $query);

    $_SESSION['lang'] = $display_language;
    
    session_regenerate_id();
    
    //return data

    $data = array("loggedin"=>1,"ongoing_bk"=>$ongoing_booking,"fb_conf"=>$firebase_rtdb_conf,"recent_locs"=>$recent_loc_data,"is_activated"=>$user_account_details['is_activated'],"account_active"=>$user_account_details['account_active'],'wallet_amt' => $_SESSION['wallet_amt'],'reward_points'=>$_SESSION['reward_points'],'cc_num'=>CALL_CENTER_NUMBER,'profileinfo' => $profiledata,'tariff_data'=>$tariff_data,'profileinfo' => $profiledata,'online_pay'=>$online_payment_data,'app_version_ios'=>APP_VERSION_CUSTOMER_IOS,'app_version_android'=>APP_VERSION_CUSTOMER_ANDROID,'customer_app_update_url_ios'=>CUSTOMER_APP_UPDATE_URL_IOS,'customer_app_update_url_android'=>CUSTOMER_APP_UPDATE_URL_ANDROID,'scheduled_ride_enabled' => SCHEDULED_RIDE_ENABLED,'default_currency'=>$default_currency_data,'app_settings'=>$app_settings,'sess_id' => base64_encode(session_id()),'bannerdata'=>getbannerdata());
    echo json_encode($data);
    exit;           
    
  


}


function userLogout(){

    $_SESSION = array();  //clear session data
    session_destroy();
    $data = array("loggedout"=>1);
    echo json_encode($data);
    exit;

}



function del_user_acc(){
    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); //invalid record
        exit; 
    }

    //verify the account
    $password = !empty($_POST['pwd']) ? mysqli_real_escape_string($GLOBALS['DB'], trim($_POST['pwd'])) : "";

    $query = sprintf('SELECT * FROM %stbl_users WHERE `user_id` = %d AND pwd_raw = "%s"', DB_TBL_PREFIX,$_SESSION['uid'],$password);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(!mysqli_num_rows($result)){
            $error = array("error"=>__("Invalid account"));
            echo json_encode($error); //invalid record
            exit;  
        }
    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //invalid record
        exit; 
    }

    //check if user has a pending ride or ride in progress
    $query = sprintf('SELECT * FROM %stbl_bookings WHERE `user_id` = %d AND (`status` = 0 OR `status` = 1 OR `status` = 6)', DB_TBL_PREFIX, $_SESSION['uid']);
    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); //invalid record
            exit;
        }
    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //invalid record
        exit;
    }


    
    //delete user's account data
    $query = sprintf('UPDATE %stbl_users SET account_deleted = 1 WHERE `user_id` = %d AND pwd_raw = "%s"', DB_TBL_PREFIX, $_SESSION['uid'], $password);
    $result = mysqli_query($GLOBALS['DB'], $query);

    //delete all notifications
    /* $query = sprintf('DELETE FROM %stbl_notifications WHERE `person_id` = %d AND user_type = %d', DB_TBL_PREFIX, $_SESSION['uid'], 0);
    $result = mysqli_query($GLOBALS['DB'], $query); */


    

    $_SESSION = array();  //clear session data
    session_destroy();
    $data = array("success"=>1);
    echo json_encode($data);
    exit;


}


function rateride(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); //invalid record
        exit; 
    }

    $booking_id = (int) $_GET['bookingid'];
    $rating = (int) $_GET['rating'];
    $driver_tip = (float) $_GET['driver_tip'];
    $msg = '';

    if($rating < 0 || $rating < 1){
        $rating = 1;
    }elseif($rating > 5){
        $rating = 5;
    } 


    $booking_data = [];

    //get details for this booking
    $query = sprintf('SELECT %1$stbl_drivers.disp_lang,%1$stbl_drivers.push_notification_token,%1$stbl_users.wallet_amount AS user_wallet_amount,%1$stbl_drivers.wallet_amount AS driver_wallet_amount,%1$stbl_currencies.exchng_rate,%1$stbl_currencies.symbol,%1$stbl_currencies.iso_code,%1$stbl_bookings.driver_id, %1$stbl_drivers.driver_rating FROM %1$stbl_bookings 
    INNER JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
    INNER JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_users.route_id
    INNER JOIN %1$stbl_currencies ON %1$stbl_currencies.id = %1$stbl_routes.city_currency_id
    INNER JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id
    WHERE %1$stbl_bookings.id = %2$d', DB_TBL_PREFIX, $booking_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $booking_data = mysqli_fetch_assoc($result);
        }else{
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); //invalid record
            exit; 
        }                    
    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //invalid record
        exit; 
    }


    //add user rating record to database
    $query = sprintf('INSERT INTO %stbl_ratings_users (booking_id,`user_id`,user_comment,user_rating) VALUES (%d,%d,"%s",%d)',
        DB_TBL_PREFIX,
        $booking_id,
        $_SESSION['uid'],
        mysqli_real_escape_string($GLOBALS['DB'], strip_tags($_GET['comment'])),
        $rating
    );

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ //An error has occured while trying to update user city
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

    //update driver rating with this users rating

    $driver_new_rating = floor(($booking_data['driver_rating'] + $rating) / 2);

    if($driver_new_rating > 5)$driver_new_rating = 5;

    $query = sprintf('UPDATE %stbl_drivers SET driver_rating = %d WHERE driver_id = %d',DB_TBL_PREFIX,$driver_new_rating,$booking_data['driver_id']);
    $result = mysqli_query($GLOBALS['DB'], $query);

    //process driver tips

    if(!empty($driver_tip)){
        //get rider wallet balance after deducting the tip.
        $driver_tip_converted = $driver_tip / $booking_data['exchng_rate']; //convert tip to default currency
        $rider_wallet_ballance = $booking_data['user_wallet_amount'] - $driver_tip_converted;
        if(!($rider_wallet_ballance < 0)){
            //rider has sufficient amount in wallet for the tip
            
            $query = sprintf('UPDATE %stbl_users SET wallet_amount = wallet_amount - %f WHERE user_id = %d',DB_TBL_PREFIX,$driver_tip_converted,$_SESSION['uid']);
            $result = mysqli_query($GLOBALS['DB'], $query);
            
            $query = sprintf('UPDATE %stbl_drivers SET wallet_amount = wallet_amount + %f WHERE driver_id = %d',DB_TBL_PREFIX,$driver_tip_converted,$booking_data['driver_id']);
            $result = mysqli_query($GLOBALS['DB'], $query);

            

            //add to riders wallet transaction
            $transaction_msg = __('Debit for the reward you sent to your driver');
            $transaction_id = crypto_string();
            $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
            '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
            DB_TBL_PREFIX,
            $booking_data['symbol'],
            $booking_data['exchng_rate'],
            $booking_data['iso_code'],
            $booking_id,
            $transaction_id,
            $driver_tip,
            $rider_wallet_ballance,
            $_SESSION['uid'],
            0, //0 = customer / rider
            mysqli_real_escape_string($GLOBALS['DB'],$transaction_msg), 
            3,
            gmdate('Y-m-d H:i:s', time())

            );

            $result = mysqli_query($GLOBALS['DB'],$query);



            //add to driver's wallet transaction
            $transaction_msg = __('A reward from your passenger',[],"r|{$booking_data['disp_lang']}");
            $transaction_id = crypto_string();
            $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
            '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
            DB_TBL_PREFIX,
            $booking_data['symbol'],
            $booking_data['exchng_rate'],
            $booking_data['iso_code'],
            $booking_id,
            $transaction_id,
            $driver_tip,
            $driver_tip_converted + $booking_data['driver_wallet_amount'],
            $booking_data['driver_id'],
            1, //1 = driver
            mysqli_real_escape_string($GLOBALS['DB'],$transaction_msg), 
            2, //credit
            gmdate('Y-m-d H:i:s', time())

            );

            $result = mysqli_query($GLOBALS['DB'],$query);

            //send driver a push notification
            $title = WEBSITE_NAME . " - " . __('A reward from your passenger',[],"r|{$booking_data['disp_lang']}");
            $body = __("You have been rewarded {---1} by your passenger",["{$booking_data['symbol']}{$driver_tip}"],"r|{$booking_data['disp_lang']}");
            $device_tokens = !empty($booking_data['push_notification_token']) ? $booking_data['push_notification_token'] : 0;
            if(!empty($device_tokens)){
                sendPushNotification($title,$body,$device_tokens,NULL,1);
            }

            $msg = __('Your driver reward has been sent. Thank you');


        }else{
            //rider does not have enough money in wallet. notify rider
           $msg = __('You do not have enough money in your wallet to reward your driver. Please add money to your wallet');
        }
    }

    $data = array("success"=>1, 'message' => $msg);
    echo json_encode($data);
    exit;


}


function updateusercity(){
    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); //invalid record
        exit; 
    }

    $route_id = (int) $_GET['route_id'];

    if(!$route_id){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;  
    }
    
    //update user city on db
    $query = sprintf('UPDATE %stbl_users SET `route_id` = "%d" WHERE user_id = "%d"', DB_TBL_PREFIX,$route_id,$_SESSION['uid']);
    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ //An error has occured while trying to update user city
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

    $_SESSION['route_id'] = $route_id; //update user session data

    $recent_bookings_loc = [];
    //get 4 recently completed bookings dropoff locations
    
    $query = sprintf('SELECT %1$stbl_bookings.dropoff_address, %1$stbl_bookings.dropoff_long, %1$stbl_bookings.dropoff_lat FROM %1$stbl_bookings
    WHERE %1$stbl_bookings.user_id = %2$d AND %1$stbl_bookings.route_id = %3$d AND %1$stbl_bookings.status = 3 ORDER BY %1$stbl_bookings.id DESC LIMIT 15', DB_TBL_PREFIX, $_SESSION['uid'],$_SESSION['route_id']);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $unique_address = [];
            $count = 0;
            while($row = mysqli_fetch_assoc($result)){
                $location_coords = $row['dropoff_long'] . $row['dropoff_lat'];
                if(!in_array($location_coords,$unique_address)){
                    $count++;
                    $unique_address[] = $location_coords;
                    $recent_bookings_loc[] = $row;
                    if($count == 3)break;
                }                    

            }
        }
    }
    
    $recent_loc_data = [
        "route_id"=>$route_id,
        "locations"=>$recent_bookings_loc
    ];

    $data = array("success"=>1,"recent_locs"=>$recent_loc_data);
    echo json_encode($data);
    exit;

}



function getuserinfopages(){
       

    $user_info_pages = [];
    $query = sprintf('SELECT * FROM %stbl_appinfo_pages WHERE id = 1 OR id = 3 OR id = 5', DB_TBL_PREFIX); //Get required user information from DB


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                $user_info_pages[$row['id']] = $row;
            }
        }else{
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); //invalid record
            exit; 
        }                    
    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //invalid record
        exit; 
    }

    
    $data = array("success"=>1,'about'=>$user_info_pages[1]['content'],'terms'=>$user_info_pages[3]['content'],'drivewith'=>$user_info_pages[5]['content']);
    echo json_encode($data);
    exit;


}



function gethelpcontent(){
    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); //invalid record
        exit; 
    }
    
    $id = !empty($_GET['id']) ? (int) $_GET['id'] : 0;

    $help_item_content = [];
    $query = sprintf('SELECT title,content FROM %stbl_appinfo_pages WHERE id = %d AND type = 1', DB_TBL_PREFIX,$id); //Get required user information from DB


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $help_item_content = mysqli_fetch_assoc($result);
        }else{
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); //invalid record
            exit; 
        }                    
    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //invalid record
        exit; 
    }

    
    $data = array("success"=>1,'help_content'=>$help_item_content['content'],'help_title'=>$help_item_content['title']);
    echo json_encode($data);
    exit;


}




function gethelpdata(){
    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); //invalid record
        exit; 
    }
    

    $help_content_data = [];
    $help_categories_data = [];


    //get all help categories displayable on rider app
    $query = sprintf('SELECT * FROM %1$stbl_help_cat WHERE %1$stbl_help_cat.show_rider = 1', DB_TBL_PREFIX); //Get required user information from DB

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                $help_categories_data[$row['id']] = $row; //sort data array into categories id indexes
            }
        }else{
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); //invalid record
            exit; 
        }                    
    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //invalid record
        exit; 
    }


    //get all help contents displayable on rider app
    $help_topics_strings = [];
    $query = sprintf('SELECT id,cat_id,title,excerpt FROM %1$stbl_appinfo_pages WHERE %1$stbl_appinfo_pages.show_rider = 1 AND %1$stbl_appinfo_pages.type = 1', DB_TBL_PREFIX); //Get required user information from DB

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                $help_content_data[$row['cat_id']][] = $row;
                $help_topics_strings[$row['cat_id']] = '';
            }
        }else{
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); //invalid record
            exit; 
        }                    
    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //invalid record
        exit; 
    }

    $help_categories_string = '';
    $help_uncategorized_topics_string = '';
    
    //format categories list for display on App
    foreach($help_categories_data as $helpcategoriesdata){
        if($helpcategoriesdata['id'] == 1)continue; //skip the uncategorized category item

            $help_categories_string .= "<ons-list-item onclick='showhelpcattopics({$helpcategoriesdata['id']})' modifier='longdivider'>
                    
                                                <div class='center'>
                                                    <div style='width:100%;'><span class='list-item__title' id='cat-title-{$helpcategoriesdata['id']}'>{$helpcategoriesdata['title']}</div>
                                                    <span class='list-item__subtitle'>{$helpcategoriesdata['desc']}</span>
                                                    
                                                </div>

                                                <div class='right'>
                                                    <ons-icon icon='fa-chevron-right' size='14px' style='color:#42a5f5;'></ons-icon>                                                    
                                                </div>
                                                
                                            
                                            </ons-list-item>";

        
            
    }



    //format categories help topics list for display on App
    foreach($help_content_data as $key => $helpcontentdata){
        
        if($key == 1){ //get all topics under the uncategorized category
            foreach($help_content_data[$key] as $helpcontenttopics){
                $help_uncategorized_topics_string .= "<ons-list-item onclick='showhelptopic({$helpcontenttopics['id']})' modifier='longdivider'>
                    
                                                        <div class='center'>
                                                            <div style='width:100%;'><span class='list-item__title' id='topic-title-{$helpcontenttopics['id']}'>{$helpcontenttopics['title']}</div>
                                                            <span class='list-item__subtitle'>{$helpcontenttopics['excerpt']}</span>
                                                            
                                                        </div>
                                                        
                                                    
                                                    </ons-list-item>";
            }
              
        }else{
            foreach($help_content_data[$key] as $helpcontenttopics){
                $help_topics_strings[$key] .= "<ons-list-item onclick='showhelptopic({$helpcontenttopics['id']})' modifier='longdivider'>
                        
                                                    <div class='center'>
                                                        <div style='width:100%;'><span class='list-item__title' id='topic-title-{$helpcontenttopics['id']}'>{$helpcontenttopics['title']}</div>
                                                        <span class='list-item__subtitle'>{$helpcontenttopics['excerpt']}</span>
                                                        
                                                    </div>
                                                    
                                                
                                                </ons-list-item>";
            }

        }
            

        
            
    }


    $help_categories_string = $help_uncategorized_topics_string . $help_categories_string;


    
    $data = array("success"=>1,'help_cat'=>$help_categories_string,'help_cat_topics'=>$help_topics_strings);
    echo json_encode($data);
    exit;


}


function getplacesautocomplete(){

    
    if(empty($_SESSION['loggedin'])){
        exit; 
    }
    

    $place_hint = $_GET['place_hint'];
    if(!empty($place_hint)){
        if(strlen($place_hint) < 2){ //perform places auto complete query when user has typed more than two characters
            exit;
        }
    }else{
        exit;
    }


    $city_radius = !empty($_GET['city_radius']) ? $_GET['city_radius'] : "2000";

    $bounding_box = getBoundingBoxForCircle(floatval($_GET['location_lat']),floatval($_GET['location_lng']),intval($city_radius));    
    

    /**--------------------------------------------------- */

    /* $data_arr = [
        
        'input' => $place_hint,
        'origin' => [   'latitude' => $_GET['location_lat'],
                        'longitude' => $_GET['location_lng']
                    ],
        'locationRestriction' => [
                            'circle' => [
                                            'center' => [   'latitude' => $_GET['location_lat'],
                                                            'longitude' => $_GET['location_lng']
                                                        ],
                                            'radius' => $city_radius
                                        ]
                            ]
    ]; */

    
    $data_arr = [
        
        'input' => $place_hint,
        'origin' => [   'latitude' => $_GET['location_lat'],
                        'longitude' => $_GET['location_lng']
                    ],
        'locationRestriction' => [
                            'rectangle' => [
                                            'low' => [   'latitude' => $bounding_box['south'],
                                                            'longitude' => $bounding_box['west']
                                                        ],
                                            'high' => [   'latitude' => $bounding_box['north'],
                                                            'longitude' => $bounding_box['east']
                                                        ]
                                        ]
                            ]
    ];


    $data_json = json_encode($data_arr, JSON_UNESCAPED_UNICODE);

    $curl = curl_init("https://places.googleapis.com/v1/places:autocomplete");
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json' ,
    'X-Goog-Api-Key: ' . GMAP_API_KEY_BACKEND, 'X-Goog-FieldMask:*' ));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
    $json_response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    $response = json_decode($json_response, true);
    if(json_last_error()){
        exit;
    }

    
    $data = array("success"=>1,"places"=>$response);
    echo json_encode($data);
    exit;


}



function getBoundingBoxForCircle($centerLat, $centerLng, $radiusMeters) {

    $earthRadius = 6378137; // Earth's radius in meters

    // Convert radius to degrees
    $latOffset = rad2deg($radiusMeters / $earthRadius);
    $lngOffset = rad2deg($radiusMeters / ($earthRadius * cos(deg2rad($centerLat))));

    // Bounding box
    $north = $centerLat + $latOffset;
    $south = $centerLat - $latOffset;
    $east  = $centerLng + $lngOffset;
    $west  = $centerLng - $lngOffset;

    if($north < -90.0)$north = -90.0;
    if($north > 90.0)$north = 90.0;

    if($south < -90.0)$south = -90.0;
    if($south > 90.0)$south = 90.0;

    if($east < -180.0)$east = -180.0;
    if($east > 180.0)$east = 180.0;

    if($west < -180.0)$west = -180.0;
    if($west > 180.0)$west = 180.0;

    return [
        'north' => $north,
        'south' => $south,
        'east' => $east,
        'west' => $west
    ];

}





function geocodeplace(){


    if(empty($_SESSION['loggedin'])){
        $data = array("error"=>__("Please login to continue"));
        echo json_encode($data);
        exit;
    }

    $place_id = "";
    $response2 = NULL;
    $tariff_data = [];
    
    
    

    $get_directions = (int) $_GET['get_direction'];
    $point_lat = !empty($_GET['point_lat']) ? $_GET['point_lat'] : NULL;
    $point_long = !empty($_GET['point_long']) ? $_GET['point_long'] : NULL;
    $location_type = isset($_GET['location_type']) && $_GET['location_type'] == 1 ? 1 : 0;
    $mode = isset($_GET['mode']) ? (int) $_GET['mode'] : 0;

    if(empty($mode)){ //use place ID
        if(!empty($_GET['place_id'])){
            $place_id = $_GET['place_id'];
            $url = "https://maps.googleapis.com/maps/api/geocode/json?place_id={$place_id}&key=" . GMAP_API_KEY_BACKEND;
        }else{
            $data = array("error"=>__("Your Selected location is invalid. Please select another location"));
            echo json_encode($data);
            exit;
        }
    }else{ //use latlng
        $get_directions = 0;        
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$point_lat},{$point_long}&key=" . GMAP_API_KEY_BACKEND;

    }
    

    

    //Get geocode data from google
    
    
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPGET, true);
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
    //curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
    $json_response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    $response = json_decode($json_response, true);
    if(json_last_error()){
        $data = array("error"=>__("An error has occured"));
        echo json_encode($data);
        exit;
    }

    
    if(!empty($get_directions) && !empty($point_lat) && !empty($point_long) && $response['status'] == 'OK'){

        if($location_type){
            $p_lat = $point_lat;
            $p_lng = $point_long;
            $d_lat = $response['results'][0]['geometry']['location']['lat'];
            $d_lng = $response['results'][0]['geometry']['location']['lng'];

        }else{
            $p_lat = $response['results'][0]['geometry']['location']['lat'];
            $p_lng = $response['results'][0]['geometry']['location']['lng'];
            $d_lat = $point_lat;
            $d_lng = $point_long;
        }

        

        $response2 = emulateDirectionServiceNew($p_lat,$p_lng,$d_lat,$d_lng,0);


        


    }

    

    $data = array("success"=>1,"place_details"=>$response,'directions'=>$response2);
    echo json_encode($data);
    exit;   




}





function couponCheck(){

    if(empty($_SESSION['loggedin'])){
        $data = array("error"=>__("Please login to continue"));
        echo json_encode($data);
        exit;
    }

    if(empty($_GET['coupon_code'])){
        $data = array("error"=>__("Coupon code is invalid"));
        echo json_encode($data);
        exit;
    }
    

    $msg = '';
    $query = sprintf('SELECT %1$stbl_coupon_codes.*,%1$stbl_coupon_codes.id AS coupon_id, %1$stbl_currencies.symbol FROM %1$stbl_coupon_codes 
    INNER JOIN %1$stbl_users ON %1$stbl_users.route_id = %1$stbl_coupon_codes.city
    INNER JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_coupon_codes.city
    INNER JOIN %1$stbl_currencies ON %1$stbl_currencies.id = %1$stbl_routes.city_currency_id
    WHERE %1$stbl_coupon_codes.coupon_code = "%2$s" AND %1$stbl_users.user_id = %3$d', DB_TBL_PREFIX, mysqli_real_escape_string($GLOBALS['DB'],$_GET['coupon_code']), $_SESSION['uid']);

    if($result = mysqli_query($GLOBALS['DB'],$query)){
        if(mysqli_num_rows($result)){
            $row = mysqli_fetch_assoc($result);
            $coupon_start_date = strtotime($row['active_date']);
            $coupon_end_date = strtotime($row['expiry_date']);

            if($row['status']){
                if(time() > $coupon_start_date && time() < $coupon_end_date){

                    //get all vehicles this coupon is valid for
                    $coupon_vehicles = [];
                    $coupon_vehicles_str = '';
                    $vehicles_msg = "<br>".__("Coupon is valid for all vehicle types")."<br>";
                    if(!empty($row['vehicles'])){
                        $query = sprintf('SELECT id, ride_type FROM %stbl_rides WHERE id IN(%s)', DB_TBL_PREFIX, $row['vehicles']);
                        if($result = mysqli_query($GLOBALS['DB'], $query)){
                            if(mysqli_num_rows($result)){
                                $vehicles_msg = "<br>".__("Coupon is valid for vehicle type")."<br>";
                                while($res = mysqli_fetch_assoc($result)){
                                    $coupon_vehicles[] = $res;
                                    $vehicles_msg .= "-" . $res['ride_type'] . "<br>";
                                }  
                                
                                
                            }else{
                                $data = array("error"=>__("An error has occured"));
                                echo json_encode($data);
                                exit;  
                            }
                        }else{
                            $data = array("error"=>__("An error has occured"));
                            echo json_encode($data);
                            exit;
                        }
                    }
                    //check usage limits
                    $query = sprintf('SELECT SUM(%1$stbl_coupons_used.times_used) AS all_usage, SUM(IF(%1$stbl_coupons_used.user_id = %2$d,%1$stbl_coupons_used.times_used,NULL)) AS user_usage FROM %1$stbl_coupons_used WHERE coupon_id = %3$d', DB_TBL_PREFIX, $_SESSION['uid'],$row['coupon_id']);
                    if($result = mysqli_query($GLOBALS['DB'],$query)){
                        if(mysqli_num_rows($result)){
                            $usage_data = mysqli_fetch_assoc($result);
                            if($usage_data['all_usage'] >= $row['limit_count'] || $usage_data['user_usage'] >= $row['user_limit_count']){
                                $data = array("error"=>__("Usage limit of this coupon has been exceeded"));
                                echo json_encode($data);
                                exit;
                            }
                        }else{
                            $data = array("error"=>__("An error has occured"));
                            echo json_encode($data);
                            exit;
                        }
                    }
                    if($row['discount_type']){
                        $msg = __("This coupon qualifies you for a flat fee of {---1}",["{$row['symbol']}{$row['discount_value']}"]) . $vehicles_msg;
                    }else{
                        $msg = __("This coupon qualifies you for a {---1} discount",["{$row['discount_value']}%"]) . $vehicles_msg;
                    }
                    $data = array("success"=> 1, "message" => $msg);
                    echo json_encode($data);
                    exit;
                }else{
                    $data = array("error"=>__("This coupon has expired"));
                    echo json_encode($data);
                    exit;
                }                    

            }else{
                $data = array("error"=>__("This coupon is no longer active"));
                echo json_encode($data);
                exit;
            }
                
            $data = array("success"=>"1", "message" => $msg);
            echo json_encode($data);
            exit;
        }else{
            $data = array("error"=>__("Invalid coupon code"));
            echo json_encode($data);
            exit;
        }
    }else{
        $data = array("error"=>__("An error has occured"));
        echo json_encode($data);
        exit;
    }

   
    
    
}




function promocodecheck(){

    if(empty($_SESSION['loggedin'])){
        $data = array("error"=>__("Please login to continue"));
        echo json_encode($data);
        exit;
    }

    if(empty($_GET['coupon_code'])){
        $data = array("error"=>__("Promo code is invalid"));
        echo json_encode($data);
        exit;
    }
    

    $msg = '';
    $title = '';
    $usage_limit_count = 0;
    $user_usage_count = 0;
    $days_left = 0;
    $city = '';

    $query = sprintf('SELECT %1$stbl_coupon_codes.*,%1$stbl_coupon_codes.id AS coupon_id, %1$stbl_currencies.symbol, %1$stbl_routes.r_title FROM %1$stbl_coupon_codes 
    INNER JOIN %1$stbl_users ON %1$stbl_users.route_id = %1$stbl_coupon_codes.city
    INNER JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_coupon_codes.city
    INNER JOIN %1$stbl_currencies ON %1$stbl_currencies.id = %1$stbl_routes.city_currency_id
    WHERE %1$stbl_coupon_codes.coupon_code = "%2$s" AND %1$stbl_users.user_id = %3$d', DB_TBL_PREFIX, mysqli_real_escape_string($GLOBALS['DB'],$_GET['coupon_code']), $_SESSION['uid']);

    if($result = mysqli_query($GLOBALS['DB'],$query)){
        if(mysqli_num_rows($result)){
            $row = mysqli_fetch_assoc($result);
            $coupon_start_date = strtotime($row['active_date']);
            $coupon_end_date = strtotime($row['expiry_date']);
            $usage_limit_count = $row['user_limit_count'];
            $title = $row['coupon_title'];
            $city = $row['r_title'];
            $city_id = $row['city'];
            $discount_type = $row['discount_type'];
            $discount_value = $row['discount_value'];
            $coupon_v = $row['vehicles'];
            $coupon_min_fare = (float) $row['min_fare'];
            $coupon_max_discount = (float) $row['max_discount_amount'];

            $seconds_to_expiry = $coupon_end_date - time();
            if($seconds_to_expiry < 0){
                $days_left = 0;
            }else{
                $days_left = ceil($seconds_to_expiry / 86400);
            }   
            

            if($row['status']){
                if(time() > $coupon_start_date && time() < $coupon_end_date){

                    //get all vehicles this coupon is valid for
                    $coupon_vehicles = [];
                    $coupon_vehicles_str = '';
                    //$vehicles_msg = "<br>".__("Promo is valid for all vehicle types")."<br>";
                    $vehicles_msg = "";
                    if(!empty($row['vehicles'])){
                        $query = sprintf('SELECT id, ride_type FROM %stbl_rides WHERE id IN(%s)', DB_TBL_PREFIX, $row['vehicles']);
                        if($result = mysqli_query($GLOBALS['DB'], $query)){
                            if(mysqli_num_rows($result)){
                                $vehicles_msg = "<br>".__("Promo is valid for vehicle type")."<br>";
                                while($res = mysqli_fetch_assoc($result)){
                                    $coupon_vehicles[] = $res;
                                    //$vehicles_msg .= "-" . $res['ride_type'] . "<br>";
                                    $vehicles_msg .= $res['ride_type'] . ",";
                                }  
                                
                                
                            }else{
                                $data = array("error"=>__("An error has occured"));
                                echo json_encode($data);
                                exit;  
                            }
                        }else{
                            $data = array("error"=>__("An error has occured"));
                            echo json_encode($data);
                            exit;
                        }
                    }
                    //check usage limits
                    $query = sprintf('SELECT COUNT(%1$stbl_coupons_used.user_id) AS coupon_users, SUM(%1$stbl_coupons_used.times_used) AS all_usage, SUM(IF(%1$stbl_coupons_used.user_id = %2$d,%1$stbl_coupons_used.times_used,NULL)) AS user_usage FROM %1$stbl_coupons_used WHERE coupon_id = %3$d', DB_TBL_PREFIX, $_SESSION['uid'],$row['coupon_id']);
                    if($result = mysqli_query($GLOBALS['DB'],$query)){
                        if(mysqli_num_rows($result)){
                            $usage_data = mysqli_fetch_assoc($result);
                            $user_usage_count = empty($usage_data['user_usage']) ? 0 : $usage_data['user_usage'];

                            if(!is_null($usage_data['user_usage'])){
                                //user previously added this token
                                if($usage_data['user_usage'] >= $row['user_limit_count']){
                                    $data = array("error"=>__("Usage limit of this promo code has been exceeded"));
                                    echo json_encode($data);
                                    exit;
                                }

                            }elseif($usage_data['all_usage'] >= $row['limit_count'] || (($usage_data['coupon_users'] * $row['user_limit_count']) + $row['user_limit_count']) > $row['limit_count']){
                                $data = array("error"=>__("Usage limit of this promo code has been exceeded"));
                                echo json_encode($data);
                                exit;
                            }

                            
                        }else{
                            $data = array("error"=>__("An error has occured"));
                            echo json_encode($data);
                            exit;
                        }
                    }
                    if($row['discount_type']){
                        if(!empty($coupon_min_fare)){
                            $msg = __("This promo qualifies you for a discount of {---1} for trip fares of {---2} and above",["{$row['symbol']}{$discount_value}","{$row['symbol']}{$coupon_min_fare}"]) . $vehicles_msg;
                        }else{
                            $msg = __("This promo qualifies you for a discount of {---1}",["{$row['symbol']}{$discount_value}"]) . $vehicles_msg;
                        }
                        
                    }else{
                        $discount_value_formatted = (int) $discount_value;
                        if(!empty($coupon_max_discount)){
                            $msg = __("This promo qualifies you for a discount of {---1} or {---2}",["{$discount_value_formatted}%","{$row['symbol']}{$coupon_max_discount}"]) . $vehicles_msg;
                        }else{
                            $msg = __("This promo qualifies you for a discount of {---1}",["{$discount_value_formatted}%"]) . $vehicles_msg;
                        }                        
                    }

                    //add this coupon as an entry for this user
                    if(is_null($usage_data['user_usage'])){
                        $query = sprintf('INSERT INTO %stbl_coupons_used (coupon_id,`user_id`,times_used) VALUES ("%s","%s","%s")', DB_TBL_PREFIX, $row['coupon_id'], $_SESSION['uid'], 0);
                        $result = mysqli_query($GLOBALS['DB'],$query);
                    }
                    
                    $data = array("success"=> 1, "message" => $msg,'usage_limit_count'=>$usage_limit_count,'user_usage_count'=> $user_usage_count,'days_left'=>$days_left,'coupon_title'=>$title,'city'=>$city,'discount_type'=>$discount_type,'discount_value'=>$discount_value,'coupon_vehicles'=>$coupon_v,'city_id'=>$city_id, 'min_fare'=> $coupon_min_fare,'max_discount'=>$coupon_max_discount);
                    echo json_encode($data);
                    exit;
                }else{
                    $data = array("error"=>__("This promo has expired"));
                    echo json_encode($data);
                    exit;
                }                    

            }else{
                $data = array("error"=>__("This promo is no longer active"));
                echo json_encode($data);
                exit;
            }
                
            $data = array("success"=>"1", "message" => $msg);
            echo json_encode($data);
            exit;
        }else{
            $data = array("error"=>__("Promo code is invalid"));
            echo json_encode($data);
            exit;
        }
    }else{
        $data = array("error"=>__("An error has occured"));
        echo json_encode($data);
        exit;
    }

   
    
    
}



function getdirections(){

    if(empty($_SESSION['loggedin'])){
        $data = array("error"=>__("Please login to continue"));
        echo json_encode($data);
        exit;
    }

    if(empty($_GET['p-lat']) || empty($_GET['p-lng']) || empty($_GET['d-lat']) || empty($_GET['d-lng'])){
        $data = array("error"=>__("An error has occured"));
        echo json_encode($data);
        exit;
    }
    $waypoints_str ='';
    $waypoint1 = '';
    $waypoint2 = '';
    $waypoint_arr = [];
    $waypoints_data = isset($_GET['waypoints']) ? $_GET['waypoints'] : null;
    if(isset($waypoints_data['dest-1']) && !empty($waypoints_data['dest-1']['address'])){
        $waypoint1 = "{$waypoints_data['dest-1']['lat']},{$waypoints_data['dest-1']['lng']}";
    }

    if(isset($waypoints_data['dest-2']) && !empty($waypoints_data['dest-2']['address'])){
        $waypoint2 = "{$waypoints_data['dest-2']['lat']},{$waypoints_data['dest-2']['lng']}";
    }

    if(!empty($waypoint1) && !empty($waypoint2)){
        //$waypoints_str = "&waypoints=optimize:true|{$waypoint1}|{$waypoint2}";
        $waypoints_str = "&waypoints=via:{$waypoint1}|{$waypoint2}";
        $waypoint_arr = [["lat" => $waypoints_data['dest-1']['lat'],"lng" => $waypoints_data['dest-1']['lng']],["lat" => $waypoints_data['dest-2']['lat'],"lng" => $waypoints_data['dest-2']['lng']]];
    }elseif(!empty($waypoint1)){
        //$waypoints_str = "&waypoints=optimize:true|{$waypoint1}";
        $waypoints_str = "&waypoints=via:{$waypoint1}";
        $waypoint_arr = [["lat" => $waypoints_data['dest-1']['lat'],"lng" => $waypoints_data['dest-1']['lng']]];
    }elseif(!empty($waypoint2)){
        //$waypoints_str = "&waypoints=optimize:true|{$waypoint2}";
        $waypoints_str = "&waypoints=via:{$waypoint2}";
        $waypoint_arr = [["lat" => $waypoints_data['dest-2']['lat'],"lng" => $waypoints_data['dest-2']['lng']]];
    }

    $tariff_data = [];  
    
    //Get directions data from google
        
    $response = emulateDirectionServiceNew($_GET['p-lat'],$_GET['p-lng'],$_GET['d-lat'],$_GET['d-lng'],0,$waypoint_arr);

    
    

    $data = array("success"=>1,"direction_details"=>$response);
    echo json_encode($data);
    exit;   




}




function userResendOTPCode(){

    if(SMS_OTP_SERVICE == 'test-otp'){
        $res = array("success"=>1);
        echo json_encode($res); 
        exit;
    }

    $phone_number = isset($_POST['phone']) ? mysqli_real_escape_string($GLOBALS['DB'], trim($_POST['phone'])) : '';

    if(empty($_SESSION[$phone_number])){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;
    }


    if(isset($_SESSION['otp_request']['num_requests'])){
        if($_SESSION['otp_request']['num_requests'] > 10){ //temporary ban user from sending OTP after this number of times
            if((time() - $_SESSION['otp_request']['time']) > 3600){ //unban after 1 hour
                $_SESSION['otp_request']['num_requests'] = 1;
                $_SESSION['otp_request']['time'] = time();
            }
            $res = array("error"=>__("Too many OTP messages sent. Try again later"));
            echo json_encode($res); //invalid phone number
            exit;
        }
        $_SESSION['otp_request']['num_requests'] += 1;
        $_SESSION['otp_request']['time'] = time();
        
    }else{
        $_SESSION['otp_request']['num_requests'] = 1;
        $_SESSION['otp_request']['time'] = time();
    }

    //generate OTP code (6-digit)
    $otp_code = crypto_string("123456789",6);

    //Check if phone number is the one earlier vaidated
    if(!isset($_SESSION[$phone_number])){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;
    }

    //save code in session
    $_SESSION[$phone_number]['user_otp_code'] = $otp_code;

    $sms_message = __('{---1} is your verification code',[$otp_code]);

    if(SMS_OTP_SERVICE == 'custom'){
        sendSMSMessage($phone_number,$sms_message,$otp_code);
    }

    $res = array("success"=>1);
    echo json_encode($res); 
    exit;



}



function userPhoneNumberValidate(){

    $phone_number = isset($_POST['phone']) ? mysqli_real_escape_string($GLOBALS['DB'], trim($_POST['phone'])) : '';
    $country_dial_code = isset($_POST['country_dial_code']) ? mysqli_real_escape_string($GLOBALS['DB'], $_POST['country_dial_code']) : '';
    $country_code = isset($_POST['country_code']) ? mysqli_real_escape_string($GLOBALS['DB'], $_POST['country_code']) : '';
    $password_verified = 0;
    
    if(empty($country_dial_code) || empty($phone_number) || strlen($phone_number) < 5 || strlen($phone_number) > 20 ){
        $res = array("error"=>__("Phone number is invalid"));
        echo json_encode($res); //invalid phone number
        exit; 
    }


    $user_country = codeToCountryName(strtoupper($country_code));

    if(!$user_country){        
        $error = array("error"=>__("Invalid country selected"));
        echo json_encode($error); 
        exit;
    }

    
    $validation_res = validatePhoneNumber('+' . $country_dial_code . $phone_number);

    if(isset($validation_res['error'])){
        $res = array("error"=>__("Phone number is invalid"));
        echo json_encode($res); //invalid phone number
        exit;
    }

    //phone number is in valid format

    $otp_send_limit_reached = false;

    if(isset($_SESSION['otp_request']['num_requests'])){
        if($_SESSION['otp_request']['num_requests'] > 5){ //temporary ban user from sending OTP after this number of times
            $otp_send_limit_reached = true;
            if((time() - $_SESSION['otp_request']['time']) > 3600){ //unban after 1 hour
                $_SESSION['otp_request']['num_requests'] = 1;
                $_SESSION['otp_request']['time'] = time();
                $otp_send_limit_reached = false;
            }            
        }else{
            $otp_send_limit_reached = false;
            $_SESSION['otp_request']['num_requests'] += 1;
            $_SESSION['otp_request']['time'] = time();
        }
        
        
    }else{
        $otp_send_limit_reached = false;
        $_SESSION['otp_request']['num_requests'] = 1;
        $_SESSION['otp_request']['time'] = time();
    }

    

    //generate OTP code (6-digit)
    $demo_otp = "0";

    if(SMS_OTP_SERVICE == 'test-otp'){
        $otp_code = "123456";
        $demo_otp = "123456";        
    }else{
        $otp_code = crypto_string("123456789",6);
    }

    //save code in session
    $_SESSION[$validation_res['phone_num_int']]['user_otp_code'] = $otp_code;
    $_SESSION[$validation_res['phone_num_int']]['user_country'] = $country_code;
    $_SESSION[$validation_res['phone_num_int']]['user_cc'] = $country_dial_code;
    $_SESSION[$validation_res['phone_num_int']]['user_phone_inp'] = $phone_number;
    $_SESSION[$validation_res['phone_num_int']]['valid_phone_local'] = $validation_res['phone_num_nat'];
    $_SESSION[$validation_res['phone_num_int']]['valid_phone_int'] = $validation_res['phone_num_int'];

    $whatsapp_validation_key = "";
    $whatsapp_auth_message = "";

    if(WHATSAPP_AUTH_STATUS == 1){

        $whatsapp_validation_key = crypto_string();

        $whatsapp_auth_message = urlencode(__("Hi {---1}, Please validate my phone number using this ID: {---2}",[WEBSITE_NAME,"##{$whatsapp_validation_key}{$_POST['lang']}##"],"r|{$_POST['lang']}"));

        $redis = connectRedis();
        $auth_data = [
                        "user_otp_code" => $otp_code,
                        "user_country" => $country_code,
                        "user_cc" => $country_dial_code,
                        "user_phone_inp" => $phone_number,
                        "valid_phone_local" => $validation_res['phone_num_nat'],
                        "valid_phone_int" => $validation_res['phone_num_int'],
                        "validated" => 0
                    ];
        
        if($redis){
            $redis->setEx("wtsauthkey:{$whatsapp_validation_key}", 3600 ,serialize($auth_data)); //store in redis
        }
    }


    
    $sms_message = __('{---1} is your verification code',[$otp_code]);
    

    $phone_num_zero_prefix = substr($validation_res['phone_num_nat'],0,1) == "0" ? $validation_res['phone_num_nat'] : "0" . $validation_res['phone_num_nat'];
    $phone_num_no_zero_prefix = substr($validation_res['phone_num_nat'],0,1) == "0" ? substr($validation_res['phone_num_nat'],1) : $validation_res['phone_num_nat'];

    //check if it already exists in DB
    $query = sprintf('SELECT user_id, firstname, pwd_raw FROM %stbl_users WHERE (phone = "%s" OR phone = "%s") AND country_dial_code = "%s"', 
        DB_TBL_PREFIX,
        $phone_num_zero_prefix, 
        $phone_num_no_zero_prefix,
        "+" . $country_dial_code
    );

    
    if($result = mysqli_query($GLOBALS['DB'], $query)){

        if(mysqli_num_rows($result)){
            $row = mysqli_fetch_assoc($result);

            //phone number exists in DB. prepare for sign in
            
            //compare passwords
            if($_POST['user_pwd'] == $row['pwd_raw']){
                $password_verified = 1;
            }

            if(SMS_OTP_SERVICE == 'custom' && !$otp_send_limit_reached && !$password_verified){
                sendSMSMessage($validation_res['phone_num_int'],$sms_message,$otp_code);
            }

            $res = array("success"=>1,'exists'=>1,'phone_num'=>$validation_res['phone_num_int'],'phone_num_nat'=>$validation_res['phone_num_nat'],'service'=> SMS_OTP_SERVICE,'user_firstname' => $row['firstname'],'country_dial_code'=>$country_dial_code,'phone_num_inp'=>$phone_number,'otp_send_limit' => $otp_send_limit_reached,'pwd_valid'=> $password_verified,'demo_otp'=>$demo_otp,'whatsapp_auth_status' => WHATSAPP_AUTH_STATUS,'whatsapp_auth_phonenum' => WHATSAPP_AUTH_PHONENUM, "whatsapp_auth_msg" => $whatsapp_auth_message, "whatsapp_auth_code" => $whatsapp_validation_key);
            echo json_encode($res);
            exit;

        }

    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;
    }

    
    

    if(SMS_OTP_SERVICE == 'custom' && !$otp_send_limit_reached){
        sendSMSMessage($validation_res['phone_num_int'],$sms_message,$otp_code);
    }


    //phone number does not exists in DB. prepare for sign up          

    $res = array("success"=>1,'exists'=>0,'phone_num'=>$validation_res['phone_num_int'],'phone_num_nat'=>$validation_res['phone_num_nat'],'service'=> SMS_OTP_SERVICE,'user_firstname' => '','country_dial_code'=>$country_dial_code,'phone_num_inp'=>$phone_number,'otp_send_limit' => $otp_send_limit_reached,'pwd_valid'=> $password_verified,'demo_otp'=>$demo_otp,'whatsapp_auth_status' => WHATSAPP_AUTH_STATUS,'whatsapp_auth_phonenum' => WHATSAPP_AUTH_PHONENUM, "whatsapp_auth_msg" => $whatsapp_auth_message, "whatsapp_auth_code" => $whatsapp_validation_key);
    echo json_encode($res);
    exit;


}


function whatsappAuthCheck(){

    $redis = connectRedis();
    $code = $_GET['code'];
    if($redis){
        $data = $redis->get("wtsauthkey:{$code}");

        if(empty($data)){
            $res = ['error' => 1,'msg' => 'No auth data found'];
            echo json_encode($res);
            exit;
        }

        $data_arr = unserialize($data);

        if(!empty($data_arr['validated'])){
            $res = ['success' => 1, 'otp_code' => $data_arr['user_otp_code']];
            echo json_encode($res);
            exit;
        }else{
            $res = $res = ['error' => 1,'msg' => 'Auth not validated'];
            echo json_encode($res);
            exit;
        }

    }

    $res = $res = ['error' => 1,'msg' => 'Redis error'];
    echo json_encode($res);
    exit;

}


function sendSMSMessage($dest_num,$message,$otp_code){
    
    //code for custom SMS service goes here
    include "../drop-files/lib/sgateways/sms.php";
    return true;
}

function verifyOTPCode(){

    $phone = $_POST['phone'];
    $code = $_POST['code'];

    if(strlen($code) != 6){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;
    }

    if(!isset($_SESSION[$phone])){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;
    }

    $otp_code = $_SESSION[$phone]['user_otp_code'];

    if($otp_code != $code){
        $error = array("error"=>__("Invalid OTP code"));
        echo json_encode($error); //database error
        exit;
    }


    $res = array("success" => 1);
    echo json_encode($res);
    exit;


}





function checkLoginStatus(){

    if(isset($_POST['timezone'])){
        $timezones_fixed = ["Asia/Calcutta" => "Asia/Kolkata"];
        $_POST['timezone'] = isset($timezones_fixed[$_POST['timezone']]) ? $timezones_fixed[$_POST['timezone']] : $_POST['timezone'];
        if(isValidTimezoneId($_POST['timezone'])){
             $_SESSION['timezone'] = $_POST['timezone'];
            date_default_timezone_set($_SESSION['timezone']);
        }else{
            date_default_timezone_set('Africa/Lagos');
        }
       
    }else{
        date_default_timezone_set('Africa/Lagos');
    }

    if(isset($_POST['platform'])){
        $_SESSION['platform'] = $_POST['platform'];
    }

    $display_language = mysqli_real_escape_string($GLOBALS['DB'], $_POST['display_lang']);

    
    if(!empty($_SESSION['loggedin'])){

        $user_account_details = [];
        $query = sprintf('SELECT referral_count,cancelled_rides,completed_rides,reward_points,country_code, route_id,country_dial_code,user_rating,photo_file,account_active,referal_code,push_notification_token,`address`,account_type,user_id,firstname, lastname,email,phone,is_activated,account_active,last_login_date,account_create_date,referal_code,wallet_amount FROM %stbl_users WHERE user_id = %d', DB_TBL_PREFIX, $_SESSION['uid']); //Get required user information from DB
        

        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                $user_account_details = mysqli_fetch_assoc($result);

                if($user_account_details['is_activated'] == 0){
                    $data = array("success"=>"1","is_activated"=>$user_account_details['is_activated'],'loggedin'=>0);
                    echo json_encode($data);
                    exit;
                }
            }
            else{
                forceLogin();    
            }
            
        }
        else{ //No record matching the USER ID was found in DB. Show view to notify user
    
            forceLogin();
        }

        //get referral setting
        $ref_code_settings = [];
        $query = sprintf('SELECT * FROM %stbl_referral WHERE id = 1', DB_TBL_PREFIX); 


        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                $ref_code_settings = mysqli_fetch_assoc($result);
            }
                        
        }

        $referral_url = isset($_SESSION['platform']) && $_SESSION['platform'] == "android" ? str_replace("market://details?","https://play.google.com/store/apps/details?",CUSTOMER_APP_UPDATE_URL_ANDROID) : CUSTOMER_APP_UPDATE_URL_IOS;//SITE_URL;
        $refcode_copy = isset($ref_code_settings['status']) && $ref_code_settings['status'] == 1 && !empty($user_account_details['referal_code']) ? __("Hi, you can now book rides from your phone anywhere, anytime using Droptaxi Taxi service. Sign-up now with my referral code {---1} and get a discount on your first ride.",["{$user_account_details['referal_code']}"],"r|{$display_language}") : '';
        $ref_code_desc = __('Earn {---1}% discount on your next ride when you invite a friend to register on Droptaxi using your referral code', [round($ref_code_settings['discount_value'])],"r|{$display_language}");
        $ref_code = isset($ref_code_settings['status']) && $ref_code_settings['status'] == 1 && !empty($user_account_details['referal_code']) ? $user_account_details['referal_code'] : "";
        
        

        if(isset($user_account_details['push_notification_token'])){
            $_SESSION['push_token'] = $user_account_details['push_notification_token'];
        }
        


        //get default currency data
        $default_currency_data = [];
        $query = sprintf('SELECT * FROM %stbl_currencies WHERE `default` = 1', DB_TBL_PREFIX);
        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                $default_currency_data = mysqli_fetch_assoc($result);
            }
        }


        //get user documents
        $user_documents = [];
        $query = sprintf('SELECT *,%1$stbl_documents.id AS d_id FROM %1$stbl_documents
        LEFT JOIN %1$stbl_users_documents ON %1$stbl_users_documents.doc_id = %1$stbl_documents.id AND %1$stbl_users_documents.u_id = %2$d AND %1$stbl_users_documents.u_type = 0
        WHERE %1$stbl_documents.doc_user = 0 AND %1$stbl_documents.status = 1', DB_TBL_PREFIX, $user_account_details['user_id']);

        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                while($row = mysqli_fetch_assoc($result)){
                    if(!empty($row['u_doc_img'])){
                        $row['u_doc_img'] = SITE_URL . "ajaxuserphotofile.php?file=" . $row['u_doc_img'];
                    }
                    $user_documents[$row['d_id']] = $row;
                }
            }
        }



        //get tariff data
        $tariff_data = getroutetariffs();



       
        /* account_active,
        referal_code,
        push_notification_token,
        `address`,
        account_type,user_id,
        firstname,
        lastname,email,phone,is_activated,account_active,last_login_date,account_create_date,referal_code,wallet_amount */

        //$photo = explode('/',$user_account_details['photo_file']);
        $photo_file = isset($user_account_details['photo_file']) ? $user_account_details['photo_file'] : "0";

        $_SESSION['firstname'] = $user_account_details['firstname'];
        $_SESSION['lastname'] = $user_account_details['lastname'];
        $_SESSION['uid'] = $user_account_details['user_id'];
        $_SESSION['email'] = $user_account_details['email'];
        $_SESSION['route_id'] = $user_account_details['route_id'];
        $_SESSION['phone'] = $user_account_details['phone'];
        $_SESSION['country_dial_code'] = $user_account_details['country_dial_code'];
        $_SESSION['address'] = $user_account_details['address'];
        $_SESSION['referal_code'] = $user_account_details['referal_code'];
        $_SESSION['account_type'] = $user_account_details['account_type'];
        $_SESSION['lastseen'] = $user_account_details['last_login_date'];
        $_SESSION['joined'] = $user_account_details['account_create_date'];
        $_SESSION['loggedin'] = 1;
        $_SESSION['reward_points'] = $user_account_details['reward_points'];
        $_SESSION['is_activated'] = $user_account_details['is_activated'];
        $_SESSION['wallet_amt'] = $user_account_details['wallet_amount'];
        $_SESSION['push_token'] = $user_account_details['push_notification_token'];   
        $_SESSION['photo'] = SITE_URL . "ajaxuserphotofile.php?file=" . $photo_file;
        $_SESSION['user_rating'] = $user_account_details['user_rating'];


        //profile information
        $profiledata = array(
            'success' => 1,
            'firstname'=> $_SESSION['firstname'],
            'lastname'=> $_SESSION['lastname'],
            'email'=> $_SESSION['email'],
            'route_id'=> $_SESSION['route_id'],
            'phone'=> $_SESSION['phone'],
            'address'=> $_SESSION['address'],
            'userid' => $_SESSION['uid'],
            'ref_code'=>$ref_code,
            'ref_code_copy_msg' => $refcode_copy,
            'ref_desc' => $ref_code_desc,
            'ref_url' => $referral_url,
            'photo' => $_SESSION['photo'],            
            'referral_count'=>$user_account_details['referral_count'],
            'cancelled_rides'=>$user_account_details['cancelled_rides'],
            'completed_rides'=>$user_account_details['completed_rides'],
            'user_rating' => $user_account_details['user_rating'],
            'country_code' => $user_account_details['country_code'],
            'country_dial_code' => $user_account_details['country_dial_code'],
            'user_docs' => $user_documents            
    
        );

        $online_payment_data = array(

            'merchantid'=> P_G_PK,
            'storeid'=> STORE_ID,
            'devid'=> DEV_ID,
            'notifyurl'=> NOTIFY_URL
            
        );


        if(is_array(DEFAULT_PAYMENT_GATEWAY) && !empty(DEFAULT_PAYMENT_GATEWAY)){
            $default_pgs = DEFAULT_PAYMENT_GATEWAY;
            $payment_gateways = [];
            foreach($default_pgs as $key=>$value){
                $payment_gateways[] = $key;
            }
        }else{
            $payment_gateways = DEFAULT_PAYMENT_GATEWAY;
        }
        

        $app_settings = array(
            'payment_type' => PAYMENT_TYPE,
            'ride_otp' => RIDE_OTP,
            'default_payment_gateway'=> $payment_gateways,
            'wallet_topup_presets' => WALLET_TOPUP_PRESETS,
            'driver_tip_presets' => DRIVER_TIP_PRESETS,
            'vehicle_sel_disp_type' => VEHICLE_SEL_DISPLAY_TYPE,
            'round_trip_fares' => ROUND_TRIP_FARES,
            'trip_cancel_reasons' => RIDER_CANCEL_REASON,
            "allowed_countries_list" => ALLOWED_COUNTRIES_LIST,
            "force_update_customer_android" => FORCE_UPDATE_CUSTOMER_ANDROID,
            "force_update_customer_ios" => FORCE_UPDATE_CUSTOMER_IOS              
        );

        $firebase_rtdb_conf = array(
            'databaseURL' => FB_RTDB_URL,
            'apiKey' => FB_WEB_API_KEY,
            'storageBucket' => FB_STORAGE_BCKT
        );

        
        //update users last seen time and user selected app language
        $query = sprintf('UPDATE %stbl_users SET last_login_date = "%s", disp_lang = "%s", login_count = login_count + 1 WHERE user_id = %d', DB_TBL_PREFIX,gmdate('Y-m-d H:i:s', time()),$display_language,$_SESSION['uid']);
        $result = mysqli_query($GLOBALS['DB'], $query);

        $_SESSION['lang'] = $display_language;

        $ongoing_booking = [];
        //check if user has an ongoing booking
        $query = sprintf('SELECT *,%1$stbl_bookings.id AS booking_id FROM %1$stbl_bookings 
        INNER JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id
        INNER JOIN %1$stbl_driver_location ON %1$stbl_driver_location.driver_id = %1$stbl_bookings.driver_id
        WHERE %1$stbl_bookings.user_id = %2$d AND %1$stbl_bookings.driver_id != 0 AND (%1$stbl_bookings.status = 0 OR %1$stbl_bookings.status = 1) ORDER BY %1$stbl_bookings.id DESC LIMIT 1', DB_TBL_PREFIX, $_SESSION['uid']);

        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                $row = mysqli_fetch_assoc($result);
                $action = '';
                if(!empty($row['date_arrived']) && $row['status'] == 0){
                    $action = "driver-arrived";
                }elseif(empty($row['date_arrived']) && $row['status'] == 0 ){
                    $action = "driver-assigned";
                }else{
                    $action = "customer-onride";
                }
                $driver_photo_file = isset($row['photo_file']) ? $row['photo_file'] : "0";
                $ongoing_booking = [
                    "action"=>$action,
                    "route_id" => $row['route_id'],
                    "booking_id" => $row['booking_id'],
                    "driver_id" => $row['driver_id'],
                    "driver_firstname" => $row['firstname'],
                    "driver_phone" => $row['phone'],
                    "driver_platenum" => $row['car_plate_num'],
                    "driver_rating" => $row['driver_rating'],
                    "driver_location_lat" => $row['lat'],
                    "driver_location_long" => $row['long'],
                    "pickup_lat"=>$row['pickup_lat'],
                    "pickup_long"=>$row['pickup_long'],
                    "dropoff_lat"=>$row['dropoff_lat'],
                    "dropoff_long"=>$row['dropoff_long'],
                    "driver_carmodel" => $row['car_model'],
                    "driver_arrived_time" => !empty($row['date_arrived']) ? strtotime($row['date_arrived'] . " UTC") : 0,
                    "driver_start_time" => !empty($row['date_started']) ? strtotime($row['date_started'] . " UTC") : 0,
                    "driver_carid" => $row['ride_id'],
                    "driver_completed_rides" => $row['completed_rides'],
                    "elapsed_wait_time" => !empty($row['date_arrived']) ? time() - strtotime($row['date_arrived'] . ' UTC') : 0,
                    "completion_code"=>$row['completion_code'],
                    "driver_photo" => SITE_URL . "ajaxphotofile.php?file=" . $driver_photo_file,
                    "waypoint1_address" => $row['waypoint1_address'],
                    "waypoint1_long" => $row['waypoint1_long'],
                    "waypoint1_lat" => $row['waypoint1_lat'],
                    "waypoint2_address" => $row['waypoint2_address'],
                    "waypoint2_long" => $row['waypoint2_long'],
                    "waypoint2_lat" => $row['waypoint2_lat'],
                    "date_arrived_stop_1" => !empty($row['date_arrived_stop_1']) ? strtotime($row['date_arrived_stop_1'] . " UTC") : "",
                    "date_arrived_stop_2" => !empty($row['date_arrived_stop_2']) ? strtotime($row['date_arrived_stop_2'] . " UTC") : "",
                    "date_left_stop_1" => !empty($row['date_left_stop_1']) ? strtotime($row['date_left_stop_1'] . " UTC") : "",
                    "date_left_stop_2" => !empty($row['date_left_stop_2']) ? strtotime($row['date_left_stop_2'] . " UTC") : "",
                    "service_mode"=>$row['service_mode'],
                    "hourly_rate_hours"=>$row['hr_set_num_h']
                ];
            }
        }



        $recent_bookings_loc = [];
        //get 4 recently completed bookings dropoff locations
        if(!empty($_SESSION['route_id'])){
            $query = sprintf('SELECT %1$stbl_bookings.dropoff_address, %1$stbl_bookings.dropoff_long, %1$stbl_bookings.dropoff_lat FROM %1$stbl_bookings
            WHERE %1$stbl_bookings.user_id = %2$d AND %1$stbl_bookings.route_id = %3$d AND %1$stbl_bookings.status = 3 ORDER BY %1$stbl_bookings.id DESC LIMIT 10', DB_TBL_PREFIX, $_SESSION['uid'],$_SESSION['route_id']);

            if($result = mysqli_query($GLOBALS['DB'], $query)){
                if(mysqli_num_rows($result)){
                    $unique_address = [];
                    $count = 0;
                    while($row = mysqli_fetch_assoc($result)){
                        if(empty($row['dropoff_long']) || empty($row['dropoff_lat']))continue;
                        $location_coords = $row['dropoff_long'] . $row['dropoff_lat'];
                        if(!in_array($location_coords,$unique_address)){
                            $count++;
                            $unique_address[] = $location_coords;
                            $recent_bookings_loc[] = $row;
                            if($count == 3)break;
                        }                    

                    }
                }
            }
        }


        $recent_loc_data = [
            "route_id"=>$_SESSION['route_id'],
            "locations"=>$recent_bookings_loc
        ];
        


        $data = array("loggedin"=>1,"ongoing_bk"=>$ongoing_booking,"fb_conf"=>$firebase_rtdb_conf,"recent_locs"=>$recent_loc_data,"is_activated"=>$_SESSION['is_activated'],"account_active"=>$user_account_details['account_active'],'wallet_amt' => $_SESSION['wallet_amt'],'reward_points' => $_SESSION['reward_points'],'cc_num'=>CALL_CENTER_NUMBER,'profileinfo' => $profiledata,'tariff_data'=>$tariff_data,'online_pay'=>$online_payment_data,'app_version_ios'=>APP_VERSION_CUSTOMER_IOS,'app_version_android'=>APP_VERSION_CUSTOMER_ANDROID,'customer_app_update_url_ios'=>CUSTOMER_APP_UPDATE_URL_IOS,'customer_app_update_url_android'=>CUSTOMER_APP_UPDATE_URL_ANDROID,'driver_app_update_url_ios'=>DRIVER_APP_UPDATE_URL_IOS,'driver_app_update_url_android'=>DRIVER_APP_UPDATE_URL_ANDROID,'scheduled_ride_enabled' => SCHEDULED_RIDE_ENABLED,'default_currency'=>$default_currency_data,'app_settings'=>$app_settings,'sess_id' => base64_encode(session_id()),'bannerdata'=>getbannerdata());
        echo json_encode($data);
        exit;
    }else{

        forceLogin();
    }

}



function forceLogin(){
    
    $tariff_data = getroutetariffs();
    $display_language = mysqli_real_escape_string($GLOBALS['DB'], $_POST['display_lang']);
    $_SESSION['lang'] = $display_language;
    $app_settings = [
        "allowed_countries_list" => ALLOWED_COUNTRIES_LIST,
        "force_update_customer_android" => FORCE_UPDATE_CUSTOMER_ANDROID,
        "force_update_customer_ios" => FORCE_UPDATE_CUSTOMER_IOS
    ];
    $data = array("loggedin"=>0,'tariff_data'=>$tariff_data,'app_version_ios'=>APP_VERSION_CUSTOMER_IOS,'app_version_android'=>APP_VERSION_CUSTOMER_ANDROID,'customer_app_update_url_ios'=>CUSTOMER_APP_UPDATE_URL_IOS,'customer_app_update_url_android'=>CUSTOMER_APP_UPDATE_URL_ANDROID,'driver_app_update_url_ios'=>DRIVER_APP_UPDATE_URL_IOS,'driver_app_update_url_android'=>DRIVER_APP_UPDATE_URL_ANDROID,'cc_num'=>CALL_CENTER_NUMBER,'app_settings'=>$app_settings,'sess_id' => base64_encode(session_id()));
    echo json_encode($data);
    exit;

}


function registerUser(){
    
    $user_reg_data = $_POST['reg_data'];
    $user_photo = $user_reg_data['profile_photo'];
    $user_reg_referral = trim($user_reg_data['referral']);
    $user_firstname = mysqli_real_escape_string($GLOBALS['DB'], trim($user_reg_data['firstname']));
    $user_lastname = mysqli_real_escape_string($GLOBALS['DB'], trim($user_reg_data['lastname']));
    $user_country_dial_code = $user_reg_data['country_dial_code'];
    $user_phone_inp = $user_reg_data['phone'];
    $otp_code = $user_reg_data['otp_code'];
    $fb_user_details = $user_reg_data['fb_user_details'];
    $user_password = $user_reg_data['password'];

    if(empty($user_firstname)) {        
        $error = array("error"=>__("Please enter your first name"));
        echo json_encode($error); 
        exit;        
    }


    if(empty($user_lastname)) {        
        $error = array("error"=>__("Please enter your last name"));
        echo json_encode($error); 
        exit;
    } 


    if(strlen($user_phone_inp) > 20) {        
        $error = array("error"=>__("Your phone number is too long"));
        echo json_encode($error); 
        exit;
    } 

    if(strlen($user_phone_inp) < 5) {        
        $error = array("error"=>__("Your phone number is too short"));
        echo json_encode($error); 
        exit;
    }

    if((strlen($user_password) < 8 )){
        
        $error = array("error"=>__("Password is too short"));
        echo json_encode($error); 
        exit;
    }

    if((strlen($user_password) > 60 )){
        
        $error = array("error"=>__("Password is too long"));
        echo json_encode($error); 
        exit;
    }


    //check if phone number matches the one earlier valiated
    $validation_res = validatePhoneNumber('+' . $user_country_dial_code . $user_phone_inp);
            
    if(isset($validation_res['error'])){
        $res = array("error"=>__("Phone number is invalid"));
        echo json_encode($res); //invalid phone number
        exit;
    }


    
    if(SMS_OTP_SERVICE == 'firebase'){

        if(!empty($fb_user_details) && isset($fb_user_details['uid'])){
            //verify otp with firebase
            $verify_res = verifyFirebaseUser($fb_user_details);
            if(isset($verify_res['error'])){
                $res = array("error"=>__("An error has occured"));
                echo json_encode($res); //database error
                exit;
            }

            $user_phone_number = $verify_res['data']->phoneNumber;

            $user_phone_number_val = validatePhoneNumber($user_phone_number);

            if($user_phone_number_val['phone_num_nat'] != $validation_res['phone_num_nat']){
                $error = array("error"=>__("Invalid OTP code"));
                echo json_encode($error); //database error
                exit;
            }

                                        

        }else{
            $error = array("error"=>__("Invalid OTP code"));
            echo json_encode($error); //database error
            exit;
        }

    }else{

        
        if(!(isset($_SESSION[$validation_res['phone_num_int']]) && $_SESSION[$validation_res['phone_num_int']]['user_otp_code'] == $otp_code)){
            
            $res = array("error"=>__("Phone number is invalid"));
            echo json_encode($res); //invalid phone number
            exit;
        }
    
    }
    

    
    $user_country_code = $_SESSION[$validation_res['phone_num_int']]['user_country'];

    $user_country = codeToCountryName(strtoupper($user_country_code));

    if(!$user_country){        
        $error = array("error"=>__("Invalid country selected"));
        echo json_encode($error); 
        exit;
    }


    $query = sprintf('SELECT user_id,email,phone,country_dial_code FROM %stbl_users WHERE (phone = "%s" OR phone = "%s") AND country_dial_code = "%s"', DB_TBL_PREFIX, $user_phone_inp,$validation_res['phone_num_nat'],"+" . $user_country_dial_code);


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){

            
            
            $error = array("error"=>__("The phone number already exists. Please use a different phone number"));
            echo json_encode($error);
            exit;
            
            
            
        }
    }else{

        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;


    }

    
    if(!empty($user_photo)){
        //save user passport
        $uploaded_photo_encoded = $user_photo; //Get Base64 encoded image data.
        $uploaded_photo_encoded_array = explode(',', $uploaded_photo_encoded);
        $image_data = array_pop($uploaded_photo_encoded_array);
        $uploaded_photo_decoded = base64_decode($image_data); //Decode the data

        
        if(!$uploaded_photo_decoded){ //Verify that data is valid base64 data            
            $res = array("error"=>__("Please upload a passport photo in JPEG format"));
            echo json_encode($res); 
            exit;
        } 

        //prepare filename and save the file. Exported base64 image data in JPEG format. We should be expecting a JPEG image data.
        $filename =  crypto_string('distinct',20);

        @mkdir(realpath(CUSTOMER_PHOTO_PATH) . "/". $filename[0] . "/" . $filename[1] . "/" . $filename[2], 0777, true);

        
        $image_path = realpath(CUSTOMER_PHOTO_PATH) .  "/". $filename[0] . "/" . $filename[1] . "/" . $filename[2] . "/";
        $file = $image_path . $filename . ".jpg";

    
        
        file_put_contents($file, $uploaded_photo_decoded); //store the photo to disk.     

        $user_passport_photo = $filename . ".jpg";

    }else{
        $user_passport_photo = "";
    }

    $ref_code_result_msg = __("Thank you for joining our ride service. We hope you enjoy every ride you take. Enjoy!");
    $ref_user_data = [];
    //handle referal    
    //get referal settings
    $referal_settings_data = [];
    $query = sprintf('SELECT `status`,beneficiary,discount_value FROM %stbl_referral WHERE id = %d', DB_TBL_PREFIX, 1);
    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $referal_settings_data = mysqli_fetch_assoc($result);     
        }
    }



    $invitee_referal_benefit = 0;

    if(!empty($user_reg_referral) && strlen($user_reg_referral) < 11 && !empty($referal_settings_data['status'])){


        $ref_code = mysqli_real_escape_string($GLOBALS['DB'], $user_reg_referral);

        $query = sprintf('SELECT user_id FROM %stbl_users WHERE referal_code = "%s"', DB_TBL_PREFIX, $ref_code);

        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
    
                $ref_user_data = mysqli_fetch_assoc($result);

                $customer_notification_msg = '';                 
                if($referal_settings_data['beneficiary'] == 0 || $referal_settings_data['beneficiary'] == 2){ //old customer benefits 
                    $query = sprintf('UPDATE %stbl_users SET referral_count = referral_count + 1, referral_discounts_count = referral_discounts_count + 1 WHERE user_id = %d', DB_TBL_PREFIX, $ref_user_data['user_id']);
                    $result = mysqli_query($GLOBALS['DB'], $query);

                    //notify customer
                    $customer_notification_msg = __("A new customer registered on the serivce with your referral code. You are eligible to a {---1} discount on your next ride. Thank you",["{$referal_settings_data['discount_value']}%"]);
                    $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
                        ("%d",0,"%s",0,"%s")', 
                        DB_TBL_PREFIX,
                        $ref_user_data['user_id'],
                        mysqli_real_escape_string($GLOBALS['DB'], $customer_notification_msg),
                        gmdate('Y-m-d H:i:s', time()) 
                    );
                    $result = mysqli_query($GLOBALS['DB'], $query);

                }elseif($referal_settings_data['beneficiary'] == 1){

                    $query = sprintf('UPDATE %stbl_users SET referral_count = referral_count + 1 WHERE user_id = %d', DB_TBL_PREFIX, $ref_user_data['user_id']);
                    $result = mysqli_query($GLOBALS['DB'], $query);

                    //notify customer
                    $customer_notification_msg = __("A new customer registered on the serivce with your referral code. Thank you for growing our great and awesome service");
                    $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
                        ("%d",0,"%s",0,"%s")', 
                        DB_TBL_PREFIX,
                        $ref_user_data['user_id'],
                        mysqli_real_escape_string($GLOBALS['DB'], $customer_notification_msg),
                        gmdate('Y-m-d H:i:s', time()) 
                    );
                    $result = mysqli_query($GLOBALS['DB'], $query);

                }
                    
                
                if($referal_settings_data['beneficiary'] == 1 || $referal_settings_data['beneficiary'] == 2){ //invitee benefits 
                    $invitee_referal_benefit = 1;
                    $ref_code_result_msg = __("Thank you for joining our ride service. The referral code you registered with has earned you a {---1} discount on your next ride. Enjoy", ["{$referal_settings_data['discount_value']}%"]);
                }
                
                
            }

        }


    }

    

    //OK, all good. lets store the registrant form data in the database
    $new_user_referal_code = "";

    for ($x = 0;$x < 10;$x++){ //try to generate a unique code for a referal code
        
        
        $new_user_referal_code =  crypto_string("ABCDEFGHIJKLMNOPQRSTUVWXYZ",4);
        $new_user_referal_code .=  crypto_string("123456789",4);
        
        
    
        //check database to see if generated code already exists
        $query = sprintf('SELECT * FROM %stbl_users WHERE referal_code = "%s"',DB_TBL_PREFIX,$new_user_referal_code);
    
        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
        
                continue; //found in db? iterate loop; try again.      
            
            }else{
                break; //found? ok stop loop
            }
        }else{
            break;
        }        
    
    
    }


    $fake_email = str_replace(" ","",$user_firstname).$new_user_referal_code."@enter-your-email.com";    
    

    $query = sprintf('INSERT INTO %stbl_users (route_id,account_active,is_activated,firstname, lastname, email, phone, pwd_raw, password_hash, account_create_date,referal_code,photo_file, referral_discounts_count,country, country_code, country_dial_code) VALUES'.
    '("%d","%d","%d","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s")', 
    DB_TBL_PREFIX,
    1, //default route id
    1,
    1, //activated
    $user_firstname,
    $user_lastname,
    $fake_email,
    $validation_res['phone_num_nat'],
    $user_password,
    password_hash($user_password, PASSWORD_DEFAULT),
    gmdate('Y-m-d H:i:s', time()),
    $new_user_referal_code,
    $user_passport_photo,
    $invitee_referal_benefit,
    $user_country,
    $user_country_code,
    "+".$user_country_dial_code
    );


    if(! $result = mysqli_query($GLOBALS['DB'], $query)){

               
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
        
    }
    else{
        $user_id = mysqli_insert_id($GLOBALS['DB'] );            
    }


    if(!$user_id){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit; 

    }

    //notify customer        
    $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
        ("%d",0,"%s",0,"%s")', 
        DB_TBL_PREFIX,
        $user_id,
        mysqli_real_escape_string($GLOBALS['DB'],$ref_code_result_msg ),
        gmdate('Y-m-d H:i:s', time()) 
    );
    $result = mysqli_query($GLOBALS['DB'], $query);

   
                
    $data = array("success"=>1);
    echo json_encode($data); 
    exit;


}




function passwordReset(){

    $user_account_details = [];

    $email = !empty($_POST['email']) ? mysqli_real_escape_string($GLOBALS['DB'], $_POST['email']) : "";

    //check if this email exist on db
    $query = sprintf('SELECT user_id,email,phone FROM %stbl_users WHERE email = "%s"', DB_TBL_PREFIX, $email); //Get required user information from DB


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $user_account_details = mysqli_fetch_assoc($result);
        }
        else{
            $error = array("error"=>__("Invalid account"));
            echo json_encode($error); //invalid record
            exit;

        }
        
    }
    else{ //No record matching the USER ID was found in DB. Show view to notify user

        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;
    }


    //Generate a random code
    $code = crypto_string("nozero",6); //generate token

    

    //delete any previous password reset code for this user
    $query = sprintf('DELETE FROM %stbl_account_codes WHERE user_id = "%d" AND user_type = 0 AND context=1', DB_TBL_PREFIX, $user_account_details['user_id']); //delete already inserted record 
    $result = mysqli_query($GLOBALS['DB'], $query);

    //save code in table for this user to signify password change request
    $query = sprintf('INSERT INTO %stbl_account_codes (user_id, code,user_type,context) VALUES ("%d","%s",0,1)',DB_TBL_PREFIX, $user_account_details['user_id'], $code); 
    
    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ //An error has occured while trying to update
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

    //send code to users email
    $message = "";

    $mail_sender_address = 'From: '.MAIL_SENDER;
    $headers = array($mail_sender_address,'MIME-Version: 1.0', 'Content-Type: text/html; charset="iso-8859-1"'); //Required for a HTML formatted E-mail ;)

    if(EMAIL_TRANSPORT == 1){
        mail($email, PWD_RST_EMAIL_SUBJ,"<html>".PWD_RST_EMAIL_MSG."<br><br><h2><b style='text-align:center;'>{$code}</b></h2><br><br></html>", join("\r\n", $headers));
    }else{
        sendMail($email, PWD_RST_EMAIL_SUBJ, PWD_RST_EMAIL_MSG . "<br><br><h2><b style='text-align:center;'>{$code}</b></h2><br><br></html>");
    }

    $success = array("success"=>__("Password reset code has been sent to your email"));
    echo json_encode($success); 
    exit;




}



function passwordResetPhone(){

    $user_account_details = [];

    $country_code = !empty($_POST['country_code']) ? mysqli_real_escape_string($GLOBALS['DB'], $_POST['country_code']) : "";

    $phone = !empty($_POST['phone_number']) ? mysqli_real_escape_string($GLOBALS['DB'], $_POST['phone_number']) : "";

    //check if this phone number exist on db
    $query = sprintf('SELECT user_id,email,phone,country_dial_code,pwd_raw FROM %stbl_users WHERE phone = "%s" AND country_dial_code = "%s"', DB_TBL_PREFIX, $phone, "+" . $country_code); //Get required user information from DB


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $user_account_details = mysqli_fetch_assoc($result);
        }
        else{
            $error = array("error"=>__("Invalid account"));
            echo json_encode($error); //invalid record
            exit;

        }
        
    }
    else{ //No record matching the USER ID was found in DB. Show view to notify user

        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;
    }

    

    $success = array("success"=> 1,'pwd' => $user_account_details['pwd_raw']);
    echo json_encode($success); 
    exit;




}



function passwordResetVerify(){

    $user_account_details = [];

    $passcode = !empty($_POST['code']) ? mysqli_real_escape_string($GLOBALS['DB'], $_POST['code']) : "";

    
    //check if this email exist on db
    $query = sprintf('SELECT user_id FROM %stbl_account_codes WHERE code = "%s" AND user_type = 0 AND context = 1', DB_TBL_PREFIX, $passcode); //Get required user information from DB


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $user_account_details = mysqli_fetch_assoc($result);
        }
        else{
            $error = array("error"=>__("Invalid password reset code"));
            echo json_encode($error); //invalid record
            exit;

        }
        
        
    }
    else{ //No record matching the USER ID was found in DB. Show view to notify user

        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;
    }


    //Generate a a new pasword
    $newpassword = crypto_string("hexdec",5); //generate token


    //update user password on db
    $query = sprintf('UPDATE %stbl_users SET `pwd_raw` = "%s" WHERE user_id = "%d"', DB_TBL_PREFIX,$newpassword,$user_account_details['user_id']);
    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ //An error has occured while trying to update KSmart user ID on SIS user database record?
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }


    //delete any previous password reset code for this user
    $query = sprintf('DELETE FROM %stbl_account_codes WHERE code = "%s" AND user_type = 0 AND context=1', DB_TBL_PREFIX, $passcode); //delete already inserted record 
    $result = mysqli_query($GLOBALS['DB'], $query);

    
    $success = array("success"=>__("Password change was successful. Your new password is {---1}",["<b>{$newpassword}</b>"]));
    echo json_encode($success); 
    exit;




}



function userActivateCode(){
    $code = (int) $_POST['code'];
    if(empty($code)){
        $error = array("error"=>__("Please enter an activation code"));
        echo json_encode($error); 
        exit; 
    }

    if(!empty($_SESSION['loggedin'])){
        $user_id = $_SESSION['uid'];
        
    }elseif(!empty($_SESSION['new_reg'])){

        $user_id = $_SESSION['new_reg']['uid'];
        
    }elseif(!empty($_SESSION['not_activated_user'])){

        $user_id = $_SESSION['not_activated_user']['uid'];
        
    }else{

        $user_id = 0;
        
    }

    $query = sprintf('SELECT code FROM %stbl_account_codes WHERE code = "%d" AND user_id = "%d" AND context = 0', DB_TBL_PREFIX, $code,$user_id); //Get required user information from DB


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
        $row = mysqli_fetch_assoc($result);
        }
        else{
            $error = array("error"=>__("Wrong activation code"));
            echo json_encode($error); 
            exit;
        }
        
    }
    else{ //No record matching the USER ID was found in DB. Show view to notify user

        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }


    $query = sprintf('UPDATE %stbl_users SET is_activated = 1, account_active = 1 WHERE user_id = "%d"', DB_TBL_PREFIX,$user_id );

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ //An error has occured while trying to update KSmart user ID on SIS user database record?
        $error = array("error"=>__("An error has occured."));
        echo json_encode($error); 
        exit;
    }

    $query = sprintf('DELETE FROM %stbl_account_codes WHERE user_id = "%d" AND code="%d"', DB_TBL_PREFIX,$user_id,$code); //delete already inserted record 
    $result = mysqli_query($GLOBALS['DB'], $query);
    $_SESSION['is_activated'] = 1;

    
    $response = array("success"=>__("Your account has been successfully activated. Restart the App"));
    echo json_encode($response); 
    exit; 
   




}






function messagedriver(){

    $driver_id = (int) $_POST['driver_id'];
    $content = $_POST['content'];

   
    
    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please re-login and retry.");
        echo json_encode($error); 
        exit; 
    }


    $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
        ("%d",1,"%s",1,"%s")', 
        DB_TBL_PREFIX,
        $driver_id,
        mysqli_real_escape_string($GLOBALS['DB'],$content),
        gmdate('Y-m-d H:i:s', time()) 
    );

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ 
        $error = array("error"=>"Failed to send message.");
        echo json_encode($error); 
        exit;
    }


    $success = array("success"=>"Message sent successfully");
    echo json_encode($success); 
    exit;



}


function messagecustomer(){

    $user_id = (int) $_POST['user_id'];
    $content = $_POST['content'];

   
    
    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please re-login and retry.");
        echo json_encode($error); 
        exit; 
    }


    $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
        ("%d",0,"%s",1,"%s")', 
        DB_TBL_PREFIX,
        $user_id,
        mysqli_real_escape_string($GLOBALS['DB'],$content),
        gmdate('Y-m-d H:i:s', time()) 
    );

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ 
        $error = array("error"=>"Failed to send message.");
        echo json_encode($error); 
        exit;
    }


    $success = array("success"=>"Message sent successfully");
    echo json_encode($success); 
    exit;



}


function getrouterides(){
    
    $tariff_data = [];
    $rides_data = [];


    $route_id = (int) $_POST['route_id'];
    

   
    
    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please re-login and retry.");
        echo json_encode($error); 
        exit; 
    }


    $query = sprintf('SELECT *,%1$stbl_routes.id AS route_id  FROM %1$stbl_routes
    INNER JOIN %1$stbl_rides_tariffs ON %1$stbl_rides_tariffs.routes_id = %1$stbl_routes.id
    INNER JOIN %1$stbl_rides ON %1$stbl_rides_tariffs.ride_id = %1$stbl_rides.id
    WHERE %1$stbl_rides.avail = 1', DB_TBL_PREFIX);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                $tariff_data[] = $row;
            }
                            
        }    
    }
    



    
    $data_array = [];

    //sort rides tarif data
    foreach($tariff_data as $tariffdata){

        $rides_data[$tariffdata['route_id']]['r_id'] = $tariffdata['route_id'];
        $rides_data[$tariffdata['route_id']]['cars'][] = $tariffdata;
        $select_options = '';
        foreach($rides_data[$tariffdata['route_id']]['cars'] as $ridesdata){

            $select_options .= "<option data-cpk = {$ridesdata['cost_per_km']} data-cpm = {$ridesdata['cost_per_minute']} data-puc = {$ridesdata['pickup_cost']} data-doc={$ridesdata['drop_off_cost']} data-cc={$ridesdata['cancel_cost']} data-ncpk = {$ridesdata['ncost_per_km']} data-ncpm = {$ridesdata['ncost_per_minute']} data-npuc = {$ridesdata['npickup_cost']} data-ndoc={$ridesdata['ndrop_off_cost']} data-ncc={$ridesdata['ncancel_cost']} value={$ridesdata['ride_id']} data-rideid={$ridesdata['ride_id']} data-ridedesc={$ridesdata['ride_desc']}>{$ridesdata['ride_type']}</option>";
            
        }

        $rides_data[$tariffdata['route_id']]['cars_html'] = $select_options;

    }

    

    $data_array = array("success"=>1,'result'=>$rides_data);


    
    echo json_encode($data_array); 
    exit;



}




function getroutetariffs(){

    $tariff_data = [];
    $rides_data = [];
    $zones_data = [];
       
    
    

    $query = sprintf('SELECT *,%1$stbl_routes.id AS route_id  FROM %1$stbl_routes
    INNER JOIN %1$stbl_currencies ON %1$stbl_currencies.id = %1$stbl_routes.city_currency_id
    INNER JOIN %1$stbl_rides_tariffs ON %1$stbl_rides_tariffs.routes_id = %1$stbl_routes.id
    INNER JOIN %1$stbl_rides ON %1$stbl_rides_tariffs.ride_id = %1$stbl_rides.id
    WHERE %1$stbl_rides.avail = 1 AND %1$stbl_routes.r_scope = 0 ORDER BY %1$stbl_routes.r_title, %1$stbl_rides.id ASC', DB_TBL_PREFIX);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                $tariff_data[] = $row;
            }
                            
        }    
    }


    //get city zones
    $query = sprintf('SELECT city_id,zone_fare_type,zone_fare_value,zone_bound_coords FROM %stbl_zones', DB_TBL_PREFIX);


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                $zones_data[$row['city_id']][] = $row;
            }
                            
        }    
    }



    
    $data_array = [];
    $city_select_options = '';
    $state_select_options = '';
    $tariff_ids = [];
    $sel_route_id = !empty($_POST['sel_route_id']) ? $_POST['sel_route_id'] : 0;
    $sel_route_name = !empty($_POST['sel_route_name']) ? $_POST['sel_route_name'] : 0;
    $count = 0;
    $route_selected = '';
    $rides_ids = [];
    $rides_url = '';
    
    //sort rides tarif data
    foreach($tariff_data as $tariffdata){
        $count ++;
        $rides_data[$tariffdata['route_id']]['r_id'] = $tariffdata['route_id'];
        
        if(empty($sel_route_id)){ 
            if($count == 1){
               //    $route_selected = "checked";
            }else{
                $route_selected = "";
            }    
        }else{
            if($sel_route_id == $tariffdata['route_id'] && $sel_route_name == $tariffdata['r_title']){
                //$route_selected = "checked";
                $rides_data['route-exists'] = 1;
            }else{
                $route_selected = "";
            }
            
        }

        if(array_search($tariffdata['route_id'],$tariff_ids) === false){
            if($tariffdata['r_scope'] == 0){
                $tariff_ids[] = $tariffdata['route_id'];
                $rides_data['city_name'][] = $tariffdata['r_title'];
                $rides_data['city_id'][] = $tariffdata['route_id'];
                $route_name_variable = "'" . $tariffdata['r_title'] . "'"; 
                $city_select_options .= "<ons-list-item tappable class='city-route-list' onclick = routecityitemselected({$tariffdata['route_id']}) data-routename='{$tariffdata['r_title']}' id=route-sel-{$tariffdata['route_id']} ><label class='left'><ons-radio {$route_selected} name='city-route' id='radio-sel-{$tariffdata['route_id']}' input-id='radio-{$tariffdata['route_id']}'></ons-radio></label><label for='radio-{$tariffdata['route_id']}' class='center'>{$tariffdata['r_title']}</label></ons-list-item>";
            }else{
                $tariff_ids[] = $tariffdata['route_id'];
                $rides_data['state_name'][] = $tariffdata['r_title'];
                $rides_data['state_id'][] = $tariffdata['route_id'];
                $route_name_variable = "'" . $tariffdata['r_title'] . "'"; 
                $state_select_options .= "<ons-list-item data-plng='{$tariffdata['pick_lng']}' data-plat='{$tariffdata['pick_lat']}' data-dlng='{$tariffdata['drop_lng']}' data-dlat='{$tariffdata['drop_lat']}' data-pus='{$tariffdata['pick_name']}' data-dos='{$tariffdata['drop_name']}' tappable class='state-route-list' onclick = routestateitemselected({$tariffdata['route_id']}) data-routename='{$tariffdata['r_title']}' id=route-sel-{$tariffdata['route_id']} ><label for='radio-{$tariffdata['route_id']}' class='center'>{$tariffdata['r_title']}</label></ons-list-item>";
            }
        }     

        $rides_data[$tariffdata['route_id']]['cars'][] = $tariffdata;
        
    }

    //format each route car list array in html
    
    foreach($rides_data as $key => $route_ridesdata){
        $select_options = '';
        $list_select_options = '';
        if(!is_numeric($key))continue;
        foreach($route_ridesdata['cars'] as $ridesdata){    
            $ride_filename = explode('/',$ridesdata['ride_img']);
            $ride_title = htmlentities($ridesdata['ride_type']);
            $ride_image = SITE_URL . 'img/ride_imgs/' . array_pop($ride_filename);
            $ride_desc = htmlentities($ridesdata['ride_desc']);
            if(array_search($ridesdata['ride_id'],$rides_ids) === false){
                $rides_ids[] = $ridesdata['ride_id'];
                $rides_url .= "<img id='uniq-car-type-id-{$ridesdata['ride_id']}' src='{$ride_image}' >";
            }
                
            $select_options .= "<img id='slider-car-item-id-{$ridesdata['ride_id']}' data-avail='0' data-numseats='{$ridesdata['num_seats']}' data-cfare='{$ridesdata['cfare_enabled']}' data-ppenabled='{$ridesdata['pp_enabled']}' data-ppstart='{$ridesdata['pp_start']}' data-ppend='{$ridesdata['pp_end']}' data-ppdays='{$ridesdata['pp_active_days']}' data-ppchargetype='{$ridesdata['pp_charge_type']}' data-ppchargevalue='{$ridesdata['pp_charge_value']}' data-img='{$ride_image}' data-cpk = '{$ridesdata['cost_per_km']}' data-cpm = '{$ridesdata['cost_per_minute']}' data-puc = '{$ridesdata['pickup_cost']}' data-doc='{$ridesdata['drop_off_cost']}' data-cc='{$ridesdata['cancel_cost']}' data-ncpk = '{$ridesdata['ncost_per_km']}' data-ncpm = '{$ridesdata['ncost_per_minute']}' data-npuc = '{$ridesdata['npickup_cost']}' data-ndoc='{$ridesdata['ndrop_off_cost']}' data-ncc='{$ridesdata['ncancel_cost']}' data-ind='{$ridesdata['init_distance']}' data-nind='{$ridesdata['init_distance_n']}' data-rideid='{$ridesdata['ride_id']}' data-ridedesc='{$ride_desc}' data-title='{$ride_title}' data-wt='{$ridesdata['wait_time']} data-wcpmr='{$ridesdata['wait_cost_per_minute']}' data-nwt='{$ridesdata['nwait_time']} data-nwcpmr='{$ridesdata['nwait_cost_per_minute']}' data-hren='{$ridesdata['hr_enabled']}' data-hrcph='{$ridesdata['hr_cph']}' data-hrdist='{$ridesdata['hr_dist']}' data-nhrcph='{$ridesdata['nhr_cph']}' data-nhrdist='{$ridesdata['nhr_dist']}'  style='width:100px;margin-right:auto;margin-left:auto;' class='slider-car-item' src='{$ride_image}' />";

            $list_select_options .= "<div data-avail='0' data-numseats='{$ridesdata['num_seats']}' data-cfare='{$ridesdata['cfare_enabled']}' data-ppenabled='{$ridesdata['pp_enabled']}' data-ppstart='{$ridesdata['pp_start']}' data-ppend='{$ridesdata['pp_end']}' data-ppdays='{$ridesdata['pp_active_days']}' data-ppchargetype='{$ridesdata['pp_charge_type']}' data-ppchargevalue='{$ridesdata['pp_charge_value']}' data-img='{$ride_image}' data-cpk = '{$ridesdata['cost_per_km']}' data-cpm = '{$ridesdata['cost_per_minute']}' data-puc = '{$ridesdata['pickup_cost']}' data-doc='{$ridesdata['drop_off_cost']}' data-cc='{$ridesdata['cancel_cost']}' data-ncpk = '{$ridesdata['ncost_per_km']}' data-ncpm = '{$ridesdata['ncost_per_minute']}' data-npuc = '{$ridesdata['npickup_cost']}' data-ndoc='{$ridesdata['ndrop_off_cost']}' data-ncc='{$ridesdata['ncancel_cost']}' data-ind='{$ridesdata['init_distance']}' data-nind='{$ridesdata['init_distance_n']}' data-rideid='{$ridesdata['ride_id']}' data-ridedesc='{$ride_desc}' data-title='{$ride_title}' data-wt='{$ridesdata['wait_time']} data-wcpmr='{$ridesdata['wait_cost_per_minute']}' data-nwt='{$ridesdata['nwait_time']} data-nwcpmr='{$ridesdata['nwait_cost_per_minute']}' data-hren='{$ridesdata['hr_enabled']}' data-hrcph='{$ridesdata['hr_cph']}' data-hrdist='{$ridesdata['hr_dist']}' data-nhrcph='{$ridesdata['nhr_cph']}' data-nhrdist='{$ridesdata['nhr_dist']}' id='list-car-item-{$ridesdata['ride_id']}' class='car-list-items' style='display:flex;flex-wrap: nowrap;align-items: center;justify-content: space-around;border-top: thin solid lightgrey;height:70px;box-sizing:border-box;'> 
                                        <div style='width:60%;padding:10px 0 10px 10px;display:flex;flex-wrap: nowrap;'>
                                            <div><img id='list-car-img-{$ridesdata['ride_id']}' class='list-car-img' style='width:64px; padding: 0 10px;transition:all 0.5s' src='$ride_image' /></div>
                                            <div style='margin-left: 20px;overflow:hidden;'>
                                                <p id='list-car-name-{$ridesdata['ride_id']}' class='list-car-name' style='font-weight: bold;text-overflow: ellipsis;overflow: hidden;font-size: 16px;display: inline-block;width: 100%;padding: 0;margin: 0 0 5px 0;white-space: nowrap;'>{$ride_title}</p>
                                                <div style='display: flex;align-items: center;'><ons-icon style='color:#000000' size='12px' icon='fa-users'> </ons-icon><span id='list-ride-capacity-{$ridesdata['ride_id']}' style='margin-left:2px;font-size:14px;color:#000000'>4</span> <span class='list-ride-avail-status-ind' id='list-ride-availability-{$ridesdata['ride_id']}' style='padding: 3px 5px;text-overflow: ellipsis;color: #000000;font-size: 12px;white-space: nowrap;overflow: hidden;'>Busy</span> <ons-icon id='list-ride-info-{$ridesdata['ride_id']}' style='color:#000000;display:none;' size='16px' icon='fa-info-circle'> </ons-icon></div>
                                            </div>
                                        </div>
                                        <div class='list-bookride-cost-container' style='width:40%;padding:10px 15px 10px 5px;overflow:hidden;'>
                                            <p id = 'list-bookride-cost-{$ridesdata['ride_id']}' style='font-weight:bold;margin: 0 0 5px 0;white-space: nowrap;text-overflow: ellipsis;overflow: hidden;font-size: 17px;text-align: right;'>N1000</p>
                                            <p id = 'list-bookride-cost-full-{$ridesdata['ride_id']}' style='margin: 3px 0;text-overflow: ellipsis;overflow: hidden;font-size: 16px;color:#9e9e9e;text-decoration: line-through;text-align: right;visibility: hidden;'>$444.00</p>
                                        </div>
                                    </div>";
            
        }

        $rides_data[$key]['cars_html'] = $select_options;
        $rides_data[$key]['list_cars_html'] = $list_select_options;
        
    }


    
    

        
    $rides_data['city'] = $city_select_options;
    $rides_data['state'] = $state_select_options;
    $rides_data['preloadrides'] = $rides_url;
    $rides_data['zones'] = $zones_data;

    if(PAYMENT_TYPE == 2){ //cash and wallet
        $rides_data['payment_options'] = "<option value='1'>" . __("Cash") ."</option><option value='2'>" . __("Wallet") ."</option>";
        $rides_data['payment_options_data'] = [['name' => __("Cash"), "id" => 1], ['name' => __("Wallet"), "id" => 2]];
    }elseif(PAYMENT_TYPE == 1){ //wallet only
        $rides_data['payment_options'] = "<option value='2'>" . __("Wallet") . "</option>";
        $rides_data['payment_options_data'] = [['name' => __("Wallet"), "id" => 2]];
        
    }else{ //cash only
        $rides_data['payment_options'] = "<option value='1'>" . __("Cash") . "</option>";
        $rides_data['payment_options_data'] = [['name' => __("Cash"), "id" => 1]];
    }
    $rides_data['nighttime'] = array('start_hour'=>NIGHT_START, 'end_hour'=>NIGHT_END);
    $data_array = array("success"=>1,'result'=>$rides_data);


    
    return $data_array; 
    


}




function getstateroutetariffs(){

    $tariff_data = [];
    $rides_data = [];
	$state_str = mysqli_real_escape_string($GLOBALS['DB'], $_GET['state_str']);
	$state_str = "%{$state_str}%";
       
    
    
    $query = sprintf('SELECT *,%1$stbl_routes.id AS route_id  FROM %1$stbl_routes
    INNER JOIN %1$stbl_currencies ON %1$stbl_currencies.id = %1$stbl_routes.city_currency_id
    INNER JOIN %1$stbl_rides_tariffs ON %1$stbl_rides_tariffs.routes_id = %1$stbl_routes.id
    INNER JOIN %1$stbl_rides ON %1$stbl_rides_tariffs.ride_id = %1$stbl_rides.id
    WHERE %1$stbl_rides.avail = 1 AND %1$stbl_routes.r_scope = 1 AND %1$stbl_routes.r_title LIKE "%3$s" ORDER BY %1$stbl_routes.r_title, %1$stbl_rides.id ASC LIMIT 50', DB_TBL_PREFIX,$_SESSION['uid'],$state_str);


    if(empty($state_str)){
        $query = sprintf('SELECT *,%1$stbl_routes.id AS route_id  FROM %1$stbl_routes
        INNER JOIN %1$stbl_currencies ON %1$stbl_currencies.id = %1$stbl_routes.city_currency_id
        INNER JOIN %1$stbl_rides_tariffs ON %1$stbl_rides_tariffs.routes_id = %1$stbl_routes.id
        INNER JOIN %1$stbl_rides ON %1$stbl_rides_tariffs.ride_id = %1$stbl_rides.id
        WHERE %1$stbl_rides.avail = 1 AND %1$stbl_routes.r_scope = 1 ORDER BY %1$stbl_routes.r_title, %1$stbl_rides.id ASC LIMIT 50', DB_TBL_PREFIX,$_SESSION['uid'],$state_str);
    }


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                $tariff_data[] = $row;
            }
                            
        }    
    }


    mysqli_close($GLOBALS['DB']);

    
    $data_array = [];
    $city_select_options = '';
    $state_select_options = '';
    $tariff_ids = [];
    $sel_route_id = !empty($_POST['sel_route_id']) ? $_POST['sel_route_id'] : 0;
    $sel_route_name = !empty($_POST['sel_route_name']) ? $_POST['sel_route_name'] : 0;
    $count = 0;
    $route_selected = '';
    $rides_ids = [];
    $rides_url = '';
    
    //sort rides tarif data
    foreach($tariff_data as $tariffdata){
        $count ++;
        $rides_data[$tariffdata['route_id']]['r_id'] = $tariffdata['route_id'];
        
        if(empty($sel_route_id)){ 
            if($count == 1){
               //    $route_selected = "checked";
            }else{
                $route_selected = "";
            }    
        }else{
            if($sel_route_id == $tariffdata['route_id'] && $sel_route_name == $tariffdata['r_title']){
                //$route_selected = "checked";
                $rides_data['route-exists'] = 1;
            }else{
                $route_selected = "";
            }
            
        }

        if(array_search($tariffdata['route_id'],$tariff_ids) === false){
            if($tariffdata['r_scope'] == 0){
                $tariff_ids[] = $tariffdata['route_id'];
                $rides_data['city_name'][] = $tariffdata['r_title'];
                $rides_data['city_id'][] = $tariffdata['route_id'];
                $route_name_variable = "'" . $tariffdata['r_title'] . "'"; 
                $city_select_options .= "<ons-list-item tappable class='city-route-list' onclick = routecityitemselected({$tariffdata['route_id']}) data-routename='{$tariffdata['r_title']}' id=route-sel-{$tariffdata['route_id']} ><label class='left'><ons-radio {$route_selected} name='city-route' id='radio-sel-{$tariffdata['route_id']}' input-id='radio-{$tariffdata['route_id']}'></ons-radio></label><label for='radio-{$tariffdata['route_id']}' class='center'>{$tariffdata['r_title']}</label></ons-list-item>";
            }else{
                $tariff_ids[] = $tariffdata['route_id'];
                $rides_data['state_name'][] = $tariffdata['r_title'];
                $rides_data['state_id'][] = $tariffdata['route_id'];
                $route_name_variable = "'" . $tariffdata['r_title'] . "'"; 
                $state_select_options .= "<ons-list-item data-plng='{$tariffdata['pick_lng']}' data-plat='{$tariffdata['pick_lat']}' data-dlng='{$tariffdata['drop_lng']}' data-dlat='{$tariffdata['drop_lat']}' data-pus='{$tariffdata['pick_name']}' data-dos='{$tariffdata['drop_name']}' tappable class='state-route-list' onclick = routestateitemselected({$tariffdata['route_id']}) data-routename='{$tariffdata['r_title']}' id=route-sel-{$tariffdata['route_id']}><div class='left'><ons-icon icon='fa-random' size='18px' style='color: rgb(0, 115, 255);'></ons-icon></div><div class='center'>{$tariffdata['r_title']}</div></ons-list-item>";
            }
        }     

        $rides_data[$tariffdata['route_id']]['cars'][] = $tariffdata;
        
    }

    //format each route car list array in html
    
    foreach($rides_data as $key => $route_ridesdata){
        $select_options = '';
        $list_select_options = '';
        if(!is_numeric($key))continue;
        foreach($route_ridesdata['cars'] as $ridesdata){    
            $ride_filename = explode('/',$ridesdata['ride_img']);
            $ride_title = htmlentities($ridesdata['ride_type']);
            $ride_image = SITE_URL . 'img/ride_imgs/' . array_pop($ride_filename);
            $ride_desc = htmlentities($ridesdata['ride_desc']);
            if(array_search($ridesdata['ride_id'],$rides_ids) === false){
                $rides_ids[] = $ridesdata['ride_id'];
                $rides_url .= "<img id='uniq-car-type-id-{$ridesdata['ride_id']}' src='{$ride_image}' >";
            }
                
            $select_options .= "<img id='slider-car-item-id-{$ridesdata['ride_id']}' data-avail='0' data-numseats='{$ridesdata['num_seats']}' data-cfare='{$ridesdata['cfare_enabled']}' data-ppenabled='{$ridesdata['pp_enabled']}' data-ppstart='{$ridesdata['pp_start']}' data-ppend='{$ridesdata['pp_end']}' data-ppdays='{$ridesdata['pp_active_days']}' data-ppchargetype='{$ridesdata['pp_charge_type']}' data-ppchargevalue='{$ridesdata['pp_charge_value']}' data-img='{$ride_image}' data-cpk = '{$ridesdata['cost_per_km']}' data-cpm = '{$ridesdata['cost_per_minute']}' data-puc = '{$ridesdata['pickup_cost']}' data-doc='{$ridesdata['drop_off_cost']}' data-cc='{$ridesdata['cancel_cost']}' data-ncpk = '{$ridesdata['ncost_per_km']}' data-ncpm = '{$ridesdata['ncost_per_minute']}' data-npuc = '{$ridesdata['npickup_cost']}' data-ndoc='{$ridesdata['ndrop_off_cost']}' data-ncc='{$ridesdata['ncancel_cost']}' data-ind='{$ridesdata['init_distance']}' data-nind='{$ridesdata['init_distance_n']}' value='{$ridesdata['ride_id']}' data-rideid='{$ridesdata['ride_id']}' data-ridedesc='{$ride_desc}' data-title='{$ride_title}' data-wt='{$ridesdata['wait_time']} data-wcpmr='{$ridesdata['wait_cost_per_minute']}' data-nwt='{$ridesdata['nwait_time']} data-nwcpmr='{$ridesdata['nwait_cost_per_minute']}' data-hren='{$ridesdata['hr_enabled']}' data-hrcph='{$ridesdata['hr_cph']}' data-hrdist='{$ridesdata['hr_dist']}' data-nhrcph='{$ridesdata['nhr_cph']}' data-nhrdist='{$ridesdata['nhr_dist']}' style='width:100px;margin-right:auto;margin-left:auto;' class='slider-car-item' src='{$ride_image}' />";

            $list_select_options .= "<div data-avail='0' data-numseats='{$ridesdata['num_seats']}' data-cfare='{$ridesdata['cfare_enabled']}' data-ppenabled='{$ridesdata['pp_enabled']}' data-ppstart='{$ridesdata['pp_start']}' data-ppend='{$ridesdata['pp_end']}' data-ppdays='{$ridesdata['pp_active_days']}' data-ppchargetype='{$ridesdata['pp_charge_type']}' data-ppchargevalue='{$ridesdata['pp_charge_value']}' data-img='{$ride_image}' data-cpk = '{$ridesdata['cost_per_km']}' data-cpm = '{$ridesdata['cost_per_minute']}' data-puc = '{$ridesdata['pickup_cost']}' data-doc='{$ridesdata['drop_off_cost']}' data-cc='{$ridesdata['cancel_cost']}' data-ncpk = '{$ridesdata['ncost_per_km']}' data-ncpm = '{$ridesdata['ncost_per_minute']}' data-npuc = '{$ridesdata['npickup_cost']}' data-ndoc='{$ridesdata['ndrop_off_cost']}' data-ncc='{$ridesdata['ncancel_cost']}' data-ind='{$ridesdata['init_distance']}' data-nind='{$ridesdata['init_distance_n']}' data-rideid='{$ridesdata['ride_id']}' data-ridedesc='{$ride_desc}' data-title='{$ride_title}' data-wt='{$ridesdata['wait_time']} data-wcpmr='{$ridesdata['wait_cost_per_minute']}' data-nwt='{$ridesdata['nwait_time']} data-nwcpmr='{$ridesdata['nwait_cost_per_minute']}' data-hren='{$ridesdata['hr_enabled']}' data-hrcph='{$ridesdata['hr_cph']}' data-hrdist='{$ridesdata['hr_dist']}' data-nhrcph='{$ridesdata['nhr_cph']}' data-nhrdist='{$ridesdata['nhr_dist']}' id='list-car-item-{$ridesdata['ride_id']}' class='car-list-items' style='display:flex;flex-wrap: nowrap;align-items: center;justify-content: space-around;border-top: thin solid lightgrey;height:70px;box-sizing:border-box;'> 
                                        <div style='width:60%;padding:10px 0 10px 10px;display:flex;flex-wrap: nowrap;'>
                                            <div><img id='list-car-img-{$ridesdata['ride_id']}' class='list-car-img' style='width:64px; padding: 0 10px;transition:all 0.5s' src='$ride_image' /></div>
                                            <div style='margin-left: 20px;overflow:hidden;'>
                                                <p id='list-car-name-{$ridesdata['ride_id']}' class='list-car-name' style='font-weight: bold;text-overflow: ellipsis;overflow: hidden;font-size: 16px;display: inline-block;width: 100%;padding: 0;margin: 0 0 5px 0;white-space: nowrap;'>{$ride_title}</p>
                                                <div style='display: flex;align-items: center;'><ons-icon style='color:#000000' size='12px' icon='fa-users'> </ons-icon><span id='list-ride-capacity-{$ridesdata['ride_id']}' style='margin-left:2px;font-size:14px;color:#000000'>4</span> <span class='list-ride-avail-status-ind' id='list-ride-availability-{$ridesdata['ride_id']}' style='padding: 3px 5px;text-overflow: ellipsis;color: #000000;font-size: 12px;white-space: nowrap;overflow: hidden;'>Busy</span> <ons-icon id='list-ride-info-{$ridesdata['ride_id']}' style='color:#000000;display:none;' size='16px' icon='fa-info-circle'> </ons-icon></div>
                                            </div>
                                        </div>
                                        <div class='list-bookride-cost-container' style='width:40%;padding:10px 15px 10px 5px;overflow:hidden;'>
                                            <p id = 'list-bookride-cost-{$ridesdata['ride_id']}' style='font-weight:bold;margin: 0 0 5px 0;white-space: nowrap;text-overflow: ellipsis;overflow: hidden;font-size: 17px;text-align: right;'>N1000</p>
                                            <p id = 'list-bookride-cost-full-{$ridesdata['ride_id']}' style='margin: 3px 0;text-overflow: ellipsis;overflow: hidden;font-size: 16px;color:#9e9e9e;text-decoration: line-through;text-align: right;visibility: hidden;'>$444.00</p>
                                        </div>
                                    </div>";
            
        }

        $rides_data[$key]['cars_html'] = $select_options;
        $rides_data[$key]['list_cars_html'] = $list_select_options;
        
    }


    
    

        
    $rides_data['city'] = $city_select_options;
    $rides_data['state'] = $state_select_options;
    $rides_data['preloadrides'] = $rides_url;

    if(PAYMENT_TYPE == 2){ //cash and wallet
        $rides_data['payment_options'] = "<option value='1'>" . __("Cash") ."</option><option value='2'>" . __("Wallet") ."</option>";
        $rides_data['payment_options_data'] = [['name' => __("Cash"), "id" => 1], ['name' => __("Wallet"), "id" => 2]];
    }elseif(PAYMENT_TYPE == 1){ //wallet only
        $rides_data['payment_options'] = "<option value='2'>" . __("Wallet") . "</option>";
        $rides_data['payment_options_data'] = [['name' => __("Wallet"), "id" => 2]];
        
    }else{ //cash only
        $rides_data['payment_options'] = "<option value='1'>" . __("Cash") . "</option>";
        $rides_data['payment_options_data'] = [['name' => __("Cash"), "id" => 1]];
    }

    $rides_data['nighttime'] = array('start_hour'=>NIGHT_START, 'end_hour'=>NIGHT_END);
    $data_array = array("success"=>1,'result'=>$rides_data,'query' => $query);


	  
    echo json_encode($data_array);
    exit;




}



/* function getgooglemapapikey(){
    
    $data_array = array("success"=>1,'api_key'=>GMAP_API_KEY_BACKEND);    
    echo json_encode($data_array); 
    exit;

} */

function getcallcenternum(){

    $data_array = array("success"=>1,'cc_num'=>CALL_CENTER_NUMBER);    
    echo json_encode($data_array); 
    exit;

}





function getwalletinfo(){

    $user_wallet_details = [];

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    //get wallet data 
    $template = '';
    $template2 = ''; //stores onsenui formated earnings data
    $transaction_data_sort = [];
    $transaction_data_sort['debit-data'] = [];
    $transaction_data_sort['funding-data'] = [];

    $query = sprintf('SELECT *,%1$stbl_wallet_transactions.cur_exchng_rate AS exchng_rate,%1$stbl_bookings.cur_symbol AS b_cur_symbol,%1$stbl_wallet_transactions.cur_symbol AS t_cur_symbol,%1$stbl_wallet_transactions.transaction_id AS transaction_id,DATE(%1$stbl_wallet_transactions.transaction_date) AS transaction_date,%1$stbl_wallet_transactions.transaction_date AS transaction_dates FROM %1$stbl_wallet_transactions 
    LEFT JOIN %1$stbl_bookings ON %1$stbl_bookings.id = %1$stbl_wallet_transactions.book_id
    LEFT JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_wallet_transactions.user_id
    LEFT JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_users.route_id
    LEFT JOIN %1$stbl_currencies ON %1$stbl_currencies.id = %1$stbl_routes.city_currency_id
    WHERE %1$stbl_wallet_transactions.user_id = "%2$d" AND %1$stbl_wallet_transactions.user_type = 0 ORDER BY %1$stbl_wallet_transactions.transaction_date DESC LIMIT 0,300 ', DB_TBL_PREFIX,$_SESSION['uid']); //Get required user information from DB


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                
                if($row['type'] == 2 || $row['type'] == 3){
                    $transaction_data_sort['debit-data'][$row['transaction_date']]['date'] = $row['transaction_date'];
                    $transaction_data_sort['debit-data'][$row['transaction_date']]['data'][] = $row;
                }else{
                    $transaction_data_sort['funding-data'][$row['transaction_date']]['date'] = $row['transaction_date'];
                    $transaction_data_sort['funding-data'][$row['transaction_date']]['data'][] = $row;
                }

            }
                            
        }    
    }

    

    

    //format funding data for display on app
    foreach($transaction_data_sort['funding-data'] as $transactiondatasort){

        if(!empty($transactiondatasort['data'])){
                $day_num = date('j',strtotime($transactiondatasort['date'] . " UTC"));
                $month_name = date('F',strtotime($transactiondatasort['date'] . " UTC"));                
                $t_date_format = $day_num . " " . __($month_name);
                $template .= "<ons-list-header style='font-size: 14px;font-weight:bold;margin:20px 0'>{$t_date_format}</ons-list-header>";
                
    
                foreach($transactiondatasort['data'] as $transaction_d){
                    $transaction_time = date('g:i A',strtotime($transaction_d['transaction_dates'] . " UTC"));
                    $transaction_id_upper = strtoupper($transaction_d['transaction_id']);
                    $wallet_balance_converted =  (float) $transaction_d['wallet_balance'] * (float) $transaction_d['exchng_rate'];
                    $wallet_balance_converted = number_format((float) $wallet_balance_converted, 2);
                    $indicate_credit_debit = "<ons-icon icon='fa-circle' size='14px' style='color: green; font-size: 14px;'></ons-icon>";
                    /* $status = '';
                    switch($transaction_d['status']){
                        case 'Approved':
                        $status = "<span style='color:lightgreen'>Success</span>";
                        break;
    
                        case 'Pending':
                        $status = "<span style='color:purple'>Pending</span>";
                        break;
    
                        default:
                        $status = "<span style='color:red'>Failed</span>";
                        break;
    
    
                    } */
                    
                    
                        
                        $template .= "<ons-list-item modifier='longdivider'>
                            
                                        <div class='center'>
                                            <div style='width:100%;margin-bottom:15px;'>{$indicate_credit_debit} <span class='list-item__title'>{$transaction_time}</span> </div>
                                            <span class='list-item__subtitle' style='margin-bottom:5px;'><span style='color:#000;font-size:20px;'>" . __("Amount") . ": {$transaction_d['t_cur_symbol']}{$transaction_d['amount']}</span></span>
                                            <span class='list-item__subtitle'><span style='color:#777'><ons-icon icon='fa-square' size='8px' style='vertical-align: bottom;color: black; font-size: 10px;'></ons-icon> " . __("Description") . ": {$transaction_d['desc']}</span></span>
                                            <span class='list-item__subtitle'><span style='color:#777'><ons-icon icon='fa-square' size='8px' style='vertical-align: bottom;color: black; font-size: 10px;'></ons-icon> " . __("Transaction ID") . ":{$transaction_id_upper} </span></span>                                            
                                            <span class='list-item__subtitle'><span style='color:#777'><ons-icon icon='fa-square' size='8px' style='vertical-align: bottom;color: black; font-size: 10px;'></ons-icon> " . __("Wallet Balance") .":</span> {$transaction_d['t_cur_symbol']}{$wallet_balance_converted}</span>
                                        </div>
                                    
                                    </ons-list-item>";
                   

                }
    
        }

    }



    //format for display on app
    foreach($transaction_data_sort['debit-data'] as $transactiondatasort){

        if(!empty($transactiondatasort['data'])){ 
                $day_num = date('j',strtotime($transactiondatasort['date'] . " UTC"));
                $month_name = date('F',strtotime($transactiondatasort['date'] . " UTC"));                
                $t_date_format = $day_num . " " . __($month_name);

                $template2 .= "<ons-list-header style='font-size: 14px;font-weight:bold;margin:20px 0;'>{$t_date_format}</ons-list-header>";
    
                foreach($transactiondatasort['data'] as $transaction_d){
                    $transaction_time = date('g:i A',strtotime($transaction_d['transaction_dates'] . " UTC"));
                    $transaction_id_upper = strtoupper($transaction_d['transaction_id']);
                    $wallet_balance_converted =  (float) $transaction_d['wallet_balance'] * (float) $transaction_d['exchng_rate'];
                    $wallet_balance_converted = number_format((float) $wallet_balance_converted, 2);
                    $indicate_credit_debit = $transaction_d['type'] == 2 ? "<ons-icon icon='fa-circle' size='14px' style='color: green; font-size: 14px;'></ons-icon>" : "<ons-icon icon='fa-circle' size='14px' style='color: red; font-size: 14px;'></ons-icon>";
                    /* $status = '';
                    switch($transaction_d['status']){
                        case 'Approved':
                        $status = "<span style='color:lightgreen'>Success</span>";
                        break;
    
                        case 'Pending':
                        $status = "<span style='color:purple'>Pending</span>";
                        break;
    
                        default:
                        $status = "<span style='color:red'>Failed</span>";
                        break;
    
    
                    } */
                    
                    
                        $booking_fare = '';
                        $booking_id = '';
                        if($transaction_d['book_id']){
                            $booking_id = "#".str_pad($transaction_d['book_id'] , 5, '0', STR_PAD_LEFT);
                            $booking_fare = $transaction_d['b_cur_symbol'] . $transaction_d['paid_amount'];  
                        }else{

                            $booking_id = "N/A";
                            $booking_fare = "N/A";  

                        }
                        $booking_id = !empty($transaction_d['book_id']) ? "#".str_pad($transaction_d['book_id'] , 5, '0', STR_PAD_LEFT) : "N/A";
                        $transaction_id_upper = strtoupper($transaction_d['transaction_id']);



                        $template2 .= "<ons-list-item modifier='longdivider'>
                            
                                        <div class='center'>
                                            <div style='width:100%;margin-bottom:15px;'>{$indicate_credit_debit} <span class='list-item__title'>{$transaction_time}</span> </div>
                                            <span class='list-item__subtitle' style='margin-bottom:5px;'><span style='color:#000;font-size:20px;'>" . __("Amount") . ": {$transaction_d['t_cur_symbol']}{$transaction_d['amount']}</span></span>
                                            <span class='list-item__subtitle'><span style='color:#777'><ons-icon icon='fa-square' size='8px' style='vertical-align: bottom;color: black; font-size: 10px;'></ons-icon> " . __("Description") . ": {$transaction_d['desc']}</span></span>
                                            <span class='list-item__subtitle'><span style='color:#777'><ons-icon icon='fa-square' size='8px' style='vertical-align: bottom;color: black; font-size: 10px;'></ons-icon> " . __("Transaction ID") . ":{$transaction_id_upper} </span></span>                                            
                                            <span class='list-item__subtitle'><span style='color:#777'><ons-icon icon='fa-square' size='8px' style='vertical-align: bottom;color: black; font-size: 10px;'></ons-icon> " . __("Booking ID") . ":</span> {$booking_id} </span>
                                            <span class='list-item__subtitle'><span style='color:#777'><ons-icon icon='fa-square' size='8px' style='vertical-align: bottom;color: black; font-size: 10px;'></ons-icon> " . __("Booking Fare") . ":</span> {$booking_fare} </span>
                                            <span class='list-item__subtitle'><span style='color:#777'><ons-icon icon='fa-square' size='8px' style='vertical-align: bottom;color: black; font-size: 10px;'></ons-icon> " . __("Wallet Balance") . ":</span> {$transaction_d['t_cur_symbol']}{$wallet_balance_converted}</span>
                                        </div>
                                    
                                    </ons-list-item>";
                    

                }
    
        }

    }
    
    //Get wallet amount

    $query = sprintf('SELECT wallet_amount,reward_points FROM %stbl_users WHERE user_id = "%d"', DB_TBL_PREFIX, $_SESSION['uid']); //Get required user information from DB


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $user_wallet_details = mysqli_fetch_assoc($result);
        }
        else{
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); //invalid record
            exit;

        }
        
    }
    else{ //No record matching the USER ID was found in DB. Show view to notify user

        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;
    }

    $_SESSION['wallet_amt'] = $user_wallet_details['wallet_amount'];
    $_SESSION['reward_points'] = $user_wallet_details['reward_points'];

    
    $data_array = array("success"=>1,'reward_points'=>$_SESSION['reward_points'],'wallet_amt'=>$_SESSION['wallet_amt'], 'wallet_history' => $template,'wallet_debit' => $template2);    
    echo json_encode($data_array); 
    exit;

}


function redeempoints(){

    //get this users points value
    $user_data = [];
    $query = sprintf('SELECT * FROM %1$stbl_users WHERE user_id = %2$d', DB_TBL_PREFIX, $_SESSION['uid']);
    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $user_data = mysqli_fetch_assoc($result);
        }else{
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); //database error
            exit;
        }

    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;
    }
    

    //get points data
    $reward_points_data = [];
    $query = sprintf('SELECT * FROM %1$stbl_reward_points WHERE id = %2$d', DB_TBL_PREFIX, 1);
    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $reward_points_data = mysqli_fetch_assoc($result);
        }else{
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); //database error
            exit;
        }

    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;
    }

    if($user_data['reward_points'] < $reward_points_data['min_points_redeemable']){
        $error = array("error"=>__("You need to have up to {---1} reward points to redeem. Increase your reward points by taking more trips",[$reward_points_data['min_points_redeemable']]));
        echo json_encode($error); //database error
        exit;
    }

    $point_val = $user_data['reward_points'] * $reward_points_data['points_to_cur_conv'];

    //add redeemed points to user wallet

    $query = sprintf('UPDATE %1$stbl_users SET wallet_amount = wallet_amount + %2$f, reward_points = 0, reward_points_redeemed = reward_points_redeemed + %2$f WHERE user_id = %3$d', DB_TBL_PREFIX, $point_val, $_SESSION['uid']);
    if(!$result = mysqli_query($GLOBALS['DB'], $query)){        
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;
    }


    //get default currency data
    $default_currency_data = [];
    $query = sprintf('SELECT * FROM %stbl_currencies WHERE `default` = 1', DB_TBL_PREFIX);
    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $default_currency_data = mysqli_fetch_assoc($result);
        }
    }



    //Add this transaction to wallet transactions database table

    $transaction_id = crypto_string(); //generate a random string as transaction ID

    $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
    '("%s","%s","%s","%s","%s","%s","%d","%d","%s","%d","%s")', 
    DB_TBL_PREFIX,
    $default_currency_data['symbol'],
    $default_currency_data['exchng_rate'],
    $default_currency_data['iso_code'],
    $transaction_id,
    $point_val,
    $user_data['wallet_amount'],
    $_SESSION['uid'],
    0,
    __("Redeemed reward points funds"), 
    0,
    gmdate('Y-m-d H:i:s', time())

    );

    $result = mysqli_query($GLOBALS['DB'], $query);


    
    $resp = array("success"=>1);
    echo json_encode($resp); //database error
    exit;



}





function newbooking(){


    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $tariff_data = [];
    $num_of_pending_booking = 0;
    $booking_data = [];

    $paddress = mysqli_real_escape_string($GLOBALS['DB'], $_GET['paddress']);
    $daddress = mysqli_real_escape_string($GLOBALS['DB'], $_GET['daddress']);
    $plng = mysqli_real_escape_string($GLOBALS['DB'], $_GET['plng']);
    $plat = mysqli_real_escape_string($GLOBALS['DB'], $_GET['plat']);
    $dlng = mysqli_real_escape_string($GLOBALS['DB'], $_GET['dlng']);
    $dlat = mysqli_real_escape_string($GLOBALS['DB'], $_GET['dlat']);
    $payment_type = (int) $_GET['p_type'];
    $pdatetime = mysqli_real_escape_string($GLOBALS['DB'], $_GET['pdatetime']);
    $ride_id = (int) $_GET['ride_id'];
    $route_id = (int) $_GET['route_id'];
    $scheduled_booking = (int) $_GET['scheduled'];
    $price = $_GET['booking_price'];
    $price_hash = mysqli_real_escape_string($GLOBALS['DB'], $_GET['b_token']);
    $coupon_code = mysqli_real_escape_string($GLOBALS['DB'], $_GET['coupon_code']);
    $multidestination = (int) $_GET['multidestination'];
    $waypoints_data = $_GET['waypoints'];
    $est_trip_distance = (float) $_GET['trip_distance'];
    $est_trip_duration = (float) $_GET['trip_duration'];
    $service_mode = (int) $_GET['service_mode'];
    $hourly_rate_enabled = (int) $_GET['hourly_rate_enabled'];
    $hourly_rate_num_hours = (int) $_GET['hourly_rate_num_hours'];

        
    //check if fare wasnt tampered with by comparing hashes    
    if(md5('projectgics'.$price) !== $price_hash){
        $error = array("error"=>"Error booking your ride. Price mismatch. - " . $price_hash);
        echo json_encode($error); //database error
        exit;
    }

    $price = (float) $_GET['booking_price'];

    if($service_mode == 1){
        if(!empty($hourly_rate_enabled)){
            $price = $price * $hourly_rate_num_hours;
        }else{
            $hourly_rate_num_hours = 0;
        }
        
    }       

    
    //check if user set an old date    
    /* if(strtotime($pdatetime) < time() - 300){
        $error = array("error"=>"Error booking your ride.The booking time has passed. Please go back and rebook or select a later time.");
        echo json_encode($error); //database error
        exit;
    } */

    //check if user set an old date    
    if($scheduled_booking && strtotime($pdatetime) < time() + 1200){ //scheduled ride must be above 1200 seconds i.e 20 Minutes
        $error = array("error"=>__("Please set a time atleast 20 minutes ahead for scheduled ride"));
        echo json_encode($error); //database error
        exit;
    }

    //format date time
    if($scheduled_booking){
        $pdatetime = gmdate('Y-m-d H:i:s',strtotime($pdatetime)); //scheduled booking
    }else{
        $pdatetime = gmdate('Y-m-d H:i:s',time()); //instant booking
    }
        


    //Check for number of pending bookings;
    $query = sprintf('SELECT COUNT(*) FROM %1$stbl_bookings WHERE %1$stbl_bookings.user_id = %2$d AND (%1$stbl_bookings.status = 0 OR %1$stbl_bookings.status = 1)', DB_TBL_PREFIX, $_SESSION['uid']);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $row = mysqli_fetch_assoc($result); 
            $num_of_pending_booking = $row['COUNT(*)'];
            if($num_of_pending_booking >= USER_MAX_NUM_PEND_BOOKINGS){
                
                $error = array("error"=>__("You cannot have more than {---1} uncompleted bookings. Please cancel some bookings",[USER_MAX_NUM_PEND_BOOKINGS]));
                echo json_encode($error); //database error
                exit;
            }
        }
        mysqli_free_result($result);

    }

    

    //Check for previous pending or onride bookings within MIN_BOOKING_INTERVAL; the user set pickup time of this new booking;
    $query = sprintf('SELECT * FROM %1$stbl_bookings WHERE %1$stbl_bookings.user_id = %2$d AND (%1$stbl_bookings.status = 0 OR %1$stbl_bookings.status = 1) ORDER BY %1$stbl_bookings.id DESC LIMIT 1', DB_TBL_PREFIX, $_SESSION['uid']);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $booking_data = mysqli_fetch_assoc($result);    
        }
        mysqli_free_result($result);
    }

    if(!empty($booking_data)){
        $last_booking_pickup_datetime_seconds = strtotime($booking_data['pickup_datetime'] . ' UTC');
        $new_booking_pickup_datetime_seconds = strtotime($pdatetime .' UTC');
        $time_passed =  abs($new_booking_pickup_datetime_seconds - $last_booking_pickup_datetime_seconds);
        if(($booking_data['status'] == 0 || $booking_data['status'] == 1) && $time_passed < intval(MIN_BOOKING_INTERVAL)){
            
            $error = array("error"=>__("You currently have an on-going or pending ride within the set pickup time"));
            echo json_encode($error); //database error
            exit;

        }
        
    }



    //get user documents and check if user has unapproved documents
    if(empty(DEMO)){
        $query = sprintf('SELECT *,%1$stbl_documents.id AS d_id FROM %1$stbl_documents
        LEFT JOIN %1$stbl_users_documents ON %1$stbl_users_documents.doc_id = %1$stbl_documents.id AND %1$stbl_users_documents.u_id = %2$d AND %1$stbl_users_documents.u_type = 0
        WHERE %1$stbl_documents.doc_user = 0 AND %1$stbl_documents.status = 1', DB_TBL_PREFIX, $_SESSION['uid']);

        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                while($row = mysqli_fetch_assoc($result)){
                    if((empty($row['doc_city']) || $row['doc_city'] == $route_id) && $row['u_doc_status'] != 3){
                        $error = array("error"=>__("You have documents that are yet to be approved. Please ensure all your submitted documents are valid and approved"));
                        echo json_encode($error); //database error
                        exit;
                    }
                }
            }
        }

    }

    



    
    //Get tariff info fo this ride including currency
    $query = sprintf('SELECT * FROM %1$stbl_rides_tariffs 
    INNER JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_rides_tariffs.routes_id
    LEFT JOIN %1$stbl_currencies ON %1$stbl_currencies.id = %1$stbl_routes.city_currency_id
    WHERE %1$stbl_rides_tariffs.routes_id = "%2$d" AND %1$stbl_rides_tariffs.ride_id = "%3$d"', DB_TBL_PREFIX, $route_id,$ride_id); //Get required user information from DB

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $tariff_data = mysqli_fetch_assoc($result);                    
        }else{
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); //database error
            exit;
        }
        
    }
    else{ //No record matching the USER ID was found in DB. Show view to notify user

        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;
    }

    $currency_symbol = isset($tariff_data['symbol']) ? $tariff_data['symbol'] : '₦';
    $currency_exchange_rate = isset($tariff_data['exchng_rate']) ? $tariff_data['exchng_rate'] : 1;
    $currency_code = isset($tariff_data['iso_code']) ? $tariff_data['iso_code'] : 'NGN';
    $alternate_vehicles = !empty($tariff_data['alt_cars']) ? $tariff_data['alt_cars'] : '';
    $ride_sharing = !empty($tariff_data['rshare_enabled']) ? 1 : 0;

    //check if route is an inter state or intra city and set dispatch mode accordingly

    $dispatch_mode = 0; //auto dispatch for all rides through app

    /* if($tariff_data['r_scope'] == 0){
        $dispatch_mode = 0; //auto dispatch for intra city rides
    }else{
        $dispatch_mode = 1; //manual dispatch for inter state rides
    } */



    //check driver availability at rider's pickup location
    
    /* if($tariff_data['r_scope'] == 0 && empty($scheduled_booking)){
        $location_info_age = gmdate('Y-m-d H:i:s', time() - LOCATION_INFO_VALID_AGE);
        $driver_available = 0;

        $query = sprintf('SELECT %1$stbl_drivers.driver_id,%1$stbl_drivers.push_notification_token,%1$stbl_driver_location.*,%1$stbl_drivers.route_id, %1$stbl_drivers.ride_id FROM %1$stbl_driver_location 
        INNER JOIN %1$stbl_drivers ON %1$stbl_driver_location.driver_id = %1$stbl_drivers.driver_id
        WHERE %1$stbl_drivers.route_id = %3$d AND %1$stbl_drivers.ride_id = %4$d AND %1$stbl_drivers.is_activated = 1 AND %1$stbl_drivers.available = 1 AND %1$stbl_driver_location.location_date > "%2$s"', DB_TBL_PREFIX,$location_info_age, $route_id, $ride_id);

        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                
                while($row = mysqli_fetch_assoc($result)){
                    
                    
                    //Check for driver close to rider's pickup
                    $distance = distance($plat,$plng,$row['lat'],$row['long']);
                    
                    
                    if($distance <= MAX_DRIVER_DISTANCE){
                        $driver_available = 1;
                        break;
                    }
                    
                    
                }

                            
                mysqli_free_result($result);

            }
        
        }

        if(!$driver_available){
            $error = array("error"=>__("No drivers are currently available near your pickup location for the selected vehicle type. Try again later or choose another vehicle type"));
            echo json_encode($error); //database error
            exit;
        }
    } */


    
    

    //get user details
    $user_account_details = [];
    $query = sprintf('SELECT referral_discounts_count,wallet_amount FROM %stbl_users WHERE user_id = %d', DB_TBL_PREFIX, $_SESSION['uid']); //Get required user information from DB

    if($result = mysqli_query($GLOBALS['DB'], $query)){


        if(mysqli_num_rows($result)){
            $user_account_details = mysqli_fetch_assoc($result);
        }else{
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); //database error
            exit;
        }
                    
    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;
    }

    if($payment_type == 2){ //user using wallet payment method.check wallet balance is sufficient for the ride
        
        $balance = (float) $user_account_details['wallet_amount'] - $price;

        if(empty($balance) || $balance < 0){
            $error = array("error"=>__("You have Insufficient fund in your wallet for this ride"),"wallet_error" => 1, "deficit_amount" => abs($balance));
            echo json_encode($error); 
            exit; 
        }

        
    }elseif($user_account_details['wallet_amount'] < RIDER_MIN_WALLET_BALANCE){
        $balance = 0 - $user_account_details['wallet_amount'];
        $error = array("error"=>__("You have Insufficient fund in your wallet for this ride"),"wallet_error" => 2, "deficit_amount" => abs($balance));
        echo json_encode($error); 
        exit;
    }

    $user_referral_eligible = 0;
    $user_referral_discount = 0.00;

    //check if user is eligible for a referral discount
    if($user_account_details['referral_discounts_count'] > 0){

        //get referral details from DB
        $query = sprintf('SELECT * FROM %stbl_referral WHERE id = %d AND `status` = %d', DB_TBL_PREFIX, 1,1);

        if($result = mysqli_query($GLOBALS['DB'],$query)){
            if(mysqli_num_rows($result)){
                $row = mysqli_fetch_assoc($result);
                $ref_discount_value = $row['discount_value'];
                
                $user_referral_eligible = 1;
                $user_referral_discount = $ref_discount_value;

            }
        }

    }

    //get coupon details if available
    $coupon_discount_value = 0.00;
    $coupon_discount_type = 0;
    $coupon_code_invalid = 0;
    $coupon_min_fare = 0;
    $coupon_max_discount = 0;

    if(!empty($coupon_code)){

        $query = sprintf('SELECT * FROM %stbl_coupon_codes WHERE coupon_code = "%s" AND `status` = %d', DB_TBL_PREFIX, $coupon_code, 1);
        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                $row = mysqli_fetch_assoc($result); 
                //check if coupon has expired
                $coupon_start_date = strtotime($row['active_date']);
                $coupon_end_date = strtotime($row['expiry_date']);
                
                if(time() > $coupon_start_date && time() < $coupon_end_date){
                    //check usage limits
                    $query = sprintf('SELECT SUM(%1$stbl_coupons_used.times_used) AS all_usage, SUM(IF(%1$stbl_coupons_used.user_id = %2$d,%1$stbl_coupons_used.times_used,NULL)) AS user_usage FROM %1$stbl_coupons_used WHERE coupon_id = %3$d', DB_TBL_PREFIX, $_SESSION['uid'],$row['id']);
                    if($result = mysqli_query($GLOBALS['DB'],$query)){
                        if(mysqli_num_rows($result)){
                            $usage_data = mysqli_fetch_assoc($result);
                            
                            if($usage_data['all_usage'] >= $row['limit_count'] || $usage_data['user_usage'] >= $row['user_limit_count']){
                                $coupon_code = '';
                                $coupon_code_invalid = 1;
                            }else{
                                //check if selected vehicle is part of the vehicles assigned to this coupon
                                if(!empty($row['vehicles'])){
                                    $vehicles = explode(',',$row['vehicles']);
                                    $ride_found = 0;
                                    if(is_array($vehicles)){
                                        foreach($vehicles as $vehicle){
                                            if($ride_id == $vehicle){
                                                $ride_found = 1;
                                                $coupon_discount_value = $row['discount_value'];
                                                $coupon_discount_type = $row['discount_type'];
                                                $coupon_min_fare = $row['min_fare'];
                                                $coupon_max_discount = $row['max_discount_amount'];
                                                break;
                                            }
                                        }
                                        if(!$ride_found){
                                            $coupon_code = '';
                                            $coupon_code_invalid = 1;
                                        }
                                    }
                                }else{
                                    $coupon_discount_value = $row['discount_value'];
                                    $coupon_discount_type = $row['discount_type'];
                                    $coupon_min_fare = $row['min_fare'];
                                    $coupon_max_discount = $row['max_discount_amount'];
                                }
                            }
                        }else{
                            $coupon_code = '';
                            $coupon_code_invalid = 1;
                        }
                    
                         
                    }else{
                        $coupon_code = ''; 
                        $coupon_code_invalid = 1;
                    }
                }else{
                    $coupon_code = '';
                    $coupon_code_invalid = 1;
                } 
               
                
            }else{
                $coupon_code = '';
                $coupon_code_invalid = 1;
            }
            
        }else{
            $coupon_code = '';
            $coupon_code_invalid = 1;
        }
    }

    /* $error = array("error"=>__("An error has occured"),'coupon_invalid' => $coupon_code_invalid);
    echo json_encode($error); //database error
    exit; */
    $waypoint1_address = '';
    $waypoint1_lat = '';
    $waypoint1_lng = '';

    $waypoint2_address = '';
    $waypoint2_lat = '';
    $waypoint2_lng = '';

    if($multidestination){
        if(isset($waypoints_data['dest-1']) && $waypoints_data['dest-1']['address'] != ''){
            $waypoint1_address = $waypoints_data['dest-1']['address'];
            $waypoint1_lat = $waypoints_data['dest-1']['lat'];
            $waypoint1_lng = $waypoints_data['dest-1']['lng'];
        }

        if(isset($waypoints_data['dest-2']) && $waypoints_data['dest-2']['address'] != ''){
            $waypoint2_address = $waypoints_data['dest-2']['address'];
            $waypoint2_lat = $waypoints_data['dest-2']['lat'];
            $waypoint2_lng = $waypoints_data['dest-2']['lng'];
        }
    }

    $completion_code = crypto_string("123456789",4);
    
    $query = sprintf('INSERT INTO %stbl_bookings (service_mode,hr_set_num_h,est_distance,est_duration,ride_share,bk_alt_cars,coupon_max_discount,coupon_min_fare,dispatch_mode,waypoint1_address,waypoint1_long,waypoint1_lat,waypoint2_address,waypoint2_long,waypoint2_lat,referral_used,referral_discount_value,coupon_code,coupon_discount_type,coupon_discount_value,cur_symbol,cur_exchng_rate,cur_code,completion_code,scheduled,user_firstname,user_lastname,user_phone,user_id,pickup_datetime, pickup_address, pickup_long, pickup_lat, dropoff_address, dropoff_long,dropoff_lat,estimated_cost,route_id,ride_id,payment_type,date_created) VALUES'.
    '("%d","%d","%s","%s","%s","%s","%s","%s","%d","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%d","%s","%s","%s","%d","%s","%s","%s","%s","%s","%s","%s","%s","%d","%d","%d","%s")', 
    DB_TBL_PREFIX,  
    $service_mode,
    $hourly_rate_num_hours,  
    $est_trip_distance, 
    $est_trip_duration,
    $ride_sharing,
    $alternate_vehicles,
    $coupon_max_discount,
    $coupon_min_fare,
    $dispatch_mode,
    $waypoint1_address,
    $waypoint1_lng,
    $waypoint1_lat,
    $waypoint2_address,
    $waypoint2_lng,
    $waypoint2_lat,
    $user_referral_eligible,
    $user_referral_discount,
    $coupon_code,
    $coupon_discount_type,
    $coupon_discount_value,
    $currency_symbol,
    $currency_exchange_rate,
    $currency_code,
    $completion_code, 
    $scheduled_booking,
    $_SESSION['firstname'],
    $_SESSION['lastname'],
    $_SESSION['country_dial_code'] . substr($_SESSION['phone'],0,1) == "0" ? substr($_SESSION['phone'],1) : $_SESSION['phone'],
    $_SESSION['uid'],
    $pdatetime,
    $paddress,
    $plng,
    $plat,
    $daddress,
    $dlng,
    $dlat,
    $price,
    $route_id,
    $ride_id,
    $payment_type,
    gmdate('Y-m-d H:i:s', time()) 
    );


    if(! $result = mysqli_query($GLOBALS['DB'], $query)){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;
        
    }

    $booking_id = mysqli_insert_id($GLOBALS['DB']);

    //add booking to scheduled bookings redis list
    if($scheduled_booking){
        $redis = connectRedis();
    
        if($redis){
            $sdata = ["firstname" => $_SESSION['firstname'],"phone" => $_SESSION['phone'],"photo" => $_SESSION['photo'],"bk_id" => $booking_id, "p_loc" => $paddress, "d_loc" => $daddress, "p_lat" => $plat, "p_lng" => $plng,"d_lat" => $dlat,"d_lng" => $dlng, "p_time" => $pdatetime, "bk_time" => time(),"ride_id" => $ride_id,"route_id" => $route_id,"fare" => $price,"driver_id" => 0,"user_rating" => $_SESSION['user_rating'],"waypoint1" => $waypoint1_address,"waypoint1_lat" => $waypoint1_lat,"waypoint1_lng" => $waypoint1_lng,"waypoint2" => $waypoint2_address,"waypoint2_lat" => $waypoint2_lat,"waypoint2_lng" => $waypoint2_lng,'est_dist' => $est_trip_distance,'est_dur' => $est_trip_duration,'payment_type' => $payment_type];
            $redis->zAdd("sbknotifs:{$route_id}",strtotime($pdatetime . " UTC"),"sbkdata:{$booking_id}");
            $bk_data_ttl =  abs(strtotime($pdatetime . " UTC") - time()) + 86400;
            $redis->setEx("sbkdata:{$booking_id}", $bk_data_ttl ,serialize($sdata)); //store booking data 

            //send broadcast notification to all drivers in this city for this scheduled booking
            $spickup_date_time = date('Y-m-d H:i:s', strtotime($pdatetime . " UTC"));
            $ext_msg_data = array(
                'mode' => 1,
                'title' => "New scheduled booking",
                'message' => preg_replace("/\"/", '&#039;', htmlspecialchars("A customer has scheduled a new booking")),
                'action' => 'scheduled_bk'
            );        
            
            
            sendPushNotification(__("New scheduled booking"), __("A customer has scheduled a new booking"),"/topics/driver-route-{$route_id}",null,json_encode($ext_msg_data),null,null);

            //notify the user via background push notification
           
            /* $data = array(
                "action"=>"scheduled-bk-ind"
            );

            if(!empty($device_tokens)){
                sendPushNotification("","","/topics/driver-route-{$route_id}",$data);
            } */
        }

    }


    $data_array = array("success"=>1,'coupon_code_invalid' => $coupon_code_invalid, 'new_booking_id' => $booking_id);    
    echo json_encode($data_array); 
    exit;

}


function getUserCouponValidity($coupon_code){
    

    $query = sprintf('SELECT * FROM %stbl_coupon_codes WHERE coupon_code = "%s" AND `status` = %d', DB_TBL_PREFIX, $coupon_code, 1);

    if($result = mysqli_query($GLOBALS['DB'], $query)){

        if(mysqli_num_rows($result)){

            $row = mysqli_fetch_assoc($result); 

            //check if coupon has expired
            $coupon_start_date = strtotime($row['active_date']);
            $coupon_end_date = strtotime($row['expiry_date']);
            
            if(time() > $coupon_start_date && time() < $coupon_end_date){

                //check usage limits
                $query = sprintf('SELECT SUM(%1$stbl_coupons_used.times_used) AS all_usage, SUM(IF(%1$stbl_coupons_used.user_id = %2$d,%1$stbl_coupons_used.times_used,NULL)) AS user_usage FROM %1$stbl_coupons_used WHERE coupon_id = %3$d', DB_TBL_PREFIX, $_SESSION['uid'],$row['id']);
                if($result = mysqli_query($GLOBALS['DB'],$query)){
                    if(mysqli_num_rows($result)){
                        $usage_data = mysqli_fetch_assoc($result);
                        
                        if($usage_data['all_usage'] >= $row['limit_count'] || $usage_data['user_usage'] >= $row['user_limit_count']){
                            
                            return false;

                        }else{

                            return true;

                        }

                    }else{

                        return false;

                    }
                
                        
                }else{

                    return false;

                }
            }else{

                return false;

            } 
            
            
        }else{

            return false;

        }
        
    }else{

        return false;

    }


}



function getavailablecitydrivers(){
    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $city = (int) $_GET['city'];
    $pickup_lat = isset($_GET['pickup_location']['lat']) ? $_GET['pickup_location']['lat'] : 0;
    $pickup_lng = isset($_GET['pickup_location']['lng']) ? $_GET['pickup_location']['lng'] : 0;
    $priority_driver = (int) $_GET['priority_driver'];
    $drivers_location_data = [];
    $location_info_age = gmdate('Y-m-d H:i:s', time() - LOCATION_INFO_VALID_AGE);
    $ride_types = [];
    $ride_proximity = [];
        
    //*************Get driver location data from redis if available**********/

    $redis = connectRedis();
    
    if($redis){
        //successfully connected to redis

        $online_drivers = [];
        $redis->zRemRangeByScore("drvs_online:{$city}","-inf",time() - LOCATION_INFO_VALID_AGE); //remove drivers who haven't updated their location for a while

        if(empty($priority_driver)){
            //get all drivers
            $redis_res = $redis->zRangeByScore("drvs_online:{$city}",time() - LOCATION_INFO_VALID_AGE,"+inf",['withscores'=>false,'limit' => [0,200]]);
            if($redis_res){
                $online_drivers = $redis->mget($redis_res);
            }
        }else{
            //get just the priority driver data
            $online_drivers[] = $redis->get("drvpos:{$priority_driver}");
        }


        $count = 0;
            
            
            foreach($online_drivers as $onlinedrivers){

                

                $row = unserialize($onlinedrivers);
                if(empty($row))continue;

                if($pickup_lat != 0 && $pickup_lng != 0 && empty($priority_driver)){

                    //calculate distance of this vehicle to the rider's pickup location
                    $ride_dist = distance($pickup_lat,$pickup_lng,$row['lat'],$row['long']);

                    if($ride_dist > MAX_DRIVER_DISTANCE)continue;

                    if($count > 20)break;
                    
                    if(!isset($ride_proximity[$row['ride_id']])){

                        $ride_proximity[$row['ride_id']]['dist'] = $ride_dist;
                        $time_of_arrival = ceil((($ride_dist * 1000) / 5.555555556) / 60);
                        $ride_proximity[$row['ride_id']]['eta'] = $time_of_arrival;
                        
                        

                    }else{

                        if($ride_proximity[$row['ride_id']]['dist'] > $ride_dist){
                            $ride_proximity[$row['ride_id']]['dist'] = $ride_dist; 
                            $time_of_arrival = ceil((($ride_dist * 1000) / 5.555555556) / 60);
                            $ride_proximity[$row['ride_id']]['eta'] = $time_of_arrival;                           
                        }

                    }
                    
                }

                
                                
                //retrieve and format data for display on app
                $drivers_location_data[$count]['position']['lat'] = $row['lat'];
                $drivers_location_data[$count]['position']['lng'] = $row['long'];
                $drivers_location_data[$count]['disableAutoPan'] = true;

                     
                                
                if(!array_key_exists($row['ride_id'],$ride_types)){ //assign a different icon for every unique car type
                    
                    $ride_types[$row['ride_id']] = "img/city-driver-icon-" . $row['icon_type'] . ".png";
                    
                    $drivers_location_data[$count]['icon']['url'] = "img/city-driver-icon-" . $row['icon_type'] . ".png";
                    $drivers_location_data[$count]['icon']['size'] = ['width' => 32,'height'=> 32];
                    $drivers_location_data[$count]['title'] = $row['ride_type'];
                    $drivers_location_data[$count]['driver_id'] = $row['driver_id'];
                    $drivers_location_data[$count]['b_angle'] = $row['b_angle'];

                }else{
                    $drivers_location_data[$count]['icon']['url'] = $ride_types[$row['ride_id']];
                    $drivers_location_data[$count]['icon']['size'] = ['width' => 32,'height'=> 32];
                    $drivers_location_data[$count]['title'] = $row['ride_type'];
                    $drivers_location_data[$count]['driver_id'] = $row['driver_id'];
                    $drivers_location_data[$count]['b_angle'] = $row['b_angle'];
                }


                $count++;
                

            }

        

        
    }


    //check for single device login
    $sdl_status = 0;
    

    if($redis){
        $sdl_key = $redis->get("ridrsdlkey:{$_SESSION['uid']}");
        if(!empty($sdl_key) && $sdl_key != $_GET['sdl_key']){
            $sdl_status = 1;
        }
    }


    //*************Redis end **********/

    

    //Read from database every 15 seconds

    if(isset($_SESSION['last_location_get'])){
        if(time() - $_SESSION['last_location_get'] < 15){
            if($redis){
                $data_array = array("success"=>1,'drivers_locations'=>$drivers_location_data,'avail_vehicles' => $ride_proximity,"sdl_status" => $sdl_status);  
                echo json_encode($data_array); 
                exit;
            }else{
                $error = array("error"=>"Database error.");
                echo json_encode($error); 
                exit; 
            }
        }
    }

    $_SESSION['last_location_get'] = time() + rand(10,30);

    $mysql_db = connectMysqlDB();

    if(!$mysql_db){
        $error = array("error"=>"Database error.");
        echo json_encode($error); 
        exit;
    }

    if($redis){
        $data_array = array("success"=>1,'drivers_locations'=>$drivers_location_data,'avail_vehicles' => $ride_proximity,'bannerdata'=> getbannerdata(),"sdl_status" => $sdl_status);  
        echo json_encode($data_array); 
        exit;
    }
    

    $query = sprintf('SELECT %1$stbl_driver_location.*,%1$stbl_drivers.firstname, %1$stbl_drivers.ride_id,%1$stbl_rides.* FROM %1$stbl_driver_location 
    INNER JOIN %1$stbl_drivers ON %1$stbl_driver_location.driver_id = %1$stbl_drivers.driver_id
    INNER JOIN %1$stbl_rides ON %1$stbl_rides.id = %1$stbl_drivers.ride_id
    WHERE %1$stbl_drivers.route_id = %2$d AND %1$stbl_drivers.is_activated = 1 AND %1$stbl_drivers.available = 1 AND %1$stbl_driver_location.location_date > "%3$s" AND %1$stbl_drivers.operation_status = 0 LIMIT 500', DB_TBL_PREFIX, $city, $location_info_age);

    if(!empty($priority_driver)){
        $query = sprintf('SELECT %1$stbl_driver_location.*,%1$stbl_drivers.firstname, %1$stbl_drivers.ride_id,%1$stbl_rides.* FROM %1$stbl_driver_location 
        INNER JOIN %1$stbl_drivers ON %1$stbl_driver_location.driver_id = %1$stbl_drivers.driver_id
        INNER JOIN %1$stbl_rides ON %1$stbl_rides.id = %1$stbl_drivers.ride_id
        WHERE %1$stbl_drivers.driver_id = %2$d', DB_TBL_PREFIX, $priority_driver);
    }

    

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){

            $count = 0;            
            
            while($row = mysqli_fetch_assoc($result)){

                if($pickup_lat != 0 && $pickup_lng != 0 && empty($priority_driver)){

                    //calculate distance of this vehicle to the rider's pickup location
                    $ride_dist = distance($pickup_lat,$pickup_lng,$row['lat'],$row['long']);

                    if($ride_dist > MAX_DRIVER_DISTANCE)continue;

                    if($count > 20)break;
                    
                    if(!isset($ride_proximity[$row['ride_id']])){

                        $ride_proximity[$row['ride_id']]['dist'] = $ride_dist;
                        $time_of_arrival = ceil((($ride_dist * 1000) / 5.555555556) / 60);
                        $ride_proximity[$row['ride_id']]['eta'] = $time_of_arrival;
                        
                        

                    }else{

                        if($ride_proximity[$row['ride_id']]['dist'] > $ride_dist){
                            $ride_proximity[$row['ride_id']]['dist'] = $ride_dist; 
                            $time_of_arrival = ceil((($ride_dist * 1000) / 5.555555556) / 60);
                            $ride_proximity[$row['ride_id']]['eta'] = $time_of_arrival;                           
                        }

                    }
                    
                }

                

                //retrieve and format data for display on app
                $drivers_location_data[$count]['position']['lat'] = $row['lat'];
                $drivers_location_data[$count]['position']['lng'] = $row['long'];
                $drivers_location_data[$count]['disableAutoPan'] = true;
                
                                
                if(!array_key_exists($row['ride_id'],$ride_types)){ //assign a different icon for every unique car type
                   
                    $ride_types[$row['ride_id']] = "img/city-driver-icon-" . $row['icon_type'] . ".png";
                    

                    $drivers_location_data[$count]['icon']['url'] = "img/city-driver-icon-" . $row['icon_type'] . ".png";
                    $drivers_location_data[$count]['icon']['size'] = ['width' => 32,'height'=> 32];
                    $drivers_location_data[$count]['title'] = empty($priority_driver) ? $row['ride_type'] : "Your Driver {$row['firstname']}";
                    $drivers_location_data[$count]['driver_id'] = $row['driver_id'];
                    $drivers_location_data[$count]['b_angle'] = $row['b_angle'];

                }else{
                    $drivers_location_data[$count]['icon']['url'] = $ride_types[$row['ride_id']]['img'];
                    $drivers_location_data[$count]['icon']['size'] = ['width' => 32,'height'=> 32];
                    $drivers_location_data[$count]['title'] = empty($priority_driver) ? $row['ride_type'] : "Your Driver {$row['firstname']}";
                    $drivers_location_data[$count]['driver_id'] = $row['driver_id'];
                    $drivers_location_data[$count]['b_angle'] = $row['b_angle'];
                }

                $count++;
                

            }
            
            mysqli_free_result($result);

        }
    
    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }
    



    $data_array = array("success"=>1,'drivers_locations'=>$drivers_location_data,'avail_vehicles' => $ride_proximity,'bannerdata'=> getbannerdata());    
    echo json_encode($data_array); 
    exit;   




}




function resumebooking(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }


    $booking_id = (int) $_GET['booking_id'];

    $ongoing_booking = [];
    //check if user has an ongoing booking
    $query = sprintf('SELECT *,%1$stbl_bookings.id AS booking_id FROM %1$stbl_bookings 
    INNER JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id
    INNER JOIN %1$stbl_driver_location ON %1$stbl_driver_location.driver_id = %1$stbl_bookings.driver_id
    WHERE %1$stbl_bookings.id = %3$d AND %1$stbl_bookings.user_id = %2$d AND %1$stbl_bookings.driver_id != 0 AND (%1$stbl_bookings.status = 0 OR %1$stbl_bookings.status = 1)', DB_TBL_PREFIX, $_SESSION['uid'], $booking_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $row = mysqli_fetch_assoc($result);
            $action = '';
            if(!empty($row['date_arrived']) && $row['status'] == 0){
                $action = "driver-arrived";
            }elseif(empty($row['date_arrived']) && $row['status'] == 0 ){
                $action = "driver-assigned";
            }else{
                $action = "customer-onride";
            }
            $driver_photo_file = isset($row['photo_file']) ? $row['photo_file'] : "0";
            $ongoing_booking = [
                "action"=>$action,
                "route_id" => $row['route_id'],
                "booking_id" => $row['booking_id'],
                "driver_id" => $row['driver_id'],
                "driver_firstname" => $row['firstname'],
                "driver_phone" => $row['phone'],
                "driver_platenum" => $row['car_plate_num'],
                "driver_rating" => $row['driver_rating'],
                "driver_location_lat" => $row['lat'],
                "driver_location_long" => $row['long'],
                "pickup_addr"=>$row['pickup_address'],
                "dropoff_addr"=>$row['dropoff_address'],
                "pickup_lat"=>$row['pickup_lat'],
                "pickup_long"=>$row['pickup_long'],
                "dropoff_lat"=>$row['dropoff_lat'],
                "dropoff_long"=>$row['dropoff_long'],
                "driver_carmodel" => $row['car_color'] . " " .  $row['car_model'],
                "driver_carid" => $row['ride_id'],
                "driver_arrived_time" => !empty($row['date_arrived']) ? strtotime($row['date_arrived'] . " UTC") : 0,
                "driver_start_time" => !empty($row['date_started']) ? strtotime($row['date_started'] . " UTC") : 0,
                "driver_completed_rides" => $row['completed_rides'],
                "completion_code"=>$row['completion_code'],
                "driver_photo" => SITE_URL . "ajaxphotofile.php?file=" . $driver_photo_file,
                "waypoint1_address" => $row['waypoint1_address'],
                "waypoint1_long" => $row['waypoint1_long'],
                "waypoint1_lat" => $row['waypoint1_lat'],
                "waypoint2_address" => $row['waypoint2_address'],
                "waypoint2_long" => $row['waypoint2_long'],
                "waypoint2_lat" => $row['waypoint2_lat'],
                "date_arrived_stop_1" => !empty($row['date_arrived_stop_1']) ? strtotime($row['date_arrived_stop_1'] . " UTC") : "",
                "date_arrived_stop_2" => !empty($row['date_arrived_stop_2']) ? strtotime($row['date_arrived_stop_2'] . " UTC") : "",
                "date_left_stop_1" => !empty($row['date_left_stop_1']) ? strtotime($row['date_left_stop_1'] . " UTC") : "",
                "date_left_stop_2" => !empty($row['date_left_stop_2']) ? strtotime($row['date_left_stop_2'] . " UTC") : ""
            ];
        }else{
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); 
            exit;
        }
    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }


    $error = array("success"=>1, 'ongoing_bk'=> $ongoing_booking);
    echo json_encode($error); 
    exit;


    
}







function getbookings(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $booking_data = [];
    $booking_pend_onride = '';
    $booking_completed = '';
    $booking_cancelled = '';
    $booking_data_sort = [];
    $retry_button_shown = 0;
    $cancelled_bookings_count = 0;
    $scheduled_booking_data = [];


    $query = sprintf('SELECT *, %1$stbl_bookings.driver_id AS b_driver_id, %1$stbl_drivers.phone AS driver_phone,%1$stbl_drivers.firstname AS driver_firstname,%1$stbl_drivers.lastname AS driver_lastname, %1$stbl_drivers.photo_file AS drvr_photo,%1$stbl_bookings.date_created AS created_date,%1$stbl_bookings.id AS booking_id FROM %1$stbl_bookings 
    LEFT JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.scheduled_driver
    LEFT JOIN %1$stbl_rides ON %1$stbl_rides.id = %1$stbl_bookings.ride_id
    LEFT JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
    WHERE %1$stbl_bookings.user_id = %2$s AND %1$stbl_bookings.scheduled_driver > 0 ORDER BY %1$stbl_bookings.date_created DESC LIMIT 0,500 ', DB_TBL_PREFIX,$_SESSION['uid']);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                $row['dropoff_address'] = !empty($row['dropoff_address']) ? $row['dropoff_address'] : __('Destination not specified');
                $scheduled_booking_data[$row['booking_id']] = $row;
            }
            
            mysqli_free_result($result);
        }
    }


    $query = sprintf('SELECT *, %1$stbl_bookings.driver_id AS b_driver_id, %1$stbl_drivers.phone AS driver_phone,%1$stbl_drivers.firstname AS driver_firstname,%1$stbl_drivers.lastname AS driver_lastname, %1$stbl_drivers.photo_file AS drvr_photo,%1$stbl_bookings.date_created AS created_date,%1$stbl_bookings.id AS booking_id FROM %1$stbl_bookings 
    LEFT JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id OR (%1$stbl_drivers.driver_id = %1$stbl_bookings.scheduled_driver AND %1$stbl_bookings.driver_id = 0)
    LEFT JOIN %1$stbl_rides ON %1$stbl_rides.id = %1$stbl_bookings.ride_id
    LEFT JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
    WHERE %1$stbl_bookings.user_id = %2$s ORDER BY %1$stbl_bookings.date_created DESC LIMIT 0,500 ', DB_TBL_PREFIX,$_SESSION['uid']);


    if($result = mysqli_query($GLOBALS['DB'], $query)){

        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){

                $row['dropoff_address'] = !empty($row['dropoff_address']) ? $row['dropoff_address'] : __('Destination not specified');

                if(!empty($scheduled_booking_data[$row['booking_id']])){
                    if(empty($row['driver_id'])){
                        $row = $scheduled_booking_data[$row['booking_id']];
                    }
                }

                $booking_data[] = $row;
                $sort_date = date('Y-m-d',strtotime($row['created_date'] . ' UTC'));

                if($row['status'] == 0 || $row['status'] == 1){ //pending or onride
                    $booking_data_sort[$sort_date]['date'] = $row['created_date'];
                    $booking_data_sort[$sort_date]['pend_onride'][] = $row;
                }elseif($row['status'] == 3){ //completed
                    $booking_data_sort[$sort_date]['date'] = $row['created_date'];
                    $booking_data_sort[$sort_date]['completed'][] = $row;
                }elseif($row['status'] == 2 || $row['status'] == 4 || $row['status'] == 5){ //cancelled
                    $booking_data_sort[$sort_date]['date'] = $row['created_date'];
                    $booking_data_sort[$sort_date]['cancelled'][] = $row;
                }

            }
            
            mysqli_free_result($result);
        }else{

            $data_array = array("success"=>1,'pend_onride' => '','booking_comp'=>'','booking_canc'=>'');    
            echo json_encode($data_array); 
            exit;

        }
    }else{

        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit; 

    }

    //sort booking data
    /* $booking_data_sort = [];

    foreach($booking_data as $bookingdata){
        if($bookingdata['status'] == 0 || $bookingdata['status'] == 1){ //pending or onride
            $booking_data_sort[$bookingdata['created_date']]['date'] = $bookingdata['created_date'];
            $booking_data_sort[$bookingdata['created_date']]['pend_onride'][] = $bookingdata;
        }elseif($bookingdata['status'] == 3){ //completed
            $booking_data_sort[$bookingdata['created_date']]['date'] = $bookingdata['created_date'];
            $booking_data_sort[$bookingdata['created_date']]['completed'][] = $bookingdata;
        }elseif($bookingdata['status'] == 2 || $bookingdata['status'] == 4 || $bookingdata['status'] == 5){ //cancelled
            $booking_data_sort[$bookingdata['created_date']]['date'] = $bookingdata['created_date'];
            $booking_data_sort[$bookingdata['created_date']]['cancelled'][] = $bookingdata;
        }        
        

    } */


    //format for display on app


    foreach($booking_data_sort as $bookingdatasort){

        if(!empty($bookingdatasort['pend_onride'])){
            //save date
            $day_num = date('j',strtotime($bookingdatasort['date'] . " UTC"));
            $month_name = date('F',strtotime($bookingdatasort['date'] . " UTC"));
            $b_date_format = $day_num . " " . __($month_name);

            $booking_pend_onride .= "<ons-list-header style='font-size: 14px;font-weight:bold;margin:20px 0;'>{$b_date_format}</ons-list-header>";



            //format pending onride rides for this date
            foreach($bookingdatasort['pend_onride'] as $bookingdatasort_po){
                $booking_time = date('g:i A',strtotime($bookingdatasort_po['date_created'] . ' UTC'));

                
                $booking_ptime = date('g:i A',strtotime($bookingdatasort_po['date_created'] . ' UTC'));
                $booking_driver = isset($bookingdatasort_po['driver_id']) ? $bookingdatasort_po['driver_firstname'] ." " . $bookingdatasort_po['driver_lastname'] : "N/A";
                $booking_driver_assigned = !empty($bookingdatasort_po['b_driver_id']) ? 1 : 0;
                $resume_booking_btn = $booking_driver_assigned == 1 ? "<span class='list-item__subtitle' id='resume-bk-{$bookingdatasort_po['booking_id']}' ><ons-button style='width:100%' onclick='event.stopPropagation();resumeBooking({$bookingdatasort_po['booking_id']})'> <i class='fa fa-angle-double-right'></i> </ons-button></span>" : "";
                $booking_completion_code = !empty($bookingdatasort_po['completion_code']) ? $bookingdatasort_po['completion_code'] : "N/A";
                $status = '';
                $close_btn = '';
                if($bookingdatasort_po['status'] == 0 && $booking_driver_assigned != 0){
                    $status = "<span style='color: #ef6c00;font-weight: bold;border-radius:5px;border: thin solid #ef6c00;padding: 3px 5px;font-size: 12px;'>". __("Driver is on his way") . "</span>";
                    $close_btn = "<span style='display:inline-block;float:right'><ons-icon onclick = 'event.stopPropagation();bookingcancel({$bookingdatasort_po['booking_id']},{$booking_driver_assigned})' icon='fa-times-circle' size='18px' style='color:red'></ons-icon></span>";
                }elseif($bookingdatasort_po['status'] == 0){
                    $status = "<span style='color: #e541e5;font-weight: bold;border-radius:5px;border: thin solid #e541e5;padding: 3px 5px;font-size: 12px;'>" . __("Pending trip") . "</span>";
                    $close_btn = "<span style='display:inline-block;float:right'><ons-icon onclick = 'event.stopPropagation();bookingcancel({$bookingdatasort_po['booking_id']},{$booking_driver_assigned})' icon='fa-times-circle' size='18px' style='color:red'></ons-icon></span>";
                }else{
                    $status = "<span style='color: #43a047;font-weight: bold;border-radius:5px;border: thin solid #43a047;padding: 3px 5px;font-size: 12px;'>" . __("Current trip") . "</span>";
                    $close_btn = '';
                }

                $day_num = date('j',strtotime($bookingdatasort_po['pickup_datetime'] . " UTC"));
                $month_name = date('F',strtotime($bookingdatasort_po['pickup_datetime'] . " UTC"));
                $time_str = date('g:i A',strtotime($bookingdatasort_po['pickup_datetime'] . " UTC"));
                $booking_pdate_time = $day_num . " " . __($month_name) . ", " . $time_str;

                $booking_type = $bookingdatasort_po['scheduled'] == 1 ? 'Schedule ride' : 'Instant ride';
                //$drvr_photo = explode('/',$bookingdatasort_po['drvr_photo']);
                $drvr_photo_file = isset($bookingdatasort_po['drvr_photo']) ? SITE_URL . "ajaxphotofile.php?file=".$bookingdatasort_po['drvr_photo'] : "";
                $booking_payment_type = '';
                if(!empty($bookingdatasort_po['payment_type'])){
                    if($bookingdatasort_po['payment_type'] == 1){
                        $booking_payment_type = __("Cash");
                    }elseif($bookingdatasort_po['payment_type'] == 2){
                        $booking_payment_type = __("Wallet");
                    }else{
                        $booking_payment_type = "Card";
                    }
                }
                $ride_filename = explode('/',$bookingdatasort_po['ride_img']);
                $ride_image = SITE_URL . 'img/ride_imgs/' . array_pop($ride_filename);
                $booking_title = str_pad($bookingdatasort_po['booking_id'] , 5, '0', STR_PAD_LEFT);

                $item_data = [];
                $item_data = array(
                                    'car_desc'=> $bookingdatasort_po['ride_desc'],
                                    'driver_phone' => $bookingdatasort_po['driver_phone'],
                                    'driver_car_details' => $bookingdatasort_po['car_model'] . " [" . $bookingdatasort_po['car_color'] . "]",
                                    'driver_plate_num' => $bookingdatasort_po['car_plate_num'],
                                    'driver_rating' => $bookingdatasort_po['driver_rating'],
                                    'payment_type' => $booking_payment_type,
                                    'pick_up_time'=> $booking_pdate_time,
                                    'driver_image' => $drvr_photo_file,
                                    'car_image' => $ride_image,
                                    'driver_name' => $booking_driver,
                                    'booking_cost' => $bookingdatasort_po['cur_symbol'].$bookingdatasort_po['estimated_cost'],
                                    'car_type' => $bookingdatasort_po['ride_type'],
                                    'p_location' => $bookingdatasort_po['pickup_address'],
                                    'd_location' => $bookingdatasort_po['dropoff_address'],
                                    'booking_id' => $booking_title,
                                    'booking_type' => $booking_type,
                                    'booking_status' => $bookingdatasort_po['status'],
                                    'coupon_code' => $bookingdatasort_po['coupon_code'],
                                    'p_lat' => $bookingdatasort_po['pickup_lat'],
                                    'p_lng' => $bookingdatasort_po['pickup_long'],
                                    'd_lat' => $bookingdatasort_po['dropoff_lat'],
                                    'd_lng' => $bookingdatasort_po['dropoff_long'],
                                    'service_mode' => $bookingdatasort_po['service_mode'],
                                    'coupon_discount_value' => $bookingdatasort_po['coupon_discount_value'],
                                    'coupon_discount_type' => $bookingdatasort_po['coupon_discount_type'],
                                    'actual_cost' => $bookingdatasort_po['actual_cost'],
                                    'paid_amount' => $bookingdatasort_po['paid_amount'],
                                    'referral_used' => $bookingdatasort_po['referral_used'],
                                    'referral_discount_value' => $bookingdatasort_po['referral_discount_value'],
                                    "waypoint1_address" => $bookingdatasort_po['waypoint1_address'],
                                    "waypoint1_long" => $bookingdatasort_po['waypoint1_long'],
                                    "waypoint1_lat" => $bookingdatasort_po['waypoint1_lat'],
                                    "waypoint2_address" => $bookingdatasort_po['waypoint2_address'],
                                    "waypoint2_long" => $bookingdatasort_po['waypoint2_long'],
                                    "waypoint2_lat" => $bookingdatasort_po['waypoint2_lat']
                                );

                $item_data_json = json_encode($item_data);

                $booking_pend_onride .= "<ons-list-item onclick='showbookingdetails({$bookingdatasort_po['booking_id']})' id='booking-list-item-{$bookingdatasort_po['booking_id']}' modifier='longdivider'>
                
                                            <div class='center'>
                                                <div style='width:100%;'><span class='list-item__title'>{$booking_time} </span> {$status} {$close_btn}</div>
                                                <span style='text-align: left;margin-bottom: 15px;margin-top: 10px;color:#777;font-size:14px;'><span>ID:#{$booking_title} | OTP code: {$booking_completion_code}</span></span>  
                                                <div style='box-sizing: border-box;width: 100%;overflow: hidden;background-color: white;padding: 10px 10px;'>
                                                    <div style='border-left: 2px dashed #ccc'>
                                                        <p style='margin: 10px 0 30px 0;position: relative;padding-left: 15px;'>
                                                            <ons-icon icon='fa-dot-circle' size='18px' style='color: #8bc34a;position: absolute;font-size: 17px;left: -9px;top: -3px;background-color: white;'></ons-icon>
                                                            <span style='line-height:1.5em;font-weight: bold;font-size: 14px;width: 100%;display: inline-block;color: black;overflow: hidden;' >{$bookingdatasort_po['pickup_address']}</span>                                
                                                        </p>
                                                        <p style='margin:0;position: relative;padding-left: 15px;'>
                                                            <ons-icon icon='fa-square' size='18px' style='color: rgb(250, 75, 75);position: absolute;font-size: 18px;left: -8px;bottom: 0px;border-bottom: 3px solid white;background-color: white;'></ons-icon>
                                                            <span style='line-height:1.5em;font-weight: bold;font-size: 14px;width: 100%;display: inline-block;color: black;overflow: hidden;' >{$bookingdatasort_po['dropoff_address']}</span>                                
                                                        </p>
                                                    </div>
                                                </div>                               
                                                
                                                <span id='booking-list-item-data-{$bookingdatasort_po['booking_id']}' type='text' style='display:none'>{$item_data_json}</span>
                                                {$resume_booking_btn}
                                            </div>
                                            <div class='right'></div>
                                        
                                            
                                        
                                        </ons-list-item>";

            }


        }
        
        if(!empty($bookingdatasort['completed'])){
            //save date
            $day_num = date('j',strtotime($bookingdatasort['date'] . " UTC"));
            $month_name = date('F',strtotime($bookingdatasort['date'] . " UTC"));
            $b_date_format = $day_num . " " . __($month_name);
            $booking_completed .= "<ons-list-header style='font-size: 14px;font-weight:bold;margin:20px 0;'>{$b_date_format}</ons-list-header>";


            //format pending onride rides for this date
            foreach($bookingdatasort['completed'] as $bookingdatasort_comp){
                $booking_time = date('g:i A',strtotime($bookingdatasort_comp['date_created'] . ' UTC'));
                $booking_ptime = date('g:i A',strtotime($bookingdatasort_comp['date_created'] . ' UTC'));
                $booking_dtime = isset($bookingdatasort_comp['dropoff_datetime']) ? date('g:i A',strtotime($bookingdatasort_comp['dropoff_datetime'] . ' UTC')) : "N/A";
                $booking_paid_amt = isset($bookingdatasort_comp['paid_amount']) ? $bookingdatasort_comp['paid_amount'] : "N/A";
                $booking_driver = isset($bookingdatasort_comp['driver_id']) ? $bookingdatasort_comp['driver_firstname'] ." " . $bookingdatasort_comp['driver_lastname'] : "N/A";
                $booking_completion_code = !empty($bookingdatasort_comp['completion_code']) ? $bookingdatasort_comp['completion_code'] : "N/A";

                $day_num = date('j',strtotime($bookingdatasort_comp['pickup_datetime'] . " UTC"));
                $month_name = date('F',strtotime($bookingdatasort_comp['pickup_datetime'] . " UTC"));
                $time_str = date('g:i A',strtotime($bookingdatasort_comp['pickup_datetime'] . " UTC"));
                $booking_pdate_time = $day_num . " " . __($month_name) . ", " . $time_str;

                $booking_type = $bookingdatasort_comp['scheduled'] == 1 ? 'Schedule ride' : 'Instant ride';
                //$drvr_photo = explode('/',$bookingdatasort_comp['drvr_photo']);
                $drvr_photo_file = isset($bookingdatasort_comp['drvr_photo']) ? SITE_URL . "ajaxphotofile.php?file=".$bookingdatasort_comp['drvr_photo'] : "0";
                $booking_payment_type = '';
                if(!empty($bookingdatasort_comp['payment_type'])){
                    if($bookingdatasort_comp['payment_type'] == 1){
                        $booking_payment_type = __("Cash");
                    }elseif($bookingdatasort_comp['payment_type'] == 2){
                        $booking_payment_type = __("Wallet");
                    }else{
                        $booking_payment_type = "Card";
                    }
                }
                $ride_filename = explode('/',$bookingdatasort_comp['ride_img']);
                $ride_image = SITE_URL . 'img/ride_imgs/' . array_pop($ride_filename);
                $booking_title = str_pad($bookingdatasort_comp['booking_id'] , 5, '0', STR_PAD_LEFT);
                $ride_duration = '0 Secs';
                if(!empty($bookingdatasort_comp['date_started']) && !empty($bookingdatasort_comp['date_completed'])){
                    $ride_start_time = strtotime($bookingdatasort_comp['date_started']);
                    $ride_end_time = strtotime($bookingdatasort_comp['date_completed']);
                    $ride_duration_secs = $ride_end_time - $ride_start_time;
                    if($ride_duration_secs){
                                                
                        $hours = floor($ride_duration_secs / 3600);
                        $minutes = floor(($ride_duration_secs % 3600) / 60 );
                        $seconds = ($ride_duration_secs % 3600) % 60;
                        $ride_duration = '';
                        if(!empty($hours)){
                            $ride_duration = $hours . "H ";
                        }

                        if(!empty($minutes)){
                            $ride_duration .= $minutes . "M ";
                        }


                        if(!empty($seconds)){
                            $ride_duration .= $seconds . "S";
                        }

                    }
                    
                }

                $item_data = [];
                $item_data = array(
                                    'car_desc'=> $bookingdatasort_comp['ride_desc'],
                                    'driver_phone' => $bookingdatasort_comp['driver_phone'],
                                    'driver_car_details' => $bookingdatasort_comp['car_model'] . " [" . $bookingdatasort_comp['car_color'] . "]",
                                    'driver_plate_num' => $bookingdatasort_comp['car_plate_num'],
                                    'driver_rating' => $bookingdatasort_comp['driver_rating'],
                                    'payment_type' => $booking_payment_type,
                                    'pick_up_time'=> $booking_pdate_time,
                                    'driver_image' => $drvr_photo_file,
                                    'car_image' => $ride_image,
                                    'driver_name' => $booking_driver,
                                    'booking_cost' => $bookingdatasort_comp['cur_symbol'].$bookingdatasort_comp['paid_amount'],
                                    'car_type' => $bookingdatasort_comp['ride_type'],
                                    'p_location' => $bookingdatasort_comp['pickup_address'],
                                    'd_location' => $bookingdatasort_comp['dropoff_address'],
                                    'booking_id' => $booking_title,
                                    'booking_type' => $booking_type,
                                    'booking_status' => $bookingdatasort_comp['status'],
                                    'coupon_code' => $bookingdatasort_comp['coupon_code'],
                                    'distance_travelled' => !empty($bookingdatasort_comp['distance_travelled']) ? round(($bookingdatasort_comp['distance_travelled'] * 0.001),2) . " KM" : "0 Km",
                                    'ride_duration' => $ride_duration,
                                    'p_lat' => $bookingdatasort_comp['pickup_lat'],
                                    'p_lng' => $bookingdatasort_comp['pickup_long'],
                                    'd_lat' => $bookingdatasort_comp['dropoff_lat'],
                                    'd_lng' => $bookingdatasort_comp['dropoff_long'],
                                    'service_mode' => $bookingdatasort_comp['service_mode'],
                                    'coupon_discount_value' => $bookingdatasort_comp['coupon_discount_value'],
                                    'coupon_discount_type' => $bookingdatasort_comp['coupon_discount_type'],
                                    'actual_cost' => $bookingdatasort_comp['actual_cost'],
                                    'paid_amount' => $bookingdatasort_comp['paid_amount'],
                                    'referral_used' => $bookingdatasort_comp['referral_used'],
                                    'referral_discount_value' => $bookingdatasort_comp['referral_discount_value'],
                                    "waypoint1_address" => $bookingdatasort_comp['waypoint1_address'],
                                    "waypoint1_long" => $bookingdatasort_comp['waypoint1_long'],
                                    "waypoint1_lat" => $bookingdatasort_comp['waypoint1_lat'],
                                    "waypoint2_address" => $bookingdatasort_comp['waypoint2_address'],
                                    "waypoint2_long" => $bookingdatasort_comp['waypoint2_long'],
                                    "waypoint2_lat" => $bookingdatasort_comp['waypoint2_lat']
                                );

                $item_data_json = json_encode($item_data);


                $booking_completed .= "<ons-list-item onclick='showbookingdetails({$bookingdatasort_comp['booking_id']})' data-ridedesc='{$bookingdatasort_comp['ride_desc']}'  data-driverphone='{$bookingdatasort_comp['driver_phone']}' data-ptype='{$booking_payment_type}' data-put='{$booking_pdate_time}' data-driverimg='{$drvr_photo_file}' data-rideimg='{$ride_image}' data-drivername='{$booking_driver}' data-cost='{$bookingdatasort_comp['estimated_cost']}' data-ride='{$bookingdatasort_comp['ride_type']}' data-pul='{$bookingdatasort_comp['pickup_address']}' data-dol='{$bookingdatasort_comp['dropoff_address']}' data-btitle='{$booking_title}' id='booking-list-item-{$bookingdatasort_comp['booking_id']}' modifier='longdivider'>
                                            <div class='center'>
                                                <div style='width:100%;'><span class='list-item__title'>{$booking_time} </span> <span style='color: #1976d2;font-weight: bold;border-radius:5px;border: thin solid #1976d2;padding: 3px 5px;font-size: 12px;'>" . __("Completed") . "</span></div>
                                                <span style='text-align: left;margin-bottom: 15px;margin-top: 10px;color:#777;font-size:14px;'><span>ID:#{$booking_title} | OTP code: {$booking_completion_code}</span></span>  
                                                <div style='box-sizing: border-box;width: 100%;overflow: hidden;background-color: white;padding: 10px 10px;'>
                                                    <div style='border-left: 2px dashed #ccc'>
                                                        <p style='margin: 10px 0 30px 0;position: relative;padding-left: 15px;'>
                                                            <ons-icon icon='fa-dot-circle' size='18px' style='color: #8bc34a;position: absolute;font-size: 17px;left: -9px;top: -3px;background-color: white;'></ons-icon>
                                                            <span style='line-height:1.5em;font-weight: bold;font-size: 14px;width: 100%;display: inline-block;color: black;overflow: hidden;' >{$bookingdatasort_comp['pickup_address']}</span>                                
                                                        </p>
                                                        <p style='margin:0;position: relative;padding-left: 15px;'>
                                                            <ons-icon icon='fa-square' size='18px' style='color: rgb(250, 75, 75);position: absolute;font-size: 18px;left: -8px;bottom: 0px;border-bottom: 3px solid white;background-color: white;'></ons-icon>
                                                            <span style='line-height:1.5em;font-weight: bold;font-size: 14px;width: 100%;display: inline-block;color: black;overflow: hidden;' >{$bookingdatasort_comp['dropoff_address']}</span>                                
                                                        </p>
                                                    </div>
                                                </div>
                                                <span id='booking-list-item-data-{$bookingdatasort_comp['booking_id']}' type='text' style='display:none'>{$item_data_json}</span>
                                            </div>
                                            <div class='right'></div>
                                        
                                        </ons-list-item>";

            }


        }


        if(!empty($bookingdatasort['cancelled'])){
            //save date
            $day_num = date('j',strtotime($bookingdatasort['date'] . " UTC"));
            $month_name = date('F',strtotime($bookingdatasort['date'] . " UTC"));
            $b_date_format = $day_num . " " . __($month_name);
            $booking_cancelled .= "<ons-list-header style='font-size: 14px;font-weight:bold;margin:20px 0;'>{$b_date_format}</ons-list-header>";


            //format pending onride rides for this date
            foreach($bookingdatasort['cancelled'] as $bookingdatasort_canc){
                
                $booking_time = date('g:i A',strtotime($bookingdatasort_canc['date_created'] . ' UTC'));
                $booking_ptime = date('g:i A',strtotime($bookingdatasort_canc['date_created'] . ' UTC'));
                $booking_driver = isset($bookingdatasort_canc['driver_id']) ? $bookingdatasort_canc['driver_firstname'] ." " . $bookingdatasort_canc['driver_lastname'] : "N/A";
                $booking_completion_code = !empty($bookingdatasort_canc['completion_code']) ? $bookingdatasort_canc['completion_code'] : "N/A";
                
                $retry_btn = "";

                if(!$retry_button_shown && !$cancelled_bookings_count && strtotime($bookingdatasort_canc['date_created'] . ' UTC') > time() - 300){
                    $retry_btn = "<span onclick = 'event.stopPropagation();bookingretry({$bookingdatasort_canc['booking_id']})' style='display:inline-block;float:right;padding: 3px 5px;'><ons-icon icon='fa-refresh' size='18px' style='color:#3F9DD1'></ons-icon></span>";
                    $retry_button_shown = 1;
                }

                if($bookingdatasort_canc['status'] == 5){                    
                    $status = "<span style='color: #999999;font-weight: bold;border-radius:5px;border: thin solid #999999;padding: 3px 5px;font-size: 12px;'>". __("Expired") . "</span>";                    
                }elseif($bookingdatasort_canc['status'] == 4){
                    $status = "<span style='color: #e53935;font-weight: bold;border-radius:5px;border: thin solid #e53935;padding: 3px 5px;font-size: 12px;'>" . __("Cancelled by driver") . "</span>";;
                    $retry_btn = '';
                }elseif($bookingdatasort_canc['status'] == 2){
                    $status = "<span style='color: #e53935;font-weight: bold;border-radius:5px;border: thin solid #e53935;padding: 3px 5px;font-size: 12px;'>" . __("Cancelled by you") . "</span>";;
                    $retry_btn = '';
                }else{
                    $status = "<span style='color: #e53935;font-weight: bold;border-radius:5px;border: thin solid #e53935;padding: 3px 5px;font-size: 12px;'>" . __("Cancelled") . "</span>";;
                    $retry_btn = '';
                }
                
                $day_num = date('j',strtotime($bookingdatasort_canc['pickup_datetime'] . " UTC"));
                $month_name = date('F',strtotime($bookingdatasort_canc['pickup_datetime'] . " UTC"));
                $time_str = date('g:i A',strtotime($bookingdatasort_canc['pickup_datetime'] . " UTC"));
                $booking_pdate_time = $day_num . " " . __($month_name) . ", " . $time_str;

                $booking_type = $bookingdatasort_canc['scheduled'] == 1 ? 'Schedule ride' : 'Instant ride';
                //$drvr_photo = explode('/',$bookingdatasort_canc['drvr_photo']);
                $drvr_photo_file = isset($bookingdatasort_canc['drvr_photo']) ? SITE_URL . "ajaxphotofile.php?file=".$bookingdatasort_canc['drvr_photo'] : "0";
                $booking_payment_type = '';
                if(!empty($bookingdatasort_canc['payment_type'])){
                    if($bookingdatasort_canc['payment_type'] == 1){
                        $booking_payment_type = __("Cash");
                    }elseif($bookingdatasort_canc['payment_type'] == 2){
                        $booking_payment_type = __("Wallet");
                    }else{
                        $booking_payment_type = "Card";
                    }
                }
                $ride_filename = explode('/',$bookingdatasort_canc['ride_img']);
                $ride_image = SITE_URL . 'img/ride_imgs/' . array_pop($ride_filename);
                $booking_title = str_pad($bookingdatasort_canc['booking_id'] , 5, '0', STR_PAD_LEFT);

                $item_data = [];
                $item_data = array(
                                    'car_desc'=> $bookingdatasort_canc['ride_desc'],
                                    'driver_phone' => $bookingdatasort_canc['driver_phone'],
                                    'driver_car_details' => $bookingdatasort_canc['car_model'] . " [" . $bookingdatasort_canc['car_color'] . "]",
                                    'driver_plate_num' => $bookingdatasort_canc['car_plate_num'],
                                    'driver_rating' => $bookingdatasort_canc['driver_rating'],
                                    'payment_type' => $booking_payment_type,
                                    'pick_up_time'=> $booking_pdate_time,
                                    'driver_image' => $drvr_photo_file,
                                    'car_image' => $ride_image,
                                    'driver_name' => $booking_driver,
                                    'booking_cost' => $bookingdatasort_canc['cur_symbol'].$bookingdatasort_canc['estimated_cost'],
                                    'car_type' => $bookingdatasort_canc['ride_type'],
                                    'p_location' => $bookingdatasort_canc['pickup_address'],
                                    'd_location' => $bookingdatasort_canc['dropoff_address'],
                                    'booking_id' => $booking_title,
                                    'booking_type' => $booking_type,
                                    'booking_status' => $bookingdatasort_canc['status'],
                                    'coupon_code' => $bookingdatasort_canc['coupon_code'],
                                    'p_lat' => $bookingdatasort_canc['pickup_lat'],
                                    'p_lng' => $bookingdatasort_canc['pickup_long'],
                                    'd_lat' => $bookingdatasort_canc['dropoff_lat'],
                                    'd_lng' => $bookingdatasort_canc['dropoff_long'],
                                    'service_mode' => $bookingdatasort_canc['service_mode'],
                                    'coupon_discount_value' => $bookingdatasort_canc['coupon_discount_value'],
                                    'coupon_discount_type' => $bookingdatasort_canc['coupon_discount_type'],
                                    'actual_cost' => $bookingdatasort_canc['actual_cost'],
                                    'paid_amount' => $bookingdatasort_canc['paid_amount'],
                                    'referral_used' => $bookingdatasort_canc['referral_used'],
                                    'referral_discount_value' => $bookingdatasort_canc['referral_discount_value'],
                                    "waypoint1_address" => $bookingdatasort_canc['waypoint1_address'],
                                    "waypoint1_long" => $bookingdatasort_canc['waypoint1_long'],
                                    "waypoint1_lat" => $bookingdatasort_canc['waypoint1_lat'],
                                    "waypoint2_address" => $bookingdatasort_canc['waypoint2_address'],
                                    "waypoint2_long" => $bookingdatasort_canc['waypoint2_long'],
                                    "waypoint2_lat" => $bookingdatasort_canc['waypoint2_lat']
                                );

                $item_data_json = json_encode($item_data);
                
                $booking_cancelled .= "<ons-list-item onclick='showbookingdetails({$bookingdatasort_canc['booking_id']})' data-ridedesc='{$bookingdatasort_canc['ride_desc']}'  data-driverphone='{$bookingdatasort_canc['driver_phone']}' data-ptype='{$booking_payment_type}' data-put='{$booking_pdate_time}' data-driverimg='{$drvr_photo_file}' data-rideimg='{$ride_image}' data-drivername='{$booking_driver}' data-cost='{$bookingdatasort_canc['estimated_cost']}' data-ride='{$bookingdatasort_canc['ride_type']}' data-pul='{$bookingdatasort_canc['pickup_address']}' data-dol='{$bookingdatasort_canc['dropoff_address']}' data-btitle='{$booking_title}' id='booking-list-item-{$bookingdatasort_canc['booking_id']}' modifier='longdivider'>
                
                                            <div class='center'>
                                                <div style='width:100%;'><span class='list-item__title'>{$booking_time} </span> {$status} {$retry_btn}</div>
                                                <span style='text-align: left;margin-bottom: 15px;margin-top: 10px;color:#777;font-size:14px;'><span>ID:#{$booking_title} | OTP code: {$booking_completion_code}</span></span>   
                                                <div style='box-sizing: border-box;width: 90%;overflow: hidden;background-color: white;padding: 10px 10px;'>
                                                    <div style='border-left: 2px dashed #ccc'>
                                                        <p style='margin: 10px 0 30px 0;position: relative;padding-left: 15px;'>
                                                            <ons-icon icon='fa-dot-circle' size='18px' style='color: #8bc34a;position: absolute;font-size: 17px;left: -9px;top: -3px;background-color: white;'></ons-icon>
                                                            <span style='line-height:1.5em;font-weight: bold;font-size: 14px;width: 100%;display: inline-block;color: black;overflow: hidden;' >{$bookingdatasort_canc['pickup_address']}</span>                                
                                                        </p>
                                                        <p style='margin:0;position: relative;padding-left: 15px;'>
                                                            <ons-icon icon='fa-square' size='18px' style='color: rgb(250, 75, 75);position: absolute;font-size: 18px;left: -8px;bottom: 0px;border-bottom: 3px solid white;background-color: white;'></ons-icon>
                                                            <span style='line-height:1.5em;font-weight: bold;font-size: 14px;width: 100%;display: inline-block;color: black;overflow: hidden;' >{$bookingdatasort_canc['dropoff_address']}</span>                                
                                                        </p>
                                                    </div>
                                                </div>
                                                <span id='booking-list-item-data-{$bookingdatasort_canc['booking_id']}' type='text' style='display:none'>{$item_data_json}</span>
                                            </div>
                                        
                                        </ons-list-item>";

                $cancelled_bookings_count++;

            }


        }







    }

    
    $data_array = array("success"=>1,'pend_onride' => $booking_pend_onride,'booking_comp'=>$booking_completed,'booking_canc'=>$booking_cancelled);    
    echo json_encode($data_array); 
    exit;






}



function bookingretry(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $booking_id = (int) $_POST['bookingid'];

    $booking_data = [];

    $query = sprintf('SELECT * FROM %1$stbl_bookings WHERE %1$stbl_bookings.id = "%3$d" AND %1$stbl_bookings.user_id = "%2$d"', DB_TBL_PREFIX, $_SESSION['uid'],$booking_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $booking_data = mysqli_fetch_assoc($result);
        }else{
            $error = array("error"=>__("Booking information was not found"));
            echo json_encode($error); 
            exit;
        }
    }else{
        $error = array("error"=>"An error has occured");
        echo json_encode($error); 
        exit;
    }

    if(!empty($booking_data['driver_id'])){
        $error = array("error"=>__("Driver already assigned to booking"));
        echo json_encode($error); 
        exit;
    }elseif($booking_data['status'] != 5){
        $error = array("error"=>__("Booking status is invalid"));
        echo json_encode($error); 
        exit;
    }elseif(strtotime($booking_data['date_created'] . ' UTC') < time() - 300){
        $error = array("error"=>__("Booking cannot be restarted. Please create a new booking"));
        echo json_encode($error); 
        exit;
    }


    $query = sprintf('UPDATE %1$stbl_bookings SET %1$stbl_bookings.retry_booking = %1$stbl_bookings.retry_booking + 1, %1$stbl_bookings.status = 0, %1$stbl_bookings.pickup_datetime = "%2$s" WHERE %1$stbl_bookings.id = %3$d', DB_TBL_PREFIX,gmdate('Y-m-d H:i:s', time()), $booking_id);

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }


    
    $data_array = array("success"=>1);    
    echo json_encode($data_array); 
    exit;


}



function bookingcancel(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $booking_id = (int) $_POST['bookingid'];
    $booking_data = [];
    $comment = !empty($_POST['comment']) ? mysqli_real_escape_string($GLOBALS['DB'], $_POST['comment']) : '';


    //get booking details
    $query = sprintf('SELECT %1$stbl_bookings.scheduled,%1$stbl_bookings.scheduled_driver,%1$stbl_franchise.fwallet_amount,%1$stbl_drivers.wallet_amount AS driver_wallet_amount,%1$stbl_bookings.driver_commision,%1$stbl_bookings.franchise_commision,%1$stbl_users.wallet_amount AS user_wallet_amount,%1$stbl_rides_tariffs.cancel_cost,%1$stbl_rides_tariffs.ncancel_cost,%1$stbl_bookings.cur_symbol,%1$stbl_bookings.cur_exchng_rate,%1$stbl_bookings.cur_code,%1$stbl_bookings.status, %1$stbl_drivers.*,%1$stbl_drivers.disp_lang AS d_lang FROM %1$stbl_bookings
    INNER JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
    INNER JOIN %1$stbl_rides_tariffs ON %1$stbl_rides_tariffs.routes_id = %1$stbl_bookings.route_id AND %1$stbl_rides_tariffs.ride_id = %1$stbl_bookings.ride_id
    LEFT JOIN %1$stbl_franchise ON %1$stbl_franchise.id = %1$stbl_bookings.franchise_id
    LEFT JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id 
    WHERE %1$stbl_bookings.id = "%3$d" AND %1$stbl_users.user_id = "%2$d"', DB_TBL_PREFIX, $_SESSION['uid'],$booking_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $booking_data = mysqli_fetch_assoc($result);

            if($booking_data['status'] == 1 || $booking_data['status'] == 3){
                $error = array("error"=>__("Your trip has already started. You cannot cancel this trip. Ask your driver to cancel the trip"));
                echo json_encode($error); 
                exit;
            }elseif($booking_data['status'] == 2 || $booking_data['status'] == 4 || $booking_data['status'] == 5){
                $error = array("error"=>__("Booking already cancelled"));
                echo json_encode($error); 
                exit;
            }
            
        }else{
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); 
            exit;
        }
    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

    $query = sprintf('UPDATE %stbl_bookings SET `status` = 2, cancel_comment = "%s" WHERE id = "%d"', DB_TBL_PREFIX,$comment,$booking_id );

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ 
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

    $dlang = $booking_data['d_lang'];


    //update driver allocation entry to indicate the booking has been finalized
    $query = sprintf('UPDATE %stbl_driver_allocate SET `status` = %d WHERE booking_id = %d', DB_TBL_PREFIX, 4, $booking_id);
    $result = mysqli_query($GLOBALS['DB'], $query);


    //update user's cancelled rides count
    $query = sprintf('UPDATE %stbl_users SET cancelled_rides = cancelled_rides + 1 WHERE `user_id` = %d', DB_TBL_PREFIX, $_SESSION['uid']);
    $result = mysqli_query($GLOBALS['DB'], $query);


    if($booking_data['scheduled']){

        //update redis. remove this booking entry
        $redis = connectRedis();
        
        if($redis){        
            $redis->del("sbkdata:{$booking_id}"); //delete booking data
        }

        //get push tokken of scheduled driver and send notification only if driver hasn't been assigned yet to the booking
        if(empty($booking_data['driver_id']) && !empty($booking_data['scheduled_driver'])){
            $query = sprintf('SELECT push_notification_token,disp_lang FROM %stbl_drivers WHERE driver_id = %d', DB_TBL_PREFIX, $booking_data['scheduled_driver']);
            if($result = mysqli_query($GLOBALS['DB'], $query)){
                if(mysqli_num_rows($result)){
                    $row = mysqli_fetch_assoc($result);

                    $dlang = $row['disp_lang'];
                    //send notification to scheduled trip assigned driver that booking has been canceled
                    $device_tokens = !empty($row['push_notification_token']) ? $row['push_notification_token'] : 0;
                    if(!empty($device_tokens)){
                        $ext_msg_data = array(
                            'mode' => 1,
                            'title' => __("Scheduled booking cancelled",[],"r|{$dlang}"),
                            'message' => preg_replace("/\"/", '&#039;', htmlspecialchars(__("A customer has cancelled his scheduled booking which you were previously assigned to",[],"r|{$dlang}")))
                        );
                        sendPushNotification(__("Scheduled booking cancelled",[],"r|{$dlang}"), __("A customer has cancelled his scheduled booking which you were previously assigned to",[],"r|{$dlang}"),$device_tokens,null,json_encode($ext_msg_data),null,null);
                    }
                    
                }
            }
        }
        
        
    }
            


    if(!empty($booking_data['driver_id'])){ //driver already assigned or servicing the ride? deduct cancellation fee if included

        //check time of day
        $current_hour = (int) date('H');
        if($current_hour >= NIGHT_START || $current_hour <= NIGHT_END){
            //Night time            

            if($booking_data['user_wallet_amount'] > 0 && $booking_data['ncancel_cost'] > 0){

                $rider_wallet_balance = $booking_data['user_wallet_amount'] - $booking_data['ncancel_cost']; //deduct only cancellation cost without riders balance going into negative
                
                if($rider_wallet_balance < 0){
                    $booking_data['ncancel_cost'] = $booking_data['user_wallet_amount'];
                }
                
                $night_time_cancel_cost_converted = (float) $booking_data['ncancel_cost'] / $booking_data['cur_exchng_rate']; // convert to local currency
                //deduct value from rider wallet
                $query = sprintf('UPDATE %stbl_users SET wallet_amount = wallet_amount - %f WHERE user_id = %d', DB_TBL_PREFIX, $night_time_cancel_cost_converted,$_SESSION['uid']);
                $result = mysqli_query($GLOBALS['DB'], $query);
                $booking_title = str_pad($booking_id , 5, '0', STR_PAD_LEFT);
                $notification_msg = __("Penalty deduction for cancelling driver-assigned booking {---1}",["#{$booking_title}"]);

                //add a notification entry
                $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
                ("%d",0,"%s",3,"%s")', 
                DB_TBL_PREFIX,
                $_SESSION['uid'],
                mysqli_real_escape_string($GLOBALS['DB'],$notification_msg),
                gmdate('Y-m-d H:i:s', time()) 
                );
                $result = mysqli_query($GLOBALS['DB'], $query);

                //add to transaction table
                $transaction_id = crypto_string();
                $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                DB_TBL_PREFIX,
                $booking_data['cur_symbol'],
                $booking_data['cur_exchng_rate'],
                $booking_data['cur_code'],
                $booking_id,
                $transaction_id,
                $booking_data['ncancel_cost'],
                $booking_data['user_wallet_amount'] - $night_time_cancel_cost_converted,
                $_SESSION['uid'],
                0, //0 = rider
                mysqli_real_escape_string($GLOBALS['DB'],$notification_msg), 
                3,
                gmdate('Y-m-d H:i:s', time())

                );
                $result = mysqli_query($GLOBALS['DB'], $query);

                //update driver wallet
                $driver_earning_amount = (float) $booking_data['ncancel_cost'] * $booking_data['driver_commision'] / 100;
                $driver_earning_amount_converted = $driver_earning_amount / $booking_data['cur_exchng_rate'];

                //update database records of driver wallet
                $query = sprintf('UPDATE %stbl_drivers SET wallet_amount = wallet_amount + %f WHERE driver_id = "%d"', DB_TBL_PREFIX, $driver_earning_amount_converted,$booking_data['driver_id']);
                $result = mysqli_query($GLOBALS['DB'],$query);

                //Add this transaction to wallet transactions database table
                $transaction_id = crypto_string();
                $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                DB_TBL_PREFIX,
                $booking_data['cur_symbol'],
                $booking_data['cur_exchng_rate'],
                $booking_data['cur_code'],
                $booking_id,
                $transaction_id,
                $driver_earning_amount,
                $driver_earning_amount_converted + $booking_data['driver_wallet_amount'],
                $booking_data['driver_id'],
                1,
                "Earning for cancellation of booking: #" . $booking_id . " by rider", 
                2,
                gmdate('Y-m-d H:i:s', time())

                );

                $result = mysqli_query($GLOBALS['DB'],$query);

                

                //update franchise wallets
                if($booking_data['franchise_id'] == 1){ //owner / company franchise

                    $owner_franchise_earning = $booking_data['ncancel_cost'] - $driver_earning_amount;
                    $owner_franchise_earning_converted = $owner_franchise_earning / $booking_data['cur_exchng_rate'];

                    //update database records of owner franchise wallet
                    $query = sprintf('UPDATE %stbl_franchise SET fwallet_amount = fwallet_amount + %f WHERE id = "%d"', DB_TBL_PREFIX, $owner_franchise_earning_converted,1);
                    $result = mysqli_query($GLOBALS['DB'],$query);

                    //Add this transaction to wallet transactions database table
                    $transaction_id = crypto_string();
                    $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                    '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                    DB_TBL_PREFIX,
                    $booking_data['cur_symbol'],
                    $booking_data['cur_exchng_rate'],
                    $booking_data['cur_code'],
                    $booking_id,
                    $transaction_id,
                    $owner_franchise_earning,
                    $owner_franchise_earning_converted + $booking_data['fwallet_amount'],
                    $booking_data['franchise_id'],
                    2,
                    "Earning for cancellation of booking: #" . $booking_id . " by rider", 
                    2,
                    gmdate('Y-m-d H:i:s', time())

                    );

                    $result = mysqli_query($GLOBALS['DB'],$query);


                }else{

                    //get current wallet amount of franchise owner. always has id = 1 on franchise table
                    $owner_wallet_amount = 0.00;
                    $query = sprintf('SELECT fwallet_amount FROM %stbl_franchise WHERE id = 1', DB_TBL_PREFIX); //Get required user information from DB
                    if($result = mysqli_query($GLOBALS['DB'], $query)){
                        if(mysqli_num_rows($result)){
                            $row = mysqli_fetch_assoc($result); 
                            $owner_wallet_amount = $row['fwallet_amount'];                 ;
                        }     
                    }


                    $owner_and_franchise_earning = $booking_data['ncancel_cost'] - $driver_earning_amount;
                    $other_franchise_earning = $owner_and_franchise_earning * $booking_data['franchise_commision'] / 100;
                    $other_franchise_earning_converted = ((float) $other_franchise_earning / $booking_data['cur_exchng_rate']);

                    $owner_franchise_earning = $owner_and_franchise_earning - $other_franchise_earning;
                    $owner_franchise_earning_converted = $owner_franchise_earning / $booking_data['cur_exchng_rate'];

                    //update database records of owner franchise wallet
                    $query = sprintf('UPDATE %stbl_franchise SET fwallet_amount = fwallet_amount + %f WHERE id = "%d"', DB_TBL_PREFIX, $owner_franchise_earning_converted,1);
                    $result = mysqli_query($GLOBALS['DB'],$query);

                    //Add this transaction to wallet transactions database table
                    $transaction_id = crypto_string();
                    $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                    '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                    DB_TBL_PREFIX,
                    $booking_data['cur_symbol'],
                    $booking_data['cur_exchng_rate'],
                    $booking_data['cur_code'],
                    $booking_id,
                    $transaction_id,
                    $owner_franchise_earning,
                    $owner_franchise_earning_converted + $owner_wallet_amount,
                    1, //owner franchise ID
                    2,
                    "Earning for cancellation of booking: #" . $booking_id . " by rider", 
                    2,
                    gmdate('Y-m-d H:i:s', time())

                    );

                    $result = mysqli_query($GLOBALS['DB'],$query);


                    //update database records of the franchise wallet
                    $query = sprintf('UPDATE %stbl_franchise SET fwallet_amount = fwallet_amount + %f WHERE id = "%d"', DB_TBL_PREFIX, $owner_franchise_earning_converted,$booking_data['frnchise_id']);
                    $result = mysqli_query($GLOBALS['DB'],$query);

                    //Add this transaction to wallet transactions database table
                    $transaction_id = crypto_string();
                    $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                    '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                    DB_TBL_PREFIX,
                    $booking_data['cur_symbol'],
                    $booking_data['cur_exchng_rate'],
                    $booking_data['cur_code'],
                    $booking_id,
                    $transaction_id,
                    $other_franchise_earning,
                    $other_franchise_earning_converted + $booking_data['fwallet_amount'],
                    $booking_data['franchise_id'],
                    2,
                    "Earning for cancellation of booking: #" . $booking_id . " by rider", 
                    2,
                    gmdate('Y-m-d H:i:s', time())

                    );

                    $result = mysqli_query($GLOBALS['DB'],$query);



                }
                




            }
        }else{
            //Day time

            if($booking_data['user_wallet_amount'] > 0 && $booking_data['cancel_cost'] > 0){
                $rider_wallet_balance = $booking_data['user_wallet_amount'] - $booking_data['cancel_cost']; //deduct only cancellation cost without riders balance going into negative
                
                if($rider_wallet_balance < 0){
                    $booking_data['cancel_cost'] = $booking_data['user_wallet_amount'];
                }

                $day_time_cancel_cost_converted = (float) $booking_data['cancel_cost'] / $booking_data['cur_exchng_rate']; // convert to local currency
                //deduct value from rider wallet
                $query = sprintf('UPDATE %stbl_users SET wallet_amount = wallet_amount - %f WHERE user_id = %d', DB_TBL_PREFIX, $day_time_cancel_cost_converted,$_SESSION['uid']);
                $result = mysqli_query($GLOBALS['DB'], $query);
                $booking_title = str_pad($booking_id , 5, '0', STR_PAD_LEFT);
                $notification_msg = __("Penalty deduction for cancelling driver-assigned booking {---1}",["#{$booking_title}"]);

                //add a notification entry
                $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
                ("%d",0,"%s",3,"%s")', 
                DB_TBL_PREFIX,
                $_SESSION['uid'],
                mysqli_real_escape_string($GLOBALS['DB'],$notification_msg),
                gmdate('Y-m-d H:i:s', time()) 
                );
                $result = mysqli_query($GLOBALS['DB'], $query);

                //add to transaction table
                $transaction_id = crypto_string();
                $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                DB_TBL_PREFIX,
                $booking_data['cur_symbol'],
                $booking_data['cur_exchng_rate'],
                $booking_data['cur_code'],
                $booking_id,
                $transaction_id,
                $booking_data['cancel_cost'],
                $booking_data['user_wallet_amount'] - $day_time_cancel_cost_converted,
                $_SESSION['uid'],
                0, //0 = rider
                mysqli_real_escape_string($GLOBALS['DB'],$notification_msg), 
                3,
                gmdate('Y-m-d H:i:s', time())

                );
                $result = mysqli_query($GLOBALS['DB'], $query);

                //update driver wallet
                $driver_earning_amount = (float) $booking_data['cancel_cost'] * $booking_data['driver_commision'] / 100;
                $driver_earning_amount_converted = $driver_earning_amount / $booking_data['cur_exchng_rate'];

                //update database records of driver wallet
                $query = sprintf('UPDATE %stbl_drivers SET wallet_amount = wallet_amount + %f WHERE driver_id = "%d"', DB_TBL_PREFIX, $driver_earning_amount_converted,$booking_data['driver_id']);
                $result = mysqli_query($GLOBALS['DB'],$query);

                //Add this transaction to wallet transactions database table
                $transaction_id = crypto_string();
                $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                DB_TBL_PREFIX,
                $booking_data['cur_symbol'],
                $booking_data['cur_exchng_rate'],
                $booking_data['cur_code'],
                $booking_id,
                $transaction_id,
                $driver_earning_amount,
                $driver_earning_amount_converted + $booking_data['driver_wallet_amount'],
                $booking_data['driver_id'],
                1,
                "Earning for cancellation of booking: #" . $booking_id . " by rider", 
                2,
                gmdate('Y-m-d H:i:s', time())

                );

                $result = mysqli_query($GLOBALS['DB'],$query);

                

                //update franchise wallets
                if($booking_data['franchise_id'] == 1){ //owner / company franchise

                    $owner_franchise_earning = $booking_data['cancel_cost'] - $driver_earning_amount;
                    $owner_franchise_earning_converted = $owner_franchise_earning / $booking_data['cur_exchng_rate'];

                    //update database records of owner franchise wallet
                    $query = sprintf('UPDATE %stbl_franchise SET fwallet_amount = fwallet_amount + %f WHERE id = "%d"', DB_TBL_PREFIX, $owner_franchise_earning_converted,1);
                    $result = mysqli_query($GLOBALS['DB'],$query);

                    //Add this transaction to wallet transactions database table
                    $transaction_id = crypto_string();
                    $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                    '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                    DB_TBL_PREFIX,
                    $booking_data['cur_symbol'],
                    $booking_data['cur_exchng_rate'],
                    $booking_data['cur_code'],
                    $booking_id,
                    $transaction_id,
                    $owner_franchise_earning,
                    $owner_franchise_earning_converted + $booking_data['fwallet_amount'],
                    $booking_data['franchise_id'],
                    2,
                    "Earning for cancellation of booking: #" . $booking_id . " by rider", 
                    2,
                    gmdate('Y-m-d H:i:s', time())

                    );

                    $result = mysqli_query($GLOBALS['DB'],$query);


                }else{

                    //get current wallet amount of franchise owner. always has id = 1 on franchise table
                    $owner_wallet_amount = 0.00;
                    $query = sprintf('SELECT fwallet_amount FROM %stbl_franchise WHERE id = 1', DB_TBL_PREFIX); //Get required user information from DB
                    if($result = mysqli_query($GLOBALS['DB'], $query)){
                        if(mysqli_num_rows($result)){
                            $row = mysqli_fetch_assoc($result); 
                            $owner_wallet_amount = $row['fwallet_amount'];                 ;
                        }     
                    }


                    $owner_and_franchise_earning = $booking_data['cancel_cost'] - $driver_earning_amount;
                    $other_franchise_earning = $owner_and_franchise_earning * $booking_data['franchise_commision'] / 100;
                    $other_franchise_earning_converted = ((float) $other_franchise_earning / $booking_data['cur_exchng_rate']);

                    $owner_franchise_earning = $owner_and_franchise_earning - $other_franchise_earning;
                    $owner_franchise_earning_converted = $owner_franchise_earning / $booking_data['cur_exchng_rate'];

                    //update database records of owner franchise wallet
                    $query = sprintf('UPDATE %stbl_franchise SET fwallet_amount = fwallet_amount + %f WHERE id = "%d"', DB_TBL_PREFIX, $owner_franchise_earning_converted,1);
                    $result = mysqli_query($GLOBALS['DB'],$query);

                    //Add this transaction to wallet transactions database table
                    $transaction_id = crypto_string();
                    $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                    '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                    DB_TBL_PREFIX,
                    $booking_data['cur_symbol'],
                    $booking_data['cur_exchng_rate'],
                    $booking_data['cur_code'],
                    $booking_id,
                    $transaction_id,
                    $owner_franchise_earning,
                    $owner_franchise_earning_converted + $owner_wallet_amount,
                    1, //owner franchise ID
                    2,
                    "Earning for cancellation of booking: #" . $booking_id . " by rider", 
                    2,
                    gmdate('Y-m-d H:i:s', time())

                    );

                    $result = mysqli_query($GLOBALS['DB'],$query);


                    //update database records of the franchise wallet
                    $query = sprintf('UPDATE %stbl_franchise SET fwallet_amount = fwallet_amount + %f WHERE id = "%d"', DB_TBL_PREFIX, $owner_franchise_earning_converted,$booking_data['frnchise_id']);
                    $result = mysqli_query($GLOBALS['DB'],$query);

                    //Add this transaction to wallet transactions database table
                    $transaction_id = crypto_string();
                    $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                    '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                    DB_TBL_PREFIX,
                    $booking_data['cur_symbol'],
                    $booking_data['cur_exchng_rate'],
                    $booking_data['cur_code'],
                    $booking_id,
                    $transaction_id,
                    $other_franchise_earning,
                    $other_franchise_earning_converted + $booking_data['fwallet_amount'],
                    $booking_data['franchise_id'],
                    2,
                    "Earning for cancellation of booking: #" . $booking_id . " by rider", 
                    2,
                    gmdate('Y-m-d H:i:s', time())

                    );

                    $result = mysqli_query($GLOBALS['DB'],$query);



                }
            }
        }

        saveTravelRouteData($booking_id, $booking_data['driver_id']);

        //notify the driver via push notification
        $booking_title = str_pad($booking_id , 5, '0', STR_PAD_LEFT);
        $title = WEBSITE_NAME . " - Booking Cancelled";
        $body = __("The booking with ID({---1}) has been cancelled by the rider",["{$booking_title}"],"d|{$dlang}");
        $device_tokens = !empty($booking_data['push_notification_token']) ? $booking_data['push_notification_token'] : 0;
        if(!empty($device_tokens)){
            sendPushNotification($title,$body,$device_tokens,NULL,0);
        }

        

        //silent notification
        $title = "";
        $body = "";
        $data = array(
                    "action"=>"customer-cancelled",
                    "booking_id" => $booking_id                    
                );
        $device_tokens = !empty($booking_data['push_notification_token']) ? $booking_data['push_notification_token'] : 0;
        if(!empty($device_tokens)){
            sendPushNotification($title,$body,$device_tokens,$data,0);
        }

        //send through realtime notification
        sendRealTimeNotification('drvr-' . $booking_data['driver_id'], $data);
    }

    $data_array = array("success"=>1);    
    echo json_encode($data_array); 
    exit;

}




function saveTravelRouteData($booking_id, $driver_id){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    if(empty($booking_id) || empty($driver_id))return;

    
    $redis = connectRedis();
    
    if($redis){
        
        $redis->hDel("drvr_active_booking:{$driver_id}", $booking_id); //remove from driver active booking list so we dont keep recording driver route for this booking

        $driver_route_data = $redis->zRange("drv_route:{$driver_id}:{$booking_id}",0,-1);
        
        if(empty($driver_route_data))return;

        $driver_route_data_serialized = mysqli_real_escape_string($GLOBALS['DB'],serialize($driver_route_data));

        //save to database
        $query = sprintf('INSERT INTO %stbl_driver_travel_route (booking_id, driver_id, route_data) VALUES ("%d","%d","%s")', DB_TBL_PREFIX, $booking_id, $driver_id, $driver_route_data_serialized);
        $result = mysqli_query($GLOBALS['DB'], $query);

        //free memory used by route data on redis
        $redis->del("drv_route:{$driver_id}:{$booking_id}");
        
    }

}




function bookingCancelDriverSearch(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $booking_id = (int) $_POST['bookingid'];
    $booking_data = [];

    //get booking details
    $query = sprintf('SELECT %1$stbl_bookings.id, %1$stbl_bookings.driver_id, %1$stbl_bookings.status FROM %1$stbl_bookings
    INNER JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
    LEFT JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id 
    WHERE %1$stbl_bookings.id = "%3$d" AND %1$stbl_users.user_id = "%2$d"', DB_TBL_PREFIX, $_SESSION['uid'],$booking_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $booking_data = mysqli_fetch_assoc($result);

            if($booking_data['driver_id'] || $booking_data['status'] == 1 || $booking_data['status'] == 3){
                $error = array("error"=>__("Your trip has already started. You cannot cancel this trip. Ask your driver to cancel the trip"));
                echo json_encode($error); 
                exit;
            }elseif($booking_data['status'] == 2 || $booking_data['status'] == 4 || $booking_data['status'] == 5){
                $error = array("error"=>__("Booking already cancelled"));
                echo json_encode($error); 
                exit;
            }
            
        }else{
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); 
            exit;
        }
    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

    $query = sprintf('UPDATE %stbl_bookings SET `status` = 2 WHERE id = "%d"', DB_TBL_PREFIX,$booking_id );

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ 
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

    
    //update driver allocation entry to indicate the booking has been finalized
    $query = sprintf('UPDATE %stbl_driver_allocate SET `status` = %d WHERE booking_id = %d', DB_TBL_PREFIX, 4, $booking_id);
    $result = mysqli_query($GLOBALS['DB'], $query);


    //update user's cancelled rides count
    $query = sprintf('UPDATE %stbl_users SET cancelled_rides = cancelled_rides + 1 WHERE `user_id` = %d', DB_TBL_PREFIX, $_SESSION['uid']);
    $result = mysqli_query($GLOBALS['DB'], $query);

    
    $data_array = array("success"=>1);    
    echo json_encode($data_array); 
    exit;

}



function getChatContent($mode = 0){

    if(empty($_SESSION['loggedin'])){

        if($mode == 1){
            return array("error"=> 1);
            exit;
        }

        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $booking_id = (int) $_GET['booking_id'];

    if(empty($booking_id)){

        if($mode == 1){
            return array("error"=> 1);
            exit;
        }

        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

    $chat_data = [];
    $chat_messages_html = '';
    $count = 0;
    $chat_new_content_status = 0;

    //verify this user is accesing his chat data
    $query = sprintf('SELECT * FROM %stbl_bookings WHERE id = %d AND user_id = %d', DB_TBL_PREFIX,$booking_id,$_SESSION['uid']);
    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(!mysqli_num_rows($result)){
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); 
            exit;
        }

    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

    //get chat messages for this booking
    $query = sprintf('SELECT %1$stbl_users.photo_file AS user_photo_file,%1$stbl_users.firstname AS user_firstname,%1$stbl_drivers.firstname AS driver_firstname,%1$stbl_drivers.photo_file AS driver_photo_file, %1$stbl_chats.chat_msg, %1$stbl_chats.user_id AS chat_user_id, %1$stbl_chats.driver_id AS chat_driver_id FROM %1$stbl_chats 
    LEFT JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_chats.user_id
    LEFT JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_chats.driver_id
    WHERE %1$stbl_chats.booking_id = %2$d ORDER BY date_created ASC', 
        DB_TBL_PREFIX, 
        $booking_id,
        $_SESSION['uid']
    );

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){

            while($row = mysqli_fetch_assoc($result)){
                if($row['chat_driver_id'] != 0){
                    //driver chat message
                    $count++;
                    $driver_photo = isset($row['driver_photo_file']) ? $row['driver_photo_file'] : "0";
                    $driver_photo = SITE_URL . "ajaxphotofile.php?file=" . $driver_photo;
                    //$chat_messages_html .= "<div style='text-align:left;margin-top: 5px;'><div style='background-color:#1e88e5;width:55%;border-radius:10px;padding:5px;display:inline-block'><div><img style='width:32px;border-radius:50%;vertical-align: middle;' src='{$driver_photo}' /> <span style='font-weight:bold; font-size:14px;color:white;'>{$row['driver_firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;'>{$row['chat_msg']}</p></div></div>";
                    $chat_messages_html .= "<div style='text-align:left;margin-top: 5px;'><div style='background-color:#bbdefb;width:60%;border-radius:10px;padding:10px;display:inline-block'><div><i style='font-size: 22px;color: black;vertical-align: middle;' class='fa fa-user-circle-o'></i> <span style='font-weight:bold; font-size:14px;color:black;'>{$row['driver_firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 16px;'>{$row['chat_msg']}</p></div></div>";

                }elseif($row['chat_user_id'] != 0){
                    //rider chat message
                    $user_photo = isset($row['user_photo_file']) ? $row['user_photo_file'] : "0";
                    $user_photo = SITE_URL . "ajaxuserphotofile.php?file=" . $user_photo;
                    //$chat_messages_html .= "<div style='text-align:right;margin-top: 5px;'><div style='background-color:#7cb342;width:55%;border-radius:10px;padding:5px;display:inline-block'><div style='text-align:left'><img style='width:32px;border-radius:50%;vertical-align: middle;' src='{$user_photo}' /> <span style='font-weight:bold; font-size:14px;color:white;'>{$row['user_firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;'>{$row['chat_msg']}</p></div></div>";
                    $chat_messages_html .= "<div style='text-align:right;margin-top: 5px;'><div style='background-color:#c8e6c9;width:60%;border-radius:10px;padding:10px;display:inline-block'><div style='text-align:left'><i style='font-size: 22px;color: black;vertical-align: middle;' class='fa fa-user-circle-o'></i> <span style='font-weight:bold; font-size:14px;color:black;'>{$row['user_firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 16px;'>{$row['chat_msg']}</p></div></div>";

                }                

            }

            if(isset($_SESSION['chats'][$booking_id]['driver_msg_count'])){
                if($count > $_SESSION['chats'][$booking_id]['driver_msg_count']){
                    $chat_new_content_status = 1;
                    $_SESSION['chats'][$booking_id]['driver_msg_count'] = $count;
                }
            }else{
                $_SESSION['chats'][$booking_id]['driver_msg_count'] = 0;
                if($count > $_SESSION['chats'][$booking_id]['driver_msg_count']){
                    $chat_new_content_status = 1;
                    $_SESSION['chats'][$booking_id]['driver_msg_count'] = $count;
                }
            }

            if($mode == 1){
                return array("success"=> 1,"chat_content"=>$chat_messages_html,"chat_new_content"=>$chat_new_content_status);
                exit;
            }

            $resp = array("success"=> 1,"chat_content"=>$chat_messages_html,"chat_new_content"=>$chat_new_content_status);
            echo json_encode($resp); 
            exit;

        }else{

            if($mode == 1){
                return array("success"=> 1,"chat_content"=>"","chat_new_content"=>0);
                exit;
            }

            $resp = array("success"=> 1,"chat_content"=>"","chat_new_content"=>0);
            echo json_encode($resp); 
            exit;
        }
    }else{

        if($mode == 1){
            return array("error"=> 1);
            exit;
        }
        
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit; 
    }

}


function chatSendMsg(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $driver_data = [];
    $booking_id = (int) $_GET['booking_id'];
    $chat_msg = mysqli_real_escape_string($GLOBALS['DB'], $_GET['chat_msg']);

    if(empty($chat_msg)){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }
    

    if(empty($booking_id)){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }


    //first get driver push_notification token
    $query = sprintf('SELECT %1$stbl_drivers.driver_id, %1$stbl_drivers.push_notification_token FROM %1$stbl_bookings 
    INNER JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id
    WHERE %1$stbl_bookings.id = %2$d', DB_TBL_PREFIX, $booking_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){           
            
            $driver_data = mysqli_fetch_assoc($result);   
            

        }else{
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); 
            exit;
        }

    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }


    //save chat message in DB
    $query = sprintf('INSERT INTO %stbl_chats (chat_msg,`user_id`,booking_id,date_created) VALUES ("%s","%d","%d","%s")',
        DB_TBL_PREFIX,
        $chat_msg,
        $_SESSION['uid'],
        $booking_id,
        gmdate('Y-m-d H:i:s', time())

    );

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

    $chat_messages_data = getChatContent(1);

    $title = "";
    $body = "";
    $data = array(
        "action"=>"chat-message",
        "booking_id"=>$booking_id,
        "message"=> $chat_msg
    ); 

    $device_tokens = !empty($driver_data['push_notification_token']) ? $driver_data['push_notification_token'] : 0;
    if(!empty($device_tokens)){
        sendPushNotification($title,$body,$device_tokens,$data,0);
    }

    sendRealTimeNotification('drvr-' . $driver_data['driver_id'], $data);

    if(isset($chat_messages_data['error'])){
        $resp = array("success"=> 1,"chat_content"=>"","chat_new_content"=>0);
        echo json_encode($resp); 
        exit;
    }

    $resp = array("success"=> 1,"chat_content"=>$chat_messages_data['chat_content'],"chat_new_content"=>$chat_messages_data['chat_new_content']);
    echo json_encode($resp); 
    exit;



}



function chatSupportSendMsg(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $chat_messages_html = '';
    $chat_msg = mysqli_real_escape_string($GLOBALS['DB'], $_GET['chat_msg']);

    if(empty($chat_msg)){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }
    

    
    
    //save chat message in DB
    $query = sprintf('INSERT INTO %stbl_chatsupport (chat_msg,`user_id`,session_status,date_created) VALUES ("%s","%d","%d","%s")',
        DB_TBL_PREFIX,
        $chat_msg,
        $_SESSION['uid'],
        1,
        gmdate('Y-m-d H:i:s', time())

    );

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }


    //get all previous chats 
    $query = sprintf('SELECT * FROM %stbl_chatsupport WHERE `user_id` = %d OR rider_recipient_id = %d ORDER BY date_created ASC', DB_TBL_PREFIX, $_SESSION['uid'], $_SESSION['uid']);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){ 
                   
            while($row = mysqli_fetch_assoc($result)){
                $date_created = date('Y-m-d H:i', strtotime($row['date_created'] . ' UTC'));
                $admin_name = WEBSITE_NAME;
                if($row['rider_recipient_id'] != 0){
                    //user chat message                    
                    
                    $chat_messages_html .= "<div style='text-align:left;margin-top: 5px;'><div style='background-color:#1e88e5;width:55%;border-radius:10px;padding:5px;display:inline-block'><div><i style='font-size: 22px;color: white;vertical-align: middle;' class='fa fa-user-circle-o'></i> <span style='font-weight:bold; font-size:14px;color:white;'>{$admin_name}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;color:white;'>{$row['chat_msg']}</p><p style='margin: 5px 0;text-align: left;font-size: 10px;color:white;'>{$date_created}</p></div></div>";

                }elseif($row['user_id'] != 0){
                    //admin chat message

                    
                    //$chat_messages_html .= "<div style='text-align:right;margin-top: 5px;'><div style='background-color:#7cb342;width:55%;border-radius:10px;padding:5px;display:inline-block'><div style='text-align:left'><img style='width:32px;border-radius:50%;vertical-align: middle;' src='{$user_photo}' /> <span style='font-weight:bold; font-size:14px;color:white;'>{$row['user_firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;'>{$row['chat_msg']}</p></div></div>";
                    $chat_messages_html .= "<div style='text-align:right;margin-top: 5px;'><div style='background-color:#7cb342;width:55%;border-radius:10px;padding:5px;display:inline-block'><div style='text-align:left'><i style='font-size: 22px;color: white;vertical-align: middle;' class='fa fa-user-circle-o'></i> <span style='font-weight:bold; font-size:14px;color:white;'>{$_SESSION['firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;color:white;'>{$row['chat_msg']}</p><p style='margin: 5px 0;text-align: left;font-size: 10px;color:white;'>{$date_created}</p></div></div>";

                }                

            }
            

        }

    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

    
    $data = array(
        "action"=>"chat-smessage",
        "message"=> $chat_msg,
        "user_id" => $_SESSION['uid']
    ); 

    //sendRealTimeNotification('webadmin-1', $data);

    if(empty($chat_messages_html)){
        $resp = array("success"=> 1,"chat_content"=>"<p style='position:absolute;top:50%;left:50%;transform:translate(-50%,-50%)'>No messages</p>");
        echo json_encode($resp); 
        exit;
    }

    $resp = array("success"=> 1,"chat_content"=>$chat_messages_html);
    echo json_encode($resp); 
    exit;



}


function getChatSupportMsg(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $chat_messages_html = '';
    $count = 0;
    $new_message = 0;

    //get all previous chats 
    $query = sprintf('SELECT * FROM %stbl_chatsupport WHERE `user_id` = %d OR rider_recipient_id = %d ORDER BY date_created ASC', DB_TBL_PREFIX, $_SESSION['uid'], $_SESSION['uid']);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){ 
                   
            while($row = mysqli_fetch_assoc($result)){
                $count++;
                $date_created = date('Y-m-d H:i', strtotime($row['date_created'] . ' UTC'));
                $admin_name = WEBSITE_NAME;
                if($row['rider_recipient_id'] != 0){
                    //user chat message                    
                    
                    $chat_messages_html .= "<div style='text-align:left;margin-top: 5px;'><div style='background-color:#1e88e5;width:55%;border-radius:10px;padding:5px;display:inline-block'><div><i style='font-size: 22px;color: white;vertical-align: middle;' class='fa fa-user-circle-o'></i> <span style='font-weight:bold; font-size:14px;color:white;'>{$admin_name}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;color:white;'>{$row['chat_msg']}</p><p style='margin: 5px 0;text-align: left;font-size: 10px;color:white;'>{$date_created}</p></div></div>";

                }elseif($row['user_id'] != 0){
                    //admin chat message

                    
                    //$chat_messages_html .= "<div style='text-align:right;margin-top: 5px;'><div style='background-color:#7cb342;width:55%;border-radius:10px;padding:5px;display:inline-block'><div style='text-align:left'><img style='width:32px;border-radius:50%;vertical-align: middle;' src='{$user_photo}' /> <span style='font-weight:bold; font-size:14px;color:white;'>{$row['user_firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;'>{$row['chat_msg']}</p></div></div>";
                    $chat_messages_html .= "<div style='text-align:right;margin-top: 5px;'><div style='background-color:#7cb342;width:55%;border-radius:10px;padding:5px;display:inline-block'><div style='text-align:left'><i style='font-size: 22px;color: white;vertical-align: middle;' class='fa fa-user-circle-o'></i> <span style='font-weight:bold; font-size:14px;color:white;'>{$_SESSION['firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;color:white;'>{$row['chat_msg']}</p><p style='margin: 5px 0;text-align: left;font-size: 10px;color:white;'>{$date_created}</p></div></div>";

                }                

            }
            

        }

    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

    if(isset($_SESSION['chat_support_msg_count'])){
        if($count > $_SESSION['chat_support_msg_count']){
            $new_message = 1;
            $_SESSION['chat_support_msg_count'] = $count;
        }
    }else{
        if($count){
            $_SESSION['chat_support_msg_count'] = $count;
            $new_message = 1;
        }else{
            $_SESSION['chat_support_msg_count'] = 0;
            $new_message = 0;
        }
    }

    
    if(empty($chat_messages_html)){
        $resp = array("success"=> 1,"chat_content"=>"<p style='position:absolute;top:50%;left:50%;transform:translate(-50%,-50%)'>No messages</p>", "new_msg" => $new_message);
        echo json_encode($resp); 
        exit;
    }

    $resp = array("success"=> 1,"chat_content"=>$chat_messages_html, "new_msg" => $new_message);
    echo json_encode($resp); 
    exit;

}



function chatSendImg(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $driver_data = [];
    $booking_id = (int) $_POST['booking_id'];


    if(empty($booking_id)){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }
    

    if(!empty($_POST['chat_img'])){
        //save user passport
        $uploaded_photo_encoded = $_POST['chat_img']; //Get Base64 encoded image data. Encoded by our cropit jQuery plugin
        $uploaded_photo_encoded_array = explode(',', $uploaded_photo_encoded);
        $image_data = array_pop($uploaded_photo_encoded_array);
        $uploaded_photo_decoded = base64_decode($image_data); //Decode the data

        
        if(!$uploaded_photo_decoded){ //Verify that data is valid base64 data
            
            $resp = array("success"=> 1,"chat_content"=>"","chat_new_content"=>0);
            echo json_encode($resp); 
            exit;
        } 

        //prepare filename and save the file. Cropit plugin has been configured to export base64 image data in JPEG format. We should be expecting a JPEG image data then.
        $filename =  crypto_string('distinct',20);

        @mkdir(realpath(CUSTOMER_PHOTO_PATH) . "/". $filename[0] . "/" . $filename[1] . "/" . $filename[2], 0777, true);

        
        $image_path = realpath(CUSTOMER_PHOTO_PATH) .  "/". $filename[0] . "/" . $filename[1] . "/" . $filename[2] . "/";
        $file = $image_path . $filename . ".jpg";

    
        
        file_put_contents($file, $uploaded_photo_decoded); //store the photo to disk.     

        $chat_img_filename = $filename . ".jpg";

    }else{
        $resp = array("success"=> 1,"chat_content"=>"","chat_new_content"=>0);
        echo json_encode($resp); 
        exit;
    }

    
    $image_url = SITE_URL . "ajaxuserphotofile.php?file={$chat_img_filename}";
    $chat_msg = "<img style='width:150px' src='{$image_url}' />"; 
	$chat_msg = mysqli_real_escape_string($GLOBALS['DB'], $chat_msg);   

    


    //first get driver push_notification token
    $query = sprintf('SELECT %1$stbl_drivers.driver_id, %1$stbl_drivers.push_notification_token FROM %1$stbl_bookings 
    INNER JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id
    WHERE %1$stbl_bookings.id = %2$d', DB_TBL_PREFIX, $booking_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){           
            
            $driver_data = mysqli_fetch_assoc($result);   
            

        }else{
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); 
            exit;
        }

    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }


    //save chat message in DB
    $query = sprintf('INSERT INTO %stbl_chats (chat_msg,`user_id`,booking_id,date_created) VALUES ("%s","%d","%d","%s")',
        DB_TBL_PREFIX,
        $chat_msg,
        $_SESSION['uid'],
        $booking_id,
        gmdate('Y-m-d H:i:s', time())

    );

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

    $chat_messages_data = getChatContentImg(1);

    $title = "";
    $body = "";
    $data = array(
        "action"=>"chat-message",
        "booking_id"=>$booking_id,
        "message"=> ''
    ); 

    $device_tokens = !empty($driver_data['push_notification_token']) ? $driver_data['push_notification_token'] : 0;
    if(!empty($device_tokens)){
        sendPushNotification($title,$body,$device_tokens,$data,0);
    }

    sendRealTimeNotification('drvr-' . $driver_data['driver_id'], $data);

    if(isset($chat_messages_data['error'])){
        $resp = array("success"=> 1,"chat_content"=>"","chat_new_content"=>0);
        echo json_encode($resp); 
        exit;
    }

    $resp = array("success"=> 1,"chat_content"=>$chat_messages_data['chat_content'],"chat_new_content"=>$chat_messages_data['chat_new_content']);
    echo json_encode($resp); 
    exit;


}



function getChatContentImg($mode = 0){

    if(empty($_SESSION['loggedin'])){

        if($mode == 1){
            return array("error"=> 1);
            exit;
        }

        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $booking_id = (int) $_POST['booking_id'];

    if(empty($booking_id)){

        if($mode == 1){
            return array("error"=> 1);
            exit;
        }

        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

    $chat_data = [];
    $chat_messages_html = '';
    $count = 0;
    $chat_new_content_status = 0;

    //verify this user is accesing his chat data
    $query = sprintf('SELECT * FROM %stbl_bookings WHERE id = %d AND user_id = %d', DB_TBL_PREFIX,$booking_id,$_SESSION['uid']);
    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(!mysqli_num_rows($result)){
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); 
            exit;
        }

    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

    //get chat messages for this booking
    $query = sprintf('SELECT %1$stbl_users.photo_file AS user_photo_file,%1$stbl_users.firstname AS user_firstname,%1$stbl_drivers.firstname AS driver_firstname,%1$stbl_drivers.photo_file AS driver_photo_file, %1$stbl_chats.chat_msg, %1$stbl_chats.user_id AS chat_user_id, %1$stbl_chats.driver_id AS chat_driver_id FROM %1$stbl_chats 
    LEFT JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_chats.user_id
    LEFT JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_chats.driver_id
    WHERE %1$stbl_chats.booking_id = %2$d ORDER BY date_created ASC', 
        DB_TBL_PREFIX, 
        $booking_id,
        $_SESSION['uid']
    );

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){

            while($row = mysqli_fetch_assoc($result)){
                if($row['chat_driver_id'] != 0){
                    //driver chat message
                    $count++;
                    $driver_photo = isset($row['driver_photo_file']) ? $row['driver_photo_file'] : "0";
                    $driver_photo = SITE_URL . "ajaxphotofile.php?file=" . $driver_photo;
                    //$chat_messages_html .= "<div style='text-align:left;margin-top: 5px;'><div style='background-color:#1e88e5;width:55%;border-radius:10px;padding:5px;display:inline-block'><div><img style='width:32px;border-radius:50%;vertical-align: middle;' src='{$driver_photo}' /> <span style='font-weight:bold; font-size:14px;color:white;'>{$row['driver_firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;'>{$row['chat_msg']}</p></div></div>";
                    $chat_messages_html .= "<div style='text-align:left;margin-top: 5px;'><div style='background-color:#bbdefb;width:60%;border-radius:10px;padding:5px;display:inline-block'><div><i style='font-size: 22px;color: black;vertical-align: middle;' class='fa fa-user-circle-o'></i> <span style='font-weight:bold; font-size:14px;color:black;'>{$row['driver_firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;'>{$row['chat_msg']}</p></div></div>";

                }elseif($row['chat_user_id'] != 0){
                    //rider chat message
                    $user_photo = isset($row['user_photo_file']) ? $row['user_photo_file'] : "0";
                    $user_photo = SITE_URL . "ajaxuserphotofile.php?file=" . $user_photo;
                    //$chat_messages_html .= "<div style='text-align:right;margin-top: 5px;'><div style='background-color:#7cb342;width:55%;border-radius:10px;padding:5px;display:inline-block'><div style='text-align:left'><img style='width:32px;border-radius:50%;vertical-align: middle;' src='{$user_photo}' /> <span style='font-weight:bold; font-size:14px;color:white;'>{$row['user_firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;'>{$row['chat_msg']}</p></div></div>";
                    $chat_messages_html .= "<div style='text-align:right;margin-top: 5px;'><div style='background-color:#c8e6c9;width:60%;border-radius:10px;padding:5px;display:inline-block'><div style='text-align:left'><i style='font-size: 22px;color: black;vertical-align: middle;' class='fa fa-user-circle-o'></i> <span style='font-weight:bold; font-size:14px;color:black;'>{$row['user_firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;'>{$row['chat_msg']}</p></div></div>";

                }                

            }

            if(isset($_SESSION['chats'][$booking_id]['driver_msg_count'])){
                if($count > $_SESSION['chats'][$booking_id]['driver_msg_count']){
                    $chat_new_content_status = 1;
                    $_SESSION['chats'][$booking_id]['driver_msg_count'] = $count;
                }
            }else{
                $_SESSION['chats'][$booking_id]['driver_msg_count'] = 0;
                if($count > $_SESSION['chats'][$booking_id]['driver_msg_count']){
                    $chat_new_content_status = 1;
                    $_SESSION['chats'][$booking_id]['driver_msg_count'] = $count;
                }
            }

            if($mode == 1){
                return array("success"=> 1,"chat_content"=>$chat_messages_html,"chat_new_content"=>$chat_new_content_status);
                exit;
            }

            $resp = array("success"=> 1,"chat_content"=>$chat_messages_html,"chat_new_content"=>$chat_new_content_status);
            echo json_encode($resp); 
            exit;

        }else{

            if($mode == 1){
                return array("success"=> 1,"chat_content"=>"","chat_new_content"=>0);
                exit;
            }

            $resp = array("success"=> 1,"chat_content"=>"","chat_new_content"=>0);
            echo json_encode($resp); 
            exit;
        }
    }else{

        if($mode == 1){
            return array("error"=> 1);
            exit;
        }
        
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit; 
    }

}





function syncservertime(){

    $server_time = round(microtime(true) * 1000);
    $data = array(
        'success'=>1,
        'server_time'=>$server_time
    );
    echo json_encode($data); 
    exit;

}




function getpersoninfo(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $booking_id = (int) $_GET['booking_id'];

    $query = sprintf('SELECT %1$stbl_drivers.driver_id,%1$stbl_drivers.firstname,%1$stbl_drivers.lastname, %1$stbl_drivers.driver_rating, %1$stbl_drivers.account_create_date,%1$stbl_drivers.completed_rides,%1$stbl_drivers.cancelled_rides,%1$stbl_drivers.rejected_rides, %1$stbl_drivers.photo_file FROM %1$stbl_bookings 
    INNER JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id
    WHERE %1$stbl_bookings.id = %2$d', DB_TBL_PREFIX, $booking_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $user_data = mysqli_fetch_assoc($result);
            $user_data['lastname'] = "";
            $comments_ratings = '';
            $photo_file = isset($user_data['photo_file']) ? $user_data['photo_file'] : "0";
            //get customer comments for this driver
            $query = sprintf('SELECT %1$stbl_users.firstname, %1$stbl_ratings_users.user_comment, %1$stbl_ratings_users.user_rating FROM %1$stbl_ratings_users 
            INNER JOIN %1$stbl_bookings ON %1$stbl_bookings.id = %1$stbl_ratings_users.booking_id
            INNER JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_ratings_users.user_id
            WHERE %1$stbl_bookings.driver_id = %2$d ORDER BY %1$stbl_ratings_users.id DESC LIMIT 100', DB_TBL_PREFIX, $user_data['driver_id']);

            if($result = mysqli_query($GLOBALS['DB'], $query)){
                if(mysqli_num_rows($result)){
                    while($row = mysqli_fetch_assoc($result)){
                        $comments_ratings.= "<div style='padding: 10px 5px;border-top:thin solid #ccc;'> <div><span style='font-size:12px;font-weight:bold;margin-bottom:5px;'>{$row['firstname']}</span></div> <div><img src='img/rating-{$row['user_rating']}.png' style='width:50px;' /></div> <div><span style='font-size:14px;'>{$row['user_comment']}</span></div> </div>"; 
                    }

                    $comments_ratings = "<div style='font-size:14px;font-weight:bold;padding: 5px 5px;text-align:left;'>" . __("Comments and Ratings") . "</div>" . $comments_ratings;
                }

            }

            if(empty($comments_ratings)){
                $comments_ratings = "<div style='padding: 50px 5px;text-align:center;'>" .  __("No comments and ratings available") . "</div>";
            }

            $data = array("success"=> 1, "userdata" => $user_data, 'comments' => $comments_ratings, 'photo' => SITE_URL . "ajaxphotofile.php?file=" . $photo_file);
            echo json_encode($data); 
            exit;


        }
    }

    $error = array("error"=>1);
    echo json_encode($error); 
    exit;


}


function getusernotifications(){


    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $notification_data = [];
    $notification_data_date_sort = [];
    $formatted_notifications = '';
    $num_of_notifications = 0;
    $user_route_id = !empty($_SESSION['route_id']) ? $_SESSION['route_id'] : 0;

    $query = sprintf('SELECT COUNT(*) FROM %stbl_notifications WHERE (person_id = "%d" AND user_type = 0) OR (route_id = %d AND n_type = 5 AND user_type = 0)', DB_TBL_PREFIX, $_SESSION['uid'], $user_route_id); //Get required user information from DB
    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
    
           $row = mysqli_fetch_assoc($result);
              
          $num_of_notifications = $row['COUNT(*)'];
             
         }
        mysqli_free_result($result);
    }   


    
    
    $query = sprintf('SELECT *, DATE(date_created) AS created_date FROM %stbl_notifications WHERE (person_id = "%d" AND user_type = 0) OR (route_id = %d AND n_type = 5 AND user_type = 0) ORDER BY date_created DESC LIMIT 0,20', DB_TBL_PREFIX, $_SESSION['uid'], $user_route_id); //Get required user information from DB

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){

            while($row = mysqli_fetch_assoc($result)){
                
                $notification_data_date_sort[$row['created_date']]['date'] = $row['created_date'];
                $notification_data_date_sort[$row['created_date']]['notifications'][] = $row;
            }
            
            mysqli_free_result($result);

        }else{
            $error = array("nodata"=>__("You do not have any notifications"));
            echo json_encode($error); 
            exit; 
        }
        
    }
    else{ 

        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
        
    }

    

    

    //format data for display on app
    foreach($notification_data_date_sort as $notificationdatadatesort){
        if(!empty($notificationdatadatesort['notifications'])){
            $day_num = date('j',strtotime($notificationdatadatesort['date'] . " UTC"));
            $month_name = date('F',strtotime($notificationdatadatesort['date'] . " UTC"));
            $notifications_formated_date = $day_num . " " . __($month_name);
            $formatted_notifications .= "<ons-list-header style='font-size: 14px;font-weight:bold;margin:20px 0;'>{$notifications_formated_date}</ons-list-header>";
            
            foreach($notificationdatadatesort['notifications'] as $date_notifications){
                if($date_notifications['n_type'] == 5){
                    $close_btn = "";
                }else{
                    $close_btn = "<span style='float:right;'><ons-icon onclick = 'notifydelete({$date_notifications['id']})' icon='fa-times-circle' size='18px' style='color:red'></ons-icon></span>";
                }
                
                $notification_time = date('g:i A',strtotime($date_notifications['date_created'] . ' UTC'));
                $notification_type = '';
                
                switch($date_notifications['n_type']){
                    
                    case 1:
                    $notification_type = "<ons-icon icon='fa-bullhorn' size='14px' style='color:green;'></ons-icon>";
                    break;

                    case 2:
                    $notification_type = "<ons-icon icon='fa-bell' size='14px' style='color:blue;'></ons-icon>";
                    break;

                    case 3:
                    $notification_type = "<ons-icon icon='fa-money' size='14px' style='color:teal;'></ons-icon>";
                    break;

                    case 4:
                    $notification_type = "<ons-icon icon='fa-flag-checkered' size='14px' style='color:#9b9b0b;'></ons-icon>";
                    break;

                    case 5:
                    $notification_type = "<ons-icon icon='fa-star' size='14px' style='color:yellow;'></ons-icon>";
                    break;

                    default:
                    $notification_type = "<ons-icon icon='fa-bell' size='14px' style='color:#333;'></ons-icon>";
                    break;


                }

                $formatted_notifications .= "<ons-list-item class='notification-item' id='notification-list-item-{$date_notifications['id']}' modifier='longdivider'>
                
                                                <div class='center'>
                                                <div style='width:100%;'>{$notification_type} <span style='font-weight:bold;'class='list-item__title'>{$notification_time} </span></div>
                                                    <span class='list-item__subtitle'>{$date_notifications['content']}</span>                                                    
                                                </div>
                                            
                                            </ons-list-item>";
            }
        }
        
    }



    $data_array = array("success"=>1,"notifications"=>$formatted_notifications,'n_count'=>$num_of_notifications);    
    echo json_encode($data_array); 
    exit;



}



function deletenotification(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }


    $notification_id = (int) $_POST['n_id'];


    $query = sprintf('DELETE FROM %stbl_notifications WHERE person_id = "%d" AND user_type = 0 AND id = "%d"', DB_TBL_PREFIX, $_SESSION['uid'],$notification_id); 
    
    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ 
        $error = array("error"=>"Failed to delete notifications");
        echo json_encode($error); 
        exit;
                
    }


    $data_array = array("success"=>1);    
    echo json_encode($data_array); 
    exit;




}



function paystackInit(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please login.");
        echo json_encode($error); 
        exit; 
    }

    include "../drop-files/lib/pgateways/paystack/paystack-transaction-init.php";  
	
  
  
  }

  function pesapalInit(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please login.");
        echo json_encode($error); 
        exit; 
    }

    include "../drop-files/lib/pgateways/pesapal/pesapal-transaction-init.php";  
	
  
  
  }



  function paytrInit(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please login.");
        echo json_encode($error); 
        exit; 
    }

    include "../drop-files/lib/pgateways/paytr/paytr-transaction-init.php";  
	
  
  
  }


  function stripeInit(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please login.");
        echo json_encode($error); 
        exit; 
    }

    include "../drop-files/lib/pgateways/stripe/stripe-transaction-init.php";  
	
  
  
  }


  function flutterwaveInit(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please login.");
        echo json_encode($error); 
        exit; 
    }

    include "../drop-files/lib/pgateways/flutterwave/flutterwave-transaction-init.php";  
	
  
  
  }


  function paykuInit(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please login.");
        echo json_encode($error); 
        exit; 
    }

    include "../drop-files/lib/pgateways/payku/payku-transaction-init.php";  
	
  
  
  }



  function midtransInit(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please login.");
        echo json_encode($error); 
        exit; 
    }

    include "../drop-files/lib/pgateways/midtrans/midtrans-transaction-init.php";  
	
  
  
  }


  function paymobInit(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please login.");
        echo json_encode($error); 
        exit; 
    }

    include "../drop-files/lib/pgateways/paymob/paymob-transaction-init.php";  
	
  
  
  }


  function paytmInit(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please login.");
        echo json_encode($error); 
        exit; 
    }

    include "../drop-files/lib/pgateways/paytm/paytm-transaction-init.php";  
	
  
  
  }




  function phonepeInit(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please login.");
        echo json_encode($error); 
        exit; 
    }

    include "../drop-files/lib/pgateways/phonepe/phonepe-transaction-init.php";  
	
  
  
  }




  function paypalInit(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please login.");
        echo json_encode($error); 
        exit; 
    }

    include "../drop-files/lib/pgateways/paypal/paypal-transaction-init.php";  
	
  
  
  }



  function customInit(){
    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please re-login and retry.");
        echo json_encode($error); 
        exit; 
    }

    include "../drop-files/lib/pgateways/custom/custom-transaction-init.php";
  }



?>