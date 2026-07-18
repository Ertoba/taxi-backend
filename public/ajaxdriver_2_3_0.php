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
    if(file_exists(FILES_FOLDER . "/lang/driver/{$_SESSION['lang']}.php")){
        include FILES_FOLDER . "/lang/driver/{$_SESSION['lang']}.php"; 
    }else{
        include FILES_FOLDER . "/lang/driver/en.php";
    }
}else{
    include FILES_FOLDER . "/lang/driver/en.php";
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





function updateUserPhoto(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    //check if driver is allowed to edit his photo
    
    $query = sprintf('SELECT driver_id FROM %stbl_drivers WHERE driver_id = %d AND allow_photo_edit = %d', DB_TBL_PREFIX, $_SESSION['uid'], 0);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $error = array("error"=>__("You are not allowed to change your photo. Please contact support"));
            echo json_encode($error); 
            exit; 
        }
    }else{
        $error = array("error"=>__("Failed to update your photo"));
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

    @mkdir(realpath(USER_PHOTO_PATH) . "/". $filename[0] . "/" . $filename[1] . "/" . $filename[2], 0777, true);


    $image_path = realpath(USER_PHOTO_PATH) .  "/". $filename[0] . "/" . $filename[1] . "/" . $filename[2] . "/";
    $file = $image_path . $filename . ".jpg";
    
    file_put_contents($file, $uploaded_photo_decoded); //store the photo to disk.       

    $user_photo = $filename . ".jpg";

    
    //update database
    $query = sprintf('UPDATE %stbl_drivers SET photo_file = "%s", allow_photo_edit = "%d" WHERE driver_id = "%d"', 
    DB_TBL_PREFIX, 
    $user_photo,
    0,
    $_SESSION['uid']
    );

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){
        $error = array("error"=>__("Failed to update your photo"));
        echo json_encode($error); 
        exit;
    }
        


    $_SESSION['photo'] = SITE_URL . "ajaxphotofile.php?file=" . $user_photo;

    
    $data = array("success"=>1,'photo_url'=> SITE_URL . "ajaxphotofile.php?file=" . $user_photo);
    echo json_encode($data);
    exit;





}


function verifyUserEmail(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); //invalid record
        exit; 
    }


    if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
        
        $error = array("error"=>__("Your email is not a valid email format"));
        echo json_encode($error); 
        exit;
    }


    if($_POST['email'] == $_SESSION['email']){
        $error = array("error"=>__("The email already exists. Please use a different email"));
        echo json_encode($error); //invalid record
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

    

    //Check if email already exists
    $msg = '';
    $query = sprintf('SELECT driver_id,email FROM %stbl_drivers WHERE driver_id != "%d" AND email = "%s"', DB_TBL_PREFIX,$_SESSION['uid'], mysqli_real_escape_string($GLOBALS['DB'], $_POST['email']));

    
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
    $query = sprintf('UPDATE %stbl_drivers SET `email` = "%s" WHERE driver_id = "%d"', 
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
    LEFT JOIN %1$stbl_users_documents ON %1$stbl_users_documents.doc_id = %1$stbl_documents.id AND %1$stbl_users_documents.u_id = %3$d AND %1$stbl_users_documents.u_type = 1
    WHERE %1$stbl_documents.id = %2$d LIMIT 1', DB_TBL_PREFIX, $doc_id, $_SESSION['uid']);

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

        @mkdir(realpath(USER_PHOTO_PATH) . "/". $filename[0] . "/" . $filename[1] . "/" . $filename[2], 0777, true);


        $image_path = realpath(USER_PHOTO_PATH) .  "/". $filename[0] . "/" . $filename[1] . "/" . $filename[2] . "/";
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
        $image_path = realpath(USER_PHOTO_PATH) .  "/". $old_doc_image_filename[0] . "/" . $old_doc_image_filename[1] . "/" . $old_doc_image_filename[2] . "/";
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
            $data = array("success"=>1,'doc_img_url'=> SITE_URL . "ajaxphotofile.php?file=" . $user_doc_photo);
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
         1, //driver
         $_SESSION['uid'],
         $doc_id
        
        );

        if($result = mysqli_query($GLOBALS['DB'], $query)){            
            $data = array("success"=>1,'doc_img_url'=> SITE_URL . "ajaxphotofile.php?file=" . $user_doc_photo);
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
    LEFT JOIN %1$stbl_users_documents ON %1$stbl_users_documents.doc_id = %1$stbl_documents.id AND %1$stbl_users_documents.u_id = %2$d AND %1$stbl_users_documents.u_type = 1
    WHERE %1$stbl_documents.doc_user = 1 AND %1$stbl_documents.status = 1', DB_TBL_PREFIX, $_SESSION['uid']);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                if(!empty($row['u_doc_img'])){
                    $row['u_doc_img'] = SITE_URL . "ajaxphotofile.php?file=" . $row['u_doc_img'];
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

    $query = sprintf('UPDATE %stbl_drivers SET `pwd_raw` = "%s",password_hash = "%s" WHERE driver_id = "%d"', 
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


function updateDriverCity(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); //invalid record
        exit; 
    }

    
    $city_id = (int) $_POST['city_id'];

    if(empty($city_id )){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //invalid record
        exit;
    }


    if(ALLOW_DRV_CITY_CHANGE == 0){
        $error = array("error"=>__("You are not allowed to change cities. Please contact support"));
        echo json_encode($error); //invalid record
        exit;
    }

    //check if driver vehicle category is available in this city
    
    $query = sprintf('SELECT * FROM %stbl_rides_tariffs WHERE ride_id = %d AND routes_id = %d', DB_TBL_PREFIX, $_SESSION['driver_ride_id'], $city_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(!mysqli_num_rows($result)){
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); //invalid record
            exit;
        }
    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //invalid record
        exit;
    }

    $query = sprintf('UPDATE %stbl_drivers SET `route_id` = "%d" WHERE driver_id = "%d"', DB_TBL_PREFIX,$city_id, $_SESSION['uid']);
    $result = mysqli_query($GLOBALS['DB'], $query);

    $res = array("success"=>1);
    echo json_encode($res); //invalid record
    exit;

    
}


function updateDriverCar(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); //invalid record
        exit; 
    }

    if(empty($_POST['v_cat']) || empty($_POST['v_model']) || empty($_POST['v_model_year']) || empty($_POST['v_license']) || empty($_POST['v_paint_color'])){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //invalid record
        exit;
    }


    //get driver data and check if he is allowed to update his vehicle
    $query = sprintf('SELECT allow_vehicle_edit FROM %stbl_drivers WHERE driver_id = %d', DB_TBL_PREFIX, $_SESSION['uid']);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $driver_data = mysqli_fetch_assoc($result);
            if($driver_data['allow_vehicle_edit'] == 0){
                $error = array("error"=>__("You are not allowed to change the vehicle details. Please contact support"));
                echo json_encode($error); //invalid record
                exit;
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


    

    $query = sprintf('UPDATE %stbl_drivers SET ride_id = "%d", car_model = "%s", car_year = "%s", car_plate_num = "%s", car_color = "%s", allow_vehicle_edit = "%d" WHERE driver_id = "%d"',
        DB_TBL_PREFIX,
        (int) $_POST['v_cat'],
        mysqli_real_escape_string($GLOBALS['DB'], $_POST['v_model']),
        mysqli_real_escape_string($GLOBALS['DB'], $_POST['v_model_year']),
        mysqli_real_escape_string($GLOBALS['DB'], $_POST['v_license']),
        mysqli_real_escape_string($GLOBALS['DB'], $_POST['v_paint_color']),
        0, //disable further edit
        $_SESSION['uid']
    );

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){
        $res = array("error"=>__("An error has occured"));
        echo json_encode($res); //invalid record
        exit;
    }

    $res = array("success"=> 1);
    echo json_encode($res); //invalid record
    exit;

}


function updateBankDetails(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); //invalid record
        exit; 
    }

    $acc_name = !empty($_POST['acc_name']) ? mysqli_real_escape_string($GLOBALS['DB'], $_POST['acc_name']) : '';
    $acc_num = !empty($_POST['acc_num']) ? mysqli_real_escape_string($GLOBALS['DB'], $_POST['acc_num']) : '';
    $bank_name = !empty($_POST['bank_name']) ? mysqli_real_escape_string($GLOBALS['DB'], $_POST['bank_name']) : '';
    $bank_code = !empty($_POST['bank_code']) ? mysqli_real_escape_string($GLOBALS['DB'], $_POST['bank_code']) : '';
    
    
    $query = sprintf('UPDATE %stbl_drivers SET `bank_name` = "%s", `bank_code` = "%s", `bank_acc_holder_name` = "%s", `bank_acc_num` = "%s" WHERE driver_id = "%d"', 
        DB_TBL_PREFIX,
        $bank_name,
        $bank_code,
        $acc_name,
        $acc_num,
        $_SESSION['uid']
    );

    $result = mysqli_query($GLOBALS['DB'], $query);

    $res = array("success"=>1);
    echo json_encode($res); 
    exit;

}





function updateDriverAllocDistOnSvr(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); //invalid record
        exit; 
    }

    $dist_radius_value = (float) $_POST['dist_radius'];

    if($dist_radius_value < 0.5){
        $dist_radius_value = 0.5;
    }elseif($dist_radius_value > 10){
        $dist_radius_value = 10;
    }
    
    $query = sprintf('UPDATE %stbl_drivers SET `drv_alloc_dist` = "%f" WHERE driver_id = "%d"', 
        DB_TBL_PREFIX,
        $dist_radius_value,
        $_SESSION['uid']
    );

    $result = mysqli_query($GLOBALS['DB'], $query);

    $res = array("success"=>1);
    echo json_encode($res); 
    exit;

}






function updateUserProfile(){

    

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); //invalid record
        exit; 
    }

    $driver_country = codeToCountryName(strtoupper($_POST['country_code']));

    if(!$driver_country){
        
        $error = array("error"=>__("Invalid country selected"));
        echo json_encode($error); //invalid country
        exit;
    }

    $msg = '';
    $enable_city_ride_change = 1; //for demo
    

    if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
        
        $error = array("error"=>__("Your email is not a valid email format"));
        echo json_encode($error); //invalid record
        exit;
    }

    //Check if email or phone number already exists
    
    $query = sprintf('SELECT driver_id,email, phone FROM %stbl_drivers WHERE driver_id != "%d" AND (email = "%s" OR phone="%s")', DB_TBL_PREFIX,$_SESSION['uid'], mysqli_real_escape_string($GLOBALS['DB'], $_POST['email']),mysqli_real_escape_string($GLOBALS['DB'], $_POST['phone']));

    
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

    if($enable_city_ride_change && !empty((int)$_POST['op_city']) && !empty((int)$_POST['car_category'])){ //enable briver location and ride type change
        
        $operational_city = (int)$_POST['op_city'];
        $car_category = (int)$_POST['car_category'];

        $query = sprintf('UPDATE %stbl_drivers SET `route_id` = "%d", ride_id = %d WHERE driver_id = "%d"', DB_TBL_PREFIX,$operational_city,$car_category,$_SESSION['uid']);
        $result = mysqli_query($GLOBALS['DB'], $query);
    }

    /* if(empty($_POST['oldpassword'])){
        $error = array("error"=>"Enter your current password.");
        echo json_encode($error); //invalid record
        exit;
    } */


    if(!empty($_POST['oldpassword']) && empty($_POST['newpassword'])){
        $error = array("error"=>__("Your new password cannot be empty"));
        echo json_encode($error); //invalid record
        exit;
    }


    if(!empty($_POST['oldpassword'])){

        //verify the old password is correct
        $query = sprintf('SELECT driver_id FROM %stbl_drivers WHERE driver_id = "%d" AND pwd_raw = "%s"', DB_TBL_PREFIX, $_SESSION['uid'], mysqli_real_escape_string($GLOBALS['DB'], $_POST['oldpassword'])); //Get required user information from DB

        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                //old password is valid. update record with new password
                $query = sprintf('UPDATE %stbl_drivers SET `pwd_raw` = "%s" WHERE driver_id = "%d"', DB_TBL_PREFIX,mysqli_real_escape_string($GLOBALS['DB'], $_POST['newpassword']),$_SESSION['uid']);
                $result = mysqli_query($GLOBALS['DB'], $query);
                $msg = 'Password was changed successfully.';

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
    $query = sprintf('UPDATE %stbl_drivers SET `phone` = "%s", `email` = "%s", country_code = "%s", country_dial_code = "%s", drv_country = "%s" WHERE driver_id = "%d"', 
        DB_TBL_PREFIX,
        mysqli_real_escape_string($GLOBALS['DB'],$_POST['phone']),
        mysqli_real_escape_string($GLOBALS['DB'],$_POST['email']),
        mysqli_real_escape_string($GLOBALS['DB'],$_POST['country_code']),
        "+" . mysqli_real_escape_string($GLOBALS['DB'],$_POST['country_dial_code']),
        $driver_country,
        $_SESSION['uid']
    );
    $result = mysqli_query($GLOBALS['DB'], $query);   

    $_SESSION['email'] = mysqli_real_escape_string($GLOBALS['DB'], $_POST['email']);
    $_SESSION['phone'] = mysqli_real_escape_string($GLOBALS['DB'], $_POST['phone']); 




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



    //update fistname and lastname
    $query = sprintf('UPDATE %stbl_drivers SET `firstname` = "%s", `lastname` = "%s" WHERE driver_id = "%d"', 
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
       $query = sprintf('UPDATE %stbl_drivers SET `push_notification_token` = NULL WHERE push_notification_token = "%s"', DB_TBL_PREFIX,$push_notification_token);
       $result = mysqli_query($GLOBALS['DB'], $query);
       //update this users push notification token
       $query = sprintf('UPDATE %stbl_drivers SET `push_notification_token` = "%s" WHERE driver_id = "%d"', DB_TBL_PREFIX,$push_notification_token,$_SESSION['uid'] );
       $result = mysqli_query($GLOBALS['DB'], $query);
       $_SESSION['push_token'] = $push_notification_token;
   }


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

    $operation_city = (int) $user_reg_data['operation_city'];
    $vehicle_category = (int) $user_reg_data['car_type'];
    $vehicle_model = mysqli_real_escape_string($GLOBALS['DB'], trim($user_reg_data['car_model']));
    $vehicle_year = mysqli_real_escape_string($GLOBALS['DB'], trim($user_reg_data['car_year']));
    $vehicle_plate_num = mysqli_real_escape_string($GLOBALS['DB'], trim($user_reg_data['car_plate_num']));
    $vehicle_color = mysqli_real_escape_string($GLOBALS['DB'], trim($user_reg_data['car_color']));

    if(empty($user_firstname)) {        
        $error = array("error"=>__("Please enter your first name"));
        echo json_encode($error); 
        exit;        
    }

    if(empty($user_photo)){
        $error = array("error"=>__("Please upload a passport photo"));
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


    //check if phone number matches the one earlier validated
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


    $query = sprintf('SELECT driver_id,email,phone,country_dial_code FROM %stbl_drivers WHERE (phone = "%s" OR phone = "%s") AND country_dial_code = "%s"', DB_TBL_PREFIX, $user_phone_inp,$validation_res['phone_num_nat'],"+" . $user_country_dial_code);


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


    //save driver photo
    $uploaded_photo_encoded = $user_photo; //Get Base64 encoded image data
    $uploaded_photo_encoded_array = explode(',', $uploaded_photo_encoded);
    $image_data = array_pop($uploaded_photo_encoded_array);
    $uploaded_photo_decoded = base64_decode($image_data); //Decode the data

    
    if(!$uploaded_photo_decoded){ //Verify that data is valid base64 data
        
        $error = array("error"=>__("Please upload a passport photo in JPEG format"));
        echo json_encode($error); 
        exit;
    } 

    //prepare filename and save the file. Exported base64 image data is in JPEG format. We should be expecting a JPEG image data then.
    $filename =  crypto_string('distinct',20);

    @mkdir(realpath(USER_PHOTO_PATH) . "/". $filename[0] . "/" . $filename[1] . "/" . $filename[2], 0777, true);

    
    $image_path = realpath(USER_PHOTO_PATH) .  "/". $filename[0] . "/" . $filename[1] . "/" . $filename[2] . "/";
    $file = $image_path . $filename . ".jpg";

   
    
    file_put_contents($file, $uploaded_photo_decoded); //store the photo to disk.     

    $driver_passport_photo = $filename . ".jpg";


    //generate referal code
    for ($x = 0;$x < 10;$x++){ //try to generate a unique code for a referal code
       
        $referal_code =  crypto_string("ABCDEFGHIJKLMNOPQRSTUVWXYZ",4);
        $referal_code .=  crypto_string("123456789",4);
    
        //check database to see if generated code already exists
        $query = sprintf('SELECT * FROM %stbl_drivers WHERE referal_code = "%s"',DB_TBL_PREFIX,$referal_code);
    
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

    $fake_email = str_replace(" ","",$user_firstname).$referal_code."@enter-your-email.com";
    

    
    //check if driver registered with referral code
    $reg_with_referral_code = ''; 
    $ref_code_result_msg = __("Thank you for a patnering with us. We hope you will find success driving and earning with us. You are welcome");
    $ref_user_data = [];
    //handle referal    
    //get referal settings
    $referal_settings_data = [];
    

    if(!empty($user_reg_referral) && strlen($user_reg_referral) < 10){

        $ref_code = mysqli_real_escape_string($GLOBALS['DB'], $user_reg_referral);

        $query = sprintf('SELECT %1$stbl_referral_drivers.beneficiary,%1$stbl_referral_drivers.route_id AS ref_route_id, %1$stbl_referral_drivers.driver_incentive,%1$stbl_referral_drivers.invitee_incentive, %1$stbl_referral_drivers.number_of_rides, %1$stbl_referral_drivers.number_of_days, %1$stbl_currencies.symbol FROM %1$stbl_referral_drivers 
        INNER JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_referral_drivers.route_id
        INNER JOIN %1$stbl_currencies ON %1$stbl_currencies.id = %1$stbl_routes.city_currency_id
        WHERE %1$stbl_referral_drivers.route_id = %2$d AND %1$stbl_referral_drivers.status = 1', DB_TBL_PREFIX, $operation_city);

        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                $referal_settings_data = mysqli_fetch_assoc($result);

                $query = sprintf('SELECT driver_id, disp_lang FROM %stbl_drivers WHERE referal_code = "%s"', DB_TBL_PREFIX, $ref_code);

                if($result = mysqli_query($GLOBALS['DB'], $query)){
                    if(mysqli_num_rows($result)){
                        $reg_with_referral_code = $ref_code;
                        $ref_user_data = mysqli_fetch_assoc($result);

                        $driver_notification_msg = '';    

                        if($referal_settings_data['beneficiary'] == 0 || $referal_settings_data['beneficiary'] == 2){ //old driver benefits 
                            
                            //notify driver
                            $driver_notification_msg = __("A new driver has signed-up with your referral code. You will earn {---1} when the driver completes {---2} trips in {---3} days",[$referal_settings_data['symbol'].$referal_settings_data['driver_incentive'],$referal_settings_data['number_of_rides'],$referal_settings_data['number_of_days']],"d|{$ref_user_data['disp_lang']}");
                            $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
                                ("%d",1,"%s",0,"%s")', 
                                DB_TBL_PREFIX,
                                $ref_user_data['driver_id'],
                                mysqli_real_escape_string($GLOBALS['DB'], $driver_notification_msg),
                                gmdate('Y-m-d H:i:s', time()) 
                            );
                            $result = mysqli_query($GLOBALS['DB'], $query);

                        }elseif($referal_settings_data['beneficiary'] == 1){

                            
                            //notify driver
                            $driver_notification_msg = __("A new driver has signed-up with your referral code. Thank you for growing our great and awesome service",null,"d|{$ref_user_data['disp_lang']}");
                            $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
                                ("%d",1,"%s",0,"%s")', 
                                DB_TBL_PREFIX,
                                $ref_user_data['driver_id'],
                                mysqli_real_escape_string($GLOBALS['DB'], $driver_notification_msg),
                                gmdate('Y-m-d H:i:s', time()) 
                            );

                            $result = mysqli_query($GLOBALS['DB'], $query);

                        }
                            
                        
                        if($referal_settings_data['beneficiary'] == 1 || $referal_settings_data['beneficiary'] == 2){ //invitee benefits 
                            $ref_code_result_msg = __("Thank you for patnering with us. Your referral has qualified you to earn {---1} when you complete {---2} trips in {---3} days", [$referal_settings_data['symbol'].$referal_settings_data['invitee_incentive'],$referal_settings_data['number_of_rides'],$referal_settings_data['number_of_days']]);
                        }                        
                        
                    }

                }
                
            }

        }
        


    }
    
     



    //Store driver data to database
    $query = sprintf('INSERT INTO %stbl_drivers(car_year,drv_address,reg_route_id,reg_with_referal_code,is_activated,route_id,pwd_raw,password_hash,email,firstname,lastname,phone,drv_country,car_plate_num,car_model,car_color,ride_id,referal_code,franchise_id,account_create_date,photo_file,driver_commision,country_code,country_dial_code) VALUES'.
    '("%s","%s","%d","%s","%d","%d","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%d","%s","%d","%s","%s","%s","%s","%s")', 
        DB_TBL_PREFIX,
        $vehicle_year,
        $user_country, 
        (int) $operation_city,
        $reg_with_referral_code,
        DRIVER_REG_ACT_MODE == 2 ? 0 : 1, 
        (int) $operation_city,
        mysqli_real_escape_string($GLOBALS['DB'], $user_password),
        password_hash(mysqli_real_escape_string($GLOBALS['DB'], $user_password), PASSWORD_DEFAULT),
        $fake_email,
        $user_firstname,
        $user_lastname,
        $validation_res['phone_num_nat'],
        $user_country,
        $vehicle_plate_num,
        $vehicle_model,
        $vehicle_color,
        $vehicle_category,
        $referal_code,
        1,
        gmdate('Y-m-d H:i:s', time()),
        $driver_passport_photo,
        DRIVER_DEFAULT_COMMISSION,
        $user_country_code,
        "+" . $user_country_dial_code

    );


    if(!$result = mysqli_query($GLOBALS['DB'], $query)){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    
    }
    else{
        $id = mysqli_insert_id ( $GLOBALS['DB'] );       
        
    }


    


    $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
        ("%d",1,"%s",0,"%s")', 
        DB_TBL_PREFIX,
        $id,
        mysqli_real_escape_string($GLOBALS['DB'], $ref_code_result_msg),
        gmdate('Y-m-d H:i:s', time()) 
    );

    $result = mysqli_query($GLOBALS['DB'], $query);


    

    
    $success = array("success"=>"1");
    echo json_encode($success); 
    exit;




}




function driverLogin(){

    $phone = mysqli_real_escape_string($GLOBALS['DB'], $_POST['phone']);
    $otp_code = $_POST['otp_code'];
    $phone_formatted = $_POST['phone_formatted'];
    $password = $_POST['password'];
    $country_dial_code = $_POST['country_dial_code'];
    $driver_account_details = [];
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



    $query_str = "( " . DB_TBL_PREFIX . "tbl_drivers.phone = \"{$phone_num_zero_prefix}\" OR " . DB_TBL_PREFIX . "tbl_drivers.phone = \"{$phone_num_no_zero_prefix}\") AND " . DB_TBL_PREFIX . "tbl_drivers.pwd_raw = \"{$password}\" AND " . DB_TBL_PREFIX . "tbl_drivers.country_dial_code = \"+{$country_dial_code}\" AND " . DB_TBL_PREFIX . "tbl_drivers.account_deleted = 0";

    
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

                $query_str = "( " . DB_TBL_PREFIX . "tbl_drivers.phone = \"{$phone_num_zero_prefix}\" OR " . DB_TBL_PREFIX . "tbl_drivers.phone = \"{$phone_num_no_zero_prefix}\") AND " . DB_TBL_PREFIX . "tbl_drivers.country_dial_code = \"+{$country_dial_code}\" AND " . DB_TBL_PREFIX . "tbl_drivers.account_deleted = 0";


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

                $query_str = "( " . DB_TBL_PREFIX . "tbl_drivers.phone = \"{$phone_num_zero_prefix}\" OR " . DB_TBL_PREFIX . "tbl_drivers.phone = \"{$phone_num_no_zero_prefix}\") AND " . DB_TBL_PREFIX . "tbl_drivers.country_dial_code = \"+{$country_dial_code}\" AND " . DB_TBL_PREFIX . "tbl_drivers.account_deleted = 0";

            }else{
                $error = array("error"=>__("Invalid OTP code"));
                echo json_encode($error); //database error
                exit;
            }
        
        }
    }

    //Let's check our local DB for driver record'

    $query = sprintf('SELECT *,%1$stbl_drivers.route_id AS route_id FROM %1$stbl_drivers 
    LEFT JOIN %1$stbl_rides ON %1$stbl_rides.id = %1$stbl_drivers.ride_id
    LEFT JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_drivers.route_id
    LEFT JOIN %1$stbl_currencies ON %1$stbl_currencies.id = %1$stbl_routes.city_currency_id
    LEFT JOIN %1$stbl_referral_drivers ON %1$stbl_referral_drivers.route_id = %1$stbl_drivers.route_id
    WHERE %2$s ', DB_TBL_PREFIX, $query_str); //Get required user information from DB


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $driver_account_details = mysqli_fetch_assoc($result);
        }
        else{

            $error = array("error"=>__("Invalid account"));
            echo json_encode($error); //invalid record
            exit;

        }
        
    }
    else{ //No record found in DB. Show view to notify user

        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;
    }


    //get user documents
    $user_documents = [];
    $query = sprintf('SELECT *,%1$stbl_documents.id AS d_id FROM %1$stbl_documents
    LEFT JOIN %1$stbl_users_documents ON %1$stbl_users_documents.doc_id = %1$stbl_documents.id AND %1$stbl_users_documents.u_id = %2$d AND %1$stbl_users_documents.u_type = 1
    WHERE %1$stbl_documents.doc_user = 1 AND %1$stbl_documents.status = 1', DB_TBL_PREFIX, $driver_account_details['driver_id']);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                if(!empty($row['u_doc_img'])){
                    $row['u_doc_img'] = SITE_URL . "ajaxphotofile.php?file=" . $row['u_doc_img'];
                }
                $user_documents[$row['d_id']] = $row;
            }
        }
    }



    //get user documents
    $user_documents = [];
    $driver_pending_document = 0;

    $query = sprintf('SELECT *,%1$stbl_documents.id AS d_id FROM %1$stbl_documents
    LEFT JOIN %1$stbl_users_documents ON %1$stbl_users_documents.doc_id = %1$stbl_documents.id AND %1$stbl_users_documents.u_id = %2$d AND %1$stbl_users_documents.u_type = 1
    WHERE %1$stbl_documents.doc_user = 1 AND %1$stbl_documents.status = 1', DB_TBL_PREFIX, $driver_account_details['driver_id']);


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                if((empty($row['doc_city']) || $row['doc_city'] == $driver_account_details['route_id']) && $row['u_doc_status'] != 3){
                    $driver_pending_document = 1;
                }
                if(!empty($row['u_doc_img'])){
                    $row['u_doc_img'] = SITE_URL . "ajaxphotofile.php?file=" . $row['u_doc_img'];
                }
                $user_documents[$row['d_id']] = $row;
            }
        }
    }

    if(!empty($driver_pending_document) && !empty($driver_account_details['available']) && empty(DEMO)){

        //driver is currently set online and he has pending documents. Put him offline
        $query = sprintf('UPDATE %stbl_drivers SET `available` = %d WHERE driver_id = %d', DB_TBL_PREFIX, 0, $driver_account_details['driver_id']);
        if($result = mysqli_query($GLOBALS['DB'], $query)){
            $driver_account_details['available'] = 0;
        }
        
    } 

    $referral_url = isset($_SESSION['platform']) && $_SESSION['platform'] == "android" ? str_replace("market://details?","https://play.google.com/store/apps/details?",DRIVER_APP_UPDATE_URL_ANDROID) : DRIVER_APP_UPDATE_URL_IOS;//SITE_URL;
    $refcode_copy = isset($driver_account_details['status']) && $driver_account_details['status'] == 1 && !empty($driver_account_details['referal_code']) ? __("Hi, you can now earn good money with your car driving with Droptaxi. Sign-up now with my referral code {---1} to start earning while you drive",[$driver_account_details['referal_code']],"d|{$display_language}") : '';
    $ref_code_desc = __('Earn {---1} with your referral code when you invite a driver to Droptaxi and the driver completes {---2} trips in {---3} days', [$driver_account_details['symbol'].$driver_account_details['driver_incentive'],$driver_account_details['number_of_rides'],$driver_account_details['number_of_days']],"d|{$display_language}");
    $ref_code = isset($driver_account_details['status']) && $driver_account_details['status'] == 1 && !empty($driver_account_details['referal_code']) ? $driver_account_details['referal_code'] : "";

    
    //$photo = explode('/',$driver_account_details['photo_file']);
    $photo_file = isset($driver_account_details['photo_file']) ? $driver_account_details['photo_file'] : "0";
    
    
    $_SESSION['firstname'] = $driver_account_details['firstname'];
    $_SESSION['lastname'] = $driver_account_details['lastname'];
    $_SESSION['uid'] = $driver_account_details['driver_id'];
    $_SESSION['email'] = $driver_account_details['email'];
    $_SESSION['phone'] = $driver_account_details['phone'];
    $_SESSION['country_dial_code'] = $driver_account_details['country_dial_code'];
    $_SESSION['address'] = $driver_account_details['drv_address'];
    $_SESSION['is_activated'] = $driver_account_details['is_activated'];
    $_SESSION['loggedin'] = 1;
    $_SESSION['ref_code'] = $driver_account_details['referal_code'];
    $_SESSION['city_id'] = !empty($driver_account_details['route_id']) ? $driver_account_details['route_id'] : 0;
    $_SESSION['driver_rating'] = $driver_account_details['driver_rating'];
    $_SESSION['wallet_amt'] = $driver_account_details['wallet_amount'];
    $_SESSION['push_token'] = $driver_account_details['push_notification_token'];
    $_SESSION['photo'] = SITE_URL . "ajaxphotofile.php?file=" . $photo_file;
    $_SESSION['availability'] = $driver_account_details['available'];
    $_SESSION['driver_city'] = !empty($driver_account_details['r_title']) ? $driver_account_details['r_title'] : "Unknown";
    $_SESSION['driver_city_dist_unit'] = !empty($driver_account_details['dist_unit']) ? 1 : 0;
    $_SESSION['driver_ride_id'] = $driver_account_details['ride_id'];
    $_SESSION['driver_ride_icon'] = isset($driver_account_details['icon_type']) ? $driver_account_details['icon_type'] : "1";
    $_SESSION['car_plate_num'] = $driver_account_details['car_plate_num'];
    $_SESSION['car_model'] = $driver_account_details['car_model'];
    $_SESSION['car_color'] = $driver_account_details['car_color'];
    $_SESSION['car_year'] = $driver_account_details['car_year'];
    $_SESSION['carcat'] = $driver_account_details['ride_type'];
    $_SESSION['allow_alt_cars'] = $driver_account_details['allow_alt_cars'];
    

    $redis = connectRedis();

    if($redis){
        $redis->set("drvsdlkey:{$_SESSION['uid']}", $sdl_key);
    }

    
    
    $profiledata = array(
        'success' => 1,
        'firstname'=> $_SESSION['firstname'],
        'lastname'=> $_SESSION['lastname'],
        'email'=> $_SESSION['email'],
        'phone'=> $_SESSION['phone'],
        'address'=> $_SESSION['address'],
        'driverid' => $_SESSION['uid'],
        'ref_code'=>$ref_code,
        'ref_code_copy_msg' => $refcode_copy,
        'ref_desc' => $ref_code_desc,
        'ref_url' => $referral_url,
        'photo' => $_SESSION['photo'],
        'city_id'=> !empty($driver_account_details['route_id']) ? $driver_account_details['route_id'] : 0,
        'city' => !empty($driver_account_details['r_title']) ? $driver_account_details['r_title'] : "Unknown",
        'city_lat' => !empty($driver_account_details['lat']) ? $driver_account_details['lat'] : "",
        'city_lng' => !empty($driver_account_details['lng']) ? $driver_account_details['lng'] : "",
        'night_start' => NIGHT_START,
        'night_end' => NIGHT_END,
        'carcat' => $driver_account_details['ride_type'],
        'driver_rating' => $_SESSION['driver_rating'],
        'driver_ride_id' => $driver_account_details['ride_id'],
        'driver_ride_icon' => isset($driver_account_details['icon_type']) ? $driver_account_details['icon_type'] : "1",
        'country_code' => $driver_account_details['country_code'],
        'country_dial_code' => $driver_account_details['country_dial_code'],
        'user_docs' => $user_documents,        
        'rejected_rides'=>$driver_account_details['rejected_rides'],
        'cancelled_rides'=>$driver_account_details['cancelled_rides'],
        'completed_rides'=>$driver_account_details['completed_rides'],
        'car_plate_num' => $driver_account_details['car_plate_num'],
        'car_model' => $driver_account_details['car_model'],
        'car_color' => $driver_account_details['car_color'],
        'car_year' => $driver_account_details['car_year'],
        'bank_name' => $driver_account_details['bank_name'],
        'bank_acc_holder_name' => $driver_account_details['bank_acc_holder_name'],
        'bank_acc_num' => $driver_account_details['bank_acc_num'],
        'bank_code' => $driver_account_details['bank_code'],
        'allow_alt_cars' => $driver_account_details['allow_alt_cars']
    );


    $uncompleted_booking = [];
    //check if driver has pending bookings
    $query = sprintf('SELECT id, `status` FROM %stbl_bookings WHERE driver_id = %d AND (`status` =  0 OR `status` =  1)', DB_TBL_PREFIX, $_SESSION['uid']);
    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                $uncompleted_booking[] = $row['id'];
            }
            
        }
    }


    //get default currency data
    $default_currency_data = [];
    $query = sprintf('SELECT * FROM %stbl_currencies WHERE `default` = 1', DB_TBL_PREFIX);
    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $default_currency_data = mysqli_fetch_assoc($result);
        }
    }



    //Get online payment gateway data

    $online_payment_data = array(

        'merchantid'=> P_G_PK,
        'storeid'=> STORE_ID,
        'devid'=> DEV_ID,
        'notifyurl'=> NOTIFY_URL
        
    );

    $driver_city_currency = array(
        'cur_symbol'=> $driver_account_details['symbol'],
        'cur_code'=> $driver_account_details['iso_code'],
        'cur_exchng'=>$driver_account_details['exchng_rate'],
        'cur_name'=>$driver_account_details['name']
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
        'default_payment_gateway' => $payment_gateways,
        'driver_location_update_interval' => DRIVER_LOCATION_UPDATE_INTERVAL,
        'wallet_topup_presets' => WALLET_TOPUP_PRESETS,
        'default_banks_and_codes' => DEFAULT_BANKS_AND_CODES,
        'round_trip_fares' => ROUND_TRIP_FARES,
        'trip_cancel_reasons' => DRIVER_CANCEL_REASON,
        "allowed_countries_list" => ALLOWED_COUNTRIES_LIST,
        "force_update_driver_android" => FORCE_UPDATE_DRIVER_ANDROID,
        "force_update_driver_ios" => FORCE_UPDATE_DRIVER_IOS               
    );

    $firebase_rtdb_conf = array(
        'databaseURL' => FB_RTDB_URL,
        'apiKey' => FB_WEB_API_KEY,
        'storageBucket' => FB_STORAGE_BCKT
    );


    

    //update driver last seen time and user selected app language
    if($driver_account_details['is_activated'] == 1){
        $query = sprintf('UPDATE %stbl_drivers SET last_login_date = "%s", disp_lang = "%s", account_active = "%s" WHERE driver_id = %d', DB_TBL_PREFIX,gmdate('Y-m-d H:i:s', time()),$display_language,1,$_SESSION['uid']);
    }else{
        $query = sprintf('UPDATE %stbl_drivers SET last_login_date = "%s", disp_lang = "%s" WHERE driver_id = %d', DB_TBL_PREFIX,gmdate('Y-m-d H:i:s', time()),$display_language,$_SESSION['uid']);
    }
    
    $result = mysqli_query($GLOBALS['DB'], $query);

    $_SESSION['lang'] = $display_language;
    
    session_regenerate_id();
    
    //return data
    $data = array("availability" => $_SESSION['availability'],"fb_conf"=>$firebase_rtdb_conf,'online_pay'=>$online_payment_data,'profileinfo' => $profiledata,'cc_num'=>CALL_CENTER_NUMBER,'wallet_amt' => $_SESSION['wallet_amt'],'driver_min_wallet_balance' => DRIVER_MIN_WALLET_BALANCE, "driver_id"=>$driver_account_details['driver_id'],"email"=>$driver_account_details['email'],"is_activated"=>$driver_account_details['is_activated'],"account_active"=>$driver_account_details['account_active'],'app_version_ios'=>APP_VERSION_DRIVER_IOS,'app_version_android'=>APP_VERSION_DRIVER_ANDROID,'driver_app_update_url_ios'=>DRIVER_APP_UPDATE_URL_IOS,'driver_app_update_url_android'=>DRIVER_APP_UPDATE_URL_ANDROID,'currency_data'=>$driver_city_currency,'uncompleted_bk'=>$uncompleted_booking,'default_currency'=>$default_currency_data,'app_settings'=>$app_settings,'sess_id' => base64_encode(session_id()),'bannerdata'=>getbannerdata(),'scheduled_trips_count' => getScheduledBookings(1));
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
    $query = sprintf('SELECT driver_id, firstname, pwd_raw FROM %stbl_drivers WHERE (phone = "%s" OR phone = "%s") AND country_dial_code = "%s"', 
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



function checkDriverLoginStatus(){

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

        

        $driver_account_details = [];        

        //Get account activation status

        $query = sprintf('SELECT *,%1$stbl_drivers.route_id AS route_id FROM %1$stbl_drivers 
        LEFT JOIN %1$stbl_rides ON %1$stbl_rides.id = %1$stbl_drivers.ride_id
        LEFT JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_drivers.route_id
        LEFT JOIN %1$stbl_currencies ON %1$stbl_currencies.id = %1$stbl_routes.city_currency_id
        LEFT JOIN %1$stbl_referral_drivers ON %1$stbl_referral_drivers.route_id = %1$stbl_drivers.route_id
        WHERE %1$stbl_drivers.driver_id = "%2$d"', DB_TBL_PREFIX,$_SESSION['uid']); //Get required user information from DB

        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                $driver_account_details = mysqli_fetch_assoc($result);

                /* if($driver_account_details['is_activated'] == 0){
                    $data = array("success"=>"1","is_activated"=>$driver_account_details['is_activated'],'loggedin'=>0);
                    echo json_encode($data);
                    exit;
                } */
            }
            else{
                forceLogin();    
            }
            
        }
        else{ //No record matching the USER ID was found in DB. Show view to notify user
    
            forceLogin();
        }

        //get user documents
        $user_documents = [];
        $driver_pending_document = 0;

        $query = sprintf('SELECT *,%1$stbl_documents.id AS d_id FROM %1$stbl_documents
        LEFT JOIN %1$stbl_users_documents ON %1$stbl_users_documents.doc_id = %1$stbl_documents.id AND %1$stbl_users_documents.u_id = %2$d AND %1$stbl_users_documents.u_type = 1
        WHERE %1$stbl_documents.doc_user = 1 AND %1$stbl_documents.status = 1', DB_TBL_PREFIX, $driver_account_details['driver_id']);


        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                while($row = mysqli_fetch_assoc($result)){
                    if((empty($row['doc_city']) || $row['doc_city'] == $driver_account_details['route_id']) && $row['u_doc_status'] != 3){
                        $driver_pending_document = 1;
                    }
                    if(!empty($row['u_doc_img'])){
                        $row['u_doc_img'] = SITE_URL . "ajaxphotofile.php?file=" . $row['u_doc_img'];
                    }
                    $user_documents[$row['d_id']] = $row;
                }
            }
        }

        if(!empty($driver_pending_document) && !empty($driver_account_details['available']) && empty(DEMO)){

            //driver is currently set online and he has pending documents. Put him offline
            $query = sprintf('UPDATE %stbl_drivers SET `available` = %d WHERE driver_id = %d', DB_TBL_PREFIX, 0, $driver_account_details['driver_id']);
            if($result = mysqli_query($GLOBALS['DB'], $query)){
                $driver_account_details['available'] = 0;
            }
            
        }       
        
        
        $referral_url = isset($_SESSION['platform']) && $_SESSION['platform'] == "android" ? str_replace("market://details?","https://play.google.com/store/apps/details?",DRIVER_APP_UPDATE_URL_ANDROID) : DRIVER_APP_UPDATE_URL_IOS;//SITE_URL;
        $refcode_copy = isset($driver_account_details['status']) && $driver_account_details['status'] == 1 && !empty($driver_account_details['referal_code']) ? __("Hi, you can now earn good money with your car driving with Droptaxi. Sign-up now with my referral code {---1} to start earning while you drive",[$driver_account_details['referal_code']],"d|{$display_language}") : '';
        $ref_code_desc = __('Earn {---1} with your referral code when you invite a driver to Droptaxi and the driver completes {---2} trips in {---3} days', [$driver_account_details['symbol'].$driver_account_details['driver_incentive'],$driver_account_details['number_of_rides'],$driver_account_details['number_of_days']],"d|{$display_language}");
        $ref_code = isset($driver_account_details['status']) && $driver_account_details['status'] == 1 && !empty($driver_account_details['referal_code']) ? $driver_account_details['referal_code'] : "";
    
        

        if(isset($driver_account_details['is_activated'])){
            $_SESSION['is_activated'] = $driver_account_details['is_activated'];
        }

        if(isset($driver_account_details['available'])){
            $_SESSION['availability'] = $driver_account_details['available'];
        }

        if(isset($driver_account_details['push_notification_token'])){
            $_SESSION['push_token'] = $driver_account_details['push_notification_token'];
        }

        //$photo = explode('/',$driver_account_details['photo_file']);
        $photo_file = isset($driver_account_details['photo_file']) ? $driver_account_details['photo_file'] : "0";

        $_SESSION['firstname'] = $driver_account_details['firstname'];
        $_SESSION['lastname'] = $driver_account_details['lastname'];
        $_SESSION['uid'] = $driver_account_details['driver_id'];
        $_SESSION['email'] = $driver_account_details['email'];
        $_SESSION['phone'] = $driver_account_details['phone'];
        $_SESSION['country_dial_code'] = $driver_account_details['country_dial_code'];
        $_SESSION['address'] = $driver_account_details['drv_address'];
        $_SESSION['lastseen'] = $driver_account_details['last_login_date'];
        $_SESSION['joined'] = $driver_account_details['account_create_date'];
        $_SESSION['loggedin'] = 1;
        $_SESSION['ref_code'] = $driver_account_details['referal_code'];
        $_SESSION['city_id'] = !empty($driver_account_details['route_id']) ? $driver_account_details['route_id'] : 0;
        $_SESSION['is_activated'] = $driver_account_details['is_activated'];
        $_SESSION['driver_rating'] = $driver_account_details['driver_rating'];
        $_SESSION['wallet_amt'] = $driver_account_details['wallet_amount'];
        $_SESSION['push_token'] = $driver_account_details['push_notification_token'];                
        $_SESSION['photo'] = SITE_URL . "ajaxphotofile.php?file=" . $photo_file;               
        $_SESSION['driver_city'] = !empty($driver_account_details['r_title']) ? $driver_account_details['r_title'] : "Unknown";
        $_SESSION['driver_city_dist_unit'] = !empty($driver_account_details['dist_unit']) ? 1 : 0;
        $_SESSION['driver_ride_id'] = $driver_account_details['ride_id'];
        $_SESSION['driver_ride_icon'] = isset($driver_account_details['icon_type']) ? $driver_account_details['icon_type'] : "1";
        $_SESSION['car_plate_num'] = $driver_account_details['car_plate_num'];
        $_SESSION['car_model'] = $driver_account_details['car_model'];
        $_SESSION['car_color'] = $driver_account_details['car_color'];
        $_SESSION['car_year'] = $driver_account_details['car_year'];
        $_SESSION['carcat'] = $driver_account_details['ride_type'];
        $_SESSION['allow_alt_cars'] = $driver_account_details['allow_alt_cars'];

               


        $tariff_data = getroutetariffs();

        $uncompleted_booking = [];
        //check if driver has pending bookings
        $query = sprintf('SELECT id, `status` FROM %stbl_bookings WHERE driver_id = %d AND (`status` =  0 OR `status` =  1)', DB_TBL_PREFIX, $_SESSION['uid']);
        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                while($row = mysqli_fetch_assoc($result)){
                    $uncompleted_booking[] = $row['id'];
                }
                
            }
        }


        //get default currency data
        $default_currency_data = [];
        $query = sprintf('SELECT * FROM %stbl_currencies WHERE `default` = 1', DB_TBL_PREFIX);
        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                $default_currency_data = mysqli_fetch_assoc($result);
            }
        }
        

        
        //profile information
        $profiledata = array(
            'success' => 1,
            'firstname'=> $_SESSION['firstname'],
            'lastname'=> $_SESSION['lastname'],
            'email'=> $_SESSION['email'],
            'phone'=> $_SESSION['phone'],
            'address'=> $_SESSION['address'],
            'driverid' => $_SESSION['uid'],
            'ref_code'=>$ref_code,
            'ref_code_copy_msg' => $refcode_copy,
            'ref_desc' => $ref_code_desc,
            'ref_url' => $referral_url,
            'photo' => $_SESSION['photo'],
            'city_id'=> !empty($driver_account_details['route_id']) ? $driver_account_details['route_id'] : 0,
            'city' => !empty($driver_account_details['r_title']) ? $driver_account_details['r_title'] : "Unknown",
            'city_lat' => !empty($driver_account_details['lat']) ? $driver_account_details['lat'] : "",
            'city_lng' => !empty($driver_account_details['lng']) ? $driver_account_details['lng'] : "",
            'night_start' => NIGHT_START,
            'night_end' => NIGHT_END,
            'carcat' => !empty($driver_account_details['ride_type']) ? $driver_account_details['ride_type'] : "N/A",
            'driver_rating' => $_SESSION['driver_rating'],
            'driver_ride_id' => $driver_account_details['ride_id'],
            'driver_ride_icon' => isset($driver_account_details['icon_type']) ? $driver_account_details['icon_type'] : "1",
            'country_code' => $driver_account_details['country_code'],
            'country_dial_code' => $driver_account_details['country_dial_code'],
            'user_docs' => $user_documents,
            'rejected_rides'=>$driver_account_details['rejected_rides'],
            'cancelled_rides'=>$driver_account_details['cancelled_rides'],
            'completed_rides'=>$driver_account_details['completed_rides'],
            'car_plate_num' => $driver_account_details['car_plate_num'],
            'car_model' => $driver_account_details['car_model'],
            'car_color' => $driver_account_details['car_color'],
            'car_year' => $driver_account_details['car_year'],
            'bank_name' => $driver_account_details['bank_name'],
            'bank_acc_holder_name' => $driver_account_details['bank_acc_holder_name'],
            'bank_acc_num' => $driver_account_details['bank_acc_num'],
            'bank_code' => $driver_account_details['bank_code'],
            'allow_alt_cars' => $driver_account_details['allow_alt_cars']
    
        );

        
    
    
    
        //Get online payment gateway data
    
        $online_payment_data = array(
    
            'merchantid'=> P_G_PK,
            'storeid'=> STORE_ID,
            'devid'=> DEV_ID,
            'notifyurl'=> NOTIFY_URL
            
        );

        $driver_city_currency = array(
            'cur_symbol'=> $driver_account_details['symbol'],
            'cur_code'=> $driver_account_details['iso_code'],
            'cur_exchng'=>$driver_account_details['exchng_rate'],
            'cur_name'=>$driver_account_details['name']
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
            'default_payment_gateway' => $payment_gateways,
            'driver_location_update_interval' => DRIVER_LOCATION_UPDATE_INTERVAL,
            'wallet_topup_presets' => WALLET_TOPUP_PRESETS,
            'default_banks_and_codes' => DEFAULT_BANKS_AND_CODES,
            'round_trip_fares' => ROUND_TRIP_FARES,
            'trip_cancel_reasons' => DRIVER_CANCEL_REASON,
            "allowed_countries_list" => ALLOWED_COUNTRIES_LIST,
            "force_update_driver_android" => FORCE_UPDATE_DRIVER_ANDROID,
            "force_update_driver_ios" => FORCE_UPDATE_DRIVER_IOS               
        );
        

        $firebase_rtdb_conf = array(
            'databaseURL' => FB_RTDB_URL,
            'apiKey' => FB_WEB_API_KEY,
            'storageBucket' => FB_STORAGE_BCKT
        );


        

        //update driver last seen time and user selected app language
        
        if($driver_account_details['is_activated'] == 1){
            $query = sprintf('UPDATE %stbl_drivers SET last_login_date = "%s", disp_lang = "%s", account_active = "%s" WHERE driver_id = %d', DB_TBL_PREFIX,gmdate('Y-m-d H:i:s', time()),$display_language,1,$_SESSION['uid']);
        }else{
            $query = sprintf('UPDATE %stbl_drivers SET last_login_date = "%s", disp_lang = "%s" WHERE driver_id = %d', DB_TBL_PREFIX,gmdate('Y-m-d H:i:s', time()),$display_language,$_SESSION['uid']);
        }
        
        $result = mysqli_query($GLOBALS['DB'], $query);

        $_SESSION['lang'] = $display_language;

        //get drivers time online

        $time_online = gettodaytimeonline();

        $hours = floor($time_online / 3600);
        $minutes = floor(($time_online % 3600) / 60 );
        //$seconds = ($time_online % 3600) % 60;

        $driver_time_online_formated = '';
        if(!empty($hours)){
            $driver_time_online_formated = $hours . "H ";
        }

        if(!empty($minutes)){
            $driver_time_online_formated .= $minutes . "M ";
        }else{
            $driver_time_online_formated .=  "0M ";
        }


        //get drivers total earning and number of copleted rides today
        $driver_today_earnings = 0;   
        $completed_trips_today = 0; 

        $query = sprintf('SELECT %1$stbl_bookings.paid_amount, %1$stbl_bookings.cur_exchng_rate, %1$stbl_bookings.driver_commision FROM %1$stbl_bookings
        WHERE %1$stbl_bookings.driver_id = %2$d AND %1$stbl_bookings.status = 3 AND DATE(%1$stbl_bookings.date_completed) = "%3$s"', DB_TBL_PREFIX,$_SESSION['uid'], date('Y-m-d', time()));
        

        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                while($row = mysqli_fetch_assoc($result)){
                    $completed_trips_today++;
                    $driver_today_earnings += ((float) $row['paid_amount'] / $row['cur_exchng_rate']) * $row['driver_commision'] / 100;
                }
                                
            }    
        }
    
        
        //return data
        $data = array('driver_time_online'=>$driver_time_online_formated,"fb_conf"=>$firebase_rtdb_conf,'driver_today_earning'=>$driver_today_earnings,'completed_trips'=>$completed_trips_today,"availability" => $_SESSION['availability'],"tariff_data"=>$tariff_data,"loggedin"=>1,'online_pay'=>$online_payment_data,'profileinfo' => $profiledata,'cc_num'=>CALL_CENTER_NUMBER,'wallet_amt' => $_SESSION['wallet_amt'],'driver_min_wallet_balance' => DRIVER_MIN_WALLET_BALANCE,"driver_id"=>$driver_account_details['driver_id'],"email"=>$driver_account_details['email'],"is_activated"=>$driver_account_details['is_activated'],"account_active"=>$driver_account_details['account_active'],'currency_data'=>$driver_city_currency,'app_version_ios'=>APP_VERSION_DRIVER_IOS,'app_version_android'=>APP_VERSION_DRIVER_ANDROID,'driver_app_update_url_ios'=>DRIVER_APP_UPDATE_URL_IOS,'driver_app_update_url_android'=>DRIVER_APP_UPDATE_URL_ANDROID,'uncompleted_bk'=>$uncompleted_booking,'default_currency'=>$default_currency_data,'app_settings'=>$app_settings,'sess_id' => base64_encode(session_id()),'bannerdata'=>getbannerdata(),'scheduled_trips_count' => getScheduledBookings(1));
        echo json_encode($data);
        exit;

    }else{

        forceLogin();

    }

}


function forceLogin(){

    $display_language = mysqli_real_escape_string($GLOBALS['DB'], $_POST['display_lang']);

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
            'default_payment_gateway' => $payment_gateways,
            'driver_location_update_interval' => DRIVER_LOCATION_UPDATE_INTERVAL,
            'wallet_topup_presets' => WALLET_TOPUP_PRESETS,
            'default_banks_and_codes' => DEFAULT_BANKS_AND_CODES,
            "allowed_countries_list" => ALLOWED_COUNTRIES_LIST,
            "force_update_driver_android" => FORCE_UPDATE_DRIVER_ANDROID,
            "force_update_driver_ios" => FORCE_UPDATE_DRIVER_IOS              
        );

        //get active driver referrals data
        $referral_data = [];
        $query = sprintf('SELECT %1$stbl_referral_drivers.route_id AS ref_route_id, %1$stbl_referral_drivers.invitee_incentive, %1$stbl_referral_drivers.number_of_rides, %1$stbl_referral_drivers.number_of_days, %1$stbl_currencies.symbol FROM %1$stbl_referral_drivers 
        INNER JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_referral_drivers.route_id
        INNER JOIN %1$stbl_currencies ON %1$stbl_currencies.id = %1$stbl_routes.city_currency_id
        WHERE %1$stbl_referral_drivers.status = 1', DB_TBL_PREFIX);

        if($result = mysqli_query($GLOBALS['DB'],$query)){
            if(mysqli_num_rows($result)){
                while($row = mysqli_fetch_assoc($result)){
                    $referral_data[$row['ref_route_id']] = $row;
                    $referral_data[$row['ref_route_id']]['ref_reg_msg'] = __('Enter a referral code and earn {---1} when you complete {---2} trips in {---3} days',[$row['symbol'].$row['invitee_incentive'],$row['number_of_rides'],$row['number_of_days']],"d|{$display_language}");
                }
            }
        }

        $tariff_data = getroutetariffs();

        $display_language = mysqli_real_escape_string($GLOBALS['DB'], $_POST['display_lang']);
        $_SESSION['lang'] = $display_language;

        //return data
        $data = array("loggedin"=>0,"referral_data"=>$referral_data,"tariff_data"=>$tariff_data,'cc_num'=>CALL_CENTER_NUMBER,'app_version_ios'=>APP_VERSION_DRIVER_IOS,'app_version_android'=>APP_VERSION_DRIVER_ANDROID,'driver_app_update_url_ios'=>DRIVER_APP_UPDATE_URL_IOS,'driver_app_update_url_android'=>DRIVER_APP_UPDATE_URL_ANDROID,'app_settings'=>$app_settings,'sess_id' => base64_encode(session_id()));
        echo json_encode($data);
        exit;
}




function getuserinfopages(){

        

    $user_info_pages = [];
    $query = sprintf('SELECT * FROM %stbl_appinfo_pages WHERE id = 2 OR id = 4', DB_TBL_PREFIX); //Get required user information from DB


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

    
    $data = array("success"=>1,'about'=>$user_info_pages[2]['content'],'terms'=>$user_info_pages[4]['content']);
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
    $query = sprintf('SELECT * FROM %1$stbl_help_cat WHERE %1$stbl_help_cat.show_driver = 1', DB_TBL_PREFIX); //Get required user information from DB

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                $help_categories_data[$row['id']] = $row; //sort data array into categories id indexes
            }
        }else{
            $error = array("error"=>__("Help information is not available"));
            echo json_encode($error); //invalid record
            exit; 
        }                    
    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //invalid record
        exit; 
    }


    //get all help contents displayable on driver app
    $help_topics_strings = [];
    $query = sprintf('SELECT id,cat_id,title,excerpt FROM %1$stbl_appinfo_pages WHERE %1$stbl_appinfo_pages.show_driver = 1 AND %1$stbl_appinfo_pages.type = 1', DB_TBL_PREFIX); //Get required user information from DB

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                $help_content_data[$row['cat_id']][] = $row;
                $help_topics_strings[$row['cat_id']] = '';
            }
        }else{
            $error = array("error"=>__("Help information is not available"));
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





function setAvailability(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error);
        exit; 
    }

    $driver_account_details = [];

    //get driver current availability status
    $query = sprintf('SELECT * FROM %stbl_drivers WHERE driver_id = %d ', DB_TBL_PREFIX, $_SESSION['uid']); //Get required user information from DB

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $driver_account_details = mysqli_fetch_assoc($result);
        }
        else{
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); //invalid record
            exit;

        }
        
    }
    else{ 

        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;
    }


    //get user documents and check if he has any pending documents
    $driver_pending_document = 0;

    $query = sprintf('SELECT *,%1$stbl_documents.id AS d_id FROM %1$stbl_documents
    LEFT JOIN %1$stbl_users_documents ON %1$stbl_users_documents.doc_id = %1$stbl_documents.id AND %1$stbl_users_documents.u_id = %2$d AND %1$stbl_users_documents.u_type = 1
    WHERE %1$stbl_documents.doc_user = 1 AND %1$stbl_documents.status = 1', DB_TBL_PREFIX, $_SESSION['uid']);


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                if((empty($row['doc_city']) || $row['doc_city'] == $driver_account_details['route_id']) && $row['u_doc_status'] != 3){
                    $driver_pending_document = 1;
                }
            }
        }
    }

    if(!empty($driver_pending_document) && empty(DEMO)){

        if(!empty($driver_account_details['available'])){

            //driver is currently set online and he has pending documents. Put him offline
            $query = sprintf('UPDATE %stbl_drivers SET `available` = %d WHERE driver_id = %d', DB_TBL_PREFIX, 0, $driver_account_details['driver_id']);
            if($result = mysqli_query($GLOBALS['DB'], $query)){
                $_SESSION['availability'] = 0;
            }

            $error = array("error"=>__("An error has occured"), 'doc_error' => 1);
            echo json_encode($error); 
            exit;

        }else{
            $error = array("error"=>__("An error has occured"), 'doc_error' => 1);
            echo json_encode($error); 
            exit;
        }
        
    }

    //check if he has sufficient funds in wallet
    if($driver_account_details['wallet_amount'] <= DRIVER_MIN_WALLET_BALANCE){

        if(!empty($driver_account_details['available'])){
            //send driver offline and show the wallet topup page on the app
            $query = sprintf('UPDATE %stbl_drivers SET `available` = %d WHERE driver_id = %d', DB_TBL_PREFIX, 0, $_SESSION['uid']);
            if($result = mysqli_query($GLOBALS['DB'], $query)){
                $_SESSION['availability'] = 0;
            }
                    
            $error = array("error"=>__("Insufficient fund in wallet. Please fund your wallet"), "wallet_error" => 1);
            echo json_encode($error); 
            exit;
        }else{
            $error = array("error"=>__("Insufficient fund in wallet. Please fund your wallet"), "wallet_error" => 1);
            echo json_encode($error); 
            exit;
        }

    }



    $availability = !empty($driver_account_details['available']) ? 0 : 1; //toggle availability status


    $query = sprintf('UPDATE %stbl_drivers SET `available` = %d WHERE driver_id = %d', DB_TBL_PREFIX,$availability, $_SESSION['uid']);
    if($result = mysqli_query($GLOBALS['DB'], $query)){
        $_SESSION['availability'] = $availability;
        $response = array("success"=>1,"status"=>$availability);
        echo json_encode($response);
        exit;        
    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit; 
    }

    



}



function setAltCars(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error);
        exit; 
    }

    
    $driver_account_details = [];

    //get driver current alternate cars status
    $query = sprintf('SELECT allow_alt_cars FROM %stbl_drivers WHERE driver_id = %d ', DB_TBL_PREFIX, $_SESSION['uid']); //Get required user information from DB

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $driver_account_details = mysqli_fetch_assoc($result);
        }
        else{
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); //invalid record
            exit;

        }
        
    }
    else{ 

        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;
    }


    $alt_cars_status = !empty($driver_account_details['allow_alt_cars']) ? 0 : 1; //toggle alt cars status


    $query = sprintf('UPDATE %stbl_drivers SET `allow_alt_cars` = %d WHERE driver_id = %d', DB_TBL_PREFIX,$alt_cars_status, $_SESSION['uid']);
    if($result = mysqli_query($GLOBALS['DB'], $query)){
        $_SESSION['allow_alt_cars'] = $alt_cars_status;
        $response = array("success"=>1,"status"=>$alt_cars_status);
        echo json_encode($response);
        exit;        
    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit; 
    }
    



}



function driverLogout(){
    
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

    $query = sprintf('SELECT * FROM %stbl_drivers WHERE `driver_id` = %d AND pwd_raw = "%s"', DB_TBL_PREFIX,$_SESSION['uid'],$password);

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



    //check if driver has a pending ride or ride in progress
    $query = sprintf('SELECT * FROM %stbl_bookings WHERE `driver_id` = %d AND (`status` = 0 OR `status` = 1 OR `status` = 6)', DB_TBL_PREFIX, $_SESSION['uid']);
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
    $query = sprintf('UPDATE %stbl_drivers SET account_deleted = 1 WHERE `driver_id` = %d AND pwd_raw = "%s"', DB_TBL_PREFIX, $_SESSION['uid'], $password);
    $result = mysqli_query($GLOBALS['DB'], $query);


    //delete all notifications
    /* $query = sprintf('DELETE FROM %stbl_notifications WHERE `person_id` = %d AND user_type = %d', DB_TBL_PREFIX, $_SESSION['uid'], 1);
    $result = mysqli_query($GLOBALS['DB'], $query); */

    
    //we cannot delete users booking data or transaction data since it is used for analytics.


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
    if($rating < 0 || $rating < 1){
        $rating = 1;
    }elseif($rating > 5){
        $rating = 5;
    } 


    $booking_data = [];

    //get user details for this booking
    $query = sprintf('SELECT %1$stbl_bookings.user_id, %1$stbl_users.user_rating FROM %1$stbl_bookings 
    INNER JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
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
    $query = sprintf('INSERT INTO %stbl_ratings_drivers (booking_id,`driver_id`,driver_comment,driver_rating) VALUES (%d,%d,"%s",%d)',
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

    //update user rating with this users rating

    $user_new_rating = floor(($booking_data['user_rating'] + $rating) / 2);

    if($user_new_rating > 5)$user_new_rating = 5;

    $query = sprintf('UPDATE %stbl_users SET user_rating = %d WHERE user_id = %d',DB_TBL_PREFIX,$user_new_rating,$booking_data['user_id']);
    $result = mysqli_query($GLOBALS['DB'], $query);

    $data = array("success"=>1);
    echo json_encode($data);
    exit;


}



function userResendCode(){

    if(isset($_SESSION['code_resend']['time'])){
        if($_SESSION['code_resend']['time'] < time() - 30){ //enable code resend at 30 seconds interval
            $error = array("error"=>"Please wait a while before resending code.");
            echo json_encode($error); 
            exit;
        } 
    }

    if(!empty($_SESSION['loggedin'])){
        $user_id = $_SESSION['uid'];
        $email = $_SESSION['email'];
        $phone = $_SESSION['phone'];
    }elseif(!empty($_SESSION['new_reg'])){

        $user_id = $_SESSION['new_reg']['uid'];
        $email = $_SESSION['new_reg']['email'];
        $phone = $_SESSION['new_reg']['phone'];

    }else{

        $error = array("error"=>"Error resending activation code");
        echo json_encode($error); 
        exit;

    }

    $code = crypto_string("nozero",5); //generate token

    
    
    
    //delete any previous activation code for this user
    $query = sprintf('DELETE FROM %stbl_account_codes WHERE user_id = "%d" AND user_type = 1 AND context=0', DB_TBL_PREFIX, $user_id); //delete already inserted record 
    $result = mysqli_query($GLOBALS['DB'], $query);

    $query = sprintf('INSERT INTO %stbl_account_codes (user_id, code, user_type) VALUES ("%d","%s","%d")',DB_TBL_PREFIX, $user_id, $code, 1); 
    
    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ //An error has occured while trying to update
        $error = array("error"=>"Error resending activation code");
        echo json_encode($error); 
        exit;
    }

        
    $message = "";

    $message .= '<html>';
    $message .= '<div style = "max-width:100%;"><img src="'."http://". $_SERVER['HTTP_HOST'].'/img/logo-mid.png" width="180px" style="margin-left:auto; margin-right:auto; display:block;"/><br/>';
    $message .='<h2 style="text-align:center;">Thank you for registering with ' .WEBSITE_NAME . '</h2><br /><br />';
    $message .='<p>Your account has been created but is currently not activated. To complete your registration, enter the activation code as requested.</p>';
    $message .="<h2><b style='text-align:center;'>{$code}</b></h2>";
    $message .="<br /><br /><br /><br /><br /><br />";
    $message .="<p>You have received this email because a user created an account on " . WEBSITE_NAME . " Website."; 
    $message .="Simply ignore the message if it is not you</p></div>";
    $message .="</html >";

    $mail_sender_address = 'From: '.MAIL_SENDER;
    $headers = array($mail_sender_address,'MIME-Version: 1.0', 'Content-Type: text/html; charset="iso-8859-1"'); //Required for a HTML formatted E-mail ;)

    //echo $message;

    //mail($_POST['email'], 'Account Activation - UNN e-Learning Platform', $message, join("\r\n", $headers)); //send the email
    if(!mail($email, WEBSITE_NAME." - Activation Code", $message, join("\r\n", $headers))){

        $error = array("error"=>"Error resending activation code");
        echo json_encode($error); 
        exit;

    } //send the email


    //send SMS to user phone
    
    //$send_to_phone = trim($phone);
    //$msg = urlencode("Your " . WEBSITE_NAME ." activation number or key is {$code}. Thank you for choosing ".WEBSITE_NAME.". Your taxi service provider!");
    //$url = "http://smslive247.com/http/index.aspx?cmd=sendquickmsg&owneremail=myemail@yahoo.co.uk&subacct=accountname&subacctpwd=password&message={$msg}&sender=smssender&sendto={$send_to_phone}&msgtype=0";
    //$curl = curl_init($url);
    //curl_setopt($curl, CURLOPT_HEADER, true);
    //curl_setopt($curl,CURLOPT_USERAGENT,'Mozilla/5.0 (Linux; Android 8.0.0; Pixel 2 XL Build/OPD1.170816.004) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Mobile Safari/537.36');
    //curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($curl, CURLOPT_HTTPGET, true);
    //curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
    //curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
    //$response = curl_exec($curl);
    //$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    //curl_close($curl);
    
    $_SESSION['code_resend']['time'] = time();
    
    
    $success = array("success"=>"Activation code sent");
    echo json_encode($success); 
    exit;



}




function passwordReset(){

    $user_account_details = [];

    $email = !empty($_POST['email']) ? mysqli_real_escape_string($GLOBALS['DB'], $_POST['email']) : "";

    //check if this email exist on db
    $query = sprintf('SELECT driver_id,email,phone FROM %stbl_drivers WHERE email = "%s"', DB_TBL_PREFIX, $email); //Get required user information from DB


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
    $query = sprintf('DELETE FROM %stbl_account_codes WHERE user_id = "%d" AND user_type = 1 AND context=1', DB_TBL_PREFIX, $user_account_details['driver_id']); //delete already inserted record 
    $result = mysqli_query($GLOBALS['DB'], $query);

    //save code in table for this user to signify password change request
    $query = sprintf('INSERT INTO %stbl_account_codes (user_id, code,context, user_type) VALUES ("%d","%s",1,1)',DB_TBL_PREFIX, $user_account_details['driver_id'], $code); 
    
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
    $query = sprintf('SELECT driver_id,email,phone,country_dial_code,pwd_raw FROM %stbl_drivers WHERE phone = "%s" AND country_dial_code = "%s"', DB_TBL_PREFIX, $phone, "+" . $country_code); //Get required user information from DB


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
    $query = sprintf('SELECT user_id FROM %stbl_account_codes WHERE code = "%s" AND user_type = 1 AND context = 1', DB_TBL_PREFIX, $passcode); //Get required user information from DB


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
    $query = sprintf('UPDATE %stbl_drivers SET `pwd_raw` = "%s" WHERE driver_id = "%d"', DB_TBL_PREFIX,$newpassword,$user_account_details['user_id']);
    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ //An error has occured while trying to update
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }


    //delete any previous password reset code for this user
    $query = sprintf('DELETE FROM %stbl_account_codes WHERE code = "%s" AND user_type = 1 AND context=1', DB_TBL_PREFIX, $passcode); //delete already inserted record 
    $result = mysqli_query($GLOBALS['DB'], $query);

    
    $success = array("success"=>__("Password change was successful. Your new password is {---1}",["<b>{$newpassword}</b>"]));
    echo json_encode($success); 
    exit;




}





function userActivateCode(){

    $code = (int) $_POST['code'];
    if(empty($code)){
        $error = array("error"=>"Please enter an activation code");
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

    $query = sprintf('SELECT code FROM %stbl_account_codes WHERE code = "%d" AND user_id = "%d" AND user_type = 1', DB_TBL_PREFIX, $code,$user_id); //Get required user information from DB


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $row = mysqli_fetch_assoc($result);
        }
        else{
            $error = array("error"=>"Wrong activation code entered.");
            echo json_encode($error); 
            exit;
        }
        
    }
    else{ //No record matching the USER ID was found in DB. Show view to notify user

        $error = array("error"=>"Database Error");
        echo json_encode($error); 
        exit;
    }


    $query = sprintf('UPDATE %stbl_drivers SET is_activated = 1, account_active = 1 WHERE driver_id = "%d"', DB_TBL_PREFIX,$user_id );

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ //An error has occured while trying to update KSmart user ID on SIS user database record?
        $error = array("error"=>"Database error.");
        echo json_encode($error); 
        exit;
    }

    $query = sprintf('DELETE FROM %stbl_account_codes WHERE user_id = "%d" AND code="%d" AND user_type = 1', DB_TBL_PREFIX,$user_id,$code); //delete already inserted record 
    $result = mysqli_query($GLOBALS['DB'], $query);
    $_SESSION['is_activated'] = 1;

    
    $response = array("success"=>"Your account has been successfully activated. Restart app.");
    echo json_encode($response); 
    exit; 
   




}



function bookingassigndriver(){

    $booking_id = (int) $_POST['booking_id'];
    $driver_id = (int) $_POST['driver_id'];
    $driver_data = [];

       
    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please re-login and retry.");
        echo json_encode($error); 
        exit; 
    }

    $query = sprintf('SELECT * FROM %1$stbl_drivers WHERE %1$stbl_drivers.driver_id = "%2$d"', DB_TBL_PREFIX, $driver_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){

        $driver_data = mysqli_fetch_assoc($result);

        }
    }


    $driver_firstname = !empty($driver_data['firstname']) ? $driver_data['firstname'] : '';
    $driver_lastname = !empty($driver_data['lastname']) ? $driver_data['lastname'] : '';
    $driver_phone = !empty($driver_data['phone']) ? $driver_data['phone'] : '';


    $query = sprintf('UPDATE %stbl_bookings SET driver_id = "%d",driver_firstname = "%s",driver_lastname = "%s",driver_phone = "%s" WHERE id = "%d"', DB_TBL_PREFIX,$driver_id,$driver_firstname,$driver_lastname,$driver_phone,$booking_id );

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ //An error has occured while trying to update KSmart user ID on SIS user database record?
        $error = array("error"=>"Failed to assign driver");
        echo json_encode($error); 
        exit;
    }


    $success = array("success"=>"Record updated successfully");
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




function getuserprofileinfo(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please re-login and retry.");
        echo json_encode($error); 
        exit; 
    }

    $profiledata = array(
        'success' => 1,
        'firstname'=> $_SESSION['firstname'],
        'lastname'=> $_SESSION['lastname'],
        'email'=> $_SESSION['email'],
        'phone'=> $_SESSION['phone'],
        'address'=> $_SESSION['address']

    );

    
    //return data
    echo json_encode($profiledata);
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
    WHERE %1$stbl_rides.avail = 1 AND %1$stbl_routes.r_scope = 0 ORDER BY %1$stbl_routes.r_title', DB_TBL_PREFIX);

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
                
            $select_options .= "<img data-numseats='{$ridesdata['num_seats']}' data-cfare='{$ridesdata['cfare_enabled']}' data-ppenabled='{$ridesdata['pp_enabled']}' data-ppstart='{$ridesdata['pp_start']}' data-ppend='{$ridesdata['pp_end']}' data-ppdays='{$ridesdata['pp_active_days']}' data-ppchargetype='{$ridesdata['pp_charge_type']}' data-ppchargevalue='{$ridesdata['pp_charge_value']}' data-img='{$ride_image}' data-cpk = '{$ridesdata['cost_per_km']}' data-cpm = '{$ridesdata['cost_per_minute']}' data-puc = '{$ridesdata['pickup_cost']}' data-doc='{$ridesdata['drop_off_cost']}' data-cc='{$ridesdata['cancel_cost']}' data-ncpk = '{$ridesdata['ncost_per_km']}' data-ncpm = '{$ridesdata['ncost_per_minute']}' data-npuc = '{$ridesdata['npickup_cost']}' data-ndoc='{$ridesdata['ndrop_off_cost']}' data-ncc='{$ridesdata['ncancel_cost']}' data-ind='{$ridesdata['init_distance']}' data-nind='{$ridesdata['init_distance_n']}' value='{$ridesdata['ride_id']}' data-rideid='{$ridesdata['ride_id']}' data-ridedesc='{$ride_desc}' data-title='{$ride_title}' data-wt='{$ridesdata['wait_time']} data-wcpmr='{$ridesdata['wait_cost_per_minute']}' data-nwt='{$ridesdata['nwait_time']} data-nwcpmr='{$ridesdata['nwait_cost_per_minute']}' data-hren='{$ridesdata['hr_enabled']}' data-hrcph='{$ridesdata['hr_cph']}' data-hrdist='{$ridesdata['hr_dist']}' data-nhrcph='{$ridesdata['nhr_cph']}' data-nhrdist='{$ridesdata['nhr_dist']}' style='width:85px;margin-right:auto;margin-left:auto;' src='{$ride_image}' />";
            //$select_options .= "<option data-cfare='{$ridesdata['cfare_enabled']}' data-ppenabled='{$ridesdata['pp_enabled']}' data-ppstart='{$ridesdata['pp_start']}' data-ppend='{$ridesdata['pp_end']}' data-ppdays='{$ridesdata['pp_active_days']}' data-ppchargetype='{$ridesdata['pp_charge_type']}' data-ppchargevalue='{$ridesdata['pp_charge_value']}' data-img='{$ride_image}' data-cpk = '{$ridesdata['cost_per_km']}' data-cpm = '{$ridesdata['cost_per_minute']}' data-puc = '{$ridesdata['pickup_cost']}' data-doc='{$ridesdata['drop_off_cost']}' data-cc='{$ridesdata['cancel_cost']}' data-ncpk = '{$ridesdata['ncost_per_km']}' data-ncpm = '{$ridesdata['ncost_per_minute']}' data-npuc = '{$ridesdata['npickup_cost']}' data-ndoc='{$ridesdata['ndrop_off_cost']}' data-ncc='{$ridesdata['ncancel_cost']}' value='{$ridesdata['ride_id']}' data-rideid='{$ridesdata['ride_id']}' data-ridedesc='{$ride_desc}'>{$ridesdata['ride_type']}</option>";
        }

        $rides_data[$key]['cars_html'] = $select_options;
        
    }

    

        
    $rides_data['city'] = $city_select_options;
    $rides_data['state'] = $state_select_options;
    $rides_data['preloadrides'] = $rides_url;
    $rides_data['zones'] = $zones_data;

    if(PAYMENT_TYPE == 2){ //cash and wallet
        $rides_data['payment_options'] = "<option value='1'>Cash</option><option value='2'>Wallet</option>";
        $rides_data['payment_options_data'] = [['name' => __("Cash"), "id" => 1], ['name' => __("Wallet"), "id" => 2]];
    }elseif(PAYMENT_TYPE == 1){ //wallet only
        $rides_data['payment_options'] = "<option value='2'>Wallet</option>";
        $rides_data['payment_options_data'] = [['name' => __("Wallet"), "id" => 2]];
    }else{ //cash only
        $rides_data['payment_options'] = "<option value='1'>Cash</option>";
        $rides_data['payment_options_data'] = [['name' => __("Cash"), "id" => 1]];
    }
    
    $rides_data['nighttime'] = array('start_hour'=>NIGHT_START, 'end_hour'=>NIGHT_END);
    $data_array = array("success"=>1,'result'=>$rides_data);


    
    return $data_array; 
    




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
    $template2 = ''; //stores onsenui formated wallet transactions data
    $transaction_data_sort = [];
    $transaction_data_sort['credit-debit-data'] = [];
    $transaction_data_sort['funding-data'] = [];

    $query = sprintf('SELECT *,%1$stbl_wallet_transactions.cur_exchng_rate AS exchng_rate,%1$stbl_bookings.cur_symbol AS b_cur_symbol,%1$stbl_wallet_transactions.cur_symbol AS t_cur_symbol,%1$stbl_wallet_transactions.transaction_id AS transaction_id,%1$stbl_wallet_transactions.transaction_date AS transaction_date,%1$stbl_wallet_transactions.transaction_date AS transaction_dates FROM %1$stbl_wallet_transactions 
    LEFT JOIN %1$stbl_bookings ON %1$stbl_bookings.id = %1$stbl_wallet_transactions.book_id
    LEFT JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_wallet_transactions.user_id
    LEFT JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_drivers.route_id
    LEFT JOIN %1$stbl_currencies ON %1$stbl_currencies.id = %1$stbl_routes.city_currency_id
    WHERE %1$stbl_wallet_transactions.user_id = "%2$d" AND %1$stbl_wallet_transactions.user_type = 1 ORDER BY %1$stbl_wallet_transactions.transaction_date DESC LIMIT 0,100 ', DB_TBL_PREFIX,$_SESSION['uid']); //Get required user information from DB


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                $sort_date = date('Y-m-d',strtotime($row['transaction_date'] . ' UTC'));
                if($row['type'] == 2 || $row['type'] == 3){
                    $transaction_data_sort['credit-debit-data'][$sort_date]['date'] = $row['transaction_date'];
                    $transaction_data_sort['credit-debit-data'][$sort_date]['data'][] = $row;
                }else{
                    $transaction_data_sort['funding-data'][$sort_date]['date'] = $row['transaction_date'];
                    $transaction_data_sort['funding-data'][$sort_date]['data'][] = $row;
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

                $template .= "<ons-list-header style='font-size: 14px;font-weight:bold;margin:20px 0;'>{$t_date_format}</ons-list-header>";
                
    
                foreach($transactiondatasort['data'] as $transaction_d){
                    $transaction_time = date('g:i A',strtotime($transaction_d['transaction_dates'] . " UTC"));
                    $transaction_id_upper = strtoupper($transaction_d['transaction_id']);
                    $wallet_balance_converted =  (float) $transaction_d['wallet_balance'] * (float) $transaction_d['exchng_rate'];
                    $wallet_balance_converted = number_format((float) $wallet_balance_converted, 2);
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
                                            <div style='width:100%;margin-bottom:15px;'><ons-icon icon='fa-circle' size='14px' style='color: #24c539; font-size: 14px;'></ons-icon> <span class='list-item__title'>{$transaction_time}</span> </div>
                                            <span class='list-item__subtitle' style='margin-bottom:5px;'><span style='color:#000;font-size:20px;'>" . __("Amount") . ": {$transaction_d['t_cur_symbol']}{$transaction_d['amount']}</span></span>
                                            <span class='list-item__subtitle'><span style='color:#777'><ons-icon icon='fa-square' size='8px' style='vertical-align: bottom;color: black; font-size: 10px;'></ons-icon> " . __("Description") . ": {$transaction_d['desc']}</span></span>
                                            <span class='list-item__subtitle'><span style='color:#777'><ons-icon icon='fa-square' size='8px' style='vertical-align: bottom;color: black; font-size: 10px;'></ons-icon> " . __("Transaction ID") . ":{$transaction_id_upper} </span></span>                                            
                                            <span class='list-item__subtitle'><span style='color:#777'><ons-icon icon='fa-square' size='8px' style='vertical-align: bottom;color: black; font-size: 10px;'></ons-icon> " . __("Wallet Balance") . ":</span> {$transaction_d['t_cur_symbol']}{$wallet_balance_converted}</span>
                                        </div>
                                    
                                    </ons-list-item>";
                   

                }
    
        }

    }



    //format for display on app
    foreach($transaction_data_sort['credit-debit-data'] as $transactiondatasort){

        if(!empty($transactiondatasort['data'])){ 

                $day_num = date('j',strtotime($transactiondatasort['date'] . " UTC"));
                $month_name = date('F',strtotime($transactiondatasort['date'] . " UTC"));                
                $t_date_format = $day_num . " " . __($month_name);

                $template2 .= "<ons-list-header style='font-size: 14px;font-weight:bold;margin:20px 0;'>{$t_date_format}</ons-list-header>";
    
                foreach($transactiondatasort['data'] as $transaction_d){
                    $transaction_time = date('g:i A',strtotime($transaction_d['transaction_dates'] . " UTC"));
                    $transaction_id_upper = strtoupper($transaction_d['transaction_id']);
                    $wallet_balance_converted =  (float) $transaction_d['wallet_balance'] * (float) $transaction_d['exchng_rate'];
                    $wallet_balance_converted = number_format($wallet_balance_converted, 2);
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
                                            <span class='list-item__subtitle' style='margin-bottom:5px;'><span style='color:#000;font-size:20px;'>".__("Amount").": {$transaction_d['t_cur_symbol']}{$transaction_d['amount']}</span></span>
                                            <span class='list-item__subtitle'><span style='color:#777'><ons-icon icon='fa-square' size='8px' style='vertical-align: bottom;color: black; font-size: 10px;'></ons-icon> ".__("Description").": {$transaction_d['desc']}</span></span>
                                            <span class='list-item__subtitle'><span style='color:#777'><ons-icon icon='fa-square' size='8px' style='vertical-align: bottom;color: black; font-size: 10px;'></ons-icon> ".__("Transaction ID").":{$transaction_id_upper} </span></span>                                            
                                            <span class='list-item__subtitle'><span style='color:#777'><ons-icon icon='fa-square' size='8px' style='vertical-align: bottom;color: black; font-size: 10px;'></ons-icon> ".__("Booking ID").":</span> {$booking_id} </span>
                                            <span class='list-item__subtitle'><span style='color:#777'><ons-icon icon='fa-square' size='8px' style='vertical-align: bottom;color: black; font-size: 10px;'></ons-icon> ".__("Booking Fare").":</span> {$booking_fare} </span>
                                            <span class='list-item__subtitle'><span style='color:#777'><ons-icon icon='fa-square' size='8px' style='vertical-align: bottom;color: black; font-size: 10px;'></ons-icon> ".__("Wallet Balance").":</span> {$transaction_d['t_cur_symbol']}{$wallet_balance_converted}</span>
                                        </div>
                                    
                                    </ons-list-item>";   


                    

                }
    
        }

    }
    
    //Get wallet amount

    $query = sprintf('SELECT %1$stbl_drivers.wallet_amount,%1$stbl_currencies.symbol,%1$stbl_currencies.exchng_rate FROM %1$stbl_drivers 
    LEFT JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_drivers.route_id
    LEFT JOIN %1$stbl_currencies ON %1$stbl_currencies.id = %1$stbl_routes.city_currency_id
    WHERE %1$stbl_drivers.driver_id = %2$d', DB_TBL_PREFIX, $_SESSION['uid']); //Get required user information from DB


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

    $withdrawal_enabled = 0;
    $withdrawal_message = "";
    if(isset($user_wallet_details['wallet_amount'])){
        $balance = (float) $user_wallet_details['wallet_amount'] - DRIVER_WITHDRAL_MIN_BALANCE - 1;

        if(empty($balance) || $balance < 0){
            $withdrawal_enabled = 0;
            $withdrawal_message = __("Insufficient wallet amount! Complete more rides to increase wallet balance"); 
        }else{
            $withdrawal_enabled = 1;
            $withdrawal_message = __("Enter the Amount of money you want to cash out.") . __("Maximum amount") . ": <b>".$user_wallet_details['symbol'] . floattocurrency($balance * $user_wallet_details['exchng_rate']) . "</b>";
        }
    }else{
        $withdrawal_enabled = 0;
        $withdrawal_message = __("Insufficient wallet amount!");
    }

    $data_array = array("success"=>1,'wallet_amt'=>$_SESSION['wallet_amt'], 'wallet_history' => $template,'wallet_earning' => $template2,"withdrawenabled"=>$withdrawal_enabled,"withdrawmessage"=>$withdrawal_message, 'driver_min_wallet_balance' => DRIVER_MIN_WALLET_BALANCE);    
    echo json_encode($data_array); 
    exit;

}





function getbookings(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please re-login and retry.");
        echo json_encode($error); 
        exit; 
    }

    $booking_data = [];
    $booking_pend_onride = '';
    $booking_completed = '';
    $booking_cancelled = '';


    $query = sprintf('SELECT *, DATE(%1$stbl_bookings.date_created) AS created_date,%1$stbl_bookings.id AS booking_id FROM %1$stbl_bookings 
    LEFT JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id
    LEFT JOIN %1$stbl_rides ON %1$stbl_rides.id = %1$stbl_bookings.ride_id
    LEFT JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
    WHERE %1$stbl_bookings.user_id = %2$s ORDER BY %1$stbl_bookings.date_created DESC LIMIT 0,200 ', DB_TBL_PREFIX,$_SESSION['uid']);


    if($result = mysqli_query($GLOBALS['DB'], $query)){

        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                $booking_data[] = $row;
            }
            
            mysqli_free_result($result);
        }else{

            $error = array("error"=>"You do not have any booking records.");
            echo json_encode($error); 
            exit; 

        }
    }else{

        $error = array("error"=>"Error retrieving booking records.");
        echo json_encode($error); 
        exit; 

    }

    //sort booking data
    $booking_data_sort = [];

    foreach($booking_data as $bookingdata){
        if($bookingdata['status'] == 0 || $bookingdata['status'] == 1){ //pending or onride
            $booking_data_sort[$bookingdata['created_date']]['date'] = $bookingdata['created_date'];
            $booking_data_sort[$bookingdata['created_date']]['pend_onride'][] = $bookingdata;
        }elseif($bookingdata['status'] == 3){ //completed
            $booking_data_sort[$bookingdata['created_date']]['date'] = $bookingdata['created_date'];
            $booking_data_sort[$bookingdata['created_date']]['completed'][] = $bookingdata;
        }elseif($bookingdata['status'] == 2){ //cancelled
            $booking_data_sort[$bookingdata['created_date']]['date'] = $bookingdata['created_date'];
            $booking_data_sort[$bookingdata['created_date']]['cancelled'][] = $bookingdata;
        }        
        

    }


    //format for display on app


    foreach($booking_data_sort as $bookingdatasort){

        if(!empty($bookingdatasort['pend_onride'])){
            //save date
            $booking_pend_onride .= "<ons-list-header>{$bookingdatasort['date']}</ons-list-header>";


            //format pending onride rides for this date
            foreach($bookingdatasort['pend_onride'] as $bookingdatasort_po){
                $booking_time = date('g:i A',strtotime($bookingdatasort_po['date_created'] . ' UTC'));

                
                $booking_ptime = date('g:i A',strtotime($bookingdatasort_po['pickup_datetime'] . ' UTC'));
                $booking_driver = isset($bookingdatasort_po['driver_id']) ? $bookingdatasort_po['driver_firstname'] ." " . $bookingdatasort_po['driver_lastname'] : "N/A";
                $booking_driver_assigned = isset($bookingdatasort_po['driver_id']) ? 1 : 0;
                $status = '';
                $close_btn = '';
                if($bookingdatasort_po['status'] == 0){
                    $status = "<span style='color:#e541e5'>[Pending]</span>";
                    $close_btn = "<span style='display:inline-block;float:right'><ons-icon onclick = 'bookingcancel({$bookingdatasort_po['booking_id']},{$booking_driver_assigned})' icon='fa-times' size='18px' style='color:red'></ons-icon></span>";
                }else{
                    $status = "<span style='color:lightgreen'>[On-ride]</span>";
                    $close_btn = '';
                }

                $booking_pdate_time = date('d/m/Y g:i A',strtotime($bookingdatasort_po['pickup_datetime'] . ' UTC'));
                //$drvr_photo = explode('/',$bookingdatasort_po['photo_file']);
                $drvr_photo_file = isset($bookingdatasort_po['photo_file']) ? SITE_URL . "ajaxphotofile.php?file=".$bookingdatasort_po['photo_file'] : "0";
                $booking_payment_type = '';
                if(!empty($bookingdatasort_po['payment_type'])){
                    if($bookingdatasort_po['payment_type'] == 2){
                        $booking_payment_type = "Wallet";
                    }else{
                        $booking_payment_type = "Cash / POS";
                    }
                }
                $ride_filename = explode('/',$bookingdatasort_po['ride_img']);
                $ride_image = SITE_URL . 'img/ride_imgs/' . array_pop($ride_filename);
                $booking_title = str_pad($bookingdatasort_po['booking_id'] , 5, '0', STR_PAD_LEFT);
                $booking_pend_onride .= "<ons-list-item data-ridedesc='{$bookingdatasort_po['ride_desc']}'  data-driverphone='{$bookingdatasort_po['driver_phone']}' data-ptype='{$booking_payment_type}' data-put='{$booking_pdate_time}' data-driverimg='{$drvr_photo_file}' data-rideimg='{$ride_image}' data-drivername='{$booking_driver}' data-cost='{$bookingdatasort_po['estimated_cost']}' data-ride='{$bookingdatasort_po['ride_type']}' data-pul='{$bookingdatasort_po['pickup_address']}' data-dol='{$bookingdatasort_po['dropoff_address']}' data-btitle='{$booking_title}' id='booking-list-item-{$bookingdatasort_po['booking_id']}' modifier='longdivider'>
                
                                            <div class='center'>
                                                <div style='width:100%'><span class='list-item__title'>{$booking_time} {$status} </span> | <span onclick='showbookingdetails({$bookingdatasort_po['booking_id']})' style='color:skyblue'>View details</span> {$close_btn}</div>
                                                <span class='list-item__subtitle'><span style='color:yellow'>Booking ID:</span> {$booking_title}</span>
                                                <span class='list-item__subtitle'><span style='color:lightgreen'>Pick-up:</span> {$bookingdatasort_po['pickup_address']}</span>
                                                <span class='list-item__subtitle'><span style='color:orange'>Drop-off:</span> {$bookingdatasort_po['dropoff_address']}</span>
                                                <span class='list-item__subtitle'><span style='color:cyan'>Driver:</span> {$booking_driver}</span>
                                                
                                            </div>
                                        
                                        </ons-list-item>";

            }


        }
        
        if(!empty($bookingdatasort['completed'])){
            //save date
            $booking_completed .= "<ons-list-header>{$bookingdatasort['date']}</ons-list-header>";


            //format pending onride rides for this date
            foreach($bookingdatasort['completed'] as $bookingdatasort_comp){
                $booking_time = date('g:i A',strtotime($bookingdatasort_comp['date_created'] . ' UTC'));
                $booking_ptime = date('g:i A',strtotime($bookingdatasort_comp['pickup_datetime'] . ' UTC'));
                $booking_dtime = isset($bookingdatasort_comp['dropoff_datetime']) ? date('g:i A',strtotime($bookingdatasort_comp['dropoff_datetime'] . ' UTC')) : "N/A";
                $booking_paid_amt = isset($bookingdatasort_comp['paid_amount']) ? $bookingdatasort_comp['paid_amount'] : "N/A";
                $booking_driver = isset($bookingdatasort_comp['driver_id']) ? $bookingdatasort_comp['driver_firstname'] ." " . $bookingdatasort_comp['driver_lastname'] : "N/A";

                $booking_pdate_time = date('d/m/Y g:i A',strtotime($bookingdatasort_comp['pickup_datetime'] . ' UTC'));
                //$drvr_photo = explode('/',$bookingdatasort_comp['photo_file']);
                $drvr_photo_file = isset($bookingdatasort_comp['photo_file']) ? SITE_URL . "ajaxphotofile.php?file=".$bookingdatasort_comp['photo_file'] : "0";
                $booking_payment_type = '';
                if(!empty($bookingdatasort_comp['payment_type'])){
                    if($bookingdatasort_comp['payment_type'] == 2){
                        $booking_payment_type = "Wallet";
                    }else{
                        $booking_payment_type = "Cash / POS";
                    }
                }
                $ride_filename = explode('/',$bookingdatasort_comp['ride_img']);
                $ride_image = SITE_URL . 'img/ride_imgs/' . array_pop($ride_filename);
                $booking_title = str_pad($bookingdatasort_comp['booking_id'] , 5, '0', STR_PAD_LEFT);

                $booking_completed .= "<ons-list-item data-ridedesc='{$bookingdatasort_comp['ride_desc']}'  data-driverphone='{$bookingdatasort_comp['driver_phone']}' data-ptype='{$booking_payment_type}' data-put='{$booking_pdate_time}' data-driverimg='{$drvr_photo_file}' data-rideimg='{$ride_image}' data-drivername='{$booking_driver}' data-cost='{$bookingdatasort_comp['estimated_cost']}' data-ride='{$bookingdatasort_comp['ride_type']}' data-pul='{$bookingdatasort_comp['pickup_address']}' data-dol='{$bookingdatasort_comp['dropoff_address']}' data-btitle='{$booking_title}' id='booking-list-item-{$bookingdatasort_comp['booking_id']}' modifier='longdivider'>
                                            <div class='center'>
                                                <span class='list-item__title'>{$booking_time} | <span onclick='showbookingdetails({$bookingdatasort_comp['booking_id']})' style='color:skyblue'>View details</span> </span>
                                                <span class='list-item__subtitle'><span style='color:yellow'>Booking ID:</span> {$booking_title}</span>
                                                <span class='list-item__subtitle'><span style='color:lightgreen'>Pick-up:</span> {$bookingdatasort_comp['pickup_address']}</span>
                                                <span class='list-item__subtitle'><span style='color:orange'>Drop-off:</span> {$bookingdatasort_comp['dropoff_address']}</span>
                                                <span class='list-item__subtitle'><span style='color:cyan'>Driver: </span>{$booking_driver}</span>
                                            </div>
                                        
                                        </ons-list-item>";

            }


        }


        if(!empty($bookingdatasort['cancelled'])){
            //save date
            $booking_cancelled .= "<ons-list-header>{$bookingdatasort['date']}</ons-list-header>";


            //format pending onride rides for this date
            foreach($bookingdatasort['cancelled'] as $bookingdatasort_canc){
                $booking_time = date('g:i A',strtotime($bookingdatasort_canc['date_created'] . ' UTC'));
                $booking_ptime = date('g:i A',strtotime($bookingdatasort_canc['pickup_datetime'] . ' UTC'));
                $booking_driver = isset($bookingdatasort_canc['driver_id']) ? $bookingdatasort_canc['driver_firstname'] ." " . $bookingdatasort_canc['driver_lastname'] : "N/A";
                

                $booking_pdate_time = date('d/m/Y g:i A',strtotime($bookingdatasort_canc['pickup_datetime'] . ' UTC'));
                //$drvr_photo = explode('/',$bookingdatasort_canc['photo_file']);
                $drvr_photo_file = isset($bookingdatasort_canc['photo_file']) ? SITE_URL . "ajaxphotofile.php?file=".$bookingdatasort_canc['photo_file'] : "0";
                $booking_payment_type = '';
                if(!empty($bookingdatasort_canc['payment_type'])){
                    if($bookingdatasort_canc['payment_type'] == 2){
                        $booking_payment_type = "Wallet";
                    }else{
                        $booking_payment_type = "Cash / POS";
                    }
                }
                $ride_filename = explode('/',$bookingdatasort_canc['ride_img']);
                $ride_image = SITE_URL . 'img/ride_imgs/' . array_pop($ride_filename);
                $booking_title = str_pad($bookingdatasort_canc['booking_id'] , 5, '0', STR_PAD_LEFT);
                
                $booking_cancelled .= "<ons-list-item data-ridedesc='{$bookingdatasort_canc['ride_desc']}'  data-driverphone='{$bookingdatasort_canc['driver_phone']}' data-ptype='{$booking_payment_type}' data-put='{$booking_pdate_time}' data-driverimg='{$drvr_photo_file}' data-rideimg='{$ride_image}' data-drivername='{$booking_driver}' data-cost='{$bookingdatasort_canc['estimated_cost']}' data-ride='{$bookingdatasort_canc['ride_type']}' data-pul='{$bookingdatasort_canc['pickup_address']}' data-dol='{$bookingdatasort_canc['dropoff_address']}' data-btitle='{$booking_title}' id='booking-list-item-{$bookingdatasort_canc['booking_id']}' modifier='longdivider'>
                
                                            <div class='center'>
                                                <div style='width:100%'><span class='list-item__title'>{$booking_time}</span> | <span onclick='showbookingdetails({$bookingdatasort_canc['booking_id']})' style='color:skyblue'>View details</span></div>
                                                <span class='list-item__subtitle'><span style='color:yellow'>Booking ID:</span> {$booking_title}</span>
                                                <span class='list-item__subtitle'><span style='color:lightgreen'>Pick-up:</span> {$bookingdatasort_canc['pickup_address']}</span>
                                                <span class='list-item__subtitle'><span style='color:orange'>Drop-off:</span> {$bookingdatasort_canc['dropoff_address']}</span>
                                                <span class='list-item__subtitle'><span style='color:cyan'>Driver: </span>{$booking_driver}</span>
                                            </div>
                                        
                                        </ons-list-item>";

            }


        }







    }

    
    $data_array = array("success"=>1,'pend_onride' => $booking_pend_onride,'booking_comp'=>$booking_completed,'booking_canc'=>$booking_cancelled);    
    echo json_encode($data_array); 
    exit;






}

/* function setDriverLocation(){
    
    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please re-login and retry.");
        echo json_encode($error); 
        exit; 
    }

    $query = sprintf('UPDATE %stbl_driver_location SET `long` = "%s", `lat` = "%s", `location_date`= "%s" WHERE `driver_id` = "%d"', DB_TBL_PREFIX,$_POST['long'],$_POST['lat'],gmdate('Y-m-d H:i:s', time()),$_SESSION['uid']);
    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ //An error has occured while trying to update database record?
        $query = sprintf('INSERT INTO %stbl_driver_location (`long`,`lat`,`driver_id`,`location_date`)  VALUES("%s","%s","%d","%s")', 
            DB_TBL_PREFIX,
            $_POST['long'],
            $_POST['lat'],
            $_SESSION['uid'],
            gmdate('Y-m-d H:i:s', time())
        );

        if(!$result = mysqli_query($GLOBALS['DB'], $query)){ //An error has occured while trying to update database record?
            $feedback = array("feedback"=>"Insert Database error.");
            echo json_encode($feedback); 
            exit;
        }        
        
    }
    

    $feedback = array("feedback"=>"Location update OK.");
    echo json_encode($feedback); 
    exit;

} */


function getScheduledBookings($mode = 0){

        
    if(empty($_SESSION['loggedin'])){
        if($mode)return 0;
        $res = array("error"=>"Please re-login and retry.");
        echo json_encode($res); 
        exit;
    }


    if(empty($_SESSION['is_activated'])){ //if driver is not activated just show no data
        if($mode)return 0;
        $res = array("success"=>1,"scheduled_trips_data" => []);
        echo json_encode($res); 
        exit; 
    }


    $s_booking_data = [];

    $sbooking_redis_data = [];

    $redis = connectRedis();
    
    if($redis){
        
        $redis->zRemRangeByScore("sbknotifs:{$_SESSION['city_id']}","-inf",time());//remove bookings that have expired

        $redis_res = $redis->zRangeByScore("sbknotifs:{$_SESSION['city_id']}",time(),"+inf",['withscores'=>false]);
        if($redis_res){
            $sbooking_redis_data = $redis->mget($redis_res);
        }

    }else{
        if($mode)return 0;
        $res = array("error"=>__("An error has occured"));
        echo json_encode($res); 
        exit;
    }

    

    foreach($sbooking_redis_data as $sbk_data){                

        $row = unserialize($sbk_data);
        if(empty($row))continue;
        
        if(!empty($row['driver_id']) && $row['driver_id'] != $_SESSION['uid'])continue; //filter scheduled booking data. only allow un assigned bookings and bookings assigned to this driver
        if($row['route_id'] != $_SESSION['city_id'])continue; //filter scheduled booking data. only allow booking from the same city with driver
        $row["bk_id_formated"] = str_pad($row['bk_id'], 5, '0', STR_PAD_LEFT);

        $day_num = date('j',strtotime($row['p_time'] . ' UTC'));
        $month_name = date('F',strtotime($row['p_time'] . ' UTC'));
        $time_str = date('g:i A',strtotime($row['p_time'] . ' UTC'));
        $row['pickup_time'] = $day_num . " " . __($month_name) . ", " . $time_str;

        $day_num = date('j',strtotime($row['bk_time'] . ' UTC'));
        $month_name = date('F',strtotime($row['bk_time'] . ' UTC'));
        $time_str = date('g:i A',strtotime($row['bk_time'] . ' UTC'));
        $row['create_time'] = $day_num . " " . __($month_name) . ", " . $time_str;

        $s_booking_data[] = $row;


    }

    if($mode)return count($s_booking_data);
    

    $res = array("success"=>1,"scheduled_trips_data" => $s_booking_data);
    echo json_encode($res); 
    exit;


}




function acceptScheduledBooking(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please re-login and retry.");
        echo json_encode($error); 
        exit; 
    }
    

    $booking_id = (int) $_GET['booking_id'];

    //check if booking is still available

    $booking_data = [];

    $query = sprintf('SELECT %1$stbl_bookings.ride_id,%1$stbl_bookings.status,%1$stbl_bookings.scheduled_driver,%1$stbl_users.push_notification_token AS push_token,%1$stbl_bookings.user_id AS customer_id,%1$stbl_users.disp_lang AS u_lang FROM %1$stbl_bookings 
    INNER JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
    WHERE %1$stbl_bookings.id = "%2$d"', DB_TBL_PREFIX, $booking_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){

            $booking_data = mysqli_fetch_assoc($result);

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
    

    if($booking_data['status'] != 0){ //booking not pending. its either cancelled or serviced
        $res = array("success"=>1,"status" => 0);
        echo json_encode($res); 
        exit;
    }elseif(!empty($booking_data['scheduled_driver']) && $booking_data['scheduled_driver'] != $_SESSION['uid']){ //already accepted by another driver
        $res = array("success"=>1,"status" => 2);
        echo json_encode($res); 
        exit;
    }elseif($booking_data['ride_id'] != $_SESSION['driver_ride_id']){
        $res = array("error"=>"Your vehicle category does not match");
        echo json_encode($res); 
        exit;
    }

    $driver_account_details = [];

    //check if driver is suspended or account is not activated
    $query = sprintf('SELECT account_active,is_activated,route_id FROM %stbl_drivers WHERE driver_id = %d', DB_TBL_PREFIX, $_SESSION['uid']);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){

            $row = mysqli_fetch_assoc($result);
            $driver_account_details = $row;

            if($row['account_active'] != 1 && $row['is_activated'] != 1){
                $error = array("error"=>__("An error has occured"));
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


    
    //get user documents and check if he has any pending documents

    $driver_pending_document = 0;

    $query = sprintf('SELECT *,%1$stbl_documents.id AS d_id FROM %1$stbl_documents
    LEFT JOIN %1$stbl_users_documents ON %1$stbl_users_documents.doc_id = %1$stbl_documents.id AND %1$stbl_users_documents.u_id = %2$d AND %1$stbl_users_documents.u_type = 1
    WHERE %1$stbl_documents.doc_user = 1 AND %1$stbl_documents.status = 1', DB_TBL_PREFIX, $_SESSION['uid']);


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                if((empty($row['doc_city']) || $row['doc_city'] == $driver_account_details['route_id']) && $row['u_doc_status'] != 3){
                    $driver_pending_document = 1;
                }
            }
        }
    }

    if(!empty($driver_pending_document) && empty(DEMO)){

        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
        
    }



    //All good. Assign driver to scheduled booking after creating file lock so only one driver accepts a scheduled trip at a time

        
    $redis = connectRedis();

    if(empty($redis)){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }
    
    //update scheduled booking data on redis
    $sbooking_redis_data = $redis->get("sbkdata:{$booking_id}"); //get scheduled booking data from redis
    $sb_redis_data = unserialize($sbooking_redis_data);
    if(empty($sb_redis_data)){ //booking data not available
        $res = array("success"=>1,"status" => 0);
        echo json_encode($res); 
        exit;
    }


    $file_bk_loc = @fopen( __DIR__ . "/b_locks/bloc-{$booking_id}.loc", "w");


    if($file_bk_loc){

        $file_loc = flock($file_bk_loc, LOCK_EX | LOCK_NB);

        if ($file_loc === false ) {
            $res = array("success"=>1,"status" => 0);
            echo json_encode($res); 
            exit;        
        }

    }


    $sb_redis_data['driver_id'] = $_SESSION['uid'];

    $bk_data_ttl =  abs(strtotime($sb_redis_data['p_time'] . " UTC") - time()) + 86400;
    $redis->setEx("sbkdata:{$booking_id}", $bk_data_ttl ,serialize($sb_redis_data)); //store data 

    //update database
    $query = sprintf('UPDATE %stbl_bookings SET scheduled_driver = %d WHERE id = "%d"', 
        DB_TBL_PREFIX,
        $_SESSION['uid'],
        $booking_id        
     );

    if($file_bk_loc){
        fclose($file_bk_loc); //release locked file
    }

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ 
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

    //send push notification to the rider that a driver has been assigned to his scheduled trip
    $ext_msg_data = array(
        'mode' => 1,
        'title' => __("Scheduled Trip",[],"d|{$booking_data['u_lang']}"),
        'message' => preg_replace("/\"/", '&#039;', htmlspecialchars(__("A driver has been assigned to your scheduled booking. View more details about this booking on the trips page",[],"d|{$booking_data['u_lang']}")))
    );       
    
    
    sendPushNotification(__("Scheduled Trip",[],"d|{$booking_data['u_lang']}"), __("A driver has been assigned to your scheduled booking. View more details about this booking on the trips page",[],"d|{$booking_data['u_lang']}"),$booking_data['push_token'],null,json_encode($ext_msg_data),null,null);

    $res = array("success"=>1,"status" => 1);
    echo json_encode($res); 
    exit; 

}



function setDriverLocationbg(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please re-login and retry.");
        echo json_encode($error); 
        exit; 
    }

    $bg_location_data_json = file_get_contents("php://input");

    
    //file_put_contents('loc.txt',print_r($bg_location_data_json . "---time: " . time(), true));

    $bg_location_data_arr = json_decode($bg_location_data_json, true);

    if(json_last_error())exit;

    updateDriverLocation($bg_location_data_arr[0]['lat'],$bg_location_data_arr[0]['long']); 

}



function setDriverLocation(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please re-login and retry.");
        echo json_encode($error); 
        exit; 
    }

    
    updateDriverLocation($_GET['lat'],$_GET['long']); 

}





function updateDriverLocation($driver_lat,$driver_long){

    if(isset($_SESSION['driver_locations'])){

        $_SESSION['driver_locations']['old']['lat'] = $_SESSION['driver_locations']['cur']['lat'];
        $_SESSION['driver_locations']['old']['lng'] = $_SESSION['driver_locations']['cur']['lng'];

        $_SESSION['driver_locations']['cur']['lat'] = $driver_lat;
        $_SESSION['driver_locations']['cur']['lng'] = $driver_long;

        if($_SESSION['driver_locations']['old']['lat'] != 0 &&  $_SESSION['driver_locations']['old']['lng'] != 0){
            $bearing = getBearing($_SESSION['driver_locations']['old']['lat'],$_SESSION['driver_locations']['old']['lng'],$_SESSION['driver_locations']['cur']['lat'],$_SESSION['driver_locations']['cur']['lng']);

            if($bearing > 0){
                $_SESSION['driver_locations']['bearing'] = $bearing;
            }

        }

        
    }else{
        $_SESSION['driver_locations']['cur']['lat'] = $driver_lat;
        $_SESSION['driver_locations']['cur']['lng'] = $driver_long;

        $_SESSION['driver_locations']['old']['lat'] = 0;
        $_SESSION['driver_locations']['old']['lng'] = 0;

        $_SESSION['driver_locations']['bearing'] = 0.0;


    }


    $b_angle = $_SESSION['driver_locations']['bearing'];   
    

    //*************Store driver location data to redis **********/
    $redis = connectRedis();
    
    if($redis){
        //successfully connected to redis

        $driver_pos_data = [
            'driver_id' => $_SESSION['uid'],
            'ride_id' => $_SESSION['driver_ride_id'],
            'icon_type' => $_SESSION['driver_ride_icon'],
            'firstname'=>$_SESSION['firstname'],
            'lastname'=>$_SESSION['lastname'],
            'lat' =>$driver_lat,
            'long' => $driver_long,
            'b_angle' => $b_angle,
            'ride_type' => $_SESSION['carcat'],
            'upd_time' => time()
        ];
        $redis->set("drvpos:{$_SESSION['uid']}", serialize($driver_pos_data)); //store drivers latlong data as redis key string
        if(!empty($_SESSION['availability'])){
            $redis->zAdd("drvs_online:{$_SESSION['city_id']}",time(),"drvpos:{$_SESSION['uid']}"); //add driver to online driver sorted set with score of  time() in seconds. will be used to filter online drivers for this city 
        }else{
            $redis->zRem("drvs_online:{$_SESSION['city_id']}", "drvpos:{$_SESSION['uid']}"); //remove member from redis sorted list when offline
        }
        
        
        if(isset($_SESSION['last_route_upt']['time'])){
                
            if((time() - $_SESSION['last_route_upt']['time']) > 10){
                
                
                $distance_change = distance($driver_lat,$driver_long,$_SESSION['last_route_upt']['lat'],$_SESSION['last_route_upt']['lng']);

                $distance_change = 1;

                $driver_active_bookings = $redis->hGetAll("drvr_active_booking:{$_SESSION['uid']}");
                

                if(!empty($driver_active_bookings) && $distance_change > 0.01){

                    foreach($driver_active_bookings as $key => $val){
                        if((time() - $val) >  7200){

                            //stop and save travel data if recording time is greater than two hours
                            saveTravelRouteData($key, $_SESSION['uid']);
                            continue;                 
                        }

                        $drv_coord_data = $driver_lat . "|" . $driver_long . "|" . time();
                        $redis->zAdd("drv_route:{$_SESSION['uid']}:{$key}",time(),$drv_coord_data);
                        
                    }

                }
                
                $_SESSION['last_route_upt']['time'] = time();
                $_SESSION['last_route_upt']['lat'] = $driver_lat;
                $_SESSION['last_route_upt']['lng'] = $driver_long;

            }
        }else{
            $_SESSION['last_route_upt']['time'] = time();
            $_SESSION['last_route_upt']['lat'] = $driver_lat;
            $_SESSION['last_route_upt']['lng'] = $driver_long;
        }
            

    }


    //*************Redis end **********/

    

    //fallback to database. update database every 30 seconds
    if(isset($_SESSION['last_location_update'])){
        if(time() - $_SESSION['last_location_update'] < 30){
            $feedback = array("feedback"=>"Location update OK.");
            echo json_encode($feedback); 
            exit;
        }
    }

    $_SESSION['last_location_update'] = time() + rand(10,30);

    $mysql_db = connectMysqlDB();

    if(!$mysql_db){
        $feedback = array("feedback"=>"Insert Database error.");
        echo json_encode($feedback); 
        exit;
    }
    
    

    //check if row to be updated is present
    $query = sprintf('SELECT * FROM %stbl_driver_location WHERE driver_id = "%d"', DB_TBL_PREFIX,$_SESSION['uid'] );
    $result = mysqli_query($GLOBALS['DB'], $query);
    if(empty(mysqli_num_rows($result))){
         $query = sprintf('INSERT INTO %stbl_driver_location (`long`,`lat`,`driver_id`,`location_date`,`b_angle`)  VALUES("%s","%s","%d","%s","%s")', 
            DB_TBL_PREFIX,
            $driver_long,
            $driver_lat,
            $_SESSION['uid'],
            gmdate('Y-m-d H:i:s', time()),
            $b_angle
        );

        if(!$result = mysqli_query($GLOBALS['DB'], $query)){ //An error has occured while trying to update database record?
            $feedback = array("feedback"=>"Insert Database error.");
            echo json_encode($feedback); 
            exit;
        }else{
            $feedback = array("feedback"=>"Location update OK.");
            echo json_encode($feedback); 
            exit;
        }
        
    }else{
        //update driver location
        $query = sprintf('UPDATE %stbl_driver_location SET `long` = "%s", `lat` = "%s", `location_date`= "%s", `b_angle` = "%s" WHERE `driver_id` = "%d"', DB_TBL_PREFIX,$driver_long,$driver_lat,gmdate('Y-m-d H:i:s', time()),$b_angle,$_SESSION['uid']);
        if(!$result = mysqli_query($GLOBALS['DB'], $query)){ //An error has occured while trying to update database record?
            $feedback = array("feedback"=>"Update Database error.");
            echo json_encode($feedback); 
            exit;
        }   

        $feedback = array("feedback"=>"Location update OK.");
        echo json_encode($feedback); 
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



function getEarnings(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }
    

    

    
    //get wallet data 
    $template = '';
    $earnings_data = [];
    $week_earnings_data = [];
    $earnings_date = '';
    $end_date = '';
    $driver_all_day_earning = 0.00;
    $driver_city_currency_symbol = '';
    $driver_city_currency_exchange = 0.00;
    $sel_date = $_POST['sel_date'];

    if(!empty($_POST['data'])){

        $query_data = $_POST['data'];

        if(!isValidDate("{$query_data['start_year']}-{$query_data['start_month']}-{$query_data['start_date']}",'Y-m-d')){
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); 
            exit;
        }

        if(!isValidDate($sel_date, 'Y-m-d')){
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); 
            exit;
        }

        //$sel_date = gmdate('Y-m-d', strtotime($sel_date));
        $start_date = "{$query_data['start_year']}-{$query_data['start_month']}-{$query_data['start_date']}"; //gmdate('Y-m-d', strtotime("{$query_data['start_year']}-{$query_data['start_month']}-{$query_data['start_date']}"));
        $end_date = "{$query_data['end_year']}-{$query_data['end_month']}-{$query_data['end_date']}"; //gmdate('Y-m-d', strtotime("{$query_data['end_year']}-{$query_data['end_month']}-{$query_data['end_date']}"));

                         

        
    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
        
    }

    //pre-compute all week days data
    $query = sprintf('SELECT %1$stbl_currencies.symbol,%1$stbl_currencies.exchng_rate,%1$stbl_bookings.actual_cost,%1$stbl_bookings.paid_amount,%1$stbl_bookings.cur_exchng_rate,%1$stbl_bookings.driver_commision AS driver_com,%1$stbl_bookings.date_completed,%1$stbl_bookings.date_completed AS completed_date FROM %1$stbl_bookings
    LEFT JOIN %1$stbl_rides ON %1$stbl_rides.id = %1$stbl_bookings.ride_id
    INNER JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id
    LEFT JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_drivers.route_id
    LEFT JOIN %1$stbl_currencies ON %1$stbl_currencies.id = %1$stbl_routes.city_currency_id
    WHERE %1$stbl_bookings.driver_id = %2$d AND %1$stbl_bookings.status = 3 AND (DATE(%1$stbl_bookings.date_completed) >= "%3$s" AND DATE(%1$stbl_bookings.date_completed) <= "%4$s")', DB_TBL_PREFIX,$_SESSION['uid'],$start_date,$end_date);

    $query2 = $query;
    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                $day_str = date('Y-m-d', strtotime($row['date_completed'] . ' UTC'));
                $day = explode('-',$day_str);

                $driver_city_currency_symbol = $row['symbol'];
                $driver_city_currency_exchange = $row['exchng_rate'];
                
                $driver_earning = ($row['actual_cost'] * $row['driver_com']) / 100;
                $driver_earning = round($driver_earning,2);
                $deficit = $row['paid_amount'] - $row['actual_cost'];
                $company_commision = ($row['actual_cost'] - $driver_earning) + $deficit;
                $company_commision = round($company_commision,2);
                
                if(!isset($week_earnings_data[$day[2]])){
                    $week_earnings_data[$day[2]] = [];
                    $week_earnings_data[$day[2]]['num_rides'] = 1;
                    $week_earnings_data[$day[2]]['earning'] = $driver_earning;
                }else{
                    $week_earnings_data[$day[2]]['num_rides'] += 1;
                    $week_earnings_data[$day[2]]['earning'] += $driver_earning;
                }
                    

            }

            //loop through and get date with maximum earning
            $max_earned = 0;
            foreach($week_earnings_data as $weekearningsdata){
                if($weekearningsdata['earning'] > $max_earned){
                    $max_earned = $weekearningsdata['earning'];
                }
            }

            $week_earnings_data['max_earning'] = $max_earned;
                            
        }    
    }

    //Get all bookings records for the selected date 

    $query = sprintf('SELECT *,%1$stbl_users.firstname AS user_firstname,%1$stbl_users.photo_file AS user_photo_file,%1$stbl_bookings.id AS book_id,%1$stbl_bookings.driver_commision AS driver_com,%1$stbl_bookings.date_completed AS completed_date FROM %1$stbl_bookings
    LEFT JOIN %1$stbl_rides ON %1$stbl_rides.id = %1$stbl_bookings.ride_id
    LEFT JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
    INNER JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id
    LEFT JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_drivers.route_id
    LEFT JOIN %1$stbl_currencies ON %1$stbl_currencies.id = %1$stbl_routes.city_currency_id
    WHERE %1$stbl_bookings.driver_id = %2$d AND %1$stbl_bookings.status = 3 AND DATE(%1$stbl_bookings.date_completed) = "%3$s" ORDER BY %1$stbl_bookings.date_completed DESC', DB_TBL_PREFIX,$_SESSION['uid'],$sel_date);
    
    


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                $row['dropoff_address'] = !empty($row['dropoff_address']) ? $row['dropoff_address'] : __('Destination not specified');
                $earnings_data[] = $row;
            }
                            
        }    
    }


    mysqli_close($GLOBALS['DB']);

        

    //sort data by date
    $earnings_data_sort = [];
    

    for($x = 0;$x < 24;$x++){
        $earnings_raw_data[$x] = 0;
    }

    foreach($earnings_data as $earningsdata){
        $sort_date = date('Y-m-d', strtotime($earningsdata['completed_date'] . ' UTC'));
        $earnings_data_sort[$sort_date]['date'] = $earningsdata['date_completed'];
        $earnings_data_sort[$sort_date]['data'][] = $earningsdata;
        
    }

    //format for display on app
    foreach($earnings_data_sort as $earningsdatasort){

        if(!empty($earningsdatasort['data'])){ 
                $t_date_format = date('l, M j, Y H:i:s',strtotime($earningsdatasort['date'] . " UTC"));
                //$template .= "<ons-list-header>{$t_date_format}</ons-list-header>";
    
                foreach($earningsdatasort['data'] as $transaction_d){
                    $transaction_time = date('g:i A',strtotime($transaction_d['date_completed'] . " UTC"));
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

                    
                    //compute driver earning

                    $driver_city_currency_symbol = $transaction_d['symbol'];
                    $driver_city_currency_exchange = $transaction_d['exchng_rate'];
                    
                    $driver_earning = ($transaction_d['actual_cost'] * $transaction_d['driver_com']) / 100;
                    $driver_earning = round($driver_earning,2);
                    $deficit = $transaction_d['paid_amount'] - $transaction_d['actual_cost'];
                    $company_commision = ($transaction_d['actual_cost'] - $driver_earning) + $deficit;
                    $company_commision = round($company_commision,2);
                    $driver_all_day_earning += $driver_earning / $transaction_d['cur_exchng_rate']; //convert default base currency
                    
                                        
                    $booking_id = !empty($transaction_d['book_id']) ? "#".str_pad($transaction_d['book_id'] , 5, '0', STR_PAD_LEFT) : "N/A";
                    $booking_payment_type = '';
                    if(!empty($transaction_d['payment_type'])){
                
                        if($transaction_d['payment_type'] == 1){
                            $booking_payment_type = "Cash";
                        }elseif($transaction_d['payment_type'] == 2){
                            $booking_payment_type = "Wallet";
                        }else{
                            $booking_payment_type = "Card";
                        }
        
                    }

                    //create full data json
                    $day_num = date('j',strtotime($transaction_d['pickup_datetime'] . " UTC"));
                    $month_name = date('F',strtotime($transaction_d['pickup_datetime'] . " UTC"));
                    $time_str = date('g:i A',strtotime($transaction_d['pickup_datetime'] . " UTC"));
                    $booking_pdate_time = $day_num . " " . __($month_name) . ", " . $time_str;
                    
                    $booking_type = $transaction_d['scheduled'] == 1 ? 'Schedule ride' : 'Instant ride';
                    //$user_photo = explode('/',$transaction_d['user_photo_file']);
                    $user_photo_file = isset($transaction_d['user_photo_file']) ? SITE_URL . "ajaxuserphotofile.php?file=".$transaction_d['user_photo_file'] : SITE_URL . "ajaxuserphotofile.php?file=0";
                    $ride_filename = explode('/',$transaction_d['ride_img']);
                    $ride_image = SITE_URL . 'img/ride_imgs/' . array_pop($ride_filename);
                    $ride_duration = '0 Secs';
                    $distance_travelled = !empty($transaction_d['dist_unit']) ? round(($transaction_d['distance_travelled'] * 0.000621371),2) . " mi" : round(($transaction_d['distance_travelled'] * 0.001),2) . " KM";
                    if(!empty($transaction_d['date_started']) && !empty($transaction_d['date_completed'])){
                        $ride_start_time = strtotime($transaction_d['date_started']);
                        $ride_end_time = strtotime($transaction_d['date_completed']);
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
                                        'car_desc'=> $transaction_d['ride_desc'],
                                        'user_firstname' => $transaction_d['user_firstname'],
                                        'user_rating' => $transaction_d['user_rating'],
                                        'payment_type' => $booking_payment_type,
                                        'pick_up_time'=> $booking_pdate_time,
                                        'user_image' => $user_photo_file,
                                        'car_image' => $ride_image,
                                        'driver_earning' => $transaction_d['cur_symbol'].number_format((float) $driver_earning,2),
                                        'booking_cost' => $transaction_d['cur_symbol']. number_format((float) $transaction_d['actual_cost'],2),
                                        'car_type' => $transaction_d['ride_type'],
                                        'p_location' => $transaction_d['pickup_address'],
                                        'd_location' => $transaction_d['dropoff_address'],
                                        'booking_id' => $booking_id,
                                        'booking_type' => $booking_type,
                                        'booking_status' => $transaction_d['status'],
                                        'coupon_code' => $transaction_d['coupon_code'],
                                        'distance_travelled' => $distance_travelled,
                                        'ride_duration' => $ride_duration,
                                        'p_lat' => $transaction_d['pickup_lat'],
                                        'p_lng' => $transaction_d['pickup_long'],
                                        'd_lat' => $transaction_d['dropoff_lat'],
                                        'd_lng' => $transaction_d['dropoff_long'],
                                        'service_mode' => $transaction_d['service_mode'],
                                        'coupon_discount_value' => $transaction_d['coupon_discount_value'],
                                        'coupon_discount_type' => $transaction_d['coupon_discount_type'],
                                        'actual_cost' => $transaction_d['actual_cost'],
                                        'paid_amount' => $transaction_d['paid_amount'],
                                        'referral_used' => $transaction_d['referral_used'],
                                        'referral_discount_value' => $transaction_d['referral_discount_value']
                                    );

                    $item_data_json = json_encode($item_data);

                    $driver_earning_formatted = floattocurrency($driver_earning);
                    $company_commision_formatted = floattocurrency($company_commision);

                    $template .= "<ons-list-item onclick='showbookingdetails({$transaction_d['book_id']})' modifier='longdivider'>
                        
                                    <div class='center'>
                                        <div style='width:100%;margin-bottom:10px;'><span class='list-item__title'>{$transaction_time}</span> </div>
                                        <span class='list-item__subtitle'><span style='color: #000;font-size: 20px;margin-bottom:5px;'>".__("Earning").": {$transaction_d['cur_symbol']}{$driver_earning_formatted}</span></span>
                                        <span class='list-item__subtitle'><ons-icon icon='fa-square' size='8px' style='vertical-align: bottom; color: black; font-size: 8px;' modifier='material' class='ons-icon fa-square fa'></ons-icon> <span style='color:#777'>".__("Booking ID").":</span> {$booking_id} </span>
                                        <span class='list-item__subtitle'><ons-icon icon='fa-square' size='8px' style='vertical-align: bottom; color: black; font-size: 8px;' modifier='material' class='ons-icon fa-square fa'></ons-icon> <span style='color:#777'>".__("Booking Fare").":</span> {$transaction_d['cur_symbol']}{$transaction_d['paid_amount']} </span>
                                        <span class='list-item__subtitle'><ons-icon icon='fa-square' size='8px' style='vertical-align: bottom; color: black; font-size: 8px;' modifier='material' class='ons-icon fa-square fa'></ons-icon> <span style='color:#777'>".__("Company commision").":</span> {$transaction_d['cur_symbol']}{$company_commision_formatted}</span>
                                        <span id='booking-list-item-data-{$transaction_d['book_id']}' type='text' style='display:none'>{$item_data_json}</span>
                                    </div>
                                
                                </ons-list-item>";
                }
    
        }

    }
                                    
    $formatted_all_day_earning = '';
    if(empty($earnings_data)){
        $template = "<p style='color:grey;text-align:center;position:absolute;top:50%;width:100%;transform: translateY(-50%);'>".__("No record available")."</p>";                            
        
    }else{
        $formatted_all_day_earning = $driver_city_currency_symbol.floattocurrency($driver_all_day_earning * $driver_city_currency_exchange);
    }

    $data_array = array("success"=>1,'earnings_data'=>$template,'all_day_earning'=>$formatted_all_day_earning,'week_data' => $week_earnings_data, 'dd' =>$query2);    
    echo json_encode($data_array); 
    exit;


}



function getDriverHistory(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $booking_completed = [];
    $booking_cancelled = [];
    $booking_pending = [];

    $query = sprintf('SELECT *, %1$stbl_bookings.date_created AS created_date,%1$stbl_bookings.id AS booking_id FROM %1$stbl_bookings 
    LEFT JOIN %1$stbl_rides ON %1$stbl_rides.id = %1$stbl_bookings.ride_id
    LEFT JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
    WHERE %1$stbl_bookings.driver_id = %2$s AND (%1$stbl_bookings.status = 0 OR %1$stbl_bookings.status = 1 OR %1$stbl_bookings.status = 2 OR %1$stbl_bookings.status = 3 OR %1$stbl_bookings.status = 4 OR %1$stbl_bookings.status = 5) 
    ORDER BY %1$stbl_bookings.date_created DESC LIMIT 0,500 ', DB_TBL_PREFIX,$_SESSION['uid']);


    if($result = mysqli_query($GLOBALS['DB'], $query)){

        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){

                $row['dropoff_address'] = !empty($row['dropoff_address']) ? $row['dropoff_address'] : __('Destination not specified');

                $sort_date = date('Y-m-d',strtotime($row['created_date'] . ' UTC'));

                if($row['status'] == 0 || $row['status'] == 1){     //pending or onride
                    $booking_pending[$sort_date]['date'] = $row['created_date'];
                    $booking_pending[$sort_date]['pending'][] = $row;
                }
                if($row['status'] == 2 || $row['status'] == 4 || $row['status'] == 5){     //cancelled
                    $booking_cancelled[$sort_date]['date'] = $row['created_date'];
                    $booking_cancelled[$sort_date]['cancelled'][] = $row;
                }
                if($row['status'] == 3){     //Completed rides
                    $booking_completed[$sort_date]['date'] = $row['created_date'];
                    $booking_completed[$sort_date]['completed'][] = $row;
                }
             }
            //  echo json_encode($booking_cancelled); 
            mysqli_free_result($result);
        }else{

            $error = array("error"=>__("You do not have any booking records"));
            echo json_encode($error); 
            exit; 

        }
    }else{

        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit; 

    }

   
    //format for display on app
    $booking_complete_str = '';
    $booking_cancel_str = '';
    $booking_pending_str = '';

    foreach($booking_pending as $bookingdatasort){
      //save date
        $day_num = date('j',strtotime($bookingdatasort['date'] . " UTC"));
        $month_name = date('F',strtotime($bookingdatasort['date'] . " UTC"));
        $b_date_format = $day_num . " " . __($month_name);
        $booking_cancel_str .= "<ons-list-header style='font-size: 14px;font-weight:bold;margin:20px 0;'>{$b_date_format}</ons-list-header>";
        
        //format pending onride rides for this date
        foreach($bookingdatasort['pending'] as $bookingdatasort_canc){
            $booking_time = date('g:i A',strtotime($bookingdatasort_canc['date_created'] . ' UTC'));
            $booking_ptime = date('g:i A',strtotime($bookingdatasort_canc['pickup_datetime'] . ' UTC'));

            $day_num = date('j',strtotime($bookingdatasort_canc['pickup_datetime'] . " UTC"));
            $month_name = date('F',strtotime($bookingdatasort_canc['pickup_datetime'] . " UTC"));
            $time_str = date('g:i A',strtotime($bookingdatasort_canc['pickup_datetime'] . " UTC"));
            $booking_pdate_time = $day_num . " " . __($month_name) . ", " . $time_str;

            $booking_payment_type = '';
            $status = '';
            $close_btn = '';

                        
            if($bookingdatasort_canc['status'] == 0){
                $status = "<span style='color: #ef6c00;font-weight: bold;border-radius:5px;border: thin solid #ef6c00;padding: 3px 5px;font-size: 12px;'>".__("Booking accepted")."</span>";
                $close_btn = "<span style='display:inline-block;float:right'><ons-icon onclick = 'event.stopPropagation();drivercancel({$bookingdatasort_canc['booking_id']})' icon='fa-times-circle' size='20px' style='color:red'></ons-icon></span>";
            }else{
                $status = "<span style='color: #43a047;font-weight: bold;border-radius:5px;border: thin solid #43a047;padding: 3px 5px;font-size: 12px;'>".__("Servicing booking")."</span>";
                $close_btn = "<span style='display:inline-block;float:right'><ons-icon onclick = 'event.stopPropagation();drivercancel({$bookingdatasort_canc['booking_id']})' icon='fa-times-circle' size='20px' style='color:red'></ons-icon></span>";
            }

            if(!empty($bookingdatasort_canc['payment_type'])){
                
                if($bookingdatasort_canc['payment_type'] == 1){
                    $booking_payment_type = __("Cash");
                }elseif($bookingdatasort_canc['payment_type'] == 2){
                    $booking_payment_type = __("Wallet");
                }else{
                    $booking_payment_type = "Card";
                }

            }
            
            $booking_type = $bookingdatasort_canc['scheduled'] == 1 ? 'Schedule ride' : 'Instant ride';
            //$user_photo = explode('/',$bookingdatasort_canc['photo_file']);
            $user_photo_file = isset($bookingdatasort_canc['photo_file']) ? SITE_URL . "ajaxuserphotofile.php?file=".$bookingdatasort_canc['photo_file'] : SITE_URL . "ajaxuserphotofile.php?file=0";
            $ride_filename = explode('/',$bookingdatasort_canc['ride_img']);
            $ride_image = SITE_URL . 'img/ride_imgs/' . array_pop($ride_filename);
            $booking_title = str_pad($bookingdatasort_canc['booking_id'] , 5, '0', STR_PAD_LEFT);
            $item_data = [];
            $item_data = array(
                                'car_desc'=> $bookingdatasort_canc['ride_desc'],
                                'user_firstname' => $bookingdatasort_canc['firstname'],
                                'user_rating' => $bookingdatasort_canc['user_rating'],
                                'payment_type' => $booking_payment_type,
                                'pick_up_time'=> $booking_pdate_time,
                                'user_image' => $user_photo_file,
                                'car_image' => $ride_image,
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
            $booking_pending_str .= "<ons-list-item onclick='showbookingdetails({$bookingdatasort_canc['booking_id']})' id='booking-list-item-{$bookingdatasort_canc['booking_id']}' modifier='longdivider'>
            
                                            <div class='center'>
                                                <div style='width:100%;'><span class='list-item__title'>{$booking_time} </span> {$status} {$close_btn}</div>
                                                <span style='text-align: left;margin-bottom: 15px;margin-top: 10px;color:#777;font-size:14px;'><span>ID:#{$booking_title}</span></span>  
                                                <div style='box-sizing: border-box;width: 100%;overflow: hidden;background-color: white;padding: 10px 10px;'>
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
                                                <span class='list-item__subtitle' id='resume-bk-{$bookingdatasort_canc['booking_id']}' style='display:none;'><ons-button style='width:100%' onclick='event.stopPropagation();resumeBooking({$bookingdatasort_canc['booking_id']})'> Resume </ons-button></span>
                                            </div>
                                            
                                                                               
                                    </ons-list-item>";
                //<br><span class='list-item__subtitle'><span style='color:cyan'>Payment Type: </span>{$booking_payment_type}</span>
                                         
        }

    }

    foreach($booking_cancelled as $bookingdatasort){
      //save date
        $day_num = date('j',strtotime($bookingdatasort['date'] . " UTC"));
        $month_name = date('F',strtotime($bookingdatasort['date'] . " UTC"));
        $b_date_format = $day_num . " " . __($month_name);
        $booking_cancel_str .= "<ons-list-header style='font-size: 14px;font-weight:bold;margin:20px 0;'>{$b_date_format}</ons-list-header>";
        
        //format pending onride rides for this date
        foreach($bookingdatasort['cancelled'] as $bookingdatasort_canc){
            $booking_time = date('g:i A',strtotime($bookingdatasort_canc['date_created'] . ' UTC'));
            $booking_ptime = date('g:i A',strtotime($bookingdatasort_canc['pickup_datetime'] . ' UTC'));

            $day_num = date('j',strtotime($bookingdatasort_canc['pickup_datetime'] . " UTC"));
            $month_name = date('F',strtotime($bookingdatasort_canc['pickup_datetime'] . " UTC"));
            $time_str = date('g:i A',strtotime($bookingdatasort_canc['pickup_datetime'] . " UTC"));
            $booking_pdate_time = $day_num . " " . __($month_name) . ", " . $time_str;
            $booking_payment_type = '';
            $status = '';
            if($bookingdatasort_canc['status'] == 2){
                $status = "<span style='color: #e53935;font-weight: bold;border-radius:5px;border-radius:5px;border: thin solid #e53935;padding: 3px 5px;font-size: 12px;'>".__("Cancelled by rider")."</span>";
            }elseif($bookingdatasort_canc['status'] == 4){
                $status = "<span style='color: #e53935;font-weight: bold;border-radius:5px;border-radius:5px;border: thin solid #e53935;padding: 3px 5px;font-size: 12px;'>".__("Cancelled by driver")."</span>";
            }else{
                $status = "<span style='color: #e53935;font-weight: bold;border-radius:5px;border-radius:5px;border: thin solid #e53935;padding: 3px 5px;font-size: 12px;'>".__("Cancelled by Admin")."</span>";
            }           
            if(!empty($bookingdatasort_canc['payment_type'])){
                
                if($bookingdatasort_canc['payment_type'] == 1){
                    $booking_payment_type = __("Cash");
                }elseif($bookingdatasort_canc['payment_type'] == 2){
                    $booking_payment_type = __("Wallet");
                }else{
                    $booking_payment_type = "Card";
                }

            }
            
            $booking_type = $bookingdatasort_canc['scheduled'] == 1 ? 'Schedule ride' : 'Instant ride';
            //$user_photo = explode('/',$bookingdatasort_canc['photo_file']);
            $user_photo_file = isset($bookingdatasort_canc['photo_file']) ? SITE_URL . "ajaxuserphotofile.php?file=".$bookingdatasort_canc['photo_file'] : SITE_URL . "ajaxuserphotofile.php?file=0";
            $ride_filename = explode('/',$bookingdatasort_canc['ride_img']);
            $ride_image = SITE_URL . 'img/ride_imgs/' . array_pop($ride_filename);
            $booking_title = str_pad($bookingdatasort_canc['booking_id'] , 5, '0', STR_PAD_LEFT);
            $item_data = [];
            $item_data = array(
                                'car_desc'=> $bookingdatasort_canc['ride_desc'],
                                'user_firstname' => $bookingdatasort_canc['firstname'],
                                'user_rating' => $bookingdatasort_canc['user_rating'],
                                'payment_type' => $booking_payment_type,
                                'pick_up_time'=> $booking_pdate_time,
                                'user_image' => $user_photo_file,
                                'car_image' => $ride_image,
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
            $booking_cancel_str .= "<ons-list-item onclick='showbookingdetails({$bookingdatasort_canc['booking_id']})' id='booking-list-item-{$bookingdatasort_canc['booking_id']}' modifier='longdivider'>
            
                                        <div class='center'>
                                            <div style='width:100%;'><span class='list-item__title'>{$booking_time} </span> {$status}</div>
                                                <span style='text-align: left;margin-bottom: 15px;margin-top: 10px;color:#777;font-size:14px;'><span>ID:#{$booking_title}</span></span>  
                                                <div style='box-sizing: border-box;width: 100%;overflow: hidden;background-color: white;padding: 10px 10px;'>
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
                                        </div>
                                    </ons-list-item>";
                //<br><span class='list-item__subtitle'><span style='color:cyan'>Payment Type: </span>{$booking_payment_type}</span>
                                         
        }

    }
    
    foreach($booking_completed as $bookingdatasort){
        //save date
            $day_num = date('j',strtotime($bookingdatasort['date'] . " UTC"));
            $month_name = date('F',strtotime($bookingdatasort['date'] . " UTC"));
            $b_date_format = $day_num . " " . __($month_name);
          $booking_complete_str .= "<ons-list-header style='font-size: 14px;font-weight:bold;margin:20px 0;'>{$b_date_format}</ons-list-header>";
          
          //format pending onride rides for this date
          foreach($bookingdatasort['completed'] as $bookingdatasort_canc){
              $booking_time = date('g:i A',strtotime($bookingdatasort_canc['date_created'] . ' UTC'));
              $booking_ptime = date('g:i A',strtotime($bookingdatasort_canc['pickup_datetime'] . ' UTC'));

              $day_num = date('j',strtotime($bookingdatasort_canc['pickup_datetime'] . " UTC"));
                $month_name = date('F',strtotime($bookingdatasort_canc['pickup_datetime'] . " UTC"));
                $time_str = date('g:i A',strtotime($bookingdatasort_canc['pickup_datetime'] . " UTC"));
                $booking_pdate_time = $day_num . " " . __($month_name) . ", " . $time_str;

              $booking_payment_type = '';
              if(!empty($bookingdatasort_canc['payment_type'])){
                  if($bookingdatasort_canc['payment_type'] == 2){
                      $booking_payment_type = __("Wallet");
                  }
                  if($bookingdatasort_canc['payment_type'] == 1){
                      $booking_payment_type = __("Cash");
                  }
                  if($bookingdatasort_canc['payment_type'] == 4){
                    $booking_payment_type = "POS";
                    }
              }
            
            $distance_travelled = !empty($_SESSION['driver_city_dist_unit']) ? round(($bookingdatasort_canc['distance_travelled'] * 0.000621371),2) . " mi" : round(($bookingdatasort_canc['distance_travelled'] * 0.001),2) . " KM";
            $booking_type = $bookingdatasort_canc['scheduled'] == 1 ? 'Schedule ride' : 'Instant ride';
            //$user_photo = explode('/',$bookingdatasort_canc['photo_file']);
            $user_photo_file = isset($bookingdatasort_canc['photo_file']) ? SITE_URL . "ajaxuserphotofile.php?file=".$bookingdatasort_canc['photo_file'] : SITE_URL . "ajaxuserphotofile.php?file=0";
            $ride_filename = explode('/',$bookingdatasort_canc['ride_img']);
            $ride_image = SITE_URL . 'img/ride_imgs/' . array_pop($ride_filename);
            $booking_title = str_pad($bookingdatasort_canc['booking_id'] , 5, '0', STR_PAD_LEFT);
            $ride_duration = '0 Secs';

            if(!empty($bookingdatasort_canc['date_started']) && !empty($bookingdatasort_canc['date_completed'])){
                $ride_start_time = strtotime($bookingdatasort_canc['date_started'] . ' UTC');
                $ride_end_time = strtotime($bookingdatasort_canc['date_completed'] . ' UTC');
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
                                'car_desc'=> $bookingdatasort_canc['ride_desc'],
                                'user_firstname' => $bookingdatasort_canc['firstname'],
                                'user_rating' => $bookingdatasort_canc['user_rating'],
                                'payment_type' => $booking_payment_type,
                                'pick_up_time'=> $booking_pdate_time,
                                'user_image' => $user_photo_file,
                                'car_image' => $ride_image,
                                'booking_cost' => $bookingdatasort_canc['cur_symbol'].$bookingdatasort_canc['estimated_cost'],
                                'car_type' => $bookingdatasort_canc['ride_type'],
                                'p_location' => $bookingdatasort_canc['pickup_address'],
                                'd_location' => $bookingdatasort_canc['dropoff_address'],
                                'booking_id' => $booking_title,
                                'booking_type' => $booking_type,
                                'booking_status' => $bookingdatasort_canc['status'],
                                'coupon_code' => $bookingdatasort_canc['coupon_code'],
                                'distance_travelled' => $distance_travelled,
                                'ride_duration' => $ride_duration,
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
              $booking_fare = $bookingdatasort_canc['cur_symbol'].$bookingdatasort_canc['paid_amount'];
              $booking_title = str_pad($bookingdatasort_canc['booking_id'] , 5, '0', STR_PAD_LEFT);
              $booking_complete_str .= "<ons-list-item onclick='showbookingdetails({$bookingdatasort_canc['booking_id']})' id='booking-list-item-{$bookingdatasort_canc['booking_id']}' modifier='longdivider'>
            
                                            <div class='center'>
                                                <div style='width:100%;'><span class='list-item__title'>{$booking_time} </span> <span style='color: #1976d2;border-radius:5px;font-weight: bold;border: thin solid #1976d2;padding: 3px 5px;font-size: 12px;'>" . __('Completed') . "</span></div>
                                                    <span style='text-align: left;margin-bottom: 15px;margin-top: 10px;color:#777;font-size:14px;'><span>ID:#{$booking_title}</span></span>  
                                                    <div style='box-sizing: border-box;width: 100%;overflow: hidden;background-color: white;padding: 10px 10px;'>
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
                                            </div>
                                        </ons-list-item>";
  
          }
  
    }

    $data_array = array("success"=>1,'booking_comp'=>$booking_complete_str,'booking_canc'=>$booking_cancel_str,'pend_onride'=>$booking_pending_str);    
    echo json_encode($data_array); 
    exit;


}



function drivercompleted(){
    
    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }
    
    $booking_id = (int) $_GET['bookingid'];

    $complete_code = $_GET['complete_code'];

    $distance_travelled = (float) $_GET['ride_distance'];
    $total_wait_time = (int) $_GET['total_wait_time'];
    $total_wait_time_cost = (float) $_GET['total_wait_time_cost'];
    $distance_travelled = $distance_travelled * 1000;
    $ride_duration_secs = (int) $_GET['ride_duration_secs'];
    $ride_duration_formated = $_GET['ride_duration_secs_formated'];
    $dlat = !empty(mysqli_real_escape_string($GLOBALS['DB'], $_GET['driver_lat'])) ? mysqli_real_escape_string($GLOBALS['DB'], $_GET['driver_lat']) : "";
    $dlng = !empty(mysqli_real_escape_string($GLOBALS['DB'], $_GET['driver_lng'])) ? mysqli_real_escape_string($GLOBALS['DB'], $_GET['driver_lng']) : "";

    $booking_data = [];
    



    // read booking data, verify booking completion status and completion code
    $query = sprintf('SELECT *,%1$stbl_bookings.route_id AS booking_route,%1$stbl_users.referral_discounts_count AS user_referral_discount_count,%1$stbl_bookings.franchise_commision AS franchise_commision,%1$stbl_bookings.driver_commision AS driver_commision,%1$stbl_drivers.wallet_amount AS driver_wallet,%1$stbl_drivers.firstname AS driver_firstname,%1$stbl_drivers.photo_file AS driver_photo,%1$stbl_users.push_notification_token AS push_token,%1$stbl_users.wallet_amount AS user_wallet,%1$stbl_users.reward_points_redeemed,%1$stbl_users.reward_points AS user_reward_points,estimated_cost,payment_type,%1$stbl_bookings.user_id AS customer_id,%1$stbl_bookings.completion_code,%1$stbl_bookings.status,%1$stbl_users.disp_lang AS u_lang,%1$stbl_bookings.pickup_address,%1$stbl_bookings.dropoff_address FROM %1$stbl_bookings 
    INNER JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id
    INNER JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
    INNER JOIN %1$stbl_franchise ON %1$stbl_franchise.id = %1$stbl_drivers.franchise_id
    WHERE %1$stbl_bookings.id = "%2$d"', DB_TBL_PREFIX, $booking_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){

            $booking_data = mysqli_fetch_assoc($result);

            //var_dump($booking_data);
            
            if($booking_data['status'] != 1){ //check if booking status is onride
                $error = array("error"=>__("An error has occured"));
                echo json_encode($error); 
                exit;
            }

            if($booking_data['completion_code'] != $complete_code){ //check if booking complete code is same as what is sent
                $error = array("error"=>__("Invalid ride completion code"));
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

    


    //All good 

    


    $ride_fare = !empty($_GET['ride_fare']) ? (float) $_GET['ride_fare'] : $booking_data['estimated_cost'];
    $city_currency_exchange = (float) $booking_data['cur_exchng_rate'];
    $city_currency_symbol = $booking_data['cur_symbol'];
    $city_currency_code = $booking_data['cur_code'];
    $ride_date_completed = gmdate('Y-m-d H:i:s', strtotime($booking_data['date_started'] . " UTC") + $ride_duration_secs);
    $amount_paid_by_rider = (float) $_GET['amount_paid_by_rider'];

    $rlang = $booking_data['u_lang'];
    

    $referral_used = !empty($_GET['referral_used']) ? 1 : 0;
    $referral_discount_value = (float) $_GET['referral_discount_value'];
    $coupon_used = !empty($_GET['coupon_code']) ? 1 : 0;
    $coupon_discount_type = !empty($_GET['coupon_discount_type']) ? 1 : 0;
    $coupon_discount_value = (float) $_GET['coupon_discount_value'];

    //update booking
    
    $query = sprintf('UPDATE %stbl_bookings SET total_wait_time = %d, total_wait_time_cost = "%s",drv_comp_long = "%s",drv_comp_lat = "%s",actual_cost = "%s",`status` = 3, date_completed = "%s", distance_travelled = "%s", paid_amount = "%s" WHERE id = "%d" AND `status` = 1', DB_TBL_PREFIX,$total_wait_time,$total_wait_time_cost,$dlng,$dlat,$ride_fare,$ride_date_completed,$distance_travelled,$amount_paid_by_rider,$booking_id);

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }


    mysqli_query($GLOBALS['DB'],'START TRANSACTION'); //begin mysql transactions

    //compute rider referal discount if eligible
    if($referral_used && $booking_data['user_referral_discount_count'] > 0){

        $query = sprintf('UPDATE %stbl_users SET referral_discounts_count = referral_discounts_count - 1 WHERE `user_id` = %d',DB_TBL_PREFIX,$booking_data['customer_id']);
        if(!$result = mysqli_query($GLOBALS['DB'],$query)){
            mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); 
            exit;
        }

    }       

    


    //update driver allocation entry to indicate the booking has been finalized
    $query = sprintf('UPDATE %stbl_driver_allocate SET `status` = %d WHERE booking_id = %d AND driver_id = %d', DB_TBL_PREFIX, 4, $booking_id, $_SESSION['uid']);
    $result = mysqli_query($GLOBALS['DB'], $query);

    //update driver's completed rides count
    $query = sprintf('UPDATE %stbl_drivers SET completed_rides = completed_rides + 1 WHERE driver_id = %d', DB_TBL_PREFIX, $_SESSION['uid']);
    $result = mysqli_query($GLOBALS['DB'], $query);

    //update rider's completed rides count
    $query = sprintf('UPDATE %stbl_users SET completed_rides = completed_rides + 1 WHERE `user_id` = %d', DB_TBL_PREFIX, $booking_data['customer_id']);
    $result = mysqli_query($GLOBALS['DB'], $query);


    //mark that customer has used this coupon
    if($coupon_used){
        //get coupon data 
        $query = sprintf('SELECT %1$stbl_coupon_codes.id AS coupon_id,%1$stbl_coupons_used.times_used FROM %1$stbl_coupon_codes 
        LEFT JOIN %1$stbl_coupons_used ON %1$stbl_coupons_used.coupon_id = %1$stbl_coupon_codes.id AND %1$stbl_coupons_used.user_id = %4$d
        WHERE %1$stbl_coupon_codes.coupon_code = "%2$s" AND %1$stbl_coupon_codes.city = %3$d', DB_TBL_PREFIX,$booking_data['coupon_code'],$booking_data['booking_route'],$booking_data['customer_id']);
        if($result = mysqli_query($GLOBALS['DB'],$query)){
            if(mysqli_num_rows($result)){
                $row = mysqli_fetch_assoc($result);                
                if($row['times_used'] == null){
                    $query = sprintf('INSERT INTO %stbl_coupons_used (coupon_id,`user_id`,times_used) VALUES ("%d","%d","%d")',DB_TBL_PREFIX,$row['coupon_id'],$booking_data['customer_id'],1);
                    $result = mysqli_query($GLOBALS['DB'],$query);
                }else{
                    $query = sprintf('UPDATE %stbl_coupons_used SET times_used = times_used + 1 WHERE coupon_id = %d AND user_id = %d', DB_TBL_PREFIX, $row['coupon_id'],$booking_data['customer_id']);
                    $result = mysqli_query($GLOBALS['DB'],$query);
                }
            }
        }
        
    }


    //process reward points only when coupon or referral discounts are not used
    $points_earned = 0;
    if(!($coupon_used || $referral_used)){
        //get reward points data
        $query = sprintf('SELECT * FROM %stbl_reward_points WHERE id=1', DB_TBL_PREFIX);
        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                $row = mysqli_fetch_assoc($result);
                if($row['status'] == 1){

                    $curtopointsconv = (float) $row['cur_to_points_conv'];

                    $amount_paid_by_rider_converted = ((float) $amount_paid_by_rider / $city_currency_exchange);

                    

                    if($booking_data['payment_type'] == 2){  //wallet payment? Rider shouldn't re-earn points previously redeemed into wallet
                        if($booking_data['reward_points_redeemed'] == 0){ //rider has spent all previously redeemed amount
                            $points_earned = round($amount_paid_by_rider_converted / $curtopointsconv, 1);
                            $query = sprintf('UPDATE %1$stbl_users SET reward_points = reward_points + %2$f WHERE user_id = %3$d', DB_TBL_PREFIX, $points_earned, $booking_data['customer_id']);
                            $result = mysqli_query($GLOBALS['DB'], $query);
                        }else{
                            $last_redeemed_reward_point_balance = $booking_data['reward_points_redeemed'] - $amount_paid_by_rider_converted;
                            if($last_redeemed_reward_point_balance > 0){ //rider hasn't spent all previously redeemed point money. update the amount spent
                                $query = sprintf('UPDATE %1$stbl_users SET reward_points_redeemed = %2$f WHERE user_id = %3$d', DB_TBL_PREFIX, $last_redeemed_reward_point_balance, $booking_data['customer_id']);
                                $result = mysqli_query($GLOBALS['DB'], $query);
                            }elseif($last_redeemed_reward_point_balance < 0){ //rider is about to spend more than previously redeemed point amount
                                //convert the balance to positive and compute the number of point it is worth
                                $last_redeemed_reward_point_balance_real = abs($last_redeemed_reward_point_balance);
                                $points_earned = round($last_redeemed_reward_point_balance_real / $curtopointsconv, 1);
                                $query = sprintf('UPDATE %1$stbl_users SET reward_points = reward_points + %2$f, reward_points_redeemed = %4$f WHERE user_id = %3$d', DB_TBL_PREFIX, $points_earned, $booking_data['customer_id'], 0);
                                $result = mysqli_query($GLOBALS['DB'], $query);

                            }else{ //reset previously redeemed points money to zero
                                $query = sprintf('UPDATE %1$stbl_users SET reward_points_redeemed = %2$f WHERE user_id = %3$d', DB_TBL_PREFIX, 0, $booking_data['customer_id']);
                                $result = mysqli_query($GLOBALS['DB'], $query);
                            }
                        }
                    }else{
                        $points_earned = round($amount_paid_by_rider_converted / $curtopointsconv, 1);
                        $query = sprintf('UPDATE %1$stbl_users SET reward_points = reward_points + %2$f WHERE user_id = %3$d', DB_TBL_PREFIX, $points_earned, $booking_data['customer_id']);
                        $result = mysqli_query($GLOBALS['DB'], $query);
                    }

                    


                }
            }

        }

    }


    //get current wallet amount of franchise owner. always has id = 1 on franchise table
    $owner_franchise_wallet_amount = [];
    $query = sprintf('SELECT fwallet_amount FROM %stbl_franchise WHERE id = 1', DB_TBL_PREFIX); //Get required user information from DB

        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                $owner_franchise_wallet_amount = mysqli_fetch_assoc($result);                    
            }else{
                mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                $error = array("error"=>__("An error has occured"));
                echo json_encode($error); 
                exit;
            }        
        }else{
            mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); 
            exit;
        }


    //process wallet

    $owner_wallet_amount = !empty($owner_franchise_wallet_amount['fwallet_amount']) ? (float) $owner_franchise_wallet_amount['fwallet_amount'] : 0.00;
    $driver_wallet_amount =  !empty($booking_data['driver_wallet']) ? (float) $booking_data['driver_wallet'] : 0.00;
    $franchise_wallet_amount =  !empty($booking_data['fwallet_amount']) ? (float) $booking_data['fwallet_amount'] : 0.00;
    $user_wallet_amount =  !empty($booking_data['user_wallet']) ? (float) $booking_data['user_wallet'] : 0.00;
    $booking_cost = $ride_fare;
    $driver_commision = !empty($booking_data['driver_commision']) ? (float) $booking_data['driver_commision'] : 0; //percentage commission for driver
    $franchise_commision = !empty($booking_data['franchise_commision']) ? (float) $booking_data['franchise_commision'] : 0.00; //percentage commission for franchise

    

    //compute drivers referral bonus
    if(!empty($booking_data['reg_with_referal_code']) && $booking_data['referral_target_status'] == 0){ 
        //get driver referral setting
        $query = sprintf('SELECT %1$stbl_referral_drivers.beneficiary,%1$stbl_referral_drivers.route_id AS ref_route_id, %1$stbl_referral_drivers.driver_incentive,%1$stbl_referral_drivers.invitee_incentive, %1$stbl_referral_drivers.number_of_rides, %1$stbl_referral_drivers.number_of_days, %1$stbl_currencies.symbol, %1$stbl_currencies.exchng_rate, %1$stbl_currencies.iso_code FROM %1$stbl_referral_drivers 
        INNER JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_referral_drivers.route_id
        INNER JOIN %1$stbl_currencies ON %1$stbl_currencies.id = %1$stbl_routes.city_currency_id
        WHERE %1$stbl_referral_drivers.route_id = %2$d AND %1$stbl_referral_drivers.status = 1', DB_TBL_PREFIX, $booking_data['reg_route_id']);

        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                $referal_settings_data = mysqli_fetch_assoc($result);
                //check if driver has met the referral bonus condition. Only check for rides completed in the originally registered city of the driver.
                $query = sprintf('SELECT id, DATE(date_completed) AS date_comp FROM %stbl_bookings WHERE driver_id = %d AND `status` = %d AND route_id = %d ORDER BY id ASC LIMIT %d', DB_TBL_PREFIX, $_SESSION['uid'], 3, $booking_data['reg_route_id'],$referal_settings_data['number_of_rides']);
                if($result = mysqli_query($GLOBALS['DB'], $query)){
                    if(mysqli_num_rows($result)){

                        $driver_bookings = [];

                        while($row = mysqli_fetch_assoc($result)){
                        	if(!empty($row))$driver_bookings[] =$row;
                        }

                        $num_of_bookings = count($driver_bookings);
                        $start_date = $driver_bookings[0]['date_comp'];
                        $latest_date = $driver_bookings[$num_of_bookings - 1]['date_comp'];
                        $days_taken = 0;

                        //get duration of time the driver completed these bookings
                        if(isValidDate($start_date) && isValidDate($latest_date)){
                            $time_duration_sec = strtotime($latest_date) - strtotime($start_date);
                            $days_taken = intval($time_duration_sec / 86400); //convert seconds to days
                        }else{
                            $days_taken = 1000;
                        }
                        
                        //update referral task progress
                        $query = sprintf('UPDATE %stbl_drivers SET referral_task_progress = "%s" WHERE driver_id = %d', DB_TBL_PREFIX,$num_of_bookings,$_SESSION['uid']);
                        $result = mysqli_query($GLOBALS['DB'], $query);

                        
                        if($num_of_bookings >= $referal_settings_data['number_of_rides']){
                            
                            if($days_taken <= $referal_settings_data['number_of_days']){

                                //update record and grant bonuses
                                $query = sprintf('UPDATE %stbl_drivers SET referral_target_status = %d WHERE driver_id = %d', DB_TBL_PREFIX,1,$_SESSION['uid']);
                                $result = mysqli_query($GLOBALS['DB'], $query);

                                $referral_bonus_amount_driver_conv = $referal_settings_data['driver_incentive'] / $referal_settings_data['exchng_rate'];
                                $referral_bonus_amount_invitee_conv = $referal_settings_data['invitee_incentive'] / $referal_settings_data['exchng_rate'];

                                if($referal_settings_data['beneficiary'] == 0){
                                    //driver only
                                    //get the driver whose referral code was used during registration by this driver
                                    $query = sprintf('SELECT disp_lang,driver_id, wallet_amount FROM %stbl_drivers WHERE referal_code = "%s"', DB_TBL_PREFIX, $booking_data['reg_with_referal_code']);
                                    if($result = mysqli_query($GLOBALS['DB'], $query)){
                                        if(mysqli_num_rows($result)){

                                            $reg_ref_driver_data = mysqli_fetch_assoc($result);

                                            //update drivers wallet
                                            $query = sprintf('UPDATE %stbl_drivers SET wallet_amount = wallet_amount + %f WHERE driver_id = %d', DB_TBL_PREFIX,$referral_bonus_amount_driver_conv,$reg_ref_driver_data['driver_id']);
                                            $result = mysqli_query($GLOBALS['DB'], $query);

                                            //debit and update company franchise wallet. Franchise bears the cost
                                            $query = sprintf('UPDATE %stbl_franchise SET fwallet_amount = fwallet_amount - %f WHERE id = %d', DB_TBL_PREFIX,$referral_bonus_amount_driver_conv,1);
                                            $result = mysqli_query($GLOBALS['DB'], $query);

                                            $owner_wallet_amount -= $referral_bonus_amount_driver_conv;

                                            $transaction_id = crypto_string();
                                            $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                                            '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                                            DB_TBL_PREFIX,
                                            $referal_settings_data['symbol'],
                                            $referal_settings_data['exchng_rate'],
                                            $referal_settings_data['iso_code'],
                                            0,
                                            $transaction_id,
                                            0 - $referral_bonus_amount_driver_conv, 
                                            $owner_wallet_amount,
                                            1,
                                            2, //2 = franchise
                                            "Driver referral bonus debit", 
                                            3,
                                            gmdate('Y-m-d H:i:s', time())

                                            );

                                            $result = mysqli_query($GLOBALS['DB'],$query);


                                            //Add this transaction to wallet transactions database table
                                            $transaction_id = crypto_string();
                                            $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                                            '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                                            DB_TBL_PREFIX,
                                            $referal_settings_data['symbol'],
                                            $referal_settings_data['exchng_rate'],
                                            $referal_settings_data['iso_code'],
                                            0,
                                            $transaction_id,
                                            $referal_settings_data['driver_incentive'],
                                            $referral_bonus_amount_driver_conv + $reg_ref_driver_data['wallet_amount'],
                                            $reg_ref_driver_data['driver_id'],
                                            1,
                                            mysqli_real_escape_string($GLOBALS['DB'],__("Referral bonus",null,"d|{$reg_ref_driver_data['disp_lang']}")), 
                                            2,
                                            gmdate('Y-m-d H:i:s', time())

                                            );

                                            $result = mysqli_query($GLOBALS['DB'],$query);


                                            //send notification
                                            $driver_notification_msg = __("The new driver you referred to Droptaxi has completed the required number of trips within the set period of time. You have earned a referral bonus of {---1}",[$referal_settings_data['driver_incentive']],"d|{$reg_ref_driver_data['disp_lang']}");
                                            $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
                                                ("%d",1,"%s",0,"%s")', 
                                                DB_TBL_PREFIX,
                                                $reg_ref_driver_data['driver_id'],
                                                mysqli_real_escape_string($GLOBALS['DB'], $driver_notification_msg),
                                                gmdate('Y-m-d H:i:s', time()) 
                                            );
                                            $result = mysqli_query($GLOBALS['DB'], $query);

                                        }

                                    }
                                    


                                }elseif($referal_settings_data['beneficiary'] == 1){
                                    //invitee only

                                    //update drivers wallet
                                    $query = sprintf('UPDATE %stbl_drivers SET wallet_amount = wallet_amount + %f WHERE driver_id = %d', DB_TBL_PREFIX,$referral_bonus_amount_invitee_conv,$_SESSION['uid']);
                                    $result = mysqli_query($GLOBALS['DB'], $query);

                                    $driver_wallet_amount += $referral_bonus_amount_invitee_conv;

                                    //debit and update company franchise wallet. Franchise bears the cost
                                    $query = sprintf('UPDATE %stbl_franchise SET fwallet_amount = fwallet_amount - %f WHERE id = %d', DB_TBL_PREFIX,$referral_bonus_amount_invitee_conv,1);
                                    $result = mysqli_query($GLOBALS['DB'], $query);

                                    $owner_wallet_amount -= $referral_bonus_amount_invitee_conv;

                                    $transaction_id = crypto_string();
                                    $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                                    '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                                    DB_TBL_PREFIX,
                                    $referal_settings_data['symbol'],
                                    $referal_settings_data['exchng_rate'],
                                    $referal_settings_data['iso_code'],
                                    0,
                                    $transaction_id,
                                    0 - $referral_bonus_amount_invitee_conv, 
                                    $owner_wallet_amount,
                                    1,
                                    2, //2 = franchise
                                    "Driver referral bonus debit", 
                                    3,
                                    gmdate('Y-m-d H:i:s', time())

                                    );

                                    $result = mysqli_query($GLOBALS['DB'],$query);

                                    

                                    //Add this transaction to wallet transactions database table
                                    $transaction_id = crypto_string();
                                    $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                                    '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                                    DB_TBL_PREFIX,
                                    $referal_settings_data['symbol'],
                                    $referal_settings_data['exchng_rate'],
                                    $referal_settings_data['iso_code'],
                                    0,
                                    $transaction_id,
                                    $referal_settings_data['invitee_incentive'],
                                    $driver_wallet_amount,
                                    $_SESSION['uid'],
                                    1,
                                    mysqli_real_escape_string($GLOBALS['DB'],__("Referral bonus")), 
                                    2,
                                    gmdate('Y-m-d H:i:s', time())

                                    );

                                    $result = mysqli_query($GLOBALS['DB'],$query);


                                    //send notification                                    
                                    $driver_notification_msg = __("You have completed the required number of trips within the set period of time. You have earned a referral bonus of {---1}",[$referal_settings_data['invitee_incentive']]);
                                    $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
                                        ("%d",1,"%s",0,"%s")', 
                                        DB_TBL_PREFIX,
                                        $_SESSION['uid'],
                                        mysqli_real_escape_string($GLOBALS['DB'], $driver_notification_msg),
                                        gmdate('Y-m-d H:i:s', time()) 
                                    );
                                    $result = mysqli_query($GLOBALS['DB'], $query);


                                }else{
                                    //driver and invitee
                                    //get the driver whose referral code was used during registration by this driver
                                    $query = sprintf('SELECT disp_lang,driver_id, wallet_amount FROM %stbl_drivers WHERE referal_code = "%s"', DB_TBL_PREFIX, $booking_data['reg_with_referal_code']);
                                    if($result = mysqli_query($GLOBALS['DB'], $query)){
                                        if(mysqli_num_rows($result)){

                                            $reg_ref_driver_data = mysqli_fetch_assoc($result);

                                            //update drivers wallet
                                            $query = sprintf('UPDATE %stbl_drivers SET wallet_amount = wallet_amount + %f WHERE driver_id = %d', DB_TBL_PREFIX,$referral_bonus_amount_driver_conv,$reg_ref_driver_data['driver_id']);
                                            $result = mysqli_query($GLOBALS['DB'], $query);

                                            //debit and update company franchise wallet. Franchise bears the cost
                                            $query = sprintf('UPDATE %stbl_franchise SET fwallet_amount = fwallet_amount - %f WHERE id = %d', DB_TBL_PREFIX,$referral_bonus_amount_driver_conv,1);
                                            $result = mysqli_query($GLOBALS['DB'], $query);

                                            $owner_wallet_amount -= $referral_bonus_amount_driver_conv;

                                            $transaction_id = crypto_string();
                                            $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                                            '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                                            DB_TBL_PREFIX,
                                            $referal_settings_data['symbol'],
                                            $referal_settings_data['exchng_rate'],
                                            $referal_settings_data['iso_code'],
                                            0,
                                            $transaction_id,
                                            0 - $referral_bonus_amount_driver_conv, 
                                            $owner_wallet_amount,
                                            1,
                                            2, //2 = franchise
                                            "Driver referral bonus debit", 
                                            3,
                                            gmdate('Y-m-d H:i:s', time())

                                            );

                                            $result = mysqli_query($GLOBALS['DB'],$query);


                                            //Add this transaction to wallet transactions database table
                                            $transaction_id = crypto_string();
                                            $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                                            '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                                            DB_TBL_PREFIX,
                                            $referal_settings_data['symbol'],
                                            $referal_settings_data['exchng_rate'],
                                            $referal_settings_data['iso_code'],
                                            0,
                                            $transaction_id,
                                            $referal_settings_data['driver_incentive'],
                                            $referral_bonus_amount_driver_conv + $reg_ref_driver_data['wallet_amount'],
                                            $reg_ref_driver_data['driver_id'],
                                            1,
                                            mysqli_real_escape_string($GLOBALS['DB'],__("Referral bonus",null,"d|{$reg_ref_driver_data['disp_lang']}")), 
                                            2,
                                            gmdate('Y-m-d H:i:s', time())

                                            );

                                            $result = mysqli_query($GLOBALS['DB'],$query);


                                            //send notification
                                            $driver_notification_msg = __("The new driver you referred to Droptaxi has completed the required number of trips within the set period of time. You have earned a referral bonus of {---1}",[$referal_settings_data['symbol'].$referal_settings_data['driver_incentive']],"d|{$reg_ref_driver_data['disp_lang']}");
                                            $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
                                                ("%d",1,"%s",0,"%s")', 
                                                DB_TBL_PREFIX,
                                                $reg_ref_driver_data['driver_id'],
                                                mysqli_real_escape_string($GLOBALS['DB'], $driver_notification_msg),
                                                gmdate('Y-m-d H:i:s', time()) 
                                            );
                                            $result = mysqli_query($GLOBALS['DB'], $query);

                                        }

                                    }

                                    //update drivers wallet
                                    $query = sprintf('UPDATE %stbl_drivers SET wallet_amount = wallet_amount + %f WHERE driver_id = %d', DB_TBL_PREFIX,$referral_bonus_amount_invitee_conv,$_SESSION['uid']);
                                    $result = mysqli_query($GLOBALS['DB'], $query);

                                    $driver_wallet_amount += $referral_bonus_amount_invitee_conv;

                                    //debit and update company franchise wallet. Franchise bears the cost
                                    $query = sprintf('UPDATE %stbl_franchise SET fwallet_amount = fwallet_amount - %f WHERE id = %d', DB_TBL_PREFIX,$referral_bonus_amount_invitee_conv,1);
                                    $result = mysqli_query($GLOBALS['DB'], $query);

                                    $owner_wallet_amount -= $referral_bonus_amount_invitee_conv;

                                    $transaction_id = crypto_string();
                                    $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                                    '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                                    DB_TBL_PREFIX,
                                    $referal_settings_data['symbol'],
                                    $referal_settings_data['exchng_rate'],
                                    $referal_settings_data['iso_code'],
                                    0,
                                    $transaction_id,
                                    0 - $referral_bonus_amount_invitee_conv, 
                                    $owner_wallet_amount,
                                    1,
                                    2, //2 = franchise
                                    "Driver referral bonus debit", 
                                    3,
                                    gmdate('Y-m-d H:i:s', time())

                                    );

                                    $result = mysqli_query($GLOBALS['DB'],$query);

                                    

                                    //Add this transaction to wallet transactions database table
                                    $transaction_id = crypto_string();
                                    $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                                    '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                                    DB_TBL_PREFIX,
                                    $referal_settings_data['symbol'],
                                    $referal_settings_data['exchng_rate'],
                                    $referal_settings_data['iso_code'],
                                    0,
                                    $transaction_id,
                                    $referal_settings_data['invitee_incentive'],
                                    $driver_wallet_amount,
                                    $_SESSION['uid'],
                                    1,
                                    mysqli_real_escape_string($GLOBALS['DB'],__("Referral bonus")), 
                                    2,
                                    gmdate('Y-m-d H:i:s', time())

                                    );

                                    $result = mysqli_query($GLOBALS['DB'],$query);


                                    //send notification                                    
                                    $driver_notification_msg = __("You have completed the required number of trips within the set period of time. You have earned a referral bonus of {---1}",[$referal_settings_data['symbol'].$referal_settings_data['invitee_incentive']]);
                                    $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
                                        ("%d",1,"%s",0,"%s")', 
                                        DB_TBL_PREFIX,
                                        $_SESSION['uid'],
                                        mysqli_real_escape_string($GLOBALS['DB'], $driver_notification_msg),
                                        gmdate('Y-m-d H:i:s', time()) 
                                    );
                                    $result = mysqli_query($GLOBALS['DB'], $query);




                                }



                            }else{

                                
                                //driver didn't meet up with the referral bonus requirement. update record accordingly
                                $query = sprintf('UPDATE %stbl_drivers SET referral_target_status = %d WHERE driver_id = %d', DB_TBL_PREFIX,2,$_SESSION['uid']);
                                $result = mysqli_query($GLOBALS['DB'], $query);

                                //send notifications
                                if($referal_settings_data['beneficiary'] == 0){
                                    //driver only
                                    
                                    //get the driver whose referral code was used during registration by this driver
                                    $query = sprintf('SELECT disp_lang,driver_id FROM %stbl_drivers WHERE referal_code = "%s"', DB_TBL_PREFIX, $booking_data['reg_with_referal_code']);
                                    if($result = mysqli_query($GLOBALS['DB'], $query)){
                                        if(mysqli_num_rows($result)){

                                            $reg_ref_driver_data = mysqli_fetch_assoc($result);
                                            $driver_notification_msg = __("The new driver you referred to Droptaxi failed to complete the required number of trips within the set period of time. No referral bonus was paid to you",null,"d|{$reg_ref_driver_data['disp_lang']}");
                                            $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
                                                ("%d",1,"%s",0,"%s")', 
                                                DB_TBL_PREFIX,
                                                $reg_ref_driver_data['driver_id'],
                                                mysqli_real_escape_string($GLOBALS['DB'], $driver_notification_msg),
                                                gmdate('Y-m-d H:i:s', time()) 
                                            );
                                            $result = mysqli_query($GLOBALS['DB'], $query);

                                        }

                                    }

                                    

                                }elseif($referal_settings_data['beneficiary'] == 1){
                                    //invitee only
                                    $driver_notification_msg = __("You failed to complete the required number of trips within the set period of time. No referral bonus was paid to you");
                                    $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
                                        ("%d",1,"%s",0,"%s")', 
                                        DB_TBL_PREFIX,
                                        $_SESSION['uid'],
                                        mysqli_real_escape_string($GLOBALS['DB'], $driver_notification_msg),
                                        gmdate('Y-m-d H:i:s', time()) 
                                    );
                                    $result = mysqli_query($GLOBALS['DB'], $query);
                                }else{
                                    //driver and invitee
                                    //get the driver whose referral code was used during registration by this driver
                                    $query = sprintf('SELECT disp_lang,driver_id FROM %stbl_drivers WHERE referal_code = "%s"', DB_TBL_PREFIX, $booking_data['reg_with_referal_code']);
                                    if($result = mysqli_query($GLOBALS['DB'], $query)){
                                        if(mysqli_num_rows($result)){

                                            $reg_ref_driver_data = mysqli_fetch_assoc($result);
                                            $driver_notification_msg = __("The new driver you referred to Droptaxi failed to complete the required number of trips within the set period of time. No referral bonus was paid to you",null,"d|{$reg_ref_driver_data['disp_lang']}");
                                            $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
                                                ("%d",1,"%s",0,"%s")', 
                                                DB_TBL_PREFIX,
                                                $reg_ref_driver_data['driver_id'],
                                                mysqli_real_escape_string($GLOBALS['DB'], $driver_notification_msg),
                                                gmdate('Y-m-d H:i:s', time()) 
                                            );
                                            $result = mysqli_query($GLOBALS['DB'], $query);

                                        }

                                    }

                                    $driver_notification_msg = __("You failed to complete the required number of trips within the set period of time. No referral bonus was paid to you");
                                    $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
                                        ("%d",1,"%s",0,"%s")', 
                                        DB_TBL_PREFIX,
                                        $_SESSION['uid'],
                                        mysqli_real_escape_string($GLOBALS['DB'], $driver_notification_msg),
                                        gmdate('Y-m-d H:i:s', time()) 
                                    );
                                    $result = mysqli_query($GLOBALS['DB'], $query);

                                }

                            }
                            


                        }elseif($days_taken > $referal_settings_data['number_of_days']){

                            //driver didn't meet up with the referral bonus requirement. update record accordingly
                            $query = sprintf('UPDATE %stbl_drivers SET referral_target_status = %d WHERE driver_id = %d', DB_TBL_PREFIX,2,$_SESSION['uid']);
                            $result = mysqli_query($GLOBALS['DB'], $query);

                            //send notifications
                            if($referal_settings_data['beneficiary'] == 0){
                                //driver only
                                
                                //get the driver whose referral code was used during registration by this driver
                                $query = sprintf('SELECT disp_lang,driver_id FROM %stbl_drivers WHERE referal_code = "%s"', DB_TBL_PREFIX, $booking_data['reg_with_referal_code']);
                                if($result = mysqli_query($GLOBALS['DB'], $query)){
                                    if(mysqli_num_rows($result)){

                                        $reg_ref_driver_data = mysqli_fetch_assoc($result);
                                        $driver_notification_msg = __("The new driver you referred to Droptaxi failed to complete the required number of trips within the set period of time. No referral bonus was paid to you",null,"d|{$reg_ref_driver_data['disp_lang']}");
                                        $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
                                            ("%d",1,"%s",0,"%s")', 
                                            DB_TBL_PREFIX,
                                            $reg_ref_driver_data['driver_id'],
                                            mysqli_real_escape_string($GLOBALS['DB'], $driver_notification_msg),
                                            gmdate('Y-m-d H:i:s', time()) 
                                        );
                                        $result = mysqli_query($GLOBALS['DB'], $query);

                                    }

                                }

                                

                            }elseif($referal_settings_data['beneficiary'] == 1){
                                //invitee only
                                $driver_notification_msg = __("You failed to complete the required number of trips within the set period of time. No referral bonus was paid to you");
                                $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
                                    ("%d",1,"%s",0,"%s")', 
                                    DB_TBL_PREFIX,
                                    $_SESSION['uid'],
                                    mysqli_real_escape_string($GLOBALS['DB'], $driver_notification_msg),
                                    gmdate('Y-m-d H:i:s', time()) 
                                );
                                $result = mysqli_query($GLOBALS['DB'], $query);
                            }else{
                                //driver and invitee
                                //get the driver whose referral code was used during registration by this driver
                                $query = sprintf('SELECT disp_lang,driver_id FROM %stbl_drivers WHERE referal_code = "%s"', DB_TBL_PREFIX, $booking_data['reg_with_referal_code']);
                                if($result = mysqli_query($GLOBALS['DB'], $query)){
                                    if(mysqli_num_rows($result)){

                                        $reg_ref_driver_data = mysqli_fetch_assoc($result);
                                        $driver_notification_msg = __("The new driver you referred to Droptaxi failed to complete the required number of trips within the set period of time. No referral bonus was paid to you",null,"d|{$reg_ref_driver_data['disp_lang']}");
                                        $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
                                            ("%d",1,"%s",0,"%s")', 
                                            DB_TBL_PREFIX,
                                            $reg_ref_driver_data['driver_id'],
                                            mysqli_real_escape_string($GLOBALS['DB'], $driver_notification_msg),
                                            gmdate('Y-m-d H:i:s', time()) 
                                        );
                                        $result = mysqli_query($GLOBALS['DB'], $query);

                                    }

                                }

                                $driver_notification_msg = __("You failed to complete the required number of trips within the set period of time. No referral bonus was paid to you");
                                $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
                                    ("%d",1,"%s",0,"%s")', 
                                    DB_TBL_PREFIX,
                                    $_SESSION['uid'],
                                    mysqli_real_escape_string($GLOBALS['DB'], $driver_notification_msg),
                                    gmdate('Y-m-d H:i:s', time()) 
                                );
                                $result = mysqli_query($GLOBALS['DB'], $query);

                            }


                        }
                        
                        
                    }
                }

            }
        }

    }





    if($booking_data['payment_type'] == 2){ //customer paid by wallet

        //$user_wallet_balance = $user_wallet_amount - ((float) $booking_cost / $city_currency_exchange); //convert the booking fare to default currency value and deduct from customer wallet

        $user_wallet_balance = $user_wallet_amount - ((float) $amount_paid_by_rider / $city_currency_exchange); //convert the booking fare to default currency value and deduct from customer wallet

        $query = sprintf('UPDATE %stbl_users SET wallet_amount = %f WHERE user_id = "%d"', DB_TBL_PREFIX, $user_wallet_balance,$booking_data['customer_id']);
        if(!$result = mysqli_query($GLOBALS['DB'],$query)){
            mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); 
            exit;
        }

        $transaction_msg = __("Payment for completed ride with booking ID: {---1}.",["#{$booking_id}"],"d|{$rlang}");
        if($referral_used && $coupon_used){
            if($coupon_discount_type == 0){ //percentage coupon discount
                $transaction_msg = __("Payment for completed ride with booking ID: {---1}. Referral discount of {---2} followed by a coupon discount of {---3} was applied",["#{$booking_id}","{$referral_discount_value}%","{$coupon_discount_value}%"], "d|{$rlang}"); 
            }else{
                $transaction_msg = __("Payment for completed ride with booking ID: {---1}. Referral discount and Coupon flat fare price was used",["#{$booking_id}"],"d|{$rlang}"); 
            }   
            
        }elseif($referral_used){
            $transaction_msg = __("Payment for completed ride with booking ID: {---1}. Referral discount of {---2} was applied",["#{$booking_id}","{$referral_discount_value}%"],"d|{$rlang}"); 
        }elseif($coupon_used){
            if($coupon_discount_type == 0){ //percentage coupon discount
                $transaction_msg = __("Payment for completed ride with booking ID: {---1}. Coupon discount of {---2} was applied",["#{$booking_id}","{$coupon_discount_value}%"],"d|{$rlang}"); 
            }else{
                $transaction_msg = __("Payment for completed ride with booking ID: {---1}. Coupon flat fare price was used",["#{$booking_id}"],"d|{$rlang}"); 
            } 
        }

        //Add this transaction to wallet transactions database table
        $transaction_id = crypto_string();
        $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
        '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
        DB_TBL_PREFIX,
        $city_currency_symbol,
        $city_currency_exchange,
        $city_currency_code,
        $booking_id,
        $transaction_id,
        $amount_paid_by_rider,
        $user_wallet_balance,
        $booking_data['customer_id'],
        0, //0 = customer / rider
        mysqli_real_escape_string($GLOBALS['DB'],$transaction_msg), 
        3,
        gmdate('Y-m-d H:i:s', time())

        );

        if(!$result = mysqli_query($GLOBALS['DB'],$query)){
            mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); 
            exit;
        }
        

        //notify customer of wallet payment
        $booking_title = str_pad($booking_id , 5, '0', STR_PAD_LEFT);
        $amount_paid_by_rider_cur = floattocurrency($amount_paid_by_rider); //format for display as currency

        $notification_msg = __("Wallet payment of {---1} for booking {---2} was successful",["{$city_currency_symbol}{$amount_paid_by_rider_cur}","#{$booking_title}"],"d|{$rlang}");
        if($referral_used && $coupon_used){
            if($coupon_discount_type == 0){ //percentage coupon discount
                $notification_msg = __("Wallet payment of {---1} for booking {---2} was successful. Referral discount of {---3} followed by a coupon discount of {---4} was applied",["{$city_currency_symbol}{$amount_paid_by_rider_cur}","#{$booking_title}","{$referral_discount_value}%","{$coupon_discount_value}%","{$coupon_discount_value}%"],"d|{$rlang}"); 
            }else{
                $notification_msg = __("Wallet payment of {---1} for booking {---2} was successful. Referral discount and Coupon flat fare price was used",["{$city_currency_symbol}{$amount_paid_by_rider_cur}","#{$booking_title}"],"d|{$rlang}"); 
            }   
            
        }elseif($referral_used){
            $notification_msg = __("Wallet payment of {---1} for booking {---2} was successful. Referral discount of {---3} was applied",["{$city_currency_symbol}{$amount_paid_by_rider_cur}","#{$booking_title}","{$referral_discount_value}%"],"d|{$rlang}"); 
        }elseif($coupon_used){
            if($coupon_discount_type == 0){ //percentage coupon discount
                $notification_msg = __("Wallet payment of {---1} for booking {---2} was successful. Coupon discount of {---3} was applied",["{$city_currency_symbol}{$amount_paid_by_rider_cur}","#{$booking_title}","{$coupon_discount_value}%"],"d|{$rlang}"); 
            }else{
                $notification_msg = __("Wallet payment of {---1} for booking {---2} was successful. Coupon flat fare price was used",["{$city_currency_symbol}{$amount_paid_by_rider_cur}","#{$booking_title}"],"d|{$rlang}"); 
            } 
        }
        
        $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
        ("%d",0,"%s",3,"%s")', 
        DB_TBL_PREFIX,
        $booking_data['customer_id'],
        mysqli_real_escape_string($GLOBALS['DB'],$notification_msg),
        gmdate('Y-m-d H:i:s', time()) 
        );

        if(!$result = mysqli_query($GLOBALS['DB'],$query)){
            mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); 
            exit;
        }
        
        //compute driver and franchise earning. Owner franchise bears the cost of coupon and referral discounts

        $driver_earning = $booking_cost * $driver_commision / 100;
        $driver_earning_converted = ((float) $driver_earning / $city_currency_exchange);
        
        $owner_franchise_earning = 0.00;
        $other_franchise_earning = 0.00;

        if($booking_data['franchise_id'] == 1){ //driver franchise owner franchise. owner franchise has ID = 1
            $owner_franchise_earning = $booking_cost - $driver_earning;            

            $deficit_due_to_discounts = $amount_paid_by_rider - $ride_fare; //calculate deficit due to discounts if any. should be deducted from owner franchise wallet
            $owner_franchise_earning = $deficit_due_to_discounts + $owner_franchise_earning; //add gains 
            $owner_franchise_earning_converted = ((float) $owner_franchise_earning / $city_currency_exchange);

            //update database records of owner franchise wallet
            $query = sprintf('UPDATE %stbl_franchise SET fwallet_amount = fwallet_amount + %f WHERE id = "%d"', DB_TBL_PREFIX, $owner_franchise_earning_converted,1);
            if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                $error = array("error"=>__("An error has occured"));
                echo json_encode($error); 
                exit;
            }
            

            //Add this transaction to wallet transactions database table
            
            if($owner_franchise_earning < 0){
                $debit_or_credit = "Debit";
                $transaction_type = 3; //denotes debit
            }else{
                $debit_or_credit = "Credit";
                $transaction_type = 2; //denotes earning / credit
            }
            
            $transaction_msg = "Ride completion earning ({$franchise_commision}% commission) after deducting driver commission, for booking: #{$booking_id}";
            if($referral_used && $coupon_used){                
                if($coupon_discount_type == 0){ //percentage coupon discount
                    $transaction_msg = "{$debit_or_credit} for completed ride with booking ID: #{$booking_id}. Customer referral discount of {$referral_discount_value}% followed by a coupon discount of {$coupon_discount_value}% was applied."; 
                }else{
                    $transaction_msg = "{$debit_or_credit} for completed ride with booking ID: #{$booking_id}. Customer referral discount and Coupon flat fare price was used."; 
                }   
                
            }elseif($referral_used){
                
                $transaction_msg = "{$debit_or_credit} for completed ride with booking ID: #{$booking_id}. Customer referral discount of {$referral_discount_value}% was applied."; 
            }elseif($coupon_used){
                
                if($coupon_discount_type == 0){ //percentage coupon discount
                    $transaction_msg = "{$debit_or_credit} for completed ride with booking ID: #{$booking_id}. Customer used coupon discount of {$coupon_discount_value}%."; 
                }else{
                    $transaction_msg = "{$debit_or_credit} for completed ride with booking ID: #{$booking_id}. Customer used coupon flat fare price."; 
                } 
            }

            $transaction_id = crypto_string();
            $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
            '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
            DB_TBL_PREFIX,
            $city_currency_symbol,
            $city_currency_exchange,
            $city_currency_code,
            $booking_id,
            $transaction_id,
            $owner_franchise_earning,
            $owner_franchise_earning_converted + $owner_wallet_amount,
            1,
            2, //2 = franchise
            mysqli_real_escape_string($GLOBALS['DB'],$transaction_msg), 
            $transaction_type,
            gmdate('Y-m-d H:i:s', time())

            );

            if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                $error = array("error"=>__("An error has occured"));
                echo json_encode($error); 
                exit;
            }


        }else{
            $owner_and_franchise_earning = $booking_cost - $driver_earning;
            
            $other_franchise_earning = $owner_and_franchise_earning * $franchise_commision / 100;
            $other_franchise_earning_converted = ((float) $other_franchise_earning / $city_currency_exchange);

            $owner_franchise_earning = $owner_and_franchise_earning - $other_franchise_earning;
            $deficit_due_to_discounts = $amount_paid_by_rider - $ride_fare; //calculate deficit due to discounts if any. should be deducted from owner franchisw wallet
            $owner_franchise_earning = $deficit_due_to_discounts + $owner_franchise_earning; //add gains 
            
            $owner_franchise_earning_converted = ((float) $owner_franchise_earning / $city_currency_exchange);

            //update database records of owner franchise wallet
            $query = sprintf('UPDATE %stbl_franchise SET fwallet_amount = fwallet_amount + %f WHERE id = "%d"', DB_TBL_PREFIX, $owner_franchise_earning_converted,1);
            if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                $error = array("error"=>__("An error has occured"));
                echo json_encode($error); 
                exit;
            }

            //Add this transaction to wallet transactions database table
            
            if($owner_franchise_earning < 0){
                $debit_or_credit = "Debit";
                $transaction_type = 3; //denotes debit
            }else{
                $debit_or_credit = "Credit";
                $transaction_type = 2; //denotes earning / credit
            }

            $transaction_msg = "{$debit_or_credit} for ride completion after deducting driver and franchise commission, for booking: #{$booking_id}";
            
            if($referral_used && $coupon_used){                
                if($coupon_discount_type == 0){ //percentage coupon discount
                    $transaction_msg = "{$debit_or_credit} for completed ride with booking ID: #{$booking_id}. Customer referral discount of {$referral_discount_value}% followed by a coupon discount of {$coupon_discount_value}% was applied."; 
                }else{
                    $transaction_msg = "{$debit_or_credit} for completed ride with booking ID: #{$booking_id}. Customer referral discount and Coupon flat fare price was used."; 
                }   
                
            }elseif($referral_used){
                
                $transaction_msg = "{$debit_or_credit} for completed ride with booking ID: #{$booking_id}. Customer referral discount of {$referral_discount_value}% was applied."; 
            }elseif($coupon_used){
                
                if($coupon_discount_type == 0){ //percentage coupon discount
                    $transaction_msg = "{$debit_or_credit} for completed ride with booking ID: #{$booking_id}. Customer used coupon discount of {$coupon_discount_value}%."; 
                }else{
                    $transaction_msg = "{$debit_or_credit} for completed ride with booking ID: #{$booking_id}. Customer used coupon flat fare price."; 
                } 
            }

            $transaction_id = crypto_string();
            $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
            '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
            DB_TBL_PREFIX,
            $city_currency_symbol,
            $city_currency_exchange,
            $city_currency_code,
            $booking_id,
            $transaction_id,
            $owner_franchise_earning,
            $owner_franchise_earning_converted + $owner_wallet_amount,
            1,
            2, //2 = franchise
            mysqli_real_escape_string($GLOBALS['DB'],$transaction_msg), 
            $transaction_type,
            gmdate('Y-m-d H:i:s', time())

            );

            if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                $error = array("error"=>__("An error has occured"));
                echo json_encode($error); 
                exit;
            }



            //update database records of other franchise wallet
            $query = sprintf('UPDATE %stbl_franchise SET fwallet_amount = fwallet_amount + %f WHERE id = "%d"', DB_TBL_PREFIX, $other_franchise_earning_converted,$booking_data['franchise_id']);
            if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                $error = array("error"=>__("An error has occured"));
                echo json_encode($error); 
                exit;
            }

            //Add this transaction to wallet transactions database table
            $transaction_id = crypto_string();
            $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
            '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
            DB_TBL_PREFIX,
            $city_currency_symbol,
            $city_currency_exchange,
            $city_currency_code,
            $booking_id,
            $transaction_id,
            $other_franchise_earning,
            $other_franchise_earning_converted + $franchise_wallet_amount,
            $booking_data['franchise_id'],
            2, //2 = franchise
            "Ride completion earning ({$franchise_commision}%) after deducting driver commission, for booking: #" . $booking_id, 
            2,
            gmdate('Y-m-d H:i:s', time())

            );

            if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                $error = array("error"=>__("An error has occured"));
                echo json_encode($error); 
                exit;
            }


        }

        //update database records of driver wallet
        $query = sprintf('UPDATE %stbl_drivers SET wallet_amount = wallet_amount + %f WHERE driver_id = "%d"', DB_TBL_PREFIX, $driver_earning_converted,$_SESSION['uid']);
        if(!$result = mysqli_query($GLOBALS['DB'],$query)){
            mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); 
            exit;
        }

        //Add this transaction to wallet transactions database table
        $transaction_id = crypto_string();
        $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
        '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
        DB_TBL_PREFIX,
        $city_currency_symbol,
        $city_currency_exchange,
        $city_currency_code,
        $booking_id,
        $transaction_id,
        $driver_earning,
        $driver_earning_converted + $driver_wallet_amount,
        $_SESSION['uid'],
        1,
        __("Ride completion earning for booking: {---1}",["#{$booking_id}"]), 
        2,
        gmdate('Y-m-d H:i:s', time())

        );

        if(!$result = mysqli_query($GLOBALS['DB'],$query)){
            mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
            $error = array("error"=>__("An error has occured"));
            echo json_encode($error); 
            exit;
        }

        


    }elseif($booking_data['payment_type'] == 1){ //customer paid by cash to driver; deduct owner and franchise commision from driver wallet

        //compute driver and franchise earning
        $driver_earning = $booking_cost * $driver_commision / 100;

        //calculate any balance payment due to coupon or referral discounts to driver wallet
        $driver_balance_due_to_discounts = $driver_earning - $amount_paid_by_rider; //calculate balance due to discounts if any.
        $driver_balance_due_to_discounts_converted = ((float) $driver_balance_due_to_discounts / $city_currency_exchange);
        
        $owner_franchise_earning = 0.00;
        $other_franchise_earning = 0.00;

        if($booking_data['franchise_id'] == 1){ //driver franchise owner franchise. owner franchise has ID = 1
            $owner_franchise_earning = $booking_cost - $driver_earning;
            $owner_franchise_earning_converted = ((float) $owner_franchise_earning / $city_currency_exchange);

            

            $query = sprintf('UPDATE %stbl_drivers SET wallet_amount = wallet_amount + %f WHERE driver_id = "%d"', DB_TBL_PREFIX, $driver_balance_due_to_discounts_converted,$_SESSION['uid']);
            if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                $error = array("error"=>__("An error has occured"));
                echo json_encode($error); 
                exit;
            }

            $driver_wallet_amount = $driver_wallet_amount + $driver_balance_due_to_discounts_converted;

            if($driver_balance_due_to_discounts_converted < 0){ //debit 
                //Add this transaction to wallet transactions database table
                $transaction_id = crypto_string();
                $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                DB_TBL_PREFIX,
                $city_currency_symbol,
                $city_currency_exchange,
                $city_currency_code,
                $booking_id,
                $transaction_id,
                $driver_balance_due_to_discounts,
                $driver_wallet_amount,
                $_SESSION['uid'],
                1,
                __("Balance deduction for cash payment for booking: {---1}",["#$booking_id"]), 
                3, //debit
                gmdate('Y-m-d H:i:s', time())

                );

                if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                    mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                    $error = array("error"=>__("An error has occured"));
                    echo json_encode($error); 
                    exit;
                }

            }elseif($driver_balance_due_to_discounts_converted > 0){ //credit
                //Add this transaction to wallet transactions database table
                $transaction_id = crypto_string();
                $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                DB_TBL_PREFIX,
                $city_currency_symbol,
                $city_currency_exchange,
                $city_currency_code,
                $booking_id,
                $transaction_id,
                $driver_balance_due_to_discounts,
                $driver_wallet_amount,
                $_SESSION['uid'],
                1,
                __("Balance payment as a result of rider discounts for booking: {---1}",["#$booking_id"]), 
                2,//credit
                gmdate('Y-m-d H:i:s', time())

                );

                if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                    mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                    $error = array("error"=>__("An error has occured"));
                    echo json_encode($error); 
                    exit;
                }

            }else{
                //update database records of driver wallet. deduct owner commision
                $query = sprintf('UPDATE %stbl_drivers SET wallet_amount = wallet_amount - %f WHERE driver_id = "%d"', DB_TBL_PREFIX, $owner_franchise_earning_converted,$_SESSION['uid']);
                if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                    mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                    $error = array("error"=>__("An error has occured"));
                    echo json_encode($error); 
                    exit;
                }


                //Add this transaction to wallet transactions database table
                $transaction_id = crypto_string();
                $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                DB_TBL_PREFIX,
                $city_currency_symbol,
                $city_currency_exchange,
                $city_currency_code,
                $booking_id,
                $transaction_id,
                $owner_franchise_earning,
                $driver_wallet_amount - $owner_franchise_earning_converted,
                $_SESSION['uid'],
                1,
                __("Service charge deduction on ride completion for booking: {---1}",["#$booking_id"]), 
                3,
                gmdate('Y-m-d H:i:s', time())

                );

                if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                    mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                    $error = array("error"=>__("An error has occured"));
                    echo json_encode($error); 
                    exit;
                }
            }

            


            
            
            

            

            
            $deficit_due_to_discounts = $amount_paid_by_rider - $ride_fare; //calculate deficit due to discounts if any. should be deducted from owner franchisw wallet
            $owner_franchise_earning = $deficit_due_to_discounts + $owner_franchise_earning; //add gains 
            
            $owner_franchise_earning_converted = ((float) $owner_franchise_earning / $city_currency_exchange);

            //update database records of owner franchise wallet
            $query = sprintf('UPDATE %stbl_franchise SET fwallet_amount = fwallet_amount + %f WHERE id = "%d"', DB_TBL_PREFIX, $owner_franchise_earning_converted,1);
            if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                $error = array("error"=>__("An error has occured"));
                echo json_encode($error); 
                exit;
            }

            //Add this transaction to wallet transactions database table
            $transaction_msg = "Ride completion earning after deducting driver commission, for booking: #{$booking_id}";
            if($owner_franchise_earning < 0){
                $debit_or_credit = "Debit";
                $transaction_type = 3; //denotes debit
            }else{
                $debit_or_credit = "Credit";
                $transaction_type = 2; //denotes earning / credit
            }

                        
            if($referral_used && $coupon_used){                
                if($coupon_discount_type == 0){ //percentage coupon discount
                    $transaction_msg = "{$debit_or_credit} for completed ride with booking ID: #{$booking_id}. Customer referral discount of {$referral_discount_value}% followed by a coupon discount of {$coupon_discount_value}% was applied."; 
                }else{
                    $transaction_msg = "{$debit_or_credit} for completed ride with booking ID: #{$booking_id}. Customer referral discount and Coupon flat fare price was used."; 
                }   
                
            }elseif($referral_used){
                
                $transaction_msg = "{$debit_or_credit} for completed ride with booking ID: #{$booking_id}. Customer referral discount of {$referral_discount_value}% was applied."; 
            }elseif($coupon_used){
                
                if($coupon_discount_type == 0){ //percentage coupon discount
                    $transaction_msg = "{$debit_or_credit} for completed ride with booking ID: #{$booking_id}. Customer used coupon discount of {$coupon_discount_value}%."; 
                }else{
                    $transaction_msg = "{$debit_or_credit} for completed ride with booking ID: #{$booking_id}. Customer used coupon flat fare price."; 
                } 
            }

            $transaction_id = crypto_string();
            $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
            '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
            DB_TBL_PREFIX,
            $city_currency_symbol,
            $city_currency_exchange,
            $city_currency_code,
            $booking_id,
            $transaction_id,
            $owner_franchise_earning,
            $owner_franchise_earning_converted + $owner_wallet_amount,
            1,
            2, //2 = franchise
            mysqli_real_escape_string($GLOBALS['DB'],$transaction_msg), 
            $transaction_type,
            gmdate('Y-m-d H:i:s', time())

            );

            if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                $error = array("error"=>__("An error has occured"));
                echo json_encode($error); 
                exit;
            }

        }else{


            $owner_and_franchise_earning = $booking_cost - $driver_earning;
            $owner_and_franchise_earning_converted = ((float) $owner_and_franchise_earning / $city_currency_exchange);

            $other_franchise_earning = $owner_and_franchise_earning * $franchise_commision / 100;
            $other_franchise_earning_converted = ((float) $other_franchise_earning / $city_currency_exchange);

            
            //update database records of other franchise wallet
            $query = sprintf('UPDATE %stbl_franchise SET fwallet_amount = fwallet_amount + %f WHERE id = "%d"', DB_TBL_PREFIX, $other_franchise_earning_converted,$booking_data['franchise_id']);
            if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                $error = array("error"=>__("An error has occured"));
                echo json_encode($error); 
                exit;
            }

            //Add this transaction to wallet transactions database table
            $transaction_id = crypto_string();
            $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
            '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
            DB_TBL_PREFIX,
            $city_currency_symbol,
            $city_currency_exchange,
            $city_currency_code,
            $booking_id,
            $transaction_id,
            $other_franchise_earning,
            $other_franchise_earning_converted + $franchise_wallet_amount,
            $booking_data['franchise_id'],
            2, //2 = franchise
            "Ride completion earning ({$franchise_commision}%) for booking: #" . $booking_id, 
            2,
            gmdate('Y-m-d H:i:s', time())

            );

            if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                $error = array("error"=>__("An error has occured"));
                echo json_encode($error); 
                exit;
            }

            

            $query = sprintf('UPDATE %stbl_drivers SET wallet_amount = wallet_amount + %f WHERE driver_id = "%d"', DB_TBL_PREFIX, $driver_balance_due_to_discounts_converted,$_SESSION['uid']);
            if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                $error = array("error"=>__("An error has occured"));
                echo json_encode($error); 
                exit;
            }

            $driver_wallet_amount = $driver_wallet_amount + $driver_balance_due_to_discounts_converted;

            if($driver_balance_due_to_discounts_converted < 0){ //debit
                //Add this transaction to wallet transactions database table
                $transaction_id = crypto_string();
                $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                DB_TBL_PREFIX,
                $city_currency_symbol,
                $city_currency_exchange,
                $city_currency_code,
                $booking_id,
                $transaction_id,
                $driver_balance_due_to_discounts,
                $driver_wallet_amount,
                $_SESSION['uid'],
                1,
                __("Balance deduction for cash payment for booking: {---1}",["#$booking_id"]), 
                3, //debit
                gmdate('Y-m-d H:i:s', time())

                );

                if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                    mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                    $error = array("error"=>__("An error has occured"));
                    echo json_encode($error); 
                    exit;
                }

            }elseif($driver_balance_due_to_discounts_converted > 0){ //credit
                //Add this transaction to wallet transactions database table
                $transaction_id = crypto_string();
                $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                DB_TBL_PREFIX,
                $city_currency_symbol,
                $city_currency_exchange,
                $city_currency_code,
                $booking_id,
                $transaction_id,
                $driver_balance_due_to_discounts,
                $driver_wallet_amount,
                $_SESSION['uid'],
                1,
                __("Balance payment as a result of rider discounts for booking: {---1}",["#$booking_id"]), 
                2,//credit
                gmdate('Y-m-d H:i:s', time())

                );

                if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                    mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                    $error = array("error"=>__("An error has occured"));
                    echo json_encode($error); 
                    exit;
                }
            }else{

                //update database records of driver wallet. deduct owner and franchise commision
                $query = sprintf('UPDATE %stbl_drivers SET wallet_amount = wallet_amount - %f WHERE driver_id = "%d"', DB_TBL_PREFIX, $owner_and_franchise_earning_converted,$_SESSION['uid']);
                if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                    mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                    $error = array("error"=>__("An error has occured"));
                    echo json_encode($error); 
                    exit;
                }

                //Add this transaction to wallet transactions database table
                $transaction_id = crypto_string();
                $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
                '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
                DB_TBL_PREFIX,
                $city_currency_symbol,
                $city_currency_exchange,
                $city_currency_code,
                $booking_id,
                $transaction_id,
                $owner_and_franchise_earning,
                $driver_wallet_amount - $owner_and_franchise_earning_converted,
                $_SESSION['uid'],
                1,
                __("Service charge deduction on ride completion for booking: {---1}",["#$booking_id"]), 
                3,
                gmdate('Y-m-d H:i:s', time())

                );

                if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                    mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                    $error = array("error"=>__("An error has occured"));
                    echo json_encode($error); 
                    exit;
                }

            }           

            


            $owner_franchise_earning = $owner_and_franchise_earning - $other_franchise_earning;
            $deficit_due_to_discounts = $amount_paid_by_rider - $ride_fare; //calculate deficit due to discounts if any. should be deducted from owner franchisw wallet
            $owner_franchise_earning = $deficit_due_to_discounts + $owner_franchise_earning; //add gains 

            $owner_franchise_earning_converted = ((float) $owner_franchise_earning / $city_currency_exchange);

            //update database records of owner franchise wallet
            $query = sprintf('UPDATE %stbl_franchise SET fwallet_amount = fwallet_amount + %f WHERE id = "%d"', DB_TBL_PREFIX, $owner_franchise_earning_converted,1);
            if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                $error = array("error"=>__("An error has occured"));
                echo json_encode($error); 
                exit;
            }

            //Add this transaction to wallet transactions database table
            if($owner_franchise_earning < 0){
                $debit_or_credit = "Debit";
                $transaction_type = 3; //denotes debit
            }else{
                $debit_or_credit = "Credit";
                $transaction_type = 2; //denotes earning / credit
            }

            $transaction_msg = "{$debit_or_credit} for ride completion after deducting driver and franchise commission, for booking: #{$booking_id}";
            
            if($referral_used && $coupon_used){                
                if($coupon_discount_type == 0){ //percentage coupon discount
                    $transaction_msg = "{$debit_or_credit} for completed ride with booking ID: #{$booking_id}. Customer referral discount of {$referral_discount_value}% followed by a coupon discount of {$coupon_discount_value}% was applied."; 
                }else{
                    $transaction_msg = "{$debit_or_credit} for completed ride with booking ID: #{$booking_id}. Customer referral discount and Coupon flat fare price was used."; 
                }   
                
            }elseif($referral_used){
                
                $transaction_msg = "{$debit_or_credit} for completed ride with booking ID: #{$booking_id}. Customer referral discount of {$referral_discount_value}% was applied."; 
            }elseif($coupon_used){
                
                if($coupon_discount_type == 0){ //percentage coupon discount
                    $transaction_msg = "{$debit_or_credit} for completed ride with booking ID: #{$booking_id}. Customer used coupon discount of {$coupon_discount_value}%."; 
                }else{
                    $transaction_msg = "{$debit_or_credit} for completed ride with booking ID: #{$booking_id}. Customer used coupon flat fare price."; 
                } 
            }
            
            $transaction_id = crypto_string();
            $query = sprintf('INSERT INTO %stbl_wallet_transactions (cur_symbol,cur_exchng_rate,cur_code,book_id,transaction_id,amount,wallet_balance,user_id,user_type,`desc`,`type`,transaction_date) VALUES'.
            '("%s","%s","%s","%d","%s","%s","%s","%d","%d","%s","%d","%s")', 
            DB_TBL_PREFIX,
            $city_currency_symbol,
            $city_currency_exchange,
            $city_currency_code,
            $booking_id,
            $transaction_id,
            $owner_franchise_earning,
            $owner_franchise_earning_converted + $owner_wallet_amount,
            1,
            2, //2 = franchise
            mysqli_real_escape_string($GLOBALS['DB'],$transaction_msg), 
            $transaction_type,
            gmdate('Y-m-d H:i:s', time())

            );

            if(!$result = mysqli_query($GLOBALS['DB'],$query)){
                mysqli_query($GLOBALS['DB'],'ROLLBACK'); //rollback changes
                $error = array("error"=>__("An error has occured"));
                echo json_encode($error); 
                exit;
            }


        }

    }


    mysqli_query($GLOBALS['DB'],'COMMIT'); //all good. commit all database changes

    
    saveTravelRouteData($booking_id, $_SESSION['uid']);

    

    //send push notification to user
    
    $booking_title = str_pad($booking_id , 5, '0', STR_PAD_LEFT);
    $title = WEBSITE_NAME . " - " . __("Ride completed",[],"d|{$rlang}");
    $body = __("Your ride with booking ID({---1}) was completed successfully",["{$booking_title}"],"d|{$rlang}");
    $device_tokens = $booking_data['push_token'];
    if(!empty($device_tokens)){
        sendPushNotification($title,$body,$device_tokens,NULL,0);
    }

    
    //send silent push notifications processed within app.

    
    $photo_file = isset($booking_data['driver_photo']) ? $booking_data['driver_photo'] : "0";


    $booking_title = str_pad($booking_id , 5, '0', STR_PAD_LEFT);
    $title = WEBSITE_NAME . " - " . __("Ride completed",[],"d|{$rlang}");
    $body = __("Your ride with booking ID({---1}) was completed successfully",["{$booking_title}"],"d|{$rlang}");
    $device_tokens = $booking_data['push_token'];
    $data = array(
                    "action"=>"driver-complete",
                    "booking_id" => $booking_id,
                    "driver_firstname" => $booking_data['driver_firstname'],
                    "pickup_addr"=>$booking_data['pickup_address'],
                    "dropoff_addr"=>$booking_data['dropoff_address'],
                    "driver_photo" => SITE_URL . "ajaxphotofile.php?file=" . $photo_file,
                    "ride_amount" => $city_currency_symbol . $amount_paid_by_rider,
                    "ride_duration"=> $ride_duration_formated,
                    "ride_distance"=>$distance_travelled / 1000,
                    "reward_points_earned" => $points_earned,
                    "coupon_used" => $coupon_used,
                    "coupon_type" => $coupon_discount_type,
                    "coupon_val" => $coupon_discount_value,
                    "referral_used" => $referral_used,
                    "referral_discount" => $referral_discount_value,
                    "city_currency_symbol" => $city_currency_symbol,
                    "payment_method" => $booking_data['payment_type']

                );
    if(!empty($device_tokens)){
        sendPushNotification($title,$body,$device_tokens,$data);
    }

    //notify user through realtime notification
    sendRealTimeNotification('ridr-' . $booking_data['customer_id'], $data);


    

    
    //add to user DB notification
    $content = __("Your ride with booking ID({---1}) was completed successfully",["{$booking_title}"],"d|{$rlang}");
    $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
    ("%d",0,"%s",4,"%s")', 
    DB_TBL_PREFIX,
    $booking_data['customer_id'],
    mysqli_real_escape_string($GLOBALS['DB'],$content),
    gmdate('Y-m-d H:i:s', time()) 
    ); 
    $result = mysqli_query($GLOBALS['DB'], $query);


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




function getpersoninfo(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $booking_id = (int) $_GET['booking_id'];

    $query = sprintf('SELECT %1$stbl_users.user_id, %1$stbl_users.firstname,%1$stbl_users.lastname, %1$stbl_users.user_rating, %1$stbl_users.account_create_date,%1$stbl_users.completed_rides,%1$stbl_users.cancelled_rides, %1$stbl_users.photo_file FROM %1$stbl_bookings 
    INNER JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
    WHERE %1$stbl_bookings.id = %2$d', DB_TBL_PREFIX, $booking_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $user_data = mysqli_fetch_assoc($result);
            $user_data['lastname'] = "";
            $comments_ratings = '';
            $photo_file = isset($user_data['photo_file']) ? $user_data['photo_file'] : "0";
            //get customer comments for this rider from drivers
            $query = sprintf('SELECT %1$stbl_drivers.firstname, %1$stbl_ratings_drivers.driver_comment, %1$stbl_ratings_drivers.driver_rating FROM %1$stbl_ratings_drivers 
            INNER JOIN %1$stbl_bookings ON %1$stbl_bookings.id = %1$stbl_ratings_drivers.booking_id
            INNER JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_ratings_drivers.driver_id
            WHERE %1$stbl_bookings.user_id = %2$d ORDER BY %1$stbl_ratings_drivers.id DESC LIMIT 100', DB_TBL_PREFIX, $user_data['user_id']);

            if($result = mysqli_query($GLOBALS['DB'], $query)){
                if(mysqli_num_rows($result)){
                    while($row = mysqli_fetch_assoc($result)){
                        $comments_ratings.= "<div style='padding: 10px 5px;border-top:thin solid #ccc;'> <div><span style='font-size:12px;font-weight:bold;margin-bottom:5px;'>{$row['firstname']}</span></div> <div><img src='img/rating-{$row['driver_rating']}.png' style='width:50px;' /></div> <div><span style='font-size:14px;'>{$row['driver_comment']}</span></div> </div>"; 
                    }

                    $comments_ratings = "<div style='font-size:14px;font-weight:bold;padding: 5px 5px;text-align:left;'>" . __("Comments and Ratings") . "</div>" . $comments_ratings;
                }

            }

            if(empty($comments_ratings)){
                $comments_ratings = "<div style='padding: 50px 5px;text-align:center;'>" . __("No comments and ratings available") . "</div>";
            }

            $data = array("success"=> 1, "userdata" => $user_data, 'comments' => $comments_ratings, 'photo' => SITE_URL . "ajaxuserphotofile.php?file=". $photo_file);
            echo json_encode($data); 
            exit;


        }
    }

    $error = array("error"=>1);
    echo json_encode($error); 
    exit;


}





function getDriverOnride(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please re-login and retry.");
        echo json_encode($error); 
        exit; 
    }

    $booking_onride = [];
    $booking_pending = [];
    $booking_accepted = [];
    $data = [];
    
    $query = sprintf('SELECT *, DATE(%1$stbl_bookings.date_created) AS created_date,%1$stbl_bookings.id AS booking_id,%1$stbl_bookings.driver_id AS b_driver_id,%1$stbl_bookings.status AS b_status,%1$stbl_driver_allocate.status AS a_status FROM %1$stbl_bookings     
    LEFT JOIN %1$stbl_driver_allocate ON %1$stbl_driver_allocate.booking_id = %1$stbl_bookings.id
    LEFT JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
    WHERE %1$stbl_bookings.driver_id = %2$d AND (%1$stbl_bookings.status = 0 OR %1$stbl_bookings.status = 1)  OR (%1$stbl_driver_allocate.driver_id = %2$d AND %1$stbl_driver_allocate.status = 0) ORDER BY %1$stbl_bookings.date_created  
    DESC LIMIT 0,10', DB_TBL_PREFIX,$_SESSION['uid']);
    

    if($result = mysqli_query($GLOBALS['DB'], $query)){
       
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                $data[] = $row;
                if($row['b_status'] == 1){     //onride
                    $booking_onride[$row['created_date']]['date'] = $row['created_date'];
                    $booking_onride[$row['created_date']]['onride'][] = $row;
                }
          
                if($row['b_status'] == 0 && $row['b_driver_id'] == $_SESSION['uid']){ //driver accepted ride and is on way to pickup
                    $booking_accepted[$row['created_date']]['date'] = $row['created_date'];
                    $booking_accepted[$row['created_date']]['accepted'][] = $row;
                }

                if($row['a_status'] == 0 && $row['b_driver_id'] == 0){ //pending accept rides
                    $booking_pending[$row['created_date']]['date'] = $row['created_date'];
                    $booking_pending[$row['created_date']]['pending'][] = $row;
                }
             }
            //  echo json_encode($booking_cancelled); 
            mysqli_free_result($result);
        }

    }else{

        $error = array("error"=>"Error retrieving job records.");
        echo json_encode($error); 
        exit; 

    }

    
    /* var_dump($data);
    exit; */
   
    //format for display on app
    $booking_onride_str = '';
    $booking_pend_str = '';
    $booking_accept_str = '';

    foreach($booking_onride as $bookingdatasort){
      //save date
        $b_date_format = date('l, M j, Y',strtotime($bookingdatasort['date'] . " UTC"));
        $booking_onride_str .= "<ons-list-header style='font-size: 14px;font-weight:bold;margin:20px 0;'>{$b_date_format}</ons-list-header>";
        
        //format pending onride rides for this date
        foreach($bookingdatasort['onride'] as $bookingdatasort_canc){
            $booking_time = date('g:i A',strtotime($bookingdatasort_canc['date_created'] . ' UTC'));
            $booking_ptime = date('g:i A',strtotime($bookingdatasort_canc['pickup_datetime'] . ' UTC'));
            $booking_pdate_time = date('d/m/Y g:i A',strtotime($bookingdatasort_canc['pickup_datetime'] . ' UTC'));
            $booking_payment_type = '';
            $booking_fare = $bookingdatasort_canc['cur_symbol'].$bookingdatasort_canc['estimated_cost'];
            if(!empty($bookingdatasort_canc['payment_type'])){
                if($bookingdatasort_canc['payment_type'] == 1){
                    $booking_payment_type = "Cash";
                }elseif($bookingdatasort_canc['payment_type'] == 2){
                    $booking_payment_type = "Wallet";
                }else{
                    $booking_payment_type = "Card";
                }
            }
            $user_photo = explode('/',$bookingdatasort_canc['photo_file']);
            $user_photo_file = isset($user_photo[5]) ? SITE_URL . "ajaxuserphotofile.php?file=".$user_photo[5] : SITE_URL . "ajaxuserphotofile.php?file=0";
            $booking_title = str_pad($bookingdatasort_canc['booking_id'] , 5, '0', STR_PAD_LEFT);
            $booking_onride_str .= "<ons-list-item id='booking-list-item-{$bookingdatasort_canc['booking_id']}' modifier='longdivider'>
                  
                                        <div class='center'>
                                                <div style='width:100%;'><span class='list-item__title'>
                                                    <div style='border-bottom: thin solid #ccc;margin-bottom:10px;padding-bottom: 5px;'>{$booking_time}</div>
                                                    <div style='display:inline-block;width:80px;height:80px;float:left;margin-bottom:5px;margin-left:20px;background-image:url({$user_photo_file});background-size:cover;border-radius:50%;margin-right:10px;'></div>
                                                    <div style='display:inline-block;'>
                                                        <p style='margin:0;'>{$bookingdatasort_canc['user_firstname']}</p>
                                                        <p style='margin:0;'><img style='width:80px;' src='img/rating-{$bookingdatasort_canc['user_rating']}.png' /></p>
                                                        <div>
                                                            <ons-button style='border: thin solid;' modifier='outline' onclick=call_rider('{$bookingdatasort_canc['user_phone']}')><ons-icon icon='fa-phone' ></ons-icon></ons-button>
                                                            <ons-button style='border: thin solid;' modifier='outline' onclick=sms_rider('{$bookingdatasort_canc['user_phone']}')><ons-icon icon='fa-envelope' ></ons-icon></ons-button>
                                                            <ons-button style='border: thin solid;color: red;' modifier='outline' onclick=dropoffmap(0,{$bookingdatasort_canc['dropoff_long']},{$bookingdatasort_canc['dropoff_lat']})><ons-icon icon='fa-map-marker' ></ons-icon></ons-button>
                                                            <ons-button style='border: thin solid;color: red;' modifier='outline' onclick=dropoffmap(1,{$bookingdatasort_canc['dropoff_long']},{$bookingdatasort_canc['dropoff_lat']})><ons-icon icon='fa-location-arrow' ></ons-icon></ons-button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span style='text-align: left;margin-bottom: 15px;margin-left:20px;' class='list-item__subtitle'><span>Booking ID:#{$booking_title}</span> | <span>Fare:#{$booking_fare}</span> <span>({$booking_payment_type})</span> </span>                               
                                                
                                                
                                                
                                                <span class='list-item__subtitle' style='margin-bottom:5px;'><ons-icon icon='fa-circle' size='14px' style='color: #24c539; font-size: 14px;position: absolute;'></ons-icon> <span style='display:inline-block;margin-left:22px;font-weight:bold;'>{$bookingdatasort_canc['pickup_address']}</span></span>
                                                <span class='list-item__subtitle' ><ons-icon icon='fa-map-marker' size='14px' style='color: red; font-size: 14px;position: absolute;'></ons-icon> <span style='display:inline-block;margin-left:22px;font-weight:bold;'>{$bookingdatasort_canc['dropoff_address']}</span></span>
                                                
                                                
                                                <span class='list-item__subtitle' style='margin-top: 10px;border-top: thin solid grey;padding-top: 10px;'><ons-button onclick=driverendride({$bookingdatasort_canc['booking_id']}) style='width:48%;color:white;font-size: 16px;'> COMPLETE</ons-button> <ons-button onclick='onclick=drivercancel({$bookingdatasort_canc['booking_id']})' style='width:48%;color:white;font-size: 16px;background-color:black'> CANCEL</ons-button></span>
                                                    
                                                    
                                                
                                                
                                        </div>
                                    
                                    </ons-list-item>";

            //$booking_accept_str .= " <br> <div style='width:100%;text-align: center'> You are on a ride to drop off.<br>select ON RIDE tab to view it. </div><br>";
                           
        }

    }
    foreach($booking_pending as $bookingdatasort){
        //save date
        $b_date_format = date('l, M j, Y',strtotime($bookingdatasort['date'] . " UTC"));
          $booking_pend_str .= "<ons-list-header style='font-size: 14px;font-weight:bold;margin:20px 0;'>{$b_date_format}</ons-list-header>";
          
          //format pending onride rides for this date
          foreach($bookingdatasort['pending'] as $bookingdatasort_canc){
              $booking_time = date('g:i A',strtotime($bookingdatasort_canc['date_created'] . ' UTC'));
              $booking_ptime = date('g:i A',strtotime($bookingdatasort_canc['pickup_datetime'] . ' UTC'));
              $booking_pdate_time = date('d/m/Y g:i A',strtotime($bookingdatasort_canc['pickup_datetime'] . ' UTC'));
              $booking_payment_type = '';
              if(!empty($bookingdatasort_canc['payment_type'])){
                    if($bookingdatasort_canc['payment_type'] == 1){
                        $booking_payment_type = "Cash";
                    }elseif($bookingdatasort_canc['payment_type'] == 2){
                        $booking_payment_type = "Wallet";
                    }else{
                        $booking_payment_type = "Card";
                    }
                }
              $booking_title = str_pad($bookingdatasort_canc['booking_id'] , 5, '0', STR_PAD_LEFT);
              $booking_pend_str .= "<ons-list-item id='booking-list-item-{$bookingdatasort_canc['booking_id']}' modifier='longdivider'>
              
                                        <div class='center'>
                                            <div style='width:100%;'><span class='list-item__title'>{$booking_time}</div>
                                            <span style='text-align: left;margin-bottom: 15px;' class='list-item__subtitle'><span>Booking ID:#{$booking_title}</span></span>                               
                                                                                                                                    
                                            <span class='list-item__subtitle' style='margin-bottom:5px;'><ons-icon icon='fa-circle' size='14px' style='color: #24c539; font-size: 14px;position: absolute;'></ons-icon> <span style='display:inline-block;margin-left:22px;font-weight:bold;'>{$bookingdatasort_canc['pickup_address']}</span></span>
                                            <span class='list-item__subtitle'><ons-icon icon='fa-map-marker' size='14px' style='color: red; font-size: 14px;position: absolute;'></ons-icon> <span style='display:inline-block;margin-left:22px;font-weight:bold;'>{$bookingdatasort_canc['dropoff_address']}</span></span>
                                            <hr/>
                                            <span class='list-item__subtitle' style='margin-top: 10px;border-top: thin solid grey;padding-top: 10px;'><ons-button onclick='driveracceptride({$bookingdatasort_canc['booking_id']})' style='width:48%;color:white;font-size: 16px;'> ACCEPT</ons-button> <ons-button onclick=drivercancel({$bookingdatasort_canc['booking_id']}) style='width:48%;color:white;font-size: 16px;background-color:black'> CANCEL</ons-button></span>
                                        
                                        </div> 
                                      </ons-list-item>
                                      ";
//   <ons-dialog cancelable id='showoptions'><br><span class='list-item__subtitle'><span style='color:cyan'>Payment Type: </span>{$booking_payment_type}</span>
//                                       Ride Menu
//                                       <br>
//                                       <div style='text-align: center'>
//                                       <ons-button onclick=driverstartride()> start ride <ons-button>
//                                       </div>
//                                   </ons-dialog> 
        }
  
    }
    
    foreach($booking_accepted as $bookingdatasort){
            //save date
            $b_date_format = date('l, M j, Y',strtotime($bookingdatasort['date'] . " UTC"));
              $booking_accept_str .= "<ons-list-header style='font-size: 14px;font-weight:bold;margin:20px 0;'>{$b_date_format}</ons-list-header>";
              
              //format pending onride rides for this date
              foreach($bookingdatasort['accepted'] as $bookingdatasort_canc){
                  $booking_time = date('g:i A',strtotime($bookingdatasort_canc['date_created'] . ' UTC'));
                  $booking_ptime = date('g:i A',strtotime($bookingdatasort_canc['pickup_datetime'] . ' UTC'));
                  $booking_pdate_time = date('d/m/Y g:i A',strtotime($bookingdatasort_canc['pickup_datetime'] . ' UTC'));
                  $booking_payment_type = '';
                  $booking_fare = $bookingdatasort_canc['cur_symbol'].$bookingdatasort_canc['estimated_cost'];
                  if(!empty($bookingdatasort_canc['payment_type'])){
                        if($bookingdatasort_canc['payment_type'] == 1){
                            $booking_payment_type = "Cash";
                        }elseif($bookingdatasort_canc['payment_type'] == 2){
                            $booking_payment_type = "Wallet";
                        }else{
                            $booking_payment_type = "Card";
                        }
                    }
                    
                    $user_photo = explode('/',$bookingdatasort_canc['photo_file']);
                    $user_photo_file = isset($user_photo[5]) ? SITE_URL . "ajaxuserphotofile.php?file=".$user_photo[5] : SITE_URL . "ajaxuserphotofile.php?file=0";
                  $booking_title = str_pad($bookingdatasort_canc['booking_id'] , 5, '0', STR_PAD_LEFT);
                  $booking_accept_str .= "<ons-list-item id='booking-list-item-{$bookingdatasort_canc['booking_id']}' modifier='longdivider'>
                  
                                            <div class='center'>
                                                    <div style='width:100%;'><span class='list-item__title'>
                                                        <div style='border-bottom: thin solid #ccc;margin-bottom:10px;padding-bottom: 5px;'>{$booking_time}</div>
                                                        <div style='display:inline-block;width:80px;height:80px;float:left;margin-bottom:5px;margin-left:20px;background-image:url({$user_photo_file});background-size:cover;border-radius:50%;margin-right:10px;'></div>
                                                        <div style='display:inline-block;'>
                                                            <p style='margin:0;'>{$bookingdatasort_canc['user_firstname']}</p>
                                                            <p style='margin:0;'><img style='width:80px;' src='img/rating-{$bookingdatasort_canc['user_rating']}.png' /></p>
                                                            <div>
                                                                <ons-button style='border: thin solid;' modifier='outline' onclick=call_rider('{$bookingdatasort_canc['user_phone']}')><ons-icon icon='fa-phone' ></ons-icon></ons-button>
                                                                <ons-button style='border: thin solid;' modifier='outline' onclick=sms_rider('{$bookingdatasort_canc['user_phone']}')><ons-icon icon='fa-envelope' ></ons-icon></ons-button>
                                                                <ons-button style='border: thin solid;color: #24c539;' modifier='outline' onclick=pickupmap(0,{$bookingdatasort_canc['pickup_long']},{$bookingdatasort_canc['pickup_lat']})><ons-icon icon='fa-map-marker' ></ons-icon></ons-button>
                                                                <ons-button style='border: thin solid;color: #24c539;' modifier='outline' onclick=pickupmap(1,{$bookingdatasort_canc['pickup_long']},{$bookingdatasort_canc['pickup_lat']})><ons-icon icon='fa-location-arrow' ></ons-icon></ons-button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span style='text-align: left;margin-bottom: 15px;margin-left:20px;' class='list-item__subtitle'><span>Booking ID:#{$booking_title}</span> | <span>Fare:#{$booking_fare}</span> <span>({$booking_payment_type})</span> </span>                               
                                                    
                                                    
                                                    
                                                    <span class='list-item__subtitle' style='margin-bottom:5px;'><ons-icon icon='fa-circle' size='14px' style='color: #24c539; font-size: 14px;position: absolute;'></ons-icon> <span style='display:inline-block;margin-left:22px;font-weight:bold;'>{$bookingdatasort_canc['pickup_address']}</span></span>
                                                    <span class='list-item__subtitle' style='padding-bottom: 10px;border-bottom: thin solid #ccc;margin-bottom: 10px;' ><ons-icon icon='fa-map-marker' size='14px' style='color: red; font-size: 14px;position: absolute;'></ons-icon> <span style='display:inline-block;margin-left:22px;font-weight:bold;'>{$bookingdatasort_canc['dropoff_address']}</span></span>
                                                    <span class='list-item__subtitle'>
                                                        <ons-button style='width:30%;' onclick=driverarrived({$bookingdatasort_canc['booking_id']})>Arrived</ons-button>
                                                        <ons-button style='width:30%;' onclick=driverstartride({$bookingdatasort_canc['booking_id']})>Onride</ons-button>
                                                        <ons-button style='width:30%;background-color: black;' onclick=drivercancel({$bookingdatasort_canc['booking_id']})>Cancel</ons-button>
                                                        
                                                    </span>
                                                    
                                            </div>
                                          
                                          </ons-list-item>";
      
              }
      
    }

    if (empty($booking_onride_str)){
        $booking_onride_str .= "  <div style='width:100%;text-align:center;position: absolute;top: 45%;'> No rides in progress </div> ";
    }
    if (empty($booking_accept_str)){
        $booking_accept_str .= "  <div style='width:100%;text-align: center;position: absolute;top: 45%;'> No accepted rides.</div> ";
    }
    if (empty($booking_pend_str)){
        $booking_pend_str .= "  <div style='width:100%;text-align: center;position: absolute;top: 45%;'> No allocated rides. </div> ";
    }
    $data_array = array("success"=>1,'booking_accepted'=>$booking_accept_str,'booking_onride'=>$booking_onride_str,'booking_pend'=>$booking_pend_str);    
    echo json_encode($data_array); 
    exit;

}



function acceptride(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $booking_id = (int) $_GET['bookingid'];
    $dlat = !empty(mysqli_real_escape_string($GLOBALS['DB'], $_GET['driver_lat'])) ? mysqli_real_escape_string($GLOBALS['DB'], $_GET['driver_lat']) : "";
    $dlng = !empty(mysqli_real_escape_string($GLOBALS['DB'], $_GET['driver_lng'])) ? mysqli_real_escape_string($GLOBALS['DB'], $_GET['driver_lng']) : "";
    $aux_bookings = [];
    $aux_bookings_ids = [];
    

    $query = sprintf('SELECT id,dropoff_long,dropoff_lat FROM %1$stbl_bookings WHERE driver_id = %2$s AND (`status` = 0 OR `status` = 1)' , DB_TBL_PREFIX,$_SESSION['uid']);
   
    if($result = mysqli_query($GLOBALS['DB'], $query)){ 
        if(mysqli_num_rows($result)){            
            while($row = mysqli_fetch_assoc($result)){
                $aux_bookings[] = $row;
                $aux_bookings_ids[] = $row['id'];
            }
        }
    }



    //verify the allocation is still available

    $query = sprintf('SELECT * FROM %1$stbl_driver_allocate 
    INNER JOIN %1$stbl_bookings ON %1$stbl_bookings.id = %1$stbl_driver_allocate.booking_id 
    WHERE %1$stbl_driver_allocate.booking_id = %2$d AND (%1$stbl_bookings.status = 2 OR %1$stbl_bookings.status = 5 OR %1$stbl_driver_allocate.status = 1)' , DB_TBL_PREFIX,$booking_id);
   
    if($result = mysqli_query($GLOBALS['DB'], $query)){ 
        if(mysqli_num_rows($result)){

            //update allocation record on DB to show driver attempted to accept the ride. This allows for an accurate trip rejection count
            $query = sprintf('UPDATE %stbl_driver_allocate SET `status` = 3 WHERE booking_id = "%d" AND driver_id = "%d"', DB_TBL_PREFIX,$booking_id,$_SESSION['uid'] );
            $result = mysqli_query($GLOBALS['DB'], $query);

            $error = array("error"=>__("This booking allocation has expired. you did not respond on time. Please always respond promptly when a booking is allocated to you."));
            echo json_encode($error); 
            exit;
        }
    }

    

    //Get push notification token of user for this booking

    $booking_data = [];

    $query = sprintf('SELECT %1$stbl_bookings.service_mode,%1$stbl_bookings.hr_set_num_h,%1$stbl_bookings.route_id AS b_route_id,%1$stbl_bookings.waypoint1_address,%1$stbl_bookings.waypoint1_long,%1$stbl_bookings.waypoint1_lat,%1$stbl_bookings.waypoint2_address,%1$stbl_bookings.waypoint2_long,%1$stbl_bookings.waypoint2_lat,%1$stbl_bookings.completion_code,%1$stbl_bookings.cur_exchng_rate,%1$stbl_bookings.estimated_cost,%1$stbl_bookings.payment_type,%1$stbl_users.push_notification_token AS push_token,%1$stbl_bookings.user_id AS customer_id,%1$stbl_bookings.pickup_address,%1$stbl_bookings.dropoff_address, %1$stbl_bookings.pickup_lat, %1$stbl_bookings.pickup_long, %1$stbl_bookings.dropoff_lat,%1$stbl_bookings.dropoff_long, %1$stbl_users.disp_lang AS u_lang FROM %1$stbl_bookings 
    INNER JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
    WHERE %1$stbl_bookings.id = "%2$d"', DB_TBL_PREFIX, $booking_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){

            $booking_data = mysqli_fetch_assoc($result);

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

    $rlang = $booking_data['u_lang'];
    


    //Get driver details

    $driver_data = [];

    $query = sprintf('SELECT *, %1$stbl_franchise.id AS franchise_id FROM %1$stbl_drivers 
    LEFT JOIN %1$stbl_franchise ON %1$stbl_franchise.id = %1$stbl_drivers.franchise_id
    LEFT JOIN %1$stbl_driver_location ON %1$stbl_driver_location.driver_id = %1$stbl_drivers.driver_id 
    WHERE %1$stbl_drivers.driver_id = "%2$d"', DB_TBL_PREFIX, $_SESSION['uid']);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $driver_data = mysqli_fetch_assoc($result);
        }
    }

    

    //Verify driver wallet is not below set amount and has sufficient fund in wallet for this ride for cash payment
    /* if(isset($booking_data['payment_type'])){
        if($driver_data['wallet_amount'] < 0 && $driver_data['wallet_amount'] < DRIVER_MIN_WALLET_BALANCE){

            //send driver offline and show the wallet topup page on the app
            $query = sprintf('UPDATE %stbl_drivers SET `available` = %d WHERE driver_id = %d', DB_TBL_PREFIX, 0, $_SESSION['uid']);
            if($result = mysqli_query($GLOBALS['DB'], $query)){
                $_SESSION['availability'] = 0;
            }

            //update allocation record on DB to show driver attempted to accept the ride. This allows for an accurate trip rejection count
            $query = sprintf('UPDATE %stbl_driver_allocate SET `status` = 3 WHERE booking_id = "%d" AND driver_id = "%d"', DB_TBL_PREFIX,$booking_id,$_SESSION['uid'] );
            $result = mysqli_query($GLOBALS['DB'], $query);
            
            $error = array("error"=>__("Insufficient fund in wallet. Please fund your wallet"), "wallet_error" => 1);
            echo json_encode($error); 
            exit;

        }elseif($booking_data['payment_type'] == 1){
            $drv_booking_balance = (float) $booking_data['estimated_cost'] - ($booking_data['estimated_cost'] * $driver_data['driver_commision'] / 100 );
            $drv_booking_balance_converted = $drv_booking_balance / $booking_data['cur_exchng_rate'];
            $booking_balance = (float) $driver_data['wallet_amount'] - $drv_booking_balance_converted;    
            if($booking_balance <= DRIVER_MIN_WALLET_BALANCE){

                //update allocation record on DB to show driver attempted to accept the ride. This allows for an accurate trip rejection count
                $query = sprintf('UPDATE %stbl_driver_allocate SET `status` = 3 WHERE booking_id = "%d" AND driver_id = "%d"', DB_TBL_PREFIX,$booking_id,$_SESSION['uid'] );
                $result = mysqli_query($GLOBALS['DB'], $query);

                $error = array("error"=>__("Insufficient fund in wallet. Please fund your wallet"));
                echo json_encode($error); //database error
                exit; 
            }

        }
    } */


    
    
    //File locking mechanism used to ensure only one driver can accept the trip at a time 

    $file_bk_loc = @fopen( __DIR__ . "/b_locks/bloc-{$booking_id}.loc", "w");

    if($file_bk_loc){

        $file_loc = flock($file_bk_loc, LOCK_EX | LOCK_NB);

        if ($file_loc === false ) {

            //update allocation record on DB to show driver attempted to accept the ride. This allows for an accurate trip rejection count
            $query = sprintf('UPDATE %stbl_driver_allocate SET `status` = 3 WHERE booking_id = "%d" AND driver_id = "%d"', DB_TBL_PREFIX,$booking_id,$_SESSION['uid'] );
            $result = mysqli_query($GLOBALS['DB'], $query);

            $error = array("error"=>__("This booking allocation has expired. you did not respond on time. Please always respond promptly when a booking is allocated to you."));
            echo json_encode($error); 
            exit;        
        }

    }   


    //All good. Assign driver to booking
    $driver_franchise_name = isset($driver_data['franchise_name']) ? $driver_data['franchise_name'] : '';
    $driver_franchise_commision = isset($driver_data['franchise_commision']) ? $driver_data['franchise_commision'] : 0;
    $driver_franchise_id = isset($driver_data['franchise_id']) ? $driver_data['franchise_id'] : 0;

    $query = sprintf('UPDATE %stbl_bookings SET drv_acc_long = "%s", drv_acc_lat = "%s", franchise_id = "%d", driver_id = "%d", driver_firstname = "%s",
    driver_lastname = "%s", driver_phone = "%s",franchise_name = "%s",driver_commision = "%s",franchise_commision = "%s" WHERE id = "%d"', 
        DB_TBL_PREFIX,
        $dlng,
        $dlat,
        $driver_franchise_id,
        $_SESSION['uid'],
        $_SESSION['firstname'],
        $_SESSION['lastname'],
        $_SESSION['country_dial_code'].$_SESSION['phone'],
        $driver_franchise_name,
        $driver_data['driver_commision'],
        $driver_franchise_commision,
        $booking_id        
     );

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ 
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }


    //update allocation record on DB to show ride has been accepted
    $query = sprintf('UPDATE %stbl_driver_allocate SET `status` = 1 WHERE booking_id = "%d" AND driver_id = "%d"', DB_TBL_PREFIX,$booking_id,$_SESSION['uid'] );
    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ 
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }


    //create record to track driver's travel route in redis

    $redis = connectRedis();
    
    if($redis){
        $redis->hSet("drvr_active_booking:{$_SESSION['uid']}", "{$booking_id}" , "".time());
    }



    //Get directions data from google (driver location to rider pickup)
    $response = emulateDirectionServiceNew($driver_data['lat'],$driver_data['long'],$booking_data['pickup_lat'],$booking_data['pickup_long']);
    
    if(isset($response['status']) && $response['status'] == "OK"){
        
        //remove unwanted data since we are sending this through push notification to minimize payload to less than 4kb
        foreach($response['routes'][0]['legs'] as $key => $val){
            unset($response['routes'][0]['legs'][$key]['polyline']);
            unset($response['routes'][0]['legs'][$key]['startLocation']);
            unset($response['routes'][0]['legs'][$key]['endLocation']);
            unset($response['routes'][0]['legs'][$key]['steps']);
            unset($response['routes'][0]['legs'][$key]['localizedValues']);
        }
        
    }
    //clear the ride-request screen of other drivers who have received this notification as well
    $query = sprintf('SELECT %1$stbl_driver_allocate.id, %1$stbl_drivers.driver_id, %1$stbl_drivers.push_notification_token FROM %1$stbl_driver_allocate
    INNER JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_driver_allocate.driver_id
    WHERE %1$stbl_driver_allocate.booking_id = %2$d', DB_TBL_PREFIX, $booking_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                if(empty($row['push_notification_token']))continue;
                if($row['driver_id'] == $_SESSION['uid'])continue; //skip this driver
                $data = array(
                    "action"=>"clear-ride-request",
                    "booking_id" => $booking_id
                );

                sendPushNotification("","",$row['push_notification_token'],$data);

            }
        }
    }    


    //send push notification to user    
    $booking_title = str_pad($booking_id , 5, '0', STR_PAD_LEFT);
    $title = WEBSITE_NAME . " - " . __("Driver Assigned",[],"d|{$rlang}");
    $body = __("A driver has been assigned to your ride with booking ID({---1}) and is on his way. You will be contacted shortly",["{$booking_title}"],"d|{$rlang}");
    $device_tokens = $booking_data['push_token'];
    if(!empty($device_tokens)){
        sendPushNotification($title,$body,$device_tokens,null,1,1,null);
    }


    //send silent push notifications processed within app.

    //$photo = explode('/',$driver_data['photo_file']);
    $photo_file = isset($driver_data['photo_file']) ? $driver_data['photo_file'] : "0";

    
    $title = "";
    $body = "";
    $device_tokens = $booking_data['push_token'];
    $data = array("action"=>"driver-assigned",
                    "booking_id" => $booking_id,
                    "driver_id" => $driver_data['driver_id'],
                    "driver_firstname" => $driver_data['firstname'],
                    "driver_phone" => $driver_data['country_dial_code'] . substr($driver_data['phone'],0,1) == "0" ? substr($driver_data['phone'],1) : $driver_data['phone'],
                    "driver_platenum" => $driver_data['car_plate_num'],
                    "driver_rating" => $driver_data['driver_rating'],
                    "driver_location_lat" => $driver_data['lat'],
                    "pickup_addr"=>$booking_data['pickup_address'],
                    "dropoff_addr"=>$booking_data['dropoff_address'],
                    "pickup_lat"=>$booking_data['pickup_lat'],
                    "pickup_long"=>$booking_data['pickup_long'],
                    "dropoff_lat"=>$booking_data['dropoff_lat'],
                    "dropoff_long"=>$booking_data['dropoff_long'],
                    "driver_location_long" => $driver_data['long'],
                    "driver_carmodel" => $driver_data['car_color'] . " " . $driver_data['car_model'],
                    "route_id"=>$booking_data['b_route_id'],
                    "driver_carid" => $driver_data['ride_id'],
                    "driver_carcolor" => $driver_data['car_color'],
                    "driver_completed_rides" => $driver_data['completed_rides'],
                    "driver_photo" => SITE_URL . "ajaxphotofile.php?file=" . $photo_file,
                    "completion_code"=>$booking_data['completion_code'],
                    "waypoint1_address" => $booking_data['waypoint1_address'],
                    "waypoint1_long" => $booking_data['waypoint1_long'],
                    "waypoint1_lat" => $booking_data['waypoint1_lat'],
                    "waypoint2_address" => $booking_data['waypoint2_address'],
                    "waypoint2_long" => $booking_data['waypoint2_long'],
                    "waypoint2_lat" => $booking_data['waypoint2_lat'],
                    "directions" => json_encode($response),
                    "aux_bookings" => json_encode($aux_bookings),
                    "service_mode"=>$booking_data['service_mode'],
                    "hourly_rate_hours"=>$booking_data['hr_set_num_h']
                );
    if(!empty($device_tokens)){
        sendPushNotification($title,$body,$device_tokens,$data);
    }

    sendRealTimeNotification('ridr-' . $booking_data['customer_id'], $data);


    //add to user DB notification
    $content = __("A driver has been assigned to your ride with booking ID({---1}) and is on his way. You will be contacted shortly",["{$booking_title}"],"d|{$rlang}");
    $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
    ("%d",0,"%s",0,"%s")', 
    DB_TBL_PREFIX,
    $booking_data['customer_id'],
    mysqli_real_escape_string($GLOBALS['DB'],$content),
    gmdate('Y-m-d H:i:s', time()) 
    ); 
    $result = mysqli_query($GLOBALS['DB'], $query);

    if($file_bk_loc){
        fclose($file_bk_loc); //release locked file
    }


    $data_array = array("success"=>1,"directions" => $response,"aux_booking" => $aux_bookings_ids);    
    echo json_encode($data_array); 
    exit;

}


function getdirections(){

    if(empty($_SESSION['loggedin'])){
        $data = array("error"=>__("Please login to continue"));
        echo json_encode($data);
        exit;
    }

    if(empty($_GET['p-lat']) || empty($_GET['p-lng']) || empty($_GET['drv-lat']) || empty($_GET['drv-lng'])){
        $data = array("error"=>__("An error has occured"));
        echo json_encode($data);
        exit;
    }

    
    
    //Get directions data

    
    $mode = isset($_GET['mode']) ? (int) $_GET['mode'] : 0;

    if(empty($_GET['d-lat']) || empty($_GET['d-lng'])){

        //no destination provided.
        $response = emulateDirectionServiceNew($_GET['drv-lat'],$_GET['drv-lng'],$_GET['p-lat'],$_GET['p-lng'],$mode);

    }else{
        //destination provided. get route from driver location to customer dropoff location using customer pickup location as waypoint
        $waypoint_arr = [["lat" => $_GET['p-lat'],"lng" => $_GET['p-lng']]];
        $response = emulateDirectionServiceNew($_GET['drv-lat'],$_GET['drv-lng'],$_GET['d-lat'],$_GET['d-lng'],$mode,$waypoint_arr);
    }
    
    
    

       
    $data = array("success"=>1,"direction_details"=>$response);
    echo json_encode($data);
    exit;   




}





function walletwithdraw(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }
    
    $driver_wallet_details = [];
    $amount = (float) $_POST['amount'];

    if(empty($amount) || $amount < 0){
        $error = array("error"=>__("You have entered an incorrect amount"));
        echo json_encode($error); 
        exit; 
    }

    
    //check if driver has a pending request
    $query = sprintf('SELECT * FROM %stbl_wallet_withdrawal WHERE person_id = "%d" AND user_type = 0 AND request_status = 0', DB_TBL_PREFIX, $_SESSION['uid']); //Get required user information from DB


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $row = mysqli_fetch_assoc($result);
            $withdrawal_date = date('l, M j, Y H:i:s',strtotime($row['date_requested'].' UTC'));
            $error = array("error"=>__("You already have a pending withdrawal request of {---1} requested on - {---2}",["{$row['cur_symbol']}{$row['withdrawal_amount']}","{$withdrawal_date}"]));
            echo json_encode($error); 
            exit;
        }
                
    }
    else{ //No record matching the USER ID was found in DB. Show view to notify user

        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); //database error
        exit;
    }




    //Verify if driver has enough funds in wallet to make this request
    
    $query = sprintf('SELECT %1$stbl_drivers.wallet_amount,%1$stbl_currencies.symbol,%1$stbl_currencies.iso_code,%1$stbl_currencies.exchng_rate FROM %1$stbl_drivers 
    LEFT JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_drivers.route_id
    LEFT JOIN %1$stbl_currencies ON %1$stbl_currencies.id = %1$stbl_routes.city_currency_id
    WHERE %1$stbl_drivers.driver_id = %2$d', DB_TBL_PREFIX, $_SESSION['uid']); //Get required user information from DB


    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $driver_wallet_details = mysqli_fetch_assoc($result);
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
    
    //convert requested withdrawal amount to default local currency value
    $amount_converted = $amount / $driver_wallet_details['exchng_rate'];
    
    if(isset($driver_wallet_details['wallet_amount'])){
        $balance = (float) $driver_wallet_details['wallet_amount'] - DRIVER_WITHDRAL_MIN_BALANCE - $amount_converted;

        if(empty($balance) || $balance < 0){

            $error = array("error"=>__("Insufficient amount in wallet! You cannot withraw the requested amount"));
            echo json_encode($error);
            exit;

        }
    }else{
        
        $error = array("error"=>__("Insufficient amount in wallet!"));
        echo json_encode($error);
        exit;
    }



    //All good. Deduct the requested amount from the drivers wallet
    
    $query = sprintf('UPDATE %stbl_drivers SET wallet_amount = wallet_amount - %f WHERE driver_id = "%d"', DB_TBL_PREFIX, $amount_converted,$_SESSION['uid']);
    $result = mysqli_query($GLOBALS['DB'], $query);

    //save this withdrawal request
    $query = sprintf('INSERT INTO %stbl_wallet_withdrawal(cur_symbol,cur_exchng_rate,cur_code,person_id,wallet_amount,wallet_balance,withdrawal_amount,date_requested) VALUES 
    ("%s","%s","%s","%d","%f","%f","%f","%s")', 
    DB_TBL_PREFIX,
    $driver_wallet_details['symbol'],
    $driver_wallet_details['exchng_rate'],
    $driver_wallet_details['iso_code'],
    $_SESSION['uid'],
    $driver_wallet_details['wallet_amount'],
    $driver_wallet_details['wallet_amount'] - $amount_converted,
    $amount,
    gmdate('Y-m-d H:i:s', time()) 
    ); 
    $result = mysqli_query($GLOBALS['DB'], $query);


    //create an entry in wallet transaction table
    $transaction_id = crypto_string();
    $query = sprintf('INSERT INTO %stbl_wallet_transactions (transaction_id,amount,cur_symbol,cur_exchng_rate,cur_code,wallet_balance,`user_id`,user_type,`desc`,`type`,transaction_date) VALUES 
    ("%s","%f","%s","%s","%s","%f","%d","%d","%s","%d","%s")',
    DB_TBL_PREFIX,
    $transaction_id,
    $amount,
    $driver_wallet_details['symbol'],
    $driver_wallet_details['exchng_rate'],
    $driver_wallet_details['iso_code'],
    $driver_wallet_details['wallet_amount'] - $amount_converted,
    $_SESSION['uid'],
    1,
    __('Withdrawal request amount debit'),
    3,
    gmdate('Y-m-d H:i:s', time()) 
    );
    $result = mysqli_query($GLOBALS['DB'], $query);
    
    $data_array = array("success"=>1);    
    echo json_encode($data_array); 
    exit;



}




function startride(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $booking_id = (int) $_GET['bookingid'];
    $dlat = !empty(mysqli_real_escape_string($GLOBALS['DB'], $_GET['driver_lat'])) ? mysqli_real_escape_string($GLOBALS['DB'], $_GET['driver_lat']) : "";
    $dlng = !empty(mysqli_real_escape_string($GLOBALS['DB'], $_GET['driver_lng'])) ? mysqli_real_escape_string($GLOBALS['DB'], $_GET['driver_lng']) : "";

    /* $query = sprintf('SELECT * FROM %stbl_bookings WHERE `status` = 1 AND driver_id = "%d"' , DB_TBL_PREFIX,$_SESSION['uid']);
   
    if($result = mysqli_query($GLOBALS['DB'], $query)){ 
        if(mysqli_num_rows($result)){
            $error = array("error"=>__("You have a ride in progress. You cannot start another one"));
            echo json_encode($error); 
            exit;
        }
    } */



    $query = sprintf('UPDATE %stbl_bookings SET drv_start_long = "%s",drv_start_lat = "%s",`status` = 1,date_started = "%s" 
    WHERE id = "%d" AND driver_id = "%d"',
    DB_TBL_PREFIX,
    $dlng,
    $dlat,
    gmdate('Y-m-d H:i:s', time()),
    $booking_id,
    $_SESSION['uid']
    );

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ 
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }
    

    //Get booking details and user push notification

    $book_data = [];

    $query = sprintf('SELECT %1$stbl_bookings.service_mode,%1$stbl_bookings.hr_set_num_h,%1$stbl_bookings.route_id AS b_route_id,%1$stbl_bookings.date_arrived,%1$stbl_bookings.date_arrived_stop_1,%1$stbl_bookings.date_arrived_stop_2,%1$stbl_bookings.date_left_stop_1,%1$stbl_bookings.date_left_stop_2,%1$stbl_bookings.waypoint1_address,%1$stbl_bookings.waypoint1_long,%1$stbl_bookings.waypoint1_lat,%1$stbl_bookings.waypoint2_address,%1$stbl_bookings.waypoint2_long,%1$stbl_bookings.waypoint2_lat,%1$stbl_drivers.*,%1$stbl_drivers.country_dial_code AS drv_country_dial_code,%1$stbl_drivers.completed_rides AS compl_rides,%1$stbl_drivers.ride_id AS driver_ride_id,%1$stbl_bookings.completion_code,%1$stbl_drivers.photo_file AS driver_photo,%1$stbl_driver_location.lat,%1$stbl_driver_location.long,%1$stbl_users.push_notification_token AS user_push_token, %1$stbl_users.user_id AS customer_id,%1$stbl_bookings.pickup_address,%1$stbl_bookings.dropoff_address, %1$stbl_bookings.pickup_lat, %1$stbl_bookings.pickup_long, %1$stbl_bookings.dropoff_lat,%1$stbl_bookings.dropoff_long, %1$stbl_users.disp_lang AS u_lang FROM %1$stbl_bookings
    INNER JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
    INNER JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id 
    LEFT JOIN %1$stbl_driver_location ON %1$stbl_driver_location.driver_id = %1$stbl_drivers.driver_id 
    WHERE %1$stbl_bookings.id = "%3$d" AND %1$stbl_drivers.driver_id = "%2$d"', DB_TBL_PREFIX, $_SESSION['uid'],$booking_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $book_data = mysqli_fetch_assoc($result);
        }
    }

    $rlang = $book_data['u_lang'];

    
    $waypoint_arr = [];

    
    if(!empty($book_data['waypoint1_address']) && !empty($book_data['waypoint2_address'])){        
        
        $waypoint_arr = [["lat" => $book_data['waypoint1_lat'],"lng" => $book_data['waypoint1_long']],["lat" => $book_data['waypoint2_lat'],"lng" => $book_data['waypoint2_long']]];
    }elseif(!empty($book_data['waypoint1_address'])){
        
        $waypoint_arr = [["lat" => $book_data['waypoint1_lat'],"lng" => $book_data['waypoint1_long']]];
    }elseif(!empty($book_data['waypoint2_address'])){
        
        $waypoint_arr = [["lat" => $book_data['waypoint2_lat'],"lng" => $book_data['waypoint2_long']]];
    } 
    
      

    //Get directions data 
    $response = emulateDirectionServiceNew($book_data['lat'],$book_data['long'],$book_data['dropoff_lat'],$book_data['dropoff_long'],0,$waypoint_arr);
    
    
    //add to user DB notification
    $booking_title = str_pad($booking_id , 5, '0', STR_PAD_LEFT);
    $content = __("Your ride with booking ID {---1} has started",["#{$booking_title}"],"d|{$rlang}");
    $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
    ("%d",0,"%s",0,"%s")',
    DB_TBL_PREFIX,
    $book_data['customer_id'],
    mysqli_real_escape_string($GLOBALS['DB'],$content),
    gmdate('Y-m-d H:i:s', time()) 
    ); 
    $result = mysqli_query($GLOBALS['DB'], $query);


    //send silent push notifications processed within app.

    //$photo = explode('/',$book_data['driver_photo']);
    $photo_file = isset($book_data['driver_photo']) ? $book_data['driver_photo'] : "0";

    
    $title = "";
    $body = "";
    $device_tokens = $book_data['user_push_token'];
    $data = array("action"=>"customer-onride",
                    "booking_id" => $booking_id,
                    "driver_id" => $book_data['driver_id'],
                    "driver_firstname" => $book_data['firstname'],
                    "driver_phone" => $book_data['drv_country_dial_code'] . substr($book_data['phone'],0,1) == "0" ? substr($book_data['phone'],1) : $book_data['phone'],
                    "driver_platenum" => $book_data['car_plate_num'],
                    "driver_rating" => $book_data['driver_rating'],
                    "driver_location_lat" => $book_data['lat'],
                    "driver_location_long" => $book_data['long'],
                    "pickup_addr"=>$book_data['pickup_address'],
                    "dropoff_addr"=>$book_data['dropoff_address'],
                    "pickup_lat"=>$book_data['pickup_lat'],
                    "pickup_long"=>$book_data['pickup_long'],
                    "dropoff_lat"=>$book_data['dropoff_lat'],
                    "dropoff_long"=>$book_data['dropoff_long'],                    
                    "driver_carmodel" =>  __($book_data['car_color'],[],"d|{$rlang}") . " " . $book_data['car_model'],
                    "route_id" => $book_data['b_route_id'],
                    "driver_carid" => $book_data['driver_ride_id'],
                    "driver_carcolor" => $book_data['car_color'],
                    "driver_completed_rides" => $book_data['compl_rides'],
                    "completion_code"=>$book_data['completion_code'],
                    "driver_photo" => SITE_URL . "ajaxphotofile.php?file=" . $photo_file,
                    "directions" => json_encode($response),
                    "waypoint1_address" => $book_data['waypoint1_address'],
                    "waypoint1_long" => $book_data['waypoint1_long'],
                    "waypoint1_lat" => $book_data['waypoint1_lat'],
                    "waypoint2_address" => $book_data['waypoint2_address'],
                    "waypoint2_long" => $book_data['waypoint2_long'],
                    "waypoint2_lat" => $book_data['waypoint2_lat'],
                    "date_arrived_stop_1" => !empty($book_data['date_arrived_stop_1']) ? strtotime($book_data['date_arrived_stop_1'] . " UTC") : "",
                    "date_arrived_stop_2" => !empty($book_data['date_arrived_stop_2']) ? strtotime($book_data['date_arrived_stop_2'] . " UTC") : "",
                    "date_left_stop_1" => !empty($book_data['date_left_stop_1']) ? strtotime($book_data['date_left_stop_1'] . " UTC") : "",
                    "date_left_stop_2" => !empty($book_data['date_left_stop_2']) ? strtotime($book_data['date_left_stop_2'] . " UTC") : "",
                    "service_mode"=>$book_data['service_mode'],
                    "hourly_rate_hours"=>$book_data['hr_set_num_h']
                );

    if(!empty($device_tokens)){
        sendPushNotification($title,$body,$device_tokens,$data);
    }

    sendRealTimeNotification('ridr-' . $book_data['customer_id'], $data);


    $data_array = array("success"=>1,"directions" => $response);    
    echo json_encode($data_array); 
    exit;

}



function gettodaytimeonline(){
    
    if(empty($_SESSION['loggedin'])){
        return 0;
    }

    $todays_date = date('Y-m-d', time());

    //connect to redis

    $redis = connectRedis();
    
    if(empty($redis))return 0;


    //get current time spent online from redis if it exists
    $driver_online_time_data = $redis->get("drvrtimeonline:{$_SESSION['uid']}:{$todays_date}");

    if(empty($driver_online_time_data)){
        //data does not exist, create an entry on redis. at least 7 days validity
        $online_time_data = ["last_time" => time(),"total_time" => 0];
        $redis->setEx("drvrtimeonline:{$_SESSION['uid']}:{$todays_date}", 864000 , serialize($online_time_data));
        return 0;
    }    

    $online_time_data = unserialize($driver_online_time_data);

    if(!isset($online_time_data['last_time'])){
        //data error or does not exist, create an entry on redis. at least 7 days validity
        $online_time_data = ["last_time" => time(),"total_time" => 0];
        $redis->setEx("drvrtimeonline:{$_SESSION['uid']}:{$todays_date}", 864000 , serialize($online_time_data));
        return 0;
    }
   
    if(!isset($_SESSION['availability']) || $_SESSION['availability'] == 0){
        if(isset( $online_time_data['last_time'])){            
            return $online_time_data['total_time'];
        }        
        return 0;
    }


    $time_difference = time() - $online_time_data['last_time'];

    if($time_difference > (DRIVER_LOCATION_UPDATE_INTERVAL * 3)){
        $online_time_data['last_time'] = time();
        $redis->setEx("drvrtimeonline:{$_SESSION['uid']}:{$todays_date}", 864000 , serialize($online_time_data));
        return $online_time_data['total_time'];
        
    }else{
        $online_time_data['last_time'] = time();
        $online_time_data['total_time'] += $time_difference;
        $redis->setEx("drvrtimeonline:{$_SESSION['uid']}:{$todays_date}", 864000 , serialize($online_time_data));
        return $online_time_data['total_time'];
        
    }

}


function getbannerdata(){

    
    $banner_data = [];
    $formatted_banners = '';
    $user_route_id = !empty($_SESSION['city_id']) ? $_SESSION['city_id'] : 0;

    //check if cached data is available and return
    $redis = connectRedis();
    
    if($redis){
        $sbooking_redis_data = $redis->get("drvbannerdata:{$user_route_id}");
        if(!empty($sbooking_redis_data)){
            return $sbooking_redis_data . "<div style='display:none'>From Redis</div>";
        }

    }

    

        
    $query = sprintf('SELECT * FROM %stbl_banners WHERE `status` = 1 AND (visibility = 0 OR visibility = 2) AND (city = 0 OR city = %d) ORDER BY date_created DESC LIMIT 4', DB_TBL_PREFIX, $user_route_id); //Get banner information from DB

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
        
        $count++;

        
    }

    //cache bannerdata on redis
    
    
    if($redis){
        //successfully connected to redis
        $redis->setEx("drvbannerdata:{$user_route_id}", 120 , $formatted_banners);          

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
        $redis->zAdd("bannerviewcount:{$banner_id}",time(),"drvr:{$_SESSION['uid']}");
    }

    $res = array("success"=>1);
    echo json_encode($res); 
    exit;


}



function getavailablecitydrivers(){

    

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>"Please re-login and retry.");
        echo json_encode($error); 
        exit; 
    }


    $city = (int) $_GET['city'];
    $drivers_location_data = [];
    $location_info_age = gmdate('Y-m-d H:i:s', time() - LOCATION_INFO_VALID_AGE);
    $ride_types = [];


    //*************Get driver location data from redis if available**********/

    $redis = connectRedis();
    
    if($redis){
        //successfully connected to redis

        $online_drivers = [];
        $redis->zRemRangeByScore("drvs_online:{$city}","-inf",time() - LOCATION_INFO_VALID_AGE); //remove drivers who haven't updated their location for a while

        $redis_res = $redis->zRangeByScore("drvs_online:{$city}",time() - LOCATION_INFO_VALID_AGE,"+inf",['withscores'=>false,'limit' => [0,10]]);
        if($redis_res){
            $online_drivers = $redis->mget($redis_res);
        }


        $count = 0;
            
            
            foreach($online_drivers as $onlinedrivers){

                

                $row = unserialize($onlinedrivers);
                if(empty($row))continue;

                if($row['driver_id'] == $_SESSION['uid'])continue; //skip this driver's location data
                                
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


    //*************Redis end **********/


    //driver time online
    $time_online = gettodaytimeonline();

    $hours = floor($time_online / 3600);
    $minutes = floor(($time_online % 3600) / 60 );
    //$seconds = ($time_online % 3600) % 60;

    $driver_time_online_formated = '';
    if(!empty($hours)){
        $driver_time_online_formated = $hours . "H ";
    }

    if(!empty($minutes)){
        $driver_time_online_formated .= $minutes . "M ";
    }else{
        $driver_time_online_formated .=  "0M ";
    }

    //check for single device login
    $sdl_status = 0;
    

    if($redis){
        $sdl_key = $redis->get("drvsdlkey:{$_SESSION['uid']}");
        if(!empty($sdl_key) && $sdl_key != $_GET['sdl_key']){
            $sdl_status = 1;
        }
    }


   

    //Read from database every 15 seconds

    if(isset($_SESSION['last_location_get'])){
        if(time() - $_SESSION['last_location_get'] < 15){
            if($redis){
                $data_array = array("success"=>1,'drivers_locations'=>$drivers_location_data, 'driver_time_online'=>$driver_time_online_formated,"sdl_status" => $sdl_status);  
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

       

    //driver todays earning

    $driver_today_earnings = 0;   
    $completed_trips_today = 0; 

    $query = sprintf('SELECT %1$stbl_bookings.actual_cost, %1$stbl_bookings.cur_exchng_rate, %1$stbl_bookings.driver_commision FROM %1$stbl_bookings
    WHERE %1$stbl_bookings.driver_id = %2$d AND %1$stbl_bookings.status = 3 AND DATE(%1$stbl_bookings.date_completed) = "%3$s"', DB_TBL_PREFIX,$_SESSION['uid'], gmdate('Y-m-d', time()));
    

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                $completed_trips_today++;
                $driver_today_earnings += ((float) $row['actual_cost'] / $row['cur_exchng_rate']) * $row['driver_commision'] / 100;
            }
                            
        }    
    }
    


    //check driver wallet amount and disable availability if low

    $disable_availability = 0;

    if(!empty($_SESSION['availability'])){
        $query = sprintf('SELECT wallet_amount FROM %stbl_drivers WHERE driver_id = %d', DB_TBL_PREFIX, $_SESSION['uid']);
        if($result = mysqli_query($GLOBALS['DB'], $query)){
            if(mysqli_num_rows($result)){
                $row = mysqli_fetch_assoc($result);
                if($row['wallet_amount'] <= DRIVER_MIN_WALLET_BALANCE){
                    $query = sprintf('UPDATE %stbl_drivers SET available = "%s" WHERE driver_id = "%s"', DB_TBL_PREFIX, 0, $_SESSION['uid']);
                    if($result = mysqli_query($GLOBALS['DB'], $query)){
                        $_SESSION['availability'] = 0;
                        $_SESSION['dis_availability'] = 1;
                        $disable_availability = 1;                        
                    }
                }else{
                    $_SESSION['dis_availability'] = 0;
                    $disable_availability = 0;
                }
            }            
            
        }
    }



    if($redis){
        $data_array = array("success"=>1,'drivers_locations'=>$drivers_location_data, 'driver_today_earning'=>$driver_today_earnings,'completed_trips'=>$completed_trips_today,'bannerdata'=> getbannerdata(),'driver_time_online'=>$driver_time_online_formated,"sdl_status" => $sdl_status,"dis_availability" => $disable_availability);
        echo json_encode($data_array); 
        exit;
    }



     
       

    $query = sprintf('SELECT %1$stbl_driver_location.*,%1$stbl_drivers.firstname, %1$stbl_drivers.ride_id,%1$stbl_rides.* FROM %1$stbl_driver_location 
    INNER JOIN %1$stbl_drivers ON %1$stbl_driver_location.driver_id = %1$stbl_drivers.driver_id
    INNER JOIN %1$stbl_rides ON %1$stbl_rides.id = %1$stbl_drivers.ride_id
    WHERE %1$stbl_drivers.driver_id != %4$d AND %1$stbl_drivers.route_id = %2$d AND %1$stbl_drivers.is_activated = 1 AND %1$stbl_drivers.available = 1 AND %1$stbl_driver_location.location_date > "%3$s" AND %1$stbl_drivers.operation_status = 0 LIMIT 30', DB_TBL_PREFIX, $city, $location_info_age,$_SESSION['uid']);

        
    

    if($result = mysqli_query($GLOBALS['DB'], $query)){

        if(mysqli_num_rows($result)){
            $count = 0;
            
            
            while($row = mysqli_fetch_assoc($result)){

                                
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
                    $drivers_location_data[$count]['icon']['url'] = $ride_types[$row['ride_id']];
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
        $error = array("error"=>"Database error.");
        echo json_encode($error); 
        exit;
    }

    



    $data_array = array("success"=>1,'drivers_locations'=>$drivers_location_data, 'driver_today_earning'=>$driver_today_earnings,'completed_trips'=>$completed_trips_today,'bannerdata'=> getbannerdata(),'driver_time_online'=>$driver_time_online_formated);    
    echo json_encode($data_array); 
    exit;   




}




function driverarrived(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $booking_id = (int) $_GET['bookingid'];
    $dlat = !empty(mysqli_real_escape_string($GLOBALS['DB'], $_GET['driver_lat'])) ? mysqli_real_escape_string($GLOBALS['DB'], $_GET['driver_lat']) : "";
    $dlng = !empty(mysqli_real_escape_string($GLOBALS['DB'], $_GET['driver_lng'])) ? mysqli_real_escape_string($GLOBALS['DB'], $_GET['driver_lng']) : "";

    

    if(empty($_SESSION['arrived_set'][$booking_id])){
        $query = sprintf('UPDATE %stbl_bookings SET drv_arv_long = "%s",drv_arv_lat = "%s",date_arrived = "%s" 
        WHERE id = "%d" AND driver_id = "%d"',
        DB_TBL_PREFIX,
        $dlng,
        $dlat,
        gmdate('Y-m-d H:i:s', time()),
        $booking_id,
        $_SESSION['uid']
        );

        if(!$result = mysqli_query($GLOBALS['DB'], $query)){ 
            /* $error = array("error"=>"Error! failed to startride.");
            echo json_encode($error); 
            exit; */
        }else{
            $_SESSION['arrived_set'][$booking_id] = 1;
        }
    }

    //Get booking details and user push notification

    $book_data = [];

    $query = sprintf('SELECT %1$stbl_bookings.service_mode,%1$stbl_bookings.hr_set_num_h,%1$stbl_drivers.*,%1$stbl_bookings.date_arrived,%1$stbl_bookings.waypoint1_address,%1$stbl_bookings.waypoint1_long,%1$stbl_bookings.waypoint1_lat,%1$stbl_bookings.waypoint2_address,%1$stbl_bookings.waypoint2_long,%1$stbl_bookings.waypoint2_lat,%1$stbl_bookings.date_completed,%1$stbl_bookings.route_id AS b_route_id,%1$stbl_drivers.country_dial_code AS drv_country_dial_code,%1$stbl_drivers.completed_rides AS compl_rides,%1$stbl_drivers.ride_id AS driver_ride_id,%1$stbl_bookings.completion_code,%1$stbl_drivers.photo_file AS driver_photo,%1$stbl_driver_location.lat,%1$stbl_driver_location.long,%1$stbl_users.push_notification_token AS user_push_token,%1$stbl_users.user_id AS customer_id,%1$stbl_bookings.pickup_address,%1$stbl_bookings.dropoff_address,%1$stbl_bookings.pickup_lat,%1$stbl_bookings.pickup_long, %1$stbl_bookings.dropoff_lat,%1$stbl_bookings.dropoff_long,%1$stbl_users.disp_lang AS u_lang FROM %1$stbl_bookings
    INNER JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
    INNER JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id 
    LEFT JOIN %1$stbl_driver_location ON %1$stbl_driver_location.driver_id = %1$stbl_drivers.driver_id 
    WHERE %1$stbl_bookings.id = "%3$d" AND %1$stbl_drivers.driver_id = "%2$d"', DB_TBL_PREFIX, $_SESSION['uid'],$booking_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $book_data = mysqli_fetch_assoc($result);
        }
    }

    $rlang = $book_data['u_lang'];
    


    //send silent push notifications processed within app.

    //$photo = explode('/',$book_data['driver_photo']);
    $photo_file = isset($book_data['driver_photo']) ? $book_data['driver_photo'] : "0";

    
    $title = "";
    $body = "";
    $device_tokens = $book_data['user_push_token'];
    $data = array("action"=>"driver-arrived",
                    "booking_id" => $booking_id,
                    "driver_id" => $book_data['driver_id'],
                    "driver_firstname" => $book_data['firstname'],
                    "driver_phone" => $book_data['drv_country_dial_code'] . substr($book_data['phone'],0,1) == "0" ? substr($book_data['phone'],1) : $book_data['phone'],
                    "driver_platenum" => $book_data['car_plate_num'],
                    "driver_rating" => $book_data['driver_rating'],
                    "driver_location_lat" => $book_data['lat'],
                    "driver_location_long" => $book_data['long'],
                    "pickup_addr"=>$book_data['pickup_address'],
                    "dropoff_addr"=>$book_data['dropoff_address'],
                    "pickup_lat"=>$book_data['pickup_lat'],
                    "pickup_long"=>$book_data['pickup_long'],
                    "dropoff_lat"=>$book_data['dropoff_lat'],
                    "dropoff_long"=>$book_data['dropoff_long'],
                    "route_id"=>$book_data['b_route_id'],
                    "driver_arrived_time"=>time(),
                    "driver_start_time"=>!empty($book_data['date_started']) ? strtotime($book_data['date_started'] . " UTC") : 0,
                    "driver_carmodel" =>  __($book_data['car_color'],[],"d|{$rlang}") . " " . $book_data['car_model'],
                    "driver_carid" => $book_data['driver_ride_id'],
                    "driver_carcolor" => $book_data['car_color'],
                    "driver_completed_rides" => $book_data['compl_rides'],
                    "completion_code"=>$book_data['completion_code'],
                    "driver_photo" => SITE_URL . "ajaxphotofile.php?file=" . $photo_file,
                    "waypoint1_address" => $book_data['waypoint1_address'],
                    "waypoint1_long" => $book_data['waypoint1_long'],
                    "waypoint1_lat" => $book_data['waypoint1_lat'],
                    "waypoint2_address" => $book_data['waypoint2_address'],
                    "waypoint2_long" => $book_data['waypoint2_long'],
                    "waypoint2_lat" => $book_data['waypoint2_lat'],
                    "service_mode"=>$book_data['service_mode'],
                    "hourly_rate_hours"=>$book_data['hr_set_num_h']
                );
    if(!empty($device_tokens)){
        sendPushNotification($title,$body,$device_tokens,$data);

        //send push notification to user    
        $booking_title = str_pad($booking_id , 5, '0', STR_PAD_LEFT);
        $title = WEBSITE_NAME . " - " . __("Driver has arrived",[],"d|{$rlang}");
        $body = __("Your driver for booking ID({---1}) has arrived. Please meet him immediately",["{$booking_title}"],"d|{$rlang}");
        sendPushNotification($title,$body,$device_tokens,null,1,1,null);       
        
    }


    sendRealTimeNotification('ridr-' . $book_data['customer_id'], $data);

    $data_array = array("success"=>1,"driver_arrived_time"=>time());    
    echo json_encode($data_array); 
    exit;

}




function notifyCustomerStopStatus(){


    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $booking_id = (int) $_GET['booking_id'];

    if($_GET['status'] != "arrived_stop_1" && $_GET['status'] != "left_stop_1" && $_GET['status'] != "arrived_stop_2" && $_GET['status'] != "left_stop_2"){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }


    $stop_status = $_GET['status'];
    


    if(empty($_SESSION['arrived_set'][$booking_id])){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

    $stop_status_datetime_field = "";

    switch($stop_status){

        case "arrived_stop_1":
        $stop_status_datetime_field = "date_arrived_stop_1";         
        break;

        case "left_stop_1":
        $stop_status_datetime_field = "date_left_stop_1";    
        break;

        case "arrived_stop_2":
        $stop_status_datetime_field = "date_arrived_stop_2";
        break;

        case "left_stop_2":
        $stop_status_datetime_field = "date_left_stop_2";
        break;        

    }


    $query = sprintf('UPDATE %stbl_bookings SET %s = "%s" WHERE id = "%d" AND driver_id = "%d"',
    DB_TBL_PREFIX,
    $stop_status_datetime_field,
    gmdate('Y-m-d H:i:s', time()),
    $booking_id,
    $_SESSION['uid']
    );

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ 
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

    
    //Get booking details and user push notification

    $book_data = [];

    $query = sprintf('SELECT %1$stbl_drivers.*,%1$stbl_bookings.date_arrived,%1$stbl_bookings.date_arrived_stop_1,%1$stbl_bookings.date_arrived_stop_2,%1$stbl_bookings.date_left_stop_1,%1$stbl_bookings.date_left_stop_2,%1$stbl_bookings.waypoint1_address,%1$stbl_bookings.waypoint1_long,%1$stbl_bookings.waypoint1_lat,%1$stbl_bookings.waypoint2_address,%1$stbl_bookings.waypoint2_long,%1$stbl_bookings.waypoint2_lat,%1$stbl_bookings.date_completed,%1$stbl_bookings.route_id AS b_route_id,%1$stbl_drivers.country_dial_code AS drv_country_dial_code,%1$stbl_drivers.completed_rides AS compl_rides,%1$stbl_drivers.ride_id AS driver_ride_id,%1$stbl_bookings.completion_code,%1$stbl_drivers.photo_file AS driver_photo,%1$stbl_driver_location.lat,%1$stbl_driver_location.long,%1$stbl_users.push_notification_token AS user_push_token,%1$stbl_users.user_id AS customer_id,%1$stbl_bookings.pickup_address,%1$stbl_bookings.dropoff_address,%1$stbl_bookings.pickup_lat,%1$stbl_bookings.pickup_long, %1$stbl_bookings.dropoff_lat,%1$stbl_bookings.dropoff_long,%1$stbl_users.disp_lang AS u_lang FROM %1$stbl_bookings
    INNER JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
    INNER JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id 
    LEFT JOIN %1$stbl_driver_location ON %1$stbl_driver_location.driver_id = %1$stbl_drivers.driver_id 
    WHERE %1$stbl_bookings.id = "%3$d" AND %1$stbl_drivers.driver_id = "%2$d"', DB_TBL_PREFIX, $_SESSION['uid'],$booking_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $book_data = mysqli_fetch_assoc($result);
        }
    }

    $rlang = $book_data['u_lang'];

    
    $title = "";
    $body = "";

    $waypoint_arr = [];

    
    if(!empty($book_data['waypoint1_address']) && !empty($book_data['waypoint2_address'])){    
        
        $waypoint_arr = [["lat" => $book_data['waypoint1_lat'],"lng" => $book_data['waypoint1_long']],["lat" => $book_data['waypoint2_lat'],"lng" => $book_data['waypoint2_long']]];
    }elseif(!empty($book_data['waypoint1_address'])){
        
        $waypoint_arr = [["lat" => $book_data['waypoint1_lat'],"lng" => $book_data['waypoint1_long']]];
    }elseif(!empty($book_data['waypoint2_address'])){
        
        $waypoint_arr = [["lat" => $book_data['waypoint2_lat'],"lng" => $book_data['waypoint2_long']]];
    }


    $route_response = null;
    
    

    switch($stop_status){

        case "arrived_stop_1":
        
        if(!empty($book_data['waypoint1_address']) && !empty($book_data['waypoint2_address'])){

            $title = WEBSITE_NAME . " - " . __("Driver arrived first stop",[],"d|{$rlang}");
            $body = __("Your driver has arrived at your first stop",[],"d|{$rlang}");
            $waypoint_arr = [["lat" => $book_data['waypoint2_lat'],"lng" => $book_data['waypoint2_long']]];
            $response = emulateDirectionServiceNew($book_data['lat'],$book_data['long'],$book_data['dropoff_lat'],$book_data['dropoff_long'],0,$waypoint_arr);
            
        }else{
            $title = WEBSITE_NAME . " - " . __("Driver arrived stop",[],"d|{$rlang}");
            $body = __("Your driver has arrived at your stop",[],"d|{$rlang}");
            $response = emulateDirectionServiceNew($book_data['lat'],$book_data['long'],$book_data['dropoff_lat'],$book_data['dropoff_long'],0);
            
        }                
        break;

        case "left_stop_1":
        $title = WEBSITE_NAME . " - " . __("Trip has resumed",[],"d|{$rlang}");
        if(!empty($book_data['waypoint1_address']) && !empty($book_data['waypoint2_address'])){
            $body = __("Your driver has resumed the trip and is heading to your next stop",[],"d|{$rlang}");
            $waypoint_arr = [["lat" => $book_data['waypoint2_lat'],"lng" => $book_data['waypoint2_long']]];
            $response = emulateDirectionServiceNew($book_data['lat'],$book_data['long'],$book_data['dropoff_lat'],$book_data['dropoff_long'],0,$waypoint_arr);
        }else{
            $body = __("Your driver has resumed the trip and is heading to your destination",[],"d|{$rlang}");
            $response = emulateDirectionServiceNew($book_data['lat'],$book_data['long'],$book_data['dropoff_lat'],$book_data['dropoff_long'],0); 
        }       
        
        break;

        case "arrived_stop_2":        
        if(!empty($book_data['waypoint1_address']) && !empty($book_data['waypoint2_address'])){
            $title = WEBSITE_NAME . " - " . __("Driver arrived second stop",[],"d|{$rlang}");
            $body = __("Your driver has arrived at your second stop",[],"d|{$rlang}");
        }else{
            $title = WEBSITE_NAME . " - " . __("Driver arrived stop",[],"d|{$rlang}");
            $body = __("Your driver has arrived at your stop",[],"d|{$rlang}");
        }
        
        $response = emulateDirectionServiceNew($book_data['lat'],$book_data['long'],$book_data['dropoff_lat'],$book_data['dropoff_long'],0); 
        
        break;

        case "left_stop_2":
        $title = WEBSITE_NAME . " - " . __("Trip has resumed",[],"d|{$rlang}");
        $body = __("Your driver has resumed the trip and is heading to your destination",[],"d|{$rlang}");
        $response = emulateDirectionServiceNew($book_data['lat'],$book_data['long'],$book_data['dropoff_lat'],$book_data['dropoff_long'],0); 
        break;


    }

    if(isset($response['status']) && $response['status'] == "OK"){
        
        //remove unwanted data since we are sending this through push notification to minimize payload to less than 4kb
        foreach($response['routes'][0]['legs'] as $key => $val){
            unset($response['routes'][0]['legs'][$key]['polyline']);
            unset($response['routes'][0]['legs'][$key]['startLocation']);
            unset($response['routes'][0]['legs'][$key]['endLocation']);
            unset($response['routes'][0]['legs'][$key]['steps']);
            unset($response['routes'][0]['legs'][$key]['localizedValues']);
        }
        
    }
    
    
    //send silent push notifications processed within app.

    //$photo = explode('/',$book_data['driver_photo']);
    $photo_file = isset($book_data['driver_photo']) ? $book_data['driver_photo'] : "0";

    
    $device_tokens = $book_data['user_push_token'];

    $data = array("action"=>"customer-onride",
                    "booking_id" => $booking_id,
                    "driver_id" => $book_data['driver_id'],
                    "driver_firstname" => $book_data['firstname'],
                    "driver_phone" => $book_data['drv_country_dial_code'] . substr($book_data['phone'],0,1) == "0" ? substr($book_data['phone'],1) : $book_data['phone'],
                    "driver_platenum" => $book_data['car_plate_num'],
                    "driver_rating" => $book_data['driver_rating'],
                    "driver_location_lat" => $book_data['lat'],
                    "driver_location_long" => $book_data['long'],
                    "pickup_addr"=>$book_data['pickup_address'],
                    "dropoff_addr"=>$book_data['dropoff_address'],
                    "pickup_lat"=>$book_data['pickup_lat'],
                    "pickup_long"=>$book_data['pickup_long'],
                    "dropoff_lat"=>$book_data['dropoff_lat'],
                    "dropoff_long"=>$book_data['dropoff_long'],
                    "route_id"=>$book_data['b_route_id'],
                    "driver_arrived_time"=>time(),
                    "driver_start_time"=>!empty($book_data['date_started']) ? strtotime($book_data['date_started'] . " UTC") : 0,
                    "driver_carmodel" =>  __($book_data['car_color'],[],"d|{$rlang}") . " " . $book_data['car_model'],
                    "driver_carid" => $book_data['driver_ride_id'],
                    "driver_carcolor" => $book_data['car_color'],
                    "driver_completed_rides" => $book_data['compl_rides'],
                    "completion_code"=>$book_data['completion_code'],
                    "driver_photo" => SITE_URL . "ajaxphotofile.php?file=" . $photo_file,
                    "directions" => json_encode($response),
                    "waypoint1_address" => $book_data['waypoint1_address'],
                    "waypoint1_long" => $book_data['waypoint1_long'],
                    "waypoint1_lat" => $book_data['waypoint1_lat'],
                    "waypoint2_address" => $book_data['waypoint2_address'],
                    "waypoint2_long" => $book_data['waypoint2_long'],
                    "waypoint2_lat" => $book_data['waypoint2_lat'],
                    "date_arrived_stop_1" => !empty($book_data['date_arrived_stop_1']) ? strtotime($book_data['date_arrived_stop_1'] . " UTC") : "",
                    "date_arrived_stop_2" => !empty($book_data['date_arrived_stop_2']) ? strtotime($book_data['date_arrived_stop_2'] . " UTC") : "",
                    "date_left_stop_1" => !empty($book_data['date_left_stop_1']) ? strtotime($book_data['date_left_stop_1'] . " UTC") : "",
                    "date_left_stop_2" => !empty($book_data['date_left_stop_2']) ? strtotime($book_data['date_left_stop_2'] . " UTC") : ""
                );

    if(!empty($device_tokens)){
        sendPushNotification($title,$body,$device_tokens,$data);

        //send push notification to user    
        sendPushNotification($title,$body,$device_tokens,null,1,1,null);       
        
    }


    sendRealTimeNotification('ridr-' . $book_data['customer_id'], $data);

    $data_array = array("success"=>1,"directions" => json_encode($response));    
    echo json_encode($data_array); 
    exit;

}



function getreferralsdata(){
    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $referrals_data = [];
    $referrals_data_html = '';

    //get all drivers who have registered with this driver's referral code
    $query = sprintf('SELECT %1$stbl_drivers.driver_id,%1$stbl_referral_drivers.number_of_rides,%1$stbl_referral_drivers.beneficiary,%1$stbl_referral_drivers.driver_incentive,%1$stbl_drivers.photo_file,%1$stbl_drivers.referral_task_progress, %1$stbl_drivers.firstname, %1$stbl_drivers.lastname, %1$stbl_drivers.referral_target_status, DATE(%1$stbl_drivers.account_create_date) AS account_date FROM %1$stbl_drivers
    INNER JOIN %1$stbl_referral_drivers ON %1$stbl_referral_drivers.route_id = %1$stbl_drivers.reg_route_id
    WHERE %1$stbl_drivers.reg_with_referal_code = "%2$s" AND %1$stbl_referral_drivers.status = 1 ORDER BY %1$stbl_drivers.account_create_date DESC LIMIT 100', DB_TBL_PREFIX, $_SESSION['ref_code']);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            while($row = mysqli_fetch_assoc($result)){
                $referrals_data[$row['account_date']][] = $row;
            }
        }else{
            $data = array("success"=> 1,'referrals_data'=>"<div class='center-screen'><p style='text-align:center;'>" . __('You have no referrals. Invite drivers to Droptaxi and earn a commission when your invited drivers register with your referral code and complete a task. You will be able to track their progress here') . "</p></div>");
            echo json_encode($data); 
            exit; 
        }
    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

    foreach($referrals_data as $key=>$val){
        $referrals_data_html .= "<ons-list-header style='border-top:thin solid #ccc;border-bottom:thin solid #ccc;'>{$key}</ons-list-header>";
        foreach($val as $ref_driver){
            $driver_name = $ref_driver['firstname'] . " " . $ref_driver['lastname'];
            $photo_file = isset($ref_driver['photo_file']) ? $ref_driver['photo_file'] : "0";
            $driver_photo = SITE_URL . "ajaxphotofile.php?file=" . $photo_file;
            $progress_bar_color = '#2196f3';
            $progress_bar_width = "1%";
            $ref_progress_percent = 0;
            $status = __("Task in progress");
            $ref_progress_text = __('{---1} trips completed',["0/{$ref_driver['number_of_rides']}"]);
            if(!empty($ref_driver['referral_task_progress'])){
                if($ref_driver['referral_task_progress'] > $ref_driver['number_of_rides'])$ref_driver['referral_task_progress'] = $ref_driver['number_of_rides'];
                $ref_progress_percent = ceil(($ref_driver['referral_task_progress'] / $ref_driver['number_of_rides']) * 100);
                $ref_progress_percent = $ref_progress_percent > 100 ? 100 : $ref_progress_percent;
                $progress_bar_width = $ref_progress_percent . "%";

                $ref_progress_text = __('{---1} trips completed',["{$ref_driver['referral_task_progress']}/{$ref_driver['number_of_rides']}"]);

            }

            if($ref_driver['referral_target_status'] == 1){
                $progress_bar_color = "#4caf50";
                $status = __("Task completed");
            }elseif($ref_driver['referral_target_status'] == 2){
                $progress_bar_color = "#f44336";
                $status = __("Task not completed");
            }

            $referrals_data_html .= "<ons-list-item modifier='longdivider'>
                                        <div class='left'><img id='ref-driver-img-{$ref_driver['driver_id']}' src='img/driver.png' style='width:48px;border-radius:50%;' /></div>
                                        <div class='center' style='padding-right: 20px;'>
                                            <div style='width:100%'><span class='list-item__title' style='font-weight:bold;'>{$driver_name}</span></div>
                                            <div style='width:100%;text-align:right;'>{$status}</div>
                                            <div style='border-radius: 10px;border:2px solid #white;padding:2px;margin-top:10px;margin-bottom:10px;width:100%;background-color:black;'><div style='width:{$progress_bar_width};height:5px;background-color:{$progress_bar_color};border-radius: 10px;'></div></div>
                                            <div style='width:100%;margin-top:5px;'><span class='list-item__subtitle' style='width:60%;float:left'>{$ref_progress_text}</span> <span class='list-item__subtitle' style='width:30%;float:right;text-align:right;'>{$ref_progress_percent}%</span></div>
                                            <div style='clear:both;'></div><img style='display:none' src='{$driver_photo}' onload='(function(el){\$(\"#ref-driver-img-{$ref_driver['driver_id']}\").attr(\"src\",el.src)})(this)' />
                                        </div>
                                    </ons-list-item>";
        }
    }


    $data = array("success"=> 1,'referrals_data'=>"<ons-list>" .$referrals_data_html . "</ons-list>");
    echo json_encode($data); 
    exit;

    
}


function promocheck(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $promo_data = [];
    $promo_data_html = '';

    //get drivers promotions
    $query = sprintf('SELECT %1$stbl_currencies.symbol, %1$stbl_referral_drivers.invitee_incentive,%1$stbl_routes.r_title,%1$stbl_referral_drivers.number_of_rides,%1$stbl_referral_drivers.number_of_days,%1$stbl_drivers.reg_with_referal_code,%1$stbl_drivers.referral_task_progress, %1$stbl_drivers.referral_target_status,%1$stbl_drivers.driver_id, %1$stbl_drivers.reg_route_id FROM %1$stbl_drivers
    INNER JOIN %1$stbl_referral_drivers ON %1$stbl_referral_drivers.route_id = %1$stbl_drivers.reg_route_id
    INNER JOIN %1$stbl_routes ON %1$stbl_routes.id = %1$stbl_drivers.reg_route_id
    INNER JOIN %1$stbl_currencies ON %1$stbl_currencies.id = %1$stbl_routes.city_currency_id
    WHERE %1$stbl_drivers.driver_id = %2$d AND %1$stbl_drivers.reg_with_referal_code != "" AND %1$stbl_referral_drivers.status = %3$d AND (%1$stbl_referral_drivers.beneficiary = 0 OR %1$stbl_referral_drivers.beneficiary = 2)', DB_TBL_PREFIX, $_SESSION['uid'], 1);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $promo_data = mysqli_fetch_assoc($result);
            //referral task is valid. Get status
            $query = sprintf('SELECT id, DATE(date_completed) AS date_comp FROM %stbl_bookings WHERE driver_id = %d AND `status` = %d AND route_id = %d ORDER BY id ASC LIMIT %d', DB_TBL_PREFIX, $_SESSION['uid'], 3, $promo_data['reg_route_id'],$promo_data['number_of_rides']);
            if($result = mysqli_query($GLOBALS['DB'], $query)){
                if(mysqli_num_rows($result)){

                    $driver_bookings = [];

                    while($row = mysqli_fetch_assoc($result)){
                        if(!empty($row))$driver_bookings[] =$row;
                    }

                    $start_date = $driver_bookings[0]['date_comp'];
                    $progress_bar_color = '#2196f3';
                    $progress_bar_width = "1%";
                    $ref_progress_percent = 0;
                    $status = __("Task in progress");
                    $days_left_text = "";
                    
                    $ref_progress_text = __('{---1} trips completed',["0/{$promo_data['number_of_rides']}"]);
                    if(!empty($promo_data['referral_task_progress'])){
                        if($promo_data['referral_task_progress'] > $promo_data['number_of_rides'])$promo_data['referral_task_progress'] = $promo_data['number_of_rides'];
                        $ref_progress_percent = ceil(($promo_data['referral_task_progress'] / $promo_data['number_of_rides']) * 100);
                        $ref_progress_percent = $ref_progress_percent > 100 ? 100 : $ref_progress_percent;
                        $progress_bar_width = $ref_progress_percent . "%";

                        $ref_progress_text = __('{---1} trips completed',["{$promo_data['referral_task_progress']}/{$promo_data['number_of_rides']}"]);

                    }

                    if($promo_data['referral_target_status'] == 1){
                        $progress_bar_color = "#4caf50";
                        $status = __("Task completed");
                    }elseif($promo_data['referral_target_status'] == 2){
                        $progress_bar_color = "#f44336";
                        $status = __("Task not completed");
                    }

                    if(isValidDate($start_date)){
                        $time_duration_sec = time() - strtotime($start_date);
                        $days_taken = intval($time_duration_sec / 86400); //convert seconds to days
                        $days_left = $promo_data['number_of_days'] - $days_taken;
                        if($days_left < 0)$days_left = 0;
                        if(empty($days_left)){
                            $progress_bar_color = "#f44336";
                            $status = __("Task not completed");
                        }
                        $days_left_text = " | " . __("{---1} days left",["{$days_left}"]);
                    }
                    
                    $promo_data_html .= "<div style='padding:10px;margin-top: 10px;margin-bottom:10px;background: linear-gradient(90deg, rgba(2,0,36,1) 0%, rgba(9,9,121,1) 27%, rgba(0,212,255,1) 100%);border-radius: 10px;box-shadow: 1px 1px 2px 0px grey;'>
                                            
                                            <p id='promo-desc' style='color: #fff;font-size: 16px;font-weight: bold;margin:0 0 15px 0;'>" . __("Earn {---1} when you complete {---2} trips in {---3} days",["{$promo_data['symbol']}{$promo_data['invitee_incentive']}","{$promo_data['number_of_rides']}","{$promo_data['number_of_days']}"]) ."</p>
                                            <div style='border-radius: 10px;border:2px solid #white;padding:2px;margin-top:10px;margin-bottom:10px;width:100%;background-color:black;'><div style='width:{$progress_bar_width};height:5px;background-color:{$progress_bar_color};border-radius: 10px;'></div></div>
                                            <p style='margin:0 0 10px 0;font-size:12px;color: #bdbdbd;' id='promo-usage'>{$ref_progress_text}{$days_left_text}</p>
                                            <p style='margin:0 0 10px 0;font-size:12px;color: #bdbdbd;' id='promo-status'>{$status}</p>
                                            <hr>
                                            <p style='margin:0;font-size:12px;color: #bdbdbd;' id='promo-city'>{$promo_data['r_title']}</p>
      
                                        </div>"; 

                    
                    $data = array("success"=>1,'promodata'=>$promo_data_html);
                    echo json_encode($data); 
                    exit; 

                }else{

                    $progress_bar_color = '#2196f3';
                    $progress_bar_width = "1%";
                    $ref_progress_percent = 0;
                    $status = __("Task in progress");
                    $days_left = "";
                    
                    $ref_progress_text = __('{---1} trips completed',["0/{$promo_data['number_of_rides']}"]);
                    if(!empty($promo_data['referral_task_progress'])){
                        if($promo_data['referral_task_progress'] > $promo_data['number_of_rides'])$promo_data['referral_task_progress'] = $promo_data['number_of_rides'];
                        $ref_progress_percent = ceil(($promo_data['referral_task_progress'] / $promo_data['number_of_rides']) * 100);
                        $ref_progress_percent = $ref_progress_percent > 100 ? 100 : $ref_progress_percent;
                        $progress_bar_width = $ref_progress_percent . "%";

                        $ref_progress_text = __('{---1} trips completed',["{$promo_data['referral_task_progress']}/{$promo_data['number_of_rides']}"]);

                    }

                    if($promo_data['referral_target_status'] == 1){
                        $progress_bar_color = "#4caf50";
                        $status = __("Task completed");
                    }elseif($promo_data['referral_target_status'] == 2){
                        $progress_bar_color = "#f44336";
                        $status = __("Task not completed");
                    }
                    
                    $promo_data_html .= "<div style='padding:10px;margin-top: 10px;margin-bottom:10px;background: linear-gradient(90deg, rgba(2,0,36,1) 0%, rgba(9,9,121,1) 27%, rgba(0,212,255,1) 100%);border-radius: 10px;box-shadow: 1px 1px 2px 0px grey;'>
                                            
                                            <p id='promo-desc' style='color: #fff;font-size: 16px;font-weight: bold;margin:0 0 15px 0;'>" . __("Earn {---1} when you complete {---2} trips in {---3} days",["{$promo_data['symbol']}{$promo_data['invitee_incentive']}","{$promo_data['number_of_rides']}","{$promo_data['number_of_days']}"]) ."</p>
                                            <div style='border-radius: 10px;border:2px solid #white;padding:2px;margin-top:10px;margin-bottom:10px;width:100%;background-color:black;'><div style='width:{$progress_bar_width};height:5px;background-color:{$progress_bar_color};border-radius: 10px;'></div></div>
                                            <p style='margin:0 0 10px 0;font-size:12px;color: #bdbdbd;' id='promo-usage'>{$ref_progress_text}</p>
                                            <p style='margin:0 0 10px 0;font-size:12px;color: #bdbdbd;' id='promo-status'>{$status}</p>
                                            <hr>
                                            <p style='margin:0;font-size:12px;color: #bdbdbd;' id='promo-city'>{$promo_data['r_title']}</p>
      
                                        </div>"; 

                    
                    $data = array("success"=>1,'promodata'=>$promo_data_html);
                    echo json_encode($data); 
                    exit; 

                }
            }else{
                $error = array("error"=>__("An error has occured"));
                echo json_encode($error); 
                exit;
            }

        }else{
            $promo_data_html = "<div class='center-screen'><p style='text-align:center;'>" . __('You have no active promotions') . "</p></div>";
            $data = array("success"=>1,'promodata'=>$promo_data_html);
            echo json_encode($data); 
            exit; 
        }
    }else{
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

}




function bookingcancel(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $booking_id = (int) $_GET['bookingid'];

    if(empty($booking_id)){
        $error = array("error"=>"Invalid Data.");
        echo json_encode($error); 
        exit;  
    }

    $comment = !empty($_GET['comment']) ? mysqli_real_escape_string($GLOBALS['DB'], $_GET['comment']) : '';

    

    //Get booking details and user push notification

    $book_data = [];

    $query = sprintf('SELECT %1$stbl_bookings.status AS booking_status,%1$stbl_drivers.*,%1$stbl_users.push_notification_token AS user_push_token, %1$stbl_users.user_id AS customer_id, %1$stbl_users.disp_lang AS u_lang  FROM %1$stbl_bookings
    INNER JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
    INNER JOIN %1$stbl_drivers ON %1$stbl_drivers.driver_id = %1$stbl_bookings.driver_id 
    LEFT JOIN %1$stbl_driver_location ON %1$stbl_driver_location.driver_id = %1$stbl_drivers.driver_id 
    WHERE %1$stbl_bookings.id = "%3$d" AND %1$stbl_drivers.driver_id = "%2$d"', DB_TBL_PREFIX, $_SESSION['uid'],$booking_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
            $book_data = mysqli_fetch_assoc($result);
            if($book_data['booking_status'] == 2 || $book_data['booking_status'] == 4 || $book_data['booking_status'] == 5){
                $data_array = array("success"=>1);    
                echo json_encode($data_array); 
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

    $rlang = $book_data['u_lang'];
    


    $query = sprintf('UPDATE %stbl_bookings SET `status` = 4, cancel_comment = "%s" WHERE id = "%d" AND driver_id = "%d"', DB_TBL_PREFIX,$comment,$booking_id,$_SESSION['uid']);

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ 
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }


    //update driver allocation entry to indicate the booking has been finalized
    $query = sprintf('UPDATE %stbl_driver_allocate SET `status` = %d WHERE booking_id = %d', DB_TBL_PREFIX, 4, $booking_id, $_SESSION['uid']);
    $result = mysqli_query($GLOBALS['DB'], $query);

    //update driver's cancelled rides count
    $query = sprintf('UPDATE %stbl_drivers SET cancelled_rides = cancelled_rides + 1 WHERE driver_id = %d', DB_TBL_PREFIX, $_SESSION['uid']);
    $result = mysqli_query($GLOBALS['DB'], $query);


    //remove from active booking list so we dont keep recording driver route for this booking
    $redis = connectRedis();

    saveTravelRouteData($booking_id, $_SESSION['uid']);
    


    //send push notification to user
    
    $booking_title = str_pad($booking_id , 5, '0', STR_PAD_LEFT);
    $title = WEBSITE_NAME . " - " . __("Booking Cancelled",[],"d|{$rlang}");
    $body = __("Your booking {---1} has been cancelled by your assigned driver. Is this OK with you? Please contact us if there is a problem",["#{$booking_title}"],"d|{$rlang}");
    $device_tokens = $book_data['user_push_token'];
    if(!empty($device_tokens)){
        sendPushNotification($title,$body,$device_tokens);
    }

    


    //send silent push notifications processed within app.

        
    $title = "";
    $body = "";
    $device_tokens = $book_data['user_push_token'];
    $data = array("action"=>"driver-cancelled",
                    "booking_id" => $booking_id
                    /*"driver_firstname" => $book_data['firstname'],
                    "driver_phone" => $book_data['phone'],
                    "driver_platenum" => $book_data['car_plate_num'],
                    "driver_rating" => "4.4",
                    "driver_location_lat" => $book_data['lat'],
                    "driver_location_long" => $book_data['long'],
                    "driver_carmodel" => $book_data['car_model'],
                    "driver_photo" => "http://budgetcabng.com/" . "ajaxphotofile.php?file=" . $photo_file */
                );
    if(!empty($device_tokens)){
        sendPushNotification($title,$body,$device_tokens,$data);
    }

    sendRealTimeNotification('ridr-' . $book_data['customer_id'], $data);


    //add to user DB notification
    $content = __("Your booking {---1} has been cancelled by your assigned driver. Is this OK with you? Please contact us if there is a problem",["#{$booking_title}"],"d|{$rlang}");
    $query = sprintf('INSERT INTO %stbl_notifications(person_id,user_type,content,n_type,date_created) VALUES 
    ("%d",0,"%s",0,"%s")', 
    DB_TBL_PREFIX,
    $book_data['customer_id'],
    mysqli_real_escape_string($GLOBALS['DB'],$content),
    gmdate('Y-m-d H:i:s', time()) 
    ); 
    $result = mysqli_query($GLOBALS['DB'], $query);

    
    $data_array = array("success"=>1);    
    echo json_encode($data_array); 
    exit;

}



function bookingallocatecancel(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    $booking_id = (int) $_POST['bookingid'];

    
    $query = sprintf('UPDATE %stbl_driver_allocate SET `status` = 2 WHERE booking_id= "%d" AND driver_id="%d"', DB_TBL_PREFIX,$booking_id,$_SESSION['uid'] );

    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ 
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }

    $query = sprintf('UPDATE %stbl_drivers SET rejected_rides = rejected_rides + 1 WHERE driver_id = %d', DB_TBL_PREFIX, $_SESSION['uid']);
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

    //verify this driver is accesing his chat data
    $query = sprintf('SELECT * FROM %stbl_bookings WHERE id = %d AND driver_id = %d', DB_TBL_PREFIX,$booking_id,$_SESSION['uid']);
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
                    
                    $driver_photo = isset($row['driver_photo_file']) ? $row['driver_photo_file'] : "0";
                    $driver_photo = SITE_URL . "ajaxphotofile.php?file=" . $driver_photo;
                    //$chat_messages_html .= "<div style='text-align:right;margin-top: 5px;'><div style='background-color:#1e88e5;width:55%;border-radius:10px;padding:5px;display:inline-block'><div><img style='width:32px;border-radius:50%;vertical-align: middle;' src='{$driver_photo}' /> <span style='font-weight:bold; font-size:14px;color:white;'>{$row['driver_firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;'>{$row['chat_msg']}</p></div></div>";
                    $chat_messages_html .= "<div style='text-align:right;margin-top: 5px;'><div style='background-color:#c8e6c9;width:60%;border-radius:10px;padding:10px;display:inline-block'><div style='text-align:left'><i style='font-size: 22px;color: black;vertical-align: middle;' class='fa fa-user-circle-o'></i> <span style='font-weight:bold; font-size:14px;color:black;'>{$row['driver_firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 16px;'>{$row['chat_msg']}</p></div></div>";

                }elseif($row['chat_user_id'] != 0){
                    //rider chat message
                    $count++;
                    $user_photo = isset($row['user_photo_file']) ? $row['user_photo_file'] : "0";
                    $user_photo = SITE_URL . "ajaxuserphotofile.php?file=" . $user_photo;
                    //$chat_messages_html .= "<div style='text-align:left;margin-top: 5px;'><div style='background-color:#7cb342;width:55%;border-radius:10px;padding:5px;display:inline-block'><div style='text-align:left'><img style='width:32px;border-radius:50%;vertical-align: middle;' src='{$user_photo}' /> <span style='font-weight:bold; font-size:14px;color:white;'>{$row['user_firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;'>{$row['chat_msg']}</p></div></div>";
                    $chat_messages_html .= "<div style='text-align:left;margin-top: 5px;'><div style='background-color:#bbdefb;width:60%;border-radius:10px;padding:10px;display:inline-block'><div style='text-align:left'><i style='font-size: 22px;color: black;vertical-align: middle;' class='fa fa-user-circle-o'></i> <span style='font-weight:bold; font-size:14px;color:black;'>{$row['user_firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 16px;'>{$row['chat_msg']}</p></div></div>";

                }                

            }

            if(isset($_SESSION['chats'][$booking_id]['rider_msg_count'])){
                if($count > $_SESSION['chats'][$booking_id]['rider_msg_count']){
                    $chat_new_content_status = 1;
                    $_SESSION['chats'][$booking_id]['rider_msg_count'] = $count;
                }
            }else{
                $_SESSION['chats'][$booking_id]['rider_msg_count'] = 0;
                if($count > $_SESSION['chats'][$booking_id]['rider_msg_count']){
                    $chat_new_content_status = 1;
                    $_SESSION['chats'][$booking_id]['rider_msg_count'] = $count;
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

    $user_data = [];
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


    //first get user push_notification token
    $query = sprintf('SELECT %1$stbl_users.user_id, %1$stbl_users.push_notification_token FROM %1$stbl_bookings 
    INNER JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
    WHERE %1$stbl_bookings.id = %2$d', DB_TBL_PREFIX, $booking_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){           
            
            $user_data = mysqli_fetch_assoc($result);   
            

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
    $query = sprintf('INSERT INTO %stbl_chats (chat_msg,`driver_id`,booking_id,date_created) VALUES ("%s","%d","%d","%s")',
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

    $device_tokens = !empty($user_data['push_notification_token']) ? $user_data['push_notification_token'] : 0;
    
    if(!empty($device_tokens)){
        sendPushNotification($title,$body,$device_tokens,$data,0);
    }

    sendRealTimeNotification('ridr-' . $user_data['user_id'], $data);

    

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
    $query = sprintf('INSERT INTO %stbl_chatsupport (chat_msg,`driver_id`,session_status,date_created) VALUES ("%s","%d","%d","%s")',
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
    $query = sprintf('SELECT * FROM %stbl_chatsupport WHERE `driver_id` = %d OR driver_recipient_id = %d ORDER BY date_created ASC', DB_TBL_PREFIX, $_SESSION['uid'], $_SESSION['uid']);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){ 
                   
            while($row = mysqli_fetch_assoc($result)){
                $date_created = date('Y-m-d H:i', strtotime($row['date_created'] . ' UTC'));
                $admin_name = WEBSITE_NAME;
                if($row['driver_recipient_id'] != 0){
                    //user chat message                    
                    
                    $chat_messages_html .= "<div style='text-align:left;margin-top: 5px;'><div style='background-color:#1e88e5;width:55%;border-radius:10px;padding:5px;display:inline-block'><div><i style='font-size: 22px;color: white;vertical-align: middle;' class='fa fa-user-circle-o'></i> <span style='font-weight:bold; font-size:14px;color:white;'>{$admin_name}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;color:white;'>{$row['chat_msg']}</p><p style='margin: 5px 0;text-align: left;font-size: 10px;color:white;'>{$date_created}</p></div></div>";

                }elseif($row['driver_id'] != 0){
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
    $query = sprintf('SELECT * FROM %stbl_chatsupport WHERE `driver_id` = %d OR driver_recipient_id = %d ORDER BY date_created ASC', DB_TBL_PREFIX, $_SESSION['uid'], $_SESSION['uid']);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){ 
                   
            while($row = mysqli_fetch_assoc($result)){
                $count++;
                $date_created = date('Y-m-d H:i', strtotime($row['date_created'] . ' UTC'));
                $admin_name = WEBSITE_NAME;
                if($row['driver_recipient_id'] != 0){
                    //user chat message                    
                    
                    $chat_messages_html .= "<div style='text-align:left;margin-top: 5px;'><div style='background-color:#1e88e5;width:55%;border-radius:10px;padding:5px;display:inline-block'><div><i style='font-size: 22px;color: white;vertical-align: middle;' class='fa fa-user-circle-o'></i> <span style='font-weight:bold; font-size:14px;color:white;'>{$admin_name}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;color:white;'>{$row['chat_msg']}</p><p style='margin: 5px 0;text-align: left;font-size: 10px;color:white;'>{$date_created}</p></div></div>";

                }elseif($row['driver_id'] != 0){
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

    $resp = array("success"=> 1,"chat_content"=>$chat_messages_html, "new_msg" => $new_message, '');
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

        @mkdir(realpath(USER_PHOTO_PATH) . "/". $filename[0] . "/" . $filename[1] . "/" . $filename[2], 0777, true);

        
        $image_path = realpath(USER_PHOTO_PATH) .  "/". $filename[0] . "/" . $filename[1] . "/" . $filename[2] . "/";
        $file = $image_path . $filename . ".jpg";

    
        
        file_put_contents($file, $uploaded_photo_decoded); //store the photo to disk.     

        $chat_img_filename = $filename . ".jpg";

    }else{
        $resp = array("success"=> 1,"chat_content"=>"","chat_new_content"=>0);
        echo json_encode($resp); 
        exit;
    }

    
    $image_url = SITE_URL . "ajaxphotofile.php?file={$chat_img_filename}";
    $chat_msg = "<img style='width:150px' src='{$image_url}' />"; 
	$chat_msg = mysqli_real_escape_string($GLOBALS['DB'], $chat_msg);   

    if(empty($booking_id)){
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;
    }


    //first get user push_notification token
    $query = sprintf('SELECT %1$stbl_users.user_id, %1$stbl_users.push_notification_token FROM %1$stbl_bookings 
    INNER JOIN %1$stbl_users ON %1$stbl_users.user_id = %1$stbl_bookings.user_id
    WHERE %1$stbl_bookings.id = %2$d', DB_TBL_PREFIX, $booking_id);

    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){           
            
            $user_data = mysqli_fetch_assoc($result);   
            

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
    $query = sprintf('INSERT INTO %stbl_chats (chat_msg,`driver_id`,booking_id,date_created) VALUES ("%s","%d","%d","%s")',
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

    $device_tokens = !empty($user_data['push_notification_token']) ? $user_data['push_notification_token'] : 0;
    
    if(!empty($device_tokens)){
        sendPushNotification($title,$body,$device_tokens,$data,0);
    }

    sendRealTimeNotification('ridr-' . $user_data['user_id'], $data);

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

    //verify this driver is accesing his chat data
    $query = sprintf('SELECT * FROM %stbl_bookings WHERE id = %d AND driver_id = %d', DB_TBL_PREFIX,$booking_id,$_SESSION['uid']);
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
                    
                    $driver_photo = isset($row['driver_photo_file']) ? $row['driver_photo_file'] : "0";
                    $driver_photo = SITE_URL . "ajaxphotofile.php?file=" . $driver_photo;
                    //$chat_messages_html .= "<div style='text-align:right;margin-top: 5px;'><div style='background-color:#1e88e5;width:55%;border-radius:10px;padding:5px;display:inline-block'><div><img style='width:32px;border-radius:50%;vertical-align: middle;' src='{$driver_photo}' /> <span style='font-weight:bold; font-size:14px;color:white;'>{$row['driver_firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;'>{$row['chat_msg']}</p></div></div>";
                    $chat_messages_html .= "<div style='text-align:right;margin-top: 5px;'><div style='background-color:#c8e6c9;width:60%;border-radius:10px;padding:5px;display:inline-block'><div style='text-align:left'><i style='font-size: 22px;color: black;vertical-align: middle;' class='fa fa-user-circle-o'></i> <span style='font-weight:bold; font-size:14px;color:black;'>{$row['driver_firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;'>{$row['chat_msg']}</p></div></div>";

                }elseif($row['chat_user_id'] != 0){
                    //rider chat message
                    $count++;
                    $user_photo = isset($row['user_photo_file']) ? $row['user_photo_file'] : "0";
                    $user_photo = SITE_URL . "ajaxuserphotofile.php?file=" . $user_photo;
                    //$chat_messages_html .= "<div style='text-align:left;margin-top: 5px;'><div style='background-color:#7cb342;width:55%;border-radius:10px;padding:5px;display:inline-block'><div style='text-align:left'><img style='width:32px;border-radius:50%;vertical-align: middle;' src='{$user_photo}' /> <span style='font-weight:bold; font-size:14px;color:white;'>{$row['user_firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;'>{$row['chat_msg']}</p></div></div>";
                    $chat_messages_html .= "<div style='text-align:left;margin-top: 5px;'><div style='background-color:#bbdef;width:60%;border-radius:10px;padding:5px;display:inline-block'><div style='text-align:left'><i style='font-size: 22px;color: black;vertical-align: middle;' class='fa fa-user-circle-o'></i> <span style='font-weight:bold; font-size:14px;color:black;'>{$row['user_firstname']}</span></div><p style='margin: 5px 0;text-align: left;font-size: 14px;'>{$row['chat_msg']}</p></div></div>";

                }                

            }

            if(isset($_SESSION['chats'][$booking_id]['rider_msg_count'])){
                if($count > $_SESSION['chats'][$booking_id]['rider_msg_count']){
                    $chat_new_content_status = 1;
                    $_SESSION['chats'][$booking_id]['rider_msg_count'] = $count;
                }
            }else{
                $_SESSION['chats'][$booking_id]['rider_msg_count'] = 0;
                if($count > $_SESSION['chats'][$booking_id]['rider_msg_count']){
                    $chat_new_content_status = 1;
                    $_SESSION['chats'][$booking_id]['rider_msg_count'] = $count;
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

    $query = sprintf('SELECT COUNT(*) FROM %stbl_notifications WHERE (person_id = "%d" AND user_type = 1) OR (route_id = %d AND n_type = 5 AND user_type = 1)', DB_TBL_PREFIX, $_SESSION['uid'],$_SESSION['city_id']); //Get required user information from DB
    if($result = mysqli_query($GLOBALS['DB'], $query)){
        if(mysqli_num_rows($result)){
    
           $row = mysqli_fetch_assoc($result);
              
          $num_of_notifications = $row['COUNT(*)'];
             
         }
        mysqli_free_result($result);
    }   


    
    
    $query = sprintf('SELECT *, DATE(date_created) AS created_date FROM %stbl_notifications WHERE (person_id = "%d" AND user_type = 1) OR (route_id = %d AND n_type = 5 AND user_type = 1) ORDER BY date_created DESC LIMIT 0,50', DB_TBL_PREFIX, $_SESSION['uid'],$_SESSION['city_id']); //Get required user information from DB

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
            $notifications_formated_date = date('l, M j, Y',strtotime($notificationdatadatesort['date'] . " UTC"));
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


    $query = sprintf('DELETE FROM %stbl_notifications WHERE person_id = "%d" AND user_type = 1 AND id = "%d"', DB_TBL_PREFIX, $_SESSION['uid'],$notification_id); 
    
    if(!$result = mysqli_query($GLOBALS['DB'], $query)){ 
        $error = array("error"=>__("An error has occured"));
        echo json_encode($error); 
        exit;

                
    }


    $data_array = array("success"=>1);    
    echo json_encode($data_array); 
    exit;




}


function paystackInit(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    include "../drop-files/lib/pgateways/paystack/paystack-transaction-init.php";  
	
  
  
  }

  function pesapalInit(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    include "../drop-files/lib/pgateways/pesapal/pesapal-transaction-init.php";  
	
  
  
  }


  function paytrInit(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    include "../drop-files/lib/pgateways/paytr/paytr-transaction-init.php";  
	
  
  
  }


  function stripeInit(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    include "../drop-files/lib/pgateways/stripe/stripe-transaction-init.php";  
	
  
  
  }


  function flutterwaveInit(){

    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
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




  function customInt(){
    if(empty($_SESSION['loggedin'])){
        $error = array("error"=>__("Please login to continue"));
        echo json_encode($error); 
        exit; 
    }

    include "../drop-files/lib/pgateways/custom/custom-transaction-init.php";
  }


?>
